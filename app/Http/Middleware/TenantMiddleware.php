<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $tenantId = tenant('id');

        if ($user && $tenantId) {
            // Check if the user belongs to the current tenant
            if ($user->tenant_id !== $tenantId && !$user->isAdmin()) {
                Auth::logout();

                return redirect()->route('login')
                    ->with('error', 'You do not have access to this store.');
            }
        }

        return $next($request);
    }
}
