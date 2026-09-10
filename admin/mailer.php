<?php

function lp_smtp_read($socket): string
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (preg_match('/^\d{3} /', $line)) {
            break;
        }
    }
    return $response;
}

function lp_smtp_expect($socket, array $acceptedCodes): bool
{
    $response = lp_smtp_read($socket);
    $code = (int)substr(trim($response), 0, 3);
    return in_array($code, $acceptedCodes, true);
}

function lp_smtp_write($socket, string $command): void
{
    fwrite($socket, $command . "\r\n");
}

function lp_email_field(string $label, string $value): string
{
    return '<tr>'
        . '<td style="width:34%;padding:13px 16px;border-bottom:1px solid #eceff1;color:#686b72;font-size:13px;font-weight:700;vertical-align:top;">'
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        . '</td>'
        . '<td style="padding:13px 16px;border-bottom:1px solid #eceff1;color:#24262c;font-size:14px;line-height:1.5;vertical-align:top;">'
        . $value
        . '</td>'
        . '</tr>';
}

function lp_email_template(string $title, string $content): string
{
    return '<!doctype html><html><body style="margin:0;padding:24px;background:#f3f5f7;font-family:Arial,Helvetica,sans-serif;color:#24262c;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e3e7ea;border-radius:12px;overflow:hidden;">'
        . '<tr><td style="padding:22px 28px;background:#24262c;border-bottom:5px solid #f05a00;">'
        . '<div style="color:#ffffff;font-size:20px;font-weight:700;">Link Promotions and Exhibits</div>'
        . '<div style="margin-top:5px;color:#cbd0d5;font-size:12px;">New website enquiry</div>'
        . '</td></tr>'
        . '<tr><td style="padding:28px;">'
        . '<h1 style="margin:0 0 22px;color:#686b72;font-size:24px;line-height:1.25;">'
        . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
        . '</h1>'
        . $content
        . '<p style="margin:24px 0 0;color:#8a8f95;font-size:12px;line-height:1.5;">This enquiry was submitted through the Link Promotions and Exhibits website.</p>'
        . '</td></tr>'
        . '<tr><td style="padding:16px 28px;background:#fff7f1;color:#686b72;font-size:12px;">Link Promotions and Exhibits Worldwide</td></tr>'
        . '</table></body></html>';
}

function lp_smtp_clean_header(string $value): string
{
    return trim(str_replace(["\r", "\n"], '', $value));
}

function lp_send_smtp_email(string $subject, string $htmlBody, ?string $replyTo = null): bool
{
    $config = require __DIR__ . '/mail-config.php';
    $username = trim((string)($config['username'] ?? ''));
    $password = (string)($config['password'] ?? '');
    $fromEmail = trim((string)($config['from_email'] ?? '')) ?: $username;
    $toEmail = trim((string)($config['to_email'] ?? ''));

    if ($username === '' || $password === '' || $fromEmail === '' || $toEmail === '') {
        error_log('LP SMTP email skipped: Gmail SMTP credentials or recipient email are not configured.');
        return false;
    }

    $host = (string)($config['host'] ?? 'smtp.gmail.com');
    $port = (int)($config['port'] ?? 587);
    $context = stream_context_create();
    $socket = @stream_socket_client(
        'tcp://' . $host . ':' . $port,
        $errorNumber,
        $errorMessage,
        20,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        error_log('LP SMTP connection failed: ' . $errorMessage);
        return false;
    }

    stream_set_timeout($socket, 20);
    $success = lp_smtp_expect($socket, [220]);

    if ($success) {
        lp_smtp_write($socket, 'EHLO localhost');
        $success = lp_smtp_expect($socket, [250]);
    }

    if ($success) {
        lp_smtp_write($socket, 'STARTTLS');
        $success = lp_smtp_expect($socket, [220]);
    }

    if ($success) {
        $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }
        $success = (bool)stream_socket_enable_crypto(
            $socket,
            true,
            $cryptoMethod
        );
    }

    if ($success) {
        lp_smtp_write($socket, 'EHLO localhost');
        $success = lp_smtp_expect($socket, [250]);
    }

    if ($success) {
        lp_smtp_write($socket, 'AUTH LOGIN');
        $success = lp_smtp_expect($socket, [334]);
    }

    if ($success) {
        lp_smtp_write($socket, base64_encode($username));
        $success = lp_smtp_expect($socket, [334]);
    }

    if ($success) {
        lp_smtp_write($socket, base64_encode($password));
        $success = lp_smtp_expect($socket, [235]);
    }

    if ($success) {
        lp_smtp_write($socket, 'MAIL FROM:<' . lp_smtp_clean_header($fromEmail) . '>');
        $success = lp_smtp_expect($socket, [250]);
    }

    if ($success) {
        lp_smtp_write($socket, 'RCPT TO:<' . lp_smtp_clean_header($toEmail) . '>');
        $success = lp_smtp_expect($socket, [250, 251]);
    }

    if ($success) {
        lp_smtp_write($socket, 'DATA');
        $success = lp_smtp_expect($socket, [354]);
    }

    if ($success) {
        $htmlBody = lp_email_template($subject, $htmlBody);
        $fromName = lp_smtp_clean_header((string)($config['from_name'] ?? 'Link Promotions and Exhibits'));
        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $fromName . ' <' . lp_smtp_clean_header($fromEmail) . '>',
            'To: <' . lp_smtp_clean_header($toEmail) . '>',
            'Subject: ' . lp_smtp_clean_header($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . lp_smtp_clean_header($replyTo);
        }

        $body = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody;
        $body = preg_replace('/^\./m', '..', $body);
        fwrite($socket, $body . "\r\n.\r\n");
        $success = lp_smtp_expect($socket, [250]);
    }

    lp_smtp_write($socket, 'QUIT');
    fclose($socket);

    if (!$success) {
        error_log('LP SMTP email delivery failed.');
    }

    return $success;
}
