<?php
/* ==========================================================================
 *  Hamrahang CDN - Admin Single Page Application
 *  File    : index.php          (deployed at https://cdn.hamrahang.ir/)
 *  Purpose : - render the SPA shell (HTML + CSS3 + ES6, no framework)
 *            - keep the administrator session on the server (PHP session)
 *            - bridge every JSON call to the main application
 *              https://hamrahang.ir/ha.php?_qjs=...  so the browser never
 *              has to fight CORS and no token is stored in localStorage
 *  Runtime : PHP 7.2+  (LiteSpeed / Apache + .htaccess)
 * ========================================================================== */

/* ------------------------------- configuration --------------------------- */
const HRH_API_URL   = 'https://hamrahang.ir/ha.php';   // JSON endpoint of the main app
const HRH_WEB_URL   = 'https://hamrahang.ir/';         // public site (outbound links)
const HRH_THUMB_URL = 'https://hamrahang.ir/thumb/';   // thumbnails live on the main host
const HRH_IDHM      = 11210700;                        // public id offset ($IDHM in ha.php)
const HRH_MIN_ACT   = 7;                               // user.act >= 7 => administrator
const HRH_TIMEOUT   = 20;                              // upstream timeout (seconds)
const HRH_DEVICE    = 'cdn_admin';                     // device_type stored in user_devices

/* ------------------------------- session --------------------------------- */
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');

@ini_set('session.use_strict_mode', '1');
@ini_set('session.use_only_cookies', '1');
@ini_set('session.cookie_httponly', '1');
@ini_set('session.cookie_samesite', 'Lax');
@ini_set('session.cookie_secure', $isHttps ? '1' : '0');
session_name('HRHADM');
@session_start();

/* ------------------------------- helpers --------------------------------- */

/** Emit a JSON answer and stop the request. */
function send_json($data, $status = 200)
{
    if ((int) $status !== 200) {
        http_response_code((int) $status);
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Raw request body (JSON payload sent by the SPA). */
function raw_body()
{
    $body = file_get_contents('php://input');
    return ($body === false) ? '' : $body;
}

/** Per session CSRF token, echoed to the client inside the boot object. */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

/** Reject any request that does not carry the token of this session. */
function assert_csrf()
{
    $sent = isset($_SERVER['HTTP_X_ADMIN_TOKEN']) ? (string) $_SERVER['HTTP_X_ADMIN_TOKEN'] : '';
    $own  = csrf_token();
    if ($sent === '' || !hash_equals($own, $sent)) {
        send_json(array('error' => 'bad_token'), 403);
    }
}

/** The stored administrator session, or null when nobody is signed in. */
function admin_session()
{
    if (empty($_SESSION['adm']) || !is_array($_SESSION['adm'])) {
        return null;
    }
    return $_SESSION['adm'];
}

/** Client safe view of the session (tokens are never sent to the browser). */
function public_user($adm)
{
    return array(
        'id_user'   => isset($adm['id_user']) ? (int) $adm['id_user'] : 0,
        'user_name' => isset($adm['user_name']) ? (string) $adm['user_name'] : '',
        'first_name'=> isset($adm['first_name']) ? (string) $adm['first_name'] : '',
        'last_name' => isset($adm['last_name']) ? (string) $adm['last_name'] : '',
        'email'     => isset($adm['email']) ? (string) $adm['email'] : '',
        'act'       => isset($adm['act']) ? (int) $adm['act'] : 0,
    );
}

/**
 * Call the main application and decode its answer.
 *
 * @param string      $qjs   value of _qjs (ep, ual, uac, adm, ...)
 * @param array       $query extra query string parameters (p, q1, q2, sq, q)
 * @param string|null $body  JSON body, sent as POST when it is not empty
 * @return array
 */
function remote_json($qjs, array $query = array(), $body = null)
{
    $params = array_merge(array('_qjs' => $qjs), $query);
    $url    = HRH_API_URL . '?' . http_build_query($params);
    $isPost = ($body !== null && $body !== '');

    $headerLines = array(
        'Content-Type: application/json; charset=UTF-8',
        'Accept: application/json',
        'User-Agent: HamrahangCdnAdmin/1.0',
        // Lets ha.php tell admin-panel requests apart from the consumer app
        'X-Hamrahang-Client: cdn_admin',
    );

    // forward the administrator token so authenticated endpoints (uac, ...) work
    $adm = admin_session();
    if ($adm && !empty($adm['access_token'])) {
        $headerLines[] = 'Authorization: Bearer ' . $adm['access_token'];
    }

    $raw = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, HRH_TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw   = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($raw === false) {
            return array('error' => 'upstream_unreachable', 'detail' => $error);
        }
    } else {
        // fallback when the curl extension is not compiled in
        $context = stream_context_create(array(
            'http' => array(
                'method'        => $isPost ? 'POST' : 'GET',
                'header'        => implode("\r\n", $headerLines),
                'content'       => $isPost ? $body : '',
                'timeout'       => HRH_TIMEOUT,
                'ignore_errors' => true,
            ),
        ));
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            return array('error' => 'upstream_unreachable');
        }
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return array('error' => 'bad_upstream_response');
    }
    return $decoded;
}

/* ------------------------------- JSON modes ------------------------------ */

$mode = isset($_GET['mode']) ? (string) $_GET['mode'] : '';

/* ---- session state (read only, no token required) ---- */
if ($mode === 'state') {
    $adm = admin_session();
    send_json(array(
        'logged_in' => ($adm !== null),
        'admin'     => ($adm !== null && (int) $adm['act'] >= HRH_MIN_ACT),
        'user'      => ($adm !== null) ? public_user($adm) : null,
        'token'     => csrf_token(),
    ));
}

/* ---- server / API availability (public, used by the Home view) ---- */
if ($mode === 'ping') {
    send_json(remote_json('ep', array('p' => 'app', 'q1' => 'ping'), null));
}

/* ---- sign in : username + password -> JSON of the main app ---- */
if ($mode === 'login') {
    assert_csrf();

    $payload  = json_decode(raw_body(), true);
    $username = (is_array($payload) && isset($payload['username'])) ? trim((string) $payload['username']) : '';
    $password = (is_array($payload) && isset($payload['password'])) ? (string) $payload['password'] : '';

    if ($username === '' || $password === '') {
        send_json(array('error' => 'missing_credentials'), 400);
    }

    if (empty($_SESSION['did'])) {
        $_SESSION['did'] = bin2hex(random_bytes(16));
    }

    $answer = remote_json('ual', array(), json_encode(array(
        'a'           => 'login_password',
        'username'    => $username,
        'password'    => $password,
        'did'         => $_SESSION['did'],
        'device_type' => HRH_DEVICE,
    )));

    if (isset($answer['error'])) {
        send_json(array('error' => (string) $answer['error']), 401);
    }
    if (empty($answer['access_token'])) {
        send_json(array('error' => 'invalid_credentials'), 401);
    }

    $act = isset($answer['act']) ? (int) $answer['act'] : 0;
    if ($act < HRH_MIN_ACT) {
        // a normal account must never reach the editor
        send_json(array('error' => 'forbidden', 'detail' => 'administrator_role_required', 'act' => $act), 403);
    }

    $_SESSION['adm'] = array(
        'id_user'       => isset($answer['id_user']) ? (int) $answer['id_user'] : 0,
        'user_name'     => isset($answer['user_name']) ? (string) $answer['user_name'] : '',
        'first_name'    => isset($answer['first_name']) ? (string) $answer['first_name'] : '',
        'last_name'     => isset($answer['last_name']) ? (string) $answer['last_name'] : '',
        'email'         => isset($answer['email']) ? (string) $answer['email'] : '',
        'act'           => $act,
        'access_token'  => (string) $answer['access_token'],
        'refresh_token' => isset($answer['refresh_token']) ? (string) $answer['refresh_token'] : '',
        'login_at'      => time(),
    );

    session_regenerate_id(true); // session fixation protection

    send_json(array(
        'ok'    => true,
        'admin' => true,
        'user'  => public_user($_SESSION['adm']),
        'token' => csrf_token(),
    ));
}

/* ---- sign out : locally and on the main application ---- */
if ($mode === 'logout') {
    assert_csrf();
    $adm = admin_session();
    if ($adm !== null) {
        remote_json('ual', array(), json_encode(array(
            'a'            => 'logout',
            'did'          => isset($_SESSION['did']) ? $_SESSION['did'] : '',
            'refresh_token'=> isset($adm['refresh_token']) ? $adm['refresh_token'] : '',
        )));
    }
    $_SESSION = array();
    @session_destroy();
    send_json(array('ok' => true));
}

/* ---- submit of the item editor (no live sync while typing) ---- */
if ($mode === 'save') {
    if (admin_session() === null) {
        send_json(array('error' => 'unauthorized'), 401);
    }
    assert_csrf();

    $payload = json_decode(raw_body(), true);
    if (!is_array($payload)) {
        send_json(array('error' => 'bad_payload'), 400);
    }

    $action = isset($payload['a']) ? strtolower(trim((string) $payload['a'])) : 'update_item';

    /*
     * Admin read helpers (full profile) — must not require type/name.
     * Example: { a: 'get_user', id: 11210717 }
     */
    if ($action === 'get_user') {
        if (!isset($payload['id'])) {
            send_json(array('error' => 'bad_payload', 'detail' => 'id required'), 400);
        }
        $answer = remote_json('adm', array(), json_encode($payload));
        if (is_array($answer)) {
            send_json($answer);
        }
        send_json(array('error' => 'upstream_unreachable'));
    }

    // create / update catalog items require type + id + name (name optional only for user table)
    if (!isset($payload['type']) || !isset($payload['id'])) {
        send_json(array('error' => 'bad_payload', 'detail' => 'type and id required'), 400);
    }
    $table = isset($payload['table']) ? strtolower(trim((string) $payload['table'])) : strtolower((string) $payload['type']);
    if ($table !== 'user' && (!isset($payload['name']) || trim((string) $payload['name']) === '')) {
        send_json(array('error' => 'bad_payload', 'detail' => 'name required'), 400);
    }
    if ($table === 'user' && !isset($payload['name'])) {
        $payload['name'] = '';
    }

    if ($action !== 'create_item' && $action !== 'update_item') {
        $action = 'update_item';
    }
    $payload['a'] = $action;

    // POST body (not ?q=) so large lyrics/media payloads are not truncated by URL limits
    $answer = remote_json('adm', array(), json_encode($payload));

    if (is_array($answer) && (!empty($answer['updated']) || !empty($answer['created']))) {
        $outId = isset($answer['id']) ? (int) $answer['id'] : (int) $payload['id'];
        send_json(array(
            'ok'      => true,
            'saved'   => true,
            'created' => !empty($answer['created']),
            'type'    => isset($answer['type']) ? (string) $answer['type'] : (string) $payload['type'],
            'id'      => $outId,
            'real_id' => isset($answer['real_id']) ? (int) $answer['real_id'] : 0,
            'changes' => isset($answer['changes']) ? $answer['changes'] : array(),
            'user'    => isset($answer['user']) ? $answer['user'] : null,
        ));
    }
    if (is_array($answer) && !empty($answer['error'])) {
        send_json($answer);
    }

    // upstream answered without "updated"/"created" -> the block is not live yet
    send_json(array(
        'ok'     => true,
        'staged' => true,
        'type'   => (string) $payload['type'],
        'id'     => (int) $payload['id'],
    ));
}

/* ---- generic JSON gateway (administrator session required) ---- */
if ($mode === 'query') {
    if (admin_session() === null) {
        send_json(array('error' => 'unauthorized'), 401);
    }
    assert_csrf();

    $qjs = isset($_GET['qjs']) ? (string) $_GET['qjs'] : 'ep';
    if (!in_array($qjs, array('ep', 'ual', 'uac', 'adm', 'lan', 'gnr', 'own'), true)) {
        send_json(array('error' => 'bad_endpoint'), 400);
    }

    $params = array();
    foreach (array('p', 'q1', 'q2', 'sq', 'q', 'artist', 'ids', 'min_act') as $key) {
        if (isset($_GET[$key])) {
            $params[$key] = (string) $_GET[$key];
        }
    }
    if (isset($params['p']) && !in_array($params['p'], array('search', 'song', 'album', 'artist', 'video', 'playlist', 'app'), true)) {
        send_json(array('error' => 'bad_page'), 400);
    }

    $body = raw_body();
    send_json(remote_json($qjs, $params, $body !== '' ? $body : null));
}

if ($mode !== '') {
    send_json(array('error' => 'invalid_action'), 400);
}

/* ------------------------------- boot state ------------------------------ */

$adm = admin_session();

$boot = array(
    'logged_in'  => ($adm !== null),
    'admin'      => ($adm !== null && (int) $adm['act'] >= HRH_MIN_ACT),
    'user'       => ($adm !== null) ? public_user($adm) : null,
    'token'      => csrf_token(),
    'thumb_base' => HRH_THUMB_URL,
    'web_base'   => HRH_WEB_URL,
    'id_offset'  => HRH_IDHM,
);
$bootJson = json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

?><!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0c0d12">
<title>Hamrahang Admin &mdash; Content Console</title>
<style>
/* ==========================================================================
   Design tokens
   ========================================================================== */
