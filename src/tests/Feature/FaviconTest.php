<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_declare_the_configured_png_favicon(): void
    {
        $this->get('/pqrsf')
            ->assertOk()
            ->assertSee('<link rel="icon" type="image/png"', false)
            ->assertSee('logo_favicon.png');
    }
}
