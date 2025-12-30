<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ShortenedUrl;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ShortenUrlController extends Controller
{
    use ApiResponse;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'original_url' => 'required|url|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse('Validation failed', $validator->errors());
            }

            $user = $request->user();
            $originalUrl = $request->original_url;

            // Check for duplicate URL for the same user
            $existingUrl = $user->shortenedUrls()->where('original_url', $originalUrl)->first();
            if ($existingUrl) {
                return $this->successResponse('URL already shortened', [
                    'short_code' => $existingUrl->short_code,
                    'original_url' => $existingUrl->original_url,
                    'shortened_url' => $this->generateShortUrl($existingUrl->short_code),
                    'created_at' => $existingUrl->created_at,
                ]);
            }

            // Generate unique short code
            $shortCode = $this->generateUniqueShortCode();

            // Create shortened URL
            $shortenedUrl = $user->shortenedUrls()->create([
                'original_url' => $originalUrl,
                'short_code' => $shortCode,
            ]);

            return $this->successResponse('URL shortened successfully', [
                'short_code' => $shortenedUrl->short_code,
                'original_url' => $shortenedUrl->original_url,
                'shortened_url' => $this->generateShortUrl($shortenedUrl->short_code),
                'created_at' => $shortenedUrl->created_at,
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to shorten URL', 500);
        }
    }

    /**
     * Redirect from short URL to original URL
     */
    public function redirect(string $shortCode)
    {
        try {
            $url = ShortenedUrl::where('short_code', $shortCode)->first();

            if (!$url) {
                return $this->errorResponse('Invalid short code', 404, [
                    'short_code' => $shortCode
                ]);
            }

            return redirect($url->original_url);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to redirect URL', 500);
        }
    }

    /**
     * Generate a unique short code
     */
    private function generateUniqueShortCode(): string
    {
        do {
            $shortCode = Str::random(6);
        } while (ShortenedUrl::where('short_code', $shortCode)->exists());

        return $shortCode;
    }

    /**
     * Generate the full shortened URL
     */
    private function generateShortUrl(string $shortCode): string
    {
        return config('app.url') . '/s/' . $shortCode;
    }
}
