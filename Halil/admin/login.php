<?php
require_once __DIR__ . '/includes.php';

if (adminIsLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['website'])) {
        $error = 'İstek reddedildi.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (adminLogin($username, $password)) {
            header('Location: index.php');
            exit;
        }

        $error = 'Kullanıcı adı veya şifre hatalı.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manager Girişi | TET Architects</title>
    <style>
        :root {
            --bg: #f5f2ee;
            --panel: #ffffff;
            --text: #1a1a1a;
            --muted: #777;
            --accent: #1d2a36;
            --line: #e7e0d8;
            --danger: #a02828;
            --success: #1d5d42;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f6f2ee 0%, #efe7df 100%);
            color: var(--text);
            display: grid;
            place-items: center;
            min-height: 100vh;
        }
        .panel {
            width: min(440px, 92vw);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 32px 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .eyebrow {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 12px;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 2rem;
        }
        p {
            margin: 0 0 20px;
            color: var(--muted);
            line-height: 1.5;
        }
        .field {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 1rem;
        }
        .honeypot {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
        .button {
            width: 100%;
            border: none;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            padding: 14px 18px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            background: #fff1f1;
            border: 1px solid #f3d0d0;
            color: var(--danger);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .meta {
            margin-top: 18px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="panel">
        <div class="eyebrow">Private Access</div>
        <h1>Manager Portal</h1>
        <p>Bu alan yalnızca yetkili kullanıcılar içindir. Yönetim paneline erişmek için kullanıcı adı ve şifre ile giriş yapın.</p>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo adminEscape($error); ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="honeypot">
                <label for="website">Website</label>
                <input id="website" name="website" tabindex="-1" autocomplete="off" />
            </div>

            <div class="field">
                <label for="username">Kullanıcı Adı</label>
                <input id="username" name="username" type="text" value="" required />
            </div>

            <div class="field">
                <label for="password">Şifre</label>
                <input id="password" name="password" type="password" value="" required />
            </div>

            <button type="submit" class="button">Giriş Yap</button>
        </form>

        <div class="meta">Varsayılan kullanıcı: manager / ChangeMe123!</div>
    </div>
</body>
</html>
