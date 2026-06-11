<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Load .env for local development
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2)) + [1 => ''];
        if ($k && getenv($k) === false) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
}

$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$subject = trim(strip_tags($_POST['subject'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

if (!$name || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$apiKey = getenv('RESEND_API_KEY');
$to     = getenv('SMTP_TO') ?: 'charleschukwudichukwudi@gmail.com';

if (!$apiKey) {
    error_log('RESEND_API_KEY not configured.');
    echo json_encode(['success' => false, 'message' => 'Mail service not configured. Please reach out via WhatsApp or email directly.']);
    exit;
}

$html = "
    <div style='font-family:sans-serif;max-width:520px'>
      <h3 style='color:#38bdf8;margin-bottom:16px'>New Portfolio Message</h3>
      <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
      <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
      <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
      <hr style='border-color:#1e3a5f;margin:16px 0'>
      <p>" . nl2br(htmlspecialchars($message)) . "</p>
    </div>
";

$payload = json_encode([
    'from'     => 'Portfolio Contact <onboarding@resend.dev>',
    'to'       => [$to],
    'reply_to' => "$name <$email>",
    'subject'  => "Portfolio Contact: $subject",
    'html'     => $html,
]);

$ch = curl_init('https://api.resend.com/emails');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => $payload,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    error_log('Resend cURL error: ' . $curlError);
    echo json_encode(['success' => false, 'message' => 'Could not send message. Please try WhatsApp or email directly.']);
    exit;
}

if ($httpCode === 200 || $httpCode === 201) {
    echo json_encode(['success' => true, 'message' => "Thanks $name! I'll get back to you shortly."]);
} else {
    error_log('Resend API error (' . $httpCode . '): ' . $response);
    echo json_encode(['success' => false, 'message' => 'Could not send message. Please try WhatsApp or email directly.']);
}
