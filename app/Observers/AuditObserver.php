<?php

namespace App\Observers;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        app(AuditLogService::class)->record(
            'created',
            $model,
            [],
            $model->getAttributes()
        );
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $before = [];

        foreach (array_keys($changes) as $attribute) {
            $before[$attribute] = $model->getRawOriginal($attribute);
        }

        app(AuditLogService::class)->record('updated', $model, $before, $changes);
    }

    public function deleted(Model $model): void
    {
        app(AuditLogService::class)->record(
            'deleted',
            $model,
            $model->getAttributes(),
            []
        );
    }
}
