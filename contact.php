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

$safeName    = htmlspecialchars($name);
$safeEmail   = htmlspecialchars($email);
$safeSubject = htmlspecialchars($subject);
$safeMessage = nl2br(htmlspecialchars($message));
$replyLink   = 'mailto:' . rawurlencode($email) . '?subject=' . rawurlencode('Re: ' . $subject);
$date        = date('F j, Y \a\t g:i A');

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Portfolio Message</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#050b18 0%,#0d1f3c 100%);border-radius:16px 16px 0 0;padding:36px 40px 28px;text-align:center;">
              <p style="margin:0 0 8px;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#38bdf8;font-weight:600;">Portfolio Notification</p>
              <h1 style="margin:0;font-size:26px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">New Message Received</h1>
              <p style="margin:10px 0 0;font-size:13px;color:#64748b;">{$date}</p>
            </td>
          </tr>

          <!-- Accent bar -->
          <tr>
            <td style="height:4px;background:linear-gradient(90deg,#38bdf8,#818cf8,#f472b6);"></td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="background:#ffffff;padding:36px 40px;">

              <!-- Sender card -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                <tr>
                  <td style="padding:24px 28px;">
                    <p style="margin:0 0 4px;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#94a3b8;font-weight:600;">From</p>

                    <!-- Avatar + name row -->
                    <table cellpadding="0" cellspacing="0" border="0" style="margin-top:12px;">
                      <tr>
                        <td style="width:46px;vertical-align:middle;">
                          <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#38bdf8,#818cf8);display:inline-block;text-align:center;line-height:42px;font-size:18px;font-weight:700;color:#ffffff;">
                            {$safeName[0]}
                          </div>
                        </td>
                        <td style="vertical-align:middle;padding-left:14px;">
                          <p style="margin:0;font-size:17px;font-weight:700;color:#0f172a;">{$safeName}</p>
                          <p style="margin:3px 0 0;font-size:13px;color:#38bdf8;">{$safeEmail}</p>
                        </td>
                      </tr>
                    </table>

                    <!-- Subject -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;border-top:1px solid #e2e8f0;padding-top:16px;">
                      <tr>
                        <td>
                          <p style="margin:0 0 4px;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#94a3b8;font-weight:600;">Subject</p>
                          <p style="margin:6px 0 0;font-size:15px;font-weight:600;color:#1e293b;">{$safeSubject}</p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Message body -->
              <p style="margin:0 0 10px;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#94a3b8;font-weight:600;">Message</p>
              <div style="background:#f8fafc;border-left:4px solid #38bdf8;border-radius:0 8px 8px 0;padding:20px 24px;margin-bottom:32px;">
                <p style="margin:0;font-size:15px;line-height:1.8;color:#334155;">{$safeMessage}</p>
              </div>

              <!-- Reply CTA -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center">
                    <a href="{$replyLink}" style="display:inline-block;background:linear-gradient(135deg,#38bdf8,#818cf8);color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 40px;border-radius:8px;letter-spacing:0.3px;">
                      Reply to {$safeName}
                    </a>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#0d1f3c;border-radius:0 0 16px 16px;padding:24px 40px;text-align:center;">
              <p style="margin:0 0 6px;font-size:14px;font-weight:700;color:#ffffff;">Chukwudi Charles</p>
              <p style="margin:0;font-size:12px;color:#475569;">Full-Stack Developer &bull; Portfolio Contact Form</p>
              <p style="margin:14px 0 0;font-size:11px;color:#334155;">This email was sent via your portfolio contact form at chukstech.onrender.com</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
HTML;

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
