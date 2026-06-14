<?php

namespace Tests\Unit;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use ReflectionProperty;
use Tests\TestCase;

class AdminPanelProviderTest extends TestCase
{
    public function test_admin_panel_uses_full_max_content_width(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertSame(Width::Full, $panel->getMaxContentWidth());
    }

    public function test_admin_panel_registers_sidebar_navigation_search_hook(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());
        $renderHooksProperty = new ReflectionProperty($panel, 'renderHooks');

        /** @var array<string, array<string, array<callable>>> $renderHooks */
        $renderHooks = $renderHooksProperty->getValue($panel);
        $sidebarHooks = $renderHooks[PanelsRenderHook::SIDEBAR_NAV_START][''] ?? [];

        $this->assertCount(1, $sidebarHooks);
        $this->assertStringContainsString(
            'data-sidebar-navigation-search',
            (string) app()->call($sidebarHooks[0]),
        );
    }
}
