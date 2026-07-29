<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureLaboratoryStaff
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()?->role === 'pemohon') {
            return redirect()->route('lab-requests.index')
                ->with('info', 'Akun pemohon hanya dapat mengisi dan memantau permohonan pengujian laboratorium.');
        }

        return $next($request);
    }
}