:root{
  --bg:#0c0d12;
  --surface:#15161d;
  --surface-2:#1c1e27;
  --surface-3:#232633;
  --line:#ffffff14;
  --line-strong:#ffffff2b;
  --text:#f2f4f8;
  --muted:#9699ad;
  --accent:#1db954;
  --accent-2:#8b5cf6;
  --danger:#ff5c7a;
  --warn:#f5a524;
  --radius:16px;
  --radius-sm:11px;
  --shadow:0 18px 44px #00000066;
  --ease:cubic-bezier(.4,0,.2,1);
  --sidebar-w:252px;
}
*,*::before,*::after{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  background:
    radial-gradient(1100px 520px at 88% -12%, #1b1230 0%, transparent 60%),
    radial-gradient(900px 500px at -10% 110%, #07251a 0%, transparent 60%),
    var(--bg);
  color:var(--text);
  font:15px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
  -webkit-font-smoothing:antialiased;
}
[hidden]{display:none !important}
button,input,select,textarea{font:inherit;color:inherit}
a{color:inherit}
:focus-visible{outline:2px solid var(--accent);outline-offset:2px;border-radius:6px}
::-webkit-scrollbar{width:10px;height:10px}
::-webkit-scrollbar-thumb{background:#ffffff1f;border-radius:99px;border:3px solid transparent;background-clip:content-box}
::-webkit-scrollbar-thumb:hover{background:#ffffff33;background-clip:content-box}
::selection{background:#1db95455}

/* ==========================================================================
   Shell : left sidebar (PC) + stage
   ========================================================================== */
.app{
  display:grid;
  grid-template-columns:var(--sidebar-w) minmax(0,1fr);
  min-height:100vh;
  min-height:100dvh;
}
.sidebar{
  position:sticky;top:0;align-self:start;
  height:100vh;height:100dvh;
  display:flex;flex-direction:column;gap:6px;
  padding:22px 16px;
  background:#101119cc;
  backdrop-filter:blur(14px);
  border-right:1px solid var(--line);
}
.brand{
  display:flex;align-items:center;gap:10px;
  text-decoration:none;padding:6px 8px 20px;
}
.brand-mark{
  width:34px;height:34px;flex:none;border-radius:10px;
  display:grid;place-items:center;font-size:17px;font-weight:800;color:#04140b;
  background:linear-gradient(135deg,var(--accent),#3ddc84);
  box-shadow:0 8px 20px #1db95440;
}
.brand-text{font-weight:800;letter-spacing:.2px;font-size:16px}
.brand-text em{font-style:normal;color:var(--muted);font-weight:600;font-size:11px;
  display:block;letter-spacing:.14em;text-transform:uppercase}

.nav{display:flex;flex-direction:column;gap:4px;margin-top:6px}
.nav-label{font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:#6d7085;padding:14px 10px 6px}
.nav-item{
  all:unset;cursor:pointer;
  display:flex;align-items:center;gap:11px;
  padding:11px 12px;border-radius:12px;
  color:var(--muted);font-size:14.5px;font-weight:600;
  transition:color .18s var(--ease),background .18s var(--ease);
  text-decoration:none;box-sizing:border-box;
}
a.nav-item{color:var(--muted)}
.nav-item:hover{color:var(--text);background:#ffffff0d}
.nav-item.is-active{
  color:#fff;
  background:linear-gradient(90deg,#1db95430,#8b5cf626);
  box-shadow:inset 0 0 0 1px #1db95455;
}
.nav-item svg{width:18px;height:18px;flex:none;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.sidebar-foot{margin-top:auto;padding-top:14px;border-top:1px solid var(--line);display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.who{display:flex;align-items:center;gap:8px;min-width:0;flex:1}
.who-dot{width:8px;height:8px;border-radius:50%;background:#5b5f73;flex:none}
body.is-authed .who-dot{background:var(--accent);box-shadow:0 0 0 4px #1db95426}
.who-name{font-size:13px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* ---- stage / main ---- */
.stage{display:flex;flex-direction:column;min-width:0}
main{flex:1;padding:26px 32px 48px;min-width:0}
.topbar{display:none}

.view{display:none}
.view.is-active{display:block;animation:fadeUp .34s var(--ease) both}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}

h1{font-size:clamp(21px,2.4vw,28px);line-height:1.25;margin:0 0 6px;letter-spacing:-.01em}
h2{font-size:16px;margin:0 0 10px}
.sub{color:var(--muted);font-size:14px;margin:0 0 22px;max-width:70ch}
.section{margin-bottom:30px}

/* ==========================================================================
   Primitives
   ========================================================================== */
.panel{
  background:var(--surface);
  border:1px solid var(--line);
  border-radius:var(--radius);
  padding:20px;
  max-width:100%;
  min-width:0;
  box-sizing:border-box;
}
.btn{
  all:unset;box-sizing:border-box;cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:11px 18px;border-radius:12px;font-size:14px;font-weight:700;
  transition:.18s var(--ease);white-space:nowrap;
}
.btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.btn-primary{background:linear-gradient(135deg,var(--accent),#17a94d);color:#04140b;box-shadow:0 10px 24px #1db95433}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 14px 30px #1db9544d}
.btn-accent{background:linear-gradient(135deg,var(--accent-2),#6d3ff0);color:#fff;box-shadow:0 10px 24px #8b5cf633}
.btn-accent:hover{transform:translateY(-1px)}
.btn-ghost{background:#ffffff0f;color:var(--text);box-shadow:inset 0 0 0 1px var(--line-strong)}
.btn-ghost:hover{background:#ffffff1a}
.btn-sm{padding:8px 13px;font-size:13px;border-radius:10px}
.btn[disabled],.btn[aria-busy="true"]{opacity:.6;pointer-events:none}
.btn-block{display:flex;width:100%;max-width:100%;box-sizing:border-box}

.field{display:flex;flex-direction:column;gap:6px;min-width:0}
.field > label{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)}
.input,.select,.textarea{
  width:100%;padding:12px 14px;border-radius:12px;
  background:var(--surface-2);border:1px solid var(--line-strong);
  color:var(--text);font-size:14.5px;transition:.18s var(--ease);
}
.select{
  appearance:none;padding-right:34px;cursor:pointer;
  background-image:url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239699ad' stroke-width='2' stroke-linecap='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 11px center;background-size:16px;
}
.input::placeholder{color:#6d7085}
.input:focus,.select:focus,.textarea:focus{
  outline:none;border-color:var(--accent);
  box-shadow:0 0 0 4px #1db95426;background:var(--surface-3);
}
.hint{font-size:12.5px;color:var(--muted)}
.form-alert{
  margin:0;padding:11px 13px;border-radius:11px;font-size:13.5px;font-weight:600;
  background:#ff5c7a1f;color:#ff9db1;border:1px solid #ff5c7a4d;
}
.form-alert.is-info{background:#3b82f61f;color:#a8c7ff;border-color:#3b82f64d}

.status{
  margin:14px 0 0;font-size:13.5px;font-weight:600;color:var(--muted);
  display:flex;align-items:center;gap:9px;min-height:20px;
}
.status.is-error{color:#ff9db1}
.status.is-info{color:#8fbcfd}
.status.is-warn{color:#ffc86b}
.status.is-ok{color:#5ee08f}
.status::before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor;flex:none;opacity:.85}

/* ==========================================================================
   Home view
   ========================================================================== */
.hero{
  position:relative;overflow:hidden;
  border:1px solid var(--line);border-radius:22px;
  padding:30px 30px 28px;
  background:linear-gradient(135deg,#1b1330 0%,#11131b 55%,#0e1a14 100%);
  box-shadow:var(--shadow);
}
.hero::after{
  content:"";position:absolute;right:-70px;top:-90px;width:280px;height:280px;border-radius:50%;
  background:conic-gradient(from 210deg,var(--accent),var(--accent-2),var(--accent));
  filter:blur(70px);opacity:.32;
}
.hero h1{font-size:clamp(24px,3vw,34px)}
.hero p{color:#c6c9d8;max-width:62ch;margin:0 0 20px;position:relative}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap;position:relative}
.cards{display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));margin-top:22px}
.card{
  background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);
  padding:18px;cursor:pointer;transition:.2s var(--ease);text-align:left;
}
.card:hover{transform:translateY(-4px);border-color:var(--line-strong);background:var(--surface-2);box-shadow:var(--shadow)}
.card .card-ico{
  width:38px;height:38px;border-radius:11px;display:grid;place-items:center;margin-bottom:12px;
  background:#1db95426;color:#4ade80;font-size:18px;
}
.card b{display:block;font-size:15px;margin-bottom:4px}
.card span{color:var(--muted);font-size:13px;line-height:1.5;display:block}
.pill{
  display:inline-flex;align-items:center;gap:8px;padding:7px 13px;border-radius:999px;
  background:#ffffff0f;border:1px solid var(--line);font-size:12.5px;font-weight:700;color:var(--muted);
}
.pill i{width:8px;height:8px;border-radius:50%;background:var(--warn);font-style:normal}
.pill.is-ok i{background:var(--accent);box-shadow:0 0 0 4px #1db95426}
.pill.is-bad i{background:var(--danger)}

/* ==========================================================================
   FAQ view
   ========================================================================== */
.faq{display:flex;flex-direction:column;gap:12px;max-width:840px}
.faq details{
  background:var(--surface);border:1px solid var(--line);border-radius:14px;
  padding:0 18px;transition:.2s var(--ease);
}
.faq details[open]{border-color:#1db95440;background:var(--surface-2)}
.faq summary{
  cursor:pointer;list-style:none;padding:16px 0;font-weight:700;font-size:15px;
  display:flex;align-items:center;justify-content:space-between;gap:14px;
}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";font-size:20px;color:var(--muted);transition:.2s var(--ease)}
.faq details[open] summary::after{transform:rotate(45deg);color:var(--accent)}
.faq p{margin:0 0 16px;color:var(--muted);font-size:14px}
.faq code{background:#0c0d12;padding:2px 6px;border-radius:6px;color:#8ee6ac;font-size:13px}

/* ==========================================================================
   Edit view : login gate
   ========================================================================== */
.login-wrap{max-width:430px;width:100%;margin:6vh auto 0;min-width:0;box-sizing:border-box}
.login-head{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.login-head .brand-mark{width:42px;height:42px;font-size:20px}
.login-head b{display:block;font-size:18px}
.login-head span{color:var(--muted);font-size:13px}
.login-form{display:flex;flex-direction:column;gap:15px;min-width:0;max-width:100%;box-sizing:border-box}
.login-form .btn-block{align-self:stretch;width:100%;max-width:100%;box-sizing:border-box}
.login-meta{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:4px;flex-wrap:wrap}

/* ==========================================================================
   Edit view : workspace
   ========================================================================== */
.ws-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:20px}
.ws-user{display:flex;align-items:center;gap:10px;padding:8px 8px 8px 14px;border-radius:999px;background:#ffffff0d;border:1px solid var(--line)}
.ws-user b{font-size:13.5px}
.ws-user em{font-style:normal;font-size:12px;color:var(--muted)}

/* search box: input + button on the first row, the type select right under it */
.searchbar{
  display:grid;gap:14px 12px;
  grid-template-columns:minmax(0,1fr) auto;
  grid-template-areas:"q btn" "type type";
  align-items:end;
}
.searchbar .field > label{margin-bottom:2px}
.field-q{grid-area:q}
.field-type{grid-area:type;max-width:340px}
#searchBtn{grid-area:btn;min-width:0;white-space:nowrap}
@media (max-width:760px){
  .searchbar{grid-template-columns:minmax(0,1fr);grid-template-areas:"q" "type" "btn"}
  .field-type{max-width:none}
  /* keep button inside the panel — no forced 100% width that overflows padding */
  #searchBtn{width:auto;max-width:100%;justify-self:stretch}
  .login-wrap{max-width:100%;margin-left:0;margin-right:0;padding:0 2px}
  .login-form .btn-block{width:100%;max-width:100%;box-sizing:border-box}
  .panel{max-width:100%;box-sizing:border-box}
}

/* ---- results ---- */
.results-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:24px 0 12px;flex-wrap:wrap}
.results-head h2{margin:0}
.results{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}
.result{
  display:grid;grid-template-columns:52px minmax(0,1fr) auto auto;
  gap:14px;align-items:center;
  padding:10px 12px;border-radius:14px;
  background:var(--surface);border:1px solid var(--line);
  transition:.18s var(--ease);
}
.result:hover{background:var(--surface-2);border-color:var(--line-strong);transform:translateX(3px)}
.result.is-selected{border-color:var(--accent);box-shadow:0 0 0 3px #1db95426}
.thumb{
  position:relative;width:52px;height:52px;border-radius:11px;overflow:hidden;
  background:linear-gradient(135deg,#252838,#1a1c26);
  display:grid;place-items:center;flex:none;
}
.thumb i{font-style:normal;font-weight:800;color:#ffffff73;font-size:18px}
.thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.result-main{min-width:0}
.result-title{font-weight:700;font-size:14.5px;display:flex;align-items:baseline;gap:9px;flex-wrap:wrap}
.result-title .fa{color:var(--muted);font-weight:500;font-size:13.5px}
.result-sub{color:var(--muted);font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:3px}
.result-meta{display:flex;flex-direction:column;align-items:flex-end;gap:5px}
.rid{font-size:11.5px;color:#6d7085;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
.badge{
  display:inline-block;padding:4px 9px;border-radius:999px;
  font-size:10.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;
}
.badge-song{background:#1db95426;color:#54e08b}
.badge-podcast{background:#8b5cf62b;color:#c3b0fd}
.badge-album{background:#3b82f626;color:#8fbcfd}
.badge-artist{background:#f59e0b29;color:#f8cd72}
.badge-musicvideo{background:#f43f5e2b;color:#fd93a7}
.badge-video{background:#f43f5e2b;color:#fd93a7}
.badge-playlist{background:#06b6d429;color:#77dff1}
.badge-user{background:#64748b33;color:#cbd5e1}
.badge-owner{background:#a78bfa33;color:#c4b5fd}
@media (max-width:640px){
  .result{grid-template-columns:44px minmax(0,1fr);grid-auto-rows:auto}
  .thumb{width:44px;height:44px}
  .result-meta{grid-column:2;flex-direction:row;align-items:center;gap:10px}
  .result .btn{grid-column:2;justify-self:start}
}

.empty{
  border:1px dashed var(--line-strong);border-radius:14px;padding:26px 20px;text-align:center;
  color:var(--muted);font-size:14px;background:#ffffff06;
}
.empty b{display:block;color:var(--text);margin-bottom:5px;font-size:15px}

.skeleton{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}
.skeleton li{
  height:72px;border-radius:14px;background:var(--surface);border:1px solid var(--line);
  background-image:linear-gradient(90deg,transparent 20%,#ffffff0f 40%,transparent 60%);
  background-size:280% 100%;animation:shimmer 1.3s linear infinite;
}
@keyframes shimmer{from{background-position:140% 0}to{background-position:-60% 0}}

/* ---- add new ---- */
.add-block{margin-top:26px;padding-top:22px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:16px;align-items:flex-start}
.add-panel{width:100%}
.add-placeholder{display:flex;flex-direction:column;gap:8px}
.add-placeholder .steps{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.add-placeholder .step{
  font-size:12px;font-weight:700;padding:6px 11px;border-radius:999px;
  background:#ffffff0f;border:1px solid var(--line);color:var(--muted);
}
.add-placeholder .step b{color:var(--accent-2)}

/* ==========================================================================
   Edit view : item editor (form for one search result)
   ========================================================================== */
.zone-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.zone-bar .zone-title{display:flex;align-items:center;gap:11px;min-width:0;flex-wrap:wrap}
.zone-bar .zone-title b{font-size:15.5px}
.zone-bar .zone-title .fa{color:var(--muted);font-weight:500;font-size:14px}
.type-switch{display:flex;gap:8px;flex-wrap:wrap}
.type-switch .badge{
  cursor:pointer;border:0;padding:6px 13px;font-family:inherit;
  opacity:.42;filter:saturate(.55);transition:.18s var(--ease);
  box-shadow:inset 0 0 0 1px transparent;
}
.type-switch .badge:hover{opacity:.75}
.type-switch .badge.is-on{
  opacity:1;filter:none;transform:translateY(-1px);
  box-shadow:0 8px 18px #00000055, inset 0 0 0 1px #ffffff2e;
}
.rid-lg{font-size:13px;color:#6d7085;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}

.editor{display:flex;flex-direction:column;gap:0}
.estep{
  padding:20px 0;border-top:1px solid var(--line);
  display:flex;flex-direction:column;gap:13px;
}
.estep:first-child{padding-top:0;border-top:0}
.estep > h2{
  margin:0;font-size:12.5px;font-weight:800;letter-spacing:.13em;
  text-transform:uppercase;color:var(--muted);
  display:flex;align-items:center;gap:9px;
}
.estep > h2 .num{
  width:21px;height:21px;border-radius:7px;flex:none;display:grid;place-items:center;
  background:#1db95426;color:#54e08b;font-size:11.5px;
}
.estep-row{display:grid;grid-template-columns:190px minmax(0,1fr);gap:13px;align-items:center}
.estep-row > label{font-size:12.5px;font-weight:700;color:var(--muted)}
.estep-row .hint{grid-column:2}
.lan-dur-row{display:grid;grid-template-columns:minmax(0,1fr) 137px;gap:10px;align-items:center}
.input.is-invalid{border-color:var(--danger) !important;box-shadow:0 0 0 1px color-mix(in srgb, var(--danger) 45%, transparent)}
.btn.is-disabled,.btn:disabled{opacity:.45;cursor:not-allowed;pointer-events:none}
.ro-field{
  padding:8px 12px;border-radius:10px;background:#0f1017;border:1px solid var(--line);
  font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;color:#8ee6ac;
  display:inline-flex;align-items:center;gap:8px;white-space:nowrap;
}
.ro-field .ro-meta{color:#6d7085;font-size:11.5px;font-weight:500}
/* Item id row: compact id chip + type switch (Song/Podcast or Music video/Video) on one line */
.id-line{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.id-line .type-switch{margin:0}
@media (max-width:760px){
  .estep-row{grid-template-columns:1fr;gap:6px;align-items:stretch}
  .estep-row .hint{grid-column:1}
}

/* name field : globe icon + input + plus, locale rows below */
.name-stack{display:flex;flex-direction:column;gap:9px}
.name-line{display:grid;grid-template-columns:34px minmax(0,1fr) auto;gap:9px;align-items:center}
.name-line .globe{
  width:34px;height:34px;border-radius:10px;display:grid;place-items:center;
  background:#1db9541f;color:#54e08b;border:1px solid #1db95440;
}
.name-line .globe svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.name-line .lang-sel{width:74px}
.name-line .icon-btn{
  width:34px;height:34px;padding:0;border-radius:10px;display:grid;place-items:center;
  font-size:17px;line-height:1;
}
.name-line .icon-btn.is-del{color:#ff9db1}
.name-line .icon-btn.is-add{color:#54e08b}
@media (max-width:520px){
  .name-line{grid-template-columns:34px minmax(0,1fr) auto}
  .name-line .lang-sel{width:64px;padding-left:8px;padding-right:24px;font-size:13px}
}

.triple{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;max-width:430px}
@media (max-width:520px){.triple{max-width:none}}

.thumb-edit{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.thumb-edit .thumb{width:74px;height:74px;border-radius:14px;font-size:26px}
.thumb-edit .field{flex:1;min-width:220px}

/* Media rows (thumb / cover / art / gallery) */
.media-list{display:flex;flex-direction:column;gap:12px}
.media-row{
  display:grid;grid-template-columns:74px minmax(0,1fr) auto auto;
  gap:12px;align-items:center;
  padding:12px;border-radius:14px;background:#0f1017;border:1px solid var(--line);
}
.media-row .thumb{width:74px;height:74px;border-radius:12px;font-size:22px;position:relative;overflow:hidden;
  background:#1a1b24;display:grid;place-items:center;flex:none}
.media-row .media-src{display:flex;gap:8px;align-items:center;min-width:0}
.media-row .media-src .input{flex:1;min-width:0}
.media-row .media-role{width:128px}
.media-row .media-file{display:none}
.media-row.is-dup .media-role{
  border-color:var(--danger)!important;
  background:color-mix(in srgb, var(--danger) 18%, #0f1017);
  box-shadow:0 0 0 1px color-mix(in srgb, var(--danger) 35%, transparent);
}
.media-row.is-dup{
  border-color:color-mix(in srgb, var(--danger) 45%, var(--line));
}
.media-dup-hint{
  margin-top:8px;padding:8px 11px;border-radius:10px;
  background:color-mix(in srgb, var(--danger) 12%, transparent);
  border:1px solid color-mix(in srgb, var(--danger) 40%, transparent);
  color:#f0a8a8;font-size:12.5px;line-height:1.45;
}
@media (max-width:760px){
  .media-row{grid-template-columns:64px minmax(0,1fr);gap:10px}
  .media-row .media-role{width:100%;grid-column:1 / -1}
  .media-row .thumb{width:64px;height:64px}
}
.media-add-wrap{margin-top:4px}

/* Sources (stream links) */
.src-list{display:flex;flex-direction:column;gap:12px}
.src-row{
  padding:12px;border-radius:14px;background:#0f1017;border:1px solid var(--line);
  display:flex;flex-direction:column;gap:8px;
}
.src-row.is-off{border-color:color-mix(in srgb, var(--danger) 50%, var(--line));background:color-mix(in srgb, var(--danger) 8%, #0f1017)}
.src-row.is-locked{border-color:color-mix(in srgb, var(--warn) 45%, var(--line));background:color-mix(in srgb, var(--warn) 8%, #0f1017)}
.src-row.is-demo{border-color:color-mix(in srgb, var(--accent) 35%, var(--line));}
.src-kind.is-demo{color:var(--accent);border-color:color-mix(in srgb, var(--accent) 50%, var(--line));}
.src-main{display:grid;grid-template-columns:40px minmax(0,1fr) 72px minmax(120px,160px) auto 36px;gap:8px;align-items:center}
.src-owner{position:relative;min-width:0}
.src-owner .src-user{width:100%}
.src-owner-label{
  display:block;padding:8px 10px;border-radius:10px;background:var(--surface-2);
  border:1px solid var(--line);font-size:12.5px;color:var(--muted);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.src-owner .pick-list{position:absolute;z-index:5;left:0;right:0;top:100%;margin-top:4px;max-height:180px;overflow:auto}
@media (max-width:760px){
  .src-main{grid-template-columns:40px minmax(0,1fr);gap:8px}
  .src-main .src-bit,.src-main .src-owner,.src-main .src-kind,.src-main .src-del{grid-column:2}
}
.src-play{
  width:40px;height:40px;border-radius:12px;border:1px solid var(--line);
  background:var(--surface-2);color:#fff;cursor:pointer;display:grid;place-items:center;flex:none;
}
.src-play:hover{border-color:var(--accent);color:var(--accent)}
.src-play.is-on{background:color-mix(in srgb, var(--accent) 25%, #0f1017);border-color:var(--accent);color:var(--accent)}
.src-play:disabled{opacity:.45;cursor:not-allowed}
.src-prog{display:none;align-items:center;gap:10px;padding:0 2px 0 48px}
.src-prog.is-on{display:flex}
.src-bar{
  flex:1;height:12px;border-radius:99px;background:#ffffff18;overflow:hidden;cursor:pointer;
  touch-action:none;position:relative;
}
.src-bar > i{display:block;height:100%;width:0;background:var(--accent);border-radius:99px;pointer-events:none}
.src-time{font-size:11.5px;color:var(--muted);font-family:ui-monospace,Menlo,monospace;white-space:nowrap}
.src-lock-note{font-size:12px;color:var(--warn);padding-left:48px}
.src-act-btns{display:flex;gap:6px;flex-wrap:wrap;align-items:center;padding-left:48px;min-height:28px}
.src-err{font-size:12px;color:var(--danger);margin-left:4px}

/* Album tracks list */
.trk-list{display:flex;flex-direction:column;gap:8px}
.trk-row{
  display:grid;grid-template-columns:36px 48px minmax(0,1fr) auto;
  gap:10px;align-items:center;padding:8px 10px;border-radius:14px;
  background:#0f1017;border:1px solid var(--line);
}
.trk-row .trk-num{
  font-family:ui-monospace,Menlo,monospace;font-size:13px;font-weight:700;
  color:var(--muted);text-align:center;
}
.trk-row .thumb{width:48px;height:48px;border-radius:12px;font-size:16px}
.trk-row .trk-main{min-width:0}
.trk-row .trk-title{font-weight:700;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.trk-row .trk-title .name-alt{font-weight:500;color:var(--muted);margin-inline-start:6px}
.trk-row .trk-sub{font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.trk-actions{display:flex;align-items:center;gap:4px;flex:none}
.rel-attach-row{display:flex;gap:8px;align-items:center}
.rel-attach-row .input{flex:1;min-width:0}
.rel-attach-row .btn{flex:none;white-space:nowrap}
.trk-move{display:flex;flex-direction:column;gap:2px}
.trk-move .btn{width:28px;height:22px;padding:0;border-radius:8px;display:grid;place-items:center}
.trk-move .btn svg{width:14px;height:14px}
@media (max-width:760px){
  .trk-row{grid-template-columns:28px 40px minmax(0,1fr);gap:8px}
  .trk-actions{grid-column:1 / -1;justify-content:flex-end}
}

.artist-chips{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.chip{
  display:inline-flex;align-items:center;gap:8px;padding:7px 8px 7px 13px;
  border-radius:999px;background:#f59e0b1f;border:1px solid #f59e0b4d;
  font-size:13px;font-weight:700;color:#f8cd72;
}
.chip .x{
  all:unset;cursor:pointer;width:19px;height:19px;border-radius:50%;display:grid;place-items:center;
  background:#ffffff1f;color:#ffe1ad;font-size:12px;line-height:1;
}
.chip .x:hover{background:var(--danger);color:#fff}
.chip.is-empty{background:#ffffff0d;border-color:var(--line);color:var(--muted);font-weight:600}
/* chip with an artist thumbnail */
.chip.has-thumb{padding:4px 8px 4px 4px}
.chip .thumb{width:23px;height:23px;border-radius:7px;flex:none}
.chip .thumb i{font-size:11px}
/* Album chip with track number */
.album-chip{gap:8px;padding:5px 8px 5px 5px;align-items:center}
.album-chip-name{max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.trk-wrap{display:inline-flex;align-items:center;gap:2px;margin-left:2px}
.trk-hash{font-size:11px;color:var(--muted);font-weight:700}
.trk-input{
  width:48px !important;padding:4px 6px !important;font-size:12.5px !important;
  text-align:center;border-radius:8px !important;height:28px;
}
.trk-ro{font-size:12px;color:var(--muted);font-family:ui-monospace,Menlo,monospace}
.saved-media-list{display:flex;flex-direction:column;gap:8px}
.saved-media-row{
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
  padding:8px 10px;border-radius:12px;background:#0f1017;border:1px solid var(--line);
}
.saved-media-row .thumb{width:48px;height:48px;border-radius:10px;font-size:16px;flex:none}
.saved-media-url{
  font-size:12.5px;color:var(--muted);word-break:break-all;
  font-family:ui-monospace,SFMono-Regular,Menlo,monospace;min-width:0;flex:1;
}
/* genre row : input + add button, suggestions below */
.genre-line{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:9px;align-items:center}
.genre-line .icon-btn{
  width:34px;height:34px;padding:0;border-radius:10px;display:grid;place-items:center;
  font-size:17px;line-height:1;color:#54e08b;
}
.pick-list{display:flex;flex-direction:column;gap:7px;margin-top:4px}
.pick.is-on{
  border-color:#1db954;background:#1db9541f;color:#8ee6ac;
  box-shadow:0 8px 18px #00000055, inset 0 0 0 1px #1db95459;
}
.pick .pin-flag{margin-left:8px;color:#54e08b;font-weight:800}
.pick{
  all:unset;cursor:pointer;display:flex;align-items:center;gap:11px;
  padding:9px 12px;border-radius:12px;background:var(--surface-2);
  border:1px solid var(--line);transition:.16s var(--ease);
}
.pick:hover{border-color:var(--accent);background:var(--surface-3);transform:translateX(3px)}
.pick .rid{margin-left:auto}

.lyric-box{display:flex;flex-direction:column;gap:10px}
.lyric-box .textarea{min-height:170px;resize:vertical;line-height:1.75;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13.5px;white-space:pre-wrap}
.lyric-empty{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.lyric-list{display:flex;flex-direction:column;gap:8px}
.lyric-row{
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
  padding:10px 12px;border-radius:12px;background:#0f1017;border:1px solid var(--line);
}
.lyric-row .lyric-meta{display:flex;gap:6px;align-items:center;flex:none}
.lyric-row .lyric-preview{
  flex:1;min-width:120px;font-size:12.5px;color:var(--muted);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  font-family:ui-monospace,Menlo,monospace;
}
.lyric-edit-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.lyric-edit-bar .select{width:auto;min-width:90px}
.saved-lyric-list{display:flex;flex-direction:column;gap:10px}
.saved-lyric-block{
  border:1px solid var(--line);border-radius:12px;background:#0f1017;overflow:hidden;
}
.saved-lyric-head{
  display:flex;gap:8px;align-items:center;padding:8px 12px;border-bottom:1px solid var(--line);
}
.saved-lyric-body{
  max-height:180px;overflow:auto;padding:10px 12px;
  font-size:13px;line-height:1.7;white-space:pre-wrap;
  font-family:ui-monospace,SFMono-Regular,Menlo,monospace;color:#c8cad4;
}

.editor-foot{
  display:flex;gap:11px;justify-content:flex-end;flex-wrap:wrap;
  padding-top:18px;margin-top:4px;border-top:1px solid var(--line);
}
@media (max-width:520px){
  .editor-foot .btn{flex:1}
}

/* saved card view after submit */
.saved-card{display:flex;flex-direction:column;gap:16px}
.saved-head{display:flex;gap:15px;align-items:center;flex-wrap:wrap}
.saved-head .thumb{width:84px;height:84px;border-radius:16px;font-size:30px}
.saved-head .saved-titles{min-width:0}
.saved-head h2{margin:0 0 5px;font-size:19px}
.saved-head .fa{color:var(--muted);font-weight:500}
.saved-meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px}
.saved-cell{
  background:#0f1017;border:1px solid var(--line);border-radius:12px;padding:11px 13px;
}
.saved-cell b{display:block;font-size:10.5px;letter-spacing:.1em;text-transform:uppercase;color:#6d7085;margin-bottom:5px}
.saved-cell span{font-size:13.5px;word-break:break-word;white-space:pre-wrap}
.saved-names{display:flex;flex-direction:column;gap:4px}
.saved-names .row{display:flex;gap:9px;align-items:baseline;font-size:13.5px}
.saved-names .code{
  font-size:10.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
  background:#8b5cf62b;color:#c3b0fd;border-radius:6px;padding:2px 7px;flex:none;
}

/* ==========================================================================
   Mobile navigation (bottom tab bar + top bar)
   ========================================================================== */
.tabbar{
  display:none;position:sticky;bottom:0;z-index:40;
  background:#101119f2;backdrop-filter:blur(14px);
  border-top:1px solid var(--line);
  padding:8px 6px calc(8px + env(safe-area-inset-bottom));
}
.tab{
  all:unset;cursor:pointer;flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;
  padding:7px 4px;border-radius:12px;color:var(--muted);font-size:11px;font-weight:700;
  transition:.18s var(--ease);
}
.tab svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.tab.is-active{color:#fff;background:linear-gradient(180deg,#1db95433,#8b5cf622);box-shadow:inset 0 0 0 1px #1db95455}

@media (max-width:900px){
  .app{grid-template-columns:1fr}
  .sidebar{display:none}
  .topbar{
    display:flex;align-items:center;gap:10px;position:sticky;top:0;z-index:40;
    padding:12px 16px;background:#101119f2;backdrop-filter:blur(14px);
    border-bottom:1px solid var(--line);
  }
  .topbar .brand-mark{width:30px;height:30px;font-size:15px}
  .topbar b{font-size:15px}
  .topbar .topbar-who{margin-left:auto;font-size:12.5px;color:var(--muted);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:45%}
  .tabbar{display:flex;gap:4px}
  main{padding:20px 16px 34px}
  .hero{padding:24px 20px}
}
@media (min-width:901px){.topbar{display:none}}

@media (prefers-reduced-motion:reduce){
  *{animation-duration:.01ms !important;transition-duration:.01ms !important}
}
</style>
</head>
<body class="<?= !empty($boot['logged_in']) ? 'is-authed' : '' ?>">

<div class="app">

  <!-- ============================ PC menu (left) ======================== -->
  <aside class="sidebar">
    <a class="brand" href="#/home">
      <span class="brand-mark">&#9834;</span>
      <span class="brand-text">Hamrahang<em>Admin console</em></span>
    </a>

    <nav class="nav" aria-label="Primary">
      <span class="nav-label">Menu</span>
      <button class="nav-item is-active" type="button" data-view-btn="home">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5.5 9.5V20h13V9.5"/><path d="M10 20v-5h4v5"/></svg>
        <span>Home</span>
      </button>
      <button class="nav-item" type="button" data-view-btn="faq">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.2a2.5 2.5 0 1 1 3.7 2.2c-.8.5-1.3 1-1.3 1.9v.4"/><path d="M12 17.2h.01"/></svg>
        <span>FAQ</span>
      </button>
      <button class="nav-item" type="button" data-view-btn="edit">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M14.5 5.5l4 4"/></svg>
        <span>Edit</span>
      </button>
      <!-- Opens the standalone browser / storage diagnostic page (ckbr.html) -->
      <a class="nav-item" href="ckbr.html" target="_blank" rel="noopener noreferrer" title="Browser and storage diagnostic">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/><path d="M12 18v2"/><path d="M9 10l2 2 4-4"/></svg>
        <span>Browser check</span>
      </a>
    </nav>

    <div class="sidebar-foot">
      <span class="who"><span class="who-dot"></span><span class="who-name js-user-name">guest</span></span>
      <button class="btn btn-ghost btn-sm js-logout" type="button" hidden>Sign out</button>
    </div>
  </aside>

  <!-- ================================ stage ============================= -->
  <div class="stage">

    <!-- ======================== Mobile menu (top) ====================== -->
    <header class="topbar">
      <span class="brand-mark">&#9834;</span>
      <b>Hamrahang Admin</b>
      <span class="topbar-who js-user-name">guest</span>
    </header>

    <main id="main">

      <!-- ============================== HOME =========================== -->
      <section class="view is-active" id="view-home" data-view="home">
        <div class="hero">
          <span class="pill" id="serverPill"><i></i><span id="serverStatus">Checking API...</span></span>
          <h1 style="margin-top:16px">Content console</h1>
          <p>Search, create and maintain every song, album, artist, video and playlist of the
             Hamrahang catalogue from one single page. All data is exchanged as JSON with the
             main application.</p>
          <div class="hero-actions">
            <button class="btn btn-primary" type="button" data-view-btn="edit">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M14.5 5.5l4 4"/></svg>
              Open the editor
            </button>
            <button class="btn btn-ghost" type="button" data-view-btn="faq">Read the FAQ</button>
          </div>
        </div>

        <div class="cards">
          <button class="card" type="button" data-view-btn="edit">
            <span class="card-ico">&#9835;</span>
            <b>Edit catalogue</b>
            <span>Sign in, look an item up by id or keyword and change it.</span>
          </button>
          <button class="card" type="button" data-view-btn="edit">
            <span class="card-ico" style="background:#8b5cf626;color:#c3b0fd">+</span>
            <b>Add new content</b>
            <span>Artist &rarr; album or single &rarr; song, in one guided flow.</span>
          </button>
          <button class="card" type="button" data-view-btn="faq">
            <span class="card-ico" style="background:#3b82f626;color:#8fbcfd">?</span>
            <b>How it works</b>
            <span>Sessions, ids, endpoints and the JSON gateway explained.</span>
          </button>
        </div>
      </section>

      <!-- ============================== FAQ ============================ -->
      <section class="view" id="view-faq" data-view="faq">
        <h1>FAQ</h1>
        <p class="sub">Everything worth knowing about this console.</p>

        <div class="faq">
          <details open>
            <summary>How do I sign in?</summary>
            <p>Open the <b>Edit</b> page: if you are not signed in the login form appears first.
               Use your Hamrahang username (or e-mail) and password. The credentials are posted as
               JSON to <code>?_qjs=wal</code> through the PHP gateway, the answer is stored in a
               server side session and the browser only receives a CSRF token.</p>
          </details>
          <details>
            <summary>Who is allowed to use the editor?</summary>
            <p>Accounts whose <code>act</code> value is greater than or equal to
               <code>7</code> (the administrator flag used by <code>ha.php</code>). Any other
               account receives <code>403 forbidden</code> and never sees the workspace.</p>
          </details>
          <details>
            <summary>Which ids can I type in the search box?</summary>
            <p>Both. Public ids (<code>11210700 + real id</code>) and raw database ids are
               resolved: when the first attempt returns nothing the gateway retries with the
               id offset applied. Plain text queries fall back to the full text search.</p>
          </details>
          <details>
            <summary>What does the type drop down change?</summary>
            <p>It narrows the query. With <i>All types</i> the search runs across artists,
               albums, songs, videos and playlists; picking a single type restricts the answer to
               that table (and to the right sub type, for example <i>Podcast</i> inside songs or
               <i>Music video</i> inside videos).</p>
          </details>
          <details>
            <summary>Where do the results come from?</summary>
            <p>Every call is proxied server side to
               <code>https://hamrahang.ir/ha.php?_qjs=...</code>, so no CORS preflight, no token in
               <code>localStorage</code> and one single place to change the upstream url.</p>
          </details>
        </div>
      </section>

      <!-- ============================== EDIT =========================== -->
      <section class="view" id="view-edit" data-view="edit">

        <!-- ---------- login gate (checked before anything else) ---------- -->
        <div class="login-wrap" id="loginGate">
          <div class="login-head">
            <span class="brand-mark">&#9835;</span>
            <span><b>Administrator sign in</b><span>This area is protected</span></span>
          </div>

          <form class="panel login-form" id="loginForm" novalidate autocomplete="on">
            <div class="field">
              <label for="loginUser">Username or e-mail</label>
              <input class="input" id="loginUser" name="username" type="text"
                     autocomplete="username" spellcheck="false" required autofocus>
            </div>

            <div class="field">
              <label for="loginPass">Password</label>
              <input class="input" id="loginPass" name="password" type="password"
                     autocomplete="current-password" required>
            </div>

            <p class="form-alert" id="loginError" role="alert" hidden></p>

            <button class="btn btn-primary btn-block" id="loginBtn" type="submit">Sign in</button>

            <div class="login-meta">
              <span class="hint">Credentials are verified by <b>hamrahang.ir</b> over JSON.</span>
            </div>
          </form>
        </div>

        <!-- ---------------------- edit workspace ---------------------- -->
        <div id="editWorkspace" hidden>

          <div class="ws-head">
            <div>
              <h1>Edit</h1>
              <p class="sub" style="margin-bottom:0">Look an item up by id or keyword, then edit it
                 or add a new one. The page never reloads.</p>
            </div>
            <div class="ws-user">
              <span><b class="js-user-name">admin</b><br><em class="js-user-role">administrator</em></span>
              <button class="btn btn-ghost btn-sm js-logout" type="button">Sign out</button>
            </div>
          </div>

          <!-- ---------------------- search zone (default) ---------------------- -->
          <div id="searchZone">

          <!-- search form -->
          <form class="panel" id="searchForm" autocomplete="off" novalidate>
            <div class="searchbar">
              <div class="field field-q">
                <label for="searchInput">Id or keyword</label>
                <input class="input" id="searchInput" type="search" name="q"
                       placeholder="11210708, baroone, Ebi ..." spellcheck="false">
              </div>

              <div class="field field-type">
                <label for="searchType">Content type</label>
                <select class="select" id="searchType" name="type">
                  <option value="all">All types</option>
                  <option value="song">Song</option>
                  <option value="podcast">Podcast</option>
                  <option value="album">Album</option>
                  <option value="artist">Artist</option>
                  <option value="musicvideo">Music video</option>
                  <option value="video">Video</option>
                  <option value="playlist">Playlist</option>
                </select>
              </div>

              <button class="btn btn-primary" id="searchBtn" type="submit">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                Search
              </button>
            </div>
          </form>

          <!-- answer of the search, rendered right here -->
          <div class="results-head" id="resultsHead" hidden>
            <h2 id="resultsTitle">Results</h2>
            <span class="pill" id="resultsCount">0</span>
          </div>

          <p class="status" id="searchStatus" role="status" aria-live="polite" hidden></p>

          <ul class="skeleton" id="resultsSkeleton" hidden>
            <li></li><li></li><li></li>
          </ul>

          <ul class="results" id="resultsList"></ul>

          <div class="empty" id="resultsEmpty" hidden>
            <b>No match</b>
            Try another id or keyword, or switch the content type to
            <i>All types</i>.
          </div>

          <!-- add new -->
          <div class="add-block">
            <button class="btn btn-accent" id="addNewBtn" type="button" aria-expanded="false" aria-controls="addPanel">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Add new
            </button>

            <div class="panel add-panel" id="addPanel">
              <div class="add-placeholder">
                <h2>New content</h2>
                <p class="hint" style="margin:0 0 12px">Open a blank editor. Global name is required; other fields are optional.</p>
                <div class="rel-attach-row" style="flex-wrap:wrap">
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="song">+ Song</button>
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="podcast">+ Podcast</button>
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="artist">+ Artist</button>
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="album">+ Album</button>
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="musicvideo">+ Music video</button>
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="video">+ Video</button>
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="owner" data-act9-only hidden>+ Owner</button>
                  <button class="btn btn-ghost btn-sm" type="button" data-create-type="playlist" data-act9-only hidden>+ Playlist</button>
                </div>
              </div>
            </div>
          </div><!-- /add-block -->

          </div><!-- /searchZone -->

          <!-- ----------------- item zone : editor for one item ------------------ -->
          <div id="itemZone" hidden></div>

        </div><!-- /editWorkspace -->
      </section>
    </main>

    <!-- ======================= Mobile menu (bottom) ==================== -->
    <nav class="tabbar" aria-label="Primary mobile">
      <button class="tab is-active" type="button" data-view-btn="home">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5.5 9.5V20h13V9.5"/><path d="M10 20v-5h4v5"/></svg>
        Home
      </button>
      <button class="tab" type="button" data-view-btn="faq">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.2a2.5 2.5 0 1 1 3.7 2.2c-.8.5-1.3 1-1.3 1.9v.4"/><path d="M12 17.2h.01"/></svg>
        FAQ
      </button>
      <button class="tab" type="button" data-view-btn="edit">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M14.5 5.5l4 4"/></svg>
        Edit
      </button>
    </nav>

  </div><!-- /stage -->
</div><!-- /app -->

<noscript>
  <div style="padding:20px;color:#ff9db1">JavaScript is required to run this console.</div>
</noscript>

<script>
/* ==========================================================================
 *  Boot state produced by PHP (session, csrf token, endpoints)
 * ========================================================================== */
window.__BOOT__ = <?= $bootJson ?>;
</script>
<script>
'use strict';

/* --------------------------------------------------------------------------
 *  Small helpers
 * ------------------------------------------------------------------------ */
const $  = (sel, root) => (root || document).querySelector(sel);
const $$ = (sel, root) => Array.prototype.slice.call((root || document).querySelectorAll(sel));

const BOOT = window.__BOOT__ || {};

const state = {
  loggedIn  : BOOT.logged_in === true,
  admin     : BOOT.admin === true,
  user      : BOOT.user || null,
  token     : BOOT.token || '',
  thumbBase : BOOT.thumb_base || 'https://hamrahang.ir/thumb/',
  idOffset  : Number(BOOT.id_offset) || 0,
  busy      : false,
  selected  : null,
  pingDone  : false,
  item      : null,     // draft of the item currently being edited
  itemBusy  : false,
  lastSaved : null,     // payload of the last successful submit
  editStack : [],       // parent drafts when opening nested edit (album → song, …)
  artistHits: [],       // artist search hits shown under "add artist"
  langs     : [],       // taxonomy cache (localStorage ha_lan)  -> SN.lan
  gnrs      : [],       // taxonomy cache (localStorage ha_gnr)  -> SN.gnr
  profile   : null      // localStorage ha_profile (same key as main app)
};

const ALL_TABLES = ['song', 'album', 'artist', 'video', 'playlist'];

/* select value -> ha.php page (p=...) */
const TYPE_TABLE = {
  song:'song', podcast:'song', album:'album', artist:'artist',
  musicvideo:'video', video:'video', playlist:'playlist'
};

const TYPE_LABEL = {
  song:'Song', podcast:'Podcast', album:'Album', artist:'Artist',
  musicvideo:'Music video', video:'Video', playlist:'Playlist'
};

const ERRORS = {
  missing_credentials  : 'Username and password are required.',
  invalid_credentials  : 'Wrong username or password.',
  account_disabled     : 'This account is disabled. Contact the support.',
  forbidden            : 'This account has no administrator access.',
  unauthorized         : 'Your session has expired, please sign in again.',
  bad_token            : 'Security token mismatch, please reload the page.',
  upstream_unreachable : 'The Hamrahang API can not be reached right now.',
  bad_upstream_response: 'The Hamrahang API answered something unexpected.',
  not_found            : 'This item no longer exists (or it is deactivated).',
  bad_payload          : 'The gateway rejected the submitted data.',
  network              : 'Network error, please try again.'
};

function esc(value) {
  return String(value === null || value === undefined ? '' : value)
    .replace(/[&<>"']/g, c => ({
      '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
    })[c]);
}

function errorText(data) {
  if (!data) return ERRORS.network;
  let base = '';
  if (data.error && ERRORS[data.error]) base = ERRORS[data.error];
  else if (data.error) base = 'Error: ' + data.error;
  else return ERRORS.network;
  // surface upstream detail (e.g. MySQL message on update_failed)
  if (data.detail) base += ' — ' + String(data.detail);
  return base;
}

/* --------------------------------------------------------------------------
 *  JSON transport (everything goes through index.php)
 * ------------------------------------------------------------------------ */
async function request(mode, params, payload) {
  const qs = new URLSearchParams();
  qs.set('mode', mode);
  if (params) {
    Object.keys(params).forEach(key => qs.set(key, params[key]));
  }

  const init = {
    method      : (payload === undefined) ? 'GET' : 'POST',
    credentials : 'same-origin',
    headers     : {
      'X-Requested-With': 'XMLHttpRequest',
      // every call carries the session token, including the read only ones
      'X-Admin-Token'    : state.token
    }
  };

  if (payload !== undefined) {
    init.headers['Content-Type'] = 'application/json; charset=UTF-8';
    init.body = JSON.stringify(payload);
  }

  let res;
  try {
    res = await fetch('index.php?' + qs.toString(), init);
  } catch (err) {
    return { error: 'network' };
  }

  let data = null;
  try { data = await res.json(); } catch (err) { data = { error: 'bad_response' }; }

  if (res.status === 401 && data && data.error === 'unauthorized') {
    signOutLocally('Your session has expired, please sign in again.');
  }
  return data;
}

const queryGet  = params      => request('query', params);
const queryPost = (params, b) => request('query', params, b);

/* --------------------------------------------------------------------------
 *  Identity : gate, chips, logout
 * ------------------------------------------------------------------------ */
function renderIdentity() {
  const name = state.user
    ? (state.user.user_name || state.user.email || ('#' + state.user.id_user))
    : 'guest';
  const role = state.user
    ? (state.admin ? 'administrator' : ('role act=' + state.user.act))
    : 'not signed in';

  $$('.js-user-name').forEach(el => { el.textContent = name; });
  $$('.js-user-role').forEach(el => { el.textContent = role; });
  $$('.js-logout').forEach(el => { el.hidden = !state.loggedIn; });

  document.body.classList.toggle('is-authed', state.loggedIn);
}

function renderGate() {
  const signedIn = state.loggedIn && state.admin;
  $('#loginGate').hidden   = signedIn;
  $('#editWorkspace').hidden = !signedIn;
}

function showLoginError(message, isInfo) {
  const el = $('#loginError');
  if (!message) { el.hidden = true; el.textContent = ''; return; }
  el.textContent = message;
  el.hidden = false;
  el.className = 'form-alert' + (isInfo ? ' is-info' : '');
}

function signOutLocally(message) {
  state.loggedIn = false;
  state.admin    = false;
  state.user     = null;
  if (window.SN) window.SN.user = null;
  state.selected = null;
  renderGate();
  renderIdentity();
  if (message) showLoginError(message, true);
}

/* --------------------------------------------------------------------------
 *  Sign in / sign out
 * ------------------------------------------------------------------------ */
async function submitLogin(event) {
  event.preventDefault();

  const username = $('#loginUser').value.trim();
  const password = $('#loginPass').value;

  if (!username || !password) {
    showLoginError('Username and password are required.');
    return;
  }

  showLoginError('');
  const btn = $('#loginBtn');
  btn.setAttribute('aria-busy', 'true');
  btn.textContent = 'Signing in...';

  const data = await request('login', null, { username, password });

  btn.removeAttribute('aria-busy');
  btn.textContent = 'Sign in';

  if (!data || !data.ok) {
    showLoginError(errorText(data));
    return;
  }

  state.loggedIn = true;
  state.admin    = data.admin === true;
  state.token    = data.token || state.token;
  syncSnUser(data.user || null);

  // ha_profile : ensure it exists and is written (same key as main app)
  LoadProfile();
  SaveProfile();

  $('#loginPass').value = '';
  renderGate();
  renderIdentity();
  // taxonomy is already loaded at boot; just make sure it is fresh
  ensureTaxonomy(true);

  // the page must be in "edit" mode right after a successful login
  const input = $('#searchInput');
  if (input) input.focus();
  setStatus('Signed in as ' + ((state.user && state.user.user_name) || 'admin') + '.', 'ok');
}

async function logout() {
  await request('logout', null, {});
  // a fresh session (and a fresh csrf token) is created by the next request
  window.location.hash = '#/edit';
  window.location.reload();
}

/* --------------------------------------------------------------------------
 *  Router (hash based, single page)
 * ------------------------------------------------------------------------ */
const VIEWS = ['home', 'faq', 'edit'];

function route() {
  const raw   = (window.location.hash || '').replace(/^#\/?/, '');
  const name  = raw.split('/')[0];
  const view  = VIEWS.indexOf(name) === -1 ? 'home' : name;

  $$('.view').forEach(section => {
    section.classList.toggle('is-active', section.id === 'view-' + view);
  });

  // only the two real menus receive the active style,
  // plain buttons that simply jump to a view are left alone
  $$('.nav-item[data-view-btn], .tab[data-view-btn]').forEach(btn => {
    const on = btn.getAttribute('data-view-btn') === view;
    btn.classList.toggle('is-active', on);
    btn.setAttribute('aria-current', on ? 'page' : 'false');
  });

  if (view === 'home') checkServer();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* --------------------------------------------------------------------------
 *  Home : live status of the upstream API
 * ------------------------------------------------------------------------ */
async function checkServer() {
  if (state.pingDone) return;
  state.pingDone = true;

  const pill  = $('#serverPill');
  const label = $('#serverStatus');
  const data  = await request('ping');

  if (data && data.status === 'ok') {
    label.textContent = 'API online - v' + (data.version || '?');
    pill.classList.add('is-ok');
  } else {
    label.textContent = 'API unreachable';
    pill.classList.add('is-bad');
  }
}

/* --------------------------------------------------------------------------
 *  Edit : status line, busy state
 * ------------------------------------------------------------------------ */
function setStatus(text, kind) {
  const el = $('#searchStatus');
  if (!text) { el.hidden = true; el.textContent = ''; el.className = 'status'; return; }
  el.textContent = text;
  el.className   = 'status' + (kind ? ' is-' + kind : '');
  el.hidden      = false;
}

function setBusy(busy) {
  state.busy = busy;
  $('#searchBtn').setAttribute('aria-busy', busy ? 'true' : 'false');
  $('#resultsSkeleton').hidden = !busy;
  if (busy) {
    $('#resultsList').innerHTML   = '';
    $('#resultsEmpty').hidden     = true;
    $('#resultsHead').hidden      = true;
  }
}

/* --------------------------------------------------------------------------
 *  Edit : search
 * ------------------------------------------------------------------------ */
function inferType(table, item) {
  const meta = (item && item.meta) || {};
  if (table === 'song')  return meta.type === 'podcast' ? 'podcast' : 'song';
  if (table === 'video') return meta.type === 'video'    ? 'video'   : 'musicvideo';
  return table;
}

/** Secondary titles from data.names (object) joined with " - ". No table *_fa. */
function namesAltFromItem(item) {
  if (!item || typeof item !== 'object') return '';
  const n = item.names;
  if (n && typeof n === 'object' && !Array.isArray(n)) {
    const vals = Object.keys(n).map(k => String(n[k] || '').trim()).filter(Boolean);
    if (vals.length) return vals.join(' - ');
  }
  if (Array.isArray(n)) {
    const vals = n.map(x => {
      if (typeof x === 'string') return x.trim();
      if (x && typeof x === 'object') return String(x.name || x.val || '').trim();
      return '';
    }).filter(Boolean);
    if (vals.length) return vals.join(' - ');
  }
  return '';
}

function toRows(table, items) {
  if (!Array.isArray(items)) return [];
  return items.map(item => ({
    type  : inferType(table, item),
    table : table,
    id    : Number(item.id) || 0,
    name  : item.name || '(untitled)',
    nameAlt: namesAltFromItem(item),
    url   : item.url || '',
    artists: Array.isArray(item.artists)
      ? item.artists.map(a => a.name).filter(Boolean).join(', ')
      : '',
    thumb : (item.meta && item.meta.tmb) ? String(item.meta.tmb) : '',
    raw   : item
  }));
}

function filterByType(rows, wanted) {
  if (wanted === 'all') return rows;
  return rows.filter(row => row.type === wanted);
}

function mergeRows(groups) {
  const seen = {};
  const out  = [];
  groups.forEach(rows => {
    (rows || []).forEach(row => {
      const key = row.type + ':' + row.id;
      if (row.id && seen[key]) return;
      seen[key] = true;
      out.push(row);
    });
  });
  return out;
}

/** Full text search on every table, then narrowed by the selected type. */
async function searchByText(query, type) {
  const data = await queryGet({ qjs: 'ep', p: 'search', q1: query });
  if (data && data.error) throw new Error(data.error);

  const box = (data && data.search) || {};
  const rows = [].concat(
    toRows('artist',   box.artists),
    toRows('album',    box.albums),
    toRows('song',     box.songs),
    toRows('video',    box.videos),
    toRows('playlist', box.playlists)
  );
  return filterByType(rows, type);
}

/** Exact id lookup through the "resolve" endpoint of every target table. */
async function resolveTables(tables, id) {
  const groups = await Promise.all(tables.map(table =>
    queryPost({ qjs: 'ep', p: table, q1: 'resolve' }, { ids: [Number(id)] })
      .then(data => {
        if (data && data.error) return [];
        if (!Array.isArray(data)) return [];
        return data.map(item => ({ table, item }));
      })
      .catch(() => [])
  ));

  const rows = [];
  groups.forEach(group => group.forEach(entry => {
    rows.push.apply(rows, toRows(entry.table, [entry.item]));
  }));
  return rows;
}

async function searchById(query, type) {
  const tables = (type === 'all') ? ALL_TABLES : [TYPE_TABLE[type]];
  let rows = await resolveTables(tables, query);

  // the id may be the raw database id instead of the public one
  if (rows.length === 0 && state.idOffset) {
    rows = await resolveTables(tables, Number(query) + state.idOffset);
  }
  return rows;
}

async function runSearch(event) {
  if (event) event.preventDefault();
  if (state.busy) return;

  const query = $('#searchInput').value.trim();
  const type  = $('#searchType').value;

  if (!query) {
    setStatus('Enter an id, a title or an artist name first.', 'warn');
    $('#searchInput').focus();
    return;
  }

  setStatus('');
  setBusy(true);

  try {
    const jobs = [];
    // exact id hit first, text matches after it
    if (/^\d+$/.test(query)) jobs.push(searchById(query, type));
    jobs.push(searchByText(query, type));

    const groups = await Promise.all(jobs);
    let rows = mergeRows(groups);
    // Playlists are admin-only (act9)
    if (!isAct9()) {
      rows = rows.filter(r => r && r.type !== 'playlist');
    }
    if (type === 'playlist' && !isAct9()) {
      setStatus('Playlists are only available for act9 administrators.', 'warn');
      setBusy(false);
      return;
    }
    renderResults(rows, query, type);
  } catch (err) {
    setStatus('Search failed: ' + (err && err.message ? err.message : 'unknown error'), 'error');
  } finally {
    setBusy(false);
  }
}

function thumbHtml(row) {
  const letter = esc((row.name || '?').charAt(0).toUpperCase());
  let image = '';
  const t = row && row.thumb ? String(row.thumb).trim() : '';
  if (t) {
    let src = '';
    if (/^https?:\/\//i.test(t) || /^data:image\//i.test(t)) {
      // already a full URL or data-URL (saved form / media row)
      src = t;
    } else if (/^[A-Za-z0-9_\-.]+$/.test(t)) {
      // id_img key on the thumb host
      src = state.thumbBase + encodeURIComponent(t) + '.jpg';
    }
    if (src) {
      image = '<img src="' + esc(src) + '" alt="" loading="lazy" onerror="this.style.display=\'none\'">';
    }
  }
  return '<span class="thumb"><i>' + letter + '</i>' + image + '</span>';
}

function rowHtml(row) {
  const label = TYPE_LABEL[row.type] || row.type;
  const parts = [];
  if (row.artists) parts.push(esc(row.artists));
  if (row.url) parts.push(esc(row.url));
  const alt = row.nameAlt || '';

  return '' +
    '<li class="result" data-key="' + esc(row.type + ':' + row.id) + '">' +
      thumbHtml(row) +
      '<div class="result-main">' +
        '<div class="result-title"><span>' + esc(row.name) + '</span>' +
          (alt ? '<span class="fa">' + esc(alt) + '</span>' : '') +
        '</div>' +
        '<div class="result-sub">' + (parts.length ? parts.join(' &middot; ') : '&mdash;') + '</div>' +
      '</div>' +
      '<div class="result-meta">' +
        '<span class="badge badge-' + esc(row.type) + '">' + esc(label) + '</span>' +
        '<span class="rid">#' + esc(row.id) + '</span>' +
      '</div>' +
      '<button class="btn btn-ghost btn-sm" type="button" data-edit="' +
        esc(row.type + ':' + row.id) + '">Edit</button>' +
    '</li>';
}

function renderResults(rows, query, type) {
  const list  = $('#resultsList');
  const empty = $('#resultsEmpty');
  const head  = $('#resultsHead');

  if (!rows.length) {
    list.innerHTML = '';
    head.hidden = true;
    empty.hidden = false;
    setStatus('No result for "' + query + '".', 'warn');
    return;
  }

  list.innerHTML = rows.map(rowHtml).join('');
  empty.hidden = true;
  head.hidden  = false;
  $('#resultsTitle').textContent = 'Results for "' + query + '"';
  $('#resultsCount').textContent = rows.length + (type === 'all' ? ' items' : ' ' + (TYPE_LABEL[type] || type));
  setStatus(rows.length + ' result' + (rows.length === 1 ? '' : 's') + ' found.', 'ok');
}

/**
 * --------------------------------------------------------------------------
 *  Edit : item editor (fetch full card JSON, render form, submit -> card)
 * ------------------------------------------------------------------------ */

/* Taxonomy for the edit form selectors.
   Source of truth at runtime: window.SN.lan / window.SN.gnr
   (hydrated from localStorage ha_lan / ha_gnr at boot, then refreshed
   from ?_qjs=lan / ?_qjs=gnr in the background). No hardcoded lists. */

function taxLangs() {
  const raw = (window.SN && Array.isArray(window.SN.lan) && window.SN.lan.length)
    ? window.SN.lan
    : (state.langs || []);
  return raw.map(x => Array.isArray(x) ? { code: x[0], label: x[1] } : x);
}
function taxGenres() {
  const raw = (window.SN && Array.isArray(window.SN.gnr) && window.SN.gnr.length)
    ? window.SN.gnr
    : (state.gnrs || []);
  return raw.map(x => Array.isArray(x) ? { id: x[0], slug: x[1], name: x[2] } : x);
}

const ACT_OPTIONS = [
  { v:0, label:'0 · Off (deactivated)' },
  { v:1, label:'1 · Active' },
  { v:2, label:'2 · Special' },
  { v:3, label:'3 · Advertising' }
];

const GLOBE_SVG = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/>' +
  '<path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18"/></svg>';

function langOptions(selected) {
  const list = taxLangs().slice();
  if (selected && !list.some(l => l.code === selected)) {
    list.unshift({ code: selected, label: selected.toUpperCase() + ' (from item2lang)' });
  }
  const head = selected ? '' : '<option value="" selected>&mdash; none &mdash;</option>';
  return head + list.map(l =>
    '<option value="' + esc(l.code) + '"' + (l.code === selected ? ' selected' : '') + '>' +
    esc(l.code + ' — ' + l.label) + '</option>'
  ).join('');
}

function actOptions(selected) {
  return ACT_OPTIONS.map(o =>
    '<option value="' + o.v + '"' + (Number(selected) === o.v ? ' selected' : '') + '>' +
    esc(o.label) + '</option>'
  ).join('');
}

/** Build the editable draft from the full card JSON (?_qjs=ep&p=<type>&q1=<id>). */
function buildDraft(table, full) {
  const meta = full.meta || {};
  const names = {};
  if (full.names && typeof full.names === 'object') {
    Object.keys(full.names).forEach(code => {
      if (full.names[code]) names[code] = String(full.names[code]);
    });
  }
  // Language comes from item2lang (the read API exposes it as full.languages).
  // meta.lan is intentionally NOT used as a source any more.
  const langs = Array.isArray(full.languages) ? full.languages : [];
  const lan   = langs.length ? String(langs[0]) : '';
  const genres = Array.isArray(full.genres) ? full.genres : [];
  const gnr = genres.length
    ? genres.map(g => ({ id: Number(g.id) || 0, name: g.name || '', slug: g.slug || '' }))
    : (Array.isArray(meta.gnr) ? meta.gnr.map(id => ({ id: Number(id) || 0, name: '', slug: '' })) : []);

  // Media rows: meta exposes resolved file keys (id_img). Build full preview URLs.
  const media = mediaFromMeta(meta);

  return {
    table   : table,
    type    : inferType(table, full),
    id      : Number(full.id) || 0,
    name    : full.name || '',
    names   : names,
    des     : (meta.des === undefined || meta.des === null) ? '' : String(meta.des),
    lan     : lan || '',
    gnr     : gnr,
    url     : full.url || '',
    rls     : meta.rls || '',
    act     : (meta.act === undefined || meta.act === null || meta.act === '') ? 1 : Number(meta.act),
    tmb     : meta.tmb ? String(meta.tmb) : '',
    media   : media,
    lyrics  : lyricsFromCard(full, meta),
    lyricEdit : null,   // { index|null, lng, knd, fmt, body } while the inline editor is open
    artists : (Array.isArray(full.artists) ? full.artists : []).map(a => ({
      id  : Number(a.id) || 0,
      name: a.name || '',
      url : a.url || '',
      thumb: (a.meta && a.meta.tmb) ? String(a.meta.tmb) : ''
    })),
    albums  : albumsFromCard(full),
    videos  : videosFromCard(full),
    songs   : songsFromCard(full),
    sources : sourcesFromCard(full),
    tracks  : tracksFromCard(full),
    dur     : (meta.dur != null && meta.dur !== '') ? Number(meta.dur) : 0,
    // artist-only profile fields
    cc      : meta.cc ? String(meta.cc) : (meta.nat ? String(meta.nat) : ''),
    bd      : meta.bd ? String(meta.bd) : '',
    ytm     : meta.ytm ? String(meta.ytm) : '',
    ins     : meta.ins ? String(meta.ins) : '',
    tlg     : meta.tlg ? String(meta.tlg) : '',
    raw     : full
  };
}

/** Songs linked to a video (full.songs from song2video). */
function songsFromCard(full) {
  const list = [];
  const push = s => {
    if (!s) return;
    const id = Number(s.id) || 0;
    if (!id || list.some(x => x.id === id)) return;
    const type = (s.meta && s.meta.type === 'podcast') ? 'podcast' : 'song';
    list.push({
      id   : id,
      name : s.name || '',
      nameAlt: namesAltFromItem(s),
      url  : s.url || '',
      type : type,
      thumb: (s.meta && s.meta.tmb) ? String(s.meta.tmb) : (s.thumb ? String(s.thumb) : ''),
      artists: Array.isArray(s.artists)
        ? s.artists.map(a => (a && a.name) || '').filter(Boolean).join(', ')
        : ''
    });
  };
  if (Array.isArray(full.songs)) full.songs.forEach(push);
  // artist full card may split podcasts into a separate bucket
  if (Array.isArray(full.podcasts)) full.podcasts.forEach(s => {
    if (!s) return;
    if (!s.meta) s.meta = {};
    if (!s.meta.type) s.meta.type = 'podcast';
    push(s);
  });
  return list;
}

/**
 * Album track list from full.songs (song table only — no videos).
 * Prefer explicit trk / trk_num; missing or 0 → end of list, then renumber 1..n for UI.
 */
function tracksFromCard(full) {
  const raw = Array.isArray(full.songs) ? full.songs : [];
  const list = raw.map((s, i) => {
    if (!s) return null;
    const id = Number(s.id) || 0;
    if (!id) return null;
    const trk = (s.trk != null) ? Number(s.trk)
      : (s.trk_num != null) ? Number(s.trk_num) : 0;
    const type = (s.meta && s.meta.type === 'podcast') ? 'podcast' : 'song';
    return {
      id   : id,
      name : s.name || ('#' + id),
      nameAlt: namesAltFromItem(s),
      type : type,
      url  : s.url || '',
      thumb: (s.meta && s.meta.tmb) ? String(s.meta.tmb) : '',
      artists: Array.isArray(s.artists)
        ? s.artists.map(a => a.name).filter(Boolean).join(', ')
        : '',
      trk  : (trk > 0) ? trk : 0
    };
  }).filter(Boolean);

  return normalizeTrackOrder(list);
}

/** Keep array order; set trk = 1..n (after arrow move / delete / add). */
function renumberTracks(list) {
  const ordered = (list || []).slice();
  ordered.forEach((t, i) => { t.trk = i + 1; });
  return ordered;
}

/**
 * Initial load from card only: sort by existing trk (0/missing last), then 1..n.
 * Do NOT call after a manual reorder — sorting by old trk undoes the swap.
 */
function normalizeTrackOrder(list) {
  const arr = (list || []).slice();
  const numbered = arr.filter(t => t.trk > 0).sort((a, b) => a.trk - b.trk);
  const rest = arr.filter(t => !(t.trk > 0));
  return renumberTracks(numbered.concat(rest));
}

function isAct9() {
  // Prefer SN.user (same mirror as main app), fall back to state.user from boot/login
  const u = (window.SN && window.SN.user) ? window.SN.user : state.user;
  return !!(u && Number(u.act) >= 9);
}
function myUserId() {
  const u = (window.SN && window.SN.user) ? window.SN.user : state.user;
  return u ? (Number(u.id_user) || 0) : 0;
}

/** Show/hide act9-only controls (Owner, Playlist create + search type). */
function applyAct9Ui() {
  const on = isAct9();
  document.querySelectorAll('[data-act9-only]').forEach(el => {
    if (el.tagName === 'OPTION') {
      el.disabled = !on;
      el.hidden = !on;
      if (!on && el.selected) {
        const sel = el.parentElement;
        if (sel) sel.value = 'all';
      }
    } else {
      el.hidden = !on;
    }
  });
}


/* ---- SN.owners + ?_qjs=own ------------------------------------------------ */
const OWNERS_LS_KEY = 'ha_owners';

function ensureOwnersMap() {
  if (typeof window.SN !== 'object' || window.SN === null) window.SN = {};
  if (!window.SN.owners || typeof window.SN.owners !== 'object') {
    window.SN.owners = {};
    try {
      const raw = localStorage.getItem(OWNERS_LS_KEY);
      if (raw) {
        const parsed = JSON.parse(raw);
        if (parsed && typeof parsed === 'object') window.SN.owners = parsed;
      }
    } catch (e) {}
  }
  // System / Hamrahang
  if (!window.SN.owners['0']) {
    window.SN.owners['0'] = { id: 0, name: 'Hamrahang', act: 0, photo: '' };
  }
  return window.SN.owners;
}

function saveOwnersCache() {
  ensureOwnersMap();
  try {
    localStorage.setItem(OWNERS_LS_KEY, JSON.stringify(window.SN.owners));
  } catch (e) {}
}

function ownerDisplayName(o) {
  if (!o) return 'Hamrahang';
  if (Number(o.id) === 0) return 'Hamrahang';
  return o.name || ('User #' + o.id);
}

function getOwner(pubId) {
  const id = Number(pubId) || 0;
  ensureOwnersMap();
  return window.SN.owners[String(id)] || null;
}

/** Cache one or more owner objects from API. */
function putOwners(list) {
  ensureOwnersMap();
  (list || []).forEach(o => {
    if (!o || o.id == null) return;
    const id = Number(o.id) || 0;
    window.SN.owners[String(id)] = {
      id: id,
      name: o.name || (id ? ('User #' + id) : 'Hamrahang'),
      act: Number(o.act) || 0,
      photo: o.photo ? String(o.photo) : ''
    };
  });
  saveOwnersCache();
}

/**
 * Resolve missing public user ids via ?_qjs=own&ids=1,2,3
 * Returns a map id -> owner (from SN after fill).
 */
async function resolveOwners(ids) {
  ensureOwnersMap();
  const need = [];
  const seen = {};
  (ids || []).forEach(raw => {
    const id = Number(raw) || 0;
    if (id <= 0) return;
    if (seen[id]) return;
    seen[id] = true;
    if (!window.SN.owners[String(id)]) need.push(id);
  });
  if (!need.length) return window.SN.owners;
  try {
    const data = await queryGet({ qjs: 'own', ids: need.join(',') });
    if (data && Array.isArray(data.owners)) putOwners(data.owners);
  } catch (e) {}
  return window.SN.owners;
}

/**
 * Search collaborators (act >= minAct, default 7).
 * 1) Filter SN.owners locally (instant, no network)
 * 2) Always also query API so users never cached still appear
 * 3) Merge unique by id (local first, then remote)
 */
async function searchOwners(query, minAct) {
  const q = String(query || '').trim();
  if (!q) return [];
  const min = minAct != null ? Number(minAct) : 7;
  const qLower = q.toLowerCase();

  ensureOwnersMap();
  const local = [];
  Object.keys(window.SN.owners).forEach(k => {
    const o = window.SN.owners[k];
    if (!o) return;
    const id = Number(o.id) || 0;
    if (id > 0 && (Number(o.act) || 0) < min) return;
    const name = String(o.name || '').toLowerCase();
    const idStr = String(id);
    if (name.indexOf(qLower) !== -1 || idStr === q || idStr.indexOf(q) === 0) {
      local.push({
        id: id,
        name: o.name || (id ? ('User #' + id) : 'Hamrahang'),
        act: Number(o.act) || 0,
        photo: o.photo || ''
      });
    }
  });

  let remote = [];
  try {
    const data = await queryGet({ qjs: 'own', q: q, min_act: String(min) });
    if (data && Array.isArray(data.owners)) {
      putOwners(data.owners);
      remote = data.owners;
    }
  } catch (e) {}

  const byId = {};
  const out = [];
  local.forEach(o => {
    const id = Number(o.id) || 0;
    if (byId[id]) return;
    byId[id] = true;
    out.push(o);
  });
  remote.forEach(o => {
    if (!o || o.id == null) return;
    const id = Number(o.id) || 0;
    if (byId[id]) return;
    byId[id] = true;
    out.push({
      id: id,
      name: o.name || (id ? ('User #' + id) : 'Hamrahang'),
      act: Number(o.act) || 0,
      photo: o.photo ? String(o.photo) : ''
    });
  });
  return out;
}

/** Keep SN.user in sync with the admin session (id, act, name, …). */
function syncSnUser(user) {
  if (typeof window.SN !== 'object' || window.SN === null) window.SN = {};
  if (user && typeof user === 'object') {
    window.SN.user = {
      id_user   : Number(user.id_user) || 0,
      user_name : user.user_name || '',
      first_name: user.first_name || '',
      last_name : user.last_name || '',
      email     : user.email || '',
      act       : Number(user.act) || 0
    };
    state.user = window.SN.user;
  } else {
    window.SN.user = null;
    state.user = null;
  }
  try { applyAct9Ui(); } catch (e) {}
}

/**
 * Sources from full card JSON.
 * After PHP update: { id, url, bit, user, act }.
 * act7: server already filtered; client still hides others as a soft guard.
 */
function sourcesFromCard(full) {
  const list = [];
  let raw = [];
  if (Array.isArray(full.sources)) raw = full.sources;
  else if (full.raw && Array.isArray(full.raw.sources)) raw = full.raw.sources;

  const act9 = isAct9();
  const me = myUserId();

  raw.forEach((s, i) => {
    if (!s || typeof s !== 'object') return;
    const url = String(s.url || s.song_link || s.video_link || s.link || '').trim();
    if (!url) return;
    let act = (s.act != null) ? Number(s.act)
      : (s.link_act != null) ? Number(s.link_act) : NaN;
    if (!Number.isFinite(act)) {
      const ty = String(s.type || '').toLowerCase();
      if (ty === 'demo') act = 2;
      else if (ty === 'locked' || ty === 'lock') act = 3;
      else if (ty === 'off' || ty === 'deactivated') act = 0;
      else act = 1;
    }
    if (act < 0 || act > 3) act = 1;
    const user = (s.user != null) ? Number(s.user)
      : (s.id_user != null) ? Number(s.id_user) : 0;
    const bit = (s.bit != null) ? Number(s.bit)
      : (s.song_bit != null) ? Number(s.song_bit)
      : (s.vdo_bit != null) ? Number(s.vdo_bit) : 0;
    const id = (s.id != null) ? Number(s.id)
      : (s.id_sng2lnk != null) ? Number(s.id_sng2lnk)
      : (s.id_vdo2lnk != null) ? Number(s.id_vdo2lnk) : 0;

    // Soft client filter: act7 only own rows (when user id is known on the row)
    if (!act9 && user > 0 && me > 0 && user !== me) return;

    list.push({
      id   : id,
      url  : url,
      bit  : bit,
      user : user,
      act  : act,
      _key : 's' + i + '_' + (id || i)
    });
  });
  return list;
}

/** Music videos linked to this song / artist (full.videos, musicvideos, parents). */
function videosFromCard(full) {
  const list = [];
  const push = v => {
    if (!v) return;
    const id = Number(v.id) || 0;
    if (!id || list.some(x => x.id === id)) return;
    const type = (v.meta && v.meta.type) ? v.meta.type
      : (v.type || 'musicvideo');
    list.push({
      id   : id,
      name : v.name || '',
      nameAlt: namesAltFromItem(v),
      url  : v.url || '',
      type : type,
      thumb: (v.meta && v.meta.tmb) ? String(v.meta.tmb) : (v.thumb ? String(v.thumb) : '')
    });
  };
  if (Array.isArray(full.videos)) full.videos.forEach(push);
  if (Array.isArray(full.musicvideos)) full.musicvideos.forEach(push);
  if (Array.isArray(full.parents)) {
    full.parents.filter(p => p && (p.type === 'musicvideo' || p.type === 'video')).forEach(push);
  }
  return list;
}

/**
 * Build lyrics[] from full card.
 * Prefer full.lyrics (with body); fall back to meta.lrc index (no body until loaded).
 */
function lyricsFromCard(full, meta) {
  const out = [];
  const push = (x) => {
    if (!x) return;
    const lng = String(x.lng || x.lang || '').toLowerCase().slice(0, 3);
    const knd = String(x.knd || x.kind || 'lrc').toLowerCase().slice(0, 3);
    const fmt = String(x.fmt || 'txt').toLowerCase().slice(0, 3);
    const src = x.src ? String(x.src) : '';
    const body = (x.body != null) ? String(x.body) : '';
    if (!lng && !src && !body) return;
    // dedupe by lng+knd
    if (out.some(r => r.lng === lng && r.knd === knd)) return;
    out.push({ lng: lng || 'und', knd: knd === 'srt' ? 'srt' : 'lrc', fmt: fmt === 'jsn' ? 'jsn' : 'txt', src: src, body: body });
  };
  if (Array.isArray(full.lyrics)) full.lyrics.forEach(push);
  if (Array.isArray(meta.lrc)) meta.lrc.forEach(push);
  // legacy single numeric lrc id — keep a placeholder so the form shows "has lyric"
  if (!out.length && meta.lrc && typeof meta.lrc !== 'object') {
    out.push({ lng: 'und', knd: 'lrc', fmt: 'txt', src: String(meta.lrc), body: '' });
  }
  return out;
}

/** Albums linked to this song (full.albums or parents with type album). */
function albumsFromCard(full) {
  const list = [];
  const push = a => {
    if (!a) return;
    const id = Number(a.id) || 0;
    if (!id || list.some(x => x.id === id)) return;
    const trk = (a.trk != null) ? Number(a.trk)
      : (a.trk_num != null) ? Number(a.trk_num)
      : (a.meta && a.meta.trk != null) ? Number(a.meta.trk)
      : 0;
    list.push({
      id   : id,
      name : a.name || '',
      nameAlt: namesAltFromItem(a),
      url  : a.url || '',
      thumb: (a.meta && a.meta.tmb) ? String(a.meta.tmb) : (a.thumb ? String(a.thumb) : ''),
      trk  : isNaN(trk) ? 0 : trk
    });
  };
  if (Array.isArray(full.albums)) full.albums.forEach(push);
  if (Array.isArray(full.parents)) {
    full.parents.filter(p => p && p.type === 'album').forEach(push);
  }
  return list;
}

/** Build media[] draft rows from card meta (tmb / tmv / cvr / art / gal). */
function mediaFromMeta(meta) {
  const rows = [];
  const pushKey = (role, key) => {
    if (!key) return;
    const k = String(key);
    const url = /^https?:\/\//i.test(k)
      ? k
      : (state.thumbBase + encodeURIComponent(k) + '.jpg');
    rows.push({ role: role, url: url });
  };
  if (meta.tmb) pushKey('tmb', meta.tmb);
  if (meta.tmv) pushKey('tmv', meta.tmv);
  if (meta.cvr) pushKey('cvr', meta.cvr);
  if (meta.art) pushKey('art', meta.art);
  if (Array.isArray(meta.gal)) {
    meta.gal.forEach(k => pushKey('gal', k));
  } else if (meta.gal) {
    pushKey('gal', meta.gal);
  }
  if (!rows.length) rows.push({ role: 'tmb', url: '' });
  return rows;
}

/**
 * Collapse media rows the same way the server does:
 * non-gal roles → last row wins; gal → keep all; drop off/empty.
 */
function collapseMediaRows(list) {
  if (!Array.isArray(list) || !list.length) return [{ role: 'tmb', url: '' }];
  const unique = {};
  const gals = [];
  list.forEach(m => {
    if (!m) return;
    const role = String(m.role || 'tmb').toLowerCase();
    const url  = m.url != null ? String(m.url).trim() : '';
    if (role === 'off' || !url) return;
    if (role === 'gal') {
      gals.push({ role: 'gal', url: url });
      return;
    }
    // Owner roles ico/photo must stay; content roles stay; unknown → tmb
    const allowed = ['tmb', 'tmv', 'cvr', 'art', 'ico', 'photo'];
    const r = (allowed.indexOf(role) >= 0) ? role : 'tmb';
    unique[r] = { role: r, url: url };
  });
  const out = [];
  ['tmb', 'tmv', 'cvr', 'art', 'ico', 'photo'].forEach(r => {
    if (unique[r]) out.push(unique[r]);
  });
  gals.forEach(g => out.push(g));
  return out.length ? out : [{ role: 'tmb', url: '' }];
}

/** Prefer explicit tmb, else first media row with role=tmb and a non-empty url. */
function resolveDraftThumb(p) {
  if (p && p.tmb) return String(p.tmb);
  if (p && Array.isArray(p.media)) {
    // last tmb wins (matches collapse / server)
    const tmbs = p.media.filter(m => m && m.role === 'tmb' && m.url);
    if (tmbs.length) return String(tmbs[tmbs.length - 1].url);
    const any = p.media.filter(m => m && m.url && m.role !== 'off');
    if (any.length) return String(any[any.length - 1].url);
  }
  return '';
}

/** Go from a submitted payload back to an editable draft (card -> form). */
function payloadToDraft(p) {
  const mediaRaw = Array.isArray(p.media) && p.media.length
    ? p.media.map(m => ({ role: m.role || 'tmb', url: m.url || '' }))
    : (p.tmb
        ? [{ role: 'tmb', url: /^https?:\/\//i.test(String(p.tmb)) || /^data:image\//i.test(String(p.tmb))
            ? String(p.tmb)
            : (state.thumbBase + p.tmb + '.jpg') }]
        : [{ role: 'tmb', url: '' }]);
  // After submit / re-edit from saved payload: show only what the server keeps
  const media = collapseMediaRows(mediaRaw);
  const tmb = resolveDraftThumb({ tmb: p.tmb, media: media });
  return {
    table   : p.table || 'song',
    type    : p.type || 'song',
    id      : Number(p.id) || 0,
    name    : p.name || '',
    names   : (p.names && typeof p.names === 'object') ? p.names : {},
    des     : (p.des === undefined || p.des === null) ? '' : String(p.des),
    lan     : p.lan || '',
    gnr     : (Array.isArray(p.gnr) ? p.gnr : []).map(g =>
      (g && typeof g === 'object')
        ? { id: Number(g.id) || 0, name: g.name || '', slug: g.slug || '' }
        : { id: Number(g) || 0, name: '', slug: '' }
    ),
    url     : p.url || '',
    rls     : p.rd || '',
    act     : Number(p.act) || 0,
    tmb     : tmb,
    media   : media,
    lyrics  : (Array.isArray(p.lyrics) ? p.lyrics : []).map(x => ({
      lng : String(x.lng || 'und').toLowerCase().slice(0, 3),
      knd : String(x.knd || 'lrc').toLowerCase().slice(0, 3),
      fmt : String(x.fmt || 'txt').toLowerCase().slice(0, 3),
      src : x.src ? String(x.src) : '',
      body: (x.body != null) ? String(x.body) : ''
    })),
    lyricEdit : null,
    artists : Array.isArray(p.artists) ? p.artists : [],
    albums  : (Array.isArray(p.albums) ? p.albums : []).map(a => ({
      id   : Number(a && a.id) || 0,
      name : (a && a.name) || '',
      url  : (a && a.url) || '',
      thumb: (a && a.thumb) || '',
      trk  : (a && a.trk != null) ? Number(a.trk) : 0
    })),
    videos  : (Array.isArray(p.videos) ? p.videos : []).map(v => ({
      id   : Number(v && v.id) || 0,
      name : (v && v.name) || '',
      url  : (v && v.url) || '',
      thumb: (v && v.thumb) || ''
    })),
    tracks  : normalizeTrackOrder((Array.isArray(p.tracks) ? p.tracks : []).map(t => ({
      id   : Number(t && t.id) || 0,
      name : (t && t.name) || '',
      nameAlt: (t && t.nameAlt) || '',
      type : (t && t.type) || 'song',
      url  : (t && t.url) || '',
      thumb: (t && t.thumb) || '',
      artists: (t && t.artists) || '',
      trk  : (t && t.trk != null) ? Number(t.trk) : 0
    })).filter(t => t.id)),
    songs   : (Array.isArray(p.songs) ? p.songs : []).map(s => ({
      id   : Number(s && s.id) || 0,
      name : (s && s.name) || '',
      type : (s && s.type) || 'song',
      url  : (s && s.url) || '',
      thumb: (s && s.thumb) || '',
      artists: (s && s.artists) || ''
    })).filter(s => s.id),
    cc      : p.cc || '',
    bd      : p.bd || '',
    ytm     : p.ytm || '',
    ins     : p.ins || '',
    tlg     : p.tlg || '',
    sources : (Array.isArray(p.sources) ? p.sources : []).map(s => ({
      id   : Number(s && s.id) || 0,
      url  : (s && s.url) || '',
      bit  : Number(s && s.bit) || 0,
      user : Number(s && s.user) || 0,
      act  : (s && s.act != null) ? Number(s.act) : 1,
      _key : s._key || ('s_' + Math.random().toString(36).slice(2, 8))
    })),
    dur     : Number(p.dur) || 0,
    raw     : null
  };
}

/* ---- small view helpers for the item zone ---- */

function zoneBarHtml(d, backLabel) {
  // Header: back + global name only (no secondary names, no id)
  // When nested (editStack), backLabel is "Back to {parent}" so Cancel / header share the same path.
  return '<div class="zone-bar">' +
    '<div class="zone-title">' +
      '<button class="btn btn-ghost btn-sm" type="button" data-zone-back>' +
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>' +
        esc(backLabel || 'Results') +
      '</button>' +
      '<b>' + esc(d.name || '(untitled)') + '</b>' +
    '</div>' +
  '</div>';
}

/**
 * Type switch for tables that share one DB table with two public types.
 * song table  -> Song | Podcast
 * video table -> Music video | Video
 * other tables -> single read-only badge
 */
function typeSwitchHtml(d) {
  let options = null;
  if (d.table === 'song')  options = ['song', 'podcast'];
  if (d.table === 'video') options = ['musicvideo', 'video'];

  if (options) {
    return '<div class="type-switch" id="typeSwitch">' +
      options.map(t =>
        '<button type="button" class="badge badge-' + t + (d.type === t ? ' is-on' : '') +
        '" data-type-to="' + t + '">' + esc(TYPE_LABEL[t]) + '</button>'
      ).join('') +
    '</div>';
  }
  return '<div class="type-switch">' +
    '<span class="badge badge-' + esc(d.type) + ' is-on">' + esc(TYPE_LABEL[d.type] || d.type) + '</span>' +
  '</div>';
}

/** Compact id chip + type switch on one line (Identity section). */
function idRowHtml(d) {
  const isNew = !(Number(d.id) > 0);
  const dbId = isNew ? '—' : (d.id - state.idOffset);
  return '<div class="estep-row">' +
    '<label>Item id</label>' +
    '<div class="id-line">' +
      '<div class="ro-field">' + (isNew ? 'New' : ('#' + esc(d.id))) +
        '<span class="ro-meta">' +
          (isNew ? 'not saved yet' : ('db ' + esc(dbId))) +
          ' · ' + esc(d.table) +
        '</span>' +
      '</div>' +
      typeSwitchHtml(d) +
    '</div>' +
  '</div>';
}

/** Empty draft for Create (id = 0). Same shape as buildDraft output. */
function emptyDraft(table, type) {
  const t = type || table;
  return {
    table   : table,
    type    : t,
    id      : 0,
    name    : '',
    names   : {},
    des     : '',
    lan     : '',
    gnr     : [],
    url     : '',
    rls     : '',
    act     : 1,
    tmb     : '',
    media   : [{ role: 'tmb', url: '' }],
    lyrics  : [],
    lyricEdit: null,
    artists : [],
    albums  : [],
    videos  : [],
    songs   : [],
    sources : [],
    tracks  : [],
    dur     : 0,
    cc      : '',
    bd      : '',
    ytm     : '',
    ins     : '',
    tlg     : '',
    raw     : null
  };
}


/* ==========================================================================
 *  Owner (user) hub — act9 only: search + create/edit
 * ========================================================================== */

/** Resolve a media key or URL to a previewable URL for the form. */
function resolveMediaPreviewUrl(raw) {
  const k = raw != null ? String(raw).trim() : '';
  if (!k) return '';
  if (/^https?:\/\//i.test(k) || /^data:image\//i.test(k)) return k;
  // Format-suffix keys written by IngestThumbFromSource:
  //   u11210717tsvg → file u11210717t.svg
  //   u11210717ipng → file u11210717i.png
  const fm = k.match(/^(.*?)(png|svg|webp|gif)$/i);
  if (fm) {
    const base = fm[1];
    const ext  = fm[2].toLowerCase();
    // plan-B bases end with a role letter (t/v/c/a/i) or gal index gN
    if (base && (/[tvcia]$/i.test(base) || /g\d+$/i.test(base))) {
      return state.thumbBase + encodeURIComponent(base) + '.' + ext;
    }
  }
  // classic jpg plan-B key
  if (/^[A-Za-z0-9_\-.]+$/.test(k)) {
    return state.thumbBase + encodeURIComponent(k) + '.jpg';
  }
  return k;
}



function emptyOwnerDraft(kind) {
  const isOwner = kind === 'owner';
  return {
    table: 'user',
    type: isOwner ? 'owner' : 'user',
    id: 0,
    name: '',          // u_name
    pass: '',          // plain password (create or change only)
    url: '',           // f_name (site url / first name)
    other: '',         // l_name
    email: '',
    tlg: '',           // id_tlg raw digits or empty
    ref: '',           // u_ref e.g. t123 / h11210700
    act: isOwner ? 7 : 1,
    join: 0,
    media: [{ role: 'tmb', url: '' }],
    des: '',
    raw: null
  };
}

function openOwnerHub() {
  if (!isAct9()) {
    setItemStatus('Only act9 administrators can manage owners.', 'error');
    return;
  }
  state.editStack = [];
  state.item = null;
  state.selected = { type: 'owner', id: 0, name: '' };
  renderOwnerHubSearch();
}

/** Search list only — same shell as other editors (zone-bar + one panel). */
function renderOwnerHubSearch() {
  const zone = $('#itemZone');
  if (!zone) return;
  zone.dataset.ownerBack = '';
  zone.innerHTML =
    '<div class="zone-bar">' +
      '<div class="zone-title">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-zone-back>' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>Results</button>' +
        '<b>Owners</b>' +
      '</div>' +
    '</div>' +
    '<div class="panel editor" id="ownerHub">' +
      '<section class="estep">' +
        '<h2><span class="num">1</span> Find owner</h2>' +
        '<div class="estep-row">' +
          '<label for="ownerFind">Search</label>' +
          '<div>' +
            '<div class="rel-attach-row">' +
              '<input class="input" id="ownerFind" type="search" placeholder="Name, email, public id …" spellcheck="false">' +
              '<button class="btn btn-accent btn-sm" type="button" id="ownerNewBtn">+ New</button>' +
            '</div>' +
            '<div class="pick-list" id="ownerFindPicks"></div>' +
            '<p class="hint" style="margin-top:6px">Click a result to edit. + New opens a blank form.</p>' +
          '</div>' +
        '</div>' +
      '</section>' +
    '</div>' +
    '<p class="status" id="itemStatus" role="status" aria-live="polite" hidden></p>';
  showItemZone();
  bindOwnerHub();
}

function bindOwnerHub() {
  const find = $('#ownerFind');
  const picks = $('#ownerFindPicks');
  const newBtn = $('#ownerNewBtn');
  let timer = null;

  if (find) {
    find.addEventListener('input', () => {
      clearTimeout(timer);
      const q = find.value.trim();
      if (q.length < 1) {
        if (picks) picks.innerHTML = '';
        return;
      }
      timer = setTimeout(async () => {
        if (picks) picks.innerHTML = '<div class="hint">Searching …</div>';
        const rows = await searchOwners(q, 0);
        if (!picks || find.value.trim() !== q) return;
        if (!rows.length) {
          picks.innerHTML = '<div class="hint">No user match.</div>';
          return;
        }
        picks.innerHTML = rows.map(o =>
          '<button class="pick" type="button" data-owner-edit="' + esc(o.id) + '">' +
            '<b>' + esc(o.name || ('#' + o.id)) + '</b>' +
            '<span class="rid">act' + esc(o.act || 0) + ' · #' + esc(o.id) + '</span></button>'
        ).join('');
      }, 220);
    });
  }

  if (picks) {
    picks.addEventListener('click', async (ev) => {
      const btn = ev.target.closest('[data-owner-edit]');
      if (!btn) return;
      const id = Number(btn.getAttribute('data-owner-edit')) || 0;
      if (!id) return;
      await openOwnerEdit(id);
    });
  }

  if (newBtn) {
    newBtn.addEventListener('click', () => {
      state.item = emptyOwnerDraft('owner');
      renderOwnerForm();
    });
  }
}


/** Unix join timestamp → readable local date-time for the form header. */
function formatJoinDate(join) {
  const n = Number(join);
  if (!n || n <= 0) return '';
  // seconds vs milliseconds
  const ms = n < 1e12 ? n * 1000 : n;
  const d = new Date(ms);
  if (isNaN(d.getTime())) return '';
  try {
    return d.toLocaleString(undefined, {
      year: 'numeric', month: 'short', day: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });
  } catch (e) {
    return d.toISOString().replace('T', ' ').slice(0, 16);
  }
}

async function openOwnerEdit(pubId) {
  setItemStatus('Loading owner …', 'info');
  try {
    // Full fields from get_user (u_data.tmb / ico / photo)
    const ans = await request('save', null, { a: 'get_user', id: pubId });
    let card = (ans && ans.user) ? ans.user : null;

    // Fallback: public OwnerCard via ?_qjs=own
    if (!card) {
      const data = await queryGet({ qjs: 'own', ids: String(pubId) });
      const list = (data && data.owners) ? data.owners : [];
      const o = list[0];
      if (!o) {
        setItemStatus('Owner not found.', 'error');
        return;
      }
      card = {
        id: o.id,
        name: o.name,
        act: o.act != null ? o.act : 7,
        url: '',
        other: '',
        email: '',
        tlg: '',
        ref: '',
        tmb: (o.meta && o.meta.tmb) || o.tmb || '',
        ico: (o.meta && o.meta.ico) || o.ico || '',
        photo: (o.meta && o.meta.photo) || o.photo || '',
        des: (o.meta && o.meta.des) || ''
      };
    }

    const tmb = card.tmb || (card.meta && card.meta.tmb) || '';
    const ico = card.ico || (card.meta && card.meta.ico) || '';
    const photo = card.photo || (card.meta && card.meta.photo) || '';

    state.item = {
      table: 'user',
      type: (Number(card.act) >= 7) ? 'owner' : 'user',
      id: Number(card.id) || pubId,
      name: card.name || card.u_name || '',
      pass: '',
      url: card.url || card.f_name || '',
      other: card.other || card.l_name || '',
      email: card.email || card.u_email || '',
      tlg: card.tlg != null ? String(card.tlg) : (card.id_tlg ? String(card.id_tlg) : ''),
      ref: card.ref || card.u_ref || '',
      act: card.act != null ? Number(card.act) : 1,
      join: card.join || (card.meta && card.meta.join) || 0,
      media: (function () {
        const rows = [];
        if (tmb) rows.push({ role: 'tmb', url: resolveMediaPreviewUrl(tmb) });
        if (ico) rows.push({ role: 'ico', url: resolveMediaPreviewUrl(ico) });
        if (photo) rows.push({ role: 'photo', url: resolveMediaPreviewUrl(photo) });
        if (!rows.length) rows.push({ role: 'tmb', url: '' });
        return rows;
      })(),
      des: card.des || '',
      raw: card
    };
    renderOwnerForm();
    setItemStatus('');
  } catch (err) {
    setItemStatus('Failed to load owner.', 'error');
  }
}

function renderOwnerForm() {
  const d = state.item;
  if (!d || d.table !== 'user') return;
  const zone = $('#itemZone');
  if (!zone) return;
  const isNew = !(Number(d.id) > 0);
  const kind = d.type === 'owner' ? 'owner' : 'user';
  const title = d.name || d.email || (isNew ? 'New owner' : ('#' + d.id));

  zone.innerHTML =
    '<div class="zone-bar">' +
      '<div class="zone-title">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-owner-cancel>' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>Owners</button>' +
        '<b>' + esc(title) + '</b>' +
      '</div>' +
    '</div>' +
    '<form class="panel editor" id="ownerForm" autocomplete="off" novalidate>' +
      '<section class="estep">' +
        '<h2><span class="num">1</span> Identity</h2>' +
        '<div class="estep-row">' +
          '<label>Item id</label>' +
          '<div class="id-line">' +
            '<div class="ro-field">' + (isNew ? 'New' : ('#' + esc(d.id))) +
              '<span class="ro-meta">' + (isNew
                ? 'not saved yet'
                : (formatJoinDate(d.join) || 'joined —')) + '</span></div>' +
            '<div class="type-switch" id="ownerKindSwitch" role="group" aria-label="Account kind">' +
              '<button type="button" class="badge badge-user' + (kind === 'user' ? ' is-on' : '') +
                '" data-owner-kind="user">User</button>' +
              '<button type="button" class="badge badge-owner' + (kind === 'owner' ? ' is-on' : '') +
                '" data-owner-kind="owner">Owner</button>' +
            '</div>' +
          '</div>' +
        '</div>' +
        '<div class="estep-row"><label for="oName">Username <span class="hint">(u_name)</span></label>' +
          '<input class="input" id="oName" type="text" value="' + esc(d.name) +
            '" placeholder="login name — optional" spellcheck="false"></div>' +
        '<div class="estep-row"><label for="oPass">Password <span class="hint">(u_pass)</span></label>' +
          '<input class="input" id="oPass" type="password" value="" placeholder="' +
            (isNew ? 'min 6 characters' : 'leave blank to keep current') +
            '" autocomplete="new-password"></div>' +
        '<div class="estep-row"><label for="oEmail">Email <span class="hint">(u_email)</span></label>' +
          '<input class="input" id="oEmail" type="text" value="' + esc(d.email) +
            '" placeholder="optional — empty is allowed" spellcheck="false"></div>' +
        '<div class="estep-row"><label for="oUrl">' +
          (kind === 'owner' ? 'Site URL' : 'First name') +
          ' <span class="hint">(f_name)</span></label>' +
          '<input class="input" id="oUrl" type="text" value="' + esc(d.url) +
            '" placeholder="' + (kind === 'owner' ? 'https://…' : 'first name') +
            '" spellcheck="false"></div>' +
        '<div class="estep-row"><label for="oOther">' +
          (kind === 'owner' ? 'Display label' : 'Last name') +
          ' <span class="hint">(l_name)</span></label>' +
          '<input class="input" id="oOther" type="text" value="' + esc(d.other) +
            '" placeholder="' + (kind === 'owner' ? 'public label' : 'last name') +
            '" spellcheck="false"></div>' +
        '<div class="estep-row"><label for="oAct">Status <span class="hint">(act 0–9)</span></label>' +
          '<div>' +
            '<input class="input" id="oAct" type="number" min="0" max="9" step="1" value="' + esc(d.act) + '">' +
            '<p class="hint" style="margin-top:6px">1 = user · 7+ = owner / partner · 9 = admin</p>' +
          '</div></div>' +
        '<div class="estep-row"><label for="oTlg">Telegram <span class="hint">(id_tlg)</span></label>' +
          '<input class="input" id="oTlg" type="text" inputmode="numeric" value="' + esc(d.tlg || '') +
            '" placeholder="digits only — empty = none" spellcheck="false"></div>' +
        '<div class="estep-row"><label for="oRef">Referral <span class="hint">(u_ref)</span></label>' +
          '<div>' +
            '<input class="input" id="oRef" type="text" value="' + esc(d.ref || '') +
              '" placeholder="t… Telegram · h… Hamrahang · other" spellcheck="false">' +
            '<p class="hint" style="margin-top:6px">Full admin profile — all fields editable for any act.</p>' +
          '</div></div>' +
      '</section>' +
      '<section class="estep">' +
        '<h2><span class="num">2</span> Thumb &amp; cover</h2>' +
        mediaSectionHtml(d) +
      '</section>' +
      '<div class="editor-foot">' +
        '<button class="btn btn-ghost" type="button" data-owner-cancel>Cancel</button>' +
        '<button class="btn btn-primary" id="ownerSubmit" type="submit">' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>' +
          (isNew ? 'Create' : 'Submit') + '</button>' +
      '</div>' +
    '</form>' +
    '<p class="status" id="itemStatus" role="status" aria-live="polite" hidden></p>';

  showItemZone();
  bindMediaEditor();
  const form = $('#ownerForm');
  if (form) form.addEventListener('submit', submitOwnerForm);
  form.querySelectorAll('[data-owner-kind]').forEach(btn => {
    btn.addEventListener('click', () => {
      collectOwnerDraft();
      state.item.type = btn.getAttribute('data-owner-kind');
      if (state.item.type === 'owner' && Number(state.item.act) < 7) state.item.act = 7;
      if (state.item.type === 'user' && Number(state.item.act) >= 7) state.item.act = 1;
      renderOwnerForm();
    });
  });
  $$('[data-owner-cancel]').forEach(btn => {
    btn.addEventListener('click', () => {
      state.item = null;
      renderOwnerHubSearch();
    });
  });
}

function collectOwnerDraft() {
  if (!state.item || state.item.table !== 'user') return state.item;
  const d = state.item;
  d.name = ($('#oName') && $('#oName').value.trim()) || '';
  d.pass = ($('#oPass') && $('#oPass').value) || '';
  d.email = ($('#oEmail') && $('#oEmail').value.trim()) || '';
  d.url = ($('#oUrl') && $('#oUrl').value.trim()) || '';
  d.other = ($('#oOther') && $('#oOther').value.trim()) || '';
  d.act = $('#oAct') ? Number($('#oAct').value) || 0 : d.act;
  d.tlg = ($('#oTlg') && $('#oTlg').value.trim()) || '';
  d.ref = ($('#oRef') && $('#oRef').value.trim()) || '';
  // media via collectDraft-like
  const media = [];
  $$('#mediaList [data-media-row]').forEach(row => {
    const role = (row.querySelector('[data-media-role]') || {}).value || 'tmb';
    let url = String((row.querySelector('[data-media-url]') || {}).value || '').trim();
    // Collapse our thumb host URLs back to id_img keys (server expects keys for host files)
    if (role !== 'photo' && url) {
      const base = String(state.thumbBase || '').replace(/\/?$/, '/');
      if (base && url.indexOf(base) === 0) {
        const file = decodeURIComponent(url.slice(base.length).split('?')[0]);
        const m = file.match(/^(.+)\.(jpg|jpeg|png|svg|webp|gif)$/i);
        if (m) {
          const stem = m[1];
          const ext  = m[2].toLowerCase() === 'jpeg' ? 'jpg' : m[2].toLowerCase();
          url = (ext === 'jpg') ? stem : (stem + ext);
        }
      }
    }
    media.push({ role: role, url: url });
  });
  if (media.length) d.media = media;
  return d;
}

async function submitOwnerForm(event) {
  event.preventDefault();
  if (state.busy) return;
  if (!isAct9()) {
    setItemStatus('Only act9 can save owners.', 'error');
    return;
  }
  const d = collectOwnerDraft();
  const isCreate = !(Number(d.id) > 0);
  if (isCreate && !d.name && !d.email && !d.tlg) {
    setItemStatus('Need at least username, email, or Telegram id.', 'error');
    return;
  }
  if (isCreate && (!d.pass || d.pass.length < 6)) {
    setItemStatus('Password is required (min 6 characters) for new accounts.', 'error');
    return;
  }
  if (!isCreate && d.pass && d.pass.length > 0 && d.pass.length < 6) {
    setItemStatus('Password must be at least 6 characters.', 'error');
    return;
  }

  const mediaForSave = (typeof collapseMediaRows === 'function')
    ? collapseMediaRows(d.media || [])
    : (d.media || []);

  const payload = {
    a: isCreate ? 'create_item' : 'update_item',
    table: 'user',
    type: d.type || 'user',
    id: isCreate ? 0 : d.id,
    name: d.name,
    pass: d.pass || undefined,
    email: d.email,
    url: d.url,
    other: d.other,
    act: d.act,
    tlg: d.tlg === '' ? 0 : d.tlg,
    ref: d.ref,
    media: mediaForSave
  };

  state.busy = true;
  const btn = $('#ownerSubmit');
  if (btn) { btn.setAttribute('aria-busy', 'true'); btn.textContent = isCreate ? 'Creating …' : 'Saving …'; }
  setItemStatus(isCreate ? 'Creating owner …' : 'Saving owner …', 'info');

  const res = await request('save', null, payload);
  state.busy = false;
  if (btn) {
    btn.removeAttribute('aria-busy');
    btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>' + (isCreate ? 'Create' : 'Submit');
  }

  if (!res || res.ok !== true) {
    setItemStatus(errorText(res), 'error');
    return;
  }

  if (isCreate && res.id) {
    d.id = Number(res.id);
    state.item.id = d.id;
  }
  if (res.user || (res.changes && res.changes.name)) {
    putOwners([{
      id: d.id,
      name: d.name || d.email || ('User #' + d.id),
      act: d.act,
      photo: ''
    }]);
  }
  state.item = null;
  renderOwnerHubSearch();
  setItemStatus(
    (isCreate ? 'Owner created #' : 'Owner saved #') + (res.id || d.id) +
    ' · ' + (d.name || d.email || 'user'),
    'ok'
  );
}

/**
 * Open the editor in create mode.
 * @param {string} type  song|podcast|album|artist|video|musicvideo
 * @param {{ nested?: boolean }} [opts]
 */

/* ==========================================================================
 *  System playlist hub — act9 only (pl_act = 9). User playlists never appear.
 * ========================================================================== */
function emptyPlaylistDraft() {
  return {
    table: 'playlist',
    type: 'playlist',
    id: 0,
    name: '',
    names: {},
    des: '',
    act: 9,           // system
    url: '',
    media: [{ role: 'tmb', url: '' }],
    items: [],        // mixed song/video/podcast/musicvideo ordered
    owner: 0,         // system → 0 / Hamrahang
    raw: null
  };
}

/** Merge songs[] + videos[] from PLAYLIST() into one ordered items list. */
function playlistItemsFromCard(full) {
  const bag = [];
  const push = (it, fallbackType) => {
    if (!it) return;
    const id = Number(it.id) || 0;
    if (!id) return;
    const type = (it.meta && it.meta.type) ? String(it.meta.type)
      : (it.type ? String(it.type) : fallbackType);
    const pos = (it.position != null) ? Number(it.position)
      : (it.trk != null ? Number(it.trk) : 0);
    bag.push({
      id: id,
      name: it.name || '',
      nameAlt: namesAltFromItem(it),
      type: type || fallbackType,
      thumb: (it.meta && it.meta.tmb) ? String(it.meta.tmb) : (it.thumb ? String(it.thumb) : ''),
      artists: Array.isArray(it.artists)
        ? it.artists.map(a => (a && a.name) || '').filter(Boolean).join(', ')
        : '',
      pos: isNaN(pos) ? 0 : pos
    });
  };
  (Array.isArray(full.songs) ? full.songs : []).forEach(s => push(s, 'song'));
  (Array.isArray(full.videos) ? full.videos : []).forEach(v => push(v, 'musicvideo'));
  bag.sort((a, b) => (a.pos || 0) - (b.pos || 0));
  // renumber 1..n for UI
  return bag.map((x, i) => Object.assign({}, x, { pos: i + 1 }));
}

function openPlaylistHub() {
  if (!isAct9()) {
    setItemStatus('Only act9 can manage system playlists.', 'error');
    return;
  }
  state.editStack = [];
  state.item = null;
  state.selected = { type: 'playlist', id: 0, name: '' };
  renderPlaylistHubSearch();
}

function renderPlaylistHubSearch() {
  const zone = $('#itemZone');
  if (!zone) return;
  zone.innerHTML =
    '<div class="zone-bar">' +
      '<div class="zone-title">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-zone-back>' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>Results</button>' +
        '<b>System playlists</b>' +
      '</div>' +
    '</div>' +
    '<div class="panel editor" id="plHub">' +
      '<section class="estep">' +
        '<h2><span class="num">1</span> Find playlist</h2>' +
        '<div class="estep-row">' +
          '<label for="plFind">Search</label>' +
          '<div>' +
            '<div class="rel-attach-row">' +
              '<input class="input" id="plFind" type="search" placeholder="Name or public id …" spellcheck="false">' +
              '<button class="btn btn-accent btn-sm" type="button" id="plNewBtn">+ New</button>' +
            '</div>' +
            '<div class="pick-list" id="plFindPicks"></div>' +
            '<p class="hint" style="margin-top:6px">Only system playlists (pl_act = 9). User playlists are hidden.</p>' +
          '</div>' +
        '</div>' +
      '</section>' +
    '</div>' +
    '<p class="status" id="itemStatus" role="status" aria-live="polite" hidden></p>';
  showItemZone();
  bindPlaylistHub();
}

function bindPlaylistHub() {
  const find = $('#plFind');
  const picks = $('#plFindPicks');
  const newBtn = $('#plNewBtn');
  let timer = null;

  if (find) {
    find.addEventListener('input', () => {
      clearTimeout(timer);
      const q = find.value.trim();
      if (q.length < 1) {
        if (picks) picks.innerHTML = '';
        return;
      }
      timer = setTimeout(async () => {
        if (picks) picks.innerHTML = '<div class="hint">Searching …</div>';
        let rows = [];
        try {
          if (/^\d+$/.test(q)) {
            rows = await searchById(q, 'playlist');
          }
          const textRows = await searchByText(q, 'playlist');
          rows = mergeRows([rows, textRows]);
        } catch (e) {
          rows = [];
        }
        // Keep system-only when flag present; if absent keep all (server filter later)
        rows = rows.filter(r => {
          if (!r || r.type !== 'playlist') return false;
          if (r.system === true) return true;
          if (r.meta && r.meta.system === true) return true;
          if (r.public === true && r.system === undefined) return true; // interim
          return r.system !== false;
        });
        if (!picks || find.value.trim() !== q) return;
        if (!rows.length) {
          picks.innerHTML = '<div class="hint">No system playlist match.</div>';
          return;
        }
        picks.innerHTML = rows.map(r =>
          '<button class="pick" type="button" data-pl-edit="' + esc(r.id) + '">' +
            (r.thumb ? thumbHtml(r) : '') +
            '<b>' + esc(r.name || ('#' + r.id)) + '</b>' +
            '<span class="rid">#' + esc(r.id) +
              (r.owner ? (' · owner #' + esc(r.owner)) : ' · system') +
            '</span></button>'
        ).join('');
      }, 220);
    });
  }

  if (picks) {
    picks.addEventListener('click', async (ev) => {
      const btn = ev.target.closest('[data-pl-edit]');
      if (!btn) return;
      const id = Number(btn.getAttribute('data-pl-edit')) || 0;
      if (!id) return;
      await openPlaylistEdit(id);
    });
  }

  if (newBtn) {
    newBtn.addEventListener('click', () => {
      state.item = emptyPlaylistDraft();
      renderPlaylistForm();
    });
  }
}

async function openPlaylistEdit(pubId) {
  setItemStatus('Loading playlist …', 'info');
  try {
    const data = await queryGet({ qjs: 'ep', p: 'playlist', q1: String(pubId) });
    const full = (data && data.playlist) ? data.playlist : data;
    if (!full || !full.id) {
      setItemStatus('Playlist not found.', 'error');
      return;
    }
    // system only for this admin panel
    if (full.system === false) {
      setItemStatus('This is a user playlist — not editable here.', 'warn');
      return;
    }
    state.item = {
      table: 'playlist',
      type: 'playlist',
      id: Number(full.id) || pubId,
      name: full.name || '',
      names: (function () {
        const n = {};
        if (full.names && typeof full.names === 'object') {
          Object.keys(full.names).forEach(c => { if (full.names[c]) n[c] = String(full.names[c]); });
        }
        if (full.name_fa && !n.fa) n.fa = String(full.name_fa);
        return n;
      })(),
      des: (full.meta && full.meta.des) ? String(full.meta.des) : '',
      act: 9,
      url: full.url || '',
      media: mediaFromMeta(full.meta || {}),
      items: playlistItemsFromCard(full),
      owner: full.owner
        ? (typeof full.owner === 'object' ? Number(full.owner.id) || 0 : Number(full.owner) || 0)
        : 0,
      raw: full
    };
    renderPlaylistForm();
    setItemStatus('');
  } catch (e) {
    setItemStatus('Failed to load playlist.', 'error');
  }
}

function plItemRowHtml(t, index) {
  const n = index + 1;
  const type = t.type || 'song';
  const typeBadge = '<span class="badge badge-' + esc(type) + '">' + esc(TYPE_LABEL[type] || type) + '</span>';
  const nameAlt = t.nameAlt ? (' <span class="name-alt">' + esc(t.nameAlt) + '</span>') : '';
  return '<div class="trk-row" data-pl-item data-pl-id="' + esc(t.id) + '" data-pl-type="' + esc(type) + '">' +
    '<span class="trk-num">' + n + '.</span>' +
    (t.thumb ? thumbHtml({ name: t.name, thumb: t.thumb }) : '<span class="thumb is-empty"></span>') +
    '<div class="trk-meta">' +
      typeBadge + ' ' + esc(t.name || ('#' + t.id)) + nameAlt +
      (t.artists ? (' <span class="hint">' + esc(t.artists) + '</span>') : '') +
    '</div>' +
    '<div class="trk-actions">' +
      '<button class="btn btn-ghost btn-sm" type="button" data-pl-up title="Move up">↑</button>' +
      '<button class="btn btn-ghost btn-sm" type="button" data-pl-down title="Move down">↓</button>' +
      '<button class="btn btn-ghost btn-sm" type="button" data-pl-del title="Remove">&times;</button>' +
    '</div>' +
  '</div>';
}

function playlistItemsSectionHtml(d) {
  const rows = Array.isArray(d.items) ? d.items : [];
  let html = '<div id="plItemSection">' +
    '<div class="trk-list" id="plItemList">';
  if (!rows.length) {
    html += '<div class="hint" id="plItemEmpty">No items yet — attach songs or videos.</div>';
  } else {
    html += rows.map((t, i) => plItemRowHtml(t, i)).join('');
  }
  html += '</div>' +
    '<div class="field" style="margin-top:12px">' +
      '<label for="plItemSearch">Attach song or video</label>' +
      '<div class="rel-attach-row">' +
        '<input class="input" id="plItemSearch" type="search" placeholder="Name or public id …" spellcheck="false">' +
      '</div>' +
      '<div class="pick-list" id="plItemPicks"></div>' +
      '<div class="hint" style="margin-top:6px">Songs, podcasts, music videos and videos. Order is position on submit (1…n).</div>' +
    '</div></div>';
  return html;
}

function collectPlaylistDraft() {
  if (!state.item || state.item.table !== 'playlist') return state.item;
  const d = state.item;
  d.name = ($('#fName') && $('#fName').value.trim()) || d.name || '';
  d.des = ($('#fDes') && $('#fDes').value) || '';
  // secondary names — same as collectDraft
  const names = {};
  $$('#nameStack [data-locale-row]').forEach(row => {
    const code = (row.querySelector('[data-lang]') || {}).value || '';
    const valEl = row.querySelector('[data-name-val]');
    const val = valEl ? valEl.value.trim() : '';
    if (code && val) names[code] = val;
  });
  d.names = names;
  // media
  const media = [];
  $$('#mediaList [data-media-row]').forEach(row => {
    const role = (row.querySelector('[data-media-role]') || {}).value || 'tmb';
    const url = (row.querySelector('[data-media-url]') || {}).value || '';
    media.push({ role: role, url: String(url).trim() });
  });
  if (media.length) d.media = media;
  // items order from DOM
  const items = [];
  $$('#plItemList [data-pl-item]').forEach((row, i) => {
    const id = Number(row.getAttribute('data-pl-id')) || 0;
    const type = row.getAttribute('data-pl-type') || 'song';
    if (!id) return;
    const prev = (d.items || []).find(x => x.id === id && x.type === type)
      || (d.items || []).find(x => x.id === id);
    items.push({
      id: id,
      type: type,
      name: prev ? prev.name : '',
      nameAlt: prev ? prev.nameAlt : '',
      thumb: prev ? prev.thumb : '',
      artists: prev ? prev.artists : '',
      pos: i + 1
    });
  });
  d.items = items;
  return d;
}

function renderPlaylistForm() {
  const d = state.item;
  if (!d || d.table !== 'playlist') return;
  const zone = $('#itemZone');
  if (!zone) return;
  const isNew = !(Number(d.id) > 0);
  const title = d.name || (isNew ? 'New system playlist' : ('#' + d.id));

  zone.innerHTML =
    '<div class="zone-bar">' +
      '<div class="zone-title">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-pl-cancel>' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>Playlists</button>' +
        '<b>' + esc(title) + '</b>' +
        '<span class="badge badge-playlist is-on">System</span>' +
      '</div>' +
    '</div>' +
    '<form class="panel editor" id="plForm" autocomplete="off" novalidate>' +
      '<section class="estep">' +
        '<h2><span class="num">1</span> Identity</h2>' +
        '<div class="estep-row">' +
          '<label>Item id</label>' +
          '<div class="id-line">' +
            '<div class="ro-field">' + (isNew ? 'New' : ('#' + esc(d.id))) +
              '<span class="ro-meta">' + (isNew ? 'not saved yet' : 'playlist') + ' · system</span></div>' +
          '</div>' +
        '</div>' +
        '<div class="estep-row"><label>Name</label>' +
          '<div>' + nameLinesHtml(d) + '</div></div>' +
        '<div class="estep-row"><label for="fDes">Description</label>' +
          '<textarea class="input" id="fDes" rows="3" placeholder="optional">' + esc(d.des || '') + '</textarea></div>' +
        '<div class="estep-row"><label>Owner</label>' +
          '<div class="ro-field">Hamrahang<span class="ro-meta">system · #0</span></div></div>' +
      '</section>' +
      '<section class="estep">' +
        '<h2><span class="num">2</span> Thumb &amp; cover</h2>' +
        mediaSectionHtml(d) +
      '</section>' +
      '<section class="estep">' +
        '<h2><span class="num">3</span> Items</h2>' +
        playlistItemsSectionHtml(d) +
      '</section>' +
      '<div class="editor-foot">' +
        '<button class="btn btn-ghost" type="button" data-pl-cancel>Cancel</button>' +
        '<button class="btn btn-primary" id="plSubmit" type="submit">' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>' +
          (isNew ? 'Create' : 'Submit') + '</button>' +
      '</div>' +
    '</form>' +
    '<p class="status" id="itemStatus" role="status" aria-live="polite" hidden></p>';

  showItemZone();
  bindMediaEditor();
  bindPlaylistItemsEditor();
  // localized names (+ button) — same as item editor
  const nameAdd = $('#nameAdd');
  if (nameAdd) {
    nameAdd.addEventListener('click', () => {
      const used = $$('#nameStack [data-locale-row] select').map(s => s.value);
      let next = 'fa';
      if (used.indexOf('fa') >= 0) next = (used.indexOf('en') >= 0) ? 'ar' : 'en';
      $('#nameStack').insertAdjacentHTML('beforeend', nameLineHtml(next, ''));
      const rows = $$('#nameStack [data-locale-row]');
      const last = rows[rows.length - 1];
      if (last) { const inp = last.querySelector('[data-name-val]'); if (inp) inp.focus(); }
    });
  }
  const form = $('#plForm');
  if (form) form.addEventListener('submit', submitPlaylistForm);
  $$('[data-pl-cancel]').forEach(btn => {
    btn.addEventListener('click', () => {
      state.item = null;
      renderPlaylistHubSearch();
    });
  });
  const nameEl = $('#fName');
  if (nameEl) {
    nameEl.addEventListener('input', () => {
      if (!nameEl.value.trim()) nameEl.classList.add('is-invalid');
      else nameEl.classList.remove('is-invalid');
    });
  }
}

function bindPlaylistItemsEditor() {
  const list = $('#plItemList');
  if (!list) return;

  list.addEventListener('click', (ev) => {
    const row = ev.target.closest('[data-pl-item]');
    if (!row || !state.item) return;
    collectPlaylistDraft();
    const id = Number(row.getAttribute('data-pl-id'));
    const type = row.getAttribute('data-pl-type') || 'song';
    const items = state.item.items || [];
    const idx = items.findIndex(x => x.id === id && x.type === type);

    if (ev.target.closest('[data-pl-del]')) {
      if (idx >= 0) items.splice(idx, 1);
      state.item.items = items.map((x, i) => Object.assign({}, x, { pos: i + 1 }));
      renderPlaylistForm();
      return;
    }
    if (ev.target.closest('[data-pl-up]') && idx > 0) {
      const tmp = items[idx - 1];
      items[idx - 1] = items[idx];
      items[idx] = tmp;
      state.item.items = items.map((x, i) => Object.assign({}, x, { pos: i + 1 }));
      renderPlaylistForm();
      return;
    }
    if (ev.target.closest('[data-pl-down]') && idx >= 0 && idx < items.length - 1) {
      const tmp = items[idx + 1];
      items[idx + 1] = items[idx];
      items[idx] = tmp;
      state.item.items = items.map((x, i) => Object.assign({}, x, { pos: i + 1 }));
      renderPlaylistForm();
      return;
    }
  });

  // attach search (song + video)
  const inp = $('#plItemSearch');
  const picks = $('#plItemPicks');
  let timer = null;
  if (inp) {
    inp.addEventListener('input', () => {
      clearTimeout(timer);
      const q = inp.value.trim();
      if (q.length < 1) {
        if (picks) picks.innerHTML = '';
        return;
      }
      timer = setTimeout(async () => {
        if (picks) picks.innerHTML = '<div class="hint">Searching …</div>';
        let rows = [];
        try {
          const jobs = [];
          if (/^\d+$/.test(q)) {
            jobs.push(searchById(q, 'all'));
          }
          jobs.push(searchByText(q, 'all'));
          const groups = await Promise.all(jobs);
          rows = mergeRows(groups).filter(r =>
            r && (r.type === 'song' || r.type === 'podcast' || r.type === 'video' || r.type === 'musicvideo')
          );
        } catch (e) { rows = []; }
        // exclude already attached
        const have = {};
        (state.item.items || []).forEach(x => { have[x.type + ':' + x.id] = true; });
        rows = rows.filter(r => !have[r.type + ':' + r.id]);
        if (!picks || inp.value.trim() !== q) return;
        if (!rows.length) {
          picks.innerHTML = '<div class="hint">No match.</div>';
          return;
        }
        picks.innerHTML = rows.slice(0, 20).map(r =>
          '<button class="pick" type="button" data-pl-attach="' + esc(r.id) + '" ' +
            'data-pl-atype="' + esc(r.type) + '" data-name="' + esc(r.name || '') + '" ' +
            'data-thumb="' + esc(r.thumb || '') + '">' +
            (r.thumb ? thumbHtml(r) : '') +
            '<span class="badge badge-' + esc(r.type) + '">' + esc(TYPE_LABEL[r.type] || r.type) + '</span> ' +
            '<b>' + esc(r.name || ('#' + r.id)) + '</b>' +
            '<span class="rid">#' + esc(r.id) + '</span></button>'
        ).join('');
      }, 220);
    });
  }
  if (picks) {
    picks.addEventListener('click', (ev) => {
      const btn = ev.target.closest('[data-pl-attach]');
      if (!btn || !state.item) return;
      collectPlaylistDraft();
      const id = Number(btn.getAttribute('data-pl-attach')) || 0;
      const type = btn.getAttribute('data-pl-atype') || 'song';
      if (!id) return;
      if (!state.item.items) state.item.items = [];
      if (state.item.items.some(x => x.id === id && x.type === type)) return;
      state.item.items.push({
        id: id,
        type: type,
        name: btn.getAttribute('data-name') || ('#' + id),
        nameAlt: '',
        thumb: btn.getAttribute('data-thumb') || '',
        artists: '',
        pos: state.item.items.length + 1
      });
      renderPlaylistForm();
    });
  }
}

async function submitPlaylistForm(event) {
  event.preventDefault();
  if (state.busy) return;
  if (!isAct9()) {
    setItemStatus('Only act9 can save system playlists.', 'error');
    return;
  }
  const d = collectPlaylistDraft();
  if (!d.name) {
    setItemStatus('Playlist name is required.', 'error');
    const el = $('#fName');
    if (el) { el.classList.add('is-invalid'); el.focus(); }
    return;
  }
  const isCreate = !(Number(d.id) > 0);
  const mediaForSave = (typeof collapseMediaRows === 'function')
    ? collapseMediaRows(d.media || [])
    : (d.media || []);

  const payload = {
    a: isCreate ? 'create_item' : 'update_item',
    table: 'playlist',
    type: 'playlist',
    id: isCreate ? 0 : d.id,
    name: d.name,
    names: d.names || {},
    des: d.des || '',
    act: 9,
    media: mediaForSave,
    items: (d.items || []).map((x, i) => ({
      id: x.id,
      type: x.type || 'song',
      pos: i + 1
    })),
    system: true
  };

  state.busy = true;
  const btn = $('#plSubmit');
  if (btn) { btn.setAttribute('aria-busy', 'true'); btn.textContent = isCreate ? 'Creating …' : 'Saving …'; }
  setItemStatus(isCreate ? 'Creating system playlist …' : 'Saving playlist …', 'info');

  // UI-only for now if PHP not ready — still try save
  const res = await request('save', null, payload);
  state.busy = false;
  if (btn) {
    btn.removeAttribute('aria-busy');
    btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>' +
      (isCreate ? 'Create' : 'Submit');
  }

  if (!res || res.ok !== true) {
    // Allow UI review: show staged message without hard fail if not implemented
    if (res && res.error === 'create_not_implemented') {
      setItemStatus('UI ready — PHP create/update playlist not on server yet.', 'warn');
      return;
    }
    setItemStatus(errorText(res), 'error');
    return;
  }

  if (isCreate && res.id) d.id = Number(res.id);
  state.item = null;
  renderPlaylistHubSearch();
  setItemStatus((isCreate ? 'Playlist created #' : 'Playlist saved #') + (res.id || d.id), 'ok');
}

function openCreate(type, opts) {
  const nested = !!(opts && opts.nested);
  if (!nested) state.editStack = [];
  else if (state.item) pushEditStack();

  const table = TYPE_TABLE[type] || type;
  const resolvedType = (type === 'podcast' || type === 'musicvideo' || type === 'video')
    ? type
    : (table === 'song' ? 'song' : table === 'video' ? 'musicvideo' : table);

  state.item = emptyDraft(table, resolvedType);
  state.selected = { type: resolvedType, id: 0, name: '' };
  renderEditor();
  const nameEl = $('#fName');
  if (nameEl) nameEl.focus();
}

function nameLinesHtml(d) {
  let html = '<div class="name-line">' +
    '<span class="globe" title="Global / default name">' + GLOBE_SVG + '</span>' +
    '<input class="input" id="fName" type="text" value="' + esc(d.name) + '" ' +
      'placeholder="Global name (stored as song_name)" spellcheck="false">' +
    '<button class="btn btn-ghost icon-btn is-add" type="button" id="nameAdd" ' +
      'title="Add a localized name" aria-label="Add a localized name">+</button>' +
  '</div>';
  Object.keys(d.names).forEach(code => { html += nameLineHtml(code, d.names[code]); });
  return '<div class="name-stack" id="nameStack">' + html + '</div>';
}

function nameLineHtml(code, value) {
  return '<div class="name-line" data-locale-row>' +
    '<select class="select lang-sel" data-lang aria-label="Language">' + langOptions(code) + '</select>' +
    '<input class="input" type="text" data-name-val value="' + esc(value === undefined ? '' : value) + '" ' +
      'placeholder="Localized name" spellcheck="false">' +
    '<button class="btn btn-ghost icon-btn is-del" type="button" data-locale-del ' +
      'title="Remove this name" aria-label="Remove this name">&minus;</button>' +
  '</div>';
}

/**
 * Year/month/day selects.
 * @param {string} rls  YYYY or YYYY-MM-DD
 * @param {{ minYear?: number }} [opts]  birth dates need ~1900; release stays ~1950
 */
function tripleHtml(rls, opts) {
  const s = String(rls || '');
  const y = s ? s.slice(0, 4) : '';
  const m = s.length >= 7 ? s.slice(5, 7) : '';
  const dd = s.length >= 10 ? s.slice(8, 10) : '';
  const now = new Date().getFullYear();
  let minY = (opts && opts.minYear) ? Number(opts.minYear) : 1950;
  if (!isFinite(minY) || minY < 1800) minY = 1950;
  // always include the current value even if outside the default range
  const yNum = y ? parseInt(y, 10) : 0;
  if (yNum > 0 && yNum < minY) minY = yNum;
  const maxY = Math.max(now + 1, yNum || 0);
  let years = '<option value="">—</option>';
  for (let i = maxY; i >= minY; i--) {
    years += '<option value="' + i + '"' + (String(i) === y ? ' selected' : '') + '>' + i + '</option>';
  }
  let months = '<option value="">—</option>';
  for (let i = 1; i <= 12; i++) {
    const v = (i < 10 ? '0' : '') + i;
    months += '<option value="' + v + '"' + (v === m ? ' selected' : '') + '>' + v + '</option>';
  }
  let days = '<option value="">—</option>';
  for (let i = 1; i <= 31; i++) {
    const v = (i < 10 ? '0' : '') + i;
    days += '<option value="' + v + '"' + (v === dd ? ' selected' : '') + '>' + v + '</option>';
  }
  return '<div class="triple">' +
    '<select class="select" id="fYear" aria-label="Year">' + years + '</select>' +
    '<select class="select" id="fMonth" aria-label="Month">' + months + '</select>' +
    '<select class="select" id="fDay" aria-label="Day">' + days + '</select>' +
  '</div>';
}

/** Human label for country code (None / Global / IR …). */
function countryLabel(code) {
  const c = String(code || '').toUpperCase();
  if (!c) return 'None';
  const hit = COUNTRY_OPTIONS.filter(o => o.code === c)[0];
  return hit ? hit.label : c;
}

/* Media roles for thumb table act + plan-B file suffix */
const MEDIA_ROLES = [
  { v: 'tmb', label: 'tmb · square default', act: 1 },
  { v: 'tmv', label: 'tmv · wide (music video)', act: 2 },
  { v: 'cvr', label: 'cvr · vertical cover', act: 3 },
  { v: 'art', label: 'art · hero / wide art', act: 4 },
  { v: 'gal', label: 'gal · gallery', act: 5 },
  { v: 'off', label: 'off · remove', act: 0 }
];
/* Owner / user account media — photo is external URL only; ico is host file (png/svg) */
const MEDIA_ROLES_OWNER = [
  { v: 'tmb', label: 'tmb · avatar square', act: 1 },
  { v: 'ico', label: 'ico · icon (stored as jpg on host)', act: 6 },
  { v: 'photo', label: 'photo · external URL (no host file)', act: 0 },
  { v: 'off', label: 'off · remove', act: 0 }
];

function mediaRoleOptions(selected) {
  const isOwner = state.item && state.item.table === 'user';
  const list = isOwner ? MEDIA_ROLES_OWNER : MEDIA_ROLES;
  return list.map(r =>
    '<option value="' + r.v + '"' + (r.v === selected ? ' selected' : '') + '>' +
    esc(r.label) + '</option>'
  ).join('');
}

/** One media row: preview + URL source + upload + role. */
function mediaRowHtml(row, idx) {
  const url = row && row.url ? String(row.url) : '';
  const role = (row && row.role) ? row.role : 'tmb';
  const letter = esc((state.item && state.item.name ? state.item.name : '?').charAt(0).toUpperCase());
  const previewSrc = url ? esc(url) : '';
  return '<div class="media-row" data-media-row="' + idx + '">' +
    '<span class="thumb" data-media-preview>' +
      '<i>' + letter + '</i>' +
      (previewSrc
        ? '<img src="' + previewSrc + '" alt="" onerror="this.style.display=\'none\'">'
        : '') +
    '</span>' +
    '<div class="media-src">' +
      '<input class="input" type="text" data-media-url inputmode="url" ' +
        'placeholder="https://… image URL (preview updates live)" ' +
        'value="' + esc(url) + '" spellcheck="false" autocomplete="off">' +
      '<button class="btn btn-ghost btn-sm" type="button" data-media-upload title="Upload from device">Upload</button>' +
      '<input class="media-file" type="file" accept="image/*" data-media-file tabindex="-1">' +
    '</div>' +
    '<select class="select media-role" data-media-role aria-label="Media role">' +
      mediaRoleOptions(role) +
    '</select>' +
  '</div>';
}

/** Section 2 body: list of media rows + add button. */
function mediaSectionHtml(d) {
  const list = (d.media && d.media.length) ? d.media : [{ role: 'tmb', url: '' }];
  return '<div class="media-list" id="mediaList">' +
    list.map((row, i) => mediaRowHtml(row, i)).join('') +
  '</div>' +
  '<div class="media-add-wrap">' +
    '<button class="btn btn-ghost btn-sm" type="button" id="mediaAdd" title="Add another image slot">' +
      '<svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16" style="stroke:currentColor;fill:none;stroke-width:1.8"><path d="M12 5v14"/><path d="M5 12h14"/></svg>' +
      ' Add media</button>' +
    '<div id="mediaDupHint" class="media-dup-hint" hidden></div>' +
    '<div class="hint" style="margin-top:8px">' +
      (state.item && state.item.table === 'user'
        ? 'Owner media: <code>tmb</code> · <code>ico</code> on host as <code>u{id}t.jpg</code> / <code>u{id}i.jpg</code>. ' +
          '<code>photo</code> = external URL only (not downloaded). Roles are unique; last wins.'
        : 'Source is a URL or a local file (Upload). Preview follows the URL field live. ' +
          'Roles <code>tmb</code> · <code>tmv</code> · <code>cvr</code> · <code>art</code> are unique (last row wins on the server). ' +
          '<code>gal</code> may repeat. <code>off</code> removes the slot.') +
    '</div>' +
  '</div>';
}

/**
 * Highlight duplicate non-gallery roles (tmb/tmv/cvr/art).
 * Gallery may have many rows. Returns list of duplicated role codes.
 */
function refreshMediaRoleWarnings() {
  const rows = $$('#mediaList [data-media-row]');
  const counts = {};
  rows.forEach(row => {
    const sel = row.querySelector('[data-media-role]');
    const role = sel ? sel.value : 'tmb';
    row.classList.remove('is-dup');
    if (role === 'gal' || role === 'off') return;
    counts[role] = (counts[role] || 0) + 1;
  });
  const dups = Object.keys(counts).filter(r => counts[r] > 1);
  rows.forEach(row => {
    const sel = row.querySelector('[data-media-role]');
    const role = sel ? sel.value : '';
    if (dups.indexOf(role) !== -1) row.classList.add('is-dup');
  });
  const hint = $('#mediaDupHint');
  if (hint) {
    if (dups.length) {
      hint.hidden = false;
      hint.textContent = 'Duplicate role' + (dups.length > 1 ? 's' : '') +
        ' (' + dups.join(', ') + '): only the last image for each role will be saved. ' +
        'Change the selector or remove the extra row.';
    } else {
      hint.hidden = true;
      hint.textContent = '';
    }
  }
  return dups;
}

function thumbEditHtml(d) {
  return mediaSectionHtml(d);
}

function decodeURIComponentSafe(v) {
  try { return encodeURIComponent(v); } catch (e) { return String(v); }
}

function artistChipsHtml(d, displayOnly) {
  if (!d.artists || !d.artists.length) {
    return '<span class="chip is-empty">No artist linked</span>';
  }
  return d.artists.map(a =>
    '<span class="chip' + (a.thumb ? ' has-thumb' : '') + '">' +
      (a.thumb ? thumbHtml({ name: a.name, thumb: a.thumb }) : '') +
      esc(a.name) +
      (displayOnly ? '' :
        '<button class="x" type="button" data-artist-del="' + esc(a.id) + '" ' +
          'title="Detach" aria-label="Detach ' + esc(a.name) + '">&times;</button>') +
    '</span>'
  ).join('');
}

/** Short label for media URL (never dump full base64 on the saved card). */
function mediaUrlLabel(url) {
  const u = url != null ? String(url).trim() : '';
  if (!u) return '—';
  if (/^data:image\//i.test(u)) {
    const kind = (u.match(/^data:image\/([a-z0-9+.-]+)/i) || [])[1] || 'image';
    return 'Local upload · ' + kind;
  }
  // plan-B key without host
  if (/^[A-Za-z0-9_\-.]+$/.test(u) && u.length < 80) return u;
  // full http URL — keep readable, truncate middle if huge
  if (u.length > 96) return u.slice(0, 56) + '…' + u.slice(-24);
  return u;
}

function savedSourcesHtml(d) {
  const rows = (d.sources || []).filter(s => s && s.url);
  if (!rows.length) return '<span class="chip is-empty">No sources</span>';
  return rows.map(s => {
    const act = Number(s.act) || 0;
    const actLabel = act === 3 ? 'locked' : (act === 2 ? 'demo' : (act === 0 ? 'off' : 'orig'));
    return '<div class="saved-media-row">' +
      '<span class="badge" style="flex:none">' + esc(actLabel) + '</span>' +
      '<span class="badge badge-song" style="flex:none">' + esc((s.bit || 0) + 'k') + '</span>' +
      '<span class="badge" style="flex:none">' +
        esc(ownerDisplayName(getOwner(s.user) || { id: Number(s.user) || 0, name: '' })) +
      '</span>' +
      (s.id ? '<span class="hint" style="margin:0">#' + esc(s.id) + '</span>' : '') +
      '<span class="saved-media-url" title="' + esc(s.url) + '">' + esc(s.url.length > 80 ? s.url.slice(0, 56) + '…' : s.url) + '</span>' +
    '</div>';
  }).join('');
}

/** Saved-card media list: one row per image (preview + role + short label). */
function savedMediaHtml(d) {
  const rows = (d.media || []).filter(m => m && m.url && m.role !== 'off');
  if (!rows.length) {
    return '<span class="chip is-empty">No media attached</span>';
  }
  return rows.map(m => {
    const role = m.role || 'tmb';
    const url  = String(m.url);
    const label = mediaUrlLabel(url);
    // Preview: data URL and http work as-is; keys go through thumbHtml
    const previewSrc = url;
    return '<div class="saved-media-row">' +
      thumbHtml({ name: d.name || role, thumb: previewSrc }) +
      '<span class="badge" style="flex:none">' + esc(role) + '</span>' +
      '<span class="saved-media-url" title="' +
        (/^data:image\//i.test(url) ? 'Local file upload (data URL not shown)' : esc(url)) +
      '">' + esc(label) + '</span>' +
    '</div>';
  }).join('');
}

function albumChipsHtml(d, displayOnly) {
  if (!d.albums || !d.albums.length) {
    return '<span class="chip is-empty">No album linked</span>';
  }
  return d.albums.map(a =>
    '<span class="chip album-chip' + (a.thumb ? ' has-thumb' : '') + '" ' +
      'data-album-id="' + esc(a.id) + '" data-name="' + esc(a.name || '') + '" ' +
      'data-thumb="' + esc(a.thumb || '') + '">' +
      (a.thumb ? thumbHtml({ name: a.name, thumb: a.thumb }) : '') +
      '<span class="album-chip-name">' + esc(a.name || ('#' + a.id)) + '</span>' +
      (displayOnly
        ? '<span class="trk-ro">#' + esc(a.trk || 0) + '</span>'
        : '<label class="trk-wrap" title="Track number on this album">' +
            '<span class="trk-hash">#</span>' +
            '<input class="input trk-input" type="number" min="0" max="999" step="1" ' +
              'data-album-trk value="' + esc(a.trk != null ? a.trk : 0) + '" ' +
              'aria-label="Track number for ' + esc(a.name || a.id) + '">' +
          '</label>' +
          '<button class="x" type="button" data-album-del="' + esc(a.id) + '" ' +
            'title="Detach" aria-label="Detach album ' + esc(a.name) + '">&times;</button>') +
    '</span>'
  ).join('');
}

function videoChipsHtml(d, displayOnly) {
  if (!d.videos || !d.videos.length) {
    return '<span class="chip is-empty">No video linked</span>';
  }
  return d.videos.map(v =>
    '<span class="chip' + (v.thumb ? ' has-thumb' : '') + '" ' +
      'data-video-id="' + esc(v.id) + '" data-name="' + esc(v.name || '') + '" ' +
      'data-thumb="' + esc(v.thumb || '') + '">' +
      (v.thumb ? thumbHtml({ name: v.name, thumb: v.thumb }) : '') +
      esc(v.name || ('#' + v.id)) +
      (displayOnly ? '' :
        '<button class="x" type="button" data-video-del="' + esc(v.id) + '" ' +
          'title="Detach" aria-label="Detach video ' + esc(v.name) + '">&times;</button>') +
    '</span>'
  ).join('');
}

/** Songs linked to a video (song2video from the video side). */
function songChipsHtml(d, displayOnly) {
  if (!d.songs || !d.songs.length) {
    return '<span class="chip is-empty">No song linked</span>';
  }
  return d.songs.map(s =>
    '<span class="chip' + (s.thumb ? ' has-thumb' : '') + '" ' +
      'data-song-id="' + esc(s.id) + '" data-name="' + esc(s.name || '') + '" ' +
      'data-thumb="' + esc(s.thumb || '') + '">' +
      (s.thumb ? thumbHtml({ name: s.name, thumb: s.thumb }) : '') +
      esc(s.name || ('#' + s.id)) +
      (s.type === 'podcast' ? ' <span class="hint">podcast</span>' : '') +
      (displayOnly ? '' :
        '<button class="x" type="button" data-song-del="' + esc(s.id) + '" ' +
          'title="Detach" aria-label="Detach song ' + esc(s.name) + '">&times;</button>') +
    '</span>'
  ).join('');
}

function fmtTime(sec) {
  if (!isFinite(sec) || sec < 0) return '0:00';
  const s = Math.floor(sec % 60);
  const m = Math.floor(sec / 60);
  return m + ':' + (s < 10 ? '0' : '') + s;
}

function sourceOwnerFieldHtml(s, canEditUser) {
  ensureOwnersMap();
  const uid = (s.user != null && s.user !== '') ? Number(s.user) : myUserId();
  const owner = getOwner(uid) || (uid === 0
    ? { id: 0, name: 'Hamrahang', act: 0 }
    : { id: uid, name: '#' + uid, act: 0 });
  const label = ownerDisplayName(owner);
  // Hidden stores public id (0 = system). Visible: name or search for act9.
  let html = '<div class="src-owner" data-src-owner>' +
    '<input type="hidden" data-src-user value="' + esc(uid) + '">';
  if (canEditUser) {
    html +=
      '<input class="input src-user" data-src-owner-q type="search" autocomplete="off" ' +
        'placeholder="Owner (name / id) · 0 = Hamrahang" value="' + esc(label) + '" ' +
        'title="Search act≥7 users or type 0 for system">' +
      '<div class="pick-list" data-src-owner-picks hidden></div>';
  } else {
    html +=
      '<span class="src-owner-label" title="Owner #' + esc(uid) + '">' + esc(label) + '</span>';
  }
  html += '</div>';
  return html;
}

function sourceRowHtml(s, index) {
  const act = Number(s.act);
  const a = (act === 0 || act === 1 || act === 2 || act === 3) ? act : 1;
  const locked = a === 3;
  const off = a === 0;
  const isDemo = a === 2;
  // canEdit: url/bit/del/kind. canEditUser: only act≥9 may change owner (unless locked).
  const canEdit = !locked && (isAct9() || Number(s.user) === myUserId() || !s.id);
  const canEditUser = isAct9() && !locked;
  const cls = 'src-row' + (off ? ' is-off' : '') + (locked ? ' is-locked' : '') + (isDemo ? ' is-demo' : '');
  const key = s._key || ('k' + index);

  // NOTE: do NOT put data-src-act on the row — that attribute is only for action buttons.
  // A row-level data-src-act made every click (inputs, seek bar) re-render the section.
  let html = '<div class="' + cls + '" data-src-row data-src-key="' + esc(key) + '" ' +
    'data-src-id="' + esc(s.id || 0) + '" data-src-row-act="' + esc(a) + '">' +
    '<div class="src-main">' +
      '<button class="src-play" type="button" data-src-play title="Play / pause" ' +
        (s.url ? '' : 'disabled') + '>' +
        '<svg data-src-icon viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">' +
          '<path d="M8 5v14l11-7z"/></svg></button>' +
      '<input class="input" data-src-url type="text" inputmode="url" autocomplete="off" ' +
        'placeholder="https://…/track.mp3" ' +
        'value="' + esc(s.url || '') + '" spellcheck="false"' + (canEdit ? '' : ' readonly') + '>' +
      '<input class="input src-bit" data-src-bit type="number" min="0" max="9999" step="1" ' +
        'placeholder="bit" title="Bitrate (kbps)" value="' + esc(s.bit || '') + '"' +
        (canEdit ? '' : ' readonly') + '>' +
      sourceOwnerFieldHtml(s, canEditUser);

  // Kind toggle: orig ↔ demo (fixed ~4-letter labels so button width stays stable)
  if (canEdit && !off) {
    const kindLabel = isDemo ? 'demo' : 'orig';
    const kindTitle = isDemo ? 'Demo — click for original' : 'Original — click for demo';
    html += '<button class="btn btn-ghost btn-sm src-kind' + (isDemo ? ' is-demo' : '') + '" ' +
      'type="button" data-src-kind title="' + kindTitle + '">' + kindLabel + '</button>';
  } else if (isDemo) {
    html += '<span class="src-kind is-demo" title="Demo">demo</span>';
  } else if (!locked && !off) {
    html += '<span class="src-kind" title="Original">orig</span>';
  } else {
    html += '<span></span>';
  }

  html += (canEdit
        ? '<button class="btn btn-ghost btn-sm src-del" type="button" data-src-del title="Delete source">&times;</button>'
        : '<span></span>') +
    '</div>' +
    '<div class="src-prog" data-src-prog>' +
      '<div class="src-bar" data-src-bar><i data-src-fill></i></div>' +
      '<span class="src-time" data-src-time>0:00 / 0:00</span>' +
    '</div>' +
    '<div data-src-vprev hidden></div>';

  if (locked) {
    html += '<div class="src-lock-note">Locked by admin · contact admin · link #' +
      esc(s.id || '—') + '</div>';
  }

  // Action row always present so play errors can sit next to Deactivate
  html += '<div class="src-act-btns" data-src-actions>';
  if (!locked && canEdit) {
    if (off) {
      html += '<button class="btn btn-ghost btn-sm" type="button" data-src-act="1">Re-activate</button>';
      if (isAct9()) html += '<button class="btn btn-ghost btn-sm" type="button" data-src-act="3">Lock</button>';
    } else {
      html += '<button class="btn btn-ghost btn-sm" type="button" data-src-act="0">Deactivate</button>';
      if (isAct9()) html += '<button class="btn btn-ghost btn-sm" type="button" data-src-act="3">Lock</button>';
    }
  }
  html += '<span class="src-err" data-src-err hidden></span></div>';

  html += '</div>';
  return html;
}

function sourcesSectionHtml(d) {
  const rows = Array.isArray(d.sources) ? d.sources : [];
  const isVideo = d.table === 'video';
  const playHint = isVideo
    ? 'Play uses HTML5 <code>video</code> for duration (picture if the browser allows). Quality/bitrate is manual (kbps). Sync via <code>link_video</code>.'
    : 'Play uses HTML5 <code>audio</code> for duration. Bitrate is manual. Sync via <code>link_song</code>.';
  // Kick off owner resolve (non-blocking); refresh labels when done
  const ownerIds = rows.map(s => Number(s.user) || 0).filter(id => id > 0);
  if (ownerIds.length) {
    resolveOwners(ownerIds).then(() => {
      const list = $('#srcList');
      if (!list || !state.item) return;
      // Only update owner labels without full rebind if possible
      $$('#srcList [data-src-row]').forEach(row => {
        const hid = row.querySelector('[data-src-user]');
        const qEl = row.querySelector('[data-src-owner-q]');
        const lab = row.querySelector('.src-owner-label');
        const uid = hid ? Number(hid.value) || 0 : 0;
        const name = ownerDisplayName(getOwner(uid) || { id: uid, name: uid ? ('#' + uid) : 'Hamrahang' });
        if (qEl && document.activeElement !== qEl) qEl.value = name;
        if (lab) lab.textContent = name;
      });
    }).catch(() => {});
  }
  return '<div id="srcSection">' +
    '<div class="src-list" id="srcList">' +
      (rows.length ? rows.map((s, i) => sourceRowHtml(s, i)).join('') :
        '<div class="hint" id="srcEmpty">No sources yet — add a stream URL.</div>') +
    '</div>' +
    '<div style="margin-top:10px">' +
      '<button class="btn btn-accent btn-sm" type="button" id="srcAdd">' +
        '<svg viewBox="0 0 24 24" width="16" height="16" style="stroke:currentColor;fill:none;stroke-width:1.8" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>' +
        ' Add source</button>' +
    '</div>' +
    '<div class="hint" style="margin-top:8px">' + playHint +
      ' act 0 = off · 1 = orig · 2 = demo · 3 = locked.</div>' +
  '</div>';
}

/** Chosen genres as chips. displayOnly = saved card (no delete button). */
function genreChipsHtml(d, displayOnly) {
  if (!d.gnr || !d.gnr.length) {
    return '<span class="chip is-empty">No genre set</span>';
  }
  return d.gnr.map(g =>
    '<span class="chip">' + esc(g.name || ('#' + g.id)) +
      (displayOnly ? '' :
        '<button class="x" type="button" data-genre-del="' + esc(g.id) + '" ' +
          'title="Remove" aria-label="Remove genre ' + esc(g.name || g.id) + '">&times;</button>') +
    '</span>'
  ).join('');
}

/** Genre row of the form: chips + type-ahead input + add button. */
function genreRowHtml(d) {
  return '<div class="estep-row">' +
    '<label>Genres</label>' +
    '<div>' +
      '<div class="artist-chips genre-chips" id="genreChips">' + genreChipsHtml(d, false) + '</div>' +
      '<div class="genre-line" style="margin-top:9px">' +
        '<input class="input" id="genreSearch" type="text" ' +
          'placeholder="Type a genre name ..." spellcheck="false">' +
        '<button class="btn btn-ghost icon-btn is-add" type="button" id="genreAdd" ' +
          'title="Add the pinned genre" aria-label="Add genre">+</button>' +
      '</div>' +
      '<div class="pick-list" id="genrePicks"></div>' +
      '<div class="hint" style="margin-top:6px">Type to filter the genre table; an exact match gets ' +
        'pinned (green), then commit it with <b>+</b> or Enter — or just click a row.</div>' +
    '</div>' +
  '</div>';
}

function lyricKndLabel(knd) {
  return knd === 'srt' ? 'subtitle' : 'lyric';
}

function lyricLangOptions(selected) {
  const langs = taxLangs();
  const opts = langs.length
    ? langs.map(l => {
        const code = (l && (l.code || l.lng)) ? String(l.code || l.lng) : String(l);
        const name = (l && (l.label || l.name)) ? String(l.label || l.name) : code;
        return '<option value="' + esc(code) + '"' + (code === selected ? ' selected' : '') + '>' +
          esc(String(code).toUpperCase() + ' · ' + name) + '</option>';
      }).join('')
    : '<option value="fa">FA</option><option value="en">EN</option>';
  return opts;
}

/** One saved lyric row (local draft — Edit / Remove). */
function lyricSavedRowHtml(row, index) {
  const preview = (row.body || '').split(/\r?\n/).filter(Boolean)[0] || '(empty)';
  return '<div class="lyric-row" data-lyric-index="' + index + '">' +
    '<div class="lyric-meta">' +
      '<span class="badge">' + esc(lyricKndLabel(row.knd)) + '</span>' +
      '<span class="badge badge-song">' + esc(String(row.lng || '').toUpperCase()) + '</span>' +
    '</div>' +
    '<span class="lyric-preview" title="' + esc(row.body || '') + '">' + esc(preview) + '</span>' +
    '<button class="btn btn-ghost btn-sm" type="button" data-lyric-edit="' + index + '">Edit</button>' +
    '<button class="btn btn-ghost btn-sm" type="button" data-lyric-del="' + index + '" title="Remove">&times;</button>' +
  '</div>';
}

/** Inline editor for a new or existing lyric entry (local Save only). */
function lyricEditorHtml(edit) {
  const body = edit && edit.body != null ? edit.body : '';
  const lng  = (edit && edit.lng) || 'fa';
  const knd  = (edit && edit.knd) || 'lrc';
  return '<div class="lyric-box" id="lyricBox">' +
    '<textarea class="textarea" id="fLyric" rows="10" ' +
      'placeholder="Paste or type lyrics…&#10;Each line is kept (Enter / Shift+Enter).">' +
      esc(body) +
    '</textarea>' +
    '<div class="lyric-edit-bar">' +
      '<label class="hint" style="margin:0">Language</label>' +
      '<select class="select" id="fLyricLng" aria-label="Lyric language">' + lyricLangOptions(lng) + '</select>' +
      '<label class="hint" style="margin:0">Kind</label>' +
      '<select class="select" id="fLyricKnd" aria-label="Lyric or subtitle">' +
        '<option value="lrc"' + (knd === 'lrc' ? ' selected' : '') + '>lyric</option>' +
        '<option value="srt"' + (knd === 'srt' ? ' selected' : '') + '>subtitle</option>' +
      '</select>' +
      '<button class="btn btn-primary btn-sm" type="button" data-lyric-local-save>Save</button>' +
      '<button class="btn btn-ghost btn-sm" type="button" data-lyric-cancel>Cancel</button>' +
    '</div>' +
    '<div class="hint">Local save only — lines keep their line-breaks. Submit the form to write the <code>lyric</code> table.</div>' +
  '</div>';
}

function lyricSectionHtml(d) {
  if (!d.lyrics) d.lyrics = [];
  let html = '<div id="lyricSection">';

  // saved local entries
  if (d.lyrics.length) {
    html += '<div class="lyric-list" id="lyricList">' +
      d.lyrics.map((row, i) => lyricSavedRowHtml(row, i)).join('') +
    '</div>';
  }

  // open editor OR add button
  if (d.lyricEdit) {
    html += lyricEditorHtml(d.lyricEdit);
  } else {
    html += '<div class="lyric-empty" id="lyricEmpty" style="margin-top:8px">' +
      '<button class="btn btn-accent btn-sm" type="button" data-lyric-add>' +
        '<svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16" style="stroke:currentColor;fill:none;stroke-width:1.8"><path d="M12 5v14"/><path d="M5 12h14"/></svg>' +
        ' Add lyric / subtitle</button>' +
    '</div>';
  }

  html += '</div>';
  return html;
}

/** Saved-card media-style blocks for lyrics (scrollable body). */
function savedLyricsHtml(d) {
  const rows = (d.lyrics || []).filter(x => x && (x.body || x.src));
  if (!rows.length) return '';
  return rows.map(x =>
    '<div class="saved-lyric-block">' +
      '<div class="saved-lyric-head">' +
        '<span class="badge">' + esc(lyricKndLabel(x.knd)) + '</span>' +
        '<span class="badge badge-song">' + esc(String(x.lng || '').toUpperCase()) + '</span>' +
        (x.src ? '<span class="hint" style="margin:0">' + esc(x.src) + '</span>' : '') +
      '</div>' +
      '<div class="saved-lyric-body">' + esc(x.body || '(body not loaded)') + '</div>' +
    '</div>'
  ).join('');
}

/** Render the whole editor form inside the item zone (no page reload). */
function tracksSectionHtml(d) {
  const rows = Array.isArray(d.tracks) ? d.tracks : [];
  let html = '<div id="trkSection">' +
    '<div class="trk-list" id="trkList">';
  if (!rows.length) {
    html += '<div class="hint" id="trkEmpty">No tracks on this album yet.</div>';
  } else {
    html += rows.map((t, i) => trackRowHtml(t, i)).join('');
  }
  html += '</div>' +
    '<div class="field" style="margin-top:12px">' +
      '<label for="trackSearch">Attach a song or podcast</label>' +
      '<div class="rel-attach-row">' +
        '<input class="input" id="trackSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-create-type="song" title="Create new song (coming soon)">+ New</button>' +
      '</div>' +
      '<div class="pick-list" id="trackPicks"></div>' +
      '<div class="hint" style="margin-top:6px">Only items from the song table. Order is track number on submit (1…n). Use arrows to reorder.</div>' +
    '</div></div>';
  return html;
}

/** ISO-style country list for artist nationality (local — not a DB table). */
const COUNTRY_OPTIONS = [
  // None = unknown / not set (no geo ranking). Global = explicit world act (rank in many markets).
  { code: '',   label: '— None (no nationality) —' },
  { code: 'GL', label: 'Global (world act)' },
  { code: 'IR', label: 'Iran (IR)' },
  { code: 'AF', label: 'Afghanistan (AF)' },
  { code: 'TR', label: 'Turkey (TR)' },
  { code: 'IQ', label: 'Iraq (IQ)' },
  { code: 'AZ', label: 'Azerbaijan (AZ)' },
  { code: 'AM', label: 'Armenia (AM)' },
  { code: 'US', label: 'United States (US)' },
  { code: 'GB', label: 'United Kingdom (GB)' },
  { code: 'CA', label: 'Canada (CA)' },
  { code: 'DE', label: 'Germany (DE)' },
  { code: 'FR', label: 'France (FR)' },
  { code: 'SE', label: 'Sweden (SE)' },
  { code: 'NO', label: 'Norway (NO)' },
  { code: 'FI', label: 'Finland (FI)' },
  { code: 'NL', label: 'Netherlands (NL)' },
  { code: 'IT', label: 'Italy (IT)' },
  { code: 'ES', label: 'Spain (ES)' },
  { code: 'PT', label: 'Portugal (PT)' },
  { code: 'BR', label: 'Brazil (BR)' },
  { code: 'MX', label: 'Mexico (MX)' },
  { code: 'AR', label: 'Argentina (AR)' },
  { code: 'IN', label: 'India (IN)' },
  { code: 'PK', label: 'Pakistan (PK)' },
  { code: 'BD', label: 'Bangladesh (BD)' },
  { code: 'JP', label: 'Japan (JP)' },
  { code: 'KR', label: 'South Korea (KR)' },
  { code: 'CN', label: 'China (CN)' },
  { code: 'AU', label: 'Australia (AU)' },
  { code: 'NZ', label: 'New Zealand (NZ)' },
  { code: 'RU', label: 'Russia (RU)' },
  { code: 'UA', label: 'Ukraine (UA)' },
  { code: 'EG', label: 'Egypt (EG)' },
  { code: 'SA', label: 'Saudi Arabia (SA)' },
  { code: 'AE', label: 'United Arab Emirates (AE)' },
  { code: 'LB', label: 'Lebanon (LB)' },
  { code: 'SY', label: 'Syria (SY)' },
  { code: 'JO', label: 'Jordan (JO)' },
  { code: 'KW', label: 'Kuwait (KW)' },
  { code: 'QA', label: 'Qatar (QA)' },
  { code: 'BH', label: 'Bahrain (BH)' },
  { code: 'OM', label: 'Oman (OM)' },
  { code: 'MA', label: 'Morocco (MA)' },
  { code: 'TN', label: 'Tunisia (TN)' },
  { code: 'DZ', label: 'Algeria (DZ)' },
  { code: 'NG', label: 'Nigeria (NG)' },
  { code: 'ZA', label: 'South Africa (ZA)' },
  { code: 'SE', label: 'Sweden (SE)' },
  { code: 'PL', label: 'Poland (PL)' },
  { code: 'GR', label: 'Greece (GR)' },
  { code: 'IE', label: 'Ireland (IE)' }
];

function countrySelectHtml(selected) {
  const cur = String(selected || '').toUpperCase();
  const known = {};
  COUNTRY_OPTIONS.forEach(c => { known[c.code] = true; });
  let opts = COUNTRY_OPTIONS.map(c =>
    '<option value="' + esc(c.code) + '"' + (c.code === cur ? ' selected' : '') + '>' +
      esc(c.label) + '</option>'
  ).join('');
  // keep unknown codes from DB so we do not wipe them
  if (cur && !known[cur]) {
    opts += '<option value="' + esc(cur) + '" selected>' + esc(cur) + ' (custom)</option>';
  }
  return '<select class="select" id="fCc" aria-label="Country / nationality">' + opts + '</select>';
}

const REL_PAGE = 20;  // initial visible rows
const REL_STEP = 10;  // each "Load more"

/**
 * Related catalog section for artist (songs / albums / videos).
 * - Fast filter is client-side over the full draft list (no API).
 * - Lazy rows: first 20, then +10; thumbs load only for visible rows.
 * - Edit opens nested form; × detaches; + New is a Create placeholder.
 */
function relatedSectionHtml(kind, d, ui) {
  const cfg = {
    songs  : { title: 'Songs',   key: 'songs',  searchId: 'songSearch',  picksId: 'songPicks',  create: 'song',  table: 'song',  hint: 'song2artist — flat list (not grouped by album). Filter is local.' },
    albums : { title: 'Albums',  key: 'albums', searchId: 'albumSearch', picksId: 'albumPicks', create: 'album', table: 'album', hint: 'album2artist. Filter is local.' },
    videos : { title: 'Videos',  key: 'videos', searchId: 'videoSearch', picksId: 'videoPicks', create: 'video', table: 'video', hint: 'video2artist. Filter is local.' }
  }[kind];
  if (!cfg) return '';
  ui = ui || {};
  const q = String(ui.q || '').trim();
  const shown = Math.max(REL_PAGE, Number(ui.shown) || REL_PAGE);
  const all = Array.isArray(d[cfg.key]) ? d[cfg.key] : [];
  const qLower = q.toLowerCase();
  const filtered = !qLower ? all : all.filter(row => {
    const name = String(row.name || '').toLowerCase();
    const alt  = String(row.nameAlt || '').toLowerCase();
    const id = String(row.id || '');
    const url = String(row.url || '').toLowerCase();
    return name.indexOf(qLower) !== -1
      || alt.indexOf(qLower) !== -1
      || id === q
      || id.indexOf(q) !== -1
      || url.indexOf(qLower) !== -1;
  });
  const rows = filtered.slice(0, shown);
  const hasMore = filtered.length > rows.length;

  let html = '<div id="relSection-' + cfg.key + '" data-rel-kind="' + esc(cfg.key) + '" ' +
    'data-rel-shown="' + shown + '" data-rel-q="' + esc(q) + '">' +
    '<div class="field" style="margin-bottom:10px">' +
      '<label for="relFilter-' + cfg.key + '">Fast filter (local)</label>' +
      '<input class="input" id="relFilter-' + cfg.key + '" type="search" ' +
        'data-rel-filter="' + esc(cfg.key) + '" value="' + esc(q) + '" ' +
        'placeholder="Filter by name or id …" spellcheck="false">' +
      '<div class="hint" style="margin-top:4px">' +
        esc(String(rows.length)) + ' of ' + esc(String(filtered.length)) +
        (q ? ' match' : ' linked') +
        (all.length !== filtered.length ? ' · ' + esc(String(all.length)) + ' total' : '') +
      '</div>' +
    '</div>' +
    '<div class="trk-list" id="relList-' + cfg.key + '">';
  if (!rows.length) {
    html += '<div class="hint">' +
      (q ? 'No match for this filter.' : ('No ' + esc(cfg.title.toLowerCase()) + ' linked yet.')) +
    '</div>';
  } else {
    html += rows.map((row, i) => relatedRowHtml(cfg, row, i)).join('');
  }
  html += '</div>';
  if (hasMore) {
    html += '<div style="margin-top:10px">' +
      '<button class="btn btn-ghost btn-sm" type="button" data-rel-more="' + esc(cfg.key) + '">' +
        'Load more (' + esc(String(Math.min(REL_STEP, filtered.length - rows.length))) +
        ' of ' + esc(String(filtered.length - rows.length)) + ' remaining)</button>' +
    '</div>';
  }
  html +=
    '<div class="field" style="margin-top:12px">' +
      '<label for="' + cfg.searchId + '">Attach existing</label>' +
      '<div class="rel-attach-row">' +
        '<input class="input" id="' + cfg.searchId + '" type="search" placeholder="Name or public id ..." spellcheck="false">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-create-type="' + esc(cfg.create) + '" ' +
          'title="Create new ' + esc(cfg.create) + ' (form not built yet)">+ New</button>' +
      '</div>' +
      '<div class="pick-list" id="' + cfg.picksId + '"></div>' +
      '<div class="hint" style="margin-top:6px">' + esc(cfg.hint) + '</div>' +
    '</div></div>';
  return html;
}

/** Re-render one related section preserving filter + page size from the DOM node. */
function refreshRelatedSection(kind) {
  if (!state.item) return;
  const el = $('#relSection-' + kind);
  const ui = {
    q: el ? (el.getAttribute('data-rel-q') || '') : '',
    shown: el ? (Number(el.getAttribute('data-rel-shown')) || REL_PAGE) : REL_PAGE
  };
  const html = relatedSectionHtml(kind, state.item, ui);
  if (el) el.outerHTML = html;
  // re-bind attach search for this kind
  if (kind === 'songs' && typeof bindSongLinkSearch === 'function') bindSongLinkSearch();
  if (kind === 'albums' || kind === 'videos') {
    try { bindEditor(); } catch (e) {}
  }
}

function relatedRowHtml(cfg, row, index) {
  const type = row.type || cfg.table || 'song';
  const editKey = (type === 'podcast' ? 'podcast' : (type === 'musicvideo' || type === 'video' ? type : cfg.table)) + ':' + row.id;
  const alt = row.nameAlt ? String(row.nameAlt) : '';
  const sub = [row.artists].filter(Boolean).join(' · ') || '';
  return '<div class="trk-row" data-rel-row data-rel-kind="' + esc(cfg.key) + '" data-rel-id="' + esc(row.id) + '">' +
    '<span class="trk-num">' + esc(index + 1) + '</span>' +
    thumbHtml({ name: row.name, thumb: row.thumb }) +
    '<div class="trk-main">' +
      '<div class="trk-title">' +
        esc(row.name || ('#' + row.id)) +
        (alt ? ' <span class="name-alt">' + esc(alt) + '</span>' : '') +
      '</div>' +
      '<div class="trk-sub">' +
        '<span class="badge badge-' + esc(type) + '" style="margin-right:6px">' +
          esc(TYPE_LABEL[type] || type) + '</span>' +
        esc(sub || ('#' + row.id)) +
      '</div>' +
    '</div>' +
    '<div class="trk-actions">' +
      '<button class="btn btn-ghost btn-sm" type="button" data-rel-edit="' + esc(editKey) + '" title="Edit item">Edit</button>' +
      '<button class="btn btn-ghost btn-sm" type="button" data-rel-del="' + esc(row.id) + '" data-rel-kind="' + esc(cfg.key) + '" title="Detach">&times;</button>' +
    '</div>' +
  '</div>';
}

function trackRowHtml(t, index) {
  const num = (t.trk > 0) ? t.trk : (index + 1);
  const alt = t.nameAlt ? String(t.nameAlt) : '';
  const sub = [t.artists].filter(Boolean).join(' · ') || t.url || '';
  const type = t.type || 'song';
  return '<div class="trk-row" data-trk-row data-trk-id="' + esc(t.id) + '" data-trk-type="' + esc(type) + '">' +
    '<span class="trk-num">' + esc(num) + '</span>' +
    thumbHtml({ name: t.name, thumb: t.thumb }) +
    '<div class="trk-main">' +
      '<div class="trk-title">' +
        esc(t.name || ('#' + t.id)) +
        (alt ? ' <span class="name-alt">' + esc(alt) + '</span>' : '') +
      '</div>' +
      '<div class="trk-sub">' +
        '<span class="badge badge-' + esc(type) + '" style="margin-right:6px">' +
          esc(TYPE_LABEL[type] || type) + '</span>' +
        esc(sub || '—') +
      '</div>' +
    '</div>' +
    '<div class="trk-actions">' +
      '<div class="trk-move">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-trk-up title="Move up" aria-label="Move up">' +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 14l6-6 6 6"/></svg></button>' +
        '<button class="btn btn-ghost btn-sm" type="button" data-trk-down title="Move down" aria-label="Move down">' +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 10l6 6 6-6"/></svg></button>' +
      '</div>' +
      '<button class="btn btn-ghost btn-sm" type="button" data-trk-edit="' + esc(type + ':' + t.id) + '" title="Edit item">Edit</button>' +
      '<button class="btn btn-ghost btn-sm" type="button" data-trk-del="' + esc(t.id) + '" title="Remove from album">&times;</button>' +
    '</div>' +
  '</div>';
}

function renderEditor() {
  const d = state.item;
  const zone = $('#itemZone');
  const table = d.table;
  const isAlbum  = (table === 'album');
  const isSong   = (table === 'song');
  const isVideo  = (table === 'video');
  const isArtist = (table === 'artist');

  let steps = '';

  /* --- section 1 : identity --- */
  const nameHint = isAlbum
    ? 'The global name becomes <code>album_name</code>; extra rows go into <code>data.names</code>.'
    : isVideo
      ? 'The global name becomes <code>video_name</code>; extra rows go into <code>data.names</code>.'
      : isArtist
        ? 'The global name becomes <code>artist_name</code> exactly as typed (case preserved, e.g. 2AFM). Extra rows go into <code>data.names</code>.'
        : 'The global name becomes <code>song_name</code> exactly as typed (case preserved). Extra rows go into <code>data.names</code>.';

  steps += '<section class="estep">' +
    '<h2><span class="num">1</span> Identity</h2>' +
    idRowHtml(d) +
    '<div class="estep-row">' +
      '<label>Names</label>' +
      '<div>' + nameLinesHtml(d) +
        '<div class="hint" style="margin-top:8px">' + nameHint + '</div>' +
      '</div>' +
    '</div>' +
    '<div class="estep-row">' +
      '<label for="fDes">Description</label>' +
      '<div>' +
        '<textarea class="textarea" id="fDes" rows="3" placeholder="Stored in data.des">' + esc(d.des) + '</textarea>' +
      '</div>' +
    '</div>';

  if (isArtist) {
    steps +=
    '<div class="estep-row">' +
      '<label for="fCc">Country / nationality</label>' +
      '<div>' +
        countrySelectHtml(d.cc) +
        '<div class="hint" style="margin-top:6px">Market / nationality for geo ranking (IP). ' +
          '<b>None</b> = not set (unknown). <b>Global (GL)</b> = explicit world act (rank in many markets). ' +
          'Not a language field — language stays on content items.</div>' +
      '</div>' +
    '</div>' +
    '<div class="estep-row">' +
      '<label>Birth date</label>' +
      '<div>' + tripleHtml(d.bd || d.rls || '', { minYear: 1900 }) +
        '<div class="hint" style="margin-top:6px">Year · month · day → <code>artist_bd</code> (from 1900).</div></div>' +
    '</div>';
  } else if (isSong || isVideo) {
    steps +=
    '<div class="estep-row">' +
      '<label for="fLan">Language / Duration</label>' +
      '<div>' +
        '<div class="lan-dur-row">' +
          '<select class="select" id="fLan">' + langOptions(d.lan) + '</select>' +
          '<input class="input" id="fDur" type="number" min="0" step="1" ' +
            'placeholder="Duration (sec)" value="' + esc(d.dur > 0 ? d.dur : '') + '" ' +
            'title="Length in seconds">' +
        '</div>' +
        '<div class="hint" style="margin-top:6px">Language from <code>item2lang</code>. Duration from <code>meta.dur</code> or play / manual.</div>' +
      '</div>' +
    '</div>';
  } else {
    steps +=
    '<div class="estep-row">' +
      '<label for="fLan">Language</label>' +
      '<div><select class="select" id="fLan">' + langOptions(d.lan) + '</select>' +
        '<div class="hint" style="margin-top:6px">From <code>item2lang</code> when available.</div></div>' +
    '</div>';
  }

  if (!isArtist) {
    steps +=
    '<div class="estep-row">' +
      '<label>Release date</label>' +
      '<div>' + tripleHtml(d.rls) +
        '<div class="hint" style="margin-top:6px">Year · month · day.</div></div>' +
    '</div>';
  }

  steps +=
    genreRowHtml(d) +
    '<div class="estep-row">' +
      '<label for="fAct">Status (act)</label>' +
      '<div><select class="select" id="fAct">' + actOptions(d.act) + '</select>' +
        '<div class="hint" style="margin-top:6px">0 off · 1 active · 2 special · 3 advertising.</div></div>' +
    '</div>' +
  '</section>';

  /* --- section 2 : thumb & cover --- */
  steps += '<section class="estep">' +
    '<h2><span class="num">2</span> Thumb &amp; cover</h2>' +
    thumbEditHtml(d) +
  '</section>';

  if (isArtist) {
    steps += '<section class="estep">' +
      '<h2><span class="num">3</span> Social</h2>' +
      '<div class="estep-row">' +
        '<label for="fYtm">YouTube</label>' +
        '<div><input class="input" id="fYtm" type="text" placeholder="channel or URL" value="' + esc(d.ytm || '') + '" spellcheck="false">' +
          '<div class="hint" style="margin-top:6px">Stored in <code>data.ytm</code>.</div></div>' +
      '</div>' +
      '<div class="estep-row">' +
        '<label for="fIns">Instagram</label>' +
        '<div><input class="input" id="fIns" type="text" placeholder="@handle or URL" value="' + esc(d.ins || '') + '" spellcheck="false">' +
          '<div class="hint" style="margin-top:6px">Stored in <code>data.ins</code>.</div></div>' +
      '</div>' +
      '<div class="estep-row">' +
        '<label for="fTlg">Telegram</label>' +
        '<div><input class="input" id="fTlg" type="text" placeholder="@channel or URL" value="' + esc(d.tlg || '') + '" spellcheck="false">' +
          '<div class="hint" style="margin-top:6px">Stored in <code>data.tlg</code>.</div></div>' +
      '</div>' +
    '</section>';

    steps += '<section class="estep">' +
      '<h2><span class="num">4</span> Songs</h2>' +
      relatedSectionHtml('songs', d) +
    '</section>';
    steps += '<section class="estep">' +
      '<h2><span class="num">5</span> Albums</h2>' +
      relatedSectionHtml('albums', d) +
    '</section>';
    steps += '<section class="estep">' +
      '<h2><span class="num">6</span> Videos</h2>' +
      relatedSectionHtml('videos', d) +
    '</section>';
  } else if (isSong) {
    /* --- section 3 : lyric --- */
    steps += '<section class="estep">' +
      '<h2><span class="num">3</span> Lyric</h2>' +
      lyricSectionHtml(d) +
    '</section>';

    /* --- section 4 : artists --- */
    steps += '<section class="estep">' +
      '<h2><span class="num">4</span> Artists</h2>' +
      '<div class="artist-chips" id="artistChips">' + artistChipsHtml(d) + '</div>' +
      '<div class="field">' +
        '<label for="artistSearch">Attach an artist</label>' +
        '<div class="rel-attach-row">' +
          '<input class="input" id="artistSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
          '<button class="btn btn-ghost btn-sm" type="button" data-create-type="artist" title="Create new artist (coming soon)">+ New</button>' +
        '</div>' +
        '<div class="pick-list" id="artistPicks"></div>' +
      '</div>' +
    '</section>';

    /* --- section 5 : albums --- */
    steps += '<section class="estep">' +
      '<h2><span class="num">5</span> Albums</h2>' +
      '<div class="artist-chips" id="albumChips">' + albumChipsHtml(d) + '</div>' +
      '<div class="field">' +
        '<label for="albumSearch">Attach an album</label>' +
        '<div class="rel-attach-row">' +
          '<input class="input" id="albumSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
          '<button class="btn btn-ghost btn-sm" type="button" data-create-type="album" title="Create new album (coming soon)">+ New</button>' +
        '</div>' +
        '<div class="pick-list" id="albumPicks"></div>' +
        '<div class="hint" style="margin-top:6px">A song may belong to several albums (song2album).</div>' +
      '</div>' +
    '</section>';

    /* --- section 6 : videos --- */
    steps += '<section class="estep">' +
      '<h2><span class="num">6</span> Videos</h2>' +
      '<div class="artist-chips" id="videoChips">' + videoChipsHtml(d) + '</div>' +
      '<div class="field">' +
        '<label for="videoSearch">Attach a music video</label>' +
        '<div class="rel-attach-row">' +
          '<input class="input" id="videoSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
          '<button class="btn btn-ghost btn-sm" type="button" data-create-type="video" title="Create new video (coming soon)">+ New</button>' +
        '</div>' +
        '<div class="pick-list" id="videoPicks"></div>' +
      '</div>' +
    '</section>';

    /* --- section 7 : sources --- */
    steps += '<section class="estep">' +
      '<h2><span class="num">7</span> Sources</h2>' +
      sourcesSectionHtml(d) +
    '</section>';
  } else if (isVideo) {
    steps += '<section class="estep">' +
      '<h2><span class="num">3</span> Lyric &amp; subtitle</h2>' +
      lyricSectionHtml(d) +
    '</section>';

    steps += '<section class="estep">' +
      '<h2><span class="num">4</span> Artists</h2>' +
      '<div class="artist-chips" id="artistChips">' + artistChipsHtml(d) + '</div>' +
      '<div class="field">' +
        '<label for="artistSearch">Attach an artist</label>' +
        '<div class="rel-attach-row">' +
          '<input class="input" id="artistSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
          '<button class="btn btn-ghost btn-sm" type="button" data-create-type="artist" title="Create new artist (coming soon)">+ New</button>' +
        '</div>' +
        '<div class="pick-list" id="artistPicks"></div>' +
      '</div>' +
    '</section>';

    steps += '<section class="estep">' +
      '<h2><span class="num">5</span> Songs</h2>' +
      '<div class="artist-chips" id="songChips">' + songChipsHtml(d) + '</div>' +
      '<div class="field">' +
        '<label for="songSearch">Attach a song or podcast</label>' +
        '<input class="input" id="songSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
        '<div class="pick-list" id="songPicks"></div>' +
        '<div class="hint" style="margin-top:6px">Link via <code>song2video</code> (same relation as song → video).</div>' +
      '</div>' +
    '</section>';

    steps += '<section class="estep">' +
      '<h2><span class="num">6</span> Sources</h2>' +
      sourcesSectionHtml(d) +
    '</section>';
  } else if (isAlbum) {
    /* --- section 3 : artists --- */
    steps += '<section class="estep">' +
      '<h2><span class="num">3</span> Artists</h2>' +
      '<div class="artist-chips" id="artistChips">' + artistChipsHtml(d) + '</div>' +
      '<div class="field">' +
        '<label for="artistSearch">Attach an artist</label>' +
        '<div class="rel-attach-row">' +
          '<input class="input" id="artistSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
          '<button class="btn btn-ghost btn-sm" type="button" data-create-type="artist" title="Create new artist (coming soon)">+ New</button>' +
        '</div>' +
        '<div class="pick-list" id="artistPicks"></div>' +
      '</div>' +
    '</section>';

    /* --- section 4 : tracks (songs / podcasts only) --- */
    steps += '<section class="estep">' +
      '<h2><span class="num">4</span> Tracks</h2>' +
      tracksSectionHtml(d) +
    '</section>';
  } else {
    /* generic: artists only for now */
    steps += '<section class="estep">' +
      '<h2><span class="num">3</span> Artists</h2>' +
      '<div class="artist-chips" id="artistChips">' + artistChipsHtml(d) + '</div>' +
      '<div class="field">' +
        '<label for="artistSearch">Attach an artist</label>' +
        '<input class="input" id="artistSearch" type="search" placeholder="Name or public id ..." spellcheck="false">' +
        '<div class="pick-list" id="artistPicks"></div>' +
      '</div>' +
    '</section>';
  }

  const cancelLabel = (state.editStack && state.editStack.length)
    ? ('Back to ' + (state.editStack[state.editStack.length - 1].name || 'parent'))
    : 'Cancel';
  const headerBack = (state.editStack && state.editStack.length)
    ? cancelLabel
    : 'Results';

  zone.innerHTML =
    zoneBarHtml(d, headerBack) +
    '<form class="panel editor" id="itemForm" autocomplete="off" novalidate>' +
      steps +
      '<div class="editor-foot">' +
        '<button class="btn btn-ghost" type="button" data-zone-back>' + esc(cancelLabel) + '</button>' +
        '<button class="btn btn-primary" id="itemSubmit" type="submit">' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>' +
          (Number(d.id) > 0 ? 'Submit' : 'Create') + '</button>' +
      '</div>' +
    '</form>' +
    '<p class="status" id="itemStatus" role="status" aria-live="polite" hidden></p>';

  showItemZone();
  bindEditor();
  validateGlobalName();
}

function setItemStatus(text, kind) {
  const el = $('#itemStatus');
  if (!el) return;
  if (!text) { el.hidden = true; el.textContent = ''; el.className = 'status'; return; }
  el.textContent = text;
  el.className = 'status' + (kind ? ' is-' + kind : '');
  el.hidden = false;
}

function showItemZone() {
  $('#searchZone').hidden = true;
  $('#itemZone').hidden = false;
}

/** Stop any playing source preview (call on leave editor / submit). */
function stopAllSources() {
  if (typeof srcAudio !== 'undefined' && srcAudio) {
    try { srcAudio.pause(); } catch (e) {}
    srcAudio = null;
  }
  if (typeof srcPlayingKey !== 'undefined') srcPlayingKey = null;
}

function pushEditStack() {
  if (!state.item) return;
  try { collectDraft(); } catch (e) {}
  // deep snapshot so nested edit cannot mutate parent draft
  state.editStack.push(JSON.parse(JSON.stringify(state.item)));
}

function leaveItemZone() {
  stopAllSources();
  if (state.editStack && state.editStack.length) {
    state.item = state.editStack.pop();
    // renumber tracks if parent is album
    if (state.item && state.item.table === 'album' && state.item.tracks) {
      state.item.tracks = normalizeTrackOrder(state.item.tracks);
    }
    renderEditor();
    return;
  }
  showSearchZone();
}

function showSearchZone() {
  stopAllSources();
  state.editStack = [];
  $('#itemZone').hidden = true;
  $('#itemZone').innerHTML = '';
  $('#searchZone').hidden = false;
}

/** Edit button of a result row: fetch the full JSON and open the form. */
/**
 * @param {string} key  type:id
 * @param {{ nested?: boolean }} [opts]  nested=true keeps editStack (album → song)
 */
async function openItem(key, opts) {
  const nested = !!(opts && opts.nested);
  if (!nested) state.editStack = [];

  const parts = String(key).split(':');
  const type  = parts[0];
  const id    = Number(parts[1]);
  const table = TYPE_TABLE[type] || type;
  if (!id) return;

  $$('#resultsList .result').forEach(el => {
    el.classList.toggle('is-selected', el.getAttribute('data-key') === key);
  });
  state.selected = { type: type, id: id, name: '' };

  const zone = $('#itemZone');
  $('#searchZone').hidden = true;
  zone.hidden = false;
  zone.innerHTML =
    '<div class="zone-bar"><div class="zone-title">' +
      '<button class="btn btn-ghost btn-sm" type="button" data-zone-back>' +
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>Results</button>' +
      '<b>Loading #' + esc(id) + ' ...</b>' +
    '</div></div>' +
    '<div class="panel"><p class="status is-info">Fetching the full card JSON ...</p></div>';
  zone.scrollIntoView({ behavior: 'smooth', block: 'start' });

  const data = await queryGet({ qjs: 'ep', p: table, q1: String(id) });

  if (!data || data.error) {
    zone.innerHTML =
      '<div class="zone-bar"><div class="zone-title">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-zone-back>' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>Results</button>' +
        '<b>#' + esc(id) + '</b>' +
      '</div></div>' +
      '<div class="panel"><p class="status is-error">' + esc(errorText(data)) + '</p></div>';
    return;
  }

  let full = null;
  if (data[table] && typeof data[table] === 'object') {
    full = data[table];
  } else {
    const keys = Object.keys(data).filter(k =>
      k !== 'error' && data[k] && typeof data[k] === 'object' && !Array.isArray(data[k]));
    if (keys.length) full = data[keys[0]];
  }
  if (!full || !full.id) {
    zone.innerHTML =
      '<div class="zone-bar"><div class="zone-title">' +
        '<button class="btn btn-ghost btn-sm" type="button" data-zone-back>' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>Results</button>' +
        '<b>#' + esc(id) + '</b>' +
      '</div></div>' +
      '<div class="panel"><p class="status is-error">No full card came back for this id.</p></div>';
    return;
  }

  state.item = buildDraft(table, full);
  // Prefetch source owners into SN.owners before / while the form paints
  if (state.item.sources && state.item.sources.length) {
    resolveOwners(state.item.sources.map(s => s.user).filter(Boolean)).catch(() => {});
  }
  renderEditor();
}

/** Collect everything the user typed into the draft. */
function collectDraft() {
  const d = state.item;
  const nameEl = $('#fName');
  if (nameEl) d.name = nameEl.value.trim();

  const names = {};
  $$('#nameStack [data-locale-row]').forEach(row => {
    const code = row.querySelector('[data-lang]').value.trim();
    const val  = row.querySelector('[data-name-val]').value.trim();
    if (code && val) names[code] = val;
  });
  d.names = names;

  const des = $('#fDes');   if (des) d.des = des.value.trim();
  const lan = $('#fLan');   if (lan) d.lan = lan.value;
  const durEl = $('#fDur');
  if (durEl) {
    const v = Number(durEl.value);
    d.dur = (durEl.value !== '' && isFinite(v) && v > 0) ? Math.round(v) : 0;
  }
  const act = $('#fAct');   if (act) d.act = Number(act.value);

  // artist profile
  const cc = $('#fCc');   if (cc) d.cc = cc.value.trim().toUpperCase();
  const ytm = $('#fYtm'); if (ytm) d.ytm = ytm.value.trim();
  const ins = $('#fIns'); if (ins) d.ins = ins.value.trim();
  const tlg = $('#fTlg'); if (tlg) d.tlg = tlg.value.trim();
  // lyrics stay in d.lyrics (managed by local Save); do not wipe on collect

  // sources (link_song rows) — UI draft for upcoming PHP
  const sources = [];
  $$('#srcList [data-src-row]').forEach(row => {
    const urlEl = row.querySelector('[data-src-url]');
    const bitEl = row.querySelector('[data-src-bit]');
    const userEl = row.querySelector('[data-src-user]');
    const url = urlEl ? urlEl.value.trim() : '';
    // Keep empty rows while editing (new source); only drop them on submit payload
    sources.push({
      id  : Number(row.getAttribute('data-src-id')) || 0,
      url : url,
      bit : bitEl && bitEl.value !== '' ? Number(bitEl.value) : 0,
      user: userEl && userEl.value !== '' ? Number(userEl.value) : myUserId(),
      act : Number(row.getAttribute('data-src-row-act')) || 0,
      _key: row.getAttribute('data-src-key') || ''
    });
  });
  if ($('#srcList')) d.sources = sources;

  // album tracks: order in DOM = track number 1..n
  if ($('#trkList') && d.table === 'album') {
    const tracks = [];
    $$('#trkList [data-trk-row]').forEach((row, i) => {
      const id = Number(row.getAttribute('data-trk-id')) || 0;
      if (!id) return;
      const prev = (d.tracks || []).find(x => x.id === id) || {};
      tracks.push({
        id   : id,
        name : prev.name || '',
        nameAlt: prev.nameAlt || '',
        type : row.getAttribute('data-trk-type') || prev.type || 'song',
        url  : prev.url || '',
        thumb: prev.thumb || '',
        artists: prev.artists || '',
        trk  : i + 1
      });
    });
    d.tracks = tracks;
  }

  // media rows: url source + role (off rows are kept so the server can clear them)
  const media = [];
  $$('#mediaList [data-media-row]').forEach(row => {
    const urlEl  = row.querySelector('[data-media-url]');
    const roleEl = row.querySelector('[data-media-role]');
    const u = urlEl ? urlEl.value.trim() : '';
    const role = roleEl ? roleEl.value : 'tmb';
    media.push({ role: role, url: u });
  });
  // Keep raw rows while editing (warnings need them); collapse only for submit/saved view
  d.media = media.length ? media : [{ role: 'tmb', url: '' }];
  const primary = [...media].reverse().find(m => m.role === 'tmb' && m.url);
  d.tmb = primary ? primary.url : '';

  // albums + track numbers from chips
  const albums = [];
  $$('#albumChips [data-album-id]').forEach(chip => {
    const id = Number(chip.getAttribute('data-album-id')) || 0;
    if (!id) return;
    const trkEl = chip.querySelector('[data-album-trk]');
    let trk = trkEl ? Number(trkEl.value) : 0;
    if (isNaN(trk) || trk < 0) trk = 0;
    albums.push({
      id   : id,
      name : chip.getAttribute('data-name') || '',
      thumb: chip.getAttribute('data-thumb') || '',
      trk  : trk
    });
  });
  if (albums.length || $('#albumChips')) d.albums = albums;

  // songs chips (video form + artist form)
  if ($('#songChips')) {
    const songs = [];
    $$('#songChips [data-song-id]').forEach(chip => {
      const id = Number(chip.getAttribute('data-song-id')) || 0;
      if (!id) return;
      songs.push({
        id   : id,
        name : chip.getAttribute('data-name') || '',
        thumb: chip.getAttribute('data-thumb') || '',
        type : 'song'
      });
    });
    d.songs = songs;
  }

  // videos chips
  if ($('#videoChips')) {
    const videos = [];
    $$('#videoChips [data-video-id]').forEach(chip => {
      const id = Number(chip.getAttribute('data-video-id')) || 0;
      if (!id) return;
      videos.push({
        id   : id,
        name : chip.getAttribute('data-name') || '',
        thumb: chip.getAttribute('data-thumb') || ''
      });
    });
    d.videos = videos;
  }

  const y = $('#fYear'), m = $('#fMonth'), day = $('#fDay');
  if (y) {
    const dateStr = y.value ? (y.value + '-' + (m.value || '01') + '-' + (day.value || '01')) : '';
    if (d.table === 'artist') d.bd = dateStr;
    else d.rls = dateStr;
  }
  return d;
}

/** Global name is mandatory for every item (search + identity). */
function validateGlobalName() {
  const el = $('#fName');
  const btn = $('#itemSubmit');
  if (!el) return true;
  const ok = el.value.trim().length > 0;
  el.classList.toggle('is-invalid', !ok);
  if (btn) {
    btn.disabled = !ok;
    btn.classList.toggle('is-disabled', !ok);
  }
  return ok;
}

/** After source play: update Duration field by comparing with previous plays. */
function applyPlayedDuration(sec) {
  sec = Math.round(Number(sec) || 0);
  if (sec <= 0) return;
  const el = $('#fDur');
  const prev = el && el.value !== '' ? Math.round(Number(el.value) || 0) : (state.item && state.item.dur ? Number(state.item.dur) : 0);
  // Prefer the longest consistent length across plays (outliers shorter than 70% of max ignored if max already set)
  let next = sec;
  if (prev > 0) {
    next = Math.max(prev, sec);
    // if new play is within 5% of prev, keep the higher precision (rounded max)
    if (Math.abs(sec - prev) / prev < 0.05) next = Math.max(prev, sec);
  }
  if (state.item) state.item.dur = next;
  if (el) el.value = String(next);
}

/** Submit : the only moment the form talks to the server. */
async function submitItem(event) {
  event.preventDefault();
  if (state.busy) return;

  // Leaving the editor UI after submit — stop any stream still playing
  stopAllSources();

  if (!validateGlobalName()) {
    setItemStatus('The global name is required — form cannot submit without it.', 'error');
    const el = $('#fName');
    if (el) el.focus();
    return;
  }

  const d = collectDraft();
  if (!d.name) {
    setItemStatus('The global name is required.', 'error');
    validateGlobalName();
    const el = $('#fName');
    if (el) el.focus();
    return;
  }

  // Soft warning: duplicate non-gal roles — server keeps the last image per role
  const mediaDups = refreshMediaRoleWarnings();
  if (mediaDups.length) {
    setItemStatus(
      'Warning: duplicate media role(s) ' + mediaDups.join(', ') +
      ' — only the last image for each will be saved. Fix the red selectors if that is not intended.',
      'warn'
    );
  }

  // Payload media matches server rule (last non-gal wins) so the saved card has no duplicates
  const mediaForSave = collapseMediaRows(d.media || []);

  const isCreate = !(Number(d.id) > 0);
  const payload = {
    a      : isCreate ? 'create_item' : 'update_item',
    table  : d.table,
    type   : d.type,
    id     : isCreate ? 0 : d.id,
    name   : d.name,
    names  : d.names,
    des    : d.des,
    url    : d.url,
    lan    : d.lan,
    rd     : d.rls,
    act    : d.act,
    media  : mediaForSave,
    lyrics : (d.lyrics || []).map(x => ({
      lng : x.lng,
      knd : x.knd,
      fmt : x.fmt || 'txt',
      src : x.src || '',
      body: x.body != null ? x.body : ''
    })),
    gnr    : d.gnr,
    artists: d.artists,
    albums : d.albums,
    videos : d.videos,
    songs  : ((d.table === 'video' || d.table === 'artist') && Array.isArray(d.songs))
      ? d.songs.map(s => ({
          id: s.id, name: s.name || '', type: s.type || 'song',
          thumb: s.thumb || '', artists: s.artists || ''
        }))
      : undefined,
    cc     : d.table === 'artist' ? (d.cc || '') : undefined,
    bd     : d.table === 'artist' ? (d.bd || '') : undefined,
    ytm    : d.table === 'artist' ? (d.ytm || '') : undefined,
    ins    : d.table === 'artist' ? (d.ins || '') : undefined,
    tlg    : d.table === 'artist' ? (d.tlg || '') : undefined,
    tracks : (d.table === 'album' && Array.isArray(d.tracks))
      ? d.tracks.map((t, i) => ({
          id  : t.id,
          trk : t.trk > 0 ? t.trk : (i + 1),
          type: t.type || 'song',
          name: t.name || '',
          nameAlt: t.nameAlt || '',
          thumb: t.thumb || '',
          artists: t.artists || ''
        }))
      : undefined,
    sources: (d.sources || []).filter(s => s && String(s.url || '').trim()).map(s => ({
      id  : s.id || 0,
      url : s.url,
      bit : s.bit || 0,
      user: s.user || 0,
      act : (s.act != null) ? s.act : 1
    })),
    dur    : d.dur || 0
  };

  state.busy = true;
  const btn = $('#itemSubmit');
  if (btn) {
    btn.setAttribute('aria-busy', 'true');
    btn.textContent = isCreate ? 'Creating ...' : 'Saving ...';
  }
  setItemStatus(isCreate ? 'Creating item ...' : 'Sending the payload ...', 'info');

  const res = await request('save', null, payload);

  state.busy = false;
  if (btn) { btn.removeAttribute('aria-busy'); }

  if (!res || res.ok !== true) {
    setItemStatus(errorText(res), 'error');
    if (btn) {
      btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>' +
        (isCreate ? 'Create' : 'Submit');
    }
    return;
  }

  // After create: adopt the new public id from the server
  if (isCreate && res.id) {
    payload.id = Number(res.id) || 0;
    d.id = payload.id;
    if (state.item) state.item.id = payload.id;
    // If created from inside another editor, link the new id into the parent draft
    attachCreatedToParent(payload);
  }

/**
 * When Create was opened nested (editStack not empty), push the new item
 * into the parent draft so Cancel/Back already shows the link.
 */
function attachCreatedToParent(created) {
  if (!state.editStack || !state.editStack.length || !created || !created.id) return;
  const parent = state.editStack[state.editStack.length - 1];
  const id = Number(created.id);
  const name = created.name || '';
  const type = created.type || created.table || 'song';
  const thumb = '';

  if (parent.table === 'album' && created.table === 'song') {
    if (!parent.tracks) parent.tracks = [];
    if (!parent.tracks.some(t => t.id === id)) {
      parent.tracks.push({
        id: id, name: name, nameAlt: '', type: type, url: '', thumb: thumb, artists: '', trk: 0
      });
      parent.tracks = renumberTracks(parent.tracks);
    }
  } else if (parent.table === 'artist' && created.table === 'song') {
    if (!parent.songs) parent.songs = [];
    if (!parent.songs.some(s => s.id === id)) {
      parent.songs.push({ id: id, name: name, type: type, thumb: thumb, artists: '' });
    }
  } else if (parent.table === 'artist' && created.table === 'album') {
    if (!parent.albums) parent.albums = [];
    if (!parent.albums.some(a => a.id === id)) {
      parent.albums.push({ id: id, name: name, thumb: thumb, trk: 0 });
    }
  } else if (parent.table === 'artist' && created.table === 'video') {
    if (!parent.videos) parent.videos = [];
    if (!parent.videos.some(v => v.id === id)) {
      parent.videos.push({ id: id, name: name, thumb: thumb, type: type });
    }
  } else if ((parent.table === 'song' || parent.table === 'video' || parent.table === 'album')
      && created.table === 'artist') {
    if (!parent.artists) parent.artists = [];
    if (!parent.artists.some(a => a.id === id)) {
      parent.artists.push({ id: id, name: name, url: '', thumb: thumb });
    }
  } else if (parent.table === 'song' && created.table === 'album') {
    if (!parent.albums) parent.albums = [];
    if (!parent.albums.some(a => a.id === id)) {
      parent.albums.push({ id: id, name: name, thumb: thumb, trk: 0 });
    }
  } else if (parent.table === 'song' && created.table === 'video') {
    if (!parent.videos) parent.videos = [];
    if (!parent.videos.some(v => v.id === id)) {
      parent.videos.push({ id: id, name: name, thumb: thumb });
    }
  } else if (parent.table === 'video' && created.table === 'song') {
    if (!parent.songs) parent.songs = [];
    if (!parent.songs.some(s => s.id === id)) {
      parent.songs.push({ id: id, name: name, type: type, thumb: thumb, artists: '' });
    }
  }
}

  // Prefer server-side media keys (id_img) so the saved card does not keep data: URLs
  if (res && res.changes && Array.isArray(res.changes.media) && res.changes.media.length) {
    const fromServer = [];
    res.changes.media.forEach(m => {
      if (!m || m.error || !m.id_img) return;
      const key = String(m.id_img);
      const url = /^https?:\/\//i.test(key)
        ? key
        : (state.thumbBase + encodeURIComponent(key) + '.jpg');
      fromServer.push({ role: m.role || 'tmb', url: url, id_img: key });
    });
    if (fromServer.length) payload.media = collapseMediaRows(fromServer);
  } else if (payload.media) {
    // Strip huge data URLs from the saved snapshot (preview kept only while editing)
    payload.media = collapseMediaRows(payload.media).map(m => {
      if (m.url && /^data:image\//i.test(m.url)) {
        return { role: m.role, url: m.url, local: true };
      }
      return m;
    });
  }

  state.lastSaved = payload;
  renderSaved(payload, res);
}

/** Read-only track list on the album saved card. */
function savedTracksHtml(d) {
  const rows = Array.isArray(d.tracks) ? d.tracks : [];
  if (!rows.length) return '<span class="hint">No tracks</span>';
  return '<div class="trk-list">' + rows.map((t, i) => {
    const num = (t.trk > 0) ? t.trk : (i + 1);
    const type = t.type || 'song';
    const sub = [t.artists, t.nameAlt].filter(Boolean).join(' · ') || '';
    return '<div class="trk-row">' +
      '<span class="trk-num">' + esc(num) + '</span>' +
      thumbHtml({ name: t.name, thumb: t.thumb }) +
      '<div class="trk-main">' +
        '<div class="trk-title">' + esc(t.name || ('#' + t.id)) + '</div>' +
        '<div class="trk-sub">' +
          '<span class="badge badge-' + esc(type) + '" style="margin-right:6px">' +
            esc(TYPE_LABEL[type] || type) + '</span>' +
          esc(sub || ('#' + t.id)) +
        '</div>' +
      '</div>' +
    '</div>';
  }).join('') + '</div>';
}

/** After a successful submit : show what we just saved, with an Edit button. */
function renderSaved(p, res) {
  const d = payloadToDraft(p);
  const zone = $('#itemZone');
  const staged = res && res.staged === true;
  const isAlbum  = (d.table === 'album');
  const isSong   = (d.table === 'song');
  const isVideo  = (d.table === 'video');
  const isArtist = (d.table === 'artist');
  const trackCount = (Array.isArray(d.tracks) ? d.tracks.length : 0);
  const songCount  = (Array.isArray(d.songs) ? d.songs.length : 0);
  const albumCount = (Array.isArray(d.albums) ? d.albums.length : 0);
  const videoCount = (Array.isArray(d.videos) ? d.videos.length : 0);

  const namesRows = Object.keys(d.names).length
    ? '<div class="saved-names">' + Object.keys(d.names).map(code =>
        '<div class="row"><span class="code">' + esc(code) + '</span><span>' + esc(d.names[code]) + '</span></div>'
      ).join('') + '</div>'
    : '<span>—</span>';

  const actLabel = (ACT_OPTIONS.filter(o => o.v === Number(d.act))[0] || ACT_OPTIONS[1]).label;

  let metaCells =
    '<div class="saved-cell"><b>Id</b><span>#' + esc(d.id) + '</span></div>' +
    '<div class="saved-cell"><b>Status</b><span>' + esc(actLabel) + '</span></div>';

  if (isArtist) {
    metaCells +=
      '<div class="saved-cell"><b>Birth date</b><span>' + esc(d.bd || d.rls || '—') + '</span></div>' +
      '<div class="saved-cell"><b>Nation</b><span>' + esc(countryLabel(d.cc)) + '</span></div>' +
      '<div class="saved-cell"><b>URL slug</b><span>' + esc(d.url || '—') + '</span></div>' +
      '<div class="saved-cell"><b>Songs</b><span>' + esc(songCount) + '</span></div>' +
      '<div class="saved-cell"><b>Albums</b><span>' + esc(albumCount) + '</span></div>' +
      '<div class="saved-cell"><b>Videos</b><span>' + esc(videoCount) + '</span></div>';
  } else {
    metaCells +=
      '<div class="saved-cell"><b>Release date</b><span>' + esc(d.rls || '—') + '</span></div>' +
      '<div class="saved-cell"><b>Language</b><span>' + esc(d.lan || '—') + '</span></div>' +
      '<div class="saved-cell"><b>URL slug</b><span>' + esc(d.url || '—') + '</span></div>';
  }

  if (isAlbum) {
    metaCells +=
      '<div class="saved-cell"><b>Tracks</b><span>' + esc(trackCount) +
        ' item' + (trackCount === 1 ? '' : 's') + '</span></div>';
  } else if (isSong || isVideo) {
    metaCells +=
      '<div class="saved-cell"><b>Duration</b><span>' +
        (d.dur ? (fmtTime(d.dur) + ' (' + esc(d.dur) + 's)') : '—') +
      '</span></div>' +
      ((d.lyrics && d.lyrics.length)
        ? ''
        : '<div class="saved-cell"><b>Lyric</b><span>none</span></div>');
  }

  metaCells +=
    '<div class="saved-cell"><b>Localized names</b>' + namesRows + '</div>' +
    '<div class="saved-cell" style="grid-column:1/-1"><b>Description</b><span>' +
      esc(d.des || '—') +
    '</span></div>' +
    '<div class="saved-cell" style="grid-column:1/-1"><b>Genres</b>' +
      '<div class="artist-chips genre-chips" style="margin-top:3px">' + genreChipsHtml(d, true) + '</div></div>';

  // Artists chips only for non-artist items
  if (!isArtist) {
    metaCells +=
      '<div class="saved-cell" style="grid-column:1/-1"><b>Artists</b>' +
        '<div class="artist-chips" style="margin-top:3px">' + artistChipsHtml(d, true) + '</div></div>';
  }

  if (isSong) {
    metaCells +=
      '<div class="saved-cell" style="grid-column:1/-1"><b>Albums</b>' +
        '<div class="artist-chips" style="margin-top:3px">' + albumChipsHtml(d, true) + '</div></div>' +
      '<div class="saved-cell" style="grid-column:1/-1"><b>Videos</b>' +
        '<div class="artist-chips" style="margin-top:3px">' + videoChipsHtml(d, true) + '</div></div>' +
      '<div class="saved-cell" style="grid-column:1/-1"><b>Sources</b>' +
        '<div class="saved-media-list" style="margin-top:8px">' + savedSourcesHtml(d) + '</div></div>';
  }

  if (isVideo) {
    metaCells +=
      '<div class="saved-cell" style="grid-column:1/-1"><b>Songs</b>' +
        '<div class="artist-chips" style="margin-top:3px">' + songChipsHtml(d, true) + '</div></div>' +
      '<div class="saved-cell" style="grid-column:1/-1"><b>Sources</b>' +
        '<div class="saved-media-list" style="margin-top:8px">' + savedSourcesHtml(d) + '</div></div>';
  }

  if (isArtist) {
    const socialLine = [
      d.ytm ? ('YouTube: ' + d.ytm) : '',
      d.ins ? ('Instagram: ' + d.ins) : '',
      d.tlg ? ('Telegram: ' + d.tlg) : ''
    ].filter(Boolean).join(' · ') || '—';
    metaCells +=
      '<div class="saved-cell" style="grid-column:1/-1"><b>Social</b><span>' +
        esc(socialLine) +
      '</span></div>';
  }

  metaCells +=
    '<div class="saved-cell" style="grid-column:1/-1"><b>Media</b>' +
      '<div class="saved-media-list" style="margin-top:8px">' + savedMediaHtml(d) + '</div></div>';

  if (isAlbum) {
    metaCells +=
      '<div class="saved-cell" style="grid-column:1/-1"><b>Tracks</b>' +
        '<div style="margin-top:8px">' + savedTracksHtml(d) + '</div></div>';
  }

  if ((isSong || isVideo) && d.lyrics && d.lyrics.length) {
    metaCells +=
      '<div class="saved-cell" style="grid-column:1/-1"><b>Lyrics / subtitles</b>' +
        '<div class="saved-lyric-list" style="margin-top:8px">' + savedLyricsHtml(d) + '</div></div>';
  }

  zone.innerHTML =
    zoneBarHtml(d) +
    '<div class="panel saved-card">' +
      '<div class="saved-head">' + thumbHtml({ name: d.name, thumb: d.tmb }) +
        '<div class="saved-titles">' +
          '<h2>' + esc(d.name || '(untitled)') + '</h2>' +
          (d.names.fa ? '<div class="fa">' + esc(d.names.fa) + '</div>' : '') +
          '<div style="margin-top:8px"><span class="badge badge-' + esc(d.type) + '">' +
            esc(TYPE_LABEL[d.type] || d.type) + '</span></div>' +
        '</div>' +
      '</div>' +
      '<div class="saved-meta">' + metaCells + '</div>' +
      '<div class="editor-foot">' +
        (state.editStack && state.editStack.length
          ? '<button class="btn btn-ghost" type="button" data-back-parent>' +
              'Back to ' + esc((state.editStack[state.editStack.length - 1].name) || 'parent') +
            '</button>'
          : '<button class="btn btn-ghost" type="button" data-zone-back>Back to search</button>') +
        '<button class="btn btn-accent" type="button" id="reEditBtn">' +
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M14.5 5.5l4 4"/></svg>' +
          'Edit</button>' +
      '</div>' +
    '</div>' +
    '<p class="status" id="itemStatus" role="status">' +
      (staged
        ? 'Payload accepted by the gateway (staged — the upstream update action is not wired in ha.php yet). This is exactly what would be written.'
        : 'Saved.') +
    '</p>';

  showItemZone();
  window.scrollTo({ top: 0, behavior: 'smooth' });

  const re = $('#reEditBtn');
  if (re) re.addEventListener('click', () => {
    state.item = payloadToDraft(p);
    renderEditor();
  });
}

/**
 * Artist suggestions for the picker.
 * The upstream search keeps its own "artists" bucket almost always empty
 * (guest filter in ha.php), so we also harvest every artist object embedded
 * in the matched albums / songs / videos / playlists, plus a direct id lookup.
 */
async function artistSuggestions(query) {
  const seen = {};
  const out  = [];
  const push = a => {
    const id = Number(a && a.id) || 0;
    if (!id || seen[id]) return;
    seen[id] = true;
    out.push({
      type  : 'artist',
      table : 'artist',
      id    : id,
      name  : (a && a.name) || '(untitled)',
      nameAlt: namesAltFromItem(a),
      url   : (a && a.url) || '',
      thumb : (a && (a.thumb || (a.meta && a.meta.tmb))) ? String(a.thumb || a.meta.tmb) : '',
      raw   : a
    });
  };

  // 1) exact id (public or raw)
  if (/^\d+$/.test(query)) {
    const hits = await resolveTables(['artist'], query);
    hits.forEach(push);
    if (!out.length && state.idOffset) {
      const more = await resolveTables(['artist'], Number(query) + state.idOffset);
      more.forEach(push);
    }
  }

  // 2) text search, harvesting artists embedded in the other buckets
  const data = await queryGet({ qjs: 'ep', p: 'search', q1: query });
  if (data && !data.error && data.search) {
    const box = data.search;
    (box.artists || []).forEach(push);
    ['albums', 'songs', 'videos', 'playlists'].forEach(key => {
      (box[key] || []).forEach(item => (item.artists || []).forEach(push));
    });
  }
  return out;
}

async function albumSuggestions(query) {
  const seen = {};
  const out  = [];
  const push = a => {
    const id = Number(a && a.id) || 0;
    if (!id || seen[id]) return;
    seen[id] = true;
    out.push({
      type  : 'album',
      table : 'album',
      id    : id,
      name  : (a && a.name) || '(untitled)',
      nameAlt: namesAltFromItem(a),
      url   : (a && a.url) || '',
      thumb : (a && (a.thumb || (a.meta && a.meta.tmb))) ? String(a.thumb || a.meta.tmb) : '',
      raw   : a
    });
  };

  if (/^\d+$/.test(query)) {
    const hits = await resolveTables(['album'], query);
    hits.forEach(push);
    if (!out.length && state.idOffset) {
      const more = await resolveTables(['album'], Number(query) + state.idOffset);
      more.forEach(push);
    }
  }

  const data = await queryGet({ qjs: 'ep', p: 'search', q1: query });
  if (data && !data.error && data.search) {
    (data.search.albums || []).forEach(push);
  }
  return out;
}

async function videoSuggestions(query) {
  const seen = {};
  const out  = [];
  const push = v => {
    const id = Number(v && v.id) || 0;
    if (!id || seen[id]) return;
    seen[id] = true;
    out.push({
      type  : 'video',
      table : 'video',
      id    : id,
      name  : (v && v.name) || '(untitled)',
      nameAlt: namesAltFromItem(v),
      url   : (v && v.url) || '',
      thumb : (v && (v.thumb || (v.meta && v.meta.tmb))) ? String(v.thumb || v.meta.tmb) : '',
      raw   : v
    });
  };

  if (/^\d+$/.test(query)) {
    const hits = await resolveTables(['video'], query);
    hits.forEach(push);
    if (!out.length && state.idOffset) {
      const more = await resolveTables(['video'], Number(query) + state.idOffset);
      more.forEach(push);
    }
  }

  const data = await queryGet({ qjs: 'ep', p: 'search', q1: query });
  if (data && !data.error && data.search) {
    (data.search.videos || []).forEach(push);
  }
  return out;
}

/* ---- editor bindings (re-run after every render) ---- */
let artistSearchTimer = null;
let albumSearchTimer  = null;
let videoSearchTimer  = null;
let genreSearchTimer  = null;
let genrePinned       = null;   // exact match currently pinned in the list

/**
 * Genre list : filtered LOCALLY against SN.gnr (from ha_gnr).
 * No request to the main site happens while typing.
 */
function genreSuggestions(query) {
  const q = String(query || '').trim().toLowerCase();
  if (!q) return Promise.resolve([]);
  const score = g => {
    const n = (g.name || '').toLowerCase();
    const s = (g.slug || '').toLowerCase();
    if (n === q || s === q) return 0;              // exact match -> pinned row
    if (n.indexOf(q) === 0 || s.indexOf(q) === 0) return 1;
    return 2;
  };
  const out = taxGenres()
    .filter(g => (g.name && g.name.toLowerCase().indexOf(q) !== -1) ||
                 (g.slug && g.slug.toLowerCase().indexOf(q) !== -1))
    .sort((a, b) => score(a) - score(b) || String(a.name).localeCompare(String(b.name)));
  return Promise.resolve(out);
}

/* --------------------------------------------------------------------------
 *  Taxonomy + profile cache  (keys match the main app exactly)
 *    ha_lan      – languages   [?_qjs=lan]
 *    ha_gnr      – genres      [?_qjs=gnr]
 *    ha_profile  – user profile (LoadProfile / SaveProfile, same shape as ha)
 *
 *  Strategy (stale-while-revalidate):
 *    1. boot  → hydrateTaxonomy() reads localStorage into SN + state (0 network)
 *    2. boot  → ensureTaxonomy() hits the API in background and updates
 *               SN + localStorage when the answer arrives
 *    3. UI    → taxLangs() / taxGenres() always read from SN → instant
 * ------------------------------------------------------------------------ */
const TAX_KEY_LAN = 'ha_lan';
const TAX_KEY_GNR = 'ha_gnr';
const TAX_KEY_PROFILE = 'ha_profile';

function taxRead(key) {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return null;
    const val = JSON.parse(raw);
    return (Array.isArray(val) && val.length) ? val : null;
  } catch (e) { return null; }
}

function taxWrite(key, val) {
  try { localStorage.setItem(key, JSON.stringify(val)); } catch (e) {}  // quota / private mode
}

/* ---- ha_profile (same shape and key as the main app) -------------------- */
const ITEM_TYPES = ['song', 'podcast', 'musicvideo', 'video', 'album', 'artist', 'playlist'];

function canUseLocalStorage() {
  try {
    const t = '__hrh_t__';
    localStorage.setItem(t, t);
    localStorage.removeItem(t);
    return true;
  } catch (e) { return false; }
}
const HAS_LOCAL = canUseLocalStorage();

/**
 * LoadProfile – identical contract to ha.php.
 * Returns SN.profile and mirrors it on state.profile.
 */
function LoadProfile() {
  if (typeof window.SN !== 'object' || window.SN === null) window.SN = {};
  if (window.SN.profile) {
    state.profile = window.SN.profile;
    return window.SN.profile;
  }

  let data = null;
  if (HAS_LOCAL) {
    try {
      const raw = localStorage.getItem(TAX_KEY_PROFILE);
      if (raw) data = JSON.parse(raw);
    } catch (e) {}
  }

  const emptyFavorite = {};
  for (const t of ITEM_TYPES) emptyFavorite[t] = [];

  window.SN.profile = {
    favorite: emptyFavorite,
    playlist: [],
    history: [],              // [{ id, type, at }]
    search: [],               // ['query', ...] or [{ q, at }]
    playlistEntities: {}
  };

  if (data && data.favorite) {
    for (const t of ITEM_TYPES) {
      if (Array.isArray(data.favorite[t])) {
        window.SN.profile.favorite[t] = data.favorite[t];
      }
    }
  }

  if (!window.SN.playlist || typeof window.SN.playlist !== 'object') {
    window.SN.playlist = {};
  }

  if (data && data.playlistEntities && typeof data.playlistEntities === 'object') {
    window.SN.profile.playlistEntities = data.playlistEntities;
    Object.keys(data.playlistEntities).forEach(id => {
      window.SN.playlist[id] = data.playlistEntities[id];
    });
  }

  if (Array.isArray(data && data.playlist)) {
    window.SN.profile.playlist = data.playlist;
  }

  if (Array.isArray(data && data.history)) {
    window.SN.profile.history = data.history
      .map(row => {
        if (row == null) return null;
        if (typeof row === 'object' && row.id != null) {
          return { id: row.id, type: row.type || 'song', at: row.at || 0 };
        }
        return { id: row, type: 'song', at: 0 };
      })
      .filter(Boolean);
  }

  if (Array.isArray(data && data.search)) {
    window.SN.profile.search = data.search;
  }

  state.profile = window.SN.profile;
  return window.SN.profile;
}

/**
 * SaveProfile – write SN.profile back to localStorage under ha_profile.
 */
function SaveProfile() {
  if (!window.SN || !window.SN.profile || !HAS_LOCAL) return;

  // keep playlistEntities in sync (same as ha)
  window.SN.profile.playlistEntities = {};
  (window.SN.profile.playlist || []).forEach(id => {
    if (window.SN.playlist && window.SN.playlist[id]) {
      window.SN.profile.playlistEntities[id] = window.SN.playlist[id];
    }
  });

  try {
    localStorage.setItem(TAX_KEY_PROFILE, JSON.stringify(window.SN.profile));
  } catch (e) {
    console.warn('SaveProfile failed:', e && e.message);
  }
  state.profile = window.SN.profile;
}

/** Wipe local profile (explicit reset only; logout keeps it by default). */
function ResetLocalProfile() {
  try { localStorage.removeItem(TAX_KEY_PROFILE); } catch (e) {}
  if (window.SN) window.SN.profile = null;
  state.profile = null;
  LoadProfile();
  SaveProfile();
}

/** Synchronous hydration at boot : localStorage only, zero network. */
function hydrateTaxonomy() {
  if (typeof window.SN !== 'object' || window.SN === null) window.SN = {};

  const lan = taxRead(TAX_KEY_LAN);
  const gnr = taxRead(TAX_KEY_GNR);

  state.langs = lan || [];
  state.gnrs  = gnr || [];
  window.SN.lan = state.langs;
  window.SN.gnr = state.gnrs;

  // ha_profile – always materialise an empty-or-cached profile in SN
  LoadProfile();
}

/**
 * Fetch lan + gnr from the main API and write them into SN + localStorage.
 * @param {boolean} force  when true, always hit the network even if SN is full
 */
async function ensureTaxonomy(force) {
  if (typeof window.SN !== 'object' || window.SN === null) window.SN = {};
  const needLan = force || !(window.SN.lan && window.SN.lan.length);
  const needGnr = force || !(window.SN.gnr && window.SN.gnr.length);
  if (!needLan && !needGnr) return;

  const jobs = [];

  jobs.push(needLan
    ? queryGet({ qjs: 'lan' }).then(r => {
        if (!r || r.error || !Array.isArray(r.lan)) return null;
        const rows = r.lan.filter(x => x && x.code)
          .map(x => [x.code, (x.data && x.data.des) || x.name || x.code]);
        if (rows.length) {
          taxWrite(TAX_KEY_LAN, rows);
          state.langs = rows;
          if (window.SN) window.SN.lan = rows;
        }
        return rows.length ? rows : null;
      }).catch(() => null)
    : Promise.resolve(null));

  jobs.push(needGnr
    ? queryGet({ qjs: 'gnr' }).then(r => {
        if (!r || r.error || !Array.isArray(r.gnr)) return null;
        const rows = r.gnr.filter(x => x && x.id)
          .map(x => [Number(x.id) || 0, x.slug || '', x.name || '']);
        if (rows.length) {
          taxWrite(TAX_KEY_GNR, rows);
          state.gnrs = rows;
          if (window.SN) window.SN.gnr = rows;
        }
        return rows.length ? rows : null;
      }).catch(() => null)
    : Promise.resolve(null));

  await Promise.all(jobs);
  // when either failed we keep whatever is already in SN / state
}

function addGenre(g) {
  if (!g || !g.id || !state.item) return false;
  if (!state.item.gnr) state.item.gnr = [];
  if (state.item.gnr.some(x => x.id === g.id)) return false;
  collectDraft();
  state.item.gnr.push({ id: g.id, name: g.name || ('#' + g.id), slug: g.slug || '' });
  const box = $('#genreChips');
  if (box) box.innerHTML = genreChipsHtml(state.item, false);
  const inp = $('#genreSearch'), picks = $('#genrePicks');
  if (inp) inp.value = '';
  if (picks) picks.innerHTML = '';
  genrePinned = null;
  if (inp) inp.focus();
  return true;
}

function renderGenrePicks(rows, query) {
  const box = $('#genrePicks');
  if (!box) return;
  if (!rows) {
    box.innerHTML = '<div class="hint">The genre list is not available right now.</div>';
    return;
  }
  const have = (state.item.gnr || []).map(g => g.id);
  const fresh = rows.filter(g => have.indexOf(g.id) === -1);
  genrePinned = fresh.find(g => g.name.toLowerCase() === query.toLowerCase()) || null;
  if (!fresh.length) {
    box.innerHTML = '<div class="hint">' +
      (query ? 'No genre matches "' + esc(query) + '".' : 'All genres are already attached.') + '</div>';
    return;
  }
  box.innerHTML = fresh.slice(0, 8).map(g => {
    const on = genrePinned && genrePinned.id === g.id;
    return '<button class="pick' + (on ? ' is-on' : '') + '" type="button" data-genre-pick="' +
      esc(g.id) + '" data-name="' + esc(g.name) + '" data-slug="' + esc(g.slug) + '">' +
      '<b>' + esc(g.name) + '</b>' +
      (g.slug ? '<span class="rid">' + esc(g.slug) + '</span>' : '') +
      (on ? '<span class="rid pin-flag">pinned</span>' : '') +
    '</button>';
  }).join('');
}

function bindEditor() {
  const form = $('#itemForm');
  if (form) form.addEventListener('submit', submitItem);

  // Global name required: red border + disable Submit while empty
  const nameEl = $('#fName');
  if (nameEl) {
    nameEl.addEventListener('input', validateGlobalName);
    nameEl.addEventListener('blur', validateGlobalName);
    validateGlobalName();
  }

  const add = $('#nameAdd');
  if (add) add.addEventListener('click', () => {
    const used = $$('#nameStack [data-locale-row] select').map(s => s.value);
    const next = (taxLangs().filter(l => used.indexOf(l.code) === -1)[0] || taxLangs()[0]).code;
    $('#nameStack').insertAdjacentHTML('beforeend', nameLineHtml(next, ''));
    const rows = $$('#nameStack [data-locale-row]');
    const last = rows[rows.length - 1];
    if (last) { const inp = last.querySelector('[data-name-val]'); if (inp) inp.focus(); }
  });

  const zone = $('#itemZone');
  const search = $('#artistSearch');
  if (search) search.addEventListener('input', () => {
    clearTimeout(artistSearchTimer);
    const q = search.value.trim();
    const box = $('#artistPicks');
    if (!box) return;
    if (q.length < 2) { box.innerHTML = ''; return; }
    artistSearchTimer = setTimeout(async () => {
      box.innerHTML = '<div class="hint">Searching ...</div>';
      const rows = await artistSuggestions(q);
      const have = (state.item.artists || []).map(a => a.id);
      const fresh = rows.filter(r => have.indexOf(r.id) === -1).slice(0, 6);
      const box2 = $('#artistPicks');
      if (!box2) return;
      box2.innerHTML = fresh.length
        ? fresh.map(r =>
            '<button class="pick" type="button" data-pick="' + esc(r.id) + '" data-name="' + esc(r.name) +
              '" data-thumb="' + esc(r.thumb || '') + '">' +
              thumbHtml(r) + '<b>' + esc(r.name) + '</b>' +
              (r.nameAlt ? '<span class="fa">' + esc(r.nameAlt) + '</span>' : '') +
              '<span class="rid">#' + esc(r.id) + '</span>' +
            '</button>'
          ).join('')
        : '<div class="hint">No artist found for "' + esc(q) + '". ' +
          'Tip: a numeric artist id (public or raw) always resolves.</div>';
    }, 320);
  });

  // album search (same pattern as artists)
  const aSearch = $('#albumSearch');
  if (aSearch) aSearch.addEventListener('input', () => {
    clearTimeout(albumSearchTimer);
    const q = aSearch.value.trim();
    const box = $('#albumPicks');
    if (!box) return;
    if (q.length < 2 && !/^\d+$/.test(q)) { box.innerHTML = ''; return; }
    albumSearchTimer = setTimeout(async () => {
      box.innerHTML = '<div class="hint">Searching ...</div>';
      const rows = await albumSuggestions(q);
      const have = (state.item.albums || []).map(a => a.id);
      const fresh = rows.filter(r => have.indexOf(r.id) === -1).slice(0, 6);
      const box2 = $('#albumPicks');
      if (!box2) return;
      box2.innerHTML = fresh.length
        ? fresh.map(r =>
            '<button class="pick" type="button" data-album-pick="' + esc(r.id) + '" data-name="' + esc(r.name) +
              '" data-thumb="' + esc(r.thumb || '') + '">' +
              thumbHtml(r) + '<b>' + esc(r.name) + '</b>' +
              (r.nameAlt ? '<span class="fa">' + esc(r.nameAlt) + '</span>' : '') +
              '<span class="rid">#' + esc(r.id) + '</span>' +
            '</button>'
          ).join('')
        : '<div class="hint">No album found for "' + esc(q) + '". ' +
          'Tip: a numeric album id (public or raw) always resolves.</div>';
    }, 320);
  });

  // video search (music videos for song2video)
  const vSearch = $('#videoSearch');
  if (vSearch) vSearch.addEventListener('input', () => {
    clearTimeout(videoSearchTimer);
    const q = vSearch.value.trim();
    const box = $('#videoPicks');
    if (!box) return;
    if (q.length < 2 && !/^\d+$/.test(q)) { box.innerHTML = ''; return; }
    videoSearchTimer = setTimeout(async () => {
      box.innerHTML = '<div class="hint">Searching ...</div>';
      const rows = await videoSuggestions(q);
      const have = (state.item.videos || []).map(v => v.id);
      const fresh = rows.filter(r => have.indexOf(r.id) === -1).slice(0, 6);
      const box2 = $('#videoPicks');
      if (!box2) return;
      box2.innerHTML = fresh.length
        ? fresh.map(r =>
            '<button class="pick" type="button" data-video-pick="' + esc(r.id) + '" data-name="' + esc(r.name) +
              '" data-thumb="' + esc(r.thumb || '') + '">' +
              thumbHtml(r) + '<b>' + esc(r.name) + '</b>' +
              (r.nameAlt ? '<span class="fa">' + esc(r.nameAlt) + '</span>' : '') +
              '<span class="rid">#' + esc(r.id) + '</span>' +
            '</button>'
          ).join('')
        : '<div class="hint">No video found for "' + esc(q) + '". ' +
          'Tip: a numeric video id (public or raw) always resolves.</div>';
    }, 320);
  });

  // genre type-ahead : filter char-by-char, exact match pins the row
  const gSearch = $('#genreSearch');
  if (gSearch) gSearch.addEventListener('input', () => {
    clearTimeout(genreSearchTimer);
    const q = gSearch.value.trim();
    if (!q) { const b = $('#genrePicks'); if (b) b.innerHTML = ''; genrePinned = null; return; }
    genreSearchTimer = setTimeout(async () => {
      const box = $('#genrePicks');
      if (box) box.innerHTML = '<div class="hint">Filtering ...</div>';
      const rows = await genreSuggestions(q);
      if (!$('#genreSearch') || $('#genreSearch').value.trim() !== q) return; // stale
      renderGenrePicks(rows, q);
    }, 120);
  });
  if (gSearch) gSearch.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
      event.preventDefault();
      commitGenre();
    }
  });
  const gAdd = $('#genreAdd');
  if (gAdd) gAdd.addEventListener('click', commitGenre);

  bindMediaEditor();
  bindSourcesEditor();
  bindTrackSearch();
  bindSongLinkSearch();
}

let songLinkSearchTimer = null;
function bindSongLinkSearch() {
  const inp = $('#songSearch');
  if (!inp || inp.dataset.bound === '1') return;
  inp.dataset.bound = '1';
  inp.addEventListener('input', () => {
    clearTimeout(songLinkSearchTimer);
    const q = inp.value.trim();
    const box = $('#songPicks');
    if (!box) return;
    if (q.length < 2 && !/^\d+$/.test(q)) { box.innerHTML = ''; return; }
    songLinkSearchTimer = setTimeout(async () => {
      box.innerHTML = '<div class="hint">Searching ...</div>';
      const rows = await songTrackSuggestions(q);
      const have = (state.item && state.item.songs || []).map(s => s.id);
      const fresh = rows.filter(r => have.indexOf(r.id) === -1).slice(0, 8);
      const box2 = $('#songPicks');
      if (!box2) return;
      box2.innerHTML = fresh.length
        ? fresh.map(r =>
            '<button class="pick" type="button" data-song-pick="' + esc(r.id) +
              '" data-name="' + esc(r.name) + '" data-thumb="' + esc(r.thumb || '') +
              '" data-type="' + esc(r.type) + '" data-artists="' + esc(r.artists || '') + '">' +
              thumbHtml(r) + '<b>' + esc(r.name) + '</b>' +
              '<span class="rid">#' + esc(r.id) + '</span>' +
            '</button>'
          ).join('')
        : '<div class="hint">No song found for "' + esc(q) + '".</div>';
    }, 320);
  });
}

let trackSearchTimer = null;
async function songTrackSuggestions(query) {
  const seen = {};
  const out = [];
  const push = s => {
    const id = Number(s && s.id) || 0;
    if (!id || seen[id]) return;
    seen[id] = true;
    const type = (s.meta && s.meta.type === 'podcast') ? 'podcast'
      : (s.type === 'podcast' ? 'podcast' : 'song');
    out.push({
      id: id,
      name: (s && s.name) || '(untitled)',
      nameAlt: namesAltFromItem(s),
      type: type,
      thumb: (s && (s.thumb || (s.meta && s.meta.tmb))) ? String(s.thumb || s.meta.tmb) : '',
      artists: Array.isArray(s.artists) ? s.artists.map(a => a.name).filter(Boolean).join(', ') : ''
    });
  };
  if (/^\d+$/.test(query)) {
    const hits = await resolveTables(['song'], query);
    hits.forEach(r => push(r.item || r));
    if (!out.length && state.idOffset) {
      const more = await resolveTables(['song'], Number(query) + state.idOffset);
      more.forEach(r => push(r.item || r));
    }
  }
  const data = await queryGet({ qjs: 'ep', p: 'search', q1: query });
  if (data && !data.error && data.search) {
    (data.search.songs || []).forEach(push);
  }
  return out;
}

function bindTrackSearch() {
  const inp = $('#trackSearch');
  if (!inp || inp.dataset.bound === '1') return;
  inp.dataset.bound = '1';
  inp.addEventListener('input', () => {
    clearTimeout(trackSearchTimer);
    const q = inp.value.trim();
    const box = $('#trackPicks');
    if (!box) return;
    if (q.length < 2 && !/^\d+$/.test(q)) { box.innerHTML = ''; return; }
    trackSearchTimer = setTimeout(async () => {
      box.innerHTML = '<div class="hint">Searching ...</div>';
      const rows = await songTrackSuggestions(q);
      const have = (state.item && state.item.tracks || []).map(t => t.id);
      const fresh = rows.filter(r => have.indexOf(r.id) === -1).slice(0, 8);
      const box2 = $('#trackPicks');
      if (!box2) return;
      box2.innerHTML = fresh.length
        ? fresh.map(r =>
            '<button class="pick" type="button" data-trk-pick="' + esc(r.id) +
              '" data-name="' + esc(r.name) + '" data-thumb="' + esc(r.thumb || '') +
              '" data-type="' + esc(r.type) + '" data-artists="' + esc(r.artists || '') + '">' +
              thumbHtml(r) + '<b>' + esc(r.name) + '</b>' +
              (r.nameAlt ? '<span class="fa">' + esc(r.nameAlt) + '</span>' : '') +
              '<span class="rid">#' + esc(r.id) + '</span>' +
            '</button>'
          ).join('')
        : '<div class="hint">No song found for "' + esc(q) + '".</div>';
    }, 320);
  });
}

/** Sources section: play/pause (resume), seek — Audio for song, Video for video table. */
let srcAudio = null; // HTMLMediaElement (Audio or Video)
let srcPlayingKey = null;

function makeSourceMedia() {
  if (state.item && state.item.table === 'video') {
    const v = document.createElement('video');
    v.preload = 'metadata';
    v.playsInline = true;
    v.muted = false;
    // small off-DOM or attach to preview slot on play
    return v;
  }
  const a = new Audio();
  a.preload = 'metadata';
  return a;
}

const SRC_ICON_PLAY = '<path d="M8 5v14l11-7z"/>';
const SRC_ICON_PAUSE = '<path d="M6 5h4v14H6zm8 0h4v14h-4z"/>';

function setSrcPlayIcon(btn, playing) {
  if (!btn) return;
  const icon = btn.querySelector('[data-src-icon]');
  if (icon) icon.innerHTML = playing ? SRC_ICON_PAUSE : SRC_ICON_PLAY;
  btn.classList.toggle('is-on', !!playing);
}

function clearSrcErrors() {
  $$('#srcList [data-src-err]').forEach(el => {
    el.hidden = true;
    el.textContent = '';
  });
}

function showSrcError(row, msg) {
  if (!row) return;
  const err = row.querySelector('[data-src-err]');
  const play = row.querySelector('[data-src-play]');
  if (err) {
    err.textContent = msg || 'Error';
    err.hidden = false;
  }
  // Never disable play — user can retry any time
  if (play) setSrcPlayIcon(play, false);
}

/** Encode spaces in path so streams like ".../Arman Garshasbi - x.mp3" play. */
function normalizeAudioUrl(url) {
  const u = String(url || '').trim();
  if (!u) return u;
  try {
    const parsed = new URL(u);
    parsed.pathname = parsed.pathname.split('/').map(seg => {
      try { return encodeURIComponent(decodeURIComponent(seg)); }
      catch (e) { return encodeURIComponent(seg); }
    }).join('/');
    return parsed.toString();
  } catch (e) {
    return u.replace(/ /g, '%20');
  }
}

function bindSourcesEditor() {
  const list = $('#srcList');
  if (!list) return;
  // avoid double-binding if section is re-rendered then rebound
  if (list.dataset.bound === '1') return;
  list.dataset.bound = '1';

  const stopSrc = (keepProgKey) => {
    if (srcAudio) {
      try { srcAudio.pause(); } catch (e) {}
      srcAudio = null;
    }
    const prevKey = srcPlayingKey;
    srcPlayingKey = null;
    $$('#srcList .src-play').forEach(b => setSrcPlayIcon(b, false));
    $$('#srcList [data-src-prog]').forEach(p => {
      const row = p.closest('[data-src-row]');
      const k = row ? row.getAttribute('data-src-key') : '';
      if (keepProgKey && k === keepProgKey) return;
      if (prevKey && k === prevKey && keepProgKey === prevKey) return;
      p.classList.remove('is-on');
    });
  };

  const seekFromEvent = (bar, event) => {
    if (!srcAudio || !isFinite(srcAudio.duration) || srcAudio.duration <= 0) return;
    const row = bar.closest('[data-src-row]');
    if (!row || row.getAttribute('data-src-key') !== srcPlayingKey) return;
    const rect = bar.getBoundingClientRect();
    const clientX = (event.touches && event.touches[0]) ? event.touches[0].clientX : event.clientX;
    const ratio = Math.min(1, Math.max(0, (clientX - rect.left) / rect.width));
    srcAudio.currentTime = ratio * srcAudio.duration;
    event.preventDefault();
    event.stopPropagation();
  };

  list.addEventListener('click', event => {
    // Seek must run before anything else and must not bubble
    const bar = event.target.closest('[data-src-bar]');
    if (bar) {
      seekFromEvent(bar, event);
      return;
    }

    const playBtn = event.target.closest('[data-src-play]');
    if (playBtn) {
      event.preventDefault();
      const row = playBtn.closest('[data-src-row]');
      if (!row || playBtn.disabled) return;
      const urlEl = row.querySelector('[data-src-url]');
      const url = urlEl ? urlEl.value.trim() : '';
      if (!url) return;
      const key = row.getAttribute('data-src-key');
      const errEl = row.querySelector('[data-src-err]');
      if (errEl) { errEl.hidden = true; errEl.textContent = ''; }

      // Same row: toggle pause / resume (do not recreate Audio → keeps position)
      if (srcPlayingKey === key && srcAudio) {
        if (!srcAudio.paused) {
          srcAudio.pause();
          setSrcPlayIcon(playBtn, false);
        } else {
          srcAudio.play().then(() => setSrcPlayIcon(playBtn, true)).catch(() => {
            showSrcError(row, 'Play failed');
          });
        }
        return;
      }

      // Different row: stop previous, start new from 0
      stopSrc();
      clearSrcErrors();
      $$('#srcList [data-src-vprev]').forEach(h => { h.hidden = true; h.innerHTML = ''; });
      srcAudio = makeSourceMedia();
      // Do NOT set crossOrigin — it forces CORS and breaks many valid public streams
      srcAudio.src = normalizeAudioUrl(url);
      if (srcAudio.tagName === 'VIDEO') {
        const host = row.querySelector('[data-src-vprev]');
        if (host) {
          host.innerHTML = '';
          srcAudio.controls = false;
          srcAudio.style.cssText = 'max-width:240px;max-height:135px;border-radius:10px;display:block;margin-top:6px;background:#000';
          host.appendChild(srcAudio);
          host.hidden = false;
        }
      }
      srcPlayingKey = key;
      setSrcPlayIcon(playBtn, true);
      const prog = row.querySelector('[data-src-prog]');
      const fill = row.querySelector('[data-src-fill]');
      const timeEl = row.querySelector('[data-src-time]');
      if (prog) prog.classList.add('is-on');

      srcAudio.addEventListener('loadedmetadata', () => {
        if (!srcAudio) return;
        if (timeEl) timeEl.textContent = '0:00 / ' + fmtTime(srcAudio.duration);
        if (isFinite(srcAudio.duration) && srcAudio.duration > 0) {
          applyPlayedDuration(srcAudio.duration);
        }
      });
      srcAudio.addEventListener('timeupdate', () => {
        if (!srcAudio || !isFinite(srcAudio.duration) || srcAudio.duration <= 0) return;
        const pct = (srcAudio.currentTime / srcAudio.duration) * 100;
        if (fill) fill.style.width = pct + '%';
        if (timeEl) timeEl.textContent = fmtTime(srcAudio.currentTime) + ' / ' + fmtTime(srcAudio.duration);
      });
      srcAudio.addEventListener('ended', () => {
        setSrcPlayIcon(playBtn, false);
        if (fill) fill.style.width = '0%';
        if (timeEl && srcAudio) timeEl.textContent = '0:00 / ' + fmtTime(srcAudio.duration);
      });
      srcAudio.addEventListener('error', () => {
        showSrcError(row, 'Play failed');
        if (prog) prog.classList.remove('is-on');
        srcPlayingKey = null;
        srcAudio = null;
      });

      srcAudio.play().then(() => setSrcPlayIcon(playBtn, true)).catch(() => {
        showSrcError(row, 'Play failed');
        if (prog) prog.classList.remove('is-on');
        srcPlayingKey = null;
        srcAudio = null;
      });
      return;
    }

    // × = hard remove from form (act9 server DELETEs; act7 soft-offs own missing rows)
    // Deactivate button (data-src-act=0) = keep row, set act=0
    const del = event.target.closest('[data-src-del]');
    if (del) {
      const row = del.closest('[data-src-row]');
      if (!row) return;
      if (row.getAttribute('data-src-key') === srcPlayingKey) stopSrc();
      row.remove();
      collectDraft();
      if (!$('#srcList [data-src-row]')) {
        $('#srcList').innerHTML = '<div class="hint" id="srcEmpty">No sources yet — add a stream URL.</div>';
      }
      return;
    }

    // orig ↔ demo toggle (act 1 ↔ 2); does not touch off/locked
    const kindBtn = event.target.closest('button[data-src-kind]');
    if (kindBtn) {
      const row = kindBtn.closest('[data-src-row]');
      if (!row) return;
      const cur = Number(row.getAttribute('data-src-row-act')) || 1;
      if (cur === 0 || cur === 3) return;
      const next = (cur === 2) ? 1 : 2;
      row.setAttribute('data-src-row-act', String(next));
      collectDraft();
      const box = $('#srcSection');
      if (box && state.item) {
        box.outerHTML = sourcesSectionHtml(state.item);
        const nl = $('#srcList');
        if (nl) nl.dataset.bound = '';
        bindSourcesEditor();
      }
      return;
    }

    // Only the explicit action BUTTONS carry data-src-act (not the row)
    // 0 = off, 1 = original (re-activate), 3 = locked (act9)
    const actBtn = event.target.closest('button[data-src-act]');
    if (actBtn) {
      const row = actBtn.closest('[data-src-row]');
      if (!row) return;
      const next = Number(actBtn.getAttribute('data-src-act'));
      row.setAttribute('data-src-row-act', String(next));
      collectDraft();
      const box = $('#srcSection');
      if (box && state.item) {
        box.outerHTML = sourcesSectionHtml(state.item);
        const nl = $('#srcList');
        if (nl) nl.dataset.bound = '';
        bindSourcesEditor();
      }
      return;
    }
  });

  // Pointer seek (drag) without collapsing the bar
  list.addEventListener('pointerdown', event => {
    const bar = event.target.closest('[data-src-bar]');
    if (!bar) return;
    seekFromEvent(bar, event);
    const move = ev => seekFromEvent(bar, ev);
    const up = () => {
      window.removeEventListener('pointermove', move);
      window.removeEventListener('pointerup', up);
    };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
  });

  list.addEventListener('input', event => {
    const urlEl = event.target.closest('[data-src-url]');
    if (!urlEl) return;
    const row = urlEl.closest('[data-src-row]');
    if (!row) return;
    const play = row.querySelector('[data-src-play]');
    const err = row.querySelector('[data-src-err]');
    if (play) {
      play.disabled = !urlEl.value.trim();
      if (urlEl.value.trim() && err) { err.hidden = true; err.textContent = ''; }
    }
  });

  const add = $('#srcAdd');
  if (add && !add.dataset.bound) {
    add.dataset.bound = '1';
    add.addEventListener('click', () => {
      if (!state.item) return;
      collectDraft();
      if (!state.item.sources) state.item.sources = [];
      state.item.sources.push({
        id: 0,
        url: '',
        bit: 320,
        user: myUserId(),
        act: 1,
        _key: 'n_' + Math.random().toString(36).slice(2, 9)
      });
      const box = $('#srcSection');
      if (box) {
        box.outerHTML = sourcesSectionHtml(state.item);
        const nl = $('#srcList');
        if (nl) nl.dataset.bound = '';
        bindSourcesEditor();
      }
      const last = $('#srcList [data-src-row]:last-child [data-src-url]');
      if (last) last.focus();
    });
  }

  // Owner search (act9 only) — type name/id → pick list from ?_qjs=own
  if (!list.dataset.ownerBound) {
    list.dataset.ownerBound = '1';
    let ownerTimer = null;
    list.addEventListener('input', event => {
      const qEl = event.target.closest('[data-src-owner-q]');
      if (!qEl) return;
      const wrap = qEl.closest('[data-src-owner]');
      const row = qEl.closest('[data-src-row]');
      if (!wrap || !row) return;
      const picks = wrap.querySelector('[data-src-owner-picks]');
      const hid = wrap.querySelector('[data-src-user]');
      const q = qEl.value.trim();

      // Numeric 0 → system
      if (q === '0' || q.toLowerCase() === 'hamrahang' || q.toLowerCase() === 'system') {
        if (hid) hid.value = '0';
        if (picks) { picks.hidden = true; picks.innerHTML = ''; }
        return;
      }
      // Pure public id while typing digits
      if (/^\d+$/.test(q) && q.length >= 1) {
        if (hid) hid.value = String(Number(q) || 0);
      }

      clearTimeout(ownerTimer);
      if (q.length < 1) {
        if (picks) { picks.hidden = true; picks.innerHTML = ''; }
        return;
      }
      ownerTimer = setTimeout(async () => {
        if (picks) {
          picks.hidden = false;
          picks.innerHTML = '<div class="hint">Searching …</div>';
        }
        const rows = await searchOwners(q, 7);
        if (!picks || qEl.value.trim() !== q) return;
        // Always offer System
        let html = '<button class="pick" type="button" data-owner-pick="0" data-owner-name="Hamrahang">' +
          '<b>Hamrahang</b><span class="rid">system · #0</span></button>';
        (rows || []).slice(0, 12).forEach(o => {
          const id = Number(o.id) || 0;
          if (!id) return;
          html += '<button class="pick" type="button" data-owner-pick="' + esc(id) + '" ' +
            'data-owner-name="' + esc(o.name || ('#' + id)) + '">' +
            '<b>' + esc(o.name || ('#' + id)) + '</b>' +
            '<span class="rid">act' + esc(o.act || 0) + ' · #' + esc(id) + '</span></button>';
        });
        if (!(rows && rows.length) && !/^\d+$/.test(q)) {
          html += '<div class="hint">No collaborator match (act≥7).</div>';
        }
        picks.innerHTML = html;
        picks.hidden = false;
      }, 220);
    });

    list.addEventListener('click', event => {
      const pick = event.target.closest('[data-owner-pick]');
      if (!pick) return;
      const wrap = pick.closest('[data-src-owner]');
      if (!wrap) return;
      const id = Number(pick.getAttribute('data-owner-pick')) || 0;
      const name = pick.getAttribute('data-owner-name') || (id ? ('#' + id) : 'Hamrahang');
      const hid = wrap.querySelector('[data-src-user]');
      const qEl = wrap.querySelector('[data-src-owner-q]');
      const picks = wrap.querySelector('[data-src-owner-picks]');
      if (hid) hid.value = String(id);
      if (qEl) qEl.value = name;
      if (picks) { picks.hidden = true; picks.innerHTML = ''; }
      putOwners([{ id: id, name: name, act: id === 0 ? 0 : 7, photo: '' }]);
    });

    // Blur: if pure number left in field, keep it as id
    list.addEventListener('change', event => {
      const qEl = event.target.closest('[data-src-owner-q]');
      if (!qEl) return;
      const wrap = qEl.closest('[data-src-owner]');
      const hid = wrap && wrap.querySelector('[data-src-user]');
      const q = qEl.value.trim();
      if (!hid) return;
      if (q === '' || q === '0' || /^hamrahang$/i.test(q) || /^system$/i.test(q)) {
        hid.value = '0';
        qEl.value = 'Hamrahang';
        return;
      }
      if (/^\d+$/.test(q)) {
        hid.value = String(Number(q) || 0);
        resolveOwners([Number(q)]).then(() => {
          const o = getOwner(Number(q));
          if (o && qEl) qEl.value = ownerDisplayName(o);
        });
      }
    });
  }
}

/** Live preview + upload + add-row for section 2 (Thumb & cover). */
function bindMediaEditor() {
  const list = $('#mediaList');
  if (!list) return;

  list.addEventListener('input', event => {
    const urlEl = event.target.closest('[data-media-url]');
    if (!urlEl) return;
    const row = urlEl.closest('[data-media-row]');
    if (!row) return;
    const box = row.querySelector('[data-media-preview]');
    if (!box) return;
    const val = urlEl.value.trim();
    let img = box.querySelector('img');
    if (!val) {
      if (img) img.remove();
      return;
    }
    if (!img) {
      img = document.createElement('img');
      img.alt = '';
      img.onerror = function () { this.style.display = 'none'; };
      box.appendChild(img);
    }
    img.style.display = '';
    img.src = val;
  });

  list.addEventListener('click', event => {
    const up = event.target.closest('[data-media-upload]');
    if (!up) return;
    const row = up.closest('[data-media-row]');
    if (!row) return;
    const file = row.querySelector('[data-media-file]');
    if (file) file.click();
  });

  list.addEventListener('change', event => {
    const roleEl = event.target.closest('[data-media-role]');
    if (roleEl) {
      refreshMediaRoleWarnings();
      return;
    }
    const fileEl = event.target.closest('[data-media-file]');
    if (!fileEl || !fileEl.files || !fileEl.files[0]) return;
    const row = fileEl.closest('[data-media-row]');
    if (!row) return;
    const file = fileEl.files[0];
    if (!/^image\//.test(file.type)) {
      setItemStatus('Please choose an image file.', 'error');
      return;
    }
    // Cap ~4MB for JSON transport (data URL)
    if (file.size > 4.5 * 1024 * 1024) {
      setItemStatus('Image is larger than 4.5MB — compress it or use a URL.', 'error');
      return;
    }
    const reader = new FileReader();
    reader.onload = () => {
      const urlEl = row.querySelector('[data-media-url]');
      if (urlEl) {
        urlEl.value = reader.result;
        urlEl.dispatchEvent(new Event('input', { bubbles: true }));
      }
    };
    reader.readAsDataURL(file);
  });

  const add = $('#mediaAdd');
  if (add) add.addEventListener('click', () => {
    if (!state.item) return;
    collectDraft();
    if (!state.item.media) state.item.media = [];
    state.item.media.push({ role: 'tmb', url: '' });
    const html = mediaRowHtml({ role: 'tmb', url: '' }, state.item.media.length - 1);
    list.insertAdjacentHTML('beforeend', html);
    refreshMediaRoleWarnings();
  });

  // initial pass after render
  refreshMediaRoleWarnings();
}

/** ⊕ / Enter : commit the pinned (exact) match, or the top row. */
function commitGenre() {
  const inp = $('#genreSearch');
  const q   = inp ? inp.value.trim() : '';
  if (genrePinned) { addGenre(genrePinned); return; }
  const first = $('#genrePicks .pick');
  if (first) {
    addGenre({
      id  : Number(first.getAttribute('data-genre-pick')),
      name: first.getAttribute('data-name') || '',
      slug: first.getAttribute('data-slug') || ''
    });
    return;
  }
  if (q) setItemStatus('Type a genre and pick an exact match first.', 'warn');
}

/* ---- one delegated listener for everything inside the item zone ---- */
function handleZoneClick(event) {
  const t = event.target;

  const back = t.closest('[data-zone-back]');
  if (back) {
    leaveItemZone();
    return;
  }

  const backParent = t.closest('[data-back-parent]');
  if (backParent) {
    leaveItemZone();
    return;
  }

  // Album track: open nested song/podcast editor
  const trkEdit = t.closest('[data-trk-edit]');
  if (trkEdit) {
    const key = trkEdit.getAttribute('data-trk-edit');
    if (key) {
      pushEditStack();
      openItem(key, { nested: true });
    }
    return;
  }

  const trkDel = t.closest('[data-trk-del]');
  if (trkDel && state.item) {
    const id = Number(trkDel.getAttribute('data-trk-del'));
    state.item.tracks = renumberTracks(
      (state.item.tracks || []).filter(x => x.id !== id)
    );
    const box = $('#trkSection');
    if (box) {
      box.outerHTML = tracksSectionHtml(state.item);
      bindTrackSearch();
    }
    return;
  }

  const trkUp = t.closest('[data-trk-up]');
  if (trkUp && state.item) {
    event.preventDefault();
    const row = trkUp.closest('[data-trk-row]');
    if (!row) return;
    const id = Number(row.getAttribute('data-trk-id'));
    const list = (state.item.tracks || []).slice();
    const i = list.findIndex(x => x.id === id);
    if (i > 0) {
      const tmp = list[i - 1];
      list[i - 1] = list[i];
      list[i] = tmp;
      // keep array order — do not re-sort by old trk
      state.item.tracks = renumberTracks(list);
      const box = $('#trkSection');
      if (box) {
        box.outerHTML = tracksSectionHtml(state.item);
        bindTrackSearch();
      }
    }
    return;
  }

  const trkDown = t.closest('[data-trk-down]');
  if (trkDown && state.item) {
    event.preventDefault();
    const row = trkDown.closest('[data-trk-row]');
    if (!row) return;
    const id = Number(row.getAttribute('data-trk-id'));
    const list = (state.item.tracks || []).slice();
    const i = list.findIndex(x => x.id === id);
    if (i >= 0 && i < list.length - 1) {
      const tmp = list[i + 1];
      list[i + 1] = list[i];
      list[i] = tmp;
      state.item.tracks = renumberTracks(list);
      const box = $('#trkSection');
      if (box) {
        box.outerHTML = tracksSectionHtml(state.item);
        bindTrackSearch();
      }
    }
    return;
  }

  const trkPick = t.closest('[data-trk-pick]');
  if (trkPick && state.item) {
    const id = Number(trkPick.getAttribute('data-trk-pick'));
    const nam = trkPick.getAttribute('data-name') || ('#' + id);
    const thumb = trkPick.getAttribute('data-thumb') || '';
    const type = trkPick.getAttribute('data-type') || 'song';
    const artists = trkPick.getAttribute('data-artists') || '';
    if (!state.item.tracks) state.item.tracks = [];
    if (id && !state.item.tracks.some(x => x.id === id)) {
      state.item.tracks.push({
        id: id, name: nam, nameAlt: '', type: type, url: '',
        thumb: thumb, artists: artists, trk: 0
      });
      // new track stays at end; renumber without re-sorting existing order
      state.item.tracks = renumberTracks(state.item.tracks);
    }
    const box = $('#trkSection');
    if (box) {
      box.outerHTML = tracksSectionHtml(state.item);
      bindTrackSearch();
    }
    const s = $('#trackSearch'), pl = $('#trackPicks');
    if (s) s.value = '';
    if (pl) pl.innerHTML = '';
    return;
  }

  const typeBtn = t.closest('[data-type-to]');
  if (typeBtn && state.item) {
    state.item.type = typeBtn.getAttribute('data-type-to');
    $$('#typeSwitch .badge').forEach(b => {
      b.classList.toggle('is-on', b.getAttribute('data-type-to') === state.item.type);
    });
    return;
  }

  const localeDel = t.closest('[data-locale-del]');
  if (localeDel) {
    const row = localeDel.closest('[data-locale-row]');
    if (row) row.remove();
    return;
  }

  const artistDel = t.closest('[data-artist-del]');
  if (artistDel && state.item) {
    const id = Number(artistDel.getAttribute('data-artist-del'));
    state.item.artists = state.item.artists.filter(a => a.id !== id);
    // the chips are re-rendered from the draft (the form itself is untouched)
    collectDraft();
    const box = $('#artistChips');
    if (box) box.innerHTML = artistChipsHtml(state.item);
    return;
  }

  const albumDel = t.closest('[data-album-del]');
  if (albumDel && state.item) {
    const id = Number(albumDel.getAttribute('data-album-del'));
    // Do NOT call collectDraft() before re-render: it re-reads chips from the DOM
    // and would put the deleted album back into state.item.albums.
    state.item.albums = (state.item.albums || []).filter(a => a.id !== id);
    const box = $('#albumChips');
    if (box) box.innerHTML = albumChipsHtml(state.item);
    if ($('#relSection-albums')) refreshRelatedSection('albums');
    return;
  }

  const albumPick = t.closest('[data-album-pick]');
  if (albumPick && state.item) {
    collectDraft();
    if (!state.item.albums) state.item.albums = [];
    const id = Number(albumPick.getAttribute('data-album-pick'));
    const nam = albumPick.getAttribute('data-name') || ('#' + id);
    const thumb = albumPick.getAttribute('data-thumb') || '';
    if (id && !state.item.albums.some(a => a.id === id)) {
      state.item.albums.push({ id: id, name: nam, url: '', thumb: thumb, trk: 0 });
    }
    const box = $('#albumChips');
    if (box) box.innerHTML = albumChipsHtml(state.item);
    if ($('#relSection-albums')) refreshRelatedSection('albums');
    const s = $('#albumSearch'), pl = $('#albumPicks');
    if (s) s.value = '';
    if (pl) pl.innerHTML = '';
    return;
  }

  const videoDel = t.closest('[data-video-del]');
  if (videoDel && state.item) {
    const id = Number(videoDel.getAttribute('data-video-del'));
    state.item.videos = (state.item.videos || []).filter(v => v.id !== id);
    const box = $('#videoChips');
    if (box) box.innerHTML = videoChipsHtml(state.item);
    if ($('#relSection-videos')) refreshRelatedSection('videos');
    return;
  }

  const videoPick = t.closest('[data-video-pick]');
  if (videoPick && state.item) {
    collectDraft();
    if (!state.item.videos) state.item.videos = [];
    const id = Number(videoPick.getAttribute('data-video-pick'));
    const nam = videoPick.getAttribute('data-name') || ('#' + id);
    const thumb = videoPick.getAttribute('data-thumb') || '';
    if (id && !state.item.videos.some(v => v.id === id)) {
      state.item.videos.push({ id: id, name: nam, url: '', thumb: thumb });
    }
    const box = $('#videoChips');
    if (box) box.innerHTML = videoChipsHtml(state.item);
    if ($('#relSection-videos')) refreshRelatedSection('videos');
    const s = $('#videoSearch'), pl = $('#videoPicks');
    if (s) s.value = '';
    if (pl) pl.innerHTML = '';
    return;
  }

  const songDel = t.closest('[data-song-del]');
  if (songDel && state.item) {
    const id = Number(songDel.getAttribute('data-song-del'));
    state.item.songs = (state.item.songs || []).filter(x => x.id !== id);
    const box = $('#songChips');
    if (box) box.innerHTML = songChipsHtml(state.item);
    const rel = $('#relSection-songs');
    if (rel) rel.outerHTML = relatedSectionHtml('songs', state.item);
    return;
  }

  const songPick = t.closest('[data-song-pick]');
  if (songPick && state.item) {
    if (!state.item.songs) state.item.songs = [];
    const id = Number(songPick.getAttribute('data-song-pick'));
    const nam = songPick.getAttribute('data-name') || ('#' + id);
    const thumb = songPick.getAttribute('data-thumb') || '';
    const type = songPick.getAttribute('data-type') || 'song';
    const artists = songPick.getAttribute('data-artists') || '';
    if (id && !state.item.songs.some(x => x.id === id)) {
      state.item.songs.push({ id: id, name: nam, type: type, url: '', thumb: thumb, artists: artists });
    }
    const box = $('#songChips');
    if (box) box.innerHTML = songChipsHtml(state.item);
    refreshRelatedSection('songs');
    const s = $('#songSearch'), pl = $('#songPicks');
    if (s) s.value = '';
    if (pl) pl.innerHTML = '';
    return;
  }

  const relEdit = t.closest('[data-rel-edit]');
  if (relEdit) {
    const key = relEdit.getAttribute('data-rel-edit');
    if (key) {
      // same as track edit: push parent draft so Cancel returns here
      pushEditStack();
      openItem(key, { nested: true });
    }
    return;
  }

  const relDel = t.closest('[data-rel-del]');
  if (relDel && state.item) {
    const id = Number(relDel.getAttribute('data-rel-del'));
    const kind = relDel.getAttribute('data-rel-kind') || 'songs';
    if (kind === 'songs') state.item.songs = (state.item.songs || []).filter(x => x.id !== id);
    else if (kind === 'albums') state.item.albums = (state.item.albums || []).filter(x => x.id !== id);
    else if (kind === 'videos') state.item.videos = (state.item.videos || []).filter(x => x.id !== id);
    refreshRelatedSection(kind);
    return;
  }

  const relMore = t.closest('[data-rel-more]');
  if (relMore) {
    const kind = relMore.getAttribute('data-rel-more');
    const el = $('#relSection-' + kind);
    if (el) {
      const shown = (Number(el.getAttribute('data-rel-shown')) || REL_PAGE) + REL_STEP;
      el.setAttribute('data-rel-shown', String(shown));
      refreshRelatedSection(kind);
    }
    return;
  }

  const createBtn = t.closest('[data-create-type]');
  if (createBtn) {
    const typ = createBtn.getAttribute('data-create-type') || 'song';
    // Nested when already editing something (album → new song, song → new artist, …)
    const nested = !!(state.item && !$('#searchZone').hidden === false);
    // Prefer nested whenever an editor is open
    const fromEditor = !!(state.item && $('#itemZone') && !$('#itemZone').hidden);
    if (typ === 'owner') {
      if (!isAct9()) { alert('Only act9 can manage owners.'); return; }
      openOwnerHub();
      return;
    }
    if (typ === 'playlist') {
      if (!isAct9()) { alert('Only act9 can manage playlists.'); return; }
      openPlaylistHub();
      return;
    }
    openCreate(typ, { nested: fromEditor });
    return;
  }

  const genreDel = t.closest('[data-genre-del]');
  if (genreDel && state.item) {
    const id = Number(genreDel.getAttribute('data-genre-del'));
    state.item.gnr = (state.item.gnr || []).filter(g => g.id !== id);
    const box = $('#genreChips');
    if (box) box.innerHTML = genreChipsHtml(state.item, false);
    return;
  }

  const genrePick = t.closest('[data-genre-pick]');
  if (genrePick && state.item) {
    addGenre({
      id  : Number(genrePick.getAttribute('data-genre-pick')),
      name: genrePick.getAttribute('data-name') || '',
      slug: genrePick.getAttribute('data-slug') || ''
    });
    return;
  }

  const pick = t.closest('[data-pick]');
  if (pick && state.item) {
    collectDraft();
    const id   = Number(pick.getAttribute('data-pick'));
    const nam  = pick.getAttribute('data-name') || ('#' + id);
    const thumb = pick.getAttribute('data-thumb') || '';
    if (!state.item.artists.some(a => a.id === id)) {
      state.item.artists.push({ id: id, name: nam, url: '', thumb: thumb });
    }
    const box = $('#artistChips');
    if (box) box.innerHTML = artistChipsHtml(state.item);
    const s = $('#artistSearch'), pl = $('#artistPicks');
    if (s) s.value = '';
    if (pl) pl.innerHTML = '';
    return;
  }

  const lyricAdd = t.closest('[data-lyric-add]');
  if (lyricAdd && state.item) {
    state.item.lyricEdit = { index: null, lng: 'fa', knd: 'lrc', fmt: 'txt', body: '' };
    const sec = $('#lyricSection');
    if (sec) sec.outerHTML = lyricSectionHtml(state.item);
    else renderEditor();
    const ta = $('#fLyric');
    if (ta) ta.focus();
    return;
  }

  const lyricCancel = t.closest('[data-lyric-cancel]');
  if (lyricCancel && state.item) {
    state.item.lyricEdit = null;
    const sec = $('#lyricSection');
    if (sec) sec.outerHTML = lyricSectionHtml(state.item);
    else renderEditor();
    return;
  }

  const lyricLocalSave = t.closest('[data-lyric-local-save]');
  if (lyricLocalSave && state.item) {
    const ta = $('#fLyric');
    const lngEl = $('#fLyricLng');
    const kndEl = $('#fLyricKnd');
    // Preserve exact line breaks from the textarea (Enter / Shift+Enter)
    const body = ta ? ta.value : '';
    const lng = lngEl ? lngEl.value.trim().toLowerCase().slice(0, 3) : 'fa';
    const knd = kndEl && kndEl.value === 'srt' ? 'srt' : 'lrc';
    if (!body.trim()) {
      setItemStatus('Lyric text is empty.', 'warn');
      if (ta) ta.focus();
      return;
    }
    if (!state.item.lyrics) state.item.lyrics = [];
    const entry = { lng: lng, knd: knd, fmt: 'txt', src: '', body: body };
    const idx = state.item.lyricEdit && state.item.lyricEdit.index;
    if (idx != null && idx >= 0 && idx < state.item.lyrics.length) {
      // keep existing src if any
      entry.src = state.item.lyrics[idx].src || '';
      state.item.lyrics[idx] = entry;
    } else {
      // replace same lng+knd if present
      const exist = state.item.lyrics.findIndex(x => x.lng === lng && x.knd === knd);
      if (exist >= 0) {
        entry.src = state.item.lyrics[exist].src || '';
        state.item.lyrics[exist] = entry;
      } else {
        state.item.lyrics.push(entry);
      }
    }
    state.item.lyricEdit = null;
    const sec = $('#lyricSection');
    if (sec) sec.outerHTML = lyricSectionHtml(state.item);
    else renderEditor();
    setItemStatus('Lyric saved locally — submit the form to write the database.', 'info');
    return;
  }

  const lyricEditBtn = t.closest('[data-lyric-edit]');
  if (lyricEditBtn && state.item) {
    const index = Number(lyricEditBtn.getAttribute('data-lyric-edit'));
    const row = (state.item.lyrics || [])[index];
    if (!row) return;
    state.item.lyricEdit = {
      index: index,
      lng: row.lng || 'fa',
      knd: row.knd || 'lrc',
      fmt: row.fmt || 'txt',
      body: row.body || ''
    };
    const sec = $('#lyricSection');
    if (sec) sec.outerHTML = lyricSectionHtml(state.item);
    else renderEditor();
    const ta = $('#fLyric');
    if (ta) ta.focus();
    return;
  }

  const lyricDelBtn = t.closest('[data-lyric-del]');
  if (lyricDelBtn && state.item) {
    const index = Number(lyricDelBtn.getAttribute('data-lyric-del'));
    if (!state.item.lyrics) return;
    state.item.lyrics.splice(index, 1);
    if (state.item.lyricEdit && state.item.lyricEdit.index === index) {
      state.item.lyricEdit = null;
    }
    const sec = $('#lyricSection');
    if (sec) sec.outerHTML = lyricSectionHtml(state.item);
    else renderEditor();
    return;
  }
}

/* --------------------------------------------------------------------------
 *  Add new panel
 * ------------------------------------------------------------------------ */
function toggleAddPanel() {
  const panel = $('#addPanel');
  const btn   = $('#addNewBtn');
  const open  = panel.hidden;
  panel.hidden = !open;
  btn.setAttribute('aria-expanded', String(open));
  if (open) panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* --------------------------------------------------------------------------
 *  Boot
 * ------------------------------------------------------------------------ */
function bind() {
  // navigation (both menus share the data-view-btn attribute)
  document.addEventListener('click', event => {
    const nav = event.target.closest('[data-view-btn]');
    if (nav) {
      window.location.hash = '#/' + nav.getAttribute('data-view-btn');
      return;
    }
    const edit = event.target.closest('[data-edit]');
    if (edit) openItem(edit.getAttribute('data-edit'));
  });

  // everything inside the item zone (editor form + saved card)
  $('#itemZone').addEventListener('click', handleZoneClick);

  // Create buttons in searchZone (Add new panel) are outside itemZone — bind on workspace.
  const editWs = $('#editWorkspace') || document.body;
  editWs.addEventListener('click', function (event) {
    const createBtn = event.target.closest && event.target.closest('[data-create-type]');
    if (!createBtn) return;
    if (createBtn.closest('#itemZone')) return; // already handled by handleZoneClick
    event.preventDefault();
    const typ = createBtn.getAttribute('data-create-type') || 'song';
    if (typ === 'owner') {
      if (!isAct9()) { alert('Only act9 can manage owners.'); return; }
      openOwnerHub();
      return;
    }
    if (typ === 'playlist') {
      if (!isAct9()) { alert('Only act9 can manage playlists.'); return; }
      openPlaylistHub();
      return;
    }
    openCreate(typ, { nested: false });
  });

  // Local filter for artist related lists (songs / albums / videos) — no API.
  let relFilterTimer = null;
  $('#itemZone').addEventListener('input', function (event) {
    const inp = event.target && event.target.closest
      ? event.target.closest('[data-rel-filter]')
      : null;
    if (!inp) return;
    clearTimeout(relFilterTimer);
    relFilterTimer = setTimeout(function () {
      const kind = inp.getAttribute('data-rel-filter');
      const el = $('#relSection-' + kind);
      if (!el) return;
      el.setAttribute('data-rel-q', inp.value.trim());
      // reset page so filter starts from top
      el.setAttribute('data-rel-shown', String(REL_PAGE));
      // keep focus value after re-render
      const q = inp.value;
      refreshRelatedSection(kind);
      const again = $('#relFilter-' + kind);
      if (again) {
        again.value = q;
        again.focus();
        try {
          const len = again.value.length;
          again.setSelectionRange(len, len);
        } catch (e) {}
      }
    }, 160);
  });

  $('#loginForm').addEventListener('submit', submitLogin);
  $('#searchForm').addEventListener('submit', runSearch);
  $('#addNewBtn').addEventListener('click', toggleAddPanel);
  $$('.js-logout').forEach(btn => btn.addEventListener('click', logout));

  window.addEventListener('hashchange', route);
}

async function boot() {
  // 1. instant hydrate from localStorage (ha_lan / ha_gnr / ha_profile)
  hydrateTaxonomy();
  // admin session user → SN.user (same place the main app expects)
  if (BOOT.user) syncSnUser(BOOT.user);
  // persist an empty ha_profile the first time so the key always exists
  if (HAS_LOCAL && window.SN && window.SN.profile) SaveProfile();

  bind();
  applyAct9Ui();
  renderIdentity();
  renderGate();
  route();

  // 2. always refresh taxonomy in background (even before login)
  //    force=false → only fetches what is still empty; next login uses force=true
  ensureTaxonomy(false);

  // 3. re-synchronise with the server session (expiry, other tab, ...)
  const data = await request('state');
  if (data && typeof data.logged_in === 'boolean') {
    state.loggedIn = data.logged_in === true;
    state.admin    = data.admin === true;
    if (data.token) state.token = data.token;
    syncSnUser(data.user || null);
    renderGate();
    renderIdentity();
    // after a restored admin session, force a fresh taxonomy pull + profile
    if (state.admin) {
      LoadProfile();
      ensureTaxonomy(true);
    }
  }
}

document.addEventListener('DOMContentLoaded', boot);
</script>
</body>
</html>
