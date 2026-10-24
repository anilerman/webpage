<?php
require_once __DIR__ . '/includes.php';
adminRequireLogin();

$content = adminGetContent();
$projectsCount = count($content['projects'] ?? []);
$awardsCount = count($content['awards'] ?? []);
$studioCount = count($content['studio'] ?? []);
$flash = adminReadFlashMessage();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manager Portal | TET Architects</title>
    <style>
        :root { --bg:#f5f2ee; --panel:#fff; --ink:#1d1d1d; --muted:#5a5b5f; --line:#e7dfd5; --accent:#1d2a36; --soft:#f8f3ee; --green:#1e5f46; --red:#8f2a2a; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--ink); }
        a { color:inherit; text-decoration:none; }
        .topbar { background:#fff; border-bottom:1px solid var(--line); position:sticky; top:0; z-index:10; }
        .topbar-inner { max-width:1280px; margin:0 auto; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; gap:20px; }
        .brand { font-weight:700; letter-spacing:0.08em; text-transform:uppercase; }
        .nav { display:flex; gap:10px; flex-wrap:wrap; }
        .nav a { padding:10px 14px; border-radius:999px; color:var(--muted); }
        .nav a.active, .nav a:hover { background:var(--soft); color:var(--ink); }
        .logout { padding:10px 18px; border-radius:999px; background:var(--accent); color:#fff; font-weight:700; }
        .wrap { max-width:1280px; margin:0 auto; padding:32px 24px 60px; }
        .hero { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:26px 22px; margin-bottom:24px; }
        h1 { margin:0 0 8px; font-size:2rem; }
        .muted { color:var(--muted); }
        .grid { display:grid; grid-template-columns:repeat(4,minmax(180px,1fr)); gap:18px; margin-top:20px; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:22px; }
        .card .label { color:var(--muted); font-size:12px; letter-spacing:.08em; text-transform:uppercase; }
        .card .value { font-size:2rem; font-weight:700; margin-top:8px; }
        .flash { margin:16px 0; border-radius:12px; padding:14px 16px; font-weight:600; }
        .flash.success { background:#edf7f1; color:var(--green); border:1px solid #cce2d6; }
        .flash.error { background:#fff0f0; color:var(--red); border:1px solid #f1d0d0; }
        .panel-list { display:grid; grid-template-columns:repeat(2,minmax(280px,1fr)); gap:18px; }
        .panel-item { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:20px; }
        .panel-item h3 { margin:0 0 10px; }
        .panel-item ul { margin:0; padding-left:18px; color:var(--muted); line-height:1.8; }
        @media (max-width:800px) { .grid, .panel-list { grid-template-columns:1fr; } .topbar-inner { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-inner">
            <div class="brand">TET Manager</div>
            <nav class="nav">
                <a class="active" href="index.php">Dashboard</a>
                <a href="projects.php">Projeler</a>
                <a href="awards.php">Ödüller</a>
                <a href="studio.php">Stüdyo</a>
                <a href="users.php">Kullanıcılar</a>
            </nav>
            <a class="logout" href="logout.php">Çıkış</a>
        </div>
    </div>

    <div class="wrap">
        <div class="hero">
            <div class="eyebrow" style="font-size:11px; letter-spacing:.1em; text-transform:uppercase; color:#687074;">Yönetim Paneli</div>
            <h1>Merhaba, <?php echo adminEscape(adminCurrentUser()['name'] ?? 'Yönetici'); ?></h1>
            <div class="muted">Bu alan yalnızca onaylı kullanıcılar tarafından erişilebilir. No third-party tracking, no external service.</div>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?php echo adminEscape($flash['type']); ?>"><?php echo adminEscape($flash['message']); ?></div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <div class="label">Projeler</div>
                <div class="value"><?php echo $projectsCount; ?></div>
            </div>
            <div class="card">
                <div class="label">Ödüller</div>
                <div class="value"><?php echo $awardsCount; ?></div>
            </div>
            <div class="card">
                <div class="label">Stüdyo</div>
                <div class="value"><?php echo $studioCount; ?></div>
            </div>
            <div class="card">
                <div class="label">Kullanıcı</div>
                <div class="value"><?php echo count(adminListUsers()); ?></div>
            </div>
        </div>

        <div class="panel-list" style="margin-top:18px;">
            <div class="panel-item">
                <h3>Yönetim Alanları</h3>
                <ul>
                    <li><a href="projects.php">Proje ekle / düzenle</a></li>
                    <li><a href="awards.php">Ödül ekle / düzenle</a></li>
                    <li><a href="studio.php">Stüdyo içeriği düzenle</a></li>
                    <li><a href="users.php">Kullanıcı oluştur / sil</a></li>
                </ul>
            </div>
            <div class="panel-item">
                <h3>Kontrol Listesi</h3>
                <ul>
                    <li>Yalnızca manager hesabı ile erişim</li>
                    <li>Sunucu tarafı doğrulama</li>
                    <li>Gizlilik odaklı, üçüncü taraf servis yok</li>
                    <li>Yeni içerik için hafif JSON tabanlı depolama</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
