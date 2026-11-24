<?php

namespace Tests\Feature;

use App\Models\Ad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdsRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::ensureDirectoryExists(storage_path());
        File::put(storage_path('installed'), 'installed');
    }

    public function test_inactive_ad_returns_404(): void
    {
        $ad = Ad::create([
            'name' => 'Test',
            'identifier' => 'test-ad',
            'link' => 'https://example.com',
            'type' => 'banner',
            'is_active' => false,
        ]);

        $this->get(route('ads.redirect', $ad))->assertNotFound();
    }

    public function test_ad_rejects_non_http_link(): void
    {
        $ad = Ad::create([
            'name' => 'Test',
            'identifier' => 'test-ad-2',
            'link' => 'javascript:alert(1)',
            'type' => 'banner',
            'is_active' => true,
        ]);

        $this->get(route('ads.redirect', $ad))->assertNotFound();
    }

    public function test_active_ad_redirects_to_external_link(): void
    {
        $ad = Ad::create([
            'name' => 'Test',
            'identifier' => 'test-ad-3',
            'link' => 'https://example.com/path',
            'type' => 'banner',
            'is_active' => true,
        ]);

        $this->get(route('ads.redirect', $ad))->assertRedirect('https://example.com/path');
    }
}
