<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Forms\PlantForm;
use App\Services\Database;

class PlantController extends Controller {

    // lista roslin uzytkownika (z wyszukiwaniem i filtrowaniem)
    public function index(Request $request) {
        $user   = $this->currentUser();
        $db     = Database::getInstance();

        $search = trim((string) $request->input('search', ''));
        $typeId = $request->input('plant_type_id');

        $where = [
            'plants.user_id' => $user->id,
            'ORDER'          => ['plants.created_at' => 'DESC'],
        ];
        if ($search) $where['plants.name[~]'] = $search;
        if ($typeId) $where['plants.plant_type_id'] = (int) $typeId;

        //  paginacja
        // count na samej tabeli 'plants' (filtry dotykaja tylko jej)
        $perPage  = 6;
        $page     = max(1, (int) $request->input('page', 1));
        $total    = $db->count('plants', $where);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $lastPage);
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];   // Medoo: [offset, ile]

        // JOIN z plant_types zeby dostac nazwe typu bez drugiego zapytania
        $plants = $db->select(
            'plants',
            ['[>]plant_types' => ['plant_type_id' => 'id']],
            ['plants.id', 'plants.name', 'plants.notes', 'plants.created_at',
             'plant_types.name(plant_type_name)'],
            $where
        ) ?: [];

        // typy do dropdownu filtra
        $plantTypes = $db->select('plant_types', ['id', 'name'],
            ['is_active' => 1, 'ORDER' => ['name' => 'ASC']]) ?: [];

        return view('plants.index', [
            'user' => $user,
            'plants' => $plants,
            'plantTypes' => $plantTypes,
            'search' => $search,
            'typeId' => $typeId,
            'page' => $page,           // dane dla pagera
            'lastPage' => $lastPage,
            'infos' => (array) session('infos', []),
            'errors' => [],
        ]);
    }

    // formularz dodania rosliny (GET)
    public function create() {
        $user = $this->currentUser();
        $db = Database::getInstance();
        $plantTypes = $db->select('plant_types', ['id', 'name'], ['is_active' => 1, 'ORDER' => ['name' => 'ASC']]) ?: [];

        return view('plants.create', [
            'user' => $user,
            'form' => new PlantForm(),
            'plantTypes' => $plantTypes,
            'errors' => [],
        ]);
    }

    // zapis nowej rosliny (POST)
    public function store(Request $request) {

        $user = $this->currentUser();
        $form  = new PlantForm();
        $form->name = trim((string) $request->input('name'));
        $form->plant_type_id = $request->input('plant_type_id');
        $form->notes = trim((string) $request->input('notes')) ?: null;
        $errors = [];

        // validate
        if (!$form->name && !$form->plant_type_id) {
            $errors[] = 'Błędne wywołanie aplikacji!';
        } else {
            if (!$form->name) {
                $errors[] = 'Nie podano nazwy rośliny.';
            }
            elseif (strlen($form->name) > 100) {
                $errors[] = 'Nazwa nie może być dłuższa niż 100 znaków.';
            }
            if (!$form->plant_type_id) {
                $errors[] = 'Nie wybrano typu rośliny.';
            }
            if ($form->notes && strlen($form->notes) > 1000) {
                $errors[] = 'Notatka nie może być dłuższa niż 1000 znaków.';
            }
        }

        if (empty($errors)) {
            $db = Database::getInstance();

            // walidacja kontekstowa — typ musi istniec i byc aktywny (nie mozna wpisac ID z palca)
            if (!$db->has('plant_types', ['id' => (int) $form->plant_type_id, 'is_active' => 1])) {
                $errors[] = 'Wybrany typ rośliny nie istnieje lub jest nieaktywny.';
            } else {
                $db->insert('plants', [
                    'user_id' => $user->id,
                    'plant_type_id' => (int) $form->plant_type_id,
                    'name' => $form->name,
                    'notes' => $form->notes,
                ]);
                return redirect()->route('plants.index')
                    ->with('infos', ['Roślina „'.$form->name.'” dodana.']); //jak ja wgl zrobilam rozne cudzslowy?
            }
        }

        // z powrotem na formularz z bledami i wypelnionymi polami
        $db = Database::getInstance();
        $plantTypes = $db->select('plant_types', ['id', 'name'], ['is_active' => 1, 'ORDER' => ['name' => 'ASC']]) ?: [];
        return view('plants.create', [
            'user' => $user,
            'form' => $form,
            'plantTypes' => $plantTypes,
            'errors' => $errors,
        ]);
    }
}
