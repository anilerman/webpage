<?php
require_once __DIR__ . '/includes.php';
adminRequireLogin();

$section = 'studio';
$content = adminGetContent();
$items = adminSortSectionItems($content[$section] ?? []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
        $uploaded = adminHandleImageUpload('image_file', 'assets/uploads');
        if ($uploaded !== '') {
            $imageUrl = $uploaded;
        }

        $item = [
            'id' => trim((string) ($_POST['id'] ?? '')),
            'title' => trim((string) ($_POST['title'] ?? '')),
            'year' => trim((string) ($_POST['year'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'subtitle' => trim((string) ($_POST['subtitle'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'image_url' => $imageUrl,
            'href' => trim((string) ($_POST['href'] ?? '')),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 9999),
        ];

        if (adminSaveSectionItem($section, $item)) {
            adminFlashMessage('success', 'Stüdyo içeriği kaydedildi.');
        } else {
            adminFlashMessage('error', 'Stüdyo içeriği kaydı sırasında doğrulama hatası oluştu.');
        }
    }

    if ($action === 'delete') {
        $itemId = trim((string) ($_POST['id'] ?? ''));
        if ($itemId !== '' && adminDeleteSectionItem($section, $itemId)) {
            adminFlashMessage('success', 'Stüdyo içeriği silindi.');
        } else {
            adminFlashMessage('error', 'Stüdyo içeriği silinemedi.');
        }
    }

    header('Location: studio.php');
    exit;
}

$selectedId = trim((string) ($_GET['edit'] ?? ''));
$selected = null;
foreach ($items as $item) {
    if ((string) ($item['id'] ?? '') === $selectedId) {
        $selected = $item;
        break;
    }
}

$flash = adminReadFlashMessage();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Stüdyo | Manager Portal</title>
    <style>
        :root { --bg:#f5f2ee; --panel:#fff; --ink:#1d1d1d; --muted:#5a5b5f; --line:#e7dfd5; --accent:#1d2a36; --soft:#f8f3ee; --green:#1e5f46; --red:#8f2a2a; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--ink); }
        a { color:inherit; text-decoration:none; }
        .topbar { background:#fff; border-bottom:1px solid var(--line); }
        .topbar-inner { max-width:1280px; margin:0 auto; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; gap:20px; }
        .brand { font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
        .nav { display:flex; gap:10px; flex-wrap:wrap; }
        .nav a { padding:10px 14px; border-radius:999px; color:var(--muted); }
        .nav a.active, .nav a:hover { background:var(--soft); color:var(--ink); }
        .logout { padding:10px 18px; border-radius:999px; background:var(--accent); color:#fff; font-weight:700; }
        .wrap { max-width:1200px; margin:0 auto; padding:32px 24px 60px; }
        .grid { display:grid; grid-template-columns:1.25fr 0.75fr; gap:18px; }
        .panel { background:#fff; border:1px solid var(--line); border-radius:18px; padding:24px; }
        .field { margin-bottom:14px; }
        .field label { display:block; margin-bottom:8px; font-size:13px; font-weight:700; }
        .field input, .field textarea, .field select { width:100%; padding:11px 12px; border:1px solid var(--line); border-radius:10px; font:inherit; }
        .field textarea { min-height:110px; resize:vertical; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .btn { border:none; border-radius:10px; padding:12px 18px; font-weight:700; cursor:pointer; }
        .btn.primary { background:var(--accent); color:#fff; }
        .btn.secondary { background:var(--soft); color:var(--ink); }
        .btn.danger { background:var(--red); color:#fff; }
        .flash { margin:0 0 18px; border-radius:10px; padding:12px 14px; }
        .flash.success { background:#edf7f1; color:var(--green); border:1px solid #cce2d6; }
        .flash.error { background:#fff0f0; color:var(--red); border:1px solid #f1d0d0; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:12px 10px; border-bottom:1px solid var(--line); text-align:left; }
        .checkbox { display:flex; align-items:center; gap:8px; }
        .checkbox input { width:auto; }
        @media (max-width:800px) { .grid { grid-template-columns:1fr; } .row { grid-template-columns:1fr; } .topbar-inner { flex-direction:column; align-items:flex-start; } }
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
                <a class="active" href="studio.php">Stüdyo</a>
                <a href="users.php">Kullanıcılar</a>
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
                <h2><?php echo $selected ? 'Stüdyo İçeriğini Düzenle' : 'Yeni Stüdyo İçeriği'; ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save" />
                    <input type="hidden" name="id" value="<?php echo adminEscape((string) ($selected['id'] ?? '')); ?>" />

                    <div class="field">
                        <label for="title">Başlık</label>
                        <input id="title" name="title" type="text" value="<?php echo adminEscape((string) ($selected['title'] ?? '')); ?>" required />
                    </div>

                    <div class="row">
                        <div class="field">
                            <label for="year">Yıl</label>
                            <input id="year" name="year" type="text" value="<?php echo adminEscape((string) ($selected['year'] ?? '')); ?>" />
                        </div>
                        <div class="field">
                            <label for="category">Kategori</label>
                            <input id="category" name="category" type="text" value="<?php echo adminEscape((string) ($selected['category'] ?? '')); ?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label for="sort_order">Sıra</label>
                            <input id="sort_order" name="sort_order" type="number" value="<?php echo adminEscape((string) ($selected['sort_order'] ?? '9999')); ?>" />
                        </div>
                        <div class="field checkbox" style="padding-top:28px;">
                            <input id="featured" name="featured" type="checkbox" <?php echo !empty($selected['featured']) ? 'checked' : ''; ?> />
                            <label for="featured" style="margin:0;">Öne çıkan</label>
                        </div>
                    </div>

                    <div class="field">
                        <label for="subtitle">Alt Başlık</label>
                        <input id="subtitle" name="subtitle" type="text" value="<?php echo adminEscape((string) ($selected['subtitle'] ?? '')); ?>" />
                    </div>

                    <div class="field">
                        <label for="href">Link</label>
                        <input id="href" name="href" type="text" value="<?php echo adminEscape((string) ($selected['href'] ?? '')); ?>" />
                    </div>

                    <div class="field">
                        <label for="image_url">Görsel URL</label>
                        <input id="image_url" name="image_url" type="text" value="<?php echo adminEscape((string) ($selected['image_url'] ?? '')); ?>" placeholder="assets/studio/tek.webp" />
                    </div>

                    <div class="field">
                        <label for="image_file">Veya yeni görsel yükle</label>
                        <input id="image_file" name="image_file" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" />
                    </div>

                    <div class="field">
                        <label for="description">Açıklama</label>
                        <textarea id="description" name="description"><?php echo adminEscape((string) ($selected['description'] ?? '')); ?></textarea>
                    </div>

                    <button class="btn primary" type="submit">Kaydet</button>
                    <?php if ($selected): ?>
                        <a class="btn secondary" href="studio.php" style="display:inline-block; margin-left:8px;">Yeni kayıt</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="panel">
                <h2>Mevcut Stüdyo İçerikleri</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Yıl</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo adminEscape((string) ($item['title'] ?? '')); ?></td>
                                <td><?php echo adminEscape((string) ($item['year'] ?? '')); ?></td>
                                <td>
                                    <a class="btn secondary" href="studio.php?edit=<?php echo adminEscape((string) ($item['id'] ?? '')); ?>" style="display:inline-block; margin-right:6px;">Düzenle</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Bu içeriği silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="action" value="delete" />
                                        <input type="hidden" name="id" value="<?php echo adminEscape((string) ($item['id'] ?? '')); ?>" />
                                        <button class="btn danger" type="submit">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
