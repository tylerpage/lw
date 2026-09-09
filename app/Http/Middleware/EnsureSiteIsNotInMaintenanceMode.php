<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Services\MaintenanceModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsNotInMaintenanceMode
{
    public function __construct(
        private MaintenanceModeService $maintenanceMode,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->maintenanceMode->shouldShowMaintenance($request)) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [
            'siteName' => SiteSetting::get('site_name', config('app.name')),
        ], 503);
    }
}
