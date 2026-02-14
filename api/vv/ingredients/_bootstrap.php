<?php
// ============================================================
// CleanFormulation API Bootstrap
// Shared headers + rate limiting
// ============================================================

// ------------------------------------------------------------
// Basic API headers
// ------------------------------------------------------------
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');

// Disable caching at all layers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// ------------------------------------------------------------
// Simple IP-based rate limiting
// ------------------------------------------------------------

$limit  = 120;      // requests
$window = 3600;     // seconds

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Hash IP (avoid raw IP storage)
$hash = hash('sha256', $ip);

// Temp storage (shared-safe)
$store = sys_get_temp_dir() . '/cf_rate_' . $hash;

$now = time();

// Initialize bucket
$bucket = [
    'count' => 0,
    'start' => $now
];

// Load bucket if exists
if (is_file($store)) {
    $loaded = json_decode(file_get_contents($store), true);
    if (
        is_array($loaded) &&
        isset($loaded['count'], $loaded['start'])
    ) {
        $bucket = $loaded;
    }
}

// Reset window if expired
if (($now - $bucket['start']) >= $window) {
    $bucket = [
        'count' => 0,
        'start' => $now
    ];
}

// Increment count
$bucket['count']++;

// Persist bucket
file_put_contents($store, json_encode($bucket), LOCK_EX);

// Rate limit headers
$remaining = max(0, $limit - $bucket['count']);

header("X-RateLimit-Limit: {$limit}");
header("X-RateLimit-Remaining: {$remaining}");
header("X-RateLimit-Reset: " . ($bucket['start'] + $window));

// Enforce limit
if ($bucket['count'] > $limit) {
    http_response_code(429);
    echo json_encode([
        'error' => 'Rate limit exceeded',
        'retry_after' => ($bucket['start'] + $window) - $now
    ]);
    exit;
}

// ------------------------------------------------------------
// Bootstrap complete — execution continues
// ------------------------------------------------------------
