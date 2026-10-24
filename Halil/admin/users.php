<?php
require_once __DIR__ . '/includes.php';
adminRequireLogin();

$currentUser = adminCurrentUser();
$users = adminListUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && adminCurrentUser()['role'] === 'admin') {
        try {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $role = in_array($_POST['role'] ?? '', ['admin', 'editor'], true) ? $_POST['role'] : 'editor';
            $name = trim((string) ($_POST['name'] ?? ''));

            adminCreateUser($username, $password, $role, $name);
            adminFlashMessage('success', 'Yeni kullanıcı oluşturuldu.');
        } catch (InvalidArgumentException $exception) {
            adminFlashMessage('error', $exception->getMessage());
        }
    }

    if ($action === 'delete' && adminCurrentUser()['role'] === 'admin') {
        $userId = (string) ($_POST['user_id'] ?? '');
        if ($userId !== '' && $userId !== ($currentUser['id'] ?? '')) {
            if (adminDeleteUser($userId)) {
                adminFlashMessage('success', 'Kullanıcı silindi.');
            } else {
                adminFlashMessage('error', 'Kullanıcı silinemedi.');
            }
        } else {
            adminFlashMessage('error', 'Kendi hesabınızı silemezsiniz.');
        }
    }

    header('Location: users.php');
    exit;
}

$flash = adminReadFlashMessage();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kullanıcılar | Manager Portal</title>
    <style>
        :root { --bg:#f5f2ee; --panel:#fff; --ink:#1d1d1d; --muted:#5a5b5f; --line:#e7dfd5; --accent:#1d2a36; --soft:#f8f3ee; --green:#1e5f46; --red:#8f2a2a; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--ink); }
        a { color:inherit; text-decoration:none; }
        .topbar { background:#fff; border-bottom:1px solid var(--line); position:sticky; top:0; z-index:10; }
        .topbar-inner { max-width:1280px; margin:0 auto; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; gap:20px; }
        .brand { font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
        .nav { display:flex; gap:10px; flex-wrap:wrap; }
        .nav a { padding:10px 14px; border-radius:999px; color:var(--muted); }
        .nav a.active, .nav a:hover { background:var(--soft); color:var(--ink); }
        .logout { padding:10px 18px; border-radius:999px; background:var(--accent); color:#fff; font-weight:700; }
        .wrap { max-width:1100px; margin:0 auto; padding:32px 24px 60px; }
        .panel { background:#fff; border:1px solid var(--line); border-radius:20px; padding:24px; margin-top:18px; }
        .grid { display:grid; grid-template-columns:1.2fr 0.8fr; gap:18px; }
        .field { margin-bottom:14px; }
        .field label { display:block; margin-bottom:8px; font-size:13px; font-weight:700; }
        .field input, .field select { width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px; font-size:1rem; }
        .btn { display:inline-block; border:none; border-radius:10px; padding:12px 18px; font-weight:700; cursor:pointer; }
        .btn.primary { background:var(--accent); color:#fff; }
        .btn.danger { background:var(--red); color:#fff; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:14px 12px; border-bottom:1px solid var(--line); text-align:left; }
        .badge { display:inline-block; padding:6px 10px; border-radius:999px; background:var(--soft); font-size:12px; font-weight:700; }
        .flash { margin-bottom:18px; border-radius:10px; padding:12px 14px; }
        .flash.success { background:#edf7f1; color:var(--green); border:1px solid #cce2d6; }
        .flash.error { background:#fff0f0; color:var(--red); border:1px solid #f1d0d0; }
        @media (max-width:800px) { .grid { grid-template-columns:1fr; } .topbar-inner { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-inner">
            <div class="brand">TET Manager</div>
            <nav class="nav">
                <a href="index.php">Dashboard</a>
                <a href="projects.php">Projeler</a>
                <a href="awards.php">Ödüller</a>
                <a href="studio.php">Stüdyo</a>
                <a class="active" href="users.php">Kullanıcılar</a>
            </nav>
            <a class="logout" href="logout.php">Çıkış</a>
        </div>
    </div>

    <div class="wrap">
        <?php if ($flash): ?>
            <div class="flash <?php echo adminEscape($flash['type']); ?>"><?php echo adminEscape($flash['message']); ?></div>
        <?php endif; ?>

        <div class="grid">
            <div class="panel">
                <h2>Kullanıcı Listesi</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Ad</th>
                            <th>Kullanıcı Adı</th>
                            <th>Rol</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo adminEscape((string) ($user['name'] ?? $user['username'] ?? '')); ?></td>
                                <td><?php echo adminEscape((string) ($user['username'] ?? '')); ?></td>
                                <td><span class="badge"><?php echo adminEscape(adminStatusBadge((string) ($user['role'] ?? 'editor'))); ?></span></td>
                                <td>
                                    <?php if (($currentUser['id'] ?? '') !== ((string) ($user['id'] ?? ''))): ?>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="user_id" value="<?php echo adminEscape((string) ($user['id'] ?? '')); ?>" />
                                            <button class="btn danger" type="submit">Sil</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge">Mevcut</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                <div class="panel">
                    <h2>Yeni Kullanıcı</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="create" />
                        <div class="field">
                            <label for="name">Ad Soyad</label>
                            <input id="name" name="name" type="text" placeholder="Örn: Ayşe Yılmaz" required />
                        </div>
                        <div class="field">
                            <label for="username">Kullanıcı Adı</label>
                            <input id="username" name="username" type="text" required />
                        </div>
                        <div class="field">
                            <label for="password">Şifre</label>
                            <input id="password" name="password" type="password" minlength="8" required />
                        </div>
                        <div class="field">
                            <label for="role">Rol</label>
                            <select id="role" name="role">
                                <option value="editor">Editör</option>
                                <option value="admin">Yönetici</option>
                            </select>
                        </div>
                        <button class="btn primary" type="submit">Kullanıcı Oluştur</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
