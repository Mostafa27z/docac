<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = public_path('docacademyy-2bb9b-firebase-adminsdk-fbsvc-a1463378a6.json');
    }

    /**
     * Get OAuth2 Access Token from Google using JWT bearer assertion (RS256).
     */
    protected function getAccessToken(): ?string
    {
        if (!file_exists($this->credentialsPath)) {
            Log::error("Firebase credential file not found at: {$this->credentialsPath}");
            return null;
        }

        $credentials = json_decode(file_get_contents($this->credentialsPath), true);
        if (!$credentials || !isset($credentials['private_key'], $credentials['client_email'])) {
            Log::error("Invalid Firebase credentials JSON format.");
            return null;
        }

        $privateKey = $credentials['private_key'];
        $clientEmail = $credentials['client_email'];

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        $success = openssl_sign(
            $base64UrlHeader . "." . $base64UrlPayload,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );

        if (!$success) {
            Log::error("Failed to sign JWT for Google OAuth2 authentication.");
            return null;
        }

        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        if ($response->failed()) {
            Log::error("Google OAuth2 token request failed: " . $response->body());
            return null;
        }

        return $response->json('access_token');
    }

    /**
     * Send push notification to multiple device tokens via FCM V1 HTTP API.
     */
    public function sendNotificationToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($tokens)) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        if (!file_exists($this->credentialsPath)) {
            return false;
        }
        $credentials = json_decode(file_get_contents($this->credentialsPath), true);
        $projectId = $credentials['project_id'] ?? 'docacademyy-2bb9b';

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $successCount = 0;

        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ]
                ]
            ];

            if (!empty($data)) {
                // Ensure all values in data payload are strings as required by FCM
                $stringData = array_map('strval', $data);
                $payload['message']['data'] = $stringData;
            }

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                $successCount++;
            } else {
                Log::warning("FCM V1 send failed for token: {$token}. Response: " . $response->body());
            }
        }

        return $successCount > 0;
    }
}
