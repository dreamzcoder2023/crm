<?php

namespace App\Http\Middleware;

use App\Models\Settings;
use Closure;
use Illuminate\Http\Request;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
         $maintenance = Settings::where('key','is_maintenance')->first();

        // Allow access to admin routes even in maintenance mode
        if ($maintenance && $maintenance->value == 1) {
            return response()->view('maintenance');
        }else{
            return $next($request);
        }
    }
}
