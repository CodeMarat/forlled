<?php

namespace App\Observers;

use App\Support\Audit\AuditLogWriter;
use Illuminate\Database\Eloquent\Model;

class AdminActivityObserver
{
    public function __construct(
        protected AuditLogWriter $auditLogWriter,
    ) {}

    public function created(Model $model): void
    {
        $this->auditLogWriter->write($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->auditLogWriter->write($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->auditLogWriter->write($model, 'deleted');
    }
}
