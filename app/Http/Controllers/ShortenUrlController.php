<?php

namespace App\Http\Controllers;

use App\Models\ShortenedUrl;
use Illuminate\Http\Request;

class ShortenUrlController extends Controller
{
    public function redirectToOriginal($shortCode)
    {
        // Find the short URL in the database
        $shortenedUrl = ShortenedUrl::where('short_code', $shortCode)->first();

        // If found, redirect to the original URL
        if ($shortenedUrl) {
            return redirect()->away($shortenedUrl->original_url);
        }

        // If not found, return 404 response
        return abort(404, 'Shortened URL not found.');
    }
}
