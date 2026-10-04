<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
    ]);
    session_start();
}

$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Application configuration is not installed.');
}
$config = require $configFile;

try {
    $pdo = new PDO(
        $config['db']['dsn'],
        $config['db']['user'],
        $config['db']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (Throwable $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    exit('The marketplace is temporarily unavailable. Please try again later.');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = (string)($_POST['csrf'] ?? '');
    if (!hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
        http_response_code(419);
        exit('The form expired. Please go back, refresh the page and try again.');
    }
}

function post(string $key, int $max = 5000): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    return mb_substr($value, 0, $max);
}

function required_fields(array $fields): void
{
    foreach ($fields as $field) {
        if (post($field) === '') {
            throw new InvalidArgumentException('Please complete all required fields.');
        }
    }
}

function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function redirect_with_message(string $path, string $key): never
{
    header('Location: ' . $path . '?message=' . rawurlencode($key));
    exit;
}

function enforce_rate_limit(PDO $pdo, string $scope): void
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $fingerprint = hash('sha256', $scope . '|' . $ip);
    $pdo->beginTransaction();
    $select = $pdo->prepare('SELECT attempts, window_started FROM rate_limits WHERE fingerprint = ? FOR UPDATE');
    $select->execute([$fingerprint]);
    $row = $select->fetch();
    if (!$row || strtotime($row['window_started']) < time() - 3600) {
        $replace = $pdo->prepare('REPLACE INTO rate_limits (fingerprint, attempts, window_started) VALUES (?, 1, NOW())');
        $replace->execute([$fingerprint]);
    } else {
        if ((int)$row['attempts'] >= 8) {
            $pdo->rollBack();
            http_response_code(429);
            exit('Too many submissions. Please try again later.');
        }
        $update = $pdo->prepare('UPDATE rate_limits SET attempts = attempts + 1 WHERE fingerprint = ?');
        $update->execute([$fingerprint]);
    }
    $pdo->commit();
}

function notify_admin(array $config, string $subject, string $body): void
{
    $to = $config['app']['notification_email'];
    $headers = [
        'From: Baltic Crew Exchange <info@balticcrewexchange.eu>',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    @mail($to, $subject, $body, implode("\r\n", $headers));
}

function listing_title(string $type, string $specialisations, ?int $count, string $city, string $country): string
{
    $first = trim(explode(',', $specialisations)[0] ?? $specialisations);
    $first = $first !== '' ? $first : ($type === 'crew' ? 'Industrial crew' : 'Project crew');
    if ($type === 'crew') {
        return $first . ' crew' . ($count ? ' · ' . $count . ' people' : '');
    }
    $place = $city !== '' ? $city : $country;
    return ($count ? $count . ' ' : '') . $first . ' required' . ($place !== '' ? ' · ' . $place : '');
}

function admin_required(): void
{
    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}
