<?php
namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;

class TrackUserObserver
{
    public function creating($model)
    {
        $model->created_by = Auth::id();
        $model->updated_by = Auth::id();
    }

    public function created($model)
    {
        $this->logActivity('created', $model);
    }

    public function updating($model)
    {
        $model->updated_by = Auth::id();
    }

    public function updated($model)
    {
        $this->logActivity('updated', $model);
    }

    protected function logActivity(string $action, $model)
    {
        $user = Auth::user();
        $modelName = class_basename($model);
        $modelId = $model->id ?? null;

        $description = sprintf(
            '[AUDIT] %s %s with ID #%s by user #%s (%s)',
            $modelName,
            $action,
            $modelId,
            $user?->id ?? 'guest',
            $user?->email ?? 'unknown'
        );

        // Log to file
        // Log::channel('daily')->info($description);

        // Log to database
        $modelId = is_numeric($model->id) ? (int) $model->id : null;

        AuditLog::create([
            'model_type' => get_class($model),
            'model_id' => $modelId,
            'action' => $action,
            'user_id' => $user?->id,
            'description' => $description,
        ]);
    }
}
