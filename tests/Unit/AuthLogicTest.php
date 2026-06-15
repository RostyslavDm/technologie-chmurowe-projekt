<?php

namespace Tests\Unit;

use App\Transfer\User;
use PHPUnit\Framework\TestCase;

// Testy jednostkowe logiki uwierzytelniania — nie dotykaja bazy ani frameworka,
// wiec sa szybkie i deterministyczne (idealne jako bramka jakosci w CI).
class AuthLogicTest extends TestCase
{
    // Hasla musza byc hashowane (bcrypt) i weryfikowalne — nigdy trzymane jawnie.
    public function test_password_is_hashed_with_bcrypt_and_verifiable(): void
    {
        $plain = 'tajneHaslo123';
        $hash  = password_hash($plain, PASSWORD_BCRYPT);

        $this->assertNotSame($plain, $hash, 'Haslo nie moze byc zapisane jawnie.');
        $this->assertStringStartsWith('$2y$', $hash, 'Oczekiwano hasha w formacie bcrypt.');
        $this->assertTrue(password_verify($plain, $hash), 'Poprawne haslo powinno przejsc weryfikacje.');
        $this->assertFalse(password_verify('zleHaslo', $hash), 'Bledne haslo nie moze przejsc weryfikacji.');
    }

    // Dostep do panelu administratora ma wylacznie uzytkownik z rola 'admin'.
    public function test_only_admin_role_is_recognised_as_admin(): void
    {
        $admin = new User(email: 'admin@plantcare.app', role: 'admin', id: 1, nickname: 'admin');
        $user  = new User(email: 'user@plantcare.app',  role: 'user',  id: 2, nickname: 'user');

        $this->assertTrue($admin->isAdmin(), 'Uzytkownik z rola admin powinien byc adminem.');
        $this->assertFalse($user->isAdmin(), 'Zwykly uzytkownik nie moze byc adminem.');
    }
}
