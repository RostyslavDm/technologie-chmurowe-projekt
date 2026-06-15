<?php

namespace Tests\Feature;

use Tests\TestCase;

// Testy feature (HTTP, end-to-end na poziomie zadania-odpowiedzi):
// sprawdzaja, ze publiczne strony realnie odpowiadaja przez caly stos Laravela.
class PublicPagesTest extends TestCase
{
    // Endpoint zdrowia (Laravel health check) — uzywany tez przez Azure App Service.
    public function test_health_endpoint_responds(): void
    {
        $this->get('/up')->assertStatus(200);
    }

    // Strona logowania jest publiczna i powinna sie poprawnie wyrenderowac.
    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    // Strona rejestracji jest publiczna i powinna sie poprawnie wyrenderowac.
    public function test_register_page_is_accessible(): void
    {
        $this->get('/register')->assertStatus(200);
    }
}
