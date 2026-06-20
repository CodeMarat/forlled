<?php

namespace App\Models\Concerns;

use App\Observers\AdminActivityObserver;

trait HasAdminAudit
{
    public static function bootHasAdminAudit(): void
    {
        static::observe(AdminActivityObserver::class);
    }
}
