<?php

namespace App\Services;

class BunnyCdnService
{
    protected string $cdnUrl;
    protected string $securityKey;
    protected string $streamLibraryId;

    public function __construct()
    {
        $this->cdnUrl = rtrim(config('services.bunny.cdn_url', env('BUNNY_CDN_URL', '')), '/');
        $this->securityKey = config('services.bunny.security_key', env('BUNNY_SECURITY_KEY', ''));
        $this->streamLibraryId = config('services.bunny.stream_library_id', env('BUNNY_STREAM_LIBRARY_ID', ''));
    }

    /**
     * Check if a video_url value is a Bunny Stream GUID (UUID format).
     */
    public function isBunnyStreamGuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    /**
     * Build the full playback URL for a video.
     * - If the value is a Bunny Stream GUID → returns the iframe embed URL.
     * - If the value is a file path → returns a CDN signed URL.
     */
    public function buildVideoPlaybackUrl(?string $videoUrl): ?string
    {
        if (empty($videoUrl)) {
            return null;
        }

        if ($this->isBunnyStreamGuid($videoUrl)) {
            return "https://iframe.mediadelivery.net/embed/{$this->streamLibraryId}/{$videoUrl}?autoplay=false&preload=true&responsive=true";
        }

        return $this->generateSignedUrl($videoUrl);
    }

    /**
     * Build a direct HLS stream URL for native video players (Bunny Stream only).
     */
    public function buildStreamDirectUrl(?string $videoUrl): ?string
    {
        if (empty($videoUrl) || !$this->isBunnyStreamGuid($videoUrl)) {
            return null;
        }

        return "https://iframe.mediadelivery.net/play/{$this->streamLibraryId}/{$videoUrl}";
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
