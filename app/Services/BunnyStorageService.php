<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class BunnyStorageService
{
    protected string $apiKey;
    protected string $streamApiKey;
    protected string $storageZone;
    protected string $libraryId;

    public function __construct()
    {
        $this->apiKey = config('services.bunny.api_key', '');
        $this->streamApiKey = config('services.bunny.stream_api_key', $this->apiKey);
        $this->storageZone = config('services.bunny.storage_zone', '');
        $this->libraryId = config('services.bunny.stream_library_id', '');
    }

    /**
     * Upload course document/PDF files directly to Bunny Storage
     */
    public function uploadFile(UploadedFile $file, string $destinationPath): ?string
    {
        \Illuminate\Support\Facades\Log::info("Bunny Storage: Preparing upload file.", [
            'destination_path' => $destinationPath,
            'file_name' => $file->getClientOriginalName(),
            'has_api_key' => !empty($this->apiKey),
            'storage_zone' => $this->storageZone
        ]);

        if (empty($this->apiKey) || empty($this->storageZone)) {
            \Illuminate\Support\Facades\Log::error("Bunny Storage: Missing API key or Storage Zone.");
            return null;
        }

        $extension = $file->getClientOriginalExtension();
        $safeBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        if (empty($safeBaseName)) {
            $safeBaseName = 'file';
        }
        $fileName = time() . '_' . $safeBaseName . '.' . strtolower($extension);

        $targetUrl = "https://storage.bunnycdn.com/{$this->storageZone}/" . ltrim($destinationPath, '/') . '/' . $fileName;

        try {
            $relativePath = ltrim($destinationPath, '/') . '/' . $fileName;

            $localDestinations = [
                storage_path('app/public/' . $relativePath),
                public_path('storage/' . $relativePath),
                public_path($relativePath),
            ];

            if (file_exists(base_path('../public_html'))) {
                $localDestinations[] = base_path('../public_html/storage/' . $relativePath);
                $localDestinations[] = base_path('../public_html/' . $relativePath);
            }
            if (file_exists(base_path('public_html'))) {
                $localDestinations[] = base_path('public_html/storage/' . $relativePath);
                $localDestinations[] = base_path('public_html/' . $relativePath);
            }

            foreach ($localDestinations as $destPath) {
                $dir = dirname($destPath);
                if (!file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
                @copy($file->getRealPath(), $destPath);
            }

            // 3. Upload to Bunny Storage cloud (bypassed for attachment files)
            if (str_starts_with(ltrim($destinationPath, '/'), 'courses/attachments')) {
                \Illuminate\Support\Facades\Log::info("Bunny Storage: Bypassing Bunny Storage upload for attachment file.", [
                    'relative_path' => $relativePath
                ]);
                return $relativePath;
            }

            $response = Http::withOptions(['verify' => false])->withHeaders([
                'AccessKey' => $this->apiKey,
            ])->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
              ->put($targetUrl);

            \Illuminate\Support\Facades\Log::info("Bunny Storage: API response status: " . $response->status(), [
                'body' => $response->body()
            ]);

            return $relativePath;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Bunny Storage: Upload exception: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null;
    }

    /**
     * Step 1: Create a video entry on Bunny Stream returning GUID
     */
    public function createStreamVideo(string $title): ?string
    {
        \Illuminate\Support\Facades\Log::info("Bunny Stream: Attempting to create video entry.", [
            'library_id' => $this->libraryId,
            'title' => $title,
            'has_api_key' => !empty($this->streamApiKey)
        ]);

        if (empty($this->streamApiKey) || empty($this->libraryId)) {
            \Illuminate\Support\Facades\Log::error("Bunny Stream: Missing streamApiKey or libraryId in configuration.");
            return null;
        }

        try {
            $createUrl = "https://video.bunnycdn.com/library/{$this->libraryId}/videos";
            $createResponse = Http::withOptions(['verify' => false])->timeout(60)->connectTimeout(30)->withHeaders([
                'AccessKey' => trim($this->streamApiKey),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post($createUrl, ['title' => $title]);

            \Illuminate\Support\Facades\Log::info("Bunny Stream: Create Video API response status: " . $createResponse->status(), [
                'body' => $createResponse->body()
            ]);

            if ($createResponse->successful()) {
                $guid = $createResponse->json('guid');
                \Illuminate\Support\Facades\Log::info("Bunny Stream: Video created successfully. GUID: {$guid}");
                return $guid;
            }

            \Illuminate\Support\Facades\Log::error("Bunny Stream: API call failed with status {$createResponse->status()}", [
                'response' => $createResponse->body()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bunny Stream create exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null;
    }

    /**
     * Step 2: Append/upload a binary chunk directly to Bunny Stream video GUID
     */
    public function uploadVideoChunk(string $videoGuid, string $binaryContent): bool
    {
        if (empty($this->streamApiKey) || empty($this->libraryId)) {
            \Illuminate\Support\Facades\Log::error("Bunny Stream Chunk: Missing streamApiKey or libraryId.");
            return false;
        }

        try {
            $uploadUrl = "https://video.bunnycdn.com/library/{$this->libraryId}/videos/{$videoGuid}";
            
            $uploadResponse = Http::withOptions(['verify' => false])->timeout(120)->connectTimeout(30)->withHeaders([
                'AccessKey' => $this->streamApiKey,
            ])->withBody($binaryContent, 'application/octet-stream')
              ->put($uploadUrl);

            \Illuminate\Support\Facades\Log::info("Bunny Stream Chunk Upload response status: " . $uploadResponse->status());

            if (!$uploadResponse->successful()) {
                \Illuminate\Support\Facades\Log::error("Bunny Stream Chunk upload failed.", [
                    'body' => $uploadResponse->body()
                ]);
            }

            return $uploadResponse->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bunny Stream chunk upload exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Push video files to Bunny Stream Library returning the video GUID.
     */
    public function uploadVideo(UploadedFile $file, string $title): ?string
    {
        $videoGuid = $this->createStreamVideo($title);
        if (!$videoGuid) {
            return null;
        }

        $success = $this->uploadVideoChunk($videoGuid, file_get_contents($file->getRealPath()));
        return $success ? $videoGuid : null;
    }
}
