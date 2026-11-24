<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    private string $installedFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installedFile = storage_path('installed');
        $this->backup = File::exists($this->installedFile) ? File::get($this->installedFile) : null;
    }

    protected function tearDown(): void
    {
        if ($this->backup === null) {
            File::delete($this->installedFile);
        } else {
            File::put($this->installedFile, $this->backup);
        }

        parent::tearDown();
    }

    private ?string $backup = null;

    public function test_requests_redirect_to_installer_when_not_installed(): void
    {
        File::delete($this->installedFile);

        $this->get('/')->assertRedirect('/install');
        $this->get('/manga')->assertRedirect('/install');
    }

    public function test_dashboard_is_not_reachable_by_guests(): void
    {
        File::delete($this->installedFile);

        // `auth` runs before `checkInstallation` (middleware priority), so a
        // guest is sent to the login page rather than straight to the installer.
        $response = $this->get('/dashboard');

        $response->assertRedirect();
        $this->assertContains(
            parse_url($response->headers->get('Location'), PHP_URL_PATH),
            ['/login', '/install']
        );
    }
}
