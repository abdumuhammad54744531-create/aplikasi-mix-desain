<?php
namespace App\Services;
use App\Models\AuditLog;
final class AuditService {
    public static function record(string $module, string $action, mixed $model, ?array $before = null): void {
        AuditLog::create(['user_id'=>auth()->id(),'module'=>$module,'action'=>$action,
            'record_type'=>is_object($model)?get_class($model):null,'record_id'=>$model->id??null,
            'before_data'=>$before,'after_data'=>is_object($model)?$model->toArray():null,
            'ip_address'=>request()->ip()]);
    }
}
