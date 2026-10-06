<?php
declare(strict_types=1);
require dirname(__DIR__) . '/inc/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}
verify_csrf();
if (post('website') !== '') {
    redirect_with_message('../index.php', 'received');
}
enforce_rate_limit($pdo, 'interest');

try {
    required_fields(['listing_id', 'company_name', 'contact_name', 'contact_email']);
    if (!valid_email(post('contact_email', 190)) || post('consent', 10) !== '1') {
        throw new InvalidArgumentException('Please provide a valid email and accept the privacy notice.');
    }
    $listingId = (int)post('listing_id', 20);
    $listingStmt = $pdo->prepare("SELECT id, public_id, title FROM listings WHERE id = ? AND status = 'published'");
    $listingStmt->execute([$listingId]);
    $listing = $listingStmt->fetch();
    if (!$listing) {
        throw new InvalidArgumentException('This listing is no longer available.');
    }

    $stmt = $pdo->prepare('INSERT INTO interests (listing_id, company_name, contact_name, contact_email, contact_phone, message, consent) VALUES (?, ?, ?, ?, ?, ?, 1)');
    $stmt->execute([
        $listingId,
        post('company_name', 180),
        post('contact_name', 160),
        post('contact_email', 190),
        post('contact_phone', 80) ?: null,
        post('message', 3000) ?: null,
    ]);
    notify_admin($config, 'New interest in ' . $listing['public_id'], "A company requested an introduction for {$listing['public_id']} — {$listing['title']}.\n\nOpen the admin panel to review it.");
    redirect_with_message('../index.php', 'interest-received');
} catch (InvalidArgumentException $e) {
    $_SESSION['form_error'] = $e->getMessage();
    header('Location: ../index.php#marketplace');
    exit;
}
