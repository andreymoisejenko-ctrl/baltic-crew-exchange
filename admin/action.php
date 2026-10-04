<?php
declare(strict_types=1);
require dirname(__DIR__) . '/inc/bootstrap.php';
admin_required();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}
verify_csrf();
$id = (int)post('id', 20);
$action = post('action', 30);

if ($action === 'publish') {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT type, public_id FROM listings WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);
    $listing = $stmt->fetch();
    if ($listing) {
        $prefix = $listing['type'] === 'crew' ? 'BCE-C' : 'BCE-R';
        $publicId = $listing['public_id'] ?: $prefix . str_pad((string)$id, 3, '0', STR_PAD_LEFT);
        $update = $pdo->prepare("UPDATE listings SET public_id = ?, status = 'published', published_at = COALESCE(published_at, NOW()) WHERE id = ?");
        $update->execute([$publicId, $id]);
    }
    $pdo->commit();
} elseif (in_array($action, ['pause', 'close', 'reject'], true)) {
    $status = ['pause' => 'paused', 'close' => 'closed', 'reject' => 'rejected'][$action];
    $stmt = $pdo->prepare('UPDATE listings SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
} elseif ($action === 'save') {
    $stmt = $pdo->prepare('UPDATE listings SET title=?, country=?, city=?, industry=?, specialisations=?, people_count=?, available_from=?, available_until=?, duration=?, certifications=?, languages=?, mobility=?, rate_info=?, accommodation=?, description=? WHERE id=?');
    $stmt->execute([
        post('title', 180), post('country', 100), post('city', 120) ?: null, post('industry', 100),
        post('specialisations', 1000), post('people_count', 5) ?: null, post('available_from', 10) ?: null,
        post('available_until', 10) ?: null, post('duration', 120) ?: null, post('certifications', 2000) ?: null,
        post('languages', 255) ?: null, post('mobility', 500) ?: null, post('rate_info', 255) ?: null,
        post('accommodation', 255) ?: null, post('description', 4000) ?: null, $id,
    ]);
}

header('Location: index.php?selected=' . $id);
exit;
