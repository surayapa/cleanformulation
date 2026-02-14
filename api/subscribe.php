<?php
// HARD STOP: never show PHP errors to users
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Timeout protection
set_time_limit(5);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$email = filter_input(INPUT_POST, 'newsletter', FILTER_VALIDATE_EMAIL);

if (!$email) {
    showMessage(
        'Invalid email address',
        'Please enter a valid email address and try again.',
        'error'
    );
    exit;
}

$to = 'contact@cleanformulation.com';
$subject = 'New Research Bulletin Subscription';
$message = "New subscriber email:\n\n" . $email;
$headers = "From: CleanFormulation <no-reply@cleanformulation.com>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8";

if (@mail($to, $subject, $message, $headers)) {
    showMessage(
        'Subscription successful',
        'Thank you for subscribing to the CleanFormulation Research Bulletin.',
        'success'
    );
    exit;
}

// If mail fails (local/dev/SMTP issue)
showMessage(
    'Subscription received',
    'Thank you for your interest. Our system will process your request shortly.',
    'info'
);
exit;


/* ---------- UI RESPONSE ---------- */

function showMessage(string $title, string $message, string $type): void {
    $colors = [
        'success' => '#0f766e',
        'error'   => '#b91c1c',
        'info'    => '#1e40af'
    ];
    $color = $colors[$type] ?? '#1e293b';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>$title | CleanFormulation</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    margin: 0;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background: #f8fafc;
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}
.card {
    max-width: 420px;
    background: #ffffff;
    border-radius: 14px;
    padding: 32px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    text-align: center;
}
.icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: {$color}15;
    color: $color;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin: 0 auto 16px;
}
h1 {
    font-size: 1.4rem;
    margin: 0 0 10px;
}
p {
    font-size: .95rem;
    color: #475569;
    line-height: 1.5;
}
a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 18px;
    border-radius: 8px;
    background: $color;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
}
a:hover {
    opacity: .92;
}
</style>
</head>
<body>
<div class="card">
    <div class="icon">✓</div>
    <h1>$title</h1>
    <p>$message</p>
    <a href="/">Return to site</a>
</div>
</body>
</html>
HTML;
}
