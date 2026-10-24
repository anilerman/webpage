<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

const ADMIN_DATA_DIR = __DIR__ . '/data';
const ADMIN_USERS_FILE = ADMIN_DATA_DIR . '/users.json';
const ADMIN_CONTENT_FILE = __DIR__ . '/../assets/content.json';

function adminEnsureStorage(): void
{
    if (!is_dir(ADMIN_DATA_DIR)) {
        mkdir(ADMIN_DATA_DIR, 0755, true);
    }

    if (!file_exists(ADMIN_USERS_FILE)) {
        $defaultUsers = [
            [
                'id' => 'user_manager_default',
                'username' => 'manager',
                'password' => 'ChangeMe123!',
                'role' => 'admin',
                'name' => 'Yönetici'
            ]
        ];
        file_put_contents(ADMIN_USERS_FILE, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    if (!file_exists(ADMIN_CONTENT_FILE)) {
        $defaultContent = [
            'projects' => [],
            'awards' => [],
            'studio' => []
        ];
        file_put_contents(ADMIN_CONTENT_FILE, json_encode($defaultContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

function adminReadJson(string $path, array $default = []): array
{
    if (!file_exists($path)) {
        return $default;
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $default;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $default;
}

function adminWriteJson(string $path, array $payload): bool
{
    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $temp = tempnam(dirname($path), '.content-');
    if ($temp === false) return false;
    chmod($temp, $path === ADMIN_USERS_FILE ? 0600 : 0644);
    if (file_put_contents($temp, $encoded, LOCK_EX) === false || !rename($temp, $path)) {
        @unlink($temp);
        return false;
    }
    return true;
}

function adminIsLoggedIn(): bool
{
    return !empty($_SESSION['manager_logged_in']) && !empty($_SESSION['manager_user']['username']);
}

function adminRequireLogin(): void
{
    if (!adminIsLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function adminCurrentUser(): ?array
{
    return $_SESSION['manager_user'] ?? null;
}

function adminPasswordMatches(string $plainPassword, string $storedPassword): bool
{
    if ($plainPassword === '' || $storedPassword === '') {
        return false;
    }

    if (password_verify($plainPassword, $storedPassword)) {
        return true;
    }

    return hash_equals($storedPassword, $plainPassword);
}

function adminLogin(string $username, string $password): bool
{
    adminEnsureStorage();

    $users = adminReadJson(ADMIN_USERS_FILE, []);
    foreach ($users as $user) {
        if (!is_array($user)) {
            continue;
        }

        if (strcasecmp((string) ($user['username'] ?? ''), trim($username)) !== 0) {
            continue;
        }

        $storedPassword = (string) ($user['password'] ?? '');
        if (!adminPasswordMatches($password, $storedPassword)) {
            continue;
        }

        session_regenerate_id(true);
        $_SESSION['manager_logged_in'] = true;
        $_SESSION['manager_user'] = [
            'id' => (string) ($user['id'] ?? uniqid('user_', true)),
            'username' => (string) ($user['username'] ?? trim($username)),
            'role' => (string) ($user['role'] ?? 'editor'),
            'name' => (string) ($user['name'] ?? (string) ($user['username'] ?? trim($username)))
        ];

        return true;
    }

    return false;
}

function adminLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function adminListUsers(): array
{
    adminEnsureStorage();
    return adminReadJson(ADMIN_USERS_FILE, []);
}

function adminSaveUsers(array $users): bool
{
    adminEnsureStorage();
    return adminWriteJson(ADMIN_USERS_FILE, $users);
}

function adminCreateUser(string $username, string $password, string $role = 'editor', string $name = ''): array
{
    $cleanUsername = trim($username);
    $cleanPassword = trim($password);
    $cleanName = trim($name) !== '' ? trim($name) : $cleanUsername;

    if ($cleanUsername === '' || strlen($cleanPassword) < 8) {
        throw new InvalidArgumentException('Kullanıcı adı boş olamaz ve şifre en az 8 karakter olmalıdır.');
    }

    $users = adminListUsers();
    foreach ($users as $user) {
        if (strcasecmp((string) ($user['username'] ?? ''), $cleanUsername) === 0) {
            throw new InvalidArgumentException('Bu kullanıcı adı zaten kullanılıyor.');
        }
    }

    $user = [
        'id' => uniqid('user_', true),
        'username' => $cleanUsername,
        'password' => password_hash($cleanPassword, PASSWORD_BCRYPT),
        'role' => in_array($role, ['admin', 'editor'], true) ? $role : 'editor',
        'name' => $cleanName,
    ];

    $users[] = $user;
    adminSaveUsers($users);

    return $user;
}

function adminDeleteUser(string $userId): bool
{
    $users = adminListUsers();
    $filtered = [];

    foreach ($users as $user) {
        if (isset($user['id']) && (string) $user['id'] === $userId) {
            continue;
        }
        $filtered[] = $user;
    }

    if (count($filtered) === count($users)) {
        return false;
    }

    return adminSaveUsers($filtered);
}

function adminGetContent(): array
{
    adminEnsureStorage();
    $default = [
        'projects' => [],
        'awards' => [],
        'studio' => []
    ];

    $content = adminReadJson(ADMIN_CONTENT_FILE, $default);
    foreach ($default as $key => $value) {
        if (!isset($content[$key]) || !is_array($content[$key])) {
            $content[$key] = $value;
        }
    }

    return $content;
}

function adminSaveContent(array $content): bool
{
    adminEnsureStorage();
    return adminWriteJson(ADMIN_CONTENT_FILE, $content);
}

function adminSortSectionItems(array $items): array
{
    usort($items, static function (array $left, array $right): int {
        $dateOrder = strcmp((string) ($right['date'] ?? $right['year'] ?? ''), (string) ($left['date'] ?? $left['year'] ?? ''));
        if ($dateOrder !== 0) return $dateOrder;
        $createdOrder = strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''));
        if ($createdOrder !== 0) return $createdOrder;
        $leftOrder = (int) ($left['sort_order'] ?? 9999);
        $rightOrder = (int) ($right['sort_order'] ?? 9999);

        if ($leftOrder === $rightOrder) {
            return strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
        }

        return $leftOrder <=> $rightOrder;
    });

    return $items;
}

function adminSaveSectionItem(string $section, array $item): bool
{
    $content = adminGetContent();
    $items = $content[$section] ?? [];

    $normalized = [
        'id' => trim((string) ($item['id'] ?? '')) ?: bin2hex(random_bytes(12)),
        'date' => trim((string) ($item['date'] ?? '')),
        'meta' => str_replace(["\r\n", "\r"], "\n", trim((string) ($item['meta'] ?? ''))),
        'gallery' => array_values(array_filter($item['gallery'] ?? [], 'strlen')),
        'initials' => trim((string) ($item['initials'] ?? '')),
        'hue' => max(0, min(360, (int) ($item['hue'] ?? 205))),
        'title' => trim((string) ($item['title'] ?? '')),
        'year' => trim((string) ($item['year'] ?? '')),
        'category' => trim((string) ($item['category'] ?? '')),
        'subtitle' => trim((string) ($item['subtitle'] ?? '')),
        'description' => str_replace(["\r\n", "\r"], "\n", trim((string) ($item['description'] ?? ''))),
        'image_url' => trim((string) ($item['image_url'] ?? '')),
        'href' => trim((string) ($item['href'] ?? '')),
        'featured' => isset($item['featured']) ? (bool) $item['featured'] : false,
        'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        'sort_order' => (int) ($item['sort_order'] ?? 9999),
    ];

    if (!in_array($section, ['projects', 'awards', 'studio'], true)) return false;
    foreach (array_merge([$normalized['image_url'], $normalized['href']], $normalized['gallery']) as $url) {
        if ($url !== '' && !preg_match('~^(https?://[^\\s]+|assets/[a-zA-Z0-9_./% -]+|project-detail-[a-zA-Z0-9_.-]+\\.html)$~', $url)) return false;
        if (strpos($url, '..') !== false) return false;
    }
    if ($normalized['date'] !== '' && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $normalized['date'])) return false;
    if ($normalized['date'] !== '') {
        [$year, $month, $day] = array_map('intval', explode('-', $normalized['date']));
        if (!checkdate($month, $day, $year)) return false;
        $normalized['year'] = substr($normalized['date'], 0, 4);
    }
    if ($normalized['year'] !== '' && !preg_match('/^\d{4}$/', $normalized['year'])) return false;
    if ($normalized['title'] === '') {
        return false;
    }

    foreach ($items as $current) {
        if (($current['id'] ?? '') === $normalized['id']) $normalized['created_at'] = $current['created_at'] ?? '';
    }
    if ($section === 'projects') {
        if ($normalized['image_url'] === '' || !$normalized['gallery']) return false;
        $existing = null;
        foreach ($items as $current) if (($current['id'] ?? '') === $normalized['id']) $existing = $current;
        $normalized['href'] = $existing['href'] ?? ('project-detail-' . $normalized['id'] . '.html');
        if (!adminPublishProject($normalized)) return false;
    }
    $updated = false;
    foreach ($items as $index => $current) {
        if ((string) ($current['id'] ?? '') === $normalized['id']) {
            $items[$index] = $normalized;
            $updated = true;
            break;
        }
    }

    if (!$updated) {
        $items[] = $normalized;
    }

    $content[$section] = adminSortSectionItems($items);
    return adminSaveContent($content);
}

function adminDeleteSectionItem(string $section, string $itemId): bool
{
    $content = adminGetContent();
    $items = $content[$section] ?? [];
    $filtered = [];

    foreach ($items as $item) {
        if ((string) ($item['id'] ?? '') !== $itemId) {
            $filtered[] = $item;
        }
    }

    $content[$section] = adminSortSectionItems($filtered);
    if (!adminSaveContent($content)) return false;
    if ($section === 'projects') {
        foreach ($items as $item) {
            if (($item['id'] ?? '') === $itemId && preg_match('/^project-detail-[a-zA-Z0-9_.-]+\.html$/', $item['href'] ?? '')) {
                $path = __DIR__ . '/../' . $item['href'];
                if (is_file($path)) unlink($path);
            }
        }
    }
    return true;
}

function adminHandleImageUpload(string $fieldName, string $targetFolder): string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return '';
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name']) || $file['size'] > 20 * 1024 * 1024) {
        throw new RuntimeException('Görsel yüklenemedi. Dosya en fazla 20 MB olmalıdır.');
    }

    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $allowed = array_keys($extensions);
    $mimeType = mime_content_type($file['tmp_name']);
    if (!in_array($mimeType, $allowed, true)) throw new RuntimeException('Yalnızca JPG, PNG ve WebP yüklenebilir.');

    $baseDir = __DIR__ . '/../' . trim($targetFolder, '/');
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }

    $fileName = uniqid('upload_', true) . '.' . $extensions[$mimeType];
    $targetPath = $baseDir . '/' . $fileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetFolder . '/' . $fileName;
    }

    throw new RuntimeException('Görsel kaydedilemedi. Klasör izinlerini kontrol edin.');
}

function adminFlashMessage(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function adminReadFlashMessage(): ?array
{
    if (empty($_SESSION['flash_message']) || !is_array($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    return $flash;
}

function adminEscape(string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function adminStatusBadge(string $role): string
{
    return $role === 'admin' ? 'Yönetici' : 'Editör';
}

adminEnsureStorage();

function adminCsrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function adminRequireCsrf(): void {
    if (!hash_equals(adminCsrfToken(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Oturum doğrulanamadı. Sayfayı yenileyin.');
    }
}

function adminPublishProject(array $project): bool
{
    $path = __DIR__ . '/../' . $project['href'];
    $html = file_get_contents(is_file($path) ? $path : __DIR__ . '/project-template.html');
    if ($html === false) return false;
    $fields = [
        '~<title>.*?</title>~s' => '<title>' . adminEscape($project['title']) . ' | TET Architects</title>',
        '~(<div class="project-info">\s*)<h2>.*?</h2>~s' => '$info',
        '~<p class="meta">.*?</p>~s' => '<p class="meta">' . nl2br(adminEscape($project['meta'])) . '</p>',
        '~<p class="desc">.*?</p>~s' => '<p class="desc">' . nl2br(adminEscape($project['description'])) . '</p>',
        '~<img id="mainDisplay"[^>]*>~s' => '<img id="mainDisplay" src="' . adminEscape($project['image_url']) . '" alt="' . adminEscape($project['title']) . '" />',
    ];
    foreach ($fields as $pattern => $replacement) {
        $html = preg_replace_callback($pattern, static function ($match) use ($replacement, $project) {
            return $replacement === '$info' ? $match[1] . '<h2>' . adminEscape($project['title']) . '</h2>' : $replacement;
        }, $html);
    }
    return file_put_contents($path, $html, LOCK_EX) !== false;
}
