<?php
/*
=====================================================
 DLE Cloud Tracker - Warezturkey Style v2.1
=====================================================
*/

error_reporting(0);
define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__FILE__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

require_once (ENGINE_DIR . '/classes/mysql.php');
require_once (ENGINE_DIR . '/data/dbconfig.php');
require_once (ENGINE_DIR . '/data/config.php');

$db = new db;
$db->connect(DBUSER, DBPASS, DBNAME, DBHOST);

$is_logged = false;
if (isset($_COOKIE['dle_user_id']) && isset($_COOKIE['dle_password'])) {
    $c_id   = intval($_COOKIE['dle_user_id']);
    $c_pass = $db->safesql($_COOKIE['dle_password']);

    $check_user = $db->super_query("SELECT user_id, name, password FROM " . USERPREFIX . "_users WHERE user_id='{$c_id}'");

    if ($check_user['user_id'] && md5($check_user['password']) === $c_pass) {
        $is_logged = true;
    }
}

if (!$is_logged) {
    header("Content-Type: text/html; charset=utf-8");
    die("<div style='text-align:center; margin-top:100px; font-family:sans-serif;'><h2>Erişim Engellendi</h2><p>İndirme linklerini sadece üyelerimiz görebilir.</p><a href='/'>Ana Sayfaya Dön</a></div>");
}

$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id > 0) {
    $row = $db->super_query("SELECT xfields FROM " . PREFIX . "_post WHERE id='{$news_id}'");
    if ($row) {
        // xfields parse - orijinal çalışan yöntem
        $fields = array();
        $temp_fields = explode('||', $row['xfields']);
        foreach($temp_fields as $value) {
            list($fieldname, $fieldvalue) = explode('|', $value);
            $fields[$fieldname] = $fieldvalue;
        }

        $download_link = isset($fields['download'])  ? trim($fields['download'])  : '';
        $download2     = isset($fields['download2']) ? trim($fields['download2']) : '';
        $download3     = isset($fields['download3']) ? trim($fields['download3']) : '';
        $filepass      = isset($fields['filepass'])  ? trim($fields['filepass'])  : '';

        if ($download_link) {
            // Ana sayaç
            $db->query("INSERT INTO " . PREFIX . "_download_stats (news_id, download_count, last_download)
                        VALUES ('$news_id', 1, NOW())
                        ON DUPLICATE KEY UPDATE download_count = download_count + 1, last_download = NOW()");

            // Detaylı log
            $user_id  = intval($check_user['user_id']);
            $username = $db->safesql($check_user['name']);
            $ip       = $db->safesql($_SERVER['REMOTE_ADDR']);
            $db->query("INSERT INTO " . PREFIX . "_download_log (news_id, user_id, username, ip, downloaded_at)
                        VALUES ('$news_id', '$user_id', '$username', '$ip', NOW())");

            $pass_html = $filepass
                ? "<div class='pass-box'><i class='fa-solid fa-key'></i> Dosya Şifresi: <span>{$filepass}</span></div>"
                : "<div class='pass-box nopass'><i class='fa-solid fa-lock-open'></i> Bu dosyanın şifresi yok</div>";

            $alt2_html = $download2
                ? "<a href='" . htmlspecialchars($download2) . "' class='btn btn-alt'><i class='fa-solid fa-link'></i> Alternatif Link 1</a>"
                : "";
            $alt3_html = $download3
                ? "<a href='" . htmlspecialchars($download3) . "' class='btn btn-alt'><i class='fa-solid fa-link'></i> Alternatif Link 2</a>"
                : "";

            $main_link = htmlspecialchars($download_link);

            echo <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya İndiriliyor...</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .box {
            background: #1e293b;
            padding: 36px 32px;
            border-radius: 14px;
            text-align: center;
            border: 1px solid #334155;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .loader {
            border: 4px solid #334155;
            border-top: 4px solid #fbbf24;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h3 { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .subtitle { font-size: 12px; color: #64748b; margin-bottom: 24px; }
        .countdown { font-size: 13px; color: #94a3b8; margin-bottom: 20px; }
        .countdown span { color: #fbbf24; font-weight: bold; font-size: 16px; }
        .pass-box {
            background: #0f172a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px dashed #fbbf24;
            color: #fbbf24;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .pass-box span { 
            background: #fbbf24; 
            color: #0f172a; 
            padding: 2px 8px; 
            border-radius: 4px; 
            margin-left: 6px;
            font-size: 15px;
        }
        .pass-box.nopass { border-color: #475569; color: #64748b; }
        .btn {
            display: block;
            padding: 13px 16px;
            margin: 8px 0;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .btn-main { background: #fbbf24; color: #0f172a; }
        .btn-alt { background: #334155; color: #cbd5e1; border: 1px solid #475569; }
        .btn i { margin-right: 8px; }
        .divider {
            border: none;
            border-top: 1px solid #334155;
            margin: 16px 0;
        }
        .back-link { display: block; margin-top: 16px; font-size: 12px; color: #475569; text-decoration: none; }
        .back-link:hover { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="box">
        <div class="loader"></div>
        <h3>Dosyanız Hazırlanıyor</h3>
        <p class="subtitle">Lütfen bekleyin, otomatik yönlendiriliyorsunuz.</p>
        <p class="countdown"><span id="sec">5</span> saniye sonra başlayacak</p>

        {$pass_html}

        <a href="{$main_link}" class="btn btn-main">
            <i class="fa-solid fa-cloud-arrow-down"></i> ANA LİNK (İNDİRMEYİ BAŞLAT)
        </a>

        {$alt2_html}
        {$alt3_html}

        <hr class="divider">
        <a href="javascript:history.back()" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Geri Dön
        </a>
    </div>
    <script>
        var s = 5;
        var t = setInterval(function(){
            s--;
            document.getElementById('sec').innerText = s;
            if(s <= 0){
                clearInterval(t);
                window.location.href = "{$main_link}";
            }
        }, 1000);
    </script>
</body>
</html>
HTML;
            exit;
        }
    }
}

header("Location: /");
exit;
?>
