<?php

$path = '/courses/attachments/course_5/1787714976_dan-koe-how-to-fix-your-entire-life-in-1-day.pdf';
$cdnUrl = 'https://vz-13215fb9-18b.b-cdn.net';
$expires = time() + 3600;

$keys = [
    'secretkey123',
    '75bdf0db-9139-4e7c-b7351aa545ae-9306-4737',
    'c7b8aee8-eadf-43d6-aa83d1e41cdd-c4ef-4beb',
    'docac-storage'
];

foreach ($keys as $key) {
    // 1. Basic Token Auth (MD5)
    $hashable = $key . $path . $expires;
    $token = base64_encode(md5($hashable, true));
    $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);
    $url = $cdnUrl . $path . '?token=' . $token . '&expires=' . $expires;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Key: $key (MD5) -> HTTP Status: $status\n";

    // 2. Advanced Token Auth (HMAC-SHA256)
    $hashable_adv = $path . $expires;
    $token_adv = base64_encode(hash_hmac('sha256', $hashable_adv, $key, true));
    $token_adv = str_replace(['+', '/', '='], ['-', '_', ''], $token_adv);
    $url_adv = $cdnUrl . $path . '?token=' . $token_adv . '&expires=' . $expires;
    
    $ch = curl_init($url_adv);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_exec($ch);
    $status_adv = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Key: $key (HMAC) -> HTTP Status: $status_adv\n";
}
