<?php

namespace App\Traits;

use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log model creation
     */
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            app(LoggingServiceInterface::class)->logAudit(
                'created',
                class_basename($model),
                $model->id,
                ['new_data' => $model->getAttributes()]
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();

            $auditChanges = [];
            foreach ($changes as $field => $newValue) {
                if ($field !== 'updated_at') {
                    $auditChanges[$field] = [
                        'from' => $original[$field] ?? null,
                        'to' => $newValue,
                    ];
                }
            }

            if (!empty($auditChanges)) {
                app(LoggingServiceInterface::class)->logAudit(
                    'updated',
                    class_basename($model),
                    $model->id,
                    $auditChanges
                );
            }
        });

        static::deleted(function ($model) {
            app(LoggingServiceInterface::class)->logAudit(
                'deleted',
                class_basename($model),
                $model->id,
                ['deleted_data' => $model->getAttributes()]
            );
        });
    }

    /**
     * Log custom activity
     */
    public function logActivity(string $action, array $data = []): void
    {
        app(LoggingServiceInterface::class)->logAudit(
            $action,
            class_basename($this),
            $this->id,
            $data
        );
    }

    /**
     * Log business operation
     */
    public function logBusinessOperation(string $operation, array $data = [], string $status = 'success'): void
    {
        app(LoggingServiceInterface::class)->logBusinessOperation(
            $operation,
            array_merge($data, ['model' => class_basename($this), 'model_id' => $this->id]),
            $status
        );
    }
}
