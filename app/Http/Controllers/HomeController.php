<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class HomeController extends Controller
{
    public function index()
    {
        // Try different methods to detect tenant context
        $currentDomain = request()->getHost();

        // Method 1: Check if current domain matches any tenant domain
        $isTenantDomain = Domain::where('domain', $currentDomain)->exists();

        // Method 2: Try to manually resolve tenant from domain
        $tenantResolver = app(DomainTenantResolver::class);
        $tenant = null;

        try {
            $tenant = $tenantResolver->resolve($currentDomain);
        } catch (\Exception $e) {
            // No tenant found for this domain
        }

        if ($isTenantDomain || $tenant) {
            // redirect to tenant homepage
            return redirect()->route('home');
        } else {
            // This is a central domain
            $tenants = Tenant::all();
            return Inertia::render('CentralWelcome', [
                'tenants' => $tenants
            ]);
        }
    }
}
