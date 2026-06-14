<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Forms\RegisterForm;
use App\Transfer\User;
use App\Services\Database;
use Medoo\Medoo;

class RegisterController extends Controller {

    public function showRegister() {
        return view('auth.register', ['form' => new RegisterForm(), 'errors' => []]);
    }

    // rejestracja nowego konta
    public function doRegister(Request $request) {
        $form = new RegisterForm();
        $form->email = $request->input('email');
        $form->pass = $request->input('pass');
        $form->passConfirm = $request->input('pass_confirm');
        $errors = [];

        // validate
        if (!$form->email && !$form->pass && !$form->passConfirm) {
            $errors[] = 'Błędne wywołanie aplikacji!';
        } else {
            if (!$form->email) {
                $errors[] = 'Nie podano adresu email.';
            }
            if (!$form->pass) {
                $errors[] = 'Nie podano hasła.';
            }
            elseif (strlen($form->pass) < 6) {
                $errors[] = 'Hasło musi mieć co najmniej 6 znaków.';
            }
            if ($form->pass && $form->passConfirm !== $form->pass) {
                $errors[] = 'Hasła nie są identyczne.';
            }
        }

        if (empty($errors)) {
            $db = Database::getInstance();
            if ($db->has('users', ['email' => $form->email])) {
                $errors[] = 'Konto z tym adresem email już istnieje. Zaloguj się.';
            }
        }

        if (empty($errors)) {
            $db = Database::getInstance();
            $userId = $this->registerNewUser($db, $form->email, $form->pass);
            $this->loginAs(new User(
                id:       $userId,
                email:    $form->email,
                nickname: $this->nicknameFromEmail($form->email),
                role:     'user',
            ));
            return redirect()->route('nickname.show')
                ->with('infos', ['Konto założone! Wybierz teraz swój nick.']);
        }

        return view('auth.register', ['form' => $form, 'errors' => $errors]);
    }

    // helpers

    private function registerNewUser(Medoo $db, string $email, string $plainPass): int {
        $userId = 0;
        $db->action(function (Medoo $db) use ($email, $plainPass, &$userId) {
            $nickname = $this->uniqueNickname($db, $this->nicknameFromEmail($email));
            $hash = password_hash($plainPass, PASSWORD_BCRYPT);

            $db->insert('users', [
                'email' => $email,
                'nickname' => $nickname,
                'password' => $hash,
            ]);
            $userId = (int) $db->id();

            // RODO: kto utworzyl rekord = sam siebie przy rejestracji wlasnej
            $db->update('users', ['created_by' => $userId], ['id' => $userId]);

            // domyslna rola 'user'
            $userRole = $db->get('roles', ['id'], ['name' => 'user']);
            if (!$userRole) {
                throw new \RuntimeException('Brak roli "user" w bazie — czy seedy się wykonały?');
            }
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => (int) $userRole['id'],
            ]);
        });
        return $userId;
    }

    private function nicknameFromEmail(string $email): string {
        $local = strstr($email, '@', true) ?: $email;
        return substr(preg_replace('/[^a-zA-Z0-9_]/', '', $local), 0, 50) ?: 'user';
    }

    private function uniqueNickname(Medoo $db, string $base): string {
        $candidate = $base;
        $i = 1;
        while ($db->has('users', ['nickname' => $candidate])) {
            $i++;
            $candidate = substr($base, 0, 47) . $i;   // 50-char limit z DDL
        }
        return $candidate;
    }
}
