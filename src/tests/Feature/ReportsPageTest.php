<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    protected bool $isMysql = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->isMysql = DB::connection()->getDriverName() === 'mysql';
    }

    public function test_reports_page_requires_auth()
    {
        $response = $this->get('/admin/reports');
        $response->assertRedirect('/admin/login');
    }

    public function test_reports_page_loads()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/admin/reports');
        $response->assertStatus(200);
    }

    public function test_pdf_download_requires_auth()
    {
        $response = $this->get('/admin/reportes/pdf');
        $response->assertRedirect('/admin/login');
    }

    public function test_pdf_download_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/reportes/pdf');

        $response->assertForbidden();
    }

    /** @requires extension pdo_mysql */
    public function test_pdf_download()
    {
        if (! $this->isMysql) {
            $this->markTestSkipped('Requires MySQL (JSON functions)');
        }

        $user = User::first();
        $response = $this->actingAs($user)->get('/admin/reportes/pdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

}
