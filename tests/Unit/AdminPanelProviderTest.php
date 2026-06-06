<?php

namespace Tests\Unit;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Tests\TestCase;

class AdminPanelProviderTest extends TestCase
{
    public function test_admin_panel_uses_full_max_content_width(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertSame(Width::Full, $panel->getMaxContentWidth());
    }
}
