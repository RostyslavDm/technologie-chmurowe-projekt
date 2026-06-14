<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Forms\PlantTypeForm;
use App\Services\Database;

class AdminController extends Controller {

    // typy roslin — zarzadzanie

    // lista + formularz nowego typu
    public function plantTypes(Request $request) {
        $user       = $this->currentUser();
        $db         = Database::getInstance();

        $where = ['ORDER' => ['name' => 'ASC']];

        // paginacja
        $perPage  = 8;
        $page     = max(1, (int) $request->input('page', 1));
        $total    = $db->count('plant_types', $where);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $lastPage);
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];   // Medoo: [offset, ile]

        $plantTypes = $db->select(
            'plant_types',
            ['id', 'name', 'description', 'watering_interval_days', 'is_active'],
            $where
        ) ?: [];

        return view('admin.plant-types', [
            'user'       => $user,
            'plantTypes' => $plantTypes,
            'form'       => new PlantTypeForm(),
            'page'       => $page,        // dane dla pagera
            'lastPage'   => $lastPage,
            'errors'     => (array) session('admin_errors', []),
            'infos'      => (array) session('infos', []),
        ]);
    }

    // dodanie nowego typu rosliny (POST)
    public function storePlantType(Request $request) {
        $form                         = new PlantTypeForm();
        $form->name                   = trim((string) $request->input('name'));
        $form->description            = trim((string) $request->input('description')) ?: null;
        $form->watering_interval_days = $request->input('watering_interval_days');
        $errors                       = [];

        // validate
        if (!$form->name && !$form->watering_interval_days) {
            $errors[] = 'Błędne wywołanie aplikacji!';
        } else {
            if (!$form->name)                                $errors[] = 'Nie podano nazwy typu.';
            elseif (strlen($form->name) > 100)               $errors[] = 'Nazwa typu nie może być dłuższa niż 100 znaków.';
            if (!$form->watering_interval_days)              $errors[] = 'Nie podano interwału podlewania.';
            elseif (!is_numeric($form->watering_interval_days))
                                                              $errors[] = 'Interwał musi być liczbą.';
            elseif ($form->watering_interval_days <= 0)      $errors[] = 'Interwał musi być większy od 0.';
            elseif ($form->watering_interval_days > 365)     $errors[] = 'Interwał nie może być większy niż 365 dni.';
            if ($form->description && strlen($form->description) > 500)
                                                              $errors[] = 'Opis nie może być dłuższy niż 500 znaków.';
        }

        if (empty($errors)) {
            $db = Database::getInstance();
            // walidacja kontekstowa — nazwa musi byc unikalna
            if ($db->has('plant_types', ['name' => $form->name])) {
                $errors[] = 'Typ o takiej nazwie już istnieje.';
            } else {
                $db->insert('plant_types', [
                    'name' => $form->name,
                    'description' => $form->description,
                    'watering_interval_days' => (int) $form->watering_interval_days,
                    'is_active' => 1,
                ]);
                return redirect()->route('admin.plant-types')
                    ->with('infos', ['Typ „'.$form->name.'” dodany.']);
            }
        }

        return redirect()->route('admin.plant-types')
            ->with('admin_errors', $errors);
    }

    // przelacz aktywne/nieaktywne (POST)
    public function togglePlantType(Request $request) {
        $id = (int) $request->input('id');
        $db = Database::getInstance();
        $pt = $db->get('plant_types', ['id', 'name', 'is_active'], ['id' => $id]);

        if (!$pt) {
            return redirect()->route('admin.plant-types')
                ->with('admin_errors', ['Nie znaleziono typu rośliny.']);
        }

        $newState = $pt['is_active'] ? 0 : 1;
        $db->update('plant_types', ['is_active' => $newState], ['id' => $id]);

        $msg = $newState
            ? 'Typ „'.$pt['name'].'” aktywowany.'
            : 'Typ „'.$pt['name'].'” dezaktywowany.';
        return redirect()->route('admin.plant-types')->with('infos', [$msg]);
    }

    // uzytkownicy — zarzadzanie

    // lista z wyszukiwaniem i filtrowaniem po roli
    public function users(Request $request) {
        $user = $this->currentUser();
        $db = Database::getInstance();

        $search       = trim((string) $request->input('search', ''));
        $filterRoleId = $request->input('role_id');

        // 1. lista userow z filtrami (bez JOIN-a do user_roles, zeby nie zduplikowac)
        $where = ['ORDER' => ['users.id' => 'ASC']];
        if ($search) {
            $where['OR'] = [
                'nickname[~]' => $search,
                'email[~]'    => $search,
            ];
        }
        // filtr po roli — przez podzapytanie ID-kow z user_roles
        if ($filterRoleId) {
            $matching = $db->select('user_roles', 'user_id', [
                'role_id'    => (int) $filterRoleId,
                'revoked_at' => null,
            ]) ?: [];
            $where['id'] = !empty($matching) ? $matching : [0];   // [0] = pusty wynik
        }

        // paginacja
        // count po samej tabeli 'users'
        $perPage  = 6;
        $page     = max(1, (int) $request->input('page', 1));
        $total    = $db->count('users', $where);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $lastPage);
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];   // Medoo: [offset, ile]

        $usersList = $db->select('users',
            ['id', 'nickname', 'email', 'created_at'],
            $where
        ) ?: [];

        // 2. doczep aktywna role do kazdego usera
        foreach ($usersList as &$u) {
            $u['role'] = $db->get(
                'roles',
                ['[>]user_roles' => ['id' => 'role_id']],
                'name',
                [
                    'user_roles.user_id'    => $u['id'],
                    'user_roles.revoked_at' => null,
                ]
            ) ?? 'user';
        }
        unset($u);

        $roles = $db->select('roles', ['id', 'name'],
            ['is_active' => 1, 'ORDER' => ['name' => 'ASC']]) ?: [];

        return view('admin.users', [
            'user'         => $user,
            'users'        => $usersList,
            'roles'        => $roles,
            'search'       => $search,
            'filterRoleId' => $filterRoleId,
            'page'         => $page,        // dane dla pagera
            'lastPage'     => $lastPage,
            'errors'       => (array) session('admin_errors', []),
            'infos'        => (array) session('infos', []),
        ]);
    }

    // zmiana roli usera (POST)
    public function updateUserRole(Request $request) {
        $userId    = (int) $request->input('user_id');
        $newRoleId = (int) $request->input('role_id');
        $admin     = $this->currentUser();
        $errors    = [];

        // walidacja
        if (!$userId)    $errors[] = 'Brak ID użytkownika.';
        if (!$newRoleId) $errors[] = 'Nie wybrano roli.';

        // walidacja kontekstowa: admin nie moze sobie sam odebrac uprawnien
        if (empty($errors) && $userId === $admin->id) {
            $errors[] = 'Nie możesz zmienić własnej roli.';
        }

        if (empty($errors)) {
            $db = Database::getInstance();
            if (!$db->has('users', ['id' => $userId])) {
                $errors[] = 'Nie znaleziono użytkownika.';
            } elseif (!$db->has('roles', ['id' => $newRoleId, 'is_active' => 1])) {
                $errors[] = 'Nie znaleziono aktywnej roli.';
            } else {
                // istniejacy wpis -> UPDATE, brak wpisu -> INSERT
                if ($db->has('user_roles', ['user_id' => $userId])) {
                    $db->update('user_roles',
                        ['role_id' => $newRoleId, 'revoked_at' => null],
                        ['user_id' => $userId]
                    );
                } else {
                    $db->insert('user_roles', [
                        'user_id' => $userId,
                        'role_id' => $newRoleId,
                    ]);
                }
                $roleName    = $db->get('roles', 'name', ['id' => $newRoleId]);
                $targetUser  = $db->get('users', ['nickname', 'email'], ['id' => $userId]);
                $targetLabel = $targetUser['nickname'] ?: $targetUser['email'];
                return redirect()->route('admin.users')
                    ->with('infos', ['Rola użytkownika „'.$targetLabel.'” zmieniona na „'.$roleName.'”.']);
            }
        }

        return redirect()->route('admin.users')
            ->with('admin_errors', $errors);
    }
}
