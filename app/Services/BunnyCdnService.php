<?php

namespace App\Services;

class BunnyCdnService
{
    protected string $cdnUrl;
    protected string $securityKey;

    public function __construct()
    {
        $this->cdnUrl = rtrim(config('services.bunny.cdn_url', env('BUNNY_CDN_URL', '')), '/');
        $this->securityKey = config('services.bunny.security_key', env('BUNNY_SECURITY_KEY', ''));
    }

    /**
     * Generate secure token URLs for private downloads and video playback.
     * Implementation of BunnyCDN Token Authentication algorithm.
     */
    public function generateSignedUrl(string $path, int $expirationSeconds = 3600): string
    {
        if (empty($this->securityKey)) {
            return $this->cdnUrl . '/' . ltrim($path, '/');
        }

        $path = '/' . ltrim($path, '/');
        $expires = time() + $expirationSeconds;
        
        // Hash format: SecurityKey + Path + ExpirationTime
        $hashable = $this->securityKey . $path . $expires;
        $token = base64_encode(md5($hashable, true));
        $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

        return $this->cdnUrl . $path . '?token=' . $token . '&expires=' . $expires;
    }
}
