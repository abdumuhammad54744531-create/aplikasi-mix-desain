<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $permission = $this->resolve($request, $permission);
        abort_unless(auth()->user()?->hasPermission($permission), 403, 'Anda tidak memiliki izin untuk mengakses menu atau tindakan ini.');

        return $next($request);
    }

    private function resolve(Request $request, string $permission): string
    {
        [$module, $action] = array_pad(explode('.', $permission, 2), 2, 'view');
        if ($module === 'aggregate') {
            $type = (string) ($request->route('aggregate') ?? $request->route('type') ?? $request->route('module'));
            if ($type === '') {
                return auth()->user()?->hasPermission('pasir.'.$action) ? 'pasir.'.$action : 'kerikil.'.$action;
            }
            $module = str_contains($type, 'fine') || str_contains($type, 'pasir') ? 'pasir' : (str_contains($type, 'coarse') || str_contains($type, 'kerikil') || str_contains($type, 'abrasion') ? 'kerikil' : 'materials');
        }
        if ($module === 'workflow') {
            $module = $request->route('type') === 'compressive-strength' ? 'kuat-tekan' : 'mix-design';
        }

        return $module.'.'.$action;
    }
}
