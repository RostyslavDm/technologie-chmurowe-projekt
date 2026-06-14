<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Forms\LoginForm;
use App\Forms\NicknameForm;
use App\Transfer\User;
use App\Services\Database;
use Medoo\Medoo;

class LoginController extends Controller {

    public function showLogin() {
        return view('auth.login', ['form' => new LoginForm(), 'errors' => []]);
    }

    // login form submission
    public function doLogin(Request $request) {
        $form        = new LoginForm();
        $form->email = $request->input('email');
        $form->pass  = $request->input('pass');
        $errors      = [];

        // validate
        if (!$form->email && !$form->pass) {
            $errors[] = 'Błędne wywołanie aplikacji!';
        } else {
            if (!$form->email) $errors[] = 'Nie podano adresu email.';
            if (!$form->pass)  $errors[] = 'Nie podano hasła.';
            elseif (strlen($form->pass) < 6) $errors[] = 'Hasło musi mieć co najmniej 6 znaków.';

        }

        if (empty($errors)) {
            // pyta baze danych — email zamiast loginu, bo tak chce DDL
            $db   = Database::getInstance();
            $row  = $db->get('users', ['id', 'email', 'nickname', 'password'], [
                'email' => $form->email
            ]);

            // OPCJA 1: email nie istnieje -> sugerujemy rejestracje
            if (!$row) {
                $errors[] = 'Nie znaleziono konta z tym adresem. Chcesz się zarejestrować?';
            }
            // OPCJA 2: istniejacy user, dobre haslo -> home
            elseif (password_verify($form->pass, $row['password'])) {
                $this->loginAs(new User(
                    id:       (int) $row['id'],
                    email:    $row['email'],
                    nickname: $row['nickname'],
                    role:     $this->fetchUserRole($db, (int) $row['id']),
                ));
                return redirect()->route('home')
                    ->with('infos', ['Witaj z powrotem, '.$row['nickname'].'.']);
            }

            // OPTION 3: zle haslo
            else {
                $errors[] = 'Spróbuj jeszcze raz — tym razem z *prawidłowym* hasłem.';
            }
        }

        // z powrotem w login z bledami
        return view('auth.login', ['form' => $form, 'errors' => $errors]);
    }

    // handle logout
    public function doLogout() {
        session()->forget('user');
        return view('auth.login', [
            'form'   => new LoginForm(),
            'errors' => [],
            'infos'  => ['Poprawnie wylogowano z systemu.'],
        ]);
    }

    // pokazuje formularz wyboru nicku (po rejestracji albo recznie)
    public function chooseNickname() {
        $user = $this->currentUser();
        $form = new NicknameForm();
        $form->nickname = $user->nickname;
        return view('auth.nickname', [
            'form'   => $form,
            'user'   => $user,
            'errors' => [],
            'infos'  => (array) session('infos', []),
        ]);
    }

    // zapis nicku
    public function saveNickname(Request $request) {
        $user = $this->currentUser();
        $form = new NicknameForm();
        $form->nickname = trim((string) $request->input('nickname'));
        $errors = [];

        // validate
        if (!$form->nickname) {
            $errors[] = 'Nie podano nicku.';
        } else {
            if (strlen($form->nickname) > 50)
                $errors[] = 'Nick nie może być dłuższy niż 50 znaków.';
            if (preg_replace('/[^a-zA-Z0-9_]/', '', $form->nickname) !== $form->nickname)
                $errors[] = 'Nick może zawierać tylko litery, cyfry i znak _.';
        }

        if (empty($errors)) {
            $db = Database::getInstance();

            // unikalnosc — z wykluczeniem siebie (gdyby nic nie zmienial)
            $taken = $db->has('users', [
                'nickname' => $form->nickname,
                'id[!]' => $user->id,
            ]);

            if ($taken) {
                $errors[] = 'Ten nick jest już zajęty.';
            } else {
                $db->update('users',
                    ['nickname' => $form->nickname],
                    ['id' => $user->id]
                );
                $user->nickname = $form->nickname;
                $this->loginAs($user);
                return redirect()->route('home')
                    ->with('infos', ['Nick ustawiony. Witaj, '.$user->nickname.'.']);
            }
        }

        return view('auth.nickname', [
            'form' => $form,
            'user' => $user,
            'errors' => $errors,
            'infos' => [],
        ]);
    }

    // helpers

    private function fetchUserRole(Medoo $db, int $userId): string {
        return $db->get(
            'roles',
            ['[>]user_roles' => ['id' => 'role_id']],
            'name',
            [
                'user_roles.user_id'    => $userId,
                'user_roles.revoked_at' => null,
            ]
        ) ?? 'user';
    }
}
