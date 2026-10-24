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
const ADMIN_CONTENT_FILE = ADMIN_DATA_DIR . '/content.json';

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
    return (bool) file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
        'id' => (string) ($item['id'] ?? uniqid($section . '_', true)),
        'title' => trim((string) ($item['title'] ?? '')),
        'year' => trim((string) ($item['year'] ?? '')),
        'category' => trim((string) ($item['category'] ?? '')),
        'subtitle' => trim((string) ($item['subtitle'] ?? '')),
        'description' => trim((string) ($item['description'] ?? '')),
        'image_url' => trim((string) ($item['image_url'] ?? '')),
        'href' => trim((string) ($item['href'] ?? '')),
        'featured' => isset($item['featured']) ? (bool) $item['featured'] : false,
        'sort_order' => (int) ($item['sort_order'] ?? 9999),
    ];

    if ($normalized['title'] === '') {
        return false;
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
    return adminSaveContent($content);
}

function adminHandleImageUpload(string $fieldName, string $targetFolder): string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return '';
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        return '';
    }

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    $mimeType = mime_content_type($file['tmp_name']);
    if (!in_array($mimeType, $allowed, true)) {
        return '';
    }

    $baseDir = __DIR__ . '/../' . trim($targetFolder, '/');
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }

    $fileName = uniqid('upload_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $targetPath = $baseDir . '/' . $fileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetFolder . '/' . $fileName;
    }

    return '';
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
