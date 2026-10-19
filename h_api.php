<?php

    if (!defined('HA_BOOT')) {
        http_response_code(403);
        exit;
    }

    // request
    $Page   = isset($_REQUEST['p'])     ? strtolower((string)$_REQUEST['p']) : '';
    $QJS    = isset($_REQUEST['_qjs'])  ? (string)$_REQUEST['_qjs']          : '';
    $Q      = isset($_REQUEST['q'])     ? $_REQUEST['q']                     : '';
    $Q1     = isset($_REQUEST['q1'])    ? $_REQUEST['q1']                    : '';
    $Q2     = isset($_REQUEST['q2'])    ? $_REQUEST['q2']                    : '';
    $SQ     = isset($_REQUEST['sq'])    ? $_REQUEST['sq']                    : 'result';


    $TLGRM_Param = isset($_GET["tgWebAppStartParam"]) ? $_GET["tgWebAppStartParam"] : '';//tgWebAppData
    $TLGRM_TOKEN = '8195304633:AAErtQNWATO0AM3KlAqJdMmFgShvo_McRrg';
    $IDHM = 11210700;

    /**
     * SMTP config — fill host/user/pass on the server for real delivery.
     * If enabled=false or host empty → falls back to PHP mail().
     */
    $HA_SMTP = [
        'enabled'   => true,
        'host'      => 'mail.hamrahang.ir',
        'port'      => 587,
        'user'      => '',
        'pass'      => '',
        'secure'    => 'tls', // tls | ssl | ''
        'from'      => 'noreply@hamrahang.ir',
        'from_name' => 'Hamrahang',
        'timeout'   => 20,
    ];

    /** Simple IP rate limit (file-based). Returns true if allowed. */
    function HA_RateLimit($bucket, $max = 8, $windowSec = 600) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? preg_replace('/[^0-9a-fA-F:\.]/', '', (string)$_SERVER['REMOTE_ADDR']) : '0';
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ha_rl';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        $file = $dir . DIRECTORY_SEPARATOR . md5($bucket . '|' . $ip) . '.json';
        $now = time();
        $hits = [];
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            $decoded = $raw ? json_decode($raw, true) : null;
            if (is_array($decoded)) {
                foreach ($decoded as $t) {
                    if (is_int($t) && ($now - $t) < $windowSec) $hits[] = $t;
                }
            }
        }
        if (count($hits) >= $max) {
            return false;
        }
        $hits[] = $now;
        @file_put_contents($file, json_encode($hits), LOCK_EX);
        return true;
    }

    function HA_SmtpRead($fp) {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    }

    function HA_SmtpCmd($fp, $cmd, $expectPrefix) {
        if ($cmd !== null) {
            fwrite($fp, $cmd . "\r\n");
        }
        $resp = HA_SmtpRead($fp);
        if ($resp === '' || strpos($resp, (string)$expectPrefix) !== 0) {
            return false;
        }
        return $resp;
    }

    /** Send via SMTP AUTH LOGIN when configured; otherwise PHP mail(). */
    function HA_SendMail($to, $subject, $htmlBody, $textBody = '') {
        global $HA_SMTP;
        $to = trim((string)$to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $fromEmail = !empty($HA_SMTP['from']) ? $HA_SMTP['from'] : 'noreply@hamrahang.ir';
        $fromName  = !empty($HA_SMTP['from_name']) ? $HA_SMTP['from_name'] : 'Hamrahang';
        if ($textBody === '') {
            $textBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
        }

        $boundary = 'ha_' . md5(uniqid((string)mt_rand(), true));
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'From: ' . sprintf('%s <%s>', $fromName, $fromEmail);
        $headers[] = 'Reply-To: ' . $fromEmail;
        $headers[] = 'To: ' . $to;
        $headers[] = 'Subject: ' . $encodedSubject;
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $headers[] = 'X-Mailer: Hamrahang-Mail';

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($textBody)) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";
        $body .= "--{$boundary}--\r\n";

        $useSmtp = !empty($HA_SMTP['enabled']) && !empty($HA_SMTP['host']);
        if ($useSmtp) {
            $host = (string)$HA_SMTP['host'];
            $port = (int)($HA_SMTP['port'] ?? 587);
            $secure = strtolower((string)($HA_SMTP['secure'] ?? 'tls'));
            $timeout = (int)($HA_SMTP['timeout'] ?? 20);
            $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
            $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
            if ($fp) {
                stream_set_timeout($fp, $timeout);
                $ok = true;
                $ok = $ok && HA_SmtpCmd($fp, null, '220');
                $ok = $ok && HA_SmtpCmd($fp, 'EHLO hamrahang.ir', '250');
                if ($ok && $secure === 'tls') {
                    $ok = HA_SmtpCmd($fp, 'STARTTLS', '220');
                    if ($ok) {
                        $ok = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                        if ($ok) {
                            $ok = HA_SmtpCmd($fp, 'EHLO hamrahang.ir', '250') !== false;
                        }
                    }
                }
                $user = (string)($HA_SMTP['user'] ?? '');
                $pass = (string)($HA_SMTP['pass'] ?? '');
                if ($ok && $user !== '') {
                    $ok = HA_SmtpCmd($fp, 'AUTH LOGIN', '334');
                    $ok = $ok && HA_SmtpCmd($fp, base64_encode($user), '334');
                    $ok = $ok && HA_SmtpCmd($fp, base64_encode($pass), '235');
                }
                if ($ok) {
                    $ok = HA_SmtpCmd($fp, 'MAIL FROM:<' . $fromEmail . '>', '250');
                    $ok = $ok && HA_SmtpCmd($fp, 'RCPT TO:<' . $to . '>', '250');
                    $ok = $ok && HA_SmtpCmd($fp, 'DATA', '354');
                    if ($ok) {
                        $msg = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
                        $ok = HA_SmtpCmd($fp, $msg, '250') !== false;
                    }
                }
                @HA_SmtpCmd($fp, 'QUIT', '221');
                fclose($fp);
                if ($ok) return true;
            }
            // fall through to mail() if SMTP failed
        }

        $mailHeaders = [];
        $mailHeaders[] = 'MIME-Version: 1.0';
        $mailHeaders[] = 'From: ' . sprintf('%s <%s>', $fromName, $fromEmail);
        $mailHeaders[] = 'Reply-To: ' . $fromEmail;
        $mailHeaders[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $mailHeaders[] = 'X-Mailer: Hamrahang-Mail';
        return (bool)@mail($to, $encodedSubject, $body, implode("\r\n", $mailHeaders), '-f' . $fromEmail);
    }

    function HA_SendVerifyEmail($to, $token, $baseUrl = 'https://hamrahang.ir') {
        $baseUrl = rtrim((string)$baseUrl, '/');
        $link = $baseUrl . '/verify/' . rawurlencode($token);
        $subject = 'Verify your email — Hamrahang';
        $html = '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.6;color:#222">'
            . '<p>Hello,</p>'
            . '<p>Please verify your Hamrahang account by opening this link:</p>'
            . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</a></p>'
            . '<p style="color:#666;font-size:13px">If you did not create this account, you can ignore this email.</p>'
            . '<p>Hamrahang</p></div>';
        $text = "Verify your email — Hamrahang\n\nOpen this link to activate your account:\n{$link}\n\nIf you did not create this account, ignore this email.\n";
        return HA_SendMail($to, $subject, $html, $text);
    }

    function HA_SendResetEmail($to, $token, $baseUrl = 'https://hamrahang.ir') {
        $baseUrl = rtrim((string)$baseUrl, '/');
        $link = $baseUrl . '/reset/' . rawurlencode($token);
        $subject = 'Reset your password — Hamrahang';
        $html = '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.6;color:#222">'
            . '<p>Hello,</p>'
            . '<p>We received a request to reset your Hamrahang password. Open this link:</p>'
            . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</a></p>'
            . '<p style="color:#666;font-size:13px">This link expires in 1 hour. If you did not request a reset, ignore this email.</p>'
            . '<p>Hamrahang</p></div>';
        $text = "Reset your password — Hamrahang\n\nOpen this link (expires in 1 hour):\n{$link}\n\nIf you did not request this, ignore this email.\n";
        return HA_SendMail($to, $subject, $html, $text);
    }
    
    function ContentTypeToInt($type) {
        static $map = [
            'song'        => 0,
            'podcast'     => 1,
            'musicvideo'  => 2,
            'video'       => 3,
            'album'       => 4,
            'artist'      => 5,
            'playlist'    => 6,
        ];
        $type = strtolower(trim((string)$type));
        return isset($map[$type]) ? $map[$type] : null;
    }
    
    function ContentTypeToString($int) {
        static $map = [
            0 => 'song',
            1 => 'podcast',
            2 => 'musicvideo',
            3 => 'video',
            4 => 'album',
            5 => 'artist',
            6 => 'playlist',
        ];
        $int = (int)$int;
        return isset($map[$int]) ? $map[$int] : null;
    }
    
    function PublicIdToReal($publicId, $IDHM) {
        return (int)$publicId - (int)$IDHM;
    }
    
    function RealIdToPublic($realId, $IDHM) {
        return (int)$realId + (int)$IDHM;
    }
    
    function PlaylistLimitsForUser($conn, $id_user) {
        $act = 1;
        $stmt = $conn->prepare("SELECT act FROM user WHERE id_user = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                $act = (int)$row['act'];
            }
        }
        // act == 1 کاربر عادی؛ غیر ۱ سقف بالاتر
        if ($act === 1) {
            return ['playlists' => 5, 'items' => 20, 'act' => $act];
        }
        return ['playlists' => 50, 'items' => 50, 'act' => $act];
    }
    
    function CountUserPlaylists($conn, $id_user) {
        // فقط پلی‌لیست‌های خود کاربر (1 و 2)؛ 9 ادمین حساب نشود
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS c FROM playlist
            WHERE id_user = ? AND pl_act IN (1, 2)
        ");
        $stmt->bind_param('i', $id_user);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['c'] ?? 0);
    }
    
    function CountPlaylistItems($conn, $realPl) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM playlist_items WHERE id_playlist = ?");
        $stmt->bind_param('i', $realPl);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['c'] ?? 0);
    }
   
    if($Page == "app"){
        if($Q1 == "ping"){
            echo '{
                    "status": "ok",
                    "serverTime": 1710000000,
                    "version": "1.0",
                    "minVersion": 12,
                    "forceUpdate": false,
                    "maintenance": false
                    }';
            exit();
        }
    }
    
    if($QJS=='tg'){

        header('Content-Type: application/json; charset=utf-8');

        $INPUT = file_get_contents("php://input");
        $JSD   = json_decode($INPUT);
        $JSQ   = (is_object($JSD) && isset($JSD->q) && is_object($JSD->q)) ? $JSD->q : null;

        $AUDIO = (is_object($JSQ) && isset($JSQ->audio) && is_array($JSQ->audio)) ? $JSQ->audio : [];

        // ورودی ناقص یا خراب → همان خروجی قدیمی تلگرام، بدون ارور PHP
        if (!$AUDIO) {
            echo '{"ok":"false"}';
            exit();
        }

        $TGID  = isset($JSD->id) ? $JSD->id : 0;
        $A_URL = isset($JSQ->artist_url) ? $JSQ->artist_url : '';
        $S_URL = isset($JSQ->url)        ? $JSQ->url        : '';
        $TMB   = isset($JSQ->thumb)      ? $JSQ->thumb      : '';
        $NAME  = isset($JSQ->name)       ? $JSQ->name       : '';
        $AR    = isset($JSQ->artist)     ? $JSQ->artist     : '';

        $QURL   = $A_URL . '-' . $S_URL;
        $THUMB  = urlencode('https://hamrahang.ir/thumb/' . $TMB . '.jpg');
        $TITLE  = urlencode($NAME);
        $PERFORMER = urlencode($AR);

        // اولین لینکی که مال خود hamrahang هست
        $NUM_AUDIO = 0;
        foreach ($AUDIO as $key => $val) {
            if (is_object($val) && isset($val->url) && strpos($val->url, 'hamrahang') !== false) {
                $NUM_AUDIO = $key;
                break;
            }
        }
        if (!isset($AUDIO[$NUM_AUDIO]) || !is_object($AUDIO[$NUM_AUDIO]) || !isset($AUDIO[$NUM_AUDIO]->url)) {
            echo '{"ok":"false"}';
            exit();
        }

        $QJ = urlencode($AR . ' - ' . $NAME) . '%0A'
            . '[%F0%9F%8E%A7] [Go To Song](https://t.me/HamrahangBot?startapp=' . $QURL . '==ref' . $TGID . ')';

        $URLS = $AUDIO[$NUM_AUDIO]->url;
        $AUD  = urlencode(!empty($AUDIO[$NUM_AUDIO]->file_id) ? $AUDIO[$NUM_AUDIO]->file_id : $URLS);

        // دکمهٔ زیر پیام (فعلاً خاموش)
        // $MRK = '{ "inline_keyboard" : [[{"text" : "Go To Song","url" : "tg://@HamrahangBot"}]]}';
        $MRK = '';

        $urlSend = "https://api.telegram.org/bot{$TLGRM_TOKEN}/sendAudio?performer=$PERFORMER&thumbnail=$THUMB&title=$TITLE&audio=$AUD&chat_id=$TGID&caption=$QJ&reply_markup=$MRK&parse_mode=markdown";

        $result = @file_get_contents($urlSend);
        if ($result === false || $result === '') {
            $result = '{"ok":"false"}';
        }

        $JSR = json_decode($result);

        // ذخیرهٔ file_id برای لینک آهنگ
        if ($JSR && !empty($JSR->ok) && empty($AUDIO[$NUM_AUDIO]->file_id)) {
            $FILE_ID = isset($JSR->result->audio->file_id) ? (string)$JSR->result->audio->file_id : '';
            $LINK    = (string)$URLS;
            if ($FILE_ID !== '' && $LINK !== '') {
                $upd = $conn->prepare("UPDATE link_song SET file_id = ? WHERE song_link = ?");
                if ($upd) {
                    $upd->bind_param('ss', $FILE_ID, $LINK);
                    $upd->execute();
                }
            }
        }

        // لاگ پاسخ تلگرام (اگر ریشه قابل نوشتن نیست، ارور نده)
        $fp = @fopen($_SERVER['DOCUMENT_ROOT'] . "/rText.txt", "wb");
        if ($fp) {
            fwrite($fp, $result);
            fclose($fp);
        }

        echo $result;

        exit();
    }
    

