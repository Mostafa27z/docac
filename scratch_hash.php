<?php

$securityKey = 'secretkey123';
$path = '/courses/attachments/course_5/1787714976_dan-koe-how-to-fix-your-entire-life-in-1-day.pdf';
$expires = 1787732853;

// 1. Basic Token Auth (MD5)
// Hash format: SecurityKey + Path + ExpirationTime
$hashable = $securityKey . $path . $expires;
$token = base64_encode(md5($hashable, true));
$token = str_replace(['+', '/', '='], ['-', '_', ''], $token);
echo "Basic MD5 Token: " . $token . "\n";

// 2. Advanced Token Auth (HMAC-SHA256)
// Signature format: Path + ExpirationTime + SecurityKey
$hashable_adv = $path . $expires;
$token_adv = base64_encode(hash_hmac('sha256', $hashable_adv, $securityKey, true));
$token_adv = str_replace(['+', '/', '='], ['-', '_', ''], $token_adv);
echo "Advanced HMAC Token (Path + Expires): " . $token_adv . "\n";

// Other variations of Advanced Token Auth signature formats
// Format: SecurityKey + Path + ExpirationTime
$token_adv2 = base64_encode(hash_hmac('sha256', $securityKey . $path . $expires, $securityKey, true));
$token_adv2 = str_replace(['+', '/', '='], ['-', '_', ''], $token_adv2);
echo "Advanced HMAC Token (Key + Path + Expires): " . $token_adv2 . "\n";
