<?php
/**
 * ارسال ایمیل ساده برای Hamrahang (تأیید اکانت و …)
 * در صورت نیاز بعداً به SMTP واقعی وصل شود.
 */
if (!defined('HA_BOOT')) {
    http_response_code(403);
    exit;
}

/**
 * @param string $to
 * @param string $subject
 * @param string $htmlBody
 * @param string $textBody optional plain text
 * @return bool
 */
function HA_SendMail($to, $subject, $htmlBody, $textBody = '') {
    $to = trim((string)$to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $fromEmail = 'noreply@hamrahang.ir';
    $fromName  = 'Hamrahang';

    $boundary = 'ha_' . md5(uniqid((string)mt_rand(), true));

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'From: ' . sprintf('%s <%s>', $fromName, $fromEmail);
    $headers[] = 'Reply-To: ' . $fromEmail;
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
    $headers[] = 'X-Mailer: Hamrahang-Mail';

    if ($textBody === '') {
        $textBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
    }

    $body  = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($textBody)) . "\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";
    $body .= "--{$boundary}--\r\n";

    // -f برای Return-Path روی خیلی از هاست‌ها لازم است
    $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers), '-f' . $fromEmail);
    return (bool)$ok;
}

/**
 * ایمیل تأیید ثبت‌نام
 */
function HA_SendVerifyEmail($to, $token, $baseUrl = 'https://hamrahang.ir') {
    $baseUrl = rtrim((string)$baseUrl, '/');
    $link = $baseUrl . '/verify/' . rawurlencode($token);

    $subject = 'تأیید ایمیل — همراهنگ';
    $html = '<div style="font-family:Tahoma,Arial,sans-serif;line-height:1.7;color:#222">'
        . '<p>سلام،</p>'
        . '<p>برای فعال‌سازی حساب همراهنگ روی لینک زیر بزنید:</p>'
        . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</a></p>'
        . '<p style="color:#666;font-size:13px">اگر شما این درخواست را نداده‌اید، این پیام را نادیده بگیرید.</p>'
        . '<p>همراهنگ</p></div>';

    $text = "تأیید ایمیل همراهنگ\n\nلینک فعال‌سازی:\n{$link}\n";

    return HA_SendMail($to, $subject, $html, $text);
}