//    echo  'startapp'.$_GET["startapp"].'<br><br>';
//    echo 'st param '.$TLGRM_Param.'<br><br>';
//    echo $_SERVER['REQUEST_URI'];


    // ==========================================
    // Languages  →  ?_qjs=lan
    // ==========================================
    if ($QJS == 'lan') {
        header('Content-Type: application/json; charset=utf-8');
        $list = [];
        $sql = "
            SELECT code, name, data
            FROM language
            WHERE lang_act != 0
            ORDER BY name ASC
        ";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $item = [
                    'code' => $row['code'],
                    'name' => $row['name'],
                ];
                if ($row['data'] !== null && $row['data'] !== '') {
                    $decoded = json_decode($row['data'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $item['data'] = $decoded;
                    } else {
                        $item['data'] = $row['data'];
                    }
                }
                $list[] = $item;
            }
        }
        echo json_encode(['lan' => $list], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // ==========================================
    // Genres  →  ?_qjs=gnr
    // ==========================================
    if ($QJS == 'gnr') {
        header('Content-Type: application/json; charset=utf-8');
        $list = [];
        $sql = "
            SELECT id_genre, slug, name, data
            FROM genre
            WHERE genre_act != 0
            ORDER BY name ASC
        ";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $item = [
                    'id'   => (int)$row['id_genre'],
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                ];
                if ($row['data'] !== null && $row['data'] !== '') {
                    $decoded = json_decode($row['data'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $item['data'] = $decoded;
                    } else {
                        $item['data'] = $row['data'];
                    }
                }
                $list[] = $item;
            }
        }
        echo json_encode(['gnr' => $list], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Owners  →  ?_qjs=own
    // Resolve: ?_qjs=own&ids=11210700
    // Batch:   ?_qjs=own&ids=11210700,11210708
    // Search:  ?_qjs=own&q=grok&min_act=7
    if ($QJS === 'own') {
        global $conn, $IDHM;

        $owners = [];

        // ---- resolve by id(s) — single or list in one param "ids" ----
        $idsParam = isset($_REQUEST['ids']) ? trim((string)$_REQUEST['ids']) : '';

        if ($idsParam !== '' && !isset($_REQUEST['q'])) {
            $parts = preg_split('/[\s,;]+/', $idsParam, -1, PREG_SPLIT_NO_EMPTY);
            $realIds = [];
            foreach ($parts as $p) {
                if (!is_numeric($p)) continue;
                $pub = (int)$p;
                if ($pub <= 0) continue;
                $real = function_exists('PublicIdToReal')
                    ? PublicIdToReal($pub, $IDHM)
                    : max(0, $pub - (int)$IDHM);
                if ($real > 0) $realIds[] = $real;
            }
            $owners = OwnerCardsByRealIds($realIds);
            echo json_encode(['owners' => $owners], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        }

        // ---- search (name / email / numeric id) ----
        $q = isset($_REQUEST['q']) ? trim((string)$_REQUEST['q']) : '';
        $minAct = isset($_REQUEST['min_act']) ? (int)$_REQUEST['min_act'] : 7;
        if ($minAct < 0) $minAct = 0;
        if ($minAct > 9) $minAct = 9;

        if ($q === '') {
            echo json_encode(['owners' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        }

        // pure digits → treat as public id (or raw fallback)
        if (preg_match('/^\d+$/', $q)) {
            $pub = (int)$q;
            $real = function_exists('PublicIdToReal')
                ? PublicIdToReal($pub, $IDHM)
                : max(0, $pub - (int)$IDHM);
            if ($real <= 0 && $pub > 0) $real = $pub;

            $card = OwnerCard($real);
            if ($card && (int)$card['act'] >= $minAct) {
                $owners[] = $card;
            }
            if (!$owners && $pub > 0 && $real !== $pub) {
                $card2 = OwnerCard($pub);
                if ($card2 && (int)$card2['act'] >= $minAct) $owners[] = $card2;
            }
            echo json_encode(['owners' => $owners], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        }

        $like = '%' . $conn->real_escape_string($q) . '%';
        $sql = "
            SELECT id_user, u_name, f_name, l_name, u_email, u_photo, act
            FROM user
            WHERE act >= " . (int)$minAct . "
              AND (
                u_name LIKE '$like'
                OR f_name LIKE '$like'
                OR l_name LIKE '$like'
                OR u_email LIKE '$like'
              )
            ORDER BY act DESC, u_name ASC
            LIMIT 20
        ";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $name = trim((string)($row['u_name'] ?? ''));
                if ($name === '') {
                    $name = trim(
                        trim((string)($row['f_name'] ?? '')) . ' ' .
                        trim((string)($row['l_name'] ?? ''))
                    );
                }
                if ($name === '') $name = trim((string)($row['u_email'] ?? ''));
                if ($name === '') {
                    $name = 'User #' . ((int)$row['id_user'] + (int)$IDHM);
                }
                $owners[] = [
                    'id'    => (int)$row['id_user'] + (int)$IDHM,
                    'name'  => $name,
                    'act'   => (int)$row['act'],
                    'photo' => !empty($row['u_photo']) ? (string)$row['u_photo'] : '',
                ];
            }
        }

        echo json_encode(['owners' => $owners], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
    
    function UserPublicId($realId) {
        global $IDHM;
        return (int)$realId + (int)$IDHM;
    }
    
    
    function IssueSessionForDevice($conn, $id_user, $device_token, $device_type) {

        $refreshToken = bin2hex(random_bytes(48));
        $refreshHash  = hash('sha256', $refreshToken);
        $now = time();
        $ip  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    
        // اگر device_token از قبل باشد → آپدیت، وگرنه insert
        $stmt = $conn->prepare("
            INSERT INTO user_devices
                (device_token, id_user, device_type, refresh_token_hash, ip, created_at, last_active_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                id_user = VALUES(id_user),
                device_type = VALUES(device_type),
                refresh_token_hash = VALUES(refresh_token_hash),
                ip = VALUES(ip),
                last_active_at = VALUES(last_active_at)
        ");
        $stmt->bind_param(
            "sisssii",
            $device_token,
            $id_user,
            $device_type,
            $refreshHash,
            $ip,
            $now,
            $now
        );
        $stmt->execute();
    
        $accessToken = GenerateJWT([
            'sub' => $id_user,
            'did' => $device_token,
            'exp' => $now + 86400
        ]);
    
        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }
    
    if ($QJS == 'ual') {
    
        $INPUT = file_get_contents("php://input");
        $JSD = json_decode($INPUT);
        $ACT = isset($JSD->a) ? $JSD->a : '';
    
        $response = [];
    
        // ---- ورود با یوزرنیم/پسورد ----
        if ($ACT == 'login_password') {
            if (!HA_RateLimit('login_password', 20, 600)) {
                echo json_encode(['error' => 'rate_limited']);
                exit();
            }

            $username    = isset($JSD->username) ? trim($JSD->username) : '';
            $password    = isset($JSD->password) ? $JSD->password : '';
            $deviceToken = isset($JSD->did) ? $JSD->did : '';
            $deviceType  = isset($JSD->device_type) ? $JSD->device_type : 'unknown';
        
            if (empty($username) || empty($password) || empty($deviceToken)) {
                echo json_encode(['error' => 'missing_credentials']);
                exit();
            }
        
            $stmt = $conn->prepare("SELECT * FROM user WHERE u_email = ? OR u_name = ? LIMIT 1");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows === 0) {
                echo json_encode(['error' => 'invalid_credentials']);
                exit();
            }
        
            $row = $result->fetch_assoc();

            $passHash = isset($row['u_pass']) ? (string)$row['u_pass'] : '';
            $passOk = false;
            if ($passHash !== '') {
                // هش مدرن bcrypt/argon
                if (password_verify($password, $passHash)) {
                    $passOk = true;
                }
                // پسورد قدیمی plaintext (قبل از password_hash)
                elseif ($passHash[0] !== '$' && hash_equals($passHash, $password)) {
                    $passOk = true;
                    // ارتقا به هش
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    if ($newHash) {
                        $up = $conn->prepare('UPDATE user SET u_pass = ? WHERE id_user = ?');
                        if ($up) {
                            $uid = (int)$row['id_user'];
                            $up->bind_param('si', $newHash, $uid);
                            $up->execute();
                        }
                    }
                }
            }
            if (!$passOk) {
                echo json_encode(['error' => 'invalid_credentials']);
                exit();
            }

            // act: 0=ایمیل تأییدنشده | 1=عادی | 2–5=تجاری | 6=مسدود | 7–9=ادمین
            $actRaw = $row['act'] ?? null;
            if ($actRaw !== null && $actRaw !== '' && (int)$actRaw === 0) {
                echo json_encode([
                    'error' => 'email_not_verified',
                    'email' => $row['u_email'] ?? ''
                ]);
                exit();
            }
            if ($actRaw !== null && $actRaw !== '' && (int)$actRaw === 6) {
                echo json_encode(['error' => 'account_disabled']);
                exit();
            }
        
            $session = IssueSessionForDevice($conn, $row['id_user'], $deviceToken, $deviceType);
        
            // لیست دستگاه‌های فعال این کاربر
            $devStmt = $conn->prepare("
                SELECT device_token, device_type, device_label, ip, created_at, last_active_at
                FROM user_devices
                WHERE id_user = ?
                ORDER BY last_active_at DESC
            ");
            $devStmt->bind_param("i", $row['id_user']);
            $devStmt->execute();
            $devResult = $devStmt->get_result();
        
            $devicesList = [];
            while ($d = $devResult->fetch_assoc()) {
                $devicesList[] = [
                    'did'          => $d['device_token'],
                    'device_type'  => $d['device_type'],
                    'device_label' => $d['device_label'],
                    'ip'           => $d['ip'],
                    'created_at'   => (int)$d['created_at'],
                    'last_active'  => (int)$d['last_active_at'],
                    'is_current'   => ($d['device_token'] === $deviceToken)
                ];
            }

            // ستون متا در جدول: u_data (نه user_data)
            $metaRaw = $row['u_data'] ?? ($row['user_data'] ?? null);
            $meta = null;
            if (is_string($metaRaw) && $metaRaw !== '') {
                $meta = json_decode($metaRaw);
            }
        
            echo json_encode([
                'access_token'  => $session['access_token'],
                'refresh_token' => $session['refresh_token'],
                'id_user'       => (int)$row['id_user'] + $IDHM,
                'user_name'     => $row['u_name'],
                'first_name'    => $row['f_name'],
                'last_name'     => $row['l_name'],
                'email'         => $row['u_email'],
                'act'           => (int)($actRaw !== null && $actRaw !== '' ? $actRaw : 1),
                'meta'          => $meta,
                'devices'       => $devicesList
            ]);
            exit();
        }

        // ---- ثبت‌نام با ایمیل ----
        if ($ACT == 'register_email') {
            if (!HA_RateLimit('register_email', 5, 3600)) {
                echo json_encode(['error' => 'rate_limited']);
                exit();
            }
            $email    = isset($JSD->email) ? strtolower(trim((string)$JSD->email)) : '';
            $password = isset($JSD->password) ? (string)$JSD->password : '';
            $u_name   = isset($JSD->username) ? trim((string)$JSD->username) : '';

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['error' => 'invalid_email']);
                exit();
            }
            if (strlen($password) < 6) {
                echo json_encode(['error' => 'password_too_short']);
                exit();
            }
            if ($u_name === '') {
                $u_name = preg_replace('/[^a-zA-Z0-9_\.\-]/', '', strstr($email, '@', true) ?: 'user');
                if ($u_name === '') $u_name = 'user' . substr(md5($email), 0, 6);
            }
            if (strlen($u_name) < 3) {
                echo json_encode(['error' => 'invalid_username']);
                exit();
            }

            // ایمیل تکراری
            $chk = $conn->prepare('SELECT id_user, act, u_data FROM user WHERE u_email = ? LIMIT 1');
            $chk->bind_param('s', $email);
            $chk->execute();
            $exist = $chk->get_result()->fetch_assoc();
            if ($exist) {
                if ((int)$exist['act'] === 0) {
                    // حساب تأییدنشده → توکن جدید + ارسال دوباره
                    $token = bin2hex(random_bytes(24));
                    $data = [];
                    if (!empty($exist['u_data'])) {
                        $decoded = json_decode($exist['u_data'], true);
                        if (is_array($decoded)) $data = $decoded;
                    }
                    $data['ev_token'] = hash('sha256', $token);
                    $data['ev_exp']   = time() + 86400;
                    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $upd = $conn->prepare('UPDATE user SET u_data = ?, u_pass = ? WHERE id_user = ?');
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $uid = (int)$exist['id_user'];
                    $upd->bind_param('ssi', $json, $hash, $uid);
                    $upd->execute();
                    $mailOk = HA_SendVerifyEmail($email, $token);
                    echo json_encode([
                        'ok' => true,
                        'pending_verify' => true,
                        'resent' => true,
                        'mail_sent' => $mailOk
                    ]);
                    exit();
                }
                echo json_encode(['error' => 'email_taken']);
                exit();
            }

            // یوزرنیم تکراری → پسوند عددی
            $baseName = $u_name;
            $try = 0;
            while ($try < 20) {
                $cand = $try === 0 ? $baseName : ($baseName . $try);
                $chk2 = $conn->prepare('SELECT id_user FROM user WHERE u_name = ? LIMIT 1');
                $chk2->bind_param('s', $cand);
                $chk2->execute();
                if (!$chk2->get_result()->fetch_assoc()) {
                    $u_name = $cand;
                    break;
                }
                $try++;
            }

            $token = bin2hex(random_bytes(24));
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            $join  = time();
            $u_data = json_encode([
                'join'     => $join,
                'ev_token' => hash('sha256', $token),
                'ev_exp'   => $join + 86400
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $act = 0; // تأییدنشده
            $id_tlg = 0;
            $f_name = '';
            $l_name = '';
            $u_ref  = '';

            $ins = $conn->prepare(
                'INSERT INTO user (id_tlg, u_name, u_pass, f_name, l_name, u_email, u_data, u_ref, act)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            if (!$ins) {
                echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                exit();
            }
            $ins->bind_param(
                'isssssssi',
                $id_tlg, $u_name, $hash, $f_name, $l_name, $email, $u_data, $u_ref, $act
            );
            if (!$ins->execute()) {
                echo json_encode(['error' => 'create_failed', 'detail' => $ins->error]);
                exit();
            }

            $mailOk = HA_SendVerifyEmail($email, $token);
            echo json_encode([
                'ok' => true,
                'pending_verify' => true,
                'mail_sent' => $mailOk,
                'user_name' => $u_name
            ]);
            exit();
        }

        // ---- تأیید ایمیل ----
        if ($ACT == 'verify_email') {
            $token = isset($JSD->token) ? trim((string)$JSD->token) : '';
            if ($token === '' || strlen($token) < 16) {
                echo json_encode(['error' => 'invalid_token']);
                exit();
            }
            $tokenHash = hash('sha256', $token);

            // توکن هش‌شده داخل u_data ذخیره شده
            $like = '%' . $conn->real_escape_string($tokenHash) . '%';
            $sql = "SELECT id_user, act, u_data, u_email FROM user WHERE act = 0 AND u_data LIKE '{$like}' LIMIT 5";
            $res = $conn->query($sql);
            $found = null;
            $data = null;
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $decoded = json_decode($row['u_data'] ?? '', true);
                    if (!is_array($decoded)) continue;
                    if (empty($decoded['ev_token']) || !hash_equals((string)$decoded['ev_token'], $tokenHash)) continue;
                    if (empty($decoded['ev_exp']) || (int)$decoded['ev_exp'] < time()) {
                        echo json_encode(['error' => 'token_expired']);
                        exit();
                    }
                    $found = $row;
                    $data = $decoded;
                    break;
                }
            }

            if (!$found || !$data) {
                echo json_encode(['error' => 'invalid_token']);
                exit();
            }

            unset($data['ev_token'], $data['ev_exp']);
            $data['email_verified_at'] = time();
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $uid = (int)$found['id_user'];
            $act = 1;
            $upd = $conn->prepare('UPDATE user SET act = ?, u_data = ? WHERE id_user = ?');
            $upd->bind_param('isi', $act, $json, $uid);
            $upd->execute();

            echo json_encode([
                'ok' => true,
                'email' => $found['u_email']
            ]);
            exit();
        }

        // ---- ارسال دوباره ایمیل تأیید ----
        if ($ACT == 'resend_verify_email') {
            if (!HA_RateLimit('resend_verify_email', 5, 3600)) {
                echo json_encode(['error' => 'rate_limited']);
                exit();
            }
            $email = isset($JSD->email) ? strtolower(trim((string)$JSD->email)) : '';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['error' => 'invalid_email']);
                exit();
            }

            $stmt = $conn->prepare('SELECT id_user, act, u_data FROM user WHERE u_email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if (!$row) {
                // برای جلوگیری از enumeration همیشه ok بده
                echo json_encode(['ok' => true, 'mail_sent' => true]);
                exit();
            }
            if ((int)$row['act'] !== 0) {
                echo json_encode(['ok' => true, 'already_verified' => true]);
                exit();
            }

            $token = bin2hex(random_bytes(24));
            $data = [];
            if (!empty($row['u_data'])) {
                $decoded = json_decode($row['u_data'], true);
                if (is_array($decoded)) $data = $decoded;
            }
            $data['ev_token'] = hash('sha256', $token);
            $data['ev_exp']   = time() + 86400;
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $uid = (int)$row['id_user'];
            $upd = $conn->prepare('UPDATE user SET u_data = ? WHERE id_user = ?');
            $upd->bind_param('si', $json, $uid);
            $upd->execute();

            $mailOk = HA_SendVerifyEmail($email, $token);
            echo json_encode(['ok' => true, 'mail_sent' => $mailOk]);
            exit();
        }

        // ---- درخواست بازیابی رمز ----
        if ($ACT == 'request_password_reset') {
            if (!HA_RateLimit('request_password_reset', 5, 3600)) {
                echo json_encode(['error' => 'rate_limited']);
                exit();
            }
            $email = isset($JSD->email) ? strtolower(trim((string)$JSD->email)) : '';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['error' => 'invalid_email']);
                exit();
            }
            $stmt = $conn->prepare('SELECT id_user, act, u_data FROM user WHERE u_email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            // همیشه پاسخ یکسان (ضد enumeration)
            if ($row && (int)$row['act'] !== 6) {
                $token = bin2hex(random_bytes(24));
                $data = [];
                if (!empty($row['u_data'])) {
                    $decoded = json_decode($row['u_data'], true);
                    if (is_array($decoded)) $data = $decoded;
                }
                $data['rp_token'] = hash('sha256', $token);
                $data['rp_exp']   = time() + 3600;
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $uid = (int)$row['id_user'];
                $upd = $conn->prepare('UPDATE user SET u_data = ? WHERE id_user = ?');
                $upd->bind_param('si', $json, $uid);
                $upd->execute();
                $mailOk = HA_SendResetEmail($email, $token);
                echo json_encode(['ok' => true, 'mail_sent' => $mailOk]);
                exit();
            }
            echo json_encode(['ok' => true, 'mail_sent' => true]);
            exit();
        }

        // ---- اعمال رمز جدید با توکن ----
        if ($ACT == 'reset_password') {
            if (!HA_RateLimit('reset_password', 10, 3600)) {
                echo json_encode(['error' => 'rate_limited']);
                exit();
            }
            $token = isset($JSD->token) ? trim((string)$JSD->token) : '';
            $password = isset($JSD->password) ? (string)$JSD->password : '';
            if ($token === '' || strlen($token) < 16) {
                echo json_encode(['error' => 'invalid_token']);
                exit();
            }
            if (strlen($password) < 6) {
                echo json_encode(['error' => 'password_too_short']);
                exit();
            }
            $tokenHash = hash('sha256', $token);
            $like = '%' . $conn->real_escape_string($tokenHash) . '%';
            $sql = "SELECT id_user, act, u_data, u_email FROM user WHERE u_data LIKE '{$like}' LIMIT 5";
            $res = $conn->query($sql);
            $found = null;
            $data = null;
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $decoded = json_decode($row['u_data'] ?? '', true);
                    if (!is_array($decoded)) continue;
                    if (empty($decoded['rp_token']) || !hash_equals((string)$decoded['rp_token'], $tokenHash)) continue;
                    if (empty($decoded['rp_exp']) || (int)$decoded['rp_exp'] < time()) {
                        echo json_encode(['error' => 'token_expired']);
                        exit();
                    }
                    if ((int)$row['act'] === 6) {
                        echo json_encode(['error' => 'account_disabled']);
                        exit();
                    }
                    $found = $row;
                    $data = $decoded;
                    break;
                }
            }
            if (!$found || !$data) {
                echo json_encode(['error' => 'invalid_token']);
                exit();
            }
            unset($data['rp_token'], $data['rp_exp']);
            $data['password_reset_at'] = time();
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $uid = (int)$found['id_user'];
            $upd = $conn->prepare('UPDATE user SET u_pass = ?, u_data = ? WHERE id_user = ?');
            $upd->bind_param('ssi', $hash, $json, $uid);
            $upd->execute();
            echo json_encode(['ok' => true, 'email' => $found['u_email']]);
            exit();
        }
        
        if ($ACT == 'logout') {
            $refreshToken = isset($JSD->refresh_token) ? (string)$JSD->refresh_token : '';
            $deviceToken  = isset($JSD->did) ? (string)$JSD->did : '';
        
            $deleted = 0;
        
            // 1) مطمئن‌ترین: فقط device_token
            if ($deviceToken !== '') {
                $stmt = $conn->prepare('DELETE FROM user_devices WHERE device_token = ?');
                $stmt->bind_param('s', $deviceToken);
                $stmt->execute();
                $deleted = $stmt->affected_rows;
            }
        
            // 2) اگر did نبود، با hash
            if ($deleted === 0 && $refreshToken !== '') {
                $hash = hash('sha256', $refreshToken);
                $stmt = $conn->prepare('DELETE FROM user_devices WHERE refresh_token_hash = ?');
                $stmt->bind_param('s', $hash);
                $stmt->execute();
                $deleted = $stmt->affected_rows;
            }
        
            echo json_encode(['ok' => true, 'deleted' => (int)$deleted]);
            exit();
        }

        if ($ACT == 'request_code') {
            // ---- درخواست کد جدید ----
            require_once __DIR__ . '/phpqrcode/qrlib.php';
        
            $conn->query("DELETE FROM tv_login_sessions WHERE created_at < " . (time() - 600));
        
            do {
                $code = strval(rand(100000, 999999));
                $checkStmt = $conn->prepare("SELECT id FROM tv_login_sessions WHERE code = ?");
                $checkStmt->bind_param("s", $code);
                $checkStmt->execute();
                $exists = $checkStmt->get_result()->num_rows > 0;
            } while ($exists);
        
            $now = time();
            $insertStmt = $conn->prepare("INSERT INTO tv_login_sessions (code, status, created_at) VALUES (?, 'pending', ?)");
            $insertStmt->bind_param("si", $code, $now);
            $insertStmt->execute();
        
            $pairUrl = 'https://hamrahang.ir/pair/' . $code;
        
            ob_start();
            QRcode::png($pairUrl, false, QR_ECLEVEL_M, 6, 1);
            $qrImageData = ob_get_contents();
            ob_end_clean();
        
            $response['code'] = $code;
            $response['pair_url'] = $pairUrl;
            $response['qr_base64'] = 'data:image/png;base64,' . base64_encode($qrImageData);
        
            echo json_encode($response);
            exit();
        }
        // ---- تلویزیون: چک وضعیت کد ----
        if ($ACT == 'check_code') {
            header('Content-Type: application/json; charset=utf-8');
        
            $code        = isset($JSD->code) ? trim((string)$JSD->code) : '';
            $deviceToken = isset($JSD->did) ? (string)$JSD->did : '';
            $deviceType  = isset($JSD->device_type) ? (string)$JSD->device_type : 'tv_webapp';
        
            if ($code === '') {
                echo json_encode(['status' => 'expired']);
                exit();
            }
        
            $stmt = $conn->prepare('SELECT status, id_user FROM tv_login_sessions WHERE code = ? LIMIT 1');
            if (!$stmt) {
                echo json_encode(['error' => 'prepare_failed', 'detail' => $conn->error]);
                exit();
            }
            $stmt->bind_param('s', $code);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result || $result->num_rows === 0) {
                echo json_encode(['status' => 'expired']);
                exit();
            }
        
            $row = $result->fetch_assoc();
            if ($row['status'] !== 'confirmed' || empty($row['id_user'])) {
                echo json_encode(['status' => $row['status']]);
                exit();
            }
        
            if ($deviceToken === '') {
                echo json_encode(['error' => 'missing_device_token']);
                exit();
            }
        
            $id_user = (int)$row['id_user'];
            $session = IssueSessionForDevice($conn, $id_user, $deviceToken, $deviceType);
        
            $userStmt = $conn->prepare(
                'SELECT u_name, f_name, l_name, u_email, act FROM user WHERE id_user = ? LIMIT 1'
            );
            $userStmt->bind_param('i', $id_user);
            $userStmt->execute();
            $userRow = $userStmt->get_result()->fetch_assoc();
            if (!$userRow) {
                echo json_encode(['error' => 'user_not_found']);
                exit();
            }
        
            // devices — اگر fail شد، لاگین نشکند
            $devicesList = [];
            try {
                $devStmt = $conn->prepare("
                    SELECT device_token, device_type, ip, created_at, last_active_at
                    FROM user_devices
                    WHERE id_user = ?
                    ORDER BY last_active_at DESC
                ");
                // بدون device_label عمداً
                if ($devStmt) {
                    $devStmt->bind_param('i', $id_user);
                    $devStmt->execute();
                    $devResult = $devStmt->get_result();
                    if ($devResult) {
                        while ($d = $devResult->fetch_assoc()) {
                            $devicesList[] = [
                                'did'          => $d['device_token'],
                                'device_type'  => $d['device_type'],
                                'device_label' => null,
                                'ip'           => $d['ip'],
                                'created_at'   => (int)$d['created_at'],
                                'last_active'  => (int)$d['last_active_at'],
                                'is_current'   => ($d['device_token'] === $deviceToken)
                            ];
                        }
                    }
                }
            } catch (Throwable $e) {
                $devicesList = [];
            }
        
            $payload = [
                'status'        => 'confirmed',
                'access_token'  => $session['access_token'],
                'refresh_token' => $session['refresh_token'],
                'id_user'       => $id_user + (int)$IDHM,
                'user_name'     => $userRow['u_name'],
                'first_name'    => $userRow['f_name'],
                'last_name'     => $userRow['l_name'],
                'email'         => $userRow['u_email'],
                'act'           => (int)$userRow['act'],
                'devices'       => $devicesList
            ];
        
            $del = $conn->prepare('DELETE FROM tv_login_sessions WHERE code = ?');
            $del->bind_param('s', $code);
            $del->execute();
        
            echo json_encode($payload);
            exit();
        }
    
        if ($ACT == 'refresh_session') {
        
            $refreshToken = isset($JSD->refresh_token) ? $JSD->refresh_token : '';
            $deviceToken  = isset($JSD->did) ? $JSD->did : '';
        
            if (empty($refreshToken) || empty($deviceToken)) {
                echo json_encode(['error' => 'missing_fields']);
                exit();
            }
        
            $refreshHash = hash('sha256', $refreshToken);
        
            $stmt = $conn->prepare("
                SELECT id_user
                FROM user_devices
                WHERE device_token = ? AND refresh_token_hash = ?
            ");
            $stmt->bind_param("ss", $deviceToken, $refreshHash);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows === 0) {
                echo json_encode(['error' => 'invalid_session']);
                exit();
            }
        
            $row = $result->fetch_assoc();
            $id_user = (int)$row['id_user'];
        
            // وجود کاربر
            $userStmt = $conn->prepare("SELECT act FROM user WHERE id_user = ?");
            $userStmt->bind_param("i", $id_user);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
        
            if ($userResult->num_rows === 0) {
                echo json_encode(['error' => 'invalid_session']);
                exit();
            }
        
            $userRow = $userResult->fetch_assoc();
            $act = (int)$userRow['act'];
        
            // چرخش توکن
            $newRefreshToken = bin2hex(random_bytes(48));
            $newRefreshHash  = hash('sha256', $newRefreshToken);
            $now = time();
        
            $update = $conn->prepare("
                UPDATE user_devices
                SET refresh_token_hash = ?, last_active_at = ?
                WHERE device_token = ?
            ");
            $update->bind_param("sis", $newRefreshHash, $now, $deviceToken);
            $update->execute();
        
            $accessToken = GenerateJWT([
                'sub' => $id_user,
                'did' => $deviceToken,
                'exp' => $now + 86400
            ]);
        
            echo json_encode([
                'access_token'  => $accessToken,
                'refresh_token' => $newRefreshToken,
                'id_user'       => $id_user + $IDHM,
                'act'           => $act
            ]);
            exit();
        }
        
        if ($ACT == 'confirm_device') {

            $id_user = GetUserIdFromAccessToken(); // اعتبارسنجی امضای JWT، نه اعتماد به ورودی خام
        
            if (!$id_user) {
                echo json_encode(['error' => 'unauthorized']);
                exit();
            }
        
            $code = isset($JSD->code) ? $JSD->code : '';
        
            $stmt = $conn->prepare("UPDATE tv_login_sessions SET status='confirmed', id_user=? WHERE code=? AND status='pending'");
            $stmt->bind_param("is", $id_user, $code);
            $stmt->execute();
        
            if ($stmt->affected_rows === 0) {
                echo json_encode(['error' => 'invalid_or_expired_code']);
                exit();
            }
        
            echo json_encode(['ok' => true]);
            exit();
        }
        
        if ($ACT == 'list_devices') {
        
            $id_user = GetUserIdFromAccessToken();
            if (!$id_user) {
                echo json_encode(['error' => 'unauthorized']);
                exit();
            }
        
            $currentDid = isset($JSD->did) ? $JSD->did : '';
        
            $stmt = $conn->prepare("
                SELECT device_token, device_type, device_label, ip, created_at, last_active_at
                FROM user_devices
                WHERE id_user = ?
                ORDER BY last_active_at DESC
            ");
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $devices = [];
            while ($d = $result->fetch_assoc()) {
                $devices[] = [
                    'did'          => $d['device_token'],
                    'device_type'  => $d['device_type'],
                    'device_label' => $d['device_label'],
                    'ip'           => $d['ip'],
                    'created_at'   => (int)$d['created_at'],
                    'last_active'  => (int)$d['last_active_at'],
                    'is_current'   => ($currentDid && $d['device_token'] === $currentDid)
                ];
            }
        
            echo json_encode(['devices' => $devices]);
            exit();
        }
        
        if ($ACT == 'revoke_device') {
        
            $id_user = GetUserIdFromAccessToken();
            if (!$id_user) {
                echo json_encode(['error' => 'unauthorized']);
                exit();
            }
        
            $targetDid = isset($JSD->did) ? $JSD->did : '';
            if (empty($targetDid)) {
                echo json_encode(['error' => 'missing_device']);
                exit();
            }
        
            $stmt = $conn->prepare("DELETE FROM user_devices WHERE device_token = ? AND id_user = ?");
            $stmt->bind_param("si", $targetDid, $id_user);
            $stmt->execute();
        
            if ($stmt->affected_rows === 0) {
                echo json_encode(['error' => 'device_not_found']);
                exit();
            }
        
            echo json_encode(['ok' => true]);
            exit();
        }
    
    
        echo json_encode(['error' => 'invalid action']);
        exit();
    }
    
 /*   // داخل webhook ربات، وقتی /start با یه پارامتر بیاد
$code = $_GET['start_param']; // یا هرجایی که پارامتر start رو می‌گیری
$tgId = $message['from']['id']; // آی‌دی تلگرام فرستنده

$stmt = $conn->prepare("UPDATE tv_login_sessions SET status='confirmed', id_tlg=? WHERE code=? AND status='pending'");
$stmt->bind_param("is", $tgId, $code);
$stmt->execute();*/

    if($QJS=='uac'){
        
        header('Content-Type: application/json; charset=utf-8');
    
        $id_user = GetUserIdFromAccessToken();
        if (!$id_user) {
            echo json_encode(['error' => 'unauthorized']);
            exit();
        }
    
        $INPUT = file_get_contents('php://input');
        $JSD   = json_decode($INPUT);
    
        // ساختار: { q: { p, a, q: [id], items?: {...} } }
        $qObj = isset($JSD->q) ? $JSD->q : null;
        if (!$qObj || !isset($qObj->a)) {
            echo json_encode(['error' => 'invalid_payload']);
            exit();
        }
    
        $ACT  = $qObj->a;
        $type = isset($qObj->p) ? $qObj->p : '';
        $ids  = (isset($qObj->q) && is_array($qObj->q)) ? $qObj->q : [];
    
        // ==========================================
        // get_favorites
        // ==========================================
        if ($ACT == 'get_favorites') {
    
            $stmt = $conn->prepare("
                SELECT item_type, item_id, created_at
                FROM user_favorites
                WHERE id_user = ?
                ORDER BY created_at DESC
            ");
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $out = [
                'song' => [], 'artist' => [], 'album' => [],
                'video' => [], 'musicvideo' => [], 'podcast' => [], 'playlist' => []
            ];
    
            while ($row = $result->fetch_assoc()) {
                $key = ContentTypeToString((int)$row['item_type']);
                if ($key && isset($out[$key])) {
                    $out[$key][] = RealIdToPublic((int)$row['item_id'], $IDHM);
                }
            }
    
            echo json_encode(['favorites' => $out]);
            exit();
        }
    
        // ==========================================
        // fav_add
        // ==========================================
        if ($ACT == 'fav_add') {
    
            $itemType = ContentTypeToInt($type);
            if ($itemType === null) {
                echo json_encode(['error' => 'invalid_type']);
                exit();
            }
            if (count($ids) < 1) {
                echo json_encode(['error' => 'missing_id']);
                exit();
            }
    
            $itemId = PublicIdToReal($ids[0], $IDHM);
            if ($itemId <= 0) {
                echo json_encode(['error' => 'invalid_id']);
                exit();
            }
    
            $now = time();
            $ins = $conn->prepare("
                INSERT IGNORE INTO user_favorites (id_user, item_type, item_id, created_at)
                VALUES (?, ?, ?, ?)
            ");
            $ins->bind_param('iiii', $id_user, $itemType, $itemId, $now);
            $ins->execute();
    
            echo json_encode([
                'ok'        => true,
                'type'      => ContentTypeToString($itemType),
                'id'        => RealIdToPublic($itemId, $IDHM),
                'favorited' => true
            ]);
            exit();
        }
    
        // ==========================================
        // fav_remove
        // ==========================================
        if ($ACT == 'fav_remove') {
    
            $itemType = ContentTypeToInt($type);
            if ($itemType === null) {
                echo json_encode(['error' => 'invalid_type']);
                exit();
            }
            if (count($ids) < 1) {
                echo json_encode(['error' => 'missing_id']);
                exit();
            }
    
            $itemId = PublicIdToReal($ids[0], $IDHM);
            if ($itemId <= 0) {
                echo json_encode(['error' => 'invalid_id']);
                exit();
            }
    
            $del = $conn->prepare("
                DELETE FROM user_favorites
                WHERE id_user = ? AND item_type = ? AND item_id = ?
            ");
            $del->bind_param('iii', $id_user, $itemType, $itemId);
            $del->execute();
    
            echo json_encode([
                'ok'        => true,
                'type'      => ContentTypeToString($itemType),
                'id'        => RealIdToPublic($itemId, $IDHM),
                'favorited' => false
            ]);
            exit();
        }
    
        // ==========================================
        // fav_toggle
        // ==========================================
        if ($ACT == 'fav_toggle') {
    
            $itemType = ContentTypeToInt($type);
            if ($itemType === null) {
                echo json_encode(['error' => 'invalid_type']);
                exit();
            }
            if (count($ids) < 1) {
                echo json_encode(['error' => 'missing_id']);
                exit();
            }
    
            $itemId = PublicIdToReal($ids[0], $IDHM);
            if ($itemId <= 0) {
                echo json_encode(['error' => 'invalid_id']);
                exit();
            }
    
            $now = time();
    
            $check = $conn->prepare("
                SELECT 1 FROM user_favorites
                WHERE id_user = ? AND item_type = ? AND item_id = ?
                LIMIT 1
            ");
            $check->bind_param('iii', $id_user, $itemType, $itemId);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
    
            if ($exists) {
                $del = $conn->prepare("
                    DELETE FROM user_favorites
                    WHERE id_user = ? AND item_type = ? AND item_id = ?
                ");
                $del->bind_param('iii', $id_user, $itemType, $itemId);
                $del->execute();
                $favorited = false;
            } else {
                $ins = $conn->prepare("
                    INSERT IGNORE INTO user_favorites (id_user, item_type, item_id, created_at)
                    VALUES (?, ?, ?, ?)
                ");
                $ins->bind_param('iiii', $id_user, $itemType, $itemId, $now);
                $ins->execute();
                $favorited = true;
            }
    
            echo json_encode([
                'ok'        => true,
                'type'      => ContentTypeToString($itemType),
                'id'        => RealIdToPublic($itemId, $IDHM),
                'favorited' => $favorited
            ]);
            exit();
        }
    
        // ==========================================
        // sync_favorites (Add-only)
        // ==========================================
        if ($ACT == 'sync_favorites') {
    
            $items = isset($qObj->items) ? $qObj->items : null;
            if (!$items || !is_object($items)) {
                echo json_encode(['error' => 'invalid_items']);
                exit();
            }
    
            $now = time();
            $added = 0;
    
            $ins = $conn->prepare("
                INSERT IGNORE INTO user_favorites (id_user, item_type, item_id, created_at)
                VALUES (?, ?, ?, ?)
            ");
    
            foreach ($items as $typeName => $idList) {
                $itemType = ContentTypeToInt($typeName);
                if ($itemType === null || !is_array($idList)) {
                    continue;
                }
    
                foreach ($idList as $pubId) {
                    $itemId = PublicIdToReal($pubId, $IDHM);
                    if ($itemId <= 0) {
                        continue;
                    }
    
                    $ins->bind_param('iiii', $id_user, $itemType, $itemId, $now);
                    $ins->execute();
                    if ($ins->affected_rows > 0) {
                        $added++;
                    }
                }
            }
    
            $stmt = $conn->prepare("
                SELECT item_type, item_id
                FROM user_favorites
                WHERE id_user = ?
                ORDER BY created_at DESC
            ");
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $out = [
                'song' => [], 'artist' => [], 'album' => [],
                'video' => [], 'musicvideo' => [], 'podcast' => [], 'playlist' => []
            ];
    
            while ($row = $result->fetch_assoc()) {
                $key = ContentTypeToString((int)$row['item_type']);
                if ($key && isset($out[$key])) {
                    $out[$key][] = RealIdToPublic((int)$row['item_id'], $IDHM);
                }
            }
    
            echo json_encode([
                'ok'        => true,
                'added'     => $added,
                'favorites' => $out
            ]);
            exit();
        }

        // get_history
        // ==========================================
        if ($ACT == 'get_history') {
        
            $HISTORY_MAX = 50;
            $allowed = [0, 1, 2, 3];
        
            $out = [
                'song'        => [],
                'podcast'     => [],
                'musicvideo'  => [],
                'video'       => []
            ];
        
            // جدیدترین‌ها؛ بعد در PHP per-type می‌بُریم
            $stmt = $conn->prepare("
                SELECT item_type, item_id, played_at
                FROM user_history
                WHERE id_user = ?
                ORDER BY played_at DESC
                LIMIT 250
            ");
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $counts = [
                'song' => 0, 'podcast' => 0, 'musicvideo' => 0, 'video' => 0
            ];
        
            while ($row = $result->fetch_assoc()) {
                $key = ContentTypeToString((int)$row['item_type']);
                if ($key === null || !isset($out[$key])) continue;
                if ($counts[$key] >= $HISTORY_MAX) continue;
        
                $out[$key][] = [
                    'id'   => RealIdToPublic((int)$row['item_id'], $IDHM),
                    'type' => $key,
                    'at'   => (int)$row['played_at']
                ];
                $counts[$key]++;
            }
        
            // آرایهٔ تخت به‌ترتیب زمان (برای کلاینت)
            $flat = [];
            foreach (['song', 'podcast', 'musicvideo', 'video'] as $k) {
                foreach ($out[$k] as $row) {
                    $flat[] = $row;
                }
            }
            usort($flat, function ($a, $b) {
                return ($b['at'] ?? 0) - ($a['at'] ?? 0);
            });
        
            echo json_encode([
                'ok'      => true,
                'history' => $flat,
                'by_type' => $out
            ]);
            exit();
        }
        
        // ==========================================
        // sync_history  — add/update only (batch)
        // items: [ { id: publicId, type: 'song'|..., at: unix }, ... ]
        // ==========================================
        if ($ACT == 'sync_history') {
        
            $items = (isset($qObj->items) && is_array($qObj->items)) ? $qObj->items : [];
            if (empty($items)) {
                echo json_encode(['ok' => true, 'upserted' => 0]);
                exit();
            }
        
            // فقط typeهای قابل پخش 0..3
            $allowed = [0, 1, 2, 3];
            $upserted = 0;
            $HISTORY_MAX = 50;
        
            $stmt = $conn->prepare("
                INSERT INTO user_history (id_user, item_type, item_id, played_at)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    played_at = GREATEST(played_at, VALUES(played_at))
            ");
        
            foreach ($items as $it) {
                $typeStr = is_object($it) ? ($it->type ?? 'song') : ($it['type'] ?? 'song');
                $pubId   = is_object($it) ? (int)($it->id ?? 0) : (int)($it['id'] ?? 0);
                $at      = is_object($it) ? (int)($it->at ?? 0) : (int)($it['at'] ?? 0);
        
                if ($pubId <= 0) continue;
                if ($at <= 0) $at = time();
        
                $itemType = ContentTypeToInt($typeStr);
                if ($itemType === null || !in_array($itemType, $allowed, true)) continue;
        
                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) continue;
        
                $stmt->bind_param('iiii', $id_user, $itemType, $realId, $at);
                if ($stmt->execute()) {
                    $upserted++;
                }
            }
        
            // سقف ۵۰ تا per type — قدیمی‌ها حذف
            foreach ($allowed as $t) {
                $conn->query("
                    DELETE FROM user_history
                    WHERE id_user = " . (int)$id_user . "
                      AND item_type = " . (int)$t . "
                      AND played_at < (
                          SELECT boundary FROM (
                              SELECT played_at AS boundary
                              FROM user_history
                              WHERE id_user = " . (int)$id_user . "
                                AND item_type = " . (int)$t . "
                              ORDER BY played_at DESC
                              LIMIT 1 OFFSET " . (int)($HISTORY_MAX - 1) . "
                          ) AS x
                      )
                ");
                // اگر کمتر از ۵۰ ردیف باشد، subquery ممکن است خالی باشد → حذف صفر ردیف (OK)
            }
        
            echo json_encode([
                'ok'       => true,
                'upserted' => $upserted
            ]);
            exit();
        }
        
        // get_playlists
        // ==========================================
        if ($ACT == 'get_playlists') {
            $stmt = $conn->prepare("
                SELECT *
                FROM playlist
                WHERE id_user = ? AND pl_act != 0
                ORDER BY updated_at DESC, id_playlist DESC
            ");
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $list = [];
            while ($row = $result->fetch_assoc()) {
                // همان منطق PLAYLIST: کارت + songs/videos با position
                // ساده‌ترین: از تابع موجود استفاده کن
                $full = PLAYLIST((int)$row['id_playlist']);
                if ($full && !empty($full['playlist'])) {
                    $list[] = $full['playlist'];
                }
            }
        
            echo json_encode([
                'playlists' => $list,
                'limits'    => PlaylistLimitsForUser($conn, $id_user)
            ]);
            exit();
        }

        // create_playlist 
        // ==========================================
        if ($ACT == 'create_playlist') {
            $name  = isset($qObj->name) ? trim((string)$qObj->name) : '';
            $items = (isset($qObj->items) && is_array($qObj->items)) ? $qObj->items : [];
        
            if ($name === '') {
                echo json_encode(['error' => 'missing_name']);
                exit();
            }
        
            $limits = PlaylistLimitsForUser($conn, $id_user);
            $countPl = CountUserPlaylists($conn, $id_user);
            if ($countPl >= $limits['playlists']) {
                echo json_encode([
                    'error'   => 'playlist_limit',
                    'limit'   => $limits['playlists'],
                    'current' => $countPl
                ]);
                exit();
            }
        
            $now = time();
            $playlist_data = '{}';
            $stmt = $conn->prepare("
                INSERT INTO playlist
                    (playlist_name, id_user, playlist_data, created_at, updated_at, pl_act)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->bind_param('sisii', $name, $id_user, $playlist_data, $now, $now);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'insert_failed', 'detail' => $stmt->error]);
                exit();
            }
            $newId = (int)$conn->insert_id;
        
            $maxItems = $limits['items'];
            $pos = 0;
            $ins = $conn->prepare("
                INSERT INTO playlist_items (id_playlist, item_type, item_id, position, added_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($items as $it) {
                if ($pos >= $maxItems) break;
        
                $typeStr = is_object($it) ? ($it->type ?? 'song') : ($it['type'] ?? 'song');
                $pubId   = is_object($it) ? (int)($it->id ?? 0) : (int)($it['id'] ?? 0);
                $itemType = ContentTypeToInt($typeStr);
                if ($itemType === null || $itemType > 3 || $pubId <= 0) continue;
        
                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) continue;
        
                $b_pl = $newId;
                $b_type = (int)$itemType;
                $b_id = (int)$realId;
                $b_pos = $pos;
                $b_at = $now;
                $ins->bind_param('iiiii', $b_pl, $b_type, $b_id, $b_pos, $b_at);
                $ins->execute();
                $pos++;
            }
        
            $full = PLAYLIST($newId);
            echo json_encode([
                'ok'       => true,
                'playlist' => ($full && !empty($full['playlist'])) ? $full['playlist'] : null,
                'limits'   => $limits,
                'capped'   => (count($items) > $maxItems)
            ]);
            exit();
        }
        
        // sync_playlists
        // ==========================================
        if ($ACT == 'sync_playlists') {
            $incoming = (isset($qObj->playlists) && is_array($qObj->playlists)) ? $qObj->playlists : [];
            $created  = [];
            $skipped  = [];
            $now      = time();
            $limits   = PlaylistLimitsForUser($conn, $id_user);
        
            foreach ($incoming as $plIn) {
                $name    = is_object($plIn) ? trim((string)($plIn->name ?? '')) : trim((string)($plIn['name'] ?? ''));
                $items   = is_object($plIn) ? ($plIn->items ?? []) : ($plIn['items'] ?? []);
                $localId = is_object($plIn) ? ($plIn->local_id ?? null) : ($plIn['local_id'] ?? null);
        
                if ($name === '') continue;
                if (!is_array($items)) $items = [];
        
                $countPl = CountUserPlaylists($conn, $id_user);
                if ($countPl >= $limits['playlists']) {
                    $skipped[] = [
                        'local_id' => $localId,
                        'reason'   => 'playlist_limit',
                        'limit'    => $limits['playlists']
                    ];
                    continue;
                }
        
                $playlist_data = '{}';
                $stmt = $conn->prepare("
                    INSERT INTO playlist
                        (playlist_name, id_user, playlist_data, created_at, updated_at, pl_act)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->bind_param('sisii', $name, $id_user, $playlist_data, $now, $now);
                if (!$stmt->execute()) {
                    $skipped[] = ['local_id' => $localId, 'reason' => 'insert_failed'];
                    continue;
                }
        
                $newId = (int)$conn->insert_id;
                if ($newId <= 0) continue;
        
                $maxItems = $limits['items'];
                $ins = $conn->prepare("
                    INSERT INTO playlist_items (id_playlist, item_type, item_id, position, added_at)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $pos = 0;
                foreach ($items as $it) {
                    if ($pos >= $maxItems) break;
        
                    $typeStr  = is_object($it) ? ($it->type ?? 'song') : ($it['type'] ?? 'song');
                    $pubId    = is_object($it) ? (int)($it->id ?? 0) : (int)($it['id'] ?? 0);
                    $itemType = ContentTypeToInt($typeStr);
                    $realId   = PublicIdToReal($pubId, $IDHM);
                    if ($itemType === null || $itemType > 3 || $realId <= 0) continue;
        
                    $b_pl = $newId;
                    $b_type = (int)$itemType;
                    $b_id = (int)$realId;
                    $b_pos = $pos;
                    $b_at = $now;
                    $ins->bind_param('iiiii', $b_pl, $b_type, $b_id, $b_pos, $b_at);
                    $ins->execute();
                    $pos++;
                }
        
                $full = PLAYLIST($newId);
                $created[] = [
                    'local_id' => $localId,
                    'playlist' => ($full && !empty($full['playlist'])) ? $full['playlist'] : null,
                    'capped'   => (count($items) > $maxItems)
                ];
            }
        
            // لیست نهایی (همان کد قبلی get all)
            $stmt = $conn->prepare("
                SELECT id_playlist FROM playlist
                WHERE id_user = ? AND pl_act != 0
                ORDER BY updated_at DESC, id_playlist DESC
            ");
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $result = $stmt->get_result();
            $all = [];
            while ($row = $result->fetch_assoc()) {
                $full = PLAYLIST((int)$row['id_playlist']);
                if ($full && !empty($full['playlist'])) {
                    $all[] = $full['playlist'];
                }
            }
        
            echo json_encode([
                'ok'        => true,
                'created'   => $created,
                'skipped'   => $skipped,
                'playlists' => $all,
                'limits'    => $limits
            ]);
            exit();
        }
        
        // add_playlist_item
        // ==========================================
        if ($ACT == 'add_playlist_item') {
            $pubPl   = isset($qObj->playlist_id) ? (int)$qObj->playlist_id : 0;
            $typeStr = isset($qObj->type) ? $qObj->type : 'song';
            $pubId   = isset($qObj->id) ? (int)$qObj->id : 0;
        
            $realPl   = PublicIdToReal($pubPl, $IDHM);
            $itemType = ContentTypeToInt($typeStr);
            $realId   = PublicIdToReal($pubId, $IDHM);
        
            if ($realPl <= 0 || $itemType === null || $itemType > 3 || $realId <= 0) {
                echo json_encode(['error' => 'invalid']);
                exit();
            }
        
            $chk = $conn->prepare("
                SELECT id_playlist FROM playlist
                WHERE id_playlist = ? AND id_user = ? AND pl_act != 0
                LIMIT 1
            ");
            $chk->bind_param('ii', $realPl, $id_user);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
        
            $limits    = PlaylistLimitsForUser($conn, $id_user);
            $itemCount = CountPlaylistItems($conn, $realPl);
            if ($itemCount >= $limits['items']) {
                echo json_encode([
                    'error'   => 'item_limit',
                    'limit'   => $limits['items'],
                    'current' => $itemCount
                ]);
                exit();
            }
        
            $ex = $conn->prepare("
                SELECT 1 FROM playlist_items
                WHERE id_playlist = ? AND item_type = ? AND item_id = ?
                LIMIT 1
            ");
            $ex->bind_param('iii', $realPl, $itemType, $realId);
            $ex->execute();
            if ($ex->get_result()->num_rows > 0) {
                echo json_encode(['ok' => true, 'exists' => true]);
                exit();
            }
        
            $posRes = $conn->query(
                "SELECT COALESCE(MAX(position), -1) + 1 AS p FROM playlist_items WHERE id_playlist = " . (int)$realPl
            );
            $posRow = ($posRes && ($posRow = $posRes->fetch_assoc())) ? $posRow : null;
            $pos = isset($posRow['p']) ? (int)$posRow['p'] : 0;
            $now = time();
        
            $ins = $conn->prepare("
                INSERT INTO playlist_items (id_playlist, item_type, item_id, position, added_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->bind_param('iiiii', $realPl, $itemType, $realId, $pos, $now);
        
            if (!$ins->execute()) {
                echo json_encode(['error' => 'insert_failed', 'detail' => $ins->error]);
                exit();
            }
        
            $conn->query(
                "UPDATE playlist SET updated_at = " . (int)$now . " WHERE id_playlist = " . (int)$realPl
            );
        
            echo json_encode(['ok' => true]);
            exit();
        }
        
        // set_playlist_items
        // ==========================================
        if ($ACT == 'set_playlist_items') {
            $pubPl = isset($qObj->playlist_id) ? (int)$qObj->playlist_id : 0;
            $items = (isset($qObj->items) && is_array($qObj->items)) ? $qObj->items : [];
            $realPl = PublicIdToReal($pubPl, $IDHM);
        
            if ($realPl <= 0) {
                echo json_encode(['error' => 'invalid']);
                exit();
            }
        
            $chk = $conn->prepare("
                SELECT id_playlist, pl_act FROM playlist
                WHERE id_playlist = ? AND id_user = ? AND pl_act != 0
                LIMIT 1
            ");
            $chk->bind_param('ii', $realPl, $id_user);
            $chk->execute();
            $res = $chk->get_result();
            if ($res->num_rows === 0) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
            $rowPl = $res->fetch_assoc();
            if ((int)$rowPl['pl_act'] === 9) {
                echo json_encode(['error' => 'forbidden']);
                exit();
            }
        
            $limits = PlaylistLimitsForUser($conn, $id_user);
            $maxItems = $limits['items'];
        
            // نرمال‌سازی + برش سقف
            $normalized = [];
            $seen = [];
            foreach ($items as $it) {
                $typeStr = is_object($it) ? ($it->type ?? 'song') : ($it['type'] ?? 'song');
                $pubId   = is_object($it) ? (int)($it->id ?? 0) : (int)($it['id'] ?? 0);
                $itemType = ContentTypeToInt($typeStr);
                $realId   = PublicIdToReal($pubId, $IDHM);
                if ($itemType === null || $itemType > 3 || $realId <= 0) continue;
        
                $key = $itemType . ':' . $realId;
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
        
                $normalized[] = [
                    'type' => (int)$itemType,
                    'id'   => (int)$realId
                ];
                if (count($normalized) >= $maxItems) break;
            }
        
            $conn->query("DELETE FROM playlist_items WHERE id_playlist = " . (int)$realPl);
        
            $now = time();
            $ins = $conn->prepare("
                INSERT INTO playlist_items (id_playlist, item_type, item_id, position, added_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            $pos = 0;
            foreach ($normalized as $row) {
                $b_pl = $realPl;
                $b_type = $row['type'];
                $b_id = $row['id'];
                $b_pos = $pos;
                $b_at = $now;
                $ins->bind_param('iiiii', $b_pl, $b_type, $b_id, $b_pos, $b_at);
                $ins->execute();
                $pos++;
            }
        
            $conn->query("UPDATE playlist SET updated_at = " . (int)$now . " WHERE id_playlist = " . (int)$realPl);
        
            $full = PLAYLIST($realPl);
            echo json_encode([
                'ok'       => true,
                'playlist' => ($full && !empty($full['playlist'])) ? $full['playlist'] : null,
                'limits'   => $limits,
                'stored'   => $pos,
                'capped'   => (count($items) > $maxItems)
            ]);
            exit();
        }
        
        // remove_playlist_item
        // ==========================================
        if ($ACT == 'remove_playlist_item') {
            $pubPl   = isset($qObj->playlist_id) ? (int)$qObj->playlist_id : 0;
            $typeStr = isset($qObj->type) ? $qObj->type : 'song';
            $pubId   = isset($qObj->id) ? (int)$qObj->id : 0;
        
            $realPl   = PublicIdToReal($pubPl, $IDHM);
            $itemType = ContentTypeToInt($typeStr);
            $realId   = PublicIdToReal($pubId, $IDHM);
        
            if ($realPl <= 0 || $itemType === null || $itemType > 3 || $realId <= 0) {
                echo json_encode(['error' => 'invalid']);
                exit();
            }
        
            $chk = $conn->prepare("SELECT id_playlist FROM playlist WHERE id_playlist = ? AND id_user = ? AND pl_act != 0");
            $chk->bind_param('ii', $realPl, $id_user);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
        
            $del = $conn->prepare("DELETE FROM playlist_items WHERE id_playlist = ? AND item_type = ? AND item_id = ?");
            $del->bind_param('iii', $realPl, $itemType, $realId);
            $del->execute();
        
            $now = time();
            $conn->query("UPDATE playlist SET updated_at = $now WHERE id_playlist = $realPl");
        
            echo json_encode(['ok' => true]);
            exit();
        }
        
        // reorder_playlist_items
        // ==========================================
        if ($ACT == 'reorder_playlist_items') {
            $pubPl = isset($qObj->playlist_id) ? (int)$qObj->playlist_id : 0;
            $items = (isset($qObj->items) && is_array($qObj->items)) ? $qObj->items : [];
            $realPl = PublicIdToReal($pubPl, $IDHM);
        
            $chk = $conn->prepare("SELECT id_playlist FROM playlist WHERE id_playlist = ? AND id_user = ? AND pl_act != 0");
            $chk->bind_param('ii', $realPl, $id_user);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
        
            $upd = $conn->prepare("UPDATE playlist_items SET position = ? WHERE id_playlist = ? AND item_type = ? AND item_id = ?");
            foreach ($items as $it) {
                $typeStr = is_object($it) ? ($it->type ?? 'song') : ($it['type'] ?? 'song');
                $pubId   = is_object($it) ? (int)($it->id ?? 0) : (int)($it['id'] ?? 0);
                $pos     = is_object($it) ? (int)($it->position ?? 0) : (int)($it['position'] ?? 0);
                $itemType = ContentTypeToInt($typeStr);
                $realId   = PublicIdToReal($pubId, $IDHM);
                if ($itemType === null || $realId <= 0) continue;
                $upd->bind_param('iiii', $pos, $realPl, $itemType, $realId);
                $upd->execute();
            }
            $now = time();
            $conn->query("UPDATE playlist SET updated_at = $now WHERE id_playlist = $realPl");
            echo json_encode(['ok' => true]);
            exit();
        }
        
        // delete_playlist
        // ==========================================
        if ($ACT == 'delete_playlist') {
            $pubPl = isset($qObj->playlist_id) ? (int)$qObj->playlist_id : 0;
            $realPl = PublicIdToReal($pubPl, $IDHM);
        
            if ($realPl <= 0) {
                echo json_encode(['error' => 'invalid']);
                exit();
            }
        
            // فقط مالک می‌تواند حذف کند
            $chk = $conn->prepare("
                SELECT id_playlist FROM playlist
                WHERE id_playlist = ? AND id_user = ?
                LIMIT 1
            ");
            $chk->bind_param('ii', $realPl, $id_user);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
        
            // 1) آیتم‌های داخل پلی‌لیست
            $conn->query("DELETE FROM playlist_items WHERE id_playlist = " . (int)$realPl);
        
            // 2) فاوریت این پلی‌لیست برای همهٔ کاربران
            $favType = ContentTypeToInt('playlist'); // معمولاً 6
            if ($favType !== null) {
                $fav = $conn->prepare("
                    DELETE FROM user_favorites
                    WHERE item_type = ? AND item_id = ?
                ");
                $fav->bind_param('ii', $favType, $realPl);
                $fav->execute();
            }
        
            // 3) خود پلی‌لیست
            $del = $conn->prepare("DELETE FROM playlist WHERE id_playlist = ? AND id_user = ?");
            $del->bind_param('ii', $realPl, $id_user);
            $del->execute();
        
            echo json_encode(['ok' => true]);
            exit();
        }

        // update_playlist  (name + description)
        // ==========================================
        if ($ACT == 'update_playlist') {
            $pubPl = isset($qObj->playlist_id) ? (int)$qObj->playlist_id : 0;
            $realPl = PublicIdToReal($pubPl, $IDHM);
            if ($realPl <= 0) {
                echo json_encode(['error' => 'invalid']);
                exit();
            }
        
            $chk = $conn->prepare("
                SELECT id_playlist, playlist_data, pl_act
                FROM playlist
                WHERE id_playlist = ? AND id_user = ?
                LIMIT 1
            ");
            $chk->bind_param('ii', $realPl, $id_user);
            $chk->execute();
            $res = $chk->get_result();
            if ($res->num_rows === 0) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
            $row = $res->fetch_assoc();
        
            if ((int)$row['pl_act'] === 9) {
                echo json_encode(['error' => 'forbidden']);
                exit();
            }
        
            $name = isset($qObj->name) ? trim((string)$qObj->name) : '';
            if ($name === '') {
                echo json_encode(['error' => 'missing_name']);
                exit();
            }
        
            $des = isset($qObj->des) ? trim((string)$qObj->des) : '';
        
            $data = json_decode($row['playlist_data'] ?: '{}');
            if (!is_object($data)) {
                $data = new stdClass();
            }
            if ($des !== '') {
                $data->des = $des;
            } else {
                unset($data->des);
            }
            // tmb را اینجا دست نزن
        
            $playlist_data = json_encode($data);
            $now = time();
        
            $upd = $conn->prepare("
                UPDATE playlist
                SET playlist_name = ?, playlist_data = ?, updated_at = ?
                WHERE id_playlist = ? AND id_user = ?
            ");
            $upd->bind_param('ssiii', $name, $playlist_data, $now, $realPl, $id_user);
            if (!$upd->execute()) {
                echo json_encode(['error' => 'update_failed', 'detail' => $upd->error]);
                exit();
            }
        
            $full = PLAYLIST($realPl);
            echo json_encode([
                'ok'       => true,
                'playlist' => ($full && !empty($full['playlist'])) ? $full['playlist'] : null
            ]);
            exit();
        }

        // set_playlist_public  (toggle 1 ↔ 2)
        // ==========================================
        if ($ACT == 'set_playlist_public') {
            $pubPl  = isset($qObj->playlist_id) ? (int)$qObj->playlist_id : 0;
            $public = !empty($qObj->public); // true → 2, false → 1
        
            $realPl = PublicIdToReal($pubPl, $IDHM);
            if ($realPl <= 0) {
                echo json_encode(['error' => 'invalid']);
                exit();
            }
        
            $newAct = $public ? 2 : 1;
            $now = time();
        
            // فقط مالک و فقط 1/2 (نه act 9)
            $upd = $conn->prepare("
                UPDATE playlist
                SET pl_act = ?, updated_at = ?
                WHERE id_playlist = ? AND id_user = ? AND pl_act IN (1, 2)
            ");
            $upd->bind_param('iiii', $newAct, $now, $realPl, $id_user);
            $upd->execute();
        
            if ($upd->affected_rows === 0) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
        
            echo json_encode([
                'ok' => true,
                'public' => $public
            ]);
            exit();
        }
    
        echo json_encode(['error' => 'invalid action']);
        exit();
    }

    if($QJS=='csp'){

        header('Content-Type: application/json; charset=utf-8');

        $INPUT = file_get_contents("php://input");
        $JSD   = json_decode($INPUT);
        $JSQ   = (is_object($JSD) && isset($JSD->q) && is_object($JSD->q)) ? $JSD->q : null;

        $QSN  = (is_object($JSQ) && isset($JSQ->name))   ? (string)$JSQ->name   : '';
        $QAN  = (is_object($JSQ) && isset($JSQ->artist)) ? (string)$JSQ->artist : '';
        $QWJS = [];

        // ---- پیدا کردن آهنگ با نام + خواننده (جایگزین SONG($artist,$name, ...)) ----
        $ID_SONG = 0;
        if ($QSN !== '') {

            $stmt = $conn->prepare("
                SELECT s.id_song
                FROM song s
                JOIN song2artist sa ON sa.id_song = s.id_song
                JOIN artist a ON a.id_artist = sa.id_artist
                WHERE s.song_name = ?
                  AND a.artist_name = ?
                  AND s.song_act != 0
                LIMIT 1
            ");
            if ($stmt) {
                $stmt->bind_param('ss', $QSN, $QAN);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $row = $res->fetch_assoc()) {
                    $ID_SONG = (int)$row['id_song'];
                }
            }

            // اگر با خواننده پیدا نشد، فقط با نام
            if (!$ID_SONG) {
                $stmt = $conn->prepare("
                    SELECT id_song FROM song
                    WHERE song_name = ? AND song_act != 0
                    ORDER BY song_ply DESC
                    LIMIT 1
                ");
                if ($stmt) {
                    $stmt->bind_param('s', $QSN);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res && $row = $res->fetch_assoc()) {
                        $ID_SONG = (int)$row['id_song'];
                    }
                }
            }
        }

        if ($ID_SONG) {

            // شمارش پخش
            $conn->query("UPDATE song SET song_ply = (song_ply) + 1 WHERE id_song = " . $ID_SONG);

            $id_user = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;

            if ($id_user) {

                $user_log = null;
                $stmt = $conn->prepare("SELECT u_log FROM user WHERE id_user = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $id_user);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res && $row = $res->fetch_assoc()) {
                        $user_log = json_decode($row['u_log']);
                    }
                }

                if (is_object($user_log)) {

                    if (!isset($user_log->ply) || !is_array($user_log->ply)) {
                        $user_log->ply = [];
                    }

                    // تازه‌ها اول لیست
                    $ply = $user_log->ply;
                    $key = array_search($ID_SONG, $ply);
                    if ($key === false) {
                        array_unshift($ply, $ID_SONG);
                    } else {
                        array_splice($ply, $key, 1);
                        array_unshift($ply, $ID_SONG);
                    }
                    $user_log->ply = $ply;

                    $ulog = json_encode($user_log);
                    $upd  = $conn->prepare("UPDATE user SET u_log = ? WHERE id_user = ?");
                    if ($upd) {
                        $upd->bind_param('si', $ulog, $id_user);
                        $upd->execute();
                    }

                    $song_obj = [];
                    foreach ($ply as $v) {
                        $song = SONG((int)$v, false);
                        if ($song && isset($song['song'])) {
                            $song_obj[] = $song['song'];
                        }
                    }
                    $QWJS = $song_obj;
                }
            }
        }

        echo json_encode($QWJS);

        exit();

    }
    
    if($QJS=='ui'){
    //    echo $Page;
        $user        = isset($_REQUEST['user'])        ? $_REQUEST['user']        : '';
        $start_param = isset($_REQUEST['start_param']) ? $_REQUEST['start_param'] : '';

    //    $secret_key = HMAC_SHA256("8195304633:AAErtQNWATO0AM3KlAqJdMmFgShvo_McRrg", tlgrm.initData);

        $tlgr_obj = json_decode($user);
        if (!is_object($tlgr_obj) || !isset($tlgr_obj->id)) {
            echo json_encode(['error' => 'invalid_user']);
            exit();
        }

        $user_obj = [];
        $user_obj['id'] = $TLGRM_USER_ID    = $tlgr_obj->id;
        $user_obj['nn'] = $TLGRM_USER_USER  = isset($tlgr_obj->username)   ? $tlgr_obj->username   : '';
        $user_obj['fn'] = $TLGRM_USER_FNAME = isset($tlgr_obj->first_name) ? $tlgr_obj->first_name : '';
        $user_obj['ln'] = $TLGRM_USER_LNAME = isset($tlgr_obj->last_name)  ? $tlgr_obj->last_name  : '';
        $user_obj['ap'] = $TLGRM_USER_APP   = !empty($tlgr_obj->allows_write_to_pm);
        $TLGRM_USER_PHOTO = isset($tlgr_obj->photo_url) ? (string)$tlgr_obj->photo_url : '';
        $user_obj['rf'] = $TLGRM_USER_REF = preg_match("/ref(\d+)/", (string)$start_param, $m) ? (int)$m[1] : 0;
        unset($m);

        $U_LOG = '{"join":'.time().'}';

        $stmt = $conn->prepare("SELECT * FROM user WHERE id_tlg = ? AND act != 0 LIMIT 1");
        $result_user = null;
        if ($stmt) {
            $stmt->bind_param('s', $TLGRM_USER_ID);
            $stmt->execute();
            $result_user = $stmt->get_result();
        }

        if ($result_user && $result_user->num_rows > 0) {
            $row_user = $result_user->fetch_assoc();
            $id_user  = (int)$row_user['id_user'];
            $user_log = json_decode($row_user['u_log']);

            $user_obj['ok'] = true;
            $user_obj['jd'] = (is_object($user_log) && isset($user_log->join)) ? (int)$user_log->join : 0;

            // هنرمندان منتخب
            if (is_object($user_log) && isset($user_log->fav) && is_object($user_log->fav)
                && !empty($user_log->fav->artist) && is_array($user_log->fav->artist)) {
                $artist_obj = [];
                foreach ($user_log->fav->artist as $v) {
                    $a = ARTIST((int)$v);
                    if ($a && isset($a['artist'])) {
                        $artist_obj[] = $a['artist'];
                    }
                }
                $user_obj['fv']['art'] = $artist_obj;
            }

            // آهنگ‌های اخیر
            if (is_object($user_log) && !empty($user_log->ply) && is_array($user_log->ply)) {
                $song_obj = [];
                foreach ($user_log->ply as $v) {
                    $s = SONG((int)$v, false);
                    if ($s && isset($s['song'])) {
                        $song_obj[] = $s['song'];
                    }
                }
                $user_obj['lp'] = $song_obj;
            }

            // آهنگ‌های محبوب
            $song_fav_obj = [];
            $stmt_fav = $conn->prepare("SELECT id_song FROM fav_song WHERE id_user = ? AND act != 0");
            if ($stmt_fav) {
                $stmt_fav->bind_param('i', $id_user);
                $stmt_fav->execute();
                $res_fav = $stmt_fav->get_result();
                if ($res_fav) {
                    while ($row_fav = $res_fav->fetch_assoc()) {
                        $s = SONG((int)$row_fav['id_song'], false);
                        if ($s && isset($s['song'])) {
                            $song_fav_obj[] = $s['song'];
                        }
                    }
                }
            }
            if ($song_fav_obj) {
                $user_obj['fv']['sng'] = $song_fav_obj;
            }

            $_SESSION['loggedin'] = true;
            $_SESSION['id_user']  = $id_user;
            if ((int)$row_user['act'] === 7) {
                $user_obj['am'] = true;
                $_SESSION['am'] = true;
            }

        }else{

            $stmt = $conn->prepare("
                INSERT INTO user (id_user, id_tlg, u_name, f_name, l_name, u_log, u_photo, u_gmail, u_ref, act)
                VALUES (NULL, ?, ?, ?, ?, ?, ?, '', ?, '1')
            ");
            $ok = false;
            if ($stmt) {
                $stmt->bind_param(
                    'sssssss',
                    $TLGRM_USER_ID, $TLGRM_USER_USER, $TLGRM_USER_FNAME,
                    $TLGRM_USER_LNAME, $U_LOG, $TLGRM_USER_PHOTO, $TLGRM_USER_REF
                );
                $ok = $stmt->execute();
            }

            if ($ok && $TLGRM_USER_PHOTO !== '') {
                // عکس پروفایل؛ اگر خالی بود copy() در PHP 8 فتال می‌ده
                $dst = 'thumb/' . preg_replace('/\D/', '', (string)$TLGRM_USER_ID) . '.jpg';
                @copy($TLGRM_USER_PHOTO, $dst);
            }
            $user_obj['ok'] = true;

        }

        // اطلاعات کاربر برای SN.user.* که انتهای صفحه چاپ می‌شود
        // (قبلاً فقط داخل p=login ست می‌شد → Undefined array key می‌داد)
        $_SESSION['tlg_id'] = $user_obj['id'];
        $_SESSION['tlg_nn'] = $user_obj['nn'];
        $_SESSION['tlg_fn'] = $user_obj['fn'];
        $_SESSION['tlg_ln'] = $user_obj['ln'];
        $_SESSION['tlg_jd'] = isset($user_obj['jd']) ? $user_obj['jd'] : 0;
        $_SESSION['tlg_rf'] = isset($user_obj['rf']) ? $user_obj['rf'] : 0; // کلید خوانده‌شده tlg_rf است نه tlg_fr
        $_SESSION['tlg_ap'] = $user_obj['ap'];

        if($Page=='login'){
            $_SESSION['loggedin'] = true;
            header("Location: https://hamrahang.ir");

            exit();

        }

        echo json_encode($user_obj);

        exit();
    }
    
    // ==========================================
    // Admin console  →  ?_qjs=adm
    // Body or ?q= JSON: { a:'update_item', table, type, id, name, names, des, rd, act, tmb, lrc, gnr, ... }
    // Phase 1: song table columns only (no related tables yet).
    // ==========================================
    if ($QJS == 'adm') {
    
        header('Content-Type: application/json; charset=utf-8');
    
        // ---- auth: valid JWT + administrator (act >= 7) ----
        $id_user = GetUserIdFromAccessToken();
        if (!$id_user) {
            echo json_encode(['error' => 'unauthorized']);
            exit();
        }
    
        $stmtAct = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
        if (!$stmtAct) {
            echo json_encode(['error' => 'db_error']);
            exit();
        }
        $stmtAct->bind_param('i', $id_user);
        $stmtAct->execute();
        $rowAct = $stmtAct->get_result()->fetch_assoc();
        $stmtAct->close();
        if (!$rowAct || (int)$rowAct['act'] < 7) {
            echo json_encode(['error' => 'forbidden', 'detail' => 'administrator_role_required']);
            exit();
        }
    
        // ---- payload: prefer POST body, fall back to ?q= (cdn gateway uses q=) ----
        $INPUT = file_get_contents('php://input');
        $JSD   = null;
        if ($INPUT !== false && $INPUT !== '') {
            $JSD = json_decode($INPUT);
        }
        if (!$JSD && isset($Q) && $Q !== '') {
            $JSD = is_string($Q) ? json_decode($Q) : null;
            if (is_array($Q)) {
                $JSD = json_decode(json_encode($Q));
            }
        }
        if (!$JSD || !is_object($JSD)) {
            echo json_encode(['error' => 'invalid_payload']);
            exit();
        }
    
        $ACT = isset($JSD->a) ? (string)$JSD->a : '';

        if ($ACT === 'get_user') {
            $pubId = isset($JSD->id) ? (int)$JSD->id : 0;
            $realId = PublicIdToReal($pubId, $IDHM);
            if ($realId <= 0) {
                echo json_encode(['error' => 'invalid_id']);
                exit();
            }

            $stmt = $conn->prepare(
                'SELECT id_user, id_tlg, u_name, f_name, l_name, u_email, u_data, u_ref, act
                 FROM user WHERE id_user = ? LIMIT 1'
            );
            if (!$stmt) {
                echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                exit();
            }
            $stmt->bind_param('i', $realId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }

            $data = [];
            if (!empty($row['u_data'])) {
                $decoded = json_decode($row['u_data'], true);
                if (is_array($decoded)) $data = $decoded;
            }

            $u_name = trim((string)($row['u_name'] ?? ''));
            $email  = trim((string)($row['u_email'] ?? ''));
            $id_tlg = (int)($row['id_tlg'] ?? 0);

            // Edit form identity fill (priority: u_name → email → telegram)
            // So submit always has a non-empty name and UNIQUE '' never happens.
            if ($u_name === '') {
                if ($email !== '') {
                    $u_name = $email;
                } elseif ($id_tlg > 0) {
                    $u_name = (string)$id_tlg;
                }
            }

            $join = 0;
            if (!empty($data['join'])) {
                $join = (int)$data['join'];
            }

            echo json_encode([
                'ok'   => true,
                'user' => [
                    'id'    => (int)$row['id_user'] + (int)$IDHM,
                    'name'  => $u_name,
                    'url'   => (string)($row['f_name'] ?? ''),
                    'other' => (string)($row['l_name'] ?? ''),
                    'email' => $email,
                    'tlg'   => $id_tlg > 0 ? (string)$id_tlg : '',
                    'ref'   => (string)($row['u_ref'] ?? ''),
                    'act'   => (int)$row['act'],
                    'join'  => $join,   // unix seconds → form header
                    'tmb'   => !empty($data['tmb'])   ? (string)$data['tmb']   : '',
                    'ico'   => !empty($data['ico'])   ? (string)$data['ico']   : '',
                    'photo' => !empty($data['photo']) ? (string)$data['photo'] : '',
                    'des'   => !empty($data['des'])   ? (string)$data['des']   : '',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        }
    
        // ==========================================
        // update_item  (song table only for now)
        // ==========================================
        if ($ACT === 'update_item') {
    
            $table = isset($JSD->table) ? strtolower(trim((string)$JSD->table)) : '';
            $type  = isset($JSD->type)  ? strtolower(trim((string)$JSD->type))  : $table;
            $pubId = isset($JSD->id)    ? (int)$JSD->id : 0;
            $name  = isset($JSD->name)  ? trim((string)$JSD->name) : '';

            if ($table === '' || $pubId <= 0) {
                echo json_encode(['error' => 'bad_payload', 'detail' => 'table and id are required']);
                exit();
            }
            // global name mandatory for catalog items only — not for user accounts
            if ($table !== 'user' && $name === '') {
                echo json_encode(['error' => 'name_required', 'detail' => 'global name is mandatory']);
                exit();
            }
    
            // ---- song / podcast share the song table ----
            if ($table === 'song') {
                $conn->begin_transaction();
                try {


                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'invalid_id']);
                    exit();
                }

                $stmt = $conn->prepare(
                    'SELECT id_song, song_name, song_data, song_rd, song_act
                     FROM song WHERE id_song = ? LIMIT 1'
                );
                if (!$stmt) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $stmt->bind_param('i', $realId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$row) {
                    $conn->rollback();
                    echo json_encode(['error' => 'not_found']);
                    exit();
                }

                // =========================================================
                // 1) song table columns
                // =========================================================
                $song_name = trim($name);

                // song_rd — keep existing unless client sent the key
                $song_rd = $row['song_rd'];
                if (property_exists($JSD, 'rd')) {
                    if ($JSD->rd === null || $JSD->rd === '') {
                        $song_rd = null;
                    } else {
                        $song_rd = null;
                        $rd = trim((string)$JSD->rd);
                        if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $rd, $rm)) {
                            $y = $rm[1];
                            $m = isset($rm[2]) ? $rm[2] : '01';
                            $d = isset($rm[3]) ? $rm[3] : '01';
                            if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                                $song_rd = $y . '-' . $m . '-' . $d;
                            }
                        }
                    }
                }

                $song_act = isset($JSD->act) ? (int)$JSD->act : (int)$row['song_act'];
                if ($song_act < 0 || $song_act > 3) {
                    $song_act = 1;
                }

                // merge song_data
                $data = [];
                if (!empty($row['song_data'])) {
                    $decoded = json_decode($row['song_data'], true);
                    if (is_array($decoded)) $data = $decoded;
                }
                unset($data['gnr'], $data['lan']); // never in data

                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if (!empty($names)) $data['names'] = $names;
                    else unset($data['names']);
                }

                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                    else unset($data['des']);
                }

                if (isset($JSD->dur) && (int)$JSD->dur > 0) {
                    $data['dur'] = (int)$JSD->dur;
                }

                // ---- media (only when key present) ----
                $mediaOut = [];
                if (property_exists($JSD, 'media') && is_array($JSD->media)) {
                    unset($data['tmb'], $data['tmv'], $data['cvr'], $data['art'], $data['gal']);
                    $uniqueRoles = [];
                    $galRows = [];
                    foreach ($JSD->media as $m) {
                        if (!is_object($m)) continue;
                        $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                        $src  = isset($m->url)  ? trim((string)$m->url)  : '';
                        if ($role === 'off' || $src === '') continue;
                        if ($role === 'gal') { $galRows[] = $m; continue; }
                        if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                        $uniqueRoles[$role] = $m;
                    }
                    $galIndex = 0;
                    foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                        if (!isset($uniqueRoles[$role])) continue;
                        $src = trim((string)$uniqueRoles[$role]->url);
                        $act = MediaRoleToAct($role);
                        if ($act < 1) continue;
                        $id_img = MediaPlanBKey('song', $pubId, $role, 0);
                        $saved  = IngestThumbFromSource($conn, $src, $id_img, $act);
                        if ($saved === '') {
                            $mediaOut[] = ['role' => $role, 'error' => 'ingest_failed'];
                            continue;
                        }
                        $data[$role] = $saved;
                        $mediaOut[]  = ['role' => $role, 'id_img' => $saved];
                    }
                    if (!empty($galRows)) {
                        $data['gal'] = [];
                        foreach ($galRows as $m) {
                            $src = trim((string)$m->url);
                            $act = MediaRoleToAct('gal');
                            $id_img = MediaPlanBKey('song', $pubId, 'gal', $galIndex++);
                            $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                            if ($saved === '') {
                                $mediaOut[] = ['role' => 'gal', 'error' => 'ingest_failed'];
                                continue;
                            }
                            $data['gal'][] = $saved;
                            $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                        }
                        if (empty($data['gal'])) unset($data['gal']);
                    }
                }

                // ---- lyrics (only when key present) ----
                $lyricsOut = [];
                $typeIntLyric = ContentTypeToInt($type === 'podcast' ? 'podcast' : 'song');
                if (property_exists($JSD, 'lyrics') && is_array($JSD->lyrics)) {
                    unset($data['lrc']);
                    $keptSrc  = [];
                    $lrcIndex = [];
                    foreach ($JSD->lyrics as $L) {
                        if (!is_object($L)) continue;
                        $lng = strtolower(trim((string)(isset($L->lng) ? $L->lng : 'fa')));
                        $lng = preg_replace('/[^a-z]/', '', $lng);
                        if ($lng === '') $lng = 'und';
                        $lng = substr($lng, 0, 3);
                        $knd = (isset($L->knd) && $L->knd === 'srt') ? 'srt' : 'lrc';
                        $fmt = (isset($L->fmt) && $L->fmt === 'jsn') ? 'jsn' : 'txt';
                        $body = isset($L->body) ? (string)$L->body : '';
                        $body = str_replace(["\r\n", "\r"], "\n", $body);
                        if (trim($body) === '') continue;
                        $src = LyricPlanBSrc('song', $pubId, $knd, $lng);
                        $now = time();
                        $st = $conn->prepare(
                            'INSERT INTO lyric (src, item_type, item_id, lng, knd, fmt, body, updated_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE
                               item_type = VALUES(item_type),
                               item_id   = VALUES(item_id),
                               lng       = VALUES(lng),
                               knd       = VALUES(knd),
                               fmt       = VALUES(fmt),
                               body      = VALUES(body),
                               updated_at= VALUES(updated_at)'
                        );
                        if ($st) {
                            $st->bind_param(
                                'siissssi',
                                $src, $typeIntLyric, $realId, $lng, $knd, $fmt, $body, $now
                            );
                            $st->execute();
                            $st->close();
                        }
                        $keptSrc[] = $src;
                        $lrcIndex[] = ['lng' => $lng, 'knd' => $knd, 'fmt' => $fmt, 'src' => $src];
                        $lyricsOut[] = ['lng' => $lng, 'knd' => $knd, 'fmt' => $fmt, 'src' => $src, 'body' => $body];
                    }
                    if (!empty($keptSrc)) {
                        $in = "'" . implode("','", array_map(function ($s) use ($conn) {
                            return $conn->real_escape_string($s);
                        }, $keptSrc)) . "'";
                        $conn->query(
                            "DELETE FROM lyric
                             WHERE item_type = " . (int)$typeIntLyric . "
                               AND item_id   = " . (int)$realId . "
                               AND src NOT IN ($in)"
                        );
                    } else {
                        $conn->query(
                            "DELETE FROM lyric
                             WHERE item_type = " . (int)$typeIntLyric . "
                               AND item_id   = " . (int)$realId
                        );
                    }
                    if (!empty($lrcIndex)) $data['lrc'] = $lrcIndex;
                    else unset($data['lrc']);
                }

                if ($type === 'podcast') $data['typ'] = 2;
                else unset($data['typ']);

                $song_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                if ($song_data_json === false) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => 'song_data json_encode failed']);
                    exit();
                }

                if ($song_rd === null) {
                    $upd = $conn->prepare(
                        'UPDATE song SET song_name = ?, song_data = ?, song_rd = NULL, song_act = ?
                         WHERE id_song = ? LIMIT 1'
                    );
                    if (!$upd) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $upd->bind_param('ssii', $song_name, $song_data_json, $song_act, $realId);
                } else {
                    $upd = $conn->prepare(
                        'UPDATE song SET song_name = ?, song_data = ?, song_rd = ?, song_act = ?
                         WHERE id_song = ? LIMIT 1'
                    );
                    if (!$upd) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $upd->bind_param('sssii', $song_name, $song_data_json, $song_rd, $song_act, $realId);
                }
                if (!$upd->execute()) {
                    $conn->rollback();
                    echo json_encode([
                        'error'  => 'update_failed',
                        'detail' => $upd->error,
                        'errno'  => $upd->errno,
                    ]);
                    $upd->close();
                    exit();
                }
                $upd->close();

                $typeInt     = ContentTypeToInt($type === 'podcast' ? 'podcast' : 'song');
                $typeSong    = ContentTypeToInt('song');
                $typePodcast = ContentTypeToInt('podcast');

                $changes = [
                    'song_name' => $song_name,
                    'song_rd'   => $song_rd,
                    'song_act'  => $song_act,
                    'media'     => $mediaOut,
                    'lyrics'    => $lyricsOut,
                ];

                // =========================================================
                // song2artist — smart sync
                // =========================================================
                $artistIds = [];
                if (property_exists($JSD, 'artists') && is_array($JSD->artists)) {
                    foreach ($JSD->artists as $a) {
                        $pid = 0;
                        if (is_object($a) && isset($a->id)) $pid = (int)$a->id;
                        elseif (is_numeric($a)) $pid = (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $artistIds[] = $rid;
                    }
                    $artistIds = array_values(array_unique($artistIds));

                    if (!empty($artistIds)) {
                        $inList = implode(',', array_map('intval', $artistIds));
                        $sqlDel = "
                            DELETE sa FROM song2artist sa
                            INNER JOIN artist a ON a.id_artist = sa.id_artist
                            WHERE sa.id_song = " . (int)$realId . "
                              AND sa.id_artist NOT IN ($inList)
                              AND a.artist_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE sa FROM song2artist sa
                            INNER JOIN artist a ON a.id_artist = sa.id_artist
                            WHERE sa.id_song = " . (int)$realId . "
                              AND a.artist_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $insA = $conn->prepare(
                        'INSERT IGNORE INTO song2artist (id_song, id_artist) VALUES (?, ?)'
                    );
                    if ($insA) {
                        foreach ($artistIds as $aid) {
                            $aid = (int)$aid;
                            $insA->bind_param('ii', $realId, $aid);
                            if (!$insA->execute()) {
                                $err = $insA->error;
                                $insA->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $insA->close();
                    }
                    $changes['artists'] = $artistIds;
                }

                // =========================================================
                // song2album — smart sync
                // =========================================================
                $albumRows = [];
                if (property_exists($JSD, 'albums') && is_array($JSD->albums)) {
                    $byId = [];
                    foreach ($JSD->albums as $a) {
                        $pid = 0;
                        $trk = 0;
                        if (is_object($a)) {
                            $pid = isset($a->id)  ? (int)$a->id  : 0;
                            $trk = isset($a->trk) ? (int)$a->trk : 0;
                        } elseif (is_numeric($a)) {
                            $pid = (int)$a;
                        }
                        if ($trk < 0) $trk = 0;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $byId[$rid] = $trk;
                    }
                    foreach ($byId as $rid => $trk) {
                        $albumRows[] = [(int)$rid, (int)$trk];
                    }
                    $desiredIds = array_map(function ($p) { return (int)$p[0]; }, $albumRows);

                    if (!empty($desiredIds)) {
                        $inList = implode(',', array_map('intval', $desiredIds));
                        $sqlDel = "
                            DELETE sa FROM song2album sa
                            INNER JOIN album al ON al.id_album = sa.id_album
                            WHERE sa.id_song = " . (int)$realId . "
                              AND sa.id_album NOT IN ($inList)
                              AND al.album_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE sa FROM song2album sa
                            INNER JOIN album al ON al.id_album = sa.id_album
                            WHERE sa.id_song = " . (int)$realId . "
                              AND al.album_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $insAlb = $conn->prepare(
                        'INSERT INTO song2album (id_song, id_album, trk_num)
                         VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE trk_num = VALUES(trk_num)'
                    );
                    if ($insAlb) {
                        foreach ($albumRows as $pair) {
                            $aid = (int)$pair[0];
                            $trk = (int)$pair[1];
                            $insAlb->bind_param('iii', $realId, $aid, $trk);
                            if (!$insAlb->execute()) {
                                $err = $insAlb->error;
                                $insAlb->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $insAlb->close();
                    }
                    $changes['albums'] = array_map(function ($p) use ($IDHM) {
                        return ['id' => $p[0] + (int)$IDHM, 'trk' => $p[1]];
                    }, $albumRows);
                }

                // =========================================================
                // song2video — smart sync
                // =========================================================
                if (property_exists($JSD, 'videos') && is_array($JSD->videos)) {
                    $videoIds = [];
                    foreach ($JSD->videos as $v) {
                        $pid = 0;
                        if (is_object($v) && isset($v->id)) $pid = (int)$v->id;
                        elseif (is_numeric($v)) $pid = (int)$v;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $videoIds[] = $rid;
                    }
                    $videoIds = array_values(array_unique($videoIds));

                    if (!empty($videoIds)) {
                        $inList = implode(',', array_map('intval', $videoIds));
                        $sqlDel = "
                            DELETE sv FROM song2video sv
                            INNER JOIN video v ON v.id_video = sv.id_video
                            WHERE sv.id_song = " . (int)$realId . "
                              AND sv.id_video NOT IN ($inList)
                              AND v.video_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE sv FROM song2video sv
                            INNER JOIN video v ON v.id_video = sv.id_video
                            WHERE sv.id_song = " . (int)$realId . "
                              AND v.video_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $insV = $conn->prepare(
                        'INSERT IGNORE INTO song2video (id_song, id_video) VALUES (?, ?)'
                    );
                    if ($insV) {
                        foreach ($videoIds as $vid) {
                            $vid = (int)$vid;
                            $insV->bind_param('ii', $realId, $vid);
                            $insV->execute();
                        }
                        $insV->close();
                    }
                    $changes['videos'] = $videoIds;
                }

                // =========================================================
                // link_song sources — only when key present
                // =========================================================
                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }

                if ($callerAct >= 7 && property_exists($JSD, 'sources') && is_array($JSD->sources)) {
                    $sourcesIn  = $JSD->sources;
                    $keptIds    = [];
                    $sourcesOut = [];
                    foreach ($sourcesIn as $S) {
                        if (!is_object($S)) continue;
                        $url = isset($S->url) ? trim((string)$S->url) : '';
                        if ($url === '') continue;
                        $bit = isset($S->bit) ? (int)$S->bit : 0;
                        $act = SourceActFromPayload($S);
                        // act 3 = locked — only act9 may set/keep via payload
                        if ($act === 3 && $callerAct < 9) $act = 1;
                        $rowId = isset($S->id) ? PublicIdToReal((int)$S->id, $IDHM) : 0;

                        if ($callerAct >= 9) {
                            $ownerPub = isset($S->user) ? (int)$S->user : 0;
                            $owner = $ownerPub > 0 ? PublicIdToReal($ownerPub, $IDHM) : $callerId;
                        } else {
                            $owner = $callerId;
                        }

                        if ($rowId > 0) {
                            if ($callerAct < 9) {
                                $chk = $conn->prepare(
                                    'SELECT id_user FROM link_song WHERE id_sng2lnk = ? AND id_song = ? LIMIT 1'
                                );
                                $chk->bind_param('ii', $rowId, $realId);
                                $chk->execute();
                                $own = $chk->get_result()->fetch_assoc();
                                $chk->close();
                                if (!$own || (int)$own['id_user'] !== $callerId) continue;
                            }
                            $updL = $conn->prepare(
                                'UPDATE link_song
                                 SET song_link = ?, song_bit = ?, id_user = ?, link_act = ?
                                 WHERE id_sng2lnk = ? AND id_song = ? LIMIT 1'
                            );
                            if ($updL) {
                                $updL->bind_param('siiiii', $url, $bit, $owner, $act, $rowId, $realId);
                                $updL->execute();
                                $updL->close();
                            }
                            $keptIds[] = $rowId;
                            $sourcesOut[] = [
                                'id' => $rowId + (int)$IDHM, 'url' => $url, 'bit' => $bit,
                                'user' => $owner > 0 ? $owner + (int)$IDHM : 0, 'act' => $act, 'type' => SourceActToType($act),
                            ];
                        } else {
                            $insL = $conn->prepare(
                                'INSERT INTO link_song (id_user, id_song, song_link, song_bit, file_id, file_unq, link_act)
                                 VALUES (?, ?, ?, ?, \'\', \'\', ?)'
                            );
                            if ($insL) {
                                $insL->bind_param('iisii', $owner, $realId, $url, $bit, $act);
                                $insL->execute();
                                $newId = (int)$conn->insert_id;
                                $insL->close();
                                if ($newId > 0) {
                                    $keptIds[] = $newId;
                                    $sourcesOut[] = [
                                        'id' => $newId + (int)$IDHM, 'url' => $url, 'bit' => $bit,
                                        'user' => $owner > 0 ? $owner + (int)$IDHM : 0, 'act' => $act, 'type' => SourceActToType($act),
                                    ];
                                }
                            }
                        }
                    }

                    if ($callerAct >= 9) {
                        if (!empty($keptIds)) {
                            $in = implode(',', array_map('intval', $keptIds));
                            $conn->query(
                                "UPDATE link_song SET link_act = 0
                                 WHERE id_song = " . (int)$realId . "
                                   AND link_act != 0
                                   AND id_sng2lnk NOT IN ($in)"
                            );
                        } else {
                            $conn->query(
                                "UPDATE link_song SET link_act = 0
                                 WHERE id_song = " . (int)$realId . " AND link_act != 0"
                            );
                        }
                    } else {
                        if (!empty($keptIds)) {
                            $in = implode(',', array_map('intval', $keptIds));
                            $conn->query(
                                "UPDATE link_song SET link_act = 0
                                 WHERE id_song = " . (int)$realId . "
                                   AND id_user = " . (int)$callerId . "
                                   AND link_act != 0
                                   AND id_sng2lnk NOT IN ($in)"
                            );
                        } else {
                            $conn->query(
                                "UPDATE link_song SET link_act = 0
                                 WHERE id_song = " . (int)$realId . "
                                   AND id_user = " . (int)$callerId . "
                                   AND link_act != 0"
                            );
                        }
                    }
                    $changes['sources'] = $sourcesOut;
                }

                // =========================================================
                // item2genre — only when gnr present
                // =========================================================
                if (property_exists($JSD, 'gnr') && is_array($JSD->gnr)) {
                    $genreIds = [];
                    foreach ($JSD->gnr as $g) {
                        $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                        if ($gid > 0) $genreIds[] = $gid;
                    }
                    $genreIds = array_values(array_unique($genreIds));

                    $delG = $conn->prepare(
                        'DELETE FROM item2genre WHERE item_id = ? AND item_type IN (?, ?)'
                    );
                    if ($delG) {
                        $delG->bind_param('iii', $realId, $typeSong, $typePodcast);
                        $delG->execute();
                        $delG->close();
                    }
                    if (!empty($genreIds)) {
                        $insG = $conn->prepare(
                            'INSERT INTO item2genre (item_type, item_id, id_genre) VALUES (?, ?, ?)'
                        );
                        if ($insG) {
                            foreach ($genreIds as $gid) {
                                $insG->bind_param('iii', $typeInt, $realId, $gid);
                                $insG->execute();
                            }
                            $insG->close();
                        }
                    }
                    $changes['genres'] = $genreIds;
                }

                // =========================================================
                // item2lang — only when lan / languages present
                // =========================================================
                if (property_exists($JSD, 'lan') || property_exists($JSD, 'languages')) {
                    $langCodes = [];
                    if (property_exists($JSD, 'lan')) {
                        $lan = strtolower(trim((string)$JSD->lan));
                        if ($lan !== '' && preg_match('/^[a-z]{2,8}$/', $lan)) {
                            $langCodes[] = $lan;
                        }
                    }
                    if (isset($JSD->languages) && is_array($JSD->languages)) {
                        foreach ($JSD->languages as $c) {
                            $c = strtolower(trim((string)$c));
                            if ($c !== '' && preg_match('/^[a-z]{2,8}$/', $c)) {
                                $langCodes[] = $c;
                            }
                        }
                    }
                    $langCodes = array_values(array_unique($langCodes));

                    $delL = $conn->prepare(
                        'DELETE FROM item2lang WHERE item_id = ? AND item_type IN (?, ?)'
                    );
                    if ($delL) {
                        $delL->bind_param('iii', $realId, $typeSong, $typePodcast);
                        $delL->execute();
                        $delL->close();
                    }
                    if (!empty($langCodes)) {
                        $insL = $conn->prepare(
                            'INSERT INTO item2lang (item_type, item_id, lang_code) VALUES (?, ?, ?)'
                        );
                        if ($insL) {
                            foreach ($langCodes as $code) {
                                $insL->bind_param('iis', $typeInt, $realId, $code);
                                $insL->execute();
                            }
                            $insL->close();
                        }
                    }
                    $changes['languages'] = $langCodes;
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'updated' => true,
                    'table'   => 'song',
                    'type'    => $type,
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => $changes,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- album ----
            if ($table === 'album') {
                $conn->begin_transaction();
                try {

                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'invalid_id']);
                    exit();
                }

                $stmt = $conn->prepare(
                    'SELECT id_album, album_name, album_data, album_rd, album_act
                     FROM album WHERE id_album = ? LIMIT 1'
                );
                if (!$stmt) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $stmt->bind_param('i', $realId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$row) {
                    $conn->rollback();
                    echo json_encode(['error' => 'not_found']);
                    exit();
                }

                $album_name = trim($name);

                // album_rd — keep existing unless client sent the key
                $album_rd = $row['album_rd'];
                if (property_exists($JSD, 'rd')) {
                    if ($JSD->rd === null || $JSD->rd === '') {
                        $album_rd = null;
                    } else {
                        $album_rd = null;
                        $rd = trim((string)$JSD->rd);
                        if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $rd, $rm)) {
                            $y = $rm[1];
                            $m = isset($rm[2]) ? $rm[2] : '01';
                            $d = isset($rm[3]) ? $rm[3] : '01';
                            if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                                $album_rd = $y . '-' . $m . '-' . $d;
                            }
                        }
                    }
                }

                $album_act = isset($JSD->act) ? (int)$JSD->act : (int)$row['album_act'];
                if ($album_act < 0 || $album_act > 3) $album_act = 1;

                $data = [];
                if (!empty($row['album_data'])) {
                    $decoded = json_decode($row['album_data'], true);
                    if (is_array($decoded)) $data = $decoded;
                }
                unset($data['gnr'], $data['lan']);

                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if (!empty($names)) $data['names'] = $names;
                    else unset($data['names']);
                }

                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                    else unset($data['des']);
                }

                // media — only when key present
                $mediaOut = [];
                if (property_exists($JSD, 'media') && is_array($JSD->media)) {
                    unset($data['tmb'], $data['tmv'], $data['cvr'], $data['art'], $data['gal']);
                    $uniqueRoles = [];
                    $galRows = [];
                    foreach ($JSD->media as $m) {
                        if (!is_object($m)) continue;
                        $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                        $src  = isset($m->url)  ? trim((string)$m->url)  : '';
                        if ($role === 'off' || $src === '') continue;
                        if ($role === 'gal') { $galRows[] = $m; continue; }
                        if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                        $uniqueRoles[$role] = $m;
                    }
                    $galIndex = 0;
                    foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                        if (!isset($uniqueRoles[$role])) continue;
                        $src = trim((string)$uniqueRoles[$role]->url);
                        $act = MediaRoleToAct($role);
                        if ($act < 1) continue;
                        $id_img = MediaPlanBKey('album', $pubId, $role, 0);
                        $saved  = IngestThumbFromSource($conn, $src, $id_img, $act);
                        if ($saved === '') {
                            $mediaOut[] = ['role' => $role, 'error' => 'ingest_failed'];
                            continue;
                        }
                        $data[$role] = $saved;
                        $mediaOut[]  = ['role' => $role, 'id_img' => $saved];
                    }
                    if (!empty($galRows)) {
                        $data['gal'] = [];
                        foreach ($galRows as $m) {
                            $src = trim((string)$m->url);
                            $act = MediaRoleToAct('gal');
                            $id_img = MediaPlanBKey('album', $pubId, 'gal', $galIndex++);
                            $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                            if ($saved === '') {
                                $mediaOut[] = ['role' => 'gal', 'error' => 'ingest_failed'];
                                continue;
                            }
                            $data['gal'][] = $saved;
                            $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                        }
                        if (empty($data['gal'])) unset($data['gal']);
                    }
                }

                $album_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                if ($album_data_json === false) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => 'album_data json_encode failed']);
                    exit();
                }

                if ($album_rd === null) {
                    $upd = $conn->prepare(
                        'UPDATE album SET album_name = ?, album_data = ?, album_rd = NULL, album_act = ?
                         WHERE id_album = ? LIMIT 1'
                    );
                    if (!$upd) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $upd->bind_param('ssii', $album_name, $album_data_json, $album_act, $realId);
                } else {
                    $upd = $conn->prepare(
                        'UPDATE album SET album_name = ?, album_data = ?, album_rd = ?, album_act = ?
                         WHERE id_album = ? LIMIT 1'
                    );
                    if (!$upd) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $upd->bind_param('sssii', $album_name, $album_data_json, $album_rd, $album_act, $realId);
                }
                if (!$upd->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $upd->error]);
                    $upd->close();
                    exit();
                }
                $upd->close();

                $changes = [
                    'name'  => $album_name,
                    'rd'    => $album_rd,
                    'act'   => $album_act,
                    'media' => $mediaOut,
                ];

                // album2artist — smart sync
                $artistIds = [];
                if (property_exists($JSD, 'artists') && is_array($JSD->artists)) {
                    foreach ($JSD->artists as $a) {
                        $pid = is_object($a) ? (isset($a->id) ? (int)$a->id : 0) : (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $artistIds[] = $rid;
                    }
                    $artistIds = array_values(array_unique($artistIds));

                    if (!empty($artistIds)) {
                        $inList = implode(',', array_map('intval', $artistIds));
                        $sqlDel = "
                            DELETE aa FROM album2artist aa
                            INNER JOIN artist a ON a.id_artist = aa.id_artist
                            WHERE aa.id_album = " . (int)$realId . "
                              AND aa.id_artist NOT IN ($inList)
                              AND a.artist_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE aa FROM album2artist aa
                            INNER JOIN artist a ON a.id_artist = aa.id_artist
                            WHERE aa.id_album = " . (int)$realId . "
                              AND a.artist_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $insA = $conn->prepare(
                        'INSERT IGNORE INTO album2artist (id_album, id_artist) VALUES (?, ?)'
                    );
                    if ($insA) {
                        foreach ($artistIds as $aid) {
                            $aid = (int)$aid;
                            $insA->bind_param('ii', $realId, $aid);
                            if (!$insA->execute()) {
                                $err = $insA->error;
                                $insA->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $insA->close();
                    }
                    $changes['artists'] = $artistIds;
                }

                // song2album tracks — smart sync
                $desired = [];
                if (property_exists($JSD, 'tracks') && is_array($JSD->tracks)) {
                    $i = 1;
                    foreach ($JSD->tracks as $t) {
                        if (!is_object($t)) continue;
                        $pid = isset($t->id) ? (int)$t->id : 0;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid <= 0) continue;
                        $trk = isset($t->trk) ? (int)$t->trk : 0;
                        if ($trk < 0) $trk = 0;
                        $desired[$rid] = ($trk > 0) ? $trk : $i;
                        $i++;
                    }

                    if (!empty($desired)) {
                        $inList = implode(',', array_map('intval', array_keys($desired)));
                        $sqlDel = "
                            DELETE sa FROM song2album sa
                            INNER JOIN song s ON s.id_song = sa.id_song
                            WHERE sa.id_album = " . (int)$realId . "
                              AND sa.id_song NOT IN ($inList)
                              AND s.song_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE sa FROM song2album sa
                            INNER JOIN song s ON s.id_song = sa.id_song
                            WHERE sa.id_album = " . (int)$realId . "
                              AND s.song_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $insT = $conn->prepare(
                        'INSERT INTO song2album (id_song, id_album, trk_num)
                         VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE trk_num = VALUES(trk_num)'
                    );
                    if ($insT) {
                        foreach ($desired as $sid => $trk) {
                            $sid = (int)$sid;
                            $trk = (int)$trk;
                            $insT->bind_param('iii', $sid, $realId, $trk);
                            if (!$insT->execute()) {
                                $err = $insT->error;
                                $insT->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $insT->close();
                    } else {
                        foreach ($desired as $sid => $trk) {
                            $sid = (int)$sid;
                            $trk = (int)$trk;
                            $chk = $conn->prepare(
                                'SELECT 1 FROM song2album WHERE id_song = ? AND id_album = ? LIMIT 1'
                            );
                            if (!$chk) continue;
                            $chk->bind_param('ii', $sid, $realId);
                            $chk->execute();
                            $exists = $chk->get_result()->fetch_assoc();
                            $chk->close();
                            if ($exists) {
                                $up = $conn->prepare(
                                    'UPDATE song2album SET trk_num = ? WHERE id_song = ? AND id_album = ? LIMIT 1'
                                );
                                if ($up) {
                                    $up->bind_param('iii', $trk, $sid, $realId);
                                    $up->execute();
                                    $up->close();
                                }
                            } else {
                                $ins = $conn->prepare(
                                    'INSERT INTO song2album (id_song, id_album, trk_num) VALUES (?, ?, ?)'
                                );
                                if ($ins) {
                                    $ins->bind_param('iii', $sid, $realId, $trk);
                                    $ins->execute();
                                    $ins->close();
                                }
                            }
                        }
                    }
                    $changes['tracks'] = [];
                    foreach ($desired as $sid => $trk) {
                        $changes['tracks'][] = [
                            'id'  => (int)$sid + (int)$IDHM,
                            'trk' => (int)$trk,
                        ];
                    }
                }

                $typeInt = ContentTypeToInt('album');

                // item2genre — only when gnr present
                if (property_exists($JSD, 'gnr') && is_array($JSD->gnr)) {
                    $genreIds = [];
                    foreach ($JSD->gnr as $g) {
                        $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                        if ($gid > 0) $genreIds[] = $gid;
                    }
                    $genreIds = array_values(array_unique($genreIds));

                    $delG = $conn->prepare(
                        'DELETE FROM item2genre WHERE item_id = ? AND item_type = ?'
                    );
                    if ($delG) {
                        $delG->bind_param('ii', $realId, $typeInt);
                        $delG->execute();
                        $delG->close();
                    }
                    if (!empty($genreIds)) {
                        $insG = $conn->prepare(
                            'INSERT INTO item2genre (item_type, item_id, id_genre) VALUES (?, ?, ?)'
                        );
                        if ($insG) {
                            foreach ($genreIds as $gid) {
                                $insG->bind_param('iii', $typeInt, $realId, $gid);
                                $insG->execute();
                            }
                            $insG->close();
                        }
                    }
                    $changes['genres'] = $genreIds;
                }

                // item2lang — only when lan present
                if (property_exists($JSD, 'lan')) {
                    $langCodes = [];
                    $lan = strtolower(trim((string)$JSD->lan));
                    if ($lan !== '' && preg_match('/^[a-z]{2,8}$/', $lan)) {
                        $langCodes[] = $lan;
                    }

                    $delL = $conn->prepare(
                        'DELETE FROM item2lang WHERE item_id = ? AND item_type = ?'
                    );
                    if ($delL) {
                        $delL->bind_param('ii', $realId, $typeInt);
                        $delL->execute();
                        $delL->close();
                    }
                    if (!empty($langCodes)) {
                        $insL = $conn->prepare(
                            'INSERT INTO item2lang (item_type, item_id, lang_code) VALUES (?, ?, ?)'
                        );
                        if ($insL) {
                            foreach ($langCodes as $code) {
                                $insL->bind_param('iis', $typeInt, $realId, $code);
                                $insL->execute();
                            }
                            $insL->close();
                        }
                    }
                    $changes['languages'] = $langCodes;
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'updated' => true,
                    'table'   => 'album',
                    'type'    => 'album',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => $changes,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- video / musicvideo share the video table ----
            if ($table === 'video') {
                $conn->begin_transaction();
                try {

                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'invalid_id']);
                    exit();
                }

                $stmt = $conn->prepare(
                    'SELECT id_video, video_name, video_data, video_rd, video_act
                     FROM video WHERE id_video = ? LIMIT 1'
                );
                if (!$stmt) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $stmt->bind_param('i', $realId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$row) {
                    $conn->rollback();
                    echo json_encode(['error' => 'not_found']);
                    exit();
                }

                $video_name = trim($name);

                // video_rd — keep existing unless client sent the key
                $video_rd = $row['video_rd'];
                if (property_exists($JSD, 'rd')) {
                    if ($JSD->rd === null || $JSD->rd === '') {
                        $video_rd = null;
                    } else {
                        $video_rd = null;
                        $rd = trim((string)$JSD->rd);
                        if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $rd, $rm)) {
                            $y = $rm[1];
                            $m = isset($rm[2]) ? $rm[2] : '01';
                            $d = isset($rm[3]) ? $rm[3] : '01';
                            if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                                $video_rd = $y . '-' . $m . '-' . $d;
                            }
                        }
                    }
                }

                $video_act = isset($JSD->act) ? (int)$JSD->act : (int)$row['video_act'];
                if ($video_act < 0 || $video_act > 3) $video_act = 1;

                $data = [];
                if (!empty($row['video_data'])) {
                    $decoded = json_decode($row['video_data'], true);
                    if (is_array($decoded)) $data = $decoded;
                }
                unset($data['gnr'], $data['lan']);

                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if (!empty($names)) $data['names'] = $names;
                    else unset($data['names']);
                }

                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                    else unset($data['des']);
                }

                if (isset($JSD->dur) && (int)$JSD->dur > 0) {
                    $data['dur'] = (int)$JSD->dur;
                }

                // media — only when key present
                $mediaOut = [];
                if (property_exists($JSD, 'media') && is_array($JSD->media)) {
                    unset($data['tmb'], $data['tmv'], $data['cvr'], $data['art'], $data['gal']);
                    $uniqueRoles = [];
                    $galRows = [];
                    foreach ($JSD->media as $m) {
                        if (!is_object($m)) continue;
                        $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                        $src  = isset($m->url)  ? trim((string)$m->url)  : '';
                        if ($role === 'off' || $src === '') continue;
                        if ($role === 'gal') { $galRows[] = $m; continue; }
                        if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                        $uniqueRoles[$role] = $m;
                    }
                    $galIndex = 0;
                    foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                        if (!isset($uniqueRoles[$role])) continue;
                        $src = trim((string)$uniqueRoles[$role]->url);
                        $act = MediaRoleToAct($role);
                        if ($act < 1) continue;
                        $id_img = MediaPlanBKey('video', $pubId, $role, 0);
                        $saved  = IngestThumbFromSource($conn, $src, $id_img, $act);
                        if ($saved === '') {
                            $mediaOut[] = ['role' => $role, 'error' => 'ingest_failed'];
                            continue;
                        }
                        $data[$role] = $saved;
                        $mediaOut[]  = ['role' => $role, 'id_img' => $saved];
                    }
                    if (!empty($galRows)) {
                        $data['gal'] = [];
                        foreach ($galRows as $m) {
                            $src = trim((string)$m->url);
                            $act = MediaRoleToAct('gal');
                            $id_img = MediaPlanBKey('video', $pubId, 'gal', $galIndex++);
                            $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                            if ($saved === '') {
                                $mediaOut[] = ['role' => 'gal', 'error' => 'ingest_failed'];
                                continue;
                            }
                            $data['gal'][] = $saved;
                            $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                        }
                        if (empty($data['gal'])) unset($data['gal']);
                    }
                }

                if ($type === 'video') $data['typ'] = 2;
                else unset($data['typ']);

                // lyrics — only when key present
                $lyricsOut = [];
                $typeIntLyric = ContentTypeToInt($type === 'video' ? 'video' : 'musicvideo');
                if (property_exists($JSD, 'lyrics') && is_array($JSD->lyrics)) {
                    unset($data['lrc']);
                    $keptSrc  = [];
                    $lrcIndex = [];
                    $now = time();
                    foreach ($JSD->lyrics as $L) {
                        if (!is_object($L)) continue;
                        $lng  = isset($L->lng) ? strtolower(trim((string)$L->lng)) : 'und';
                        $knd  = isset($L->knd) ? strtolower(trim((string)$L->knd)) : 'lrc';
                        $fmt  = isset($L->fmt) ? strtolower(trim((string)$L->fmt)) : 'txt';
                        $body = isset($L->body) ? (string)$L->body : '';
                        $body = str_replace(["\r\n", "\r"], "\n", $body);
                        if ($lng === '') $lng = 'und';
                        $lng = substr(preg_replace('/[^a-z]/', '', $lng), 0, 3);
                        if ($lng === '') $lng = 'und';
                        if ($knd !== 'srt') $knd = 'lrc';
                        if ($fmt !== 'jsn') $fmt = 'txt';
                        if (trim($body) === '') continue;

                        $src = isset($L->src) ? trim((string)$L->src) : '';
                        if ($src === '') {
                            $src = LyricPlanBSrc('video', $pubId, $knd, $lng);
                            if ($src === '' || $src === null) {
                                $src = 'v' . (int)$pubId . 'l_' . $lng . ($knd === 'srt' ? 's' : '');
                            }
                        }

                        $st = $conn->prepare(
                            'INSERT INTO lyric (src, item_type, item_id, lng, knd, fmt, body, updated_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE
                               item_type = VALUES(item_type),
                               item_id   = VALUES(item_id),
                               lng       = VALUES(lng),
                               knd       = VALUES(knd),
                               fmt       = VALUES(fmt),
                               body      = VALUES(body),
                               updated_at= VALUES(updated_at)'
                        );
                        if ($st) {
                            $st->bind_param(
                                'siissssi',
                                $src, $typeIntLyric, $realId, $lng, $knd, $fmt, $body, $now
                            );
                            $st->execute();
                            $st->close();
                        }
                        $keptSrc[] = $src;
                        $lrcIndex[] = ['lng' => $lng, 'knd' => $knd, 'fmt' => $fmt, 'src' => $src];
                        $lyricsOut[] = ['lng' => $lng, 'knd' => $knd, 'fmt' => $fmt, 'src' => $src, 'body' => $body];
                    }
                    if (!empty($keptSrc)) {
                        $in = "'" . implode("','", array_map(function ($s) use ($conn) {
                            return $conn->real_escape_string($s);
                        }, $keptSrc)) . "'";
                        $conn->query(
                            "DELETE FROM lyric
                             WHERE item_type = " . (int)$typeIntLyric . "
                               AND item_id   = " . (int)$realId . "
                               AND src NOT IN ($in)"
                        );
                    } else {
                        $conn->query(
                            "DELETE FROM lyric
                             WHERE item_type = " . (int)$typeIntLyric . "
                               AND item_id   = " . (int)$realId
                        );
                    }
                    if (!empty($lrcIndex)) $data['lrc'] = $lrcIndex;
                    else unset($data['lrc']);
                }

                $video_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                if ($video_data_json === false) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => 'video_data json_encode failed']);
                    exit();
                }

                if ($video_rd === null) {
                    $upd = $conn->prepare(
                        'UPDATE video SET video_name = ?, video_data = ?, video_rd = NULL, video_act = ?
                         WHERE id_video = ? LIMIT 1'
                    );
                    if (!$upd) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $upd->bind_param('ssii', $video_name, $video_data_json, $video_act, $realId);
                } else {
                    $upd = $conn->prepare(
                        'UPDATE video SET video_name = ?, video_data = ?, video_rd = ?, video_act = ?
                         WHERE id_video = ? LIMIT 1'
                    );
                    if (!$upd) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $upd->bind_param('sssii', $video_name, $video_data_json, $video_rd, $video_act, $realId);
                }
                if (!$upd->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $upd->error, 'errno' => $upd->errno]);
                    $upd->close();
                    exit();
                }
                $upd->close();

                $typeInt = ContentTypeToInt($type === 'video' ? 'video' : 'musicvideo');
                $typeMv  = ContentTypeToInt('musicvideo');
                $typeVd  = ContentTypeToInt('video');

                $changes = [
                    'name'   => $video_name,
                    'rd'     => $video_rd,
                    'act'    => $video_act,
                    'type'   => $type,
                    'media'  => $mediaOut,
                    'lyrics' => $lyricsOut,
                ];

                // video2artist — smart sync
                $artistIds = [];
                if (property_exists($JSD, 'artists') && is_array($JSD->artists)) {
                    foreach ($JSD->artists as $a) {
                        $pid = is_object($a) && isset($a->id) ? (int)$a->id : (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $artistIds[] = $rid;
                    }
                    $artistIds = array_values(array_unique($artistIds));

                    if (!empty($artistIds)) {
                        $inList = implode(',', array_map('intval', $artistIds));
                        $sqlDel = "
                            DELETE va FROM video2artist va
                            INNER JOIN artist a ON a.id_artist = va.id_artist
                            WHERE va.id_video = " . (int)$realId . "
                              AND va.id_artist NOT IN ($inList)
                              AND a.artist_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE va FROM video2artist va
                            INNER JOIN artist a ON a.id_artist = va.id_artist
                            WHERE va.id_video = " . (int)$realId . "
                              AND a.artist_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $insA = $conn->prepare(
                        'INSERT IGNORE INTO video2artist (id_video, id_artist) VALUES (?, ?)'
                    );
                    if ($insA) {
                        foreach ($artistIds as $aid) {
                            $aid = (int)$aid;
                            $insA->bind_param('ii', $realId, $aid);
                            if (!$insA->execute()) {
                                $err = $insA->error;
                                $insA->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $insA->close();
                    }
                    $changes['artists'] = $artistIds;
                }

                // song2video — smart sync (video side)
                if (property_exists($JSD, 'songs') && is_array($JSD->songs)) {
                    $songIds = [];
                    foreach ($JSD->songs as $s) {
                        $pid = is_object($s) && isset($s->id) ? (int)$s->id : (int)$s;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $songIds[] = $rid;
                    }
                    $songIds = array_values(array_unique($songIds));

                    if (!empty($songIds)) {
                        $inList = implode(',', array_map('intval', $songIds));
                        $sqlDel = "
                            DELETE sv FROM song2video sv
                            INNER JOIN song s ON s.id_song = sv.id_song
                            WHERE sv.id_video = " . (int)$realId . "
                              AND sv.id_song NOT IN ($inList)
                              AND s.song_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE sv FROM song2video sv
                            INNER JOIN song s ON s.id_song = sv.id_song
                            WHERE sv.id_video = " . (int)$realId . "
                              AND s.song_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $insS = $conn->prepare(
                        'INSERT IGNORE INTO song2video (id_song, id_video) VALUES (?, ?)'
                    );
                    if ($insS) {
                        foreach ($songIds as $sid) {
                            $sid = (int)$sid;
                            $insS->bind_param('ii', $sid, $realId);
                            $insS->execute();
                        }
                        $insS->close();
                    }
                    $changes['songs'] = $songIds;
                }

                // link_video sources — only when key present
                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }

                if ($callerAct >= 7 && property_exists($JSD, 'sources') && is_array($JSD->sources)) {
                    $sourcesIn  = $JSD->sources;
                    $keptIds    = [];
                    $sourcesOut = [];
                    foreach ($sourcesIn as $S) {
                        if (!is_object($S)) continue;
                        $url = isset($S->url) ? trim((string)$S->url) : '';
                        if ($url === '') continue;
                        $bit = isset($S->bit) ? (int)$S->bit : 0;
                        $act = SourceActFromPayload($S);
                        // act 3 = locked — only act9 may set/keep via payload
                        if ($act === 3 && $callerAct < 9) $act = 1;
                        $rowId = isset($S->id) ? PublicIdToReal((int)$S->id, $IDHM) : 0;

                        if ($callerAct >= 9) {
                            $ownerPub = isset($S->user) ? (int)$S->user : 0;
                            $owner = $ownerPub > 0 ? PublicIdToReal($ownerPub, $IDHM) : $callerId;
                        } else {
                            $owner = $callerId;
                        }

                        if ($rowId > 0) {
                            if ($callerAct < 9) {
                                $chk = $conn->prepare(
                                    'SELECT id_user FROM link_video
                                     WHERE id_vdo2lnk = ? AND id_video = ? LIMIT 1'
                                );
                                if ($chk) {
                                    $chk->bind_param('ii', $rowId, $realId);
                                    $chk->execute();
                                    $cr = $chk->get_result()->fetch_assoc();
                                    $chk->close();
                                    if (!$cr || (int)$cr['id_user'] !== (int)$callerId) continue;
                                }
                            }
                            $updL = $conn->prepare(
                                'UPDATE link_video
                                 SET video_link = ?, vdo_bit = ?, id_user = ?, link_act = ?
                                 WHERE id_vdo2lnk = ? AND id_video = ? LIMIT 1'
                            );
                            if ($updL) {
                                $updL->bind_param('siiiii', $url, $bit, $owner, $act, $rowId, $realId);
                                $updL->execute();
                                $updL->close();
                            }
                            $keptIds[] = $rowId;
                            $sourcesOut[] = [
                                'id'   => $rowId + (int)$IDHM,
                                'url'  => $url,
                                'bit'  => $bit,
                                'user' => $owner > 0 ? $owner + (int)$IDHM : 0,
                                'act'  => $act,
                                'type' => SourceActToType($act),
                            ];
                        } else {
                            $insL = $conn->prepare(
                                'INSERT INTO link_video
                                   (id_user, id_video, video_link, vdo_bit, file_id, file_unq, link_act)
                                 VALUES (?, ?, ?, ?, \'\', \'\', ?)'
                            );
                            if ($insL) {
                                $insL->bind_param('iisii', $owner, $realId, $url, $bit, $act);
                                $insL->execute();
                                $newId = (int)$conn->insert_id;
                                $insL->close();
                                if ($newId > 0) {
                                    $keptIds[] = $newId;
                                    $sourcesOut[] = [
                                        'id'   => $newId + (int)$IDHM,
                                        'url'  => $url,
                                        'bit'  => $bit,
                                        'user' => $owner > 0 ? $owner + (int)$IDHM : 0,
                                        'act'  => $act,
                                        'type' => SourceActToType($act),
                                    ];
                                }
                            }
                        }
                    }

                    if ($callerAct >= 9) {
                        if (!empty($keptIds)) {
                            $in = implode(',', array_map('intval', $keptIds));
                            $conn->query(
                                "UPDATE link_video SET link_act = 0
                                 WHERE id_video = " . (int)$realId . "
                                   AND link_act != 0
                                   AND id_vdo2lnk NOT IN ($in)"
                            );
                        } else {
                            $conn->query(
                                "UPDATE link_video SET link_act = 0
                                 WHERE id_video = " . (int)$realId . " AND link_act != 0"
                            );
                        }
                    } else {
                        if (!empty($keptIds)) {
                            $in = implode(',', array_map('intval', $keptIds));
                            $conn->query(
                                "UPDATE link_video SET link_act = 0
                                 WHERE id_video = " . (int)$realId . "
                                   AND id_user = " . (int)$callerId . "
                                   AND link_act != 0
                                   AND id_vdo2lnk NOT IN ($in)"
                            );
                        } else {
                            $conn->query(
                                "UPDATE link_video SET link_act = 0
                                 WHERE id_video = " . (int)$realId . "
                                   AND id_user = " . (int)$callerId . "
                                   AND link_act != 0"
                            );
                        }
                    }
                    $changes['sources'] = $sourcesOut;
                }

                // item2genre — only when gnr present
                if (property_exists($JSD, 'gnr') && is_array($JSD->gnr)) {
                    $genreIds = [];
                    foreach ($JSD->gnr as $g) {
                        $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                        if ($gid > 0) $genreIds[] = $gid;
                    }
                    $genreIds = array_values(array_unique($genreIds));

                    $delG = $conn->prepare(
                        'DELETE FROM item2genre WHERE item_id = ? AND item_type IN (?, ?)'
                    );
                    if ($delG) {
                        $delG->bind_param('iii', $realId, $typeMv, $typeVd);
                        $delG->execute();
                        $delG->close();
                    }
                    if (!empty($genreIds)) {
                        $insG = $conn->prepare(
                            'INSERT INTO item2genre (item_type, item_id, id_genre) VALUES (?, ?, ?)'
                        );
                        if ($insG) {
                            foreach ($genreIds as $gid) {
                                $insG->bind_param('iii', $typeInt, $realId, $gid);
                                $insG->execute();
                            }
                            $insG->close();
                        }
                    }
                    $changes['genres'] = $genreIds;
                }

                // item2lang — only when lan / languages present
                if (property_exists($JSD, 'lan') || property_exists($JSD, 'languages')) {
                    $langCodes = [];
                    if (property_exists($JSD, 'lan')) {
                        $lan = strtolower(trim((string)$JSD->lan));
                        if ($lan !== '' && preg_match('/^[a-z]{2,8}$/', $lan)) {
                            $langCodes[] = $lan;
                        }
                    }
                    if (isset($JSD->languages) && is_array($JSD->languages)) {
                        foreach ($JSD->languages as $c) {
                            $c = strtolower(trim((string)$c));
                            if ($c !== '' && preg_match('/^[a-z]{2,8}$/', $c)) {
                                $langCodes[] = $c;
                            }
                        }
                    }
                    $langCodes = array_values(array_unique($langCodes));

                    $delL = $conn->prepare(
                        'DELETE FROM item2lang WHERE item_id = ? AND item_type IN (?, ?)'
                    );
                    if ($delL) {
                        $delL->bind_param('iii', $realId, $typeMv, $typeVd);
                        $delL->execute();
                        $delL->close();
                    }
                    if (!empty($langCodes)) {
                        $insL = $conn->prepare(
                            'INSERT INTO item2lang (item_type, item_id, lang_code) VALUES (?, ?, ?)'
                        );
                        if ($insL) {
                            foreach ($langCodes as $code) {
                                $insL->bind_param('iis', $typeInt, $realId, $code);
                                $insL->execute();
                            }
                            $insL->close();
                        }
                    }
                    $changes['languages'] = $langCodes;
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'updated' => true,
                    'table'   => 'video',
                    'type'    => $type,
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => $changes,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- artist ----
            if ($table === 'artist') {
                $conn->begin_transaction();
                try {


                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'invalid_id']);
                    exit();
                }

                $stmt = $conn->prepare(
                    'SELECT id_artist, artist_name, artist_data, artist_cc, artist_bd, artist_act
                     FROM artist WHERE id_artist = ? LIMIT 1'
                );
                if (!$stmt) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $stmt->bind_param('i', $realId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$row) {
                    $conn->rollback();
                    echo json_encode(['error' => 'not_found']);
                    exit();
                }

                // ---- columns ----
                $artist_name = trim($name);

                // country: '' = None, 'GL' = Global, else ISO
                $artist_cc = null;
                if (property_exists($JSD, 'cc')) {
                    $cc = strtoupper(trim((string)$JSD->cc));
                    if ($cc === '' || $cc === 'NONE') {
                        $artist_cc = ''; // None
                    } elseif (preg_match('/^[A-Z]{2}$/', $cc) || $cc === 'GL') {
                        $artist_cc = $cc;
                    } else {
                        $artist_cc = substr($cc, 0, 8);
                    }
                } else {
                    $artist_cc = $row['artist_cc'];
                }

                // birth date — only change when key is present - birth date YYYY-MM-DD
                $artist_bd = $row['artist_bd']; // keep existing by default
                $clearBd   = false;
                if (property_exists($JSD, 'bd')) {
                    if ($JSD->bd === null || $JSD->bd === '') {
                        $artist_bd = null;
                        $clearBd   = true;
                    } else {
                        $bd = trim((string)$JSD->bd);
                        $artist_bd = null;
                        if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $bd, $rm)) {
                            $y = $rm[1];
                            $m = isset($rm[2]) ? $rm[2] : '01';
                            $d = isset($rm[3]) ? $rm[3] : '01';
                            if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                                $artist_bd = $y . '-' . $m . '-' . $d;
                            }
                        }
                        $clearBd = ($artist_bd === null); // invalid → null, or keep old if you prefer
                    }
                }
                
                $artist_act = isset($JSD->act) ? (int)$JSD->act : (int)$row['artist_act'];
                if ($artist_act < 0 || $artist_act > 3) {
                    $artist_act = 1;
                }

                // ---- artist_data ----
                $data = [];
                if (!empty($row['artist_data'])) {
                    $decoded = json_decode($row['artist_data'], true);
                    if (is_array($decoded)) $data = $decoded;
                }
                unset($data['gnr']); // genres live in artist2genre

                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if (!empty($names)) $data['names'] = $names;
                    else unset($data['names']);
                }

                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                    else unset($data['des']);
                }

                foreach (['ytm', 'ins', 'tlg'] as $soc) {
                    if (property_exists($JSD, $soc)) {
                        $v = trim((string)$JSD->$soc);
                        if ($v !== '') $data[$soc] = $v;
                        else unset($data[$soc]);
                    }
                }

                // media — only when client sent the media array
                $mediaOut = [];
                if (property_exists($JSD, 'media') && is_array($JSD->media)) {
                    unset($data['tmb'], $data['tmv'], $data['cvr'], $data['art'], $data['gal']);

                    $uniqueRoles = [];
                    $galRows = [];
                    foreach ($JSD->media as $m) {
                        if (!is_object($m)) continue;
                        $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                        $src  = isset($m->url)  ? trim((string)$m->url)  : '';
                        if ($role === 'off' || $src === '') continue;
                        if ($role === 'gal') { $galRows[] = $m; continue; }
                        if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                        $uniqueRoles[$role] = $m;
                    }

                    $galIndex = 0;
                    foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                        if (!isset($uniqueRoles[$role])) continue;
                        $src = trim((string)$uniqueRoles[$role]->url);
                        $act = MediaRoleToAct($role);
                        if ($act < 1) continue;
                        $id_img = MediaPlanBKey('artist', $pubId, $role, 0);
                        $saved  = IngestThumbFromSource($conn, $src, $id_img, $act);
                        if ($saved === '') {
                            $mediaOut[] = ['role' => $role, 'error' => 'ingest_failed'];
                            continue;
                        }
                        $data[$role] = $saved;
                        $mediaOut[]  = ['role' => $role, 'id_img' => $saved];
                    }
                    if (!empty($galRows)) {
                        $data['gal'] = [];
                        foreach ($galRows as $m) {
                            $src = trim((string)$m->url);
                            $act = MediaRoleToAct('gal');
                            $id_img = MediaPlanBKey('artist', $pubId, 'gal', $galIndex++);
                            $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                            if ($saved === '') {
                                $mediaOut[] = ['role' => 'gal', 'error' => 'ingest_failed'];
                                continue;
                            }
                            $data['gal'][] = $saved;
                            $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                        }
                        if (empty($data['gal'])) unset($data['gal']);
                    }
                }
                // if media key absent → keep existing tmb/ico/… inside $data

                $artist_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                
                if ($artist_data_json === false) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => 'artist_data json_encode failed']);
                    exit();
                }

                // UPDATE row
                if ($artist_bd === null) {
                    // explicit null (clear or never had)
                    $upd = $conn->prepare(
                        'UPDATE artist
                         SET artist_name = ?, artist_data = ?, artist_cc = ?, artist_bd = NULL, artist_act = ?
                         WHERE id_artist = ? LIMIT 1'
                    );
                    $upd->bind_param('sssii', $artist_name, $artist_data_json, $artist_cc, $artist_act, $realId);
                } else {
                    $upd = $conn->prepare(
                        'UPDATE artist
                         SET artist_name = ?, artist_data = ?, artist_cc = ?, artist_bd = ?, artist_act = ?
                         WHERE id_artist = ? LIMIT 1'
                    );
                    $upd->bind_param('ssssii', $artist_name, $artist_data_json, $artist_cc, $artist_bd, $artist_act, $realId);
                }
                if (!$upd->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $upd->error, 'errno' => $upd->errno]);
                    $upd->close();
                    exit();
                }
                $upd->close();

                $changes = [
                    'name'  => $artist_name,
                    'cc'    => $artist_cc,
                    'bd'    => $artist_bd,
                    'act'   => $artist_act,
                    'media' => $mediaOut,
                ];

                // ---- artist2genre ----
                if (property_exists($JSD, 'gnr') && is_array($JSD->gnr)) {
                    $genreIds = [];
                    foreach ($JSD->gnr as $g) {
                        $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                        if ($gid > 0) $genreIds[] = $gid;
                    }
                    $genreIds = array_values(array_unique($genreIds));

                    $conn->query('DELETE FROM artist2genre WHERE id_artist = ' . (int)$realId);
                    if (!empty($genreIds)) {
                        $insG = $conn->prepare(
                            'INSERT INTO artist2genre (id_artist, id_genre) VALUES (?, ?)'
                        );
                        if ($insG) {
                            foreach ($genreIds as $gid) {
                                $insG->bind_param('ii', $realId, $gid);
                                $insG->execute();
                            }
                            $insG->close();
                        }
                    }
                    $changes['genres'] = $genreIds;
                }

                // ---- song2artist (artist side) — smart sync ----
                // payload.songs: [{id}, ...] or [id, ...]
                // Preserves links to song_act=0 (and any song not in a partial form list
                // that is inactive). Active songs omitted from payload are removed.
                if (property_exists($JSD, 'songs') && is_array($JSD->songs)) {
                    $songIds = [];
                    foreach ($JSD->songs as $s) {
                        $pid = is_object($s) && isset($s->id) ? (int)$s->id : (int)$s;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $songIds[] = $rid;
                    }
                    $songIds = array_values(array_unique($songIds));

                    if (!empty($songIds)) {
                        $inList = implode(',', array_map('intval', $songIds));
                        $sqlDel = "
                            DELETE sa FROM song2artist sa
                            INNER JOIN song s ON s.id_song = sa.id_song
                            WHERE sa.id_artist = " . (int)$realId . "
                              AND sa.id_song NOT IN ($inList)
                              AND s.song_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        // explicit empty → only active songs unlinked
                        $sqlDel = "
                            DELETE sa FROM song2artist sa
                            INNER JOIN song s ON s.id_song = sa.id_song
                            WHERE sa.id_artist = " . (int)$realId . "
                              AND s.song_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $ins = $conn->prepare(
                        'INSERT IGNORE INTO song2artist (id_song, id_artist) VALUES (?, ?)'
                    );
                    if ($ins) {
                        foreach ($songIds as $sid) {
                            $sid = (int)$sid;
                            $ins->bind_param('ii', $sid, $realId);
                            if (!$ins->execute()) {
                                $err = $ins->error;
                                $ins->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $ins->close();
                    } else {
                        foreach ($songIds as $sid) {
                            $sid = (int)$sid;
                            $chk = $conn->prepare(
                                'SELECT 1 FROM song2artist WHERE id_song = ? AND id_artist = ? LIMIT 1'
                            );
                            if (!$chk) continue;
                            $chk->bind_param('ii', $sid, $realId);
                            $chk->execute();
                            $exists = $chk->get_result()->fetch_assoc();
                            $chk->close();
                            if (!$exists) {
                                $ins2 = $conn->prepare(
                                    'INSERT INTO song2artist (id_song, id_artist) VALUES (?, ?)'
                                );
                                if ($ins2) {
                                    $ins2->bind_param('ii', $sid, $realId);
                                    $ins2->execute();
                                    $ins2->close();
                                }
                            }
                        }
                    }
                    $changes['songs'] = $songIds;
                }

                // ---- album2artist (artist side) — smart sync ----
                if (property_exists($JSD, 'albums') && is_array($JSD->albums)) {
                    $albumIds = [];
                    foreach ($JSD->albums as $a) {
                        $pid = is_object($a) && isset($a->id) ? (int)$a->id : (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $albumIds[] = $rid;
                    }
                    $albumIds = array_values(array_unique($albumIds));

                    if (!empty($albumIds)) {
                        $inList = implode(',', array_map('intval', $albumIds));
                        $sqlDel = "
                            DELETE aa FROM album2artist aa
                            INNER JOIN album al ON al.id_album = aa.id_album
                            WHERE aa.id_artist = " . (int)$realId . "
                              AND aa.id_album NOT IN ($inList)
                              AND al.album_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE aa FROM album2artist aa
                            INNER JOIN album al ON al.id_album = aa.id_album
                            WHERE aa.id_artist = " . (int)$realId . "
                              AND al.album_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $ins = $conn->prepare(
                        'INSERT IGNORE INTO album2artist (id_album, id_artist) VALUES (?, ?)'
                    );
                    if ($ins) {
                        foreach ($albumIds as $aid) {
                            $aid = (int)$aid;
                            $ins->bind_param('ii', $aid, $realId);
                            if (!$ins->execute()) {
                                $err = $ins->error;
                                $ins->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $ins->close();
                    }
                    $changes['albums'] = $albumIds;
                }

                // ---- video2artist (artist side) — smart sync ----
                if (property_exists($JSD, 'videos') && is_array($JSD->videos)) {
                    $videoIds = [];
                    foreach ($JSD->videos as $v) {
                        $pid = is_object($v) && isset($v->id) ? (int)$v->id : (int)$v;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $videoIds[] = $rid;
                    }
                    $videoIds = array_values(array_unique($videoIds));

                    if (!empty($videoIds)) {
                        $inList = implode(',', array_map('intval', $videoIds));
                        $sqlDel = "
                            DELETE va FROM video2artist va
                            INNER JOIN video v ON v.id_video = va.id_video
                            WHERE va.id_artist = " . (int)$realId . "
                              AND va.id_video NOT IN ($inList)
                              AND v.video_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    } else {
                        $sqlDel = "
                            DELETE va FROM video2artist va
                            INNER JOIN video v ON v.id_video = va.id_video
                            WHERE va.id_artist = " . (int)$realId . "
                              AND v.video_act != 0
                        ";
                        if (!$conn->query($sqlDel)) {
                            $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $conn->error]);
                            exit();
                        }
                    }

                    $ins = $conn->prepare(
                        'INSERT IGNORE INTO video2artist (id_video, id_artist) VALUES (?, ?)'
                    );
                    if ($ins) {
                        foreach ($videoIds as $vid) {
                            $vid = (int)$vid;
                            $ins->bind_param('ii', $vid, $realId);
                            if (!$ins->execute()) {
                                $err = $ins->error;
                                $ins->close();
                                $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $err]);
                                exit();
                            }
                        }
                        $ins->close();
                    }
                    $changes['videos'] = $videoIds;
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'updated' => true,
                    'table'   => 'artist',
                    'type'    => 'artist',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => $changes,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- playlist ----
            if ($table === 'playlist') {
                $conn->begin_transaction();
                try {

                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }
                if ($callerAct < 9) {
                    $conn->rollback();
                    echo json_encode(['error' => 'forbidden', 'detail' => 'act9_required']);
                    exit();
                }

                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'invalid_id']);
                    exit();
                }

                $stmt = $conn->prepare(
                    'SELECT id_playlist, playlist_name, playlist_data, pl_act, id_user
                     FROM playlist WHERE id_playlist = ? LIMIT 1'
                );
                if (!$stmt) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $stmt->bind_param('i', $realId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$row) {
                    $conn->rollback();
                    echo json_encode(['error' => 'not_found']);
                    exit();
                }

                // only system playlists in this admin path
                if ((int)$row['pl_act'] !== 9) {
                    $conn->rollback();
                    echo json_encode(['error' => 'forbidden', 'detail' => 'not_system_playlist']);
                    exit();
                }

                $playlist_name = trim($name);
                $now = time();

                $data = [];
                if (!empty($row['playlist_data'])) {
                    $decoded = json_decode($row['playlist_data'], true);
                    if (is_array($decoded)) $data = $decoded;
                }
                unset($data['gnr'], $data['lan']);

                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if ($names) $data['names'] = $names;
                    else unset($data['names']);
                }

                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                    else unset($data['des']);
                }

                // media — only when key present
                $mediaOut = [];
                if (property_exists($JSD, 'media') && is_array($JSD->media)) {
                    unset($data['tmb'], $data['tmv'], $data['cvr'], $data['art'], $data['gal']);
                    $uniqueRoles = [];
                    $galRows = [];
                    foreach ($JSD->media as $m) {
                        if (!is_object($m)) continue;
                        $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                        $src  = isset($m->url) ? trim((string)$m->url) : '';
                        if ($role === 'off' || $src === '') continue;
                        if ($role === 'gal') { $galRows[] = $m; continue; }
                        if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                        $uniqueRoles[$role] = $m;
                    }
                    $galIndex = 0;
                    foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                        if (!isset($uniqueRoles[$role])) continue;
                        $src = trim((string)$uniqueRoles[$role]->url);
                        $actM = MediaRoleToAct($role);
                        if ($actM < 1) continue;
                        $id_img = MediaPlanBKey('playlist', $pubId, $role, 0);
                        $saved = IngestThumbFromSource($conn, $src, $id_img, $actM);
                        if ($saved === '') {
                            $mediaOut[] = ['role' => $role, 'error' => 'ingest_failed'];
                            continue;
                        }
                        $data[$role] = $saved;
                        $mediaOut[] = ['role' => $role, 'id_img' => $saved];
                    }
                    if (!empty($galRows)) {
                        $data['gal'] = [];
                        foreach ($galRows as $m) {
                            $src = trim((string)$m->url);
                            $id_img = MediaPlanBKey('playlist', $pubId, 'gal', $galIndex++);
                            $saved = IngestThumbFromSource($conn, $src, $id_img, MediaRoleToAct('gal'));
                            if ($saved === '') {
                                $mediaOut[] = ['role' => 'gal', 'error' => 'ingest_failed'];
                                continue;
                            }
                            $data['gal'][] = $saved;
                            $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                        }
                        if (empty($data['gal'])) unset($data['gal']);
                    }
                }

                // items — full replace only when key present
                // (playlist_items has no act flag; form must send the complete list)
                $itemCount = isset($data['nci']) ? (int)$data['nci'] : 0;
                if (property_exists($JSD, 'items') && is_array($JSD->items)) {
                    $conn->query('DELETE FROM playlist_items WHERE id_playlist = ' . (int)$realId);
                    $pos = 1;
                    $itemCount = 0;
                    $insI = $conn->prepare(
                        'INSERT INTO playlist_items (id_playlist, item_type, item_id, position, added_at)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    if ($insI) {
                        foreach ($JSD->items as $it) {
                            if (!is_object($it)) continue;
                            $pid = isset($it->id) ? (int)$it->id : 0;
                            $typeStr = isset($it->type) ? strtolower(trim((string)$it->type)) : 'song';
                            $typeInt = ContentTypeToInt($typeStr);
                            if ($typeInt === null || $typeInt > 3) continue;
                            $rid = PublicIdToReal($pid, $IDHM);
                            if ($rid <= 0) continue;
                            $insI->bind_param('iiiii', $realId, $typeInt, $rid, $pos, $now);
                            if ($insI->execute()) {
                                $pos++;
                                $itemCount++;
                            }
                        }
                        $insI->close();
                    }
                    $data['nci'] = $itemCount;
                }

                $playlist_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                if ($playlist_data_json === false) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => 'playlist_data json_encode failed']);
                    exit();
                }

                $upd = $conn->prepare(
                    'UPDATE playlist
                     SET playlist_name = ?, playlist_data = ?, updated_at = ?, pl_act = 9
                     WHERE id_playlist = ? LIMIT 1'
                );
                if (!$upd) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $upd->bind_param('ssii', $playlist_name, $playlist_data_json, $now, $realId);
                if (!$upd->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $upd->error]);
                    $upd->close();
                    exit();
                }
                $upd->close();

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'updated' => true,
                    'table'   => 'playlist',
                    'id'      => $pubId,
                    'changes' => [
                        'name'  => $playlist_name,
                        'media' => $mediaOut,
                        'items' => $itemCount,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- user ----
            if ($table === 'user') {
                $conn->begin_transaction();
                try {

                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }
                if ($callerAct < 9) {
                    $conn->rollback();
                    echo json_encode(['error' => 'forbidden', 'detail' => 'act9_required']);
                    exit();
                }

                $realId = PublicIdToReal($pubId, $IDHM);
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'invalid_id']);
                    exit();
                }

                $stmt = $conn->prepare(
                    'SELECT id_user, u_name, u_email, f_name, l_name, id_tlg, u_ref, act, u_data
                     FROM user WHERE id_user = ? LIMIT 1'
                );
                if (!$stmt) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $stmt->bind_param('i', $realId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$row) {
                    $conn->rollback();
                    echo json_encode(['error' => 'not_found']);
                    exit();
                }

                // ---- identity: keep existing unless client sent the key ----
                $u_name = trim((string)($row['u_name'] ?? ''));
                if (property_exists($JSD, 'name')) {
                    $u_name = trim((string)$JSD->name);
                }

                $email = $row['u_email']; // may be null
                if (property_exists($JSD, 'email')) {
                    $email = trim((string)$JSD->email);
                    if ($email === '') $email = null; // UNIQUE-safe
                }

                $id_tlg = (int)($row['id_tlg'] ?? 0);
                if (property_exists($JSD, 'tlg')) {
                    if ($JSD->tlg === null || $JSD->tlg === '') {
                        $id_tlg = 0;
                    } else {
                        $id_tlg = (int)preg_replace('/\D+/', '', (string)$JSD->tlg);
                    }
                }

                // Safety net if name cleared: email → telegram id
                if ($u_name === '') {
                    if ($email !== null && $email !== '') {
                        $u_name = $email;
                    } elseif ($id_tlg > 0) {
                        $u_name = (string)$id_tlg;
                    }
                }
                if ($u_name === '') {
                    $conn->rollback();
                    echo json_encode(['error' => 'identity_required', 'detail' => 'need name or email or telegram']);
                    exit();
                }

                // f_name = site URL for owners · first name for users
                $f_name = trim((string)($row['f_name'] ?? ''));
                if (property_exists($JSD, 'url')) {
                    $f_name = trim((string)$JSD->url);
                }

                $l_name = trim((string)($row['l_name'] ?? ''));
                if (property_exists($JSD, 'other')) {
                    $l_name = trim((string)$JSD->other);
                }

                $u_ref = trim((string)($row['u_ref'] ?? ''));
                if (property_exists($JSD, 'ref')) {
                    $u_ref = trim((string)$JSD->ref);
                }

                $act = (int)($row['act'] ?? 1);
                if (property_exists($JSD, 'act')) {
                    $act = (int)$JSD->act;
                    if ($act < 0 || $act > 9) $act = 1;
                }

                // password only when non-empty
                $passHash = null;
                if (property_exists($JSD, 'pass') && (string)$JSD->pass !== '') {
                    if (strlen((string)$JSD->pass) < 6) {
                        $conn->rollback();
                        echo json_encode(['error' => 'password_too_short']);
                        exit();
                    }
                    $passHash = password_hash((string)$JSD->pass, PASSWORD_DEFAULT);
                }

                // uniqueness when identity fields change
                if ($u_name !== '' && $u_name !== (string)($row['u_name'] ?? '')) {
                    $chk = $conn->prepare(
                        'SELECT id_user FROM user WHERE u_name = ? AND id_user != ? LIMIT 1'
                    );
                    if ($chk) {
                        $chk->bind_param('si', $u_name, $realId);
                        $chk->execute();
                        if ($chk->get_result()->fetch_assoc()) {
                            $chk->close();
                            $conn->rollback();
                            echo json_encode(['error' => 'duplicate', 'detail' => 'u_name']);
                            exit();
                        }
                        $chk->close();
                    }
                }
                if ($email !== null && $email !== '' && $email !== (string)($row['u_email'] ?? '')) {
                    $chk = $conn->prepare(
                        'SELECT id_user FROM user WHERE u_email = ? AND id_user != ? LIMIT 1'
                    );
                    if ($chk) {
                        $chk->bind_param('si', $email, $realId);
                        $chk->execute();
                        if ($chk->get_result()->fetch_assoc()) {
                            $chk->close();
                            $conn->rollback();
                            echo json_encode(['error' => 'duplicate', 'detail' => 'u_email']);
                            exit();
                        }
                        $chk->close();
                    }
                }

                // u_data base
                $data = [];
                if (!empty($row['u_data'])) {
                    $decoded = json_decode($row['u_data'], true);
                    if (is_array($decoded)) $data = $decoded;
                }

                $mediaOut = [];
                // media only when client sent the array
                if (property_exists($JSD, 'media') && is_array($JSD->media)) {
                    $byRole = [];
                    foreach ($JSD->media as $m) {
                        if (!is_object($m)) continue;
                        $role = isset($m->role) ? strtolower(trim((string)$m->role)) : '';
                        $src  = isset($m->url)  ? trim((string)$m->url) : '';
                        if ($role === 'off' || $role === '' || $src === '') continue;
                        if (!in_array($role, ['tmb', 'ico', 'photo'], true)) continue;
                        $byRole[$role] = $src;
                    }
                    unset($data['tmb'], $data['ico'], $data['photo']);

                    if (!empty($byRole['tmb'])) {
                        $id_img = MediaPlanBKey('user', $pubId, 'tmb', 0);
                        $saved  = IngestThumbFromSource($conn, $byRole['tmb'], $id_img, MediaRoleToAct('tmb'));
                        if ($saved !== '') {
                            $data['tmb'] = $saved;
                            $mediaOut[] = ['role' => 'tmb', 'id_img' => $saved];
                        }
                    }
                    if (!empty($byRole['ico'])) {
                        $id_img = MediaPlanBKey('user', $pubId, 'ico', 0);
                        $saved  = IngestThumbFromSource($conn, $byRole['ico'], $id_img, MediaRoleToAct('ico'));
                        if ($saved !== '') {
                            $data['ico'] = $saved;
                            $mediaOut[] = ['role' => 'ico', 'id_img' => $saved];
                        }
                    }
                    if (!empty($byRole['photo']) && preg_match('#^https?://#i', $byRole['photo'])) {
                        $data['photo'] = $byRole['photo'];
                        $mediaOut[] = ['role' => 'photo', 'url' => $byRole['photo']];
                    }
                }

                $u_data = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                // UPDATE — correct bind_param types:
                // u_name s, email s|NULL, f_name s, l_name s, id_tlg i, u_ref s, act i, u_data s, [u_pass s], id i
                $ok  = false;
                $err = '';
                if ($email === null) {
                    if ($passHash !== null) {
                        $upd = $conn->prepare(
                            "UPDATE user SET
                                u_name = ?, u_email = NULL, f_name = ?, l_name = ?,
                                id_tlg = ?, u_ref = ?, act = ?, u_data = ?, u_pass = ?
                             WHERE id_user = ? LIMIT 1"
                        );
                        // s s s i s i s s i  → 9 params
                        if ($upd) {
                            $upd->bind_param(
                                'sssisissi',
                                $u_name, $f_name, $l_name,
                                $id_tlg, $u_ref, $act, $u_data, $passHash, $realId
                            );
                            $ok = $upd->execute();
                            if (!$ok) $err = $upd->error;
                            $upd->close();
                        }
                    } else {
                        $upd = $conn->prepare(
                            "UPDATE user SET
                                u_name = ?, u_email = NULL, f_name = ?, l_name = ?,
                                id_tlg = ?, u_ref = ?, act = ?, u_data = ?
                             WHERE id_user = ? LIMIT 1"
                        );
                        // s s s i s i s i  → 8 params
                        if ($upd) {
                            $upd->bind_param(
                                'sssisisi',
                                $u_name, $f_name, $l_name,
                                $id_tlg, $u_ref, $act, $u_data, $realId
                            );
                            $ok = $upd->execute();
                            if (!$ok) $err = $upd->error;
                            $upd->close();
                        }
                    }
                } else {
                    if ($passHash !== null) {
                        $upd = $conn->prepare(
                            "UPDATE user SET
                                u_name = ?, u_email = ?, f_name = ?, l_name = ?,
                                id_tlg = ?, u_ref = ?, act = ?, u_data = ?, u_pass = ?
                             WHERE id_user = ? LIMIT 1"
                        );
                        // s s s s i s i s s i  → 10 params
                        if ($upd) {
                            $upd->bind_param(
                                'ssssisissi',
                                $u_name, $email, $f_name, $l_name,
                                $id_tlg, $u_ref, $act, $u_data, $passHash, $realId
                            );
                            $ok = $upd->execute();
                            if (!$ok) $err = $upd->error;
                            $upd->close();
                        }
                    } else {
                        $upd = $conn->prepare(
                            "UPDATE user SET
                                u_name = ?, u_email = ?, f_name = ?, l_name = ?,
                                id_tlg = ?, u_ref = ?, act = ?, u_data = ?
                             WHERE id_user = ? LIMIT 1"
                        );
                        // s s s s i s i s i  → 9 params
                        if ($upd) {
                            $upd->bind_param(
                                'ssssisisi',
                                $u_name, $email, $f_name, $l_name,
                                $id_tlg, $u_ref, $act, $u_data, $realId
                            );
                            $ok = $upd->execute();
                            if (!$ok) $err = $upd->error;
                            $upd->close();
                        }
                    }
                }

                if (!$ok) {
                    $conn->rollback();
                    echo json_encode([
                        'error'  => 'update_failed',
                        'detail' => $err !== '' ? $err : $conn->error,
                    ]);
                    exit();
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'updated' => true,
                    'table'   => 'user',
                    'id'      => $pubId,
                    'changes' => [
                        'name'  => $u_name,
                        'email' => $email,
                        'url'   => $f_name,
                        'other' => $l_name,
                        'tlg'   => $id_tlg,
                        'ref'   => $u_ref,
                        'act'   => $act,
                        'media' => $mediaOut,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'update_failed', 'detail' => $e->getMessage()]);
                    exit();
                }
            }
        }
        
        if ($ACT === 'create_item') {

            $table = isset($JSD->table) ? strtolower(trim((string)$JSD->table)) : '';
            $type  = isset($JSD->type)  ? strtolower(trim((string)$JSD->type))  : $table;
            $name  = isset($JSD->name)  ? trim((string)$JSD->name) : '';
            if ($name === '') {
                echo json_encode(['error' => 'name_required', 'detail' => 'global name is mandatory']);
                exit();
            }
            if ($table === '') {
                echo json_encode(['error' => 'bad_payload', 'detail' => 'table is required']);
                exit();
            }

            // ---- song / podcast ----
            if ($table === 'song') {
                $conn->begin_transaction();
                try {


                $song_name = $name; // exact case

                $song_rd = null;
                if (isset($JSD->rd) && $JSD->rd !== null && $JSD->rd !== '') {
                    $rd = trim((string)$JSD->rd);
                    if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $rd, $rm)) {
                        $y = $rm[1];
                        $m = isset($rm[2]) ? $rm[2] : '01';
                        $d = isset($rm[3]) ? $rm[3] : '01';
                        if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                            $song_rd = $y . '-' . $m . '-' . $d;
                        }
                    }
                }

                $song_act = isset($JSD->act) ? (int)$JSD->act : 1;
                if ($song_act < 0 || $song_act > 3) $song_act = 1;

                $data = [];
                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if ($names) $data['names'] = $names;
                }
                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                }
                if (isset($JSD->dur) && (int)$JSD->dur > 0) {
                    $data['dur'] = (int)$JSD->dur;
                }
                if ($type === 'podcast') {
                    $data['typ'] = 2;
                }

                $song_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                if ($song_rd === null) {
                    $ins = $conn->prepare(
                        'INSERT INTO song (song_name, song_data, song_rd, song_act, song_ply)
                         VALUES (?, ?, NULL, ?, 0)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('ssi', $song_name, $song_data_json, $song_act);
                } else {
                    $ins = $conn->prepare(
                        'INSERT INTO song (song_name, song_data, song_rd, song_act, song_ply)
                         VALUES (?, ?, ?, ?, 0)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('sssi', $song_name, $song_data_json, $song_rd, $song_act);
                }

                if (!$ins->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $ins->error, 'errno' => $ins->errno]);
                    $ins->close();
                    exit();
                }
                $realId = (int)$conn->insert_id;
                $ins->close();
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => 'no insert_id']);
                    exit();
                }
                $pubId = $realId + (int)$IDHM;

                // Re-use the same relation / media / lyric logic as update by
                // setting id on JSD and falling through is heavy; instead call
                // a thin second pass: run update_item path after stuffing id.
                // Simplest: return created id, client can immediately re-submit
                // as update for relations — OR duplicate relation blocks here.
                //
                // Minimal first version: row only + return id.
                // Client form already sends artists/media/… — fill them now:

                $JSD->id = $pubId;
                // Media (same as update song — last-wins)
                $mediaIn = (isset($JSD->media) && is_array($JSD->media)) ? $JSD->media : [];
                $uniqueRoles = [];
                $galRows = [];
                foreach ($mediaIn as $m) {
                    if (!is_object($m)) continue;
                    $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                    $src  = isset($m->url) ? trim((string)$m->url) : '';
                    if ($role === 'off' || $src === '') continue;
                    if ($role === 'gal') { $galRows[] = $m; continue; }
                    if (!in_array($role, ['tmb','tmv','cvr','art'], true)) $role = 'tmb';
                    $uniqueRoles[$role] = $m;
                }
                $mediaOut = [];
                $galIndex = 0;
                foreach (['tmb','tmv','cvr','art'] as $role) {
                    if (!isset($uniqueRoles[$role])) continue;
                    $src = trim((string)$uniqueRoles[$role]->url);
                    $act = MediaRoleToAct($role);
                    if ($act < 1) continue;
                    $id_img = MediaPlanBKey('song', $pubId, $role, 0);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                    if ($saved === '') continue;
                    $data[$role] = $saved;
                    $mediaOut[] = ['role' => $role, 'id_img' => $saved];
                }
                foreach ($galRows as $m) {
                    $src = trim((string)$m->url);
                    $id_img = MediaPlanBKey('song', $pubId, 'gal', $galIndex++);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, MediaRoleToAct('gal'));
                    if ($saved === '') continue;
                    if (!isset($data['gal'])) $data['gal'] = [];
                    $data['gal'][] = $saved;
                    $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                }
                if ($mediaOut) {
                    $song_data_json = json_encode(
                        !empty($data) ? $data : new stdClass(),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                    $u = $conn->prepare('UPDATE song SET song_data = ? WHERE id_song = ? LIMIT 1');
                    if ($u) {
                        $u->bind_param('si', $song_data_json, $realId);
                        $u->execute();
                        $u->close();
                    }
                }

                // artists
                $artistIds = [];
                if (isset($JSD->artists) && is_array($JSD->artists)) {
                    foreach ($JSD->artists as $a) {
                        $pid = is_object($a) && isset($a->id) ? (int)$a->id : (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $artistIds[] = $rid;
                    }
                    $artistIds = array_values(array_unique($artistIds));
                }
                if ($artistIds) {
                    $insA = $conn->prepare('INSERT IGNORE INTO song2artist (id_song, id_artist) VALUES (?, ?)');
                    if ($insA) {
                        foreach ($artistIds as $aid) {
                            $insA->bind_param('ii', $realId, $aid);
                            $insA->execute();
                        }
                        $insA->close();
                    }
                }

                // genres / langs (same typeInt as update)
                $typeInt = ContentTypeToInt($type === 'podcast' ? 'podcast' : 'song');
                if (isset($JSD->gnr) && is_array($JSD->gnr)) {
                    $insG = $conn->prepare(
                        'INSERT IGNORE INTO item2genre (item_type, item_id, id_genre) VALUES (?, ?, ?)'
                    );
                    if ($insG) {
                        foreach ($JSD->gnr as $g) {
                            $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                            if ($gid > 0) {
                                $insG->bind_param('iii', $typeInt, $realId, $gid);
                                $insG->execute();
                            }
                        }
                        $insG->close();
                    }
                }
                $lan = property_exists($JSD, 'lan') ? strtolower(trim((string)$JSD->lan)) : '';
                if ($lan !== '' && preg_match('/^[a-z]{2,8}$/', $lan)) {
                    $insL = $conn->prepare(
                        'INSERT IGNORE INTO item2lang (item_type, item_id, lang_code) VALUES (?, ?, ?)'
                    );
                    if ($insL) {
                        $insL->bind_param('iis', $typeInt, $realId, $lan);
                        $insL->execute();
                        $insL->close();
                    }
                }
                
                // =========================================================
                // link_song sources (create: all rows are INSERT, no soft-delete needed)
                // =========================================================
                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }

                $sourcesOut = [];
                if ($callerAct >= 7) {
                    $sourcesIn = (isset($JSD->sources) && is_array($JSD->sources)) ? $JSD->sources : [];
                    foreach ($sourcesIn as $S) {
                        if (!is_object($S)) continue;
                        $url = isset($S->url) ? trim((string)$S->url) : '';
                        if ($url === '') continue;

                        $bit = isset($S->bit) ? (int)$S->bit : 0;
                        $act = SourceActFromPayload($S);
                        // act 3 = locked — only act9 may set/keep via payload
                        if ($act === 3 && $callerAct < 9) $act = 1;

                        // owner
                        if ($callerAct >= 9) {
                            $ownerPub = isset($S->user) ? (int)$S->user : 0;
                            $owner = $ownerPub > 0 ? PublicIdToReal($ownerPub, $IDHM) : $callerId;
                        } else {
                            $owner = $callerId;
                        }

                        $insL = $conn->prepare(
                            'INSERT INTO link_song (id_user, id_song, song_link, song_bit, file_id, file_unq, link_act)
                             VALUES (?, ?, ?, ?, \'\', \'\', ?)'
                        );
                        if ($insL) {
                            $insL->bind_param('iisii', $owner, $realId, $url, $bit, $act);
                            $insL->execute();
                            $newId = (int)$conn->insert_id;
                            $insL->close();
                            if ($newId > 0) {
                                $sourcesOut[] = [
                                    'id'   => $newId + (int)$IDHM,
                                    'url'  => $url,
                                    'bit'  => $bit,
                                    'user' => $owner > 0 ? ($owner + (int)$IDHM) : 0,
                                    'act'  => $act,
                                    'type' => SourceActToType($act),
                                ];
                            }
                        }
                    }
                }
                
                // =========================================================
                // song2album — from payload.albums [{id, trk}, ...]
                // =========================================================
                $albumRows = [];
                if (isset($JSD->albums) && is_array($JSD->albums)) {
                    foreach ($JSD->albums as $a) {
                        $pid = 0;
                        $trk = 0;
                        if (is_object($a)) {
                            $pid = isset($a->id)  ? (int)$a->id  : 0;
                            $trk = isset($a->trk) ? (int)$a->trk : 0;
                        } elseif (is_numeric($a)) {
                            $pid = (int)$a;
                        }
                        if ($trk < 0) $trk = 0;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) {
                            $albumRows[] = [$rid, $trk];
                        }
                    }
                    $byId = [];
                    foreach ($albumRows as $pair) {
                        $byId[$pair[0]] = $pair[1];
                    }
                    $albumRows = [];
                    foreach ($byId as $rid => $trk) {
                        $albumRows[] = [(int)$rid, (int)$trk];
                    }
                }
                if (!empty($albumRows)) {
                    $insAlb = $conn->prepare(
                        'INSERT IGNORE INTO song2album (id_song, id_album, trk_num) VALUES (?, ?, ?)'
                    );
                    if ($insAlb) {
                        foreach ($albumRows as $pair) {
                            $aid = $pair[0];
                            $trk = $pair[1];
                            $insAlb->bind_param('iii', $realId, $aid, $trk);
                            $insAlb->execute();
                        }
                        $insAlb->close();
                    }
                }

                // =========================================================
                // song2video — from payload.videos
                // =========================================================
                $videoIds = [];
                if (isset($JSD->videos) && is_array($JSD->videos)) {
                    foreach ($JSD->videos as $v) {
                        $pid = 0;
                        if (is_object($v) && isset($v->id)) {
                            $pid = (int)$v->id;
                        } elseif (is_numeric($v)) {
                            $pid = (int)$v;
                        }
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) {
                            $videoIds[] = $rid;
                        }
                    }
                    $videoIds = array_values(array_unique($videoIds));
                }
                if (!empty($videoIds)) {
                    $insV = $conn->prepare(
                        'INSERT IGNORE INTO song2video (id_song, id_video) VALUES (?, ?)'
                    );
                    if ($insV) {
                        foreach ($videoIds as $vid) {
                            $insV->bind_param('ii', $realId, $vid);
                            $insV->execute();
                        }
                        $insV->close();
                    }
                }

                // =========================================================
                // lyrics → lyric table + song_data.lrc index
                // =========================================================
                unset($data['lrc']);
                $lyricsIn  = (isset($JSD->lyrics) && is_array($JSD->lyrics)) ? $JSD->lyrics : [];
                $lrcIndex  = [];
                $lyricsOut = [];
                $typeIntLyric = ContentTypeToInt($type === 'podcast' ? 'podcast' : 'song');

                foreach ($lyricsIn as $L) {
                    if (!is_object($L)) continue;
                    $lng = strtolower(trim((string)(isset($L->lng) ? $L->lng : 'fa')));
                    $lng = preg_replace('/[^a-z]/', '', $lng);
                    if ($lng === '') $lng = 'und';
                    $lng = substr($lng, 0, 3);

                    $knd = (isset($L->knd) && $L->knd === 'srt') ? 'srt' : 'lrc';
                    $fmt = (isset($L->fmt) && $L->fmt === 'jsn') ? 'jsn' : 'txt';
                    $body = isset($L->body) ? (string)$L->body : '';
                    $body = str_replace("\r\n", "\n", $body);
                    $body = str_replace("\r", "\n", $body);
                    if (trim($body) === '') continue;

                    $src = LyricPlanBSrc('song', $pubId, $knd, $lng);
                    $now = time();

                    $st = $conn->prepare(
                        'INSERT INTO lyric (src, item_type, item_id, lng, knd, fmt, body, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE
                           item_type = VALUES(item_type),
                           item_id   = VALUES(item_id),
                           lng       = VALUES(lng),
                           knd       = VALUES(knd),
                           fmt       = VALUES(fmt),
                           body      = VALUES(body),
                           updated_at= VALUES(updated_at)'
                    );
                    if ($st) {
                        $st->bind_param(
                            'siissssi',
                            $src,
                            $typeIntLyric,
                            $realId,
                            $lng,
                            $knd,
                            $fmt,
                            $body,
                            $now
                        );
                        $st->execute();
                        $st->close();
                    }

                    $lrcIndex[] = [
                        'lng' => $lng,
                        'knd' => $knd,
                        'fmt' => $fmt,
                        'src' => $src,
                    ];
                    $lyricsOut[] = [
                        'lng'  => $lng,
                        'knd'  => $knd,
                        'fmt'  => $fmt,
                        'src'  => $src,
                        'body' => $body,
                    ];
                }

                if (!empty($lrcIndex)) {
                    $data['lrc'] = $lrcIndex;
                    $song_data_json = json_encode(
                        !empty($data) ? $data : new stdClass(),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                    $u = $conn->prepare('UPDATE song SET song_data = ? WHERE id_song = ? LIMIT 1');
                    if ($u) {
                        $u->bind_param('si', $song_data_json, $realId);
                        $u->execute();
                        $u->close();
                    }
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'created' => true,
                    'table'   => 'song',
                    'type'    => $type === 'podcast' ? 'podcast' : 'song',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => [
                        'name'    => $song_name,
                        'media'   => $mediaOut,
                        'artists' => $artistIds,
                        'sources' => $sourcesOut,
                        'albums'  => $albumRows,
                        'videos'  => $videoIds,
                        'lyrics'  => $lyricsOut,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
        
            // ---- album ----
            if ($table === 'album') {
                $conn->begin_transaction();
                try {


                $album_name = $name; // exact case

                $album_rd = null;
                if (isset($JSD->rd) && $JSD->rd !== null && $JSD->rd !== '') {
                    $rd = trim((string)$JSD->rd);
                    if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $rd, $rm)) {
                        $y = $rm[1];
                        $m = isset($rm[2]) ? $rm[2] : '01';
                        $d = isset($rm[3]) ? $rm[3] : '01';
                        if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                            $album_rd = $y . '-' . $m . '-' . $d;
                        }
                    }
                }

                $album_act = isset($JSD->act) ? (int)$JSD->act : 1;
                if ($album_act < 0 || $album_act > 3) $album_act = 1;

                $data = [];
                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if ($names) $data['names'] = $names;
                }
                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                }

                $album_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                if ($album_rd === null) {
                    $ins = $conn->prepare(
                        'INSERT INTO album (album_name, album_data, album_rd, album_act)
                         VALUES (?, ?, NULL, ?)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('ssi', $album_name, $album_data_json, $album_act);
                } else {
                    $ins = $conn->prepare(
                        'INSERT INTO album (album_name, album_data, album_rd, album_act)
                         VALUES (?, ?, ?, ?)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('sssi', $album_name, $album_data_json, $album_rd, $album_act);
                }

                if (!$ins->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $ins->error, 'errno' => $ins->errno]);
                    $ins->close();
                    exit();
                }
                $realId = (int)$conn->insert_id;
                $ins->close();
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => 'no insert_id']);
                    exit();
                }
                $pubId = $realId + (int)$IDHM;

                // ---- media (last-wins, plan-B keys) ----
                $mediaIn = (isset($JSD->media) && is_array($JSD->media)) ? $JSD->media : [];
                $uniqueRoles = [];
                $galRows = [];
                foreach ($mediaIn as $m) {
                    if (!is_object($m)) continue;
                    $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                    $src  = isset($m->url) ? trim((string)$m->url) : '';
                    if ($role === 'off' || $src === '') continue;
                    if ($role === 'gal') { $galRows[] = $m; continue; }
                    if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                    $uniqueRoles[$role] = $m;
                }
                $mediaOut = [];
                $galIndex = 0;
                foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                    if (!isset($uniqueRoles[$role])) continue;
                    $src = trim((string)$uniqueRoles[$role]->url);
                    $act = MediaRoleToAct($role);
                    if ($act < 1) continue;
                    $id_img = MediaPlanBKey('album', $pubId, $role, 0);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                    if ($saved === '') continue;
                    $data[$role] = $saved;
                    $mediaOut[] = ['role' => $role, 'id_img' => $saved];
                }
                foreach ($galRows as $m) {
                    $src = trim((string)$m->url);
                    $id_img = MediaPlanBKey('album', $pubId, 'gal', $galIndex++);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, MediaRoleToAct('gal'));
                    if ($saved === '') continue;
                    if (!isset($data['gal'])) $data['gal'] = [];
                    $data['gal'][] = $saved;
                    $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                }
                if ($mediaOut) {
                    $album_data_json = json_encode(
                        !empty($data) ? $data : new stdClass(),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                    $u = $conn->prepare('UPDATE album SET album_data = ? WHERE id_album = ? LIMIT 1');
                    if ($u) {
                        $u->bind_param('si', $album_data_json, $realId);
                        $u->execute();
                        $u->close();
                    }
                }

                // ---- album2artist ----
                $artistIds = [];
                if (isset($JSD->artists) && is_array($JSD->artists)) {
                    foreach ($JSD->artists as $a) {
                        $pid = is_object($a) && isset($a->id) ? (int)$a->id : (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $artistIds[] = $rid;
                    }
                    $artistIds = array_values(array_unique($artistIds));
                }
                if ($artistIds) {
                    $insA = $conn->prepare(
                        'INSERT IGNORE INTO album2artist (id_album, id_artist) VALUES (?, ?)'
                    );
                    if ($insA) {
                        foreach ($artistIds as $aid) {
                            $insA->bind_param('ii', $realId, $aid);
                            $insA->execute();
                        }
                        $insA->close();
                    }
                }

                // ---- song2album tracks from payload.tracks [{id, trk}] ----
                $trackRows = [];
                if (isset($JSD->tracks) && is_array($JSD->tracks)) {
                    foreach ($JSD->tracks as $t) {
                        if (!is_object($t)) continue;
                        $pid = isset($t->id) ? (int)$t->id : 0;
                        $rid = PublicIdToReal($pid, $IDHM);
                        $trk = isset($t->trk) ? (int)$t->trk : 0;
                        if ($trk < 0) $trk = 0;
                        if ($rid > 0) $trackRows[] = [$rid, $trk];
                    }
                    $bySong = [];
                    foreach ($trackRows as $pair) {
                        $bySong[(int)$pair[0]] = (int)$pair[1];
                    }
                    $trackRows = [];
                    $i = 1;
                    foreach ($bySong as $sid => $trk) {
                        $trackRows[] = [(int)$sid, $trk > 0 ? $trk : $i];
                        $i++;
                    }
                }
                if (!empty($trackRows)) {
                    $insT = $conn->prepare(
                        'INSERT IGNORE INTO song2album (id_song, id_album, trk_num) VALUES (?, ?, ?)'
                    );
                    if ($insT) {
                        foreach ($trackRows as $pair) {
                            $sid = $pair[0];
                            $trk = $pair[1];
                            $insT->bind_param('iii', $sid, $realId, $trk);
                            $insT->execute();
                        }
                        $insT->close();
                    }
                }

                // ---- item2genre ----
                $typeInt = ContentTypeToInt('album');
                $genreIds = [];
                if (isset($JSD->gnr) && is_array($JSD->gnr)) {
                    foreach ($JSD->gnr as $g) {
                        $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                        if ($gid > 0) $genreIds[] = $gid;
                    }
                    $genreIds = array_values(array_unique($genreIds));
                }
                if (!empty($genreIds)) {
                    $insG = $conn->prepare(
                        'INSERT IGNORE INTO item2genre (item_type, item_id, id_genre) VALUES (?, ?, ?)'
                    );
                    if ($insG) {
                        foreach ($genreIds as $gid) {
                            $insG->bind_param('iii', $typeInt, $realId, $gid);
                            $insG->execute();
                        }
                        $insG->close();
                    }
                }

                // ---- item2lang ----
                $langCodes = [];
                if (property_exists($JSD, 'lan')) {
                    $lan = strtolower(trim((string)$JSD->lan));
                    if ($lan !== '' && preg_match('/^[a-z]{2,8}$/', $lan)) {
                        $langCodes[] = $lan;
                    }
                }
                if (!empty($langCodes)) {
                    $insL = $conn->prepare(
                        'INSERT IGNORE INTO item2lang (item_type, item_id, lang_code) VALUES (?, ?, ?)'
                    );
                    if ($insL) {
                        foreach ($langCodes as $code) {
                            $insL->bind_param('iis', $typeInt, $realId, $code);
                            $insL->execute();
                        }
                        $insL->close();
                    }
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'created' => true,
                    'table'   => 'album',
                    'type'    => 'album',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => [
                        'name'    => $album_name,
                        'media'   => $mediaOut,
                        'artists' => $artistIds,
                        'tracks'  => array_map(function ($p) use ($IDHM) {
                            return ['id' => $p[0] + (int)$IDHM, 'trk' => $p[1]];
                        }, $trackRows),
                        'genres'  => $genreIds,
                        'languages' => $langCodes,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- video / musicvideo ----
            if ($table === 'video') {
                $conn->begin_transaction();
                try {


                $video_name = $name;
                $isUserVideo = ($type === 'video'); // else musicvideo

                $video_rd = null;
                if (isset($JSD->rd) && $JSD->rd !== null && $JSD->rd !== '') {
                    $rd = trim((string)$JSD->rd);
                    if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $rd, $rm)) {
                        $y = $rm[1];
                        $m = isset($rm[2]) ? $rm[2] : '01';
                        $d = isset($rm[3]) ? $rm[3] : '01';
                        if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                            $video_rd = $y . '-' . $m . '-' . $d;
                        }
                    }
                }

                $video_act = isset($JSD->act) ? (int)$JSD->act : 1;
                if ($video_act < 0 || $video_act > 3) $video_act = 1;

                $data = [];
                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if ($names) $data['names'] = $names;
                }
                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                }
                if (isset($JSD->dur) && (int)$JSD->dur > 0) {
                    $data['dur'] = (int)$JSD->dur;
                }
                // type flag in data (same pattern as song podcast typ)
                if ($isUserVideo) {
                    $data['typ'] = 2; // plain video — adjust if your VIDEO() uses another flag
                } else {
                    unset($data['typ']); // musicvideo default
                }

                $video_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                if ($video_rd === null) {
                    $ins = $conn->prepare(
                        'INSERT INTO video (video_name, video_data, video_rd, video_act, video_ply)
                         VALUES (?, ?, NULL, ?, 0)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('ssi', $video_name, $video_data_json, $video_act);
                } else {
                    $ins = $conn->prepare(
                        'INSERT INTO video (video_name, video_data, video_rd, video_act, video_ply)
                         VALUES (?, ?, ?, ?, 0)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('sssi', $video_name, $video_data_json, $video_rd, $video_act);
                }

                if (!$ins->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $ins->error, 'errno' => $ins->errno]);
                    $ins->close();
                    exit();
                }
                $realId = (int)$conn->insert_id;
                $ins->close();
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => 'no insert_id']);
                    exit();
                }
                $pubId = $realId + (int)$IDHM;

                // media
                $mediaIn = (isset($JSD->media) && is_array($JSD->media)) ? $JSD->media : [];
                $uniqueRoles = [];
                $galRows = [];
                foreach ($mediaIn as $m) {
                    if (!is_object($m)) continue;
                    $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                    $src  = isset($m->url) ? trim((string)$m->url) : '';
                    if ($role === 'off' || $src === '') continue;
                    if ($role === 'gal') { $galRows[] = $m; continue; }
                    if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                    $uniqueRoles[$role] = $m;
                }
                $mediaOut = [];
                $galIndex = 0;
                foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                    if (!isset($uniqueRoles[$role])) continue;
                    $src = trim((string)$uniqueRoles[$role]->url);
                    $act = MediaRoleToAct($role);
                    if ($act < 1) continue;
                    $id_img = MediaPlanBKey('video', $pubId, $role, 0);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                    if ($saved === '') continue;
                    $data[$role] = $saved;
                    $mediaOut[] = ['role' => $role, 'id_img' => $saved];
                }
                foreach ($galRows as $m) {
                    $src = trim((string)$m->url);
                    $id_img = MediaPlanBKey('video', $pubId, 'gal', $galIndex++);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, MediaRoleToAct('gal'));
                    if ($saved === '') continue;
                    if (!isset($data['gal'])) $data['gal'] = [];
                    $data['gal'][] = $saved;
                    $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                }

                // lyrics
                unset($data['lrc']);
                $lyricsIn  = (isset($JSD->lyrics) && is_array($JSD->lyrics)) ? $JSD->lyrics : [];
                $lrcIndex  = [];
                $lyricsOut = [];
                $typeIntLyric = ContentTypeToInt($isUserVideo ? 'video' : 'musicvideo');
                if ($typeIntLyric === null) {
                    $typeIntLyric = ContentTypeToInt('video');
                }
                foreach ($lyricsIn as $L) {
                    if (!is_object($L)) continue;
                    $lng = strtolower(trim((string)(isset($L->lng) ? $L->lng : 'fa')));
                    $lng = preg_replace('/[^a-z]/', '', $lng);
                    if ($lng === '') $lng = 'und';
                    $lng = substr($lng, 0, 3);
                    $knd = (isset($L->knd) && $L->knd === 'srt') ? 'srt' : 'lrc';
                    $fmt = (isset($L->fmt) && $L->fmt === 'jsn') ? 'jsn' : 'txt';
                    $body = isset($L->body) ? (string)$L->body : '';
                    $body = str_replace(["\r\n", "\r"], "\n", $body);
                    if (trim($body) === '') continue;
                    $src = LyricPlanBSrc('video', $pubId, $knd, $lng);
                    $now = time();
                    $st = $conn->prepare(
                        'INSERT INTO lyric (src, item_type, item_id, lng, knd, fmt, body, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE
                           item_type=VALUES(item_type), item_id=VALUES(item_id),
                           lng=VALUES(lng), knd=VALUES(knd), fmt=VALUES(fmt),
                           body=VALUES(body), updated_at=VALUES(updated_at)'
                    );
                    if ($st) {
                        $st->bind_param('siissssi', $src, $typeIntLyric, $realId, $lng, $knd, $fmt, $body, $now);
                        $st->execute();
                        $st->close();
                    }
                    $lrcIndex[] = ['lng'=>$lng,'knd'=>$knd,'fmt'=>$fmt,'src'=>$src];
                    $lyricsOut[] = ['lng'=>$lng,'knd'=>$knd,'fmt'=>$fmt,'src'=>$src,'body'=>$body];
                }
                if ($lrcIndex) $data['lrc'] = $lrcIndex;

                if ($mediaOut || $lrcIndex) {
                    $video_data_json = json_encode(
                        !empty($data) ? $data : new stdClass(),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                    $u = $conn->prepare('UPDATE video SET video_data = ? WHERE id_video = ? LIMIT 1');
                    if ($u) {
                        $u->bind_param('si', $video_data_json, $realId);
                        $u->execute();
                        $u->close();
                    }
                }

                // video2artist
                $artistIds = [];
                if (isset($JSD->artists) && is_array($JSD->artists)) {
                    foreach ($JSD->artists as $a) {
                        $pid = is_object($a) && isset($a->id) ? (int)$a->id : (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $artistIds[] = $rid;
                    }
                    $artistIds = array_values(array_unique($artistIds));
                }
                if ($artistIds) {
                    $insA = $conn->prepare('INSERT IGNORE INTO video2artist (id_video, id_artist) VALUES (?, ?)');
                    if ($insA) {
                        foreach ($artistIds as $aid) {
                            $insA->bind_param('ii', $realId, $aid);
                            $insA->execute();
                        }
                        $insA->close();
                    }
                }

                // song2video (payload.songs)
                $songIds = [];
                if (isset($JSD->songs) && is_array($JSD->songs)) {
                    foreach ($JSD->songs as $s) {
                        $pid = is_object($s) && isset($s->id) ? (int)$s->id : (int)$s;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $songIds[] = $rid;
                    }
                    $songIds = array_values(array_unique($songIds));
                }
                if ($songIds) {
                    $insS = $conn->prepare('INSERT IGNORE INTO song2video (id_song, id_video) VALUES (?, ?)');
                    if ($insS) {
                        foreach ($songIds as $sid) {
                            $insS->bind_param('ii', $sid, $realId);
                            $insS->execute();
                        }
                        $insS->close();
                    }
                }

                // genres / langs
                $typeInt = ContentTypeToInt($isUserVideo ? 'video' : 'musicvideo');
                if ($typeInt === null) $typeInt = ContentTypeToInt('video');
                $genreIds = [];
                if (isset($JSD->gnr) && is_array($JSD->gnr)) {
                    foreach ($JSD->gnr as $g) {
                        $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                        if ($gid > 0) $genreIds[] = $gid;
                    }
                    $genreIds = array_values(array_unique($genreIds));
                }
                if ($genreIds) {
                    $insG = $conn->prepare('INSERT IGNORE INTO item2genre (item_type, item_id, id_genre) VALUES (?, ?, ?)');
                    if ($insG) {
                        foreach ($genreIds as $gid) {
                            $insG->bind_param('iii', $typeInt, $realId, $gid);
                            $insG->execute();
                        }
                        $insG->close();
                    }
                }
                $lan = property_exists($JSD, 'lan') ? strtolower(trim((string)$JSD->lan)) : '';
                if ($lan !== '' && preg_match('/^[a-z]{2,8}$/', $lan)) {
                    $insL = $conn->prepare('INSERT IGNORE INTO item2lang (item_type, item_id, lang_code) VALUES (?, ?, ?)');
                    if ($insL) {
                        $insL->bind_param('iis', $typeInt, $realId, $lan);
                        $insL->execute();
                        $insL->close();
                    }
                }

                // link_video sources
                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }
                $sourcesOut = [];
                if ($callerAct >= 7) {
                    $sourcesIn = (isset($JSD->sources) && is_array($JSD->sources)) ? $JSD->sources : [];
                    foreach ($sourcesIn as $S) {
                        if (!is_object($S)) continue;
                        $url = isset($S->url) ? trim((string)$S->url) : '';
                        if ($url === '') continue;
                        $bit = isset($S->bit) ? (int)$S->bit : 0;
                        $act = SourceActFromPayload($S);
                        // act 3 = locked — only act9 may set/keep via payload
                        if ($act === 3 && $callerAct < 9) $act = 1;
                        if ($callerAct >= 9) {
                            $ownerPub = isset($S->user) ? (int)$S->user : 0;
                            $owner = $ownerPub > 0 ? PublicIdToReal($ownerPub, $IDHM) : $callerId;
                        } else {
                            $owner = $callerId;
                        }
                        $insL = $conn->prepare(
                            'INSERT INTO link_video (id_user, id_video, video_link, vdo_bit, file_id, file_unq, link_act)
                             VALUES (?, ?, ?, ?, \'\', \'\', ?)'
                        );
                        if ($insL) {
                            $insL->bind_param('iisii', $owner, $realId, $url, $bit, $act);
                            $insL->execute();
                            $newId = (int)$conn->insert_id;
                            $insL->close();
                            if ($newId > 0) {
                                $sourcesOut[] = [
                                    'id'   => $newId + (int)$IDHM,
                                    'url'  => $url,
                                    'bit'  => $bit,
                                    'user' => $owner > 0 ? ($owner + (int)$IDHM) : 0,
                                    'act'  => $act,
                                    'type' => SourceActToType($act),
                                ];
                            }
                        }
                    }
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'created' => true,
                    'table'   => 'video',
                    'type'    => $isUserVideo ? 'video' : 'musicvideo',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => [
                        'name'    => $video_name,
                        'media'   => $mediaOut,
                        'artists' => $artistIds,
                        'songs'   => $songIds,
                        'sources' => $sourcesOut,
                        'lyrics'  => $lyricsOut,
                        'genres'  => $genreIds,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- artist ----
            if ($table === 'artist') {
                $conn->begin_transaction();
                try {


                $artist_name = $name; // exact case

                $artist_cc = '';
                if (property_exists($JSD, 'cc')) {
                    $cc = strtoupper(trim((string)$JSD->cc));
                    if ($cc === '' || $cc === 'NONE') {
                        $artist_cc = '';
                    } elseif (preg_match('/^[A-Z]{2}$/', $cc) || $cc === 'GL') {
                        $artist_cc = $cc;
                    } else {
                        $artist_cc = substr($cc, 0, 8);
                    }
                }

                $artist_bd = null;
                if (isset($JSD->bd) && $JSD->bd !== null && $JSD->bd !== '') {
                    $bd = trim((string)$JSD->bd);
                    if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $bd, $rm)) {
                        $y = $rm[1];
                        $m = isset($rm[2]) ? $rm[2] : '01';
                        $d = isset($rm[3]) ? $rm[3] : '01';
                        if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                            $artist_bd = $y . '-' . $m . '-' . $d;
                        }
                    }
                }

                $artist_act = isset($JSD->act) ? (int)$JSD->act : 1;
                if ($artist_act < 0 || $artist_act > 3) $artist_act = 1;

                $data = [];
                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if ($names) $data['names'] = $names;
                }
                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                }
                foreach (['ytm', 'ins', 'tlg'] as $soc) {
                    if (property_exists($JSD, $soc)) {
                        $v = trim((string)$JSD->$soc);
                        if ($v !== '') $data[$soc] = $v;
                    }
                }

                $artist_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                if ($artist_bd === null) {
                    $ins = $conn->prepare(
                        'INSERT INTO artist (artist_name, artist_data, artist_cc, artist_bd, artist_act)
                         VALUES (?, ?, ?, NULL, ?)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('sssi', $artist_name, $artist_data_json, $artist_cc, $artist_act);
                } else {
                    $ins = $conn->prepare(
                        'INSERT INTO artist (artist_name, artist_data, artist_cc, artist_bd, artist_act)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param('ssssi', $artist_name, $artist_data_json, $artist_cc, $artist_bd, $artist_act);
                }

                if (!$ins->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $ins->error, 'errno' => $ins->errno]);
                    $ins->close();
                    exit();
                }
                $realId = (int)$conn->insert_id;
                $ins->close();
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => 'no insert_id']);
                    exit();
                }
                $pubId = $realId + (int)$IDHM;

                // media
                $mediaIn = (isset($JSD->media) && is_array($JSD->media)) ? $JSD->media : [];
                $uniqueRoles = [];
                $galRows = [];
                foreach ($mediaIn as $m) {
                    if (!is_object($m)) continue;
                    $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                    $src  = isset($m->url) ? trim((string)$m->url) : '';
                    if ($role === 'off' || $src === '') continue;
                    if ($role === 'gal') { $galRows[] = $m; continue; }
                    if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                    $uniqueRoles[$role] = $m;
                }
                $mediaOut = [];
                $galIndex = 0;
                foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                    if (!isset($uniqueRoles[$role])) continue;
                    $src = trim((string)$uniqueRoles[$role]->url);
                    $act = MediaRoleToAct($role);
                    if ($act < 1) continue;
                    $id_img = MediaPlanBKey('artist', $pubId, $role, 0);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, $act);
                    if ($saved === '') continue;
                    $data[$role] = $saved;
                    $mediaOut[] = ['role' => $role, 'id_img' => $saved];
                }
                foreach ($galRows as $m) {
                    $src = trim((string)$m->url);
                    $id_img = MediaPlanBKey('artist', $pubId, 'gal', $galIndex++);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, MediaRoleToAct('gal'));
                    if ($saved === '') continue;
                    if (!isset($data['gal'])) $data['gal'] = [];
                    $data['gal'][] = $saved;
                    $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                }
                if ($mediaOut) {
                    $artist_data_json = json_encode(
                        !empty($data) ? $data : new stdClass(),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                    $u = $conn->prepare('UPDATE artist SET artist_data = ? WHERE id_artist = ? LIMIT 1');
                    if ($u) {
                        $u->bind_param('si', $artist_data_json, $realId);
                        $u->execute();
                        $u->close();
                    }
                }

                // artist2genre
                $genreIds = [];
                if (isset($JSD->gnr) && is_array($JSD->gnr)) {
                    foreach ($JSD->gnr as $g) {
                        $gid = is_object($g) && isset($g->id) ? (int)$g->id : (int)$g;
                        if ($gid > 0) $genreIds[] = $gid;
                    }
                    $genreIds = array_values(array_unique($genreIds));
                }
                if ($genreIds) {
                    $insG = $conn->prepare('INSERT IGNORE INTO artist2genre (id_artist, id_genre) VALUES (?, ?)');
                    if ($insG) {
                        foreach ($genreIds as $gid) {
                            $insG->bind_param('ii', $realId, $gid);
                            $insG->execute();
                        }
                        $insG->close();
                    }
                }

                // song2artist
                $songIds = [];
                if (isset($JSD->songs) && is_array($JSD->songs)) {
                    foreach ($JSD->songs as $s) {
                        $pid = is_object($s) && isset($s->id) ? (int)$s->id : (int)$s;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $songIds[] = $rid;
                    }
                    $songIds = array_values(array_unique($songIds));
                }
                if ($songIds) {
                    $ins = $conn->prepare('INSERT IGNORE INTO song2artist (id_song, id_artist) VALUES (?, ?)');
                    if ($ins) {
                        foreach ($songIds as $sid) {
                            $ins->bind_param('ii', $sid, $realId);
                            $ins->execute();
                        }
                        $ins->close();
                    }
                }

                // album2artist
                $albumIds = [];
                if (isset($JSD->albums) && is_array($JSD->albums)) {
                    foreach ($JSD->albums as $a) {
                        $pid = is_object($a) && isset($a->id) ? (int)$a->id : (int)$a;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $albumIds[] = $rid;
                    }
                    $albumIds = array_values(array_unique($albumIds));
                }
                if ($albumIds) {
                    $ins = $conn->prepare('INSERT IGNORE INTO album2artist (id_album, id_artist) VALUES (?, ?)');
                    if ($ins) {
                        foreach ($albumIds as $aid) {
                            $ins->bind_param('ii', $aid, $realId);
                            $ins->execute();
                        }
                        $ins->close();
                    }
                }

                // video2artist
                $videoIds = [];
                if (isset($JSD->videos) && is_array($JSD->videos)) {
                    foreach ($JSD->videos as $v) {
                        $pid = is_object($v) && isset($v->id) ? (int)$v->id : (int)$v;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid > 0) $videoIds[] = $rid;
                    }
                    $videoIds = array_values(array_unique($videoIds));
                }
                if ($videoIds) {
                    $ins = $conn->prepare('INSERT IGNORE INTO video2artist (id_video, id_artist) VALUES (?, ?)');
                    if ($ins) {
                        foreach ($videoIds as $vid) {
                            $ins->bind_param('ii', $vid, $realId);
                            $ins->execute();
                        }
                        $ins->close();
                    }
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'created' => true,
                    'table'   => 'artist',
                    'type'    => 'artist',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => [
                        'name'    => $artist_name,
                        'cc'      => $artist_cc,
                        'bd'      => $artist_bd,
                        'media'   => $mediaOut,
                        'genres'  => $genreIds,
                        'songs'   => $songIds,
                        'albums'  => $albumIds,
                        'videos'  => $videoIds,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- system playlist (act9 only, pl_act = 9) ----
            if ($table === 'playlist') {
                $conn->begin_transaction();
                try {

                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }
                if ($callerAct < 9) {
                    $conn->rollback();
                    echo json_encode(['error' => 'forbidden', 'detail' => 'act9_required']);
                    exit();
                }

                $playlist_name = $name;
                $now = time();
                $pl_act = 9; // system only in this panel
                $id_user = 0; // system owner

                $data = [];
                if (isset($JSD->names) && is_object($JSD->names)) {
                    $names = [];
                    foreach ((array)$JSD->names as $code => $val) {
                        $code = strtolower(trim((string)$code));
                        $val  = trim((string)$val);
                        if ($code !== '' && $val !== '') $names[$code] = $val;
                    }
                    if ($names) $data['names'] = $names;
                }
                if (property_exists($JSD, 'des')) {
                    $des = trim((string)$JSD->des);
                    if ($des !== '') $data['des'] = $des;
                }

                $playlist_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                $ins = $conn->prepare(
                    'INSERT INTO playlist
                        (playlist_name, id_user, playlist_data, created_at, updated_at, pl_act)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                if (!$ins) {
                    $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                    exit();
                }
                $ins->bind_param('sisiii', $playlist_name, $id_user, $playlist_data_json, $now, $now, $pl_act);
                if (!$ins->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $ins->error]);
                    $ins->close();
                    exit();
                }
                $realId = (int)$conn->insert_id;
                $ins->close();
                if ($realId <= 0) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => 'no insert_id']);
                    exit();
                }
                $pubId = $realId + (int)$IDHM;

                // media → playlist_data.tmb (and other roles)
                $mediaIn = (isset($JSD->media) && is_array($JSD->media)) ? $JSD->media : [];
                $uniqueRoles = [];
                $galRows = [];
                foreach ($mediaIn as $m) {
                    if (!is_object($m)) continue;
                    $role = isset($m->role) ? strtolower(trim((string)$m->role)) : 'tmb';
                    $src  = isset($m->url) ? trim((string)$m->url) : '';
                    if ($role === 'off' || $src === '') continue;
                    if ($role === 'gal') { $galRows[] = $m; continue; }
                    if (!in_array($role, ['tmb', 'tmv', 'cvr', 'art'], true)) $role = 'tmb';
                    $uniqueRoles[$role] = $m;
                }
                $mediaOut = [];
                $galIndex = 0;
                foreach (['tmb', 'tmv', 'cvr', 'art'] as $role) {
                    if (!isset($uniqueRoles[$role])) continue;
                    $src = trim((string)$uniqueRoles[$role]->url);
                    $actM = MediaRoleToAct($role);
                    if ($actM < 1) continue;
                    $id_img = MediaPlanBKey('playlist', $pubId, $role, 0);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, $actM);
                    if ($saved === '') continue;
                    $data[$role] = $saved;
                    $mediaOut[] = ['role' => $role, 'id_img' => $saved];
                }
                foreach ($galRows as $m) {
                    $src = trim((string)$m->url);
                    $id_img = MediaPlanBKey('playlist', $pubId, 'gal', $galIndex++);
                    $saved = IngestThumbFromSource($conn, $src, $id_img, MediaRoleToAct('gal'));
                    if ($saved === '') continue;
                    if (!isset($data['gal'])) $data['gal'] = [];
                    $data['gal'][] = $saved;
                    $mediaOut[] = ['role' => 'gal', 'id_img' => $saved];
                }

                // items
                $itemRows = []; // [typeInt, realId, pos]
                if (isset($JSD->items) && is_array($JSD->items)) {
                    $pos = 1;
                    foreach ($JSD->items as $it) {
                        if (!is_object($it)) continue;
                        $pid = isset($it->id) ? (int)$it->id : 0;
                        $typeStr = isset($it->type) ? strtolower(trim((string)$it->type)) : 'song';
                        $typeInt = ContentTypeToInt($typeStr);
                        if ($typeInt === null || $typeInt > 3) continue;
                        $rid = PublicIdToReal($pid, $IDHM);
                        if ($rid <= 0) continue;
                        $itemRows[] = [$typeInt, $rid, $pos++];
                    }
                }
                $data['nci'] = count($itemRows);

                $playlist_data_json = json_encode(
                    !empty($data) ? $data : new stdClass(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                $u = $conn->prepare('UPDATE playlist SET playlist_data = ? WHERE id_playlist = ? LIMIT 1');
                if ($u) {
                    $u->bind_param('si', $playlist_data_json, $realId);
                    $u->execute();
                    $u->close();
                }

                if ($itemRows) {
                    $insI = $conn->prepare(
                        'INSERT INTO playlist_items (id_playlist, item_type, item_id, position, added_at)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    if ($insI) {
                        foreach ($itemRows as $pair) {
                            $t = $pair[0]; $rid = $pair[1]; $pos = $pair[2];
                            $insI->bind_param('iiiii', $realId, $t, $rid, $pos, $now);
                            $insI->execute();
                        }
                        $insI->close();
                    }
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'created' => true,
                    'table'   => 'playlist',
                    'type'    => 'playlist',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => [
                        'name'  => $playlist_name,
                        'media' => $mediaOut,
                        'items' => count($itemRows),
                        'nci'   => count($itemRows),
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }
            
            // ---- user / owner ----
            if ($table === 'user') {
                $conn->begin_transaction();
                try {

                $callerId  = (int) GetUserIdFromAccessToken();
                $callerAct = 0;
                if ($callerId > 0) {
                    $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                    if ($stU) {
                        $stU->bind_param('i', $callerId);
                        $stU->execute();
                        $ru = $stU->get_result()->fetch_assoc();
                        $stU->close();
                        if ($ru) $callerAct = (int)$ru['act'];
                    }
                }
                if ($callerAct < 9) {
                    $conn->rollback();
                    echo json_encode(['error' => 'forbidden', 'detail' => 'act9_required']);
                    exit();
                }

                $u_name = isset($JSD->name) ? trim((string)$JSD->name) : $name;
                $email  = isset($JSD->email) ? trim((string)$JSD->email) : '';
                $pass   = isset($JSD->pass)  ? (string)$JSD->pass : '';
                $f_name = isset($JSD->url)   ? trim((string)$JSD->url) : '';
                $l_name = isset($JSD->other) ? trim((string)$JSD->other) : '';
                $u_ref  = isset($JSD->ref)   ? trim((string)$JSD->ref) : '';
                $id_tlg = 0;
                if (isset($JSD->tlg) && $JSD->tlg !== '' && $JSD->tlg !== null) {
                    $id_tlg = (int)preg_replace('/\D+/', '', (string)$JSD->tlg);
                }
                $act = isset($JSD->act) ? (int)$JSD->act : (($type === 'owner') ? 7 : 1);
                if ($act < 0 || $act > 9) $act = 1;
                if ($type === 'owner' && $act < 7) $act = 7;

                // identity fill: name → email → telegram
                if ($u_name === '') {
                    if ($email !== '') {
                        $u_name = $email;
                    } elseif ($id_tlg > 0) {
                        $u_name = (string)$id_tlg;
                    }
                }
                if ($u_name === '') {
                    $conn->rollback();
                    echo json_encode(['error' => 'identity_required', 'detail' => 'name or email or telegram']);
                    exit();
                }
                if ($pass === '' || strlen($pass) < 6) {
                    $conn->rollback();
                    echo json_encode(['error' => 'password_too_short']);
                    exit();
                }

                // uniqueness (only non-empty)
                $chk = $conn->prepare('SELECT id_user FROM user WHERE u_name = ? LIMIT 1');
                if ($chk) {
                    $chk->bind_param('s', $u_name);
                    $chk->execute();
                    if ($chk->get_result()->fetch_assoc()) {
                        $conn->rollback();
                    echo json_encode(['error' => 'duplicate', 'detail' => 'u_name']);
                        exit();
                    }
                    $chk->close();
                }
                if ($email !== '') {
                    $chk = $conn->prepare('SELECT id_user FROM user WHERE u_email = ? LIMIT 1');
                    if ($chk) {
                        $chk->bind_param('s', $email);
                        $chk->execute();
                        if ($chk->get_result()->fetch_assoc()) {
                            $conn->rollback();
                    echo json_encode(['error' => 'duplicate', 'detail' => 'u_email']);
                            exit();
                        }
                        $chk->close();
                    }
                }

                $hash   = password_hash($pass, PASSWORD_DEFAULT);
                $join   = time();
                $u_data = json_encode(['join' => $join], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                // 9 columns — no u_log, no u_photo
                if ($email === '') {
                    $ins = $conn->prepare(
                        'INSERT INTO user
                          (id_tlg, u_name, u_pass, f_name, l_name, u_email, u_data, u_ref, act)
                         VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param(
                        'issssssi',
                        $id_tlg, $u_name, $hash, $f_name, $l_name, $u_data, $u_ref, $act
                    );
                } else {
                    $ins = $conn->prepare(
                        'INSERT INTO user
                          (id_tlg, u_name, u_pass, f_name, l_name, u_email, u_data, u_ref, act)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    if (!$ins) {
                        $conn->rollback();
                    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
                        exit();
                    }
                    $ins->bind_param(
                        'isssssssi',
                        $id_tlg, $u_name, $hash, $f_name, $l_name, $email, $u_data, $u_ref, $act
                    );
                }

                if (!$ins->execute()) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $ins->error]);
                    $ins->close();
                    exit();
                }
                $realId = (int)$conn->insert_id;
                $ins->close();
                $pubId  = $realId + (int)$IDHM;

                // media after we know pubId
                $data = ['join' => $join];
                $mediaOut = [];
                if (property_exists($JSD, 'media') && is_array($JSD->media)) {
                    $byRole = [];
                    foreach ($JSD->media as $m) {
                        if (!is_object($m)) continue;
                        $role = isset($m->role) ? strtolower(trim((string)$m->role)) : '';
                        $src  = isset($m->url) ? trim((string)$m->url) : '';
                        if ($role === '' || $role === 'off' || $src === '') continue;
                        if (!in_array($role, ['tmb', 'ico', 'photo'], true)) continue;
                        $byRole[$role] = $src;
                    }
                    if (!empty($byRole['tmb'])) {
                        $id_img = MediaPlanBKey('user', $pubId, 'tmb', 0);
                        $saved  = IngestThumbFromSource($conn, $byRole['tmb'], $id_img, MediaRoleToAct('tmb'));
                        if ($saved !== '') {
                            $data['tmb'] = $saved;
                            $mediaOut[] = ['role' => 'tmb', 'id_img' => $saved];
                        }
                    }
                    if (!empty($byRole['ico'])) {
                        $id_img = MediaPlanBKey('user', $pubId, 'ico', 0);
                        $saved  = IngestThumbFromSource($conn, $byRole['ico'], $id_img, MediaRoleToAct('ico'));
                        if ($saved !== '') {
                            $data['ico'] = $saved;
                            $mediaOut[] = ['role' => 'ico', 'id_img' => $saved];
                        }
                    }
                    if (!empty($byRole['photo']) && preg_match('#^https?://#i', $byRole['photo'])) {
                        $data['photo'] = $byRole['photo'];
                        $mediaOut[] = ['role' => 'photo', 'url' => $byRole['photo']];
                    }
                    $u_data2 = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $u = $conn->prepare('UPDATE user SET u_data = ? WHERE id_user = ? LIMIT 1');
                    if ($u) {
                        $u->bind_param('si', $u_data2, $realId);
                        $u->execute();
                        $u->close();
                    }
                }

                $conn->commit();
                echo json_encode([
                    'ok'      => true,
                    'created' => true,
                    'table'   => 'user',
                    'type'    => $type === 'owner' ? 'owner' : 'user',
                    'id'      => $pubId,
                    'real_id' => $realId,
                    'changes' => [
                        'name'  => $u_name,
                        'media' => $mediaOut,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    echo json_encode(['error' => 'create_failed', 'detail' => $e->getMessage()]);
                    exit();
                }

            }

            echo json_encode(['error' => 'create_not_implemented', 'table' => $table]);
            exit();
        }
    
        echo json_encode(['error' => 'unknown_action', 'a' => $ACT]);
        exit();
    }
    
    function LoadItemGenres($itemType, $realId) {
        global $conn;
        $out = [];
        $itemType = (int)$itemType;
        $realId   = (int)$realId;
        if ($realId <= 0) return $out;
    
        $sql = "
            SELECT g.id_genre, g.slug, g.name
            FROM item2genre ig
            JOIN genre g ON g.id_genre = ig.id_genre AND g.genre_act != 0
            WHERE ig.item_type = $itemType AND ig.item_id = $realId
            ORDER BY g.name
        ";
        $res = $conn->query($sql);
        if (!$res) return $out;
    
        while ($row = $res->fetch_assoc()) {
            // id only is enough for the client (resolve label from SN.gnr);
            // slug + name kept as a light fallback when taxonomy cache is cold
            $out[] = [
                'id'   => (int)$row['id_genre'],
                'slug' => $row['slug'],
                'name' => $row['name'],
            ];
        }
        return $out;
    }
        
    function LoadItemLanguages($itemType, $realId) {
        global $conn;
        $out = [];
        $itemType = (int)$itemType;
        $realId   = (int)$realId;
        if ($realId <= 0) return $out;
    
        $sql = "
            SELECT il.lang_code
            FROM item2lang il
            WHERE il.item_type = $itemType AND il.item_id = $realId
            ORDER BY il.lang_code
        ";
        $res = $conn->query($sql);
        if (!$res) return $out;
    
        while ($row = $res->fetch_assoc()) {
            $out[] = $row['lang_code'];
        }
        return $out;
    }
    
    function LoadArtistGenres($realArtistId) {
        global $conn;
        $out = [];
        $realArtistId = (int)$realArtistId;
        if ($realArtistId <= 0) return $out;
    
        $sql = "
            SELECT g.id_genre, g.slug, g.name, g.data
            FROM artist2genre ag
            JOIN genre g ON g.id_genre = ag.id_genre AND g.genre_act != 0
            WHERE ag.id_artist = $realArtistId
            ORDER BY g.name
        ";
        $res = $conn->query($sql);
        if (!$res) return $out;
    
        while ($row = $res->fetch_assoc()) {
            $g = [
                'id'   => (int)$row['id_genre'],
                'slug' => $row['slug'],
                'name' => $row['name'],
            ];
            $data = json_decode($row['data'] ?? '', true);
            if (!empty($data['names']) && is_array($data['names'])) {
                $g['names'] = $data['names'];
            }
            $out[] = $g;
        }
        return $out;
    }
    
    /**
     * Load lyrics/subtitles for a full card (not mini-card).
     * Returns list of { lng, knd, fmt, src, body }.
     */
    function LoadItemLyrics($itemType, $realId) {
        global $conn;
        $out = [];
        $itemType = (int)$itemType;
        $realId   = (int)$realId;
        if ($realId <= 0) return $out;

        $sql = "
            SELECT src, lng, knd, fmt, body
            FROM lyric
            WHERE item_type = $itemType AND item_id = $realId
            ORDER BY knd ASC, lng ASC
        ";
        $res = $conn->query($sql);
        if (!$res) return $out;

        while ($row = $res->fetch_assoc()) {
            $out[] = [
                'lng'  => $row['lng'],
                'knd'  => $row['knd'],
                'fmt'  => $row['fmt'],
                'src'  => $row['src'],
                // body keeps real newlines; json_encode will escape them as \n
                'body' => $row['body'],
            ];
        }
        return $out;
    }

    /** plan-B lyric src: s{publicId}l_fa / s{publicId}s_en */
    function LyricPlanBSrc($table, $publicId, $knd, $lng) {
        static $prefix = [
            'song' => 's', 'podcast' => 's',
            'video' => 'v', 'musicvideo' => 'v',
        ];
        $table = strtolower((string)$table);
        $p = isset($prefix[$table]) ? $prefix[$table] : 'x';
        $knd = ($knd === 'srt') ? 's' : 'l';
        $lng = strtolower(preg_replace('/[^a-z]/', '', (string)$lng));
        if ($lng === '') $lng = 'und';
        return $p . (int)$publicId . $knd . '_' . $lng;
    }
    
    function ThumbImage($key) {
        
        global $conn;
        if ($key === null || $key === '') return '';

        $key = trim((string)$key);
        if ($key === '') return '';

        // Prefer clean string key (plan-B: s11210713t, a…, v…, or legacy filename)
        if (!is_numeric($key)) {
            $key = preg_replace('/[^A-Za-z0-9_\-.]/', '', $key);
            return $key;
        }

        $id = (int)$key;
        if ($id <= 0) return '';

        $sql = "SELECT id_img FROM thumb WHERE id_img = '" . $conn->real_escape_string((string)$id) . "' AND act != 0 LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows) {
            $row = $result->fetch_assoc();
            return $row['id_img'];
        }

        // Not in table — still return the digit string so callers can build a URL
        return (string)$id;
    }
    
    // =========================================================================
    // Media helpers (id_img only)
    // =========================================================================


    // link_act: 0=off, 1=original, 2=demo, 3=locked (admin)
    function SourceActToType($act) {
        $act = (int)$act;
        if ($act === 3) return 'locked';
        if ($act === 2) return 'demo';
        if ($act === 0) return 'off';
        return 'original';
    }

    function SourceTypeToAct($typeOrAct) {
        if (is_int($typeOrAct) || (is_string($typeOrAct) && preg_match('/^-?\d+$/', (string)$typeOrAct))) {
            $a = (int)$typeOrAct;
            if ($a < 0 || $a > 3) $a = 1;
            return $a;
        }
        $t = strtolower(trim((string)$typeOrAct));
        if ($t === 'demo') return 2;
        if ($t === 'locked' || $t === 'lock') return 3;
        if ($t === 'off' || $t === 'deactivated' || $t === 'inactive') return 0;
        if ($t === 'orig' || $t === 'original') return 1;
        return 1;
    }

    // Parse source act from payload: prefer explicit act, else type string
    function SourceActFromPayload($S) {
        if (!is_object($S)) return 1;
        if (property_exists($S, 'act') && $S->act !== null && $S->act !== '') {
            return SourceTypeToAct($S->act);
        }
        if (property_exists($S, 'type') && $S->type !== null && $S->type !== '') {
            return SourceTypeToAct($S->type);
        }
        return 1;
    }

    function MediaRoleToAct($role) {
        static $map = [
            'tmb'   => 1,
            'tmv'   => 2,
            'cvr'   => 3,
            'art'   => 4,
            'gal'   => 5,
            'ico'   => 6,  // owner icon on host
            'photo' => 0,  // external URL only
            'off'   => 0,
        ];
        $role = strtolower(trim((string)$role));
        return isset($map[$role]) ? $map[$role] : 1;
    }

    function MediaPlanBKey($table, $publicId, $role, $galIndex = 0) {
        static $prefix = [
            'song' => 's', 'podcast' => 's',
            'artist' => 'a', 'album' => 'b',
            'video' => 'v', 'musicvideo' => 'v',
            'playlist' => 'p',
            'user' => 'u', 'owner' => 'u',
        ];
        $table = strtolower((string)$table);
        $p = isset($prefix[$table]) ? $prefix[$table] : 'x';
        $id = (int)$publicId;
        $role = strtolower(trim((string)$role));
        if ($role === 'gal') {
            return $p . $id . 'g' . (int)$galIndex;
        }
        static $suf = [
            'tmb' => 't', 'tmv' => 'v', 'cvr' => 'c', 'art' => 'a', 'ico' => 'i',
        ];
        $s = isset($suf[$role]) ? $suf[$role] : 't';
        return $p . $id . $s;
    }

    // Public owner card — playlist / sources / player logo.
    // { id, name, meta?: { tmb, ico, photo, des, url? } }
    // No act here. Admin edit → get_user.
    function OwnerCard($realId) {
        global $conn, $IDHM;
        static $cache = [];

        $realId = (int)$realId;
        if ($realId <= 0) {
            return [
                'id'   => 0,
                'name' => 'Hamrahang',
            ];
        }
        if (isset($cache[$realId])) {
            return $cache[$realId];
        }

        $stmt = $conn->prepare(
            'SELECT id_user, u_name, f_name, l_name, u_email, u_data, act
             FROM user WHERE id_user = ? LIMIT 1'
        );
        if (!$stmt) {
            $cache[$realId] = null;
            return null;
        }
        $stmt->bind_param('i', $realId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) {
            $cache[$realId] = null;
            return null;
        }

        $act    = (int)$row['act'];
        $u_name = trim((string)($row['u_name'] ?? ''));
        $f_name = trim((string)($row['f_name'] ?? ''));
        $l_name = trim((string)($row['l_name'] ?? ''));

        $name = $u_name;
        if ($name === '') {
            if ($act >= 7 && preg_match('#^https?://#i', $f_name)) {
                $name = $l_name !== '' ? $l_name : $f_name;
            } else {
                $name = trim($f_name . ' ' . $l_name);
            }
        }
        if ($name === '') {
            $name = trim((string)($row['u_email'] ?? ''));
        }
        if ($name === '') {
            $name = 'User #' . ((int)$row['id_user'] + (int)$IDHM);
        }

        $meta = [];
        $data = [];
        if (!empty($row['u_data'])) {
            $decoded = json_decode($row['u_data'], true);
            if (is_array($decoded)) $data = $decoded;
        }
        if (!empty($data['tmb']))   $meta['tmb']   = (string)$data['tmb'];
        if (!empty($data['ico']))   $meta['ico']   = (string)$data['ico'];
        if (!empty($data['photo'])) $meta['photo'] = (string)$data['photo'];
        if (!empty($data['des']))   $meta['des']   = (string)$data['des'];
        if ($act >= 7 && $f_name !== '' && preg_match('#^https?://#i', $f_name)) {
            $meta['url'] = $f_name;
        }

        $card = [
            'id'   => (int)$row['id_user'] + (int)$IDHM,
            'name' => $name,
        ];
        if ($meta) {
            $card['meta'] = $meta;
        }

        $cache[$realId] = $card;
        return $card;
    }

    // Batch for ?_qjs=own — same public shape as OwnerCard (no act).
    function OwnerCardsByRealIds(array $realIds) {
        $out = [];
        $seen = [];
        foreach ($realIds as $rid) {
            $rid = (int)$rid;
            if ($rid <= 0 || isset($seen[$rid])) continue;
            $seen[$rid] = true;
            $card = OwnerCard($rid);
            if ($card) $out[] = $card;
        }
        return $out;
    }

    // Save image bytes to thumb/{id_img}.jpg and register/update thumb row by id_img.
    // Returns id_img on success, '' on failure.
    // $source = https URL or data:image/...;base64,...
        /**
     * Save image to /thumb and register thumb.id_img.
     *
     * JPG:  file = {key}.jpg      , id_img = {key}
     * PNG:  file = {key}.png      , id_img = {key}png
     * SVG:  file = {key}.svg      , id_img = {key}svg
     * WEBP: file = {key}.webp     , id_img = {key}webp
     * GIF:  file = {key}.gif      , id_img = {key}gif
     *
     * $id_img argument = plan-B base key (e.g. u11210717t / u11210717i / s11210713t)
     * Returns final id_img string or ''.
     */
    function IngestThumbFromSource($conn, $source, $id_img, $act) {
        $base = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$id_img);
        $act  = (int)$act;
        if ($base === '' || $act < 1) return '';

        $source = (string)$source;
        $binary = null;
        $ext    = 'jpg'; // default

        // ---- data URI ----
        if (preg_match('#^data:image/(jpeg|jpg|png|gif|webp|svg\+xml|svg);base64,#i', $source, $mm)) {
            $kind = strtolower($mm[1]);
            if ($kind === 'jpeg') $kind = 'jpg';
            if ($kind === 'svg+xml' || $kind === 'svg') $kind = 'svg';
            $raw = base64_decode(substr($source, strpos($source, ',') + 1), true);
            if ($raw === false || strlen($raw) < 8) return '';
            if (strlen($raw) > 6 * 1024 * 1024) return '';
            $binary = $raw;
            $ext = $kind;
        }
        // ---- http(s) URL ----
        elseif (preg_match('#^https?://#i', $source)) {
            // Already our thumb with format-suffix key in URL path
            // e.g. /thumb/u11210717i.png  or  /thumb/u11210717ipng (rare)
            if (preg_match('#/thumb/([A-Za-z0-9_\-]+)\.(jpg|jpeg|png|svg|webp|gif)(\?|$)#i', $source, $mm)) {
                $fileBase = $mm[1];
                $fileExt  = strtolower($mm[2]) === 'jpeg' ? 'jpg' : strtolower($mm[2]);
                // if URL already points at the exact file we want for this base, accept
                if ($fileBase === $base || $fileBase === $base . $fileExt) {
                    $finalKey = ($fileExt === 'jpg') ? $base : ($base . $fileExt);
                    $st = $conn->prepare('SELECT id_img FROM thumb WHERE id_img = ? LIMIT 1');
                    if ($st) {
                        $st->bind_param('s', $finalKey);
                        $st->execute();
                        $exists = $st->get_result()->fetch_assoc();
                        $st->close();
                        if ($exists) {
                            $up = $conn->prepare('UPDATE thumb SET act = ? WHERE id_img = ? LIMIT 1');
                            if ($up) { $up->bind_param('is', $act, $finalKey); $up->execute(); $up->close(); }
                            return $finalKey;
                        }
                    }
                    // fall through to download/copy if row missing
                }
            }
            // classic /thumb/{base}.jpg
            if (preg_match('#/thumb/' . preg_quote($base, '#') . '\.jpe?g(\?|$)#i', $source)) {
                $st = $conn->prepare('SELECT id_img FROM thumb WHERE id_img = ? LIMIT 1');
                if ($st) {
                    $st->bind_param('s', $base);
                    $st->execute();
                    $exists = $st->get_result()->fetch_assoc();
                    $st->close();
                    if ($exists) {
                        $up = $conn->prepare('UPDATE thumb SET act = ? WHERE id_img = ? LIMIT 1');
                        if ($up) { $up->bind_param('is', $act, $base); $up->execute(); $up->close(); }
                        return $base;
                    }
                }
            }

            $ctx = stream_context_create([
                'http' => [
                    'timeout'         => 20,
                    'follow_location' => 1,
                    'user_agent'      => 'HamrahangThumbIngest/1.0',
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $raw = @file_get_contents($source, false, $ctx);
            if ($raw === false || strlen($raw) < 8) return '';
            if (strlen($raw) > 6 * 1024 * 1024) return '';
            $binary = $raw;

            // sniff format (telegram .svg URLs often redirect to real jpg)
            if (strncmp($raw, "\xFF\xD8\xFF", 3) === 0) {
                $ext = 'jpg';
            } elseif (strncmp($raw, "\x89PNG", 4) === 0) {
                $ext = 'png';
            } elseif (strncmp($raw, 'RIFF', 4) === 0 && stripos(substr($raw, 0, 16), 'WEBP') !== false) {
                $ext = 'webp';
            } elseif (strncmp($raw, 'GIF8', 4) === 0) {
                $ext = 'gif';
            } elseif (stripos($raw, '<svg') !== false) {
                $ext = 'svg';
            } else {
                $path = (string)parse_url($source, PHP_URL_PATH);
                $pe = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if ($pe === 'jpeg') $pe = 'jpg';
                if (in_array($pe, ['jpg', 'png', 'svg', 'webp', 'gif'], true)) {
                    $ext = $pe;
                } else {
                    $ext = 'jpg';
                }
            }
        }
        // ---- already a bare id_img key (no re-download) ----
        elseif (preg_match('#^[A-Za-z0-9_\-]+$#', $source)) {
            $st = $conn->prepare('SELECT id_img FROM thumb WHERE id_img = ? LIMIT 1');
            if ($st) {
                $st->bind_param('s', $source);
                $st->execute();
                $row = $st->get_result()->fetch_assoc();
                $st->close();
                if ($row) {
                    $up = $conn->prepare('UPDATE thumb SET act = ? WHERE id_img = ? LIMIT 1');
                    if ($up) { $up->bind_param('is', $act, $source); $up->execute(); $up->close(); }
                    return $source;
                }
            }
            return '';
        } else {
            return '';
        }

        if ($binary === null) return '';

        // ---------------------------------------------------------------
        // tmb (act=1) and all non-ico roles → ALWAYS jpg, base key only
        // ico (act=6) → keep real format (png/svg/jpg/…)
        // ---------------------------------------------------------------
        $forceJpg = ($act !== 6);

        if ($forceJpg) {
            $ext = 'jpg';
            $finalKey = $base;              // u11210717t
            $filename = $base . '.jpg';     // u11210717t.jpg
        } else {
            // ico only
            if ($ext === 'jpg') {
                $finalKey = $base;          // u11210717i
                $filename = $base . '.jpg';
            } else {
                $finalKey = $base . $ext;   // u11210717ipng / u11210717isvg
                $filename = $base . '.' . $ext;
            }
        }

        // h_api lives in /int/ — public thumbs stay at site root /thumb/
        $dir = dirname(__DIR__) . '/thumb';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir . '/' . $filename;

        if ($forceJpg || $ext === 'jpg') {
            // Always produce a real JPEG when possible
            if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
                $im = @imagecreatefromstring($binary);
                if ($im) {
                    // flatten alpha on white for PNG→JPG
                    if (function_exists('imagecreatetruecolor') && function_exists('imagefill')) {
                        $w = imagesx($im);
                        $h = imagesy($im);
                        $bg = imagecreatetruecolor($w, $h);
                        $white = imagecolorallocate($bg, 255, 255, 255);
                        imagefill($bg, 0, 0, $white);
                        imagecopy($bg, $im, 0, 0, 0, 0, $w, $h);
                        imagedestroy($im);
                        $im = $bg;
                    }
                    $okJpg = @imagejpeg($im, $path, 90);
                    imagedestroy($im);
                    if (!$okJpg) return '';
                } else {
                    // Not a raster GD can read (e.g. SVG) — still write bytes as .jpg name
                    // so path exists; browser may not render SVG-as-jpg. Prefer rejecting SVG for tmb:
                    if (stripos($binary, '<svg') !== false) {
                        // SVG cannot become real JPG without Imagick — skip
                        return '';
                    }
                    if (@file_put_contents($path, $binary) === false) return '';
                }
            } else {
                if (stripos($binary, '<svg') !== false) return '';
                if (@file_put_contents($path, $binary) === false) return '';
            }
        } else {
            // ico png/svg/webp/gif — raw bytes
            if (@file_put_contents($path, $binary) === false) return '';
        }

        // Do not register DB row if file did not land on disk
        if (!is_file($path) || filesize($path) < 8) {
            return '';
        }

        // upsert thumb row with $finalKey (same as before)
        $st = $conn->prepare('SELECT id_img FROM thumb WHERE id_img = ? LIMIT 1');
        if ($st) {
            $st->bind_param('s', $finalKey);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            $st->close();
            if ($row) {
                $up = $conn->prepare('UPDATE thumb SET act = ? WHERE id_img = ? LIMIT 1');
                if ($up) { $up->bind_param('is', $act, $finalKey); $up->execute(); $up->close(); }
                return $finalKey;
            }
        }
        $ins = $conn->prepare('INSERT INTO thumb (id_img, act) VALUES (?, ?)');
        if (!$ins) return '';
        $ins->bind_param('si', $finalKey, $act);
        if (!$ins->execute()) {
            $ins->close();
            return '';
        }
        $ins->close();
        return $finalKey;
    }

    function ATMB($id) {
        global $conn;
        $id = (int)$id;
        if ($id <= 0) return '';

        // 1) Song thumb linked to this artist
        $sql_tsng = "
            SELECT song.song_data
            FROM song
            INNER JOIN song2artist ON song2artist.id_song = song.id_song
            WHERE song2artist.id_artist = $id
              AND song.song_act != 0
            ORDER BY song.id_song DESC
            LIMIT 20
        ";
        $result_tsng = $conn->query($sql_tsng);
        if ($result_tsng) {
            while ($row_tsng = $result_tsng->fetch_assoc()) {
                $song_data = json_decode($row_tsng['song_data'] ?? '');
                if (!is_object($song_data) || empty($song_data->tmb)) continue;
                $t = ThumbImage($song_data->tmb);
                if ($t !== '') return $t;
            }
        }

        // 2) Album thumb linked to this artist
        $sql_tlbm = "
            SELECT album.album_data
            FROM album
            INNER JOIN album2artist ON album2artist.id_album = album.id_album
            WHERE album2artist.id_artist = $id
              AND album.album_act != 0
            ORDER BY album.id_album DESC
            LIMIT 20
        ";
        $result_tlbm = $conn->query($sql_tlbm);
        if ($result_tlbm) {
            while ($row_tlbm = $result_tlbm->fetch_assoc()) {
                $album_data = json_decode($row_tlbm['album_data'] ?? '');
                if (!is_object($album_data) || empty($album_data->tmb)) continue;
                $t = ThumbImage($album_data->tmb);
                if ($t !== '') return $t;
            }
        }

        return '';
    }
    
    function STMB($id){

        global $conn;
    
        $id = (int)$id;
    
        $sql = "
            SELECT album_data
            FROM album
            LEFT JOIN song2album
                ON song2album.id_album = album.id_album
            WHERE song2album.id_song = $id
            LIMIT 1
        ";
    
        $result = $conn->query($sql);
    
        if(!$result || !$result->num_rows)
            return '';
    
        $row = $result->fetch_assoc();
    
        $album_data = json_decode($row['album_data']);
    
        if(empty($album_data->tmb))
            return '';
    
        return ThumbImage($album_data->tmb);
    
    }        

    function ARTIST_CARD($row_artist) {
        global $IDHM;

        $artist = [];
        $artist['id']   = (int)$row_artist['id_artist'] + (int)$IDHM;
        $artist['name'] = $row_artist['artist_name'];
        $artist['url']  = str_replace(' ', '_', strtolower($row_artist['artist_name']));

        $meta = [];

        if (!empty($row_artist['artist_cc'])) {
            $cc = strtoupper(trim($row_artist['artist_cc']));
            $meta['cc']  = $cc;
            $meta['nat'] = $cc;
        }

        if (!empty($row_artist['artist_bd'])) {
            $meta['bd'] = $row_artist['artist_bd'];
        }

        if (isset($row_artist['artist_act'])) {
            $meta['act'] = (int)$row_artist['artist_act'];
        }

        $artist_data = json_decode($row_artist['artist_data'] ?? '');
        if (!is_object($artist_data)) {
            $artist_data = new stdClass();
        }

        // secondary names from data.names
        if (!empty($artist_data->names) && (is_object($artist_data->names) || is_array($artist_data->names))) {
            $artist['names'] = (array)$artist_data->names;
        }

        if (isset($artist_data->des) && $artist_data->des !== '' && $artist_data->des !== null) {
            $meta['des'] = is_string($artist_data->des)
                ? $artist_data->des
                : (string)$artist_data->des;
        }

        // media — same plan-B keys as song/video (strings, not ints)
        $thumb = '';
        if (!empty($artist_data->tmb)) {
            $thumb = ThumbImage($artist_data->tmb);
        }
        if ($thumb === '' || $thumb === null) {
            $thumb = ATMB($row_artist['id_artist']);
        }
        if ($thumb) {
            $meta['tmb'] = $thumb;
        }

        if (!empty($artist_data->tmv)) {
            $v = ThumbImage($artist_data->tmv);
            if ($v) $meta['tmv'] = $v;
        }
        if (!empty($artist_data->cvr)) {
            $v = ThumbImage($artist_data->cvr);
            if ($v) $meta['cvr'] = $v;
        }
        if (!empty($artist_data->art)) {
            $v = ThumbImage($artist_data->art);
            if ($v) $meta['art'] = $v;
        }
        if (!empty($artist_data->gal)) {
            if (is_array($artist_data->gal) || is_object($artist_data->gal)) {
                $galOut = [];
                foreach ((array)$artist_data->gal as $g) {
                    $k = is_string($g) ? $g
                        : (is_object($g) && isset($g->id_img) ? (string)$g->id_img : '');
                    if ($k === '') continue;
                    $t = ThumbImage($k);
                    if ($t) $galOut[] = $t;
                }
                if ($galOut) $meta['gal'] = $galOut;
            } else {
                $t = ThumbImage((string)$artist_data->gal);
                if ($t) $meta['gal'] = $t;
            }
        }

        if (!empty($artist_data->ytm)) $meta['ytm'] = $artist_data->ytm;
        if (!empty($artist_data->ins)) $meta['ins'] = $artist_data->ins;
        if (!empty($artist_data->tlg)) $meta['tlg'] = $artist_data->tlg;

        // legacy comma-list until artist2genre is fully used
        if (!empty($artist_data->gnr)) {
            $meta['gnr'] = array_map('intval', explode(',', (string)$artist_data->gnr));
        }

        if (!empty($meta)) {
            $artist['meta'] = $meta;
        }

        return $artist;
    }
    
    function ARTIST($ID) {
        global $conn, $IDHM;
    
        $ID = (int)$ID;
        
        $actClause = $allowInactive ? '1=1' : 'artist_act != 0';
        $sql_artist = "
            SELECT * FROM artist
            WHERE id_artist = $ID AND $actClause
            LIMIT 1
        ";
    
        $result_artist = $conn->query($sql_artist);
        if (!$result_artist || !$result_artist->num_rows) {
            return null;
        }
    
        $row_artist = $result_artist->fetch_assoc();
    
        // پایه = همان مینی‌کارت (همهٔ meta بالا اینجاست)
        $artist_obj = [
            "artist" => ARTIST_CARD($row_artist)
        ];
        $A = &$artist_obj["artist"];
        $A['albums'] = [];
        $A['videos'] = [];
        $A['musicvideos'] = [];
        $A['songs'] = [];
        $A['podcasts'] = [];
        
        if (!isset($A["meta"]) || !is_array($A["meta"])) {
            $A["meta"] = [];
        }
    
        // کشور را دوباره محکم کن (اگر ستون پر باشد)
        if (!empty($row_artist["artist_cc"])) {
            $cc = strtoupper(trim($row_artist["artist_cc"]));
            $A["meta"]["cc"]  = $cc;
            $A["meta"]["nat"] = $cc;
        }
    
        // فالوئر از جدول favorites
        $A["meta"]["flw"] = 0;
        $favType = ContentTypeToInt('artist');
        if ($favType !== null) {
            $aid = (int)$row_artist["id_artist"];
            $cntSql = $conn->prepare("
                SELECT COUNT(*) AS c
                FROM user_favorites
                WHERE item_type = ? AND item_id = ?
            ");
            if ($cntSql) {
                $cntSql->bind_param('ii', $favType, $aid);
                $cntSql->execute();
                $cr = $cntSql->get_result()->fetch_assoc();
                $A["meta"]["flw"] = $cr ? (int)$cr["c"] : 0;
            }
        }
    
        // ژانر از artist2genre (جایگزین / تکمیل gnr)
        if (function_exists('LoadArtistGenres')) {
            $genres = LoadArtistGenres((int)$row_artist["id_artist"]);
            if ($genres) {
                $A["genres"] = $genres;
                $A["meta"]["gnr"] = array_map(function ($g) {
                    return (int)$g["id"];
                }, $genres);
            }
        }
    
        // ---- Albums ----
        $sql_album = "
            SELECT album.*
            FROM album2artist
            JOIN album ON album.id_album = album2artist.id_album
            WHERE album2artist.id_artist = " . (int)$row_artist["id_artist"] . "
              AND album.album_act != 0
            ORDER BY album.album_rd DESC
        ";
        $result_album = $conn->query($sql_album);
        if ($result_album && $result_album->num_rows) {
            $A["meta"]["nca"] = $result_album->num_rows;
            while ($row_album = $result_album->fetch_assoc()) {
                $album = ALBUM_CARD($row_album, false);
                $album["artists"] = [(int)$row_artist["id_artist"] + $IDHM];
                $A["albums"][] = $album;
            }
        }
    
        // ---- Videos / MusicVideos ----
        $sql_video = "
            SELECT video.*
            FROM video2artist
            JOIN video ON video.id_video = video2artist.id_video
            WHERE video2artist.id_artist = " . (int)$row_artist["id_artist"] . "
              AND video.video_act != 0
            ORDER BY video.video_rd DESC
        ";
        $result_video = $conn->query($sql_video);
        if ($result_video && $result_video->num_rows) {
            $musicvideo_count = 0;
            $video_count = 0;
            while ($row_video = $result_video->fetch_assoc()) {
                $video = VIDEO_CARD($row_video, false);
                $video["artists"] = [(int)$row_artist["id_artist"] + $IDHM];
                $isUserVideo = !empty($video["meta"]["type"]) && $video["meta"]["type"] === "video";
                if ($isUserVideo) {
                    $A["videos"][] = $video;
                    $video_count++;
                } else {
                    $A["musicvideos"][] = $video;
                    $musicvideo_count++;
                }
            }
            if ($musicvideo_count > 0) $A["meta"]["ncm"] = $musicvideo_count;
            if ($video_count > 0)      $A["meta"]["ncv"] = $video_count;
        }
    
        // ---- Songs / Podcasts ----
        $sql_song = "
            SELECT song.*
            FROM song2artist
            JOIN song ON song.id_song = song2artist.id_song
            WHERE song2artist.id_artist = " . (int)$row_artist["id_artist"] . "
              AND song.song_act != 0
            ORDER BY song.song_rd DESC
        ";
        $result_song = $conn->query($sql_song);
        if ($result_song && $result_song->num_rows) {
            $song_count = 0;
            $podcast_count = 0;
            while ($row_song = $result_song->fetch_assoc()) {
                $song = SONG_CARD($row_song, false);
                $song["artists"] = [(int)$row_artist["id_artist"] + $IDHM];
                $isPodcast = !empty($song["meta"]["type"]) && $song["meta"]["type"] === "podcast";
                if ($isPodcast) {
                    $A["podcasts"][] = $song;
                    $podcast_count++;
                } else {
                    $A["songs"][] = $song;
                    $song_count++;
                }
            }
            if ($song_count > 0)    $A["meta"]["ncs"] = $song_count;
            if ($podcast_count > 0) $A["meta"]["ncp"] = $podcast_count;
        }
    
        $A["full"] = true;
    
        return $artist_obj;
    }
    
    if ($Page == "artist" && $QJS == 'ep') {
    
        // resolve
        if ($Q1 === 'resolve') {
            $INPUT = file_get_contents('php://input');
            $JSD   = json_decode($INPUT);
    
            $publicIds = [];
            if (isset($JSD->ids) && is_array($JSD->ids)) {
                $publicIds = $JSD->ids;
            } elseif (is_array($JSD)) {
                $publicIds = $JSD;
            }
    
            $realIds = [];
            foreach ($publicIds as $pid) {
                $rid = (int)$pid - (int)$IDHM;
                if ($rid > 0) $realIds[] = $rid;
            }
            $realIds = array_values(array_unique($realIds));
            if (count($realIds) > 100) {
                $realIds = array_slice($realIds, 0, 100);
            }
    
            $artists_obj = [];
            if (count($realIds) > 0) {
                $in = implode(',', array_map('intval', $realIds));
                $sql = "
                    SELECT *
                    FROM artist
                    WHERE id_artist IN ($in)
                      AND artist_act != 0
                ";
                $result = $conn->query($sql);
                $byId = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $byId[(int)$row["id_artist"]] = ARTIST_CARD($row);
                    }
                }
                foreach ($realIds as $rid) {
                    if (isset($byId[$rid])) {
                        $artists_obj[] = $byId[$rid];
                    }
                }
            }
    
            echo json_encode($artists_obj);
            exit();
        }
    //ORDER BY artist_flw DESC
        if ($Q1 === 'top') {
            $artists_obj = [];
            $sql_artist = "
                SELECT *
                FROM artist
                WHERE artist_act != 0
                LIMIT 21
            ";
            $result_artist = $conn->query($sql_artist);
            if ($result_artist) {
                while ($row_artist = $result_artist->fetch_assoc()) {
                    $artists_obj[] = ARTIST_CARD($row_artist);
                }
            }
            echo json_encode($artists_obj);
            exit();
        }
    
        if (is_numeric($Q1)) {
            $ID = (int)$Q1 - (int)$IDHM;
            $artist_obj = ARTIST($ID);
            if (!$artist_obj) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
            echo json_encode($artist_obj);
            exit();
        }
    
        echo json_encode(['error' => 'invalid_action']);
        exit();
    }

    function ALBUM_CARD($row_album, $withArtists = true) {
        global $conn, $IDHM;

        $albumName = !empty($row_album['album_name'])
            ? $row_album['album_name']
            : (!empty($row_album['album_en']) ? $row_album['album_en'] : '');

        $album = [];
        $album['id']   = (int)$row_album['id_album'] + (int)$IDHM;
        $album['name'] = ucwords($albumName);
        $album['url']  = str_replace(' ', '_', strtolower($albumName));
        // no name_fa — secondary titles live in data.names

        $meta = [];
        $meta['act'] = isset($row_album['album_act']) ? (int)$row_album['album_act'] : 1;
        if (!empty($row_album['album_rd'])) {
            $meta['rls'] = $row_album['album_rd'];
        }

        $album_data = json_decode($row_album['album_data']);
        if (!empty($album_data->names) && (is_object($album_data->names) || is_array($album_data->names))) {
            $album['names'] = $album_data->names;
        }
        if (!empty($album_data->des)) {
            $meta['des'] = is_string($album_data->des)
                ? $album_data->des
                : (string)$album_data->des;
        }
        if (!empty($album_data->tmb)) {
            $thumb = ThumbImage($album_data->tmb);
            if ($thumb) $meta['tmb'] = $thumb;
        }
        if (!empty($album_data->tmv)) {
            $t = ThumbImage($album_data->tmv);
            if ($t) $meta['tmv'] = $t;
        }
        if (!empty($album_data->cvr)) {
            $t = ThumbImage($album_data->cvr);
            if ($t) $meta['cvr'] = $t;
        }
        if (!empty($album_data->art)) {
            // art may be id_img string (plan B) or legacy int
            if (is_string($album_data->art) && !is_numeric($album_data->art)) {
                $t = ThumbImage($album_data->art);
                if ($t) $meta['art'] = $t;
            } else {
                $meta['art'] = (int)$album_data->art;
            }
        }
        if (!empty($album_data->gal) && (is_array($album_data->gal) || is_object($album_data->gal))) {
            $gal = [];
            foreach ((array)$album_data->gal as $gk) {
                $t = ThumbImage($gk);
                if ($t) $gal[] = $t;
            }
            if ($gal) $meta['gal'] = $gal;
        }

        // genres / languages from relation tables (type album = 4)
        $typeInt = ContentTypeToInt('album');
        $genres  = LoadItemGenres($typeInt, (int)$row_album['id_album']);
        $langs   = LoadItemLanguages($typeInt, (int)$row_album['id_album']);
        if ($genres) $album['genres'] = $genres;
        if ($langs)  $album['languages'] = $langs;

        if (!empty($meta)) $album['meta'] = $meta;

        if ($withArtists) {
            $sql_artist = "
                SELECT artist.*
                FROM album2artist
                JOIN artist ON artist.id_artist = album2artist.id_artist
                WHERE album2artist.id_album = " . (int)$row_album['id_album'] . "
                ORDER BY album2artist.id_artist
            ";
            $result_artist = $conn->query($sql_artist);
            if ($result_artist) {
                while ($row_artist = $result_artist->fetch_assoc()) {
                    $artist = [];
                    $artist['id']   = (int)$row_artist['id_artist'] + (int)$IDHM;
                    $artist['name'] = $row_artist['artist_name'];
                    $artist['url']  = str_replace(' ', '_', strtolower($row_artist['artist_name']));
                    $ameta = [];
                    $artist_data = json_decode($row_artist['artist_data'] ?? '');
                    if (!empty($artist_data->names) && (is_object($artist_data->names) || is_array($artist_data->names))) {
                        $artist['names'] = (array)$artist_data->names;
                    }
                    if (!empty($artist_data->tmb)) {
                        $thumb = ThumbImage($artist_data->tmb);
                        if ($thumb) $ameta['tmb'] = $thumb;
                    }
                    if (!empty($ameta)) $artist['meta'] = $ameta;
                    $album['artists'][] = $artist;
                }
            }
        }

        return $album;
    }
    
    function ALBUM($ID) {
        global $conn, $IDHM;

        $ID = (int)$ID;

        // admin panel act9 may open deactivated albums
        $callerId  = 0;
        $callerAct = 0;
        try {
            $callerId = (int) GetUserIdFromAccessToken();
            if ($callerId > 0) {
                $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                if ($stU) {
                    $stU->bind_param('i', $callerId);
                    $stU->execute();
                    $ru = $stU->get_result()->fetch_assoc();
                    $stU->close();
                    if ($ru) $callerAct = (int)$ru['act'];
                }
            }
        } catch (Throwable $e) {
            $callerId = 0;
            $callerAct = 0;
        }
        $isAdminPanel = (
            isset($_SERVER['HTTP_X_HAMRAHANG_CLIENT'])
            && $_SERVER['HTTP_X_HAMRAHANG_CLIENT'] === 'cdn_admin'
        );
        $allowInactive = ($isAdminPanel && $callerAct >= 9);

        $sql_album = "
            SELECT *
            FROM album
            WHERE id_album = $ID
        ";
        if (!$allowInactive) {
            $sql_album .= " AND album_act != 0";
        }
        $sql_album .= " LIMIT 1";

        $result_album = $conn->query($sql_album);
        if (!$result_album || !$result_album->num_rows) {
            return null;
        }

        $row_album = $result_album->fetch_assoc();
        $album_obj = ['album' => []];

        $albumName = !empty($row_album['album_name'])
            ? $row_album['album_name']
            : (!empty($row_album['album_en']) ? $row_album['album_en'] : '');

        $album_obj['album']['id']   = (int)$row_album['id_album'] + (int)$IDHM;
        $album_obj['album']['name'] = ucwords($albumName);
        $album_obj['album']['url']  = str_replace(' ', '_', strtolower($albumName));

        $meta = [];
        $meta['act'] = isset($row_album['album_act']) ? (int)$row_album['album_act'] : 1;
        if (!empty($row_album['album_rd'])) {
            $meta['rls'] = $row_album['album_rd'];
        }

        $album_data = json_decode($row_album['album_data']);
        if (!empty($album_data->names) && (is_object($album_data->names) || is_array($album_data->names))) {
            $album_obj['album']['names'] = $album_data->names;
        }
        if (!empty($album_data->des)) {
            $meta['des'] = is_string($album_data->des)
                ? $album_data->des
                : (string)$album_data->des;
        }
        if (!empty($album_data->tmb)) {
            $thumb = ThumbImage($album_data->tmb);
            if ($thumb) $meta['tmb'] = $thumb;
        }
        if (!empty($album_data->tmv)) {
            $t = ThumbImage($album_data->tmv);
            if ($t) $meta['tmv'] = $t;
        }
        if (!empty($album_data->cvr)) {
            $t = ThumbImage($album_data->cvr);
            if ($t) $meta['cvr'] = $t;
        }
        if (!empty($album_data->art) && is_string($album_data->art) && !is_numeric($album_data->art)) {
            $t = ThumbImage($album_data->art);
            if ($t) $meta['art'] = $t;
        }
        if (!empty($album_data->gal) && (is_array($album_data->gal) || is_object($album_data->gal))) {
            $gal = [];
            foreach ((array)$album_data->gal as $gk) {
                $t = ThumbImage($gk);
                if ($t) $gal[] = $t;
            }
            if ($gal) $meta['gal'] = $gal;
        }

        $typeInt = ContentTypeToInt('album');
        $genres  = LoadItemGenres($typeInt, (int)$row_album['id_album']);
        $langs   = LoadItemLanguages($typeInt, (int)$row_album['id_album']);
        if ($genres) $album_obj['album']['genres'] = $genres;
        if ($langs)  $album_obj['album']['languages'] = $langs;

        if (!empty($meta)) {
            $album_obj['album']['meta'] = $meta;
        }

        $albumTmb = !empty($meta['tmb']) ? $meta['tmb'] : '';

        // Tracks: song table only + trk_num
        $sql_song = "
            SELECT song.*, song2album.trk_num
            FROM song2album
            JOIN song ON song.id_song = song2album.id_song
            WHERE song2album.id_album = " . (int)$row_album['id_album'] . "
        ";
        if (!$allowInactive) {
            $sql_song .= " AND song.song_act != 0";
        }
        $sql_song .= " ORDER BY song2album.trk_num ASC, song.id_song ASC";

        $result_song = $conn->query($sql_song);
        if ($result_song) {
            while ($row_song = $result_song->fetch_assoc()) {
                $song = SONG_CARD($row_song);
                $song['trk'] = isset($row_song['trk_num']) ? (int)$row_song['trk_num'] : 0;
                if (empty($song['meta']['tmb']) && $albumTmb) {
                    if (!isset($song['meta'])) $song['meta'] = [];
                    $song['meta']['tmb'] = $albumTmb;
                }
                $album_obj['album']['songs'][] = $song;
            }
        }

        // Artists
        $sql_artist = "
            SELECT artist.*
            FROM album2artist
            JOIN artist ON artist.id_artist = album2artist.id_artist
            WHERE album2artist.id_album = " . (int)$row_album['id_album'] . "
            ORDER BY album2artist.id_artist
        ";
        $result_artist = $conn->query($sql_artist);
        if ($result_artist) {
            while ($row_artist = $result_artist->fetch_assoc()) {
                $artist = [];
                $artist['id']   = (int)$row_artist['id_artist'] + (int)$IDHM;
                $artist['name'] = $row_artist['artist_name'];
                $artist['url']  = str_replace(' ', '_', strtolower($row_artist['artist_name']));
                $ameta = [];
                $artist_data = json_decode($row_artist['artist_data'] ?? '');
                if (!empty($artist_data->names) && (is_object($artist_data->names) || is_array($artist_data->names))) {
                    $artist['names'] = (array)$artist_data->names;
                }
                if (!empty($artist_data->tmb)) {
                    $thumb = ThumbImage($artist_data->tmb);
                    if ($thumb) $ameta['tmb'] = $thumb;
                }
                if (!empty($ameta)) $artist['meta'] = $ameta;
                $album_obj['album']['artists'][] = $artist;
            }
        }

        return $album_obj;
    }
    
    if ($Page == "album" && $QJS == 'ep') {
    
        // resolve
        if ($Q1 === 'resolve') {
            $INPUT = file_get_contents('php://input');
            $JSD   = json_decode($INPUT);
    
            $publicIds = [];
            if (isset($JSD->ids) && is_array($JSD->ids)) {
                $publicIds = $JSD->ids;
            } elseif (is_array($JSD)) {
                $publicIds = $JSD;
            }
    
            $realIds = [];
            foreach ($publicIds as $pid) {
                $rid = (int)$pid - (int)$IDHM;
                if ($rid > 0) $realIds[] = $rid;
            }
            $realIds = array_values(array_unique($realIds));
            if (count($realIds) > 100) {
                $realIds = array_slice($realIds, 0, 100);
            }
    
            $albums_obj = [];
            if (count($realIds) > 0) {
                $in = implode(',', array_map('intval', $realIds));
                $sql = "
                    SELECT *
                    FROM album
                    WHERE id_album IN ($in)
                      AND album_act != 0
                ";
                $result = $conn->query($sql);
                $byId = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $byId[(int)$row["id_album"]] = ALBUM_CARD($row);
                    }
                }
                foreach ($realIds as $rid) {
                    if (isset($byId[$rid])) {
                        $albums_obj[] = $byId[$rid];
                    }
                }
            }
    
            echo json_encode($albums_obj);
            exit();
        }
    
        if (is_numeric($Q1)) {
            $ID = (int)$Q1 - (int)$IDHM;
            $album_obj = ALBUM($ID);
            if (!$album_obj) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
            echo json_encode($album_obj);
            exit();
        }
    
        switch ($Q1) {
            case 'latest':
                $QACT = 'album.album_act != 0';
                break;
            case 'featured':
                $QACT = 'album.album_act = 2';
                break;
            default:
                echo json_encode(['error' => 'invalid_action']);
                exit();
        }
    
        $albums_obj = [];
        $sql_album = "
            SELECT *
            FROM album
            WHERE $QACT
            ORDER BY album.album_rd DESC
            LIMIT 40
        ";
        $result_album = $conn->query($sql_album);
        if ($result_album && $result_album->num_rows > 0) {
            while ($row_album = $result_album->fetch_assoc()) {
                $albums_obj[] = ALBUM_CARD($row_album);
            }
        }
    
        echo json_encode($albums_obj);
        exit();
    }

    function SONG($ID, $countPlay = true) {

        global $conn, $IDHM;

        $ID = (int)$ID;

        // ---- who is calling? (Bearer optional) ----
        $callerId  = 0;
        $callerAct = 0;
        try {
            $callerId = (int) GetUserIdFromAccessToken();
            if ($callerId > 0) {
                $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                if ($stU) {
                    $stU->bind_param('i', $callerId);
                    $stU->execute();
                    $ru = $stU->get_result()->fetch_assoc();
                    $stU->close();
                    if ($ru) $callerAct = (int)$ru['act'];
                }
            }
        } catch (Throwable $e) {
            $callerId  = 0;
            $callerAct = 0;
        }

        // Admin panel only (index.php sends this header). Consumer apps never send it.
        $isAdminPanel = (
            isset($_SERVER['HTTP_X_HAMRAHANG_CLIENT'])
            && $_SERVER['HTTP_X_HAMRAHANG_CLIENT'] === 'cdn_admin'
        );

        // act9 on panel may open deactivated songs
        $allowInactive = ($isAdminPanel && $callerAct >= 9);

        $sql_song = "
            SELECT *
            FROM song
            WHERE id_song = $ID
        ";
        if (!$allowInactive) {
            $sql_song .= " AND song_act != 0";
        }
        $sql_song .= " LIMIT 1";

        $result_song = $conn->query($sql_song);

        if (!$result_song || $result_song->num_rows === 0) {
            return null;
        }

        $row_song = $result_song->fetch_assoc();
        $song_obj = [
            "song" => []
        ];

        // play count — skip when admin panel is just loading the editor
        if ($countPlay && !$isAdminPanel) {
            $conn->query("UPDATE song SET song_ply = song_ply + 1 WHERE id_song = " . (int)$row_song["id_song"]);
            $row_song["song_ply"] = $row_song["song_ply"] + 1;
        }

        $song_obj["song"]["id"]   = $row_song["id_song"] + $IDHM;
        $song_obj["song"]["name"] = $row_song["song_name"];
        $song_obj["song"]["url"] = str_replace(' ', '_', strtolower($row_song["song_name"]));

        // Artists
        $sql_artist = "
            SELECT artist.*
            FROM song2artist
            JOIN artist ON artist.id_artist = song2artist.id_artist
            WHERE song2artist.id_song = " . (int)$row_song["id_song"] . "
            ORDER BY song2artist.id_artist
        ";
        $result_artist = $conn->query($sql_artist);
        if ($result_artist) {
            while ($row_artist = $result_artist->fetch_assoc()) {
                $artist = [];
                $artist["id"]   = $row_artist["id_artist"] + $IDHM;
                $artist["name"] = $row_artist["artist_name"];
                $artist["url"] = str_replace(' ', '_', strtolower($row_artist["artist_name"]));

                $ameta = [];
                $artist_data = json_decode($row_artist['artist_data'] ?? '');
                if (!empty($artist_data->names) && (is_object($artist_data->names) || is_array($artist_data->names))) {
                    $artist['names'] = (array)$artist_data->names;
                }
                if (!empty($artist_data->tmb)) {
                    $thumb = ThumbImage($artist_data->tmb);
                    if ($thumb) $ameta["tmb"] = $thumb;
                }
                if (!empty($ameta)) $artist["meta"] = $ameta;

                $song_obj["song"]["artists"][] = $artist;
            }
        }

        // Album links (song2album)
        $sql_parent = "
            SELECT album.*, song2album.trk_num
            FROM song2album
            JOIN album ON album.id_album = song2album.id_album
            WHERE song2album.id_song = " . (int)$row_song["id_song"] . "
        ";
        if (!$allowInactive) {
            $sql_parent .= " AND album.album_act != 0";
        }
        $sql_parent .= " ORDER BY song2album.trk_num ASC, album.id_album ASC";

        $result_parent = $conn->query($sql_parent);
        if ($result_parent) {
            while ($row_parent = $result_parent->fetch_assoc()) {
                $parent = [];
                $parent["type"] = "album";
                $parent["id"]   = $row_parent["id_album"] + $IDHM;
                $albumName = !empty($row_parent["album_name"])
                    ? $row_parent["album_name"]
                    : (!empty($row_parent["album_en"]) ? $row_parent["album_en"] : '');
                $parent["name"] = ucwords($albumName);
                $parent["url"]  = str_replace(' ', '_', strtolower($albumName));
                $parent["trk"]  = isset($row_parent["trk_num"]) ? (int)$row_parent["trk_num"] : 0;

                $metaA = [];
                $album_data = json_decode($row_parent["album_data"]);
                if (!empty($album_data->tmb)) {
                    $thumb = ThumbImage($album_data->tmb);
                    if ($thumb) $metaA["tmb"] = $thumb;
                }
                if (!empty($metaA)) $parent["meta"] = $metaA;

                $song_obj["song"]["parents"][] = $parent;
                $song_obj["song"]["albums"][]  = $parent;
            }
        }

        // Video links (song2video) — always expose arrays (even empty)
        $song_obj["song"]["videos"] = [];
        $song_obj["song"]["musicvideos"] = [];
        $sql_parent = "
            SELECT video.*
            FROM song2video
            JOIN video ON video.id_video = song2video.id_video
            WHERE song2video.id_song = " . (int)$row_song["id_song"] . "
        ";
        if (!$allowInactive) {
            $sql_parent .= " AND video.video_act != 0";
        }

        $result_parent = $conn->query($sql_parent);
        if ($result_parent) {
            while ($row_parent = $result_parent->fetch_assoc()) {
                $parent = [];
                $parent["id"]   = $row_parent["id_video"] + $IDHM;
                $parent["name"] = $row_parent["video_name"];
                $parent["url"]  = str_replace(' ', '_', strtolower($row_parent["video_name"]));

                $metaV = [];
                $video_data = json_decode($row_parent["video_data"]);
                if (!empty($video_data->typ) && (int)$video_data->typ === 2) {
                    $parent["type"] = "video";
                    $metaV["type"]  = "video";
                } else {
                    $parent["type"] = "musicvideo";
                    $metaV["type"]  = "musicvideo";
                }
                if (!empty($video_data->tmb)) {
                    $thumb = ThumbImage($video_data->tmb);
                    if ($thumb) $metaV["tmb"] = $thumb;
                }
                // artists of video (optional mini refs) — keep light for full song card
                if (!empty($metaV)) $parent["meta"] = $metaV;

                $song_obj["song"]["parents"][] = $parent;
                if ($parent["type"] === "video") {
                    $song_obj["song"]["videos"][] = $parent;
                } else {
                    $song_obj["song"]["musicvideos"][] = $parent;
                }
            }
        }

        // Meta — scalars only
        $meta = [];
        $meta["act"] = isset($row_song["song_act"]) ? (int)$row_song["song_act"] : 1;
        if ($row_song["song_rd"])  $meta["rls"] = $row_song["song_rd"];
        if ($row_song["song_ply"]) $meta["ncp"] = $row_song["song_ply"];

        $song_data = json_decode($row_song["song_data"]);
        if (!empty($song_data->art)) $meta["art"] = (int)$song_data->art;
        if (!empty($song_data->des)) $meta["des"] = $song_data->des;
        if (!empty($song_data->dur)) $meta["dur"] = (int)$song_data->dur;

        if (!empty($song_data->names) && (is_object($song_data->names) || is_array($song_data->names))) {
            $song_obj["song"]["names"] = $song_data->names;
        }

        $thumb = '';
        if (!empty($song_data->tmb)) {
            $thumb = ThumbImage($song_data->tmb);
        }
        if (empty($thumb)) {
            $thumb = STMB($row_song["id_song"]);
        }
        if (!empty($thumb)) $meta["tmb"] = $thumb;

        if (!empty($song_data->typ) && (int)$song_data->typ === 2) {
            $meta["type"] = "podcast";
        } else {
            $meta["type"] = "song";
        }

        // ---------------------------------------------------------------
        // Sources (link_song)
        //  - Consumer app / guest: link_act IN (1,2) — original+demo (client uses type)
        //  - Admin panel act7: only own rows (any link_act) + id/user/act
        //  - Admin panel act9: all rows + id/user/act
        // ---------------------------------------------------------------
        $sql_sng_link = "
            SELECT id_sng2lnk, id_user, song_link, song_bit, file_id, file_unq, link_act
            FROM link_song
            WHERE id_song = " . (int)$row_song["id_song"] . "
        ";

        if ($isAdminPanel && $callerAct >= 9) {
            // all acts
        } elseif ($isAdminPanel && $callerAct >= 7 && $callerId > 0) {
            $sql_sng_link .= " AND id_user = " . (int)$callerId;
        } else {
            // app / guest: original + demo (client skips demo for main play via type)
            $sql_sng_link .= " AND link_act IN (1, 2)";
        }
        $sql_sng_link .= " ORDER BY id_sng2lnk ASC";

        $result_sng_link = $conn->query($sql_sng_link);
        if ($result_sng_link && $result_sng_link->num_rows > 0) {
            $ADU_ARR = [];
            while ($row_sng = $result_sng_link->fetch_assoc()) {
                $ARR_song_link = [];
                $ARR_song_link["url"] = $row_sng["song_link"];
                if ($row_sng["song_bit"]) {
                    $ARR_song_link["bit"] = (int)$row_sng["song_bit"];
                }
                // 0=off 1=original 2=demo — public cards only get 1|2
                $ARR_song_link["type"] = SourceActToType((int)$row_sng["link_act"]);

                // Public owner card (logo / ico for player) — always
                $own = OwnerCard((int)$row_sng["id_user"]);
                if ($own) {
                    $ARR_song_link["owner"] = $own;
                }

                // Admin panel only: numeric user + link id/act for the form
                if ($isAdminPanel && $callerAct >= 7) {
                    $ARR_song_link["id"]   = (int)$row_sng["id_sng2lnk"] + (int)$IDHM;
                    $ARR_song_link["user"] = (int)$row_sng["id_user"] + (int)$IDHM;
                    $ARR_song_link["act"]  = (int)$row_sng["link_act"];
                }

                if (!empty($row_sng["file_id"]))  $ARR_song_link["file_id"]  = $row_sng["file_id"];
                if (!empty($row_sng["file_unq"])) $ARR_song_link["file_unq"] = $row_sng["file_unq"];

                $ADU_ARR[] = $ARR_song_link;
            }
            $song_obj["song"]["sources"] = $ADU_ARR;
        }

        // Genres + languages
        $typeStr = (!empty($song_data->typ) && (int)$song_data->typ === 2) ? 'podcast' : 'song';
        $typeInt = ContentTypeToInt($typeStr);

        $genres = LoadItemGenres($typeInt, (int)$row_song['id_song']);
        $langs  = LoadItemLanguages($typeInt, (int)$row_song['id_song']);

        if ($genres) {
            $song_obj['song']['genres'] = $genres;
        }
        if ($langs) {
            $song_obj['song']['languages'] = $langs;
        }

        // Lyrics (full card)
        $lyrics = LoadItemLyrics($typeInt, (int)$row_song['id_song']);
        if ($lyrics) {
            $song_obj['song']['lyrics'] = $lyrics;
            $meta['lrc'] = array_map(function ($x) {
                return [
                    'lng' => $x['lng'],
                    'knd' => $x['knd'],
                    'fmt' => $x['fmt'],
                    'src' => $x['src'],
                ];
            }, $lyrics);
        }

        if (!empty($meta)) {
            $song_obj['song']['meta'] = $meta;
        }

        return $song_obj;
    }

    function SONG_CARD($row_song, $withArtists = true) {
        global $conn, $IDHM;
    
        $song = [];
        $song["id"]   = $row_song["id_song"] + $IDHM;
        $song["name"] = $row_song["song_name"];
        $song["url"] = str_replace(' ', '_', strtolower($row_song["song_name"]));
    
        if ($withArtists) {
            // Artists
            $sql_artist = "
                SELECT artist.*
                FROM song2artist
                JOIN artist ON artist.id_artist = song2artist.id_artist
                WHERE song2artist.id_song = " . (int)$row_song["id_song"] . "
                ORDER BY song2artist.id_artist
            ";
            $result_artist = $conn->query($sql_artist);
            if ($result_artist) {
                while ($row_artist = $result_artist->fetch_assoc()) {
                    $artist = [];
                    $artist["id"]   = $row_artist["id_artist"] + $IDHM;
                    $artist["name"] = $row_artist["artist_name"];
                    $artist["url"] = str_replace(' ', '_', strtolower($row_artist["artist_name"]));
            
                    $ameta = [];
                    $artist_data = json_decode($row_artist["artist_data"] ?? '');
                    if (!empty($artist_data->names) && (is_object($artist_data->names) || is_array($artist_data->names))) {
                        $artist['names'] = (array)$artist_data->names;
                    }
                    if (!empty($artist_data->tmb)) {
                        $thumb = ThumbImage($artist_data->tmb);
                        if ($thumb) $ameta["tmb"] = $thumb;
                    }
                    if (!empty($ameta)) $artist["meta"] = $ameta;
            
                    $song["artists"][] = $artist;
                }
            }
        }
        // Meta — scalars only (no gnr / lan)
        $meta = [];
        $meta["act"] = isset($row_song["song_act"]) ? (int)$row_song["song_act"] : 1;
        if ($row_song["song_rd"])  $meta["rls"] = $row_song["song_rd"];
        if ($row_song["song_ply"]) $meta["ncp"] = (int)$row_song["song_ply"];

        $song_data = json_decode($row_song["song_data"]);
        // do NOT read song_data.gnr / song_data.lan
        if (!empty($song_data->art)) $meta["art"] = (int)$song_data->art;
        if (!empty($song_data->lrc)) $meta["lrc"] = (int)$song_data->lrc;
        if (!empty($song_data->des)) $meta["des"] = $song_data->des;
        if (!empty($song_data->names) && (is_object($song_data->names) || is_array($song_data->names))) {
            $song["names"] = $song_data->names;
        }

        $thumb = '';
        if (!empty($song_data->tmb)) $thumb = ThumbImage($song_data->tmb);
        if (empty($thumb)) $thumb = STMB($row_song["id_song"]);
        if ($thumb) $meta["tmb"] = $thumb;

        if (!empty($song_data->typ) && (int)$song_data->typ === 2) {
            $meta["type"] = "podcast";
        } else {
            $meta["type"] = "song";
        }

        $typeStr = (!empty($song_data->typ) && (int)$song_data->typ === 2) ? 'podcast' : 'song';
        $typeInt = ContentTypeToInt($typeStr);

        $genres = LoadItemGenres($typeInt, (int)$row_song['id_song']);
        $langs  = LoadItemLanguages($typeInt, (int)$row_song['id_song']);

        if ($genres) $song['genres'] = $genres;
        if ($langs)  $song['languages'] = $langs;

        if (!empty($meta)) $song["meta"] = $meta;

        return $song;
    }
    
    if ($Page == "song" && $QJS == 'ep') {
    
        // ---------- resolve: کارت‌های چند id ----------
        if ($Q1 === 'resolve') {
    
            $INPUT = file_get_contents('php://input');
            $JSD   = json_decode($INPUT);
    
            // انتظار: { "ids": [publicId, ...] }
            $publicIds = [];
            if (isset($JSD->ids) && is_array($JSD->ids)) {
                $publicIds = $JSD->ids;
            } elseif (isset($JSD) && is_array($JSD)) {
                $publicIds = $JSD; // اگر مستقیم آرایه فرستاد
            }
    
            $realIds = [];
            foreach ($publicIds as $pid) {
                $rid = (int)$pid - (int)$IDHM;
                if ($rid > 0) $realIds[] = $rid;
            }
            $realIds = array_values(array_unique($realIds));
    
            // سقف امن
            if (count($realIds) > 100) {
                $realIds = array_slice($realIds, 0, 100);
            }
    
            $songs_obj = [];
    
            if (count($realIds) > 0) {
                $in = implode(',', array_map('intval', $realIds));
    
                $sql_song = "
                    SELECT *
                    FROM song
                    WHERE id_song IN ($in)
                      AND song_act != 0
                ";
                $result_song = $conn->query($sql_song);
    
                // برای حفظ ترتیب درخواستی کلاینت
                $byId = [];
                if ($result_song && $result_song->num_rows > 0) {
                    while ($row_song = $result_song->fetch_assoc()) {
                        $byId[(int)$row_song["id_song"]] = SONG_CARD($row_song);
                    }
                }
    
                foreach ($realIds as $rid) {
                    if (isset($byId[$rid])) {
                        $songs_obj[] = $byId[$rid];
                    }
                }
            }

            echo json_encode($songs_obj);
            exit();
        }
        
        if (is_numeric($Q1)) {
            $ID = (int)$Q1 - (int)$IDHM;
            $song_obj = SONG($ID);
            if (!$song_obj) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
            echo json_encode($song_obj);
            exit();
        }

        // latest / top / ...
        switch ($Q1) {
            case 'latest':
                $QACT = 'song.song_act != 0';
                break;
            case 'top':
                $QACT = 'song.song_act = 2';
                break;
            default:
                echo json_encode(['error' => 'invalid_action']);
                exit();
        }
    
        $new_songs_obj = [];
        $sql_song = "
            SELECT *
            FROM song
            WHERE $QACT
            ORDER BY song.song_rd DESC
            LIMIT 40
        ";
        $result_song = $conn->query($sql_song);
    
        if ($result_song && $result_song->num_rows > 0) {
            while ($row_song = $result_song->fetch_assoc()) {
                $new_songs_obj[] = SONG_CARD($row_song);
            }
        }

        echo json_encode($new_songs_obj);
        exit();
    }
    
    function VIDEO_CARD($row_video, $withArtists = true) {
        global $conn, $IDHM;

        $video = [];
        $video['id']   = (int)$row_video['id_video'] + (int)$IDHM;
        $video['name'] = $row_video['video_name'];
        $video['url']  = str_replace(' ', '_', strtolower($row_video['video_name']));
        // no video_fa — secondary names in data.names

        $meta = [];
        $meta['act'] = isset($row_video['video_act']) ? (int)$row_video['video_act'] : 1;
        if (!empty($row_video['video_rd']))  $meta['rls'] = $row_video['video_rd'];
        if (!empty($row_video['video_ply'])) $meta['ncp'] = (int)$row_video['video_ply'];

        $video_data = json_decode($row_video['video_data']);
        if (!empty($video_data->names) && (is_object($video_data->names) || is_array($video_data->names))) {
            $video['names'] = $video_data->names;
        }
        if (!empty($video_data->des)) {
            $meta['des'] = is_string($video_data->des) ? $video_data->des : (string)$video_data->des;
        }
        if (!empty($video_data->dur)) $meta['dur'] = (int)$video_data->dur;

        if (!empty($video_data->tmb)) {
            $t = ThumbImage($video_data->tmb); if ($t) $meta['tmb'] = $t;
        }
        if (!empty($video_data->tmv)) {
            $t = ThumbImage($video_data->tmv); if ($t) $meta['tmv'] = $t;
        }
        if (!empty($video_data->cvr)) {
            $t = ThumbImage($video_data->cvr); if ($t) $meta['cvr'] = $t;
        }
        if (!empty($video_data->art) && is_string($video_data->art) && !is_numeric($video_data->art)) {
            $t = ThumbImage($video_data->art); if ($t) $meta['art'] = $t;
        }
        if (!empty($video_data->gal) && (is_array($video_data->gal) || is_object($video_data->gal))) {
            $gal = [];
            foreach ((array)$video_data->gal as $gk) {
                $t = ThumbImage($gk); if ($t) $gal[] = $t;
            }
            if ($gal) $meta['gal'] = $gal;
        }

        if (!empty($video_data->typ) && (int)$video_data->typ === 2) {
            $meta['type'] = 'video';
        } else {
            $meta['type'] = 'musicvideo';
        }

        $typeStr = $meta['type'];
        $typeInt = ContentTypeToInt($typeStr);
        $genres  = LoadItemGenres($typeInt, (int)$row_video['id_video']);
        $langs   = LoadItemLanguages($typeInt, (int)$row_video['id_video']);
        if ($genres) $video['genres'] = $genres;
        if ($langs)  $video['languages'] = $langs;

        if (!empty($meta)) $video['meta'] = $meta;

        if ($withArtists) {
            $sql_artist = "
                SELECT artist.*
                FROM video2artist
                JOIN artist ON artist.id_artist = video2artist.id_artist
                WHERE video2artist.id_video = " . (int)$row_video['id_video'] . "
                ORDER BY video2artist.id_artist
            ";
            $result_artist = $conn->query($sql_artist);
            if ($result_artist) {
                while ($row_artist = $result_artist->fetch_assoc()) {
                    $artist = [];
                    $artist['id']   = (int)$row_artist['id_artist'] + (int)$IDHM;
                    $artist['name'] = $row_artist['artist_name'];
                    $artist['url']  = str_replace(' ', '_', strtolower($row_artist['artist_name']));
                    $ameta = [];
                    $artist_data = json_decode($row_artist['artist_data'] ?? '');
                    if (!empty($artist_data->names) && (is_object($artist_data->names) || is_array($artist_data->names))) {
                        $artist['names'] = (array)$artist_data->names;
                    }
                    if (!empty($artist_data->tmb)) {
                        $thumb = ThumbImage($artist_data->tmb);
                        if ($thumb) $ameta['tmb'] = $thumb;
                    }
                    if (!empty($ameta)) $artist['meta'] = $ameta;
                    $video['artists'][] = $artist;
                }
            }
        }
        return $video;
    }
    
    function VIDEO($ID) {
        global $conn, $IDHM;

        $ID = (int)$ID;

        $callerId = 0; $callerAct = 0;
        try {
            $callerId = (int) GetUserIdFromAccessToken();
            if ($callerId > 0) {
                $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                if ($stU) {
                    $stU->bind_param('i', $callerId);
                    $stU->execute();
                    $ru = $stU->get_result()->fetch_assoc();
                    $stU->close();
                    if ($ru) $callerAct = (int)$ru['act'];
                }
            }
        } catch (Throwable $e) { $callerId = 0; $callerAct = 0; }
        $isAdminPanel = (
            isset($_SERVER['HTTP_X_HAMRAHANG_CLIENT'])
            && $_SERVER['HTTP_X_HAMRAHANG_CLIENT'] === 'cdn_admin'
        );
        $allowInactive = ($isAdminPanel && $callerAct >= 9);

        $sql_video = "SELECT * FROM video WHERE id_video = $ID";
        if (!$allowInactive) $sql_video .= " AND video_act != 0";
        $sql_video .= " LIMIT 1";

        $result_video = $conn->query($sql_video);
        if (!$result_video || !$result_video->num_rows) return null;

        $row_video = $result_video->fetch_assoc();

        // play++ only for consumer apps
        if (!$isAdminPanel) {
            $conn->query("UPDATE video SET video_ply = video_ply + 1 WHERE id_video = " . (int)$row_video['id_video']);
            $row_video['video_ply'] = (int)$row_video['video_ply'] + 1;
        }

        $card = VIDEO_CARD($row_video);
        if (!isset($card['meta'])) $card['meta'] = [];
        $card['meta']['ncp'] = (int)$row_video['video_ply'];

        $video_obj = ['video' => $card];
        
        // Related songs (song2video) — always expose array (even empty)
        $video_obj['video']['songs'] = [];
        $sql_song = "
            SELECT song.*
            FROM song2video
            JOIN song ON song.id_song = song2video.id_song
            WHERE song2video.id_video = " . (int)$row_video['id_video'] . "
            AND song.song_act != 0
            ORDER BY song.id_song ASC
        ";
        $result_song = $conn->query($sql_song);
        if ($result_song) {
            while ($row_song = $result_song->fetch_assoc()) {
                $video_obj['video']['songs'][] = SONG_CARD($row_song);
            }
        }

        // Sources (link_video) — filter by panel / act like link_song
        $srcSql = "SELECT * FROM link_video WHERE id_video = " . (int)$row_video['id_video'];
        if ($isAdminPanel && $callerAct >= 9) {
            // all acts
        } elseif ($isAdminPanel && $callerAct >= 7) {
            $srcSql .= " AND (link_act != 0 OR id_user = " . (int)$callerId . ")";
        } else {
            $srcSql .= " AND link_act IN (1, 2)";
        }
        $result_source = $conn->query($srcSql);
        if ($result_source && $result_source->num_rows) {
            while ($row = $result_source->fetch_assoc()) {
                $src = [];
                $pk = isset($row['id_vdo2lnk']) ? (int)$row['id_vdo2lnk']
                    : (isset($row['id_lnk']) ? (int)$row['id_lnk'] : 0);
                if ($pk > 0 && $isAdminPanel && $callerAct >= 7) {
                    $src['id'] = $pk + (int)$IDHM;
                }
                $src['url'] = $row['video_link'];
                if (!empty($row['vdo_bit'])) $src['bit'] = (int)$row['vdo_bit'];
                if (isset($row['link_act'])) {
                    $src['type'] = SourceActToType((int)$row['link_act']);
                }

                $own = OwnerCard(isset($row['id_user']) ? (int)$row['id_user'] : 0);
                if ($own) {
                    $src['owner'] = $own;
                }

                if ($isAdminPanel && $callerAct >= 7) {
                    if (isset($row['id_user'])) $src['user'] = (int)$row['id_user'] + (int)$IDHM;
                    if (isset($row['link_act'])) $src['act'] = (int)$row['link_act'];
                }

                if (!empty($row['file_id']))  $src['file_id']  = $row['file_id'];
                if (!empty($row['file_unq'])) $src['file_unq'] = $row['file_unq'];
                $video_obj['video']['sources'][] = $src;
            }
        }

        $typeStr = (!empty($card['meta']['type']) && $card['meta']['type'] === 'video')
            ? 'video' : 'musicvideo';
        $typeInt = ContentTypeToInt($typeStr);

        $genres = LoadItemGenres($typeInt, (int)$row_video['id_video']);
        $langs  = LoadItemLanguages($typeInt, (int)$row_video['id_video']);
        if ($genres) $video_obj['video']['genres'] = $genres;
        if ($langs)  $video_obj['video']['languages'] = $langs;

        $lyrics = LoadItemLyrics($typeInt, (int)$row_video['id_video']);
        if ($lyrics) {
            $video_obj['video']['lyrics'] = $lyrics;
            $video_obj['video']['meta']['lrc'] = array_map(function ($x) {
                return ['lng' => $x['lng'], 'knd' => $x['knd'], 'fmt' => $x['fmt'], 'src' => $x['src']];
            }, $lyrics);
        }

        return $video_obj;
    }
    
    if ($Page == "video" && $QJS == 'ep') {
    
        // resolve
        if ($Q1 === 'resolve') {
            $INPUT = file_get_contents('php://input');
            $JSD   = json_decode($INPUT);
    
            $publicIds = [];
            if (isset($JSD->ids) && is_array($JSD->ids)) {
                $publicIds = $JSD->ids;
            } elseif (is_array($JSD)) {
                $publicIds = $JSD;
            }
    
            $realIds = [];
            foreach ($publicIds as $pid) {
                $rid = (int)$pid - (int)$IDHM;
                if ($rid > 0) $realIds[] = $rid;
            }
            $realIds = array_values(array_unique($realIds));
            if (count($realIds) > 100) {
                $realIds = array_slice($realIds, 0, 100);
            }
    
            $videos_obj = [];
            if (count($realIds) > 0) {
                $in = implode(',', array_map('intval', $realIds));
                $sql = "
                    SELECT *
                    FROM video
                    WHERE id_video IN ($in)
                      AND video_act != 0
                ";
                $result = $conn->query($sql);
                $byId = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $byId[(int)$row["id_video"]] = VIDEO_CARD($row);
                    }
                }
                foreach ($realIds as $rid) {
                    if (isset($byId[$rid])) {
                        $videos_obj[] = $byId[$rid];
                    }
                }
            }
    
            echo json_encode($videos_obj);
            exit();
        }
    
        if (is_numeric($Q1)) {
            $ID = (int)$Q1 - (int)$IDHM;
            $video_obj = VIDEO($ID);
            if (!$video_obj) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
            echo json_encode($video_obj);
            exit();
        }
    
        switch ($Q1) {
            case 'top':
                $QACT = 'video.video_act = 3';
                break;
            case 'latest':
                $QACT = 'video.video_act != 0';
                break;
            case 'featured':
                $QACT = 'video.video_act = 2';
                break;
            default:
                echo json_encode(['error' => 'invalid_action']);
                exit();
        }
    
        $new_videos_obj = [];
        $sql_video = "
            SELECT *
            FROM video
            WHERE $QACT
            ORDER BY video.video_rd DESC
            LIMIT 40
        ";
        $result_video = $conn->query($sql_video);
        if ($result_video && $result_video->num_rows > 0) {
            while ($row_video = $result_video->fetch_assoc()) {
                $new_videos_obj[] = VIDEO_CARD($row_video);
            }
        }
    
        echo json_encode($new_videos_obj);
        exit();
    }
    
    function PLAYLIST_CARD($row) {
        global $IDHM;
    
        $pl = [];
        $pl["id"]   = (int)$row["id_playlist"] + (int)$IDHM;
        $pl["name"] = $row["playlist_name"];
        $pl["url"]  = str_replace(' ', '_', strtolower($row["playlist_name"]));
    
        if ((int)$row["id_user"] > 0) {
            $card = OwnerCard((int)$row["id_user"]);
            $pl["owner"] = $card ? $card : ['id' => (int)$row["id_user"] + (int)$IDHM, 'name' => 'User'];
        } else {
            $pl["owner"] = [
                'id'   => 0,
                'name' => 'Hamrahang',
                'meta' => new stdClass(),
            ];
            $pl["system"] = true;
        }
    
        $act = (int)$row["pl_act"];
        $pl["public"] = ($act === 2 || $act === 9);
        if ($act === 9) {
            $pl["system"] = true;
        }
    
        $meta = [];
        $data = json_decode($row["playlist_data"] ?? '');
        if (!is_object($data)) {
            $data = new stdClass();
        }
    
        if (!empty($data->names) && is_object($data->names)) {
            if (!empty($data->names->fa)) $pl["name_fa"] = $data->names->fa;
            if (!empty($data->names->en)) $pl["name"] = $data->names->en;
        }
        if (!empty($data->des)) {
            $meta["des"] = is_string($data->des) ? $data->des : (string)$data->des;
        }
        if (!empty($data->tmb)) {
            $thumb = ThumbImage($data->tmb);
            if ($thumb) $meta["tmb"] = $thumb;
        }
        if (isset($data->nci)) {
            $meta["nci"] = (int)$data->nci;
        }
    
        if (!empty($meta)) {
            $pl["meta"] = $meta;
        }
    
        return $pl;
    }

    function PLAYLIST($ID) {
        global $conn, $IDHM;
    
        $ID = (int)$ID;
    
        $sql = "
            SELECT *
            FROM playlist
            WHERE id_playlist = $ID
              AND pl_act != 0
            LIMIT 1
        ";
        $result = $conn->query($sql);
        if (!$result || !$result->num_rows) {
            return null;
        }
    
        $row = $result->fetch_assoc();
        $pl  = PLAYLIST_CARD($row);
        if (!isset($pl["meta"])) $pl["meta"] = [];
    
        $pl["songs"]  = [];
        $pl["videos"] = [];
    
        $sql_items = "
            SELECT item_type, item_id, position
            FROM playlist_items
            WHERE id_playlist = " . (int)$row["id_playlist"] . "
            ORDER BY position ASC
        ";
        $result_items = $conn->query($sql_items);
        if (!$result_items || !$result_items->num_rows) {
            $pl["meta"]["nci"] = 0;
            return ["playlist" => $pl];
        }
    
        // realId => [ position, ... ]  (اگر تکرار نادر بود آخرین position)
        $songPos  = []; // type 0,1
        $videoPos = []; // type 2,3
    
        while ($it = $result_items->fetch_assoc()) {
            $t   = (int)$it["item_type"];
            $rid = (int)$it["item_id"];
            $pos = (int)$it["position"];
            if ($rid <= 0 || $t < 0 || $t > 3) continue;
    
            if ($t === 0 || $t === 1) {
                $songPos[$rid] = $pos;
            } else {
                $videoPos[$rid] = $pos;
            }
        }
    
        // ---- songs table ----
        if (count($songPos)) {
            $in = implode(',', array_map('intval', array_keys($songPos)));
            $res = $conn->query("
                SELECT * FROM song
                WHERE id_song IN ($in) AND song_act != 0
            ");
            if ($res) {
                while ($rs = $res->fetch_assoc()) {
                    $rid  = (int)$rs["id_song"];
                    $card = SONG_CARD($rs);
                    $card["position"] = $songPos[$rid];
                    $pl["songs"][] = $card;
                }
            }
            // اختیاری: مرتب‌سازی songs بر اساس position
            usort($pl["songs"], function ($a, $b) {
                return ($a["position"] ?? 0) <=> ($b["position"] ?? 0);
            });
        }
    
        // ---- video table ----
        if (count($videoPos)) {
            $in = implode(',', array_map('intval', array_keys($videoPos)));
            $res = $conn->query("
                SELECT * FROM video
                WHERE id_video IN ($in) AND video_act != 0
            ");
            if ($res) {
                while ($rv = $res->fetch_assoc()) {
                    $rid  = (int)$rv["id_video"];
                    $card = VIDEO_CARD($rv);
                    $card["position"] = $videoPos[$rid];
                    $pl["videos"][] = $card;
                }
            }
            usort($pl["videos"], function ($a, $b) {
                return ($a["position"] ?? 0) <=> ($b["position"] ?? 0);
            });
        }
    
        $pl["meta"]["nci"] = count($pl["songs"]) + count($pl["videos"]);
    
        return ["playlist" => $pl];
    }

    if ($Page == "playlist" && $QJS == 'ep') {
    
        if ($Q1 === 'resolve') {
            $INPUT = file_get_contents('php://input');
            $JSD   = json_decode($INPUT);
    
            $publicIds = [];
            if (isset($JSD->ids) && is_array($JSD->ids)) {
                $publicIds = $JSD->ids;
            } elseif (is_array($JSD)) {
                $publicIds = $JSD;
            }
    
            $realIds = [];
            foreach ($publicIds as $pid) {
                $rid = (int)$pid - (int)$IDHM;
                if ($rid > 0) $realIds[] = $rid;
            }
            $realIds = array_values(array_unique($realIds));
            if (count($realIds) > 100) {
                $realIds = array_slice($realIds, 0, 100);
            }
    
            $list = [];
            if (count($realIds) > 0) {
                $in = implode(',', array_map('intval', $realIds));
                $sql = "
                    SELECT *
                    FROM playlist
                    WHERE id_playlist IN ($in)
                      AND pl_act != 0
                ";
                $result = $conn->query($sql);
                $byId = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $byId[(int)$row["id_playlist"]] = PLAYLIST_CARD($row);
                    }
                }
                foreach ($realIds as $rid) {
                    if (isset($byId[$rid])) $list[] = $byId[$rid];
                }
            }
    
            echo json_encode($list);
            exit();
        }
    
        if (is_numeric($Q1)) {
            $ID = (int)$Q1 - (int)$IDHM;
            $obj = PLAYLIST($ID);
            if (!$obj) {
                echo json_encode(['error' => 'not_found']);
                exit();
            }
            echo json_encode($obj);
            exit();
        }
    
        switch ($Q1) {
            case 'top':
                // پلی‌لیست‌های ادمین / ویژه
                $QACT = 'pl_act = 9';
                $order = 'updated_at DESC';
                break;
            case 'latest':
                // عمومی + ویژه
                $QACT = 'pl_act IN (2, 9)';
                $order = 'updated_at DESC';
                break;
            default:
                echo json_encode(['error' => 'invalid_action']);
                exit();
        }
    
        $list = [];
        $sql = "
            SELECT *
            FROM playlist
            WHERE $QACT
            ORDER BY $order
            LIMIT 40
        ";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $list[] = PLAYLIST_CARD($row);
            }
        }
    
        echo json_encode($list);
        exit();
    }
    
    if ($Page == "search" && $QJS == 'ep') {

        $q = trim((string)$Q1);
        $search_obj = [
            "search" => [
                "q" => $q,
                "artists" => [],
                "albums" => [],
                "songs" => [],
                "videos" => [],
                "playlists" => []
            ]
        ];

        if ($q === '') {
            echo json_encode($search_obj);
            exit();
        }

        // ---- caller (same pattern as SONG) ----
        $callerId  = 0;
        $callerAct = 0;
        try {
            $callerId = (int) GetUserIdFromAccessToken();
            if ($callerId > 0) {
                $stU = $conn->prepare('SELECT act FROM user WHERE id_user = ? LIMIT 1');
                if ($stU) {
                    $stU->bind_param('i', $callerId);
                    $stU->execute();
                    $ru = $stU->get_result()->fetch_assoc();
                    $stU->close();
                    if ($ru) $callerAct = (int)$ru['act'];
                }
            }
        } catch (Throwable $e) {
            $callerId = 0;
            $callerAct = 0;
        }

        $isAdminPanel = (
            isset($_SERVER['HTTP_X_HAMRAHANG_CLIENT'])
            && $_SERVER['HTTP_X_HAMRAHANG_CLIENT'] === 'cdn_admin'
        );
        // act9 on admin panel may search deactivated items
        $allowInactive = ($isAdminPanel && $callerAct >= 9);

        $like = $conn->real_escape_string($q);
        $likeFull  = '%' . $like . '%';
        $likeStart = $like . '%';

        // ---- artists ----
        if ($allowInactive) {
            $ACTSRC = '1=1';
        } elseif (!empty($_SESSION['loggedin'])) {
            $ACTSRC = 'artist_act != 0';
        } else {
            $ACTSRC = 'artist_act != 3';
        }

        $sql = "
            SELECT * FROM artist
            WHERE (artist_name LIKE '$likeFull' OR artist_data LIKE '$likeFull')
              AND $ACTSRC
            ORDER BY
                CASE
                    WHEN LOWER(artist_name) = LOWER('$like') THEN 1
                    WHEN LOWER(artist_name) LIKE LOWER('$likeStart') THEN 2
                    ELSE 3
                END
            LIMIT 21
        ";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $search_obj["search"]["artists"][] = ARTIST_CARD($row);
            }
        }

        // ---- albums ----
        $albumActSql = $allowInactive ? '1=1' : 'album_act != 0';
        $sql = "
            SELECT * FROM album
            WHERE (album_name LIKE '$likeFull' OR album_data LIKE '$likeFull')
              AND $albumActSql
            ORDER BY
                CASE
                    WHEN LOWER(album_name) = LOWER('$like') THEN 1
                    WHEN LOWER(album_name) LIKE LOWER('$likeStart') THEN 2
                    ELSE 3
                END,
                album_rd DESC
            LIMIT 21
        ";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $search_obj["search"]["albums"][] = ALBUM_CARD($row);
            }
        }

        // ---- songs ----
        $songActSql = $allowInactive ? '1=1' : 'song_act != 0';
        $sql = "
            SELECT * FROM song
            WHERE (
                song_name LIKE '$likeFull'
                OR song_data LIKE '$likeFull'
            )
              AND $songActSql
            ORDER BY
                CASE
                    WHEN LOWER(song_name) = LOWER('$like') THEN 1
                    WHEN LOWER(song_name) LIKE LOWER('$likeStart') THEN 2
                    ELSE 3
                END,
                song_rd DESC
            LIMIT 50
        ";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $search_obj["search"]["songs"][] = SONG_CARD($row);
            }
        }

        // ---- videos ----
        $videoActSql = $allowInactive ? '1=1' : 'video_act != 0';
        $sql = "
            SELECT * FROM video
            WHERE (
                video_name LIKE '$likeFull'
                OR video_data LIKE '$likeFull'
            )
              AND $videoActSql
            ORDER BY
                CASE
                    WHEN LOWER(video_name) = LOWER('$like') THEN 1
                    WHEN LOWER(video_name) LIKE LOWER('$likeStart') THEN 2
                    ELSE 3
                END,
                video_rd DESC
            LIMIT 21
        ";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $search_obj["search"]["videos"][] = VIDEO_CARD($row);
            }
        }

        // ---- playlists ----
        // act9 panel: also allow inactive special lists if you store them with other acts
        $plActSql = $allowInactive ? 'pl_act IN (0,1,2,9)' : 'pl_act IN (2, 9)';
        $sql = "
            SELECT * FROM playlist
            WHERE playlist_name LIKE '$likeFull'
              AND $plActSql
            ORDER BY
                CASE
                    WHEN LOWER(playlist_name) = LOWER('$like') THEN 1
                    WHEN LOWER(playlist_name) LIKE LOWER('$likeStart') THEN 2
                    ELSE 3
                END,
                updated_at DESC
            LIMIT 21
        ";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $search_obj["search"]["playlists"][] = PLAYLIST_CARD($row);
            }
        }

        echo json_encode($search_obj);
        exit();
    }
    
   
?>