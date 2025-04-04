<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantAssetController extends Controller
{
    /**
     * Serve a tenant-specific asset.
     *
     * @param  string  $path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke($path)
    {
        // Check if the file exists in the tenant's public storage
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Get file mime type
        $mimeType = mime_content_type(Storage::disk('public')->path($path));

        // Return file as a streamed response
        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
