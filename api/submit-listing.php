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
enforce_rate_limit($pdo, 'listing');

try {
    $type = post('type', 20);
    if (!in_array($type, ['crew', 'project'], true)) {
        throw new InvalidArgumentException('Invalid listing type.');
    }
    required_fields(['country', 'industry', 'specialisations', 'company_name', 'contact_name', 'contact_email']);
    if (!valid_email(post('contact_email', 190))) {
        throw new InvalidArgumentException('Please enter a valid email address.');
    }
    if (post('consent', 10) !== '1') {
        throw new InvalidArgumentException('Consent is required to process the submission.');
    }

    $countRaw = post('people_count', 5);
    $count = $countRaw !== '' ? max(1, min(500, (int)$countRaw)) : null;
    $title = listing_title($type, post('specialisations'), $count, post('city', 120), post('country', 100));

    $sql = 'INSERT INTO listings
        (type, title, country, city, industry, specialisations, people_count, available_from, available_until,
         duration, experience, certifications, languages, mobility, rate_info, accommodation, description,
         company_name, registration_number, contact_name, contact_email, contact_phone, consent)
        VALUES
        (:type, :title, :country, :city, :industry, :specialisations, :people_count, :available_from, :available_until,
         :duration, :experience, :certifications, :languages, :mobility, :rate_info, :accommodation, :description,
         :company_name, :registration_number, :contact_name, :contact_email, :contact_phone, 1)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'type' => $type,
        'title' => $title,
        'country' => post('country', 100),
        'city' => post('city', 120) ?: null,
        'industry' => post('industry', 100),
        'specialisations' => post('specialisations', 1000),
        'people_count' => $count,
        'available_from' => post('available_from', 10) ?: null,
        'available_until' => post('available_until', 10) ?: null,
        'duration' => post('duration', 120) ?: null,
        'experience' => post('experience', 3000) ?: null,
        'certifications' => post('certifications', 2000) ?: null,
        'languages' => post('languages', 255) ?: null,
        'mobility' => post('mobility', 500) ?: null,
        'rate_info' => post('rate_info', 255) ?: null,
        'accommodation' => post('accommodation', 255) ?: null,
        'description' => post('description', 4000) ?: null,
        'company_name' => post('company_name', 180),
        'registration_number' => post('registration_number', 80) ?: null,
        'contact_name' => post('contact_name', 160),
        'contact_email' => post('contact_email', 190),
        'contact_phone' => post('contact_phone', 80) ?: null,
    ]);

    $id = (int)$pdo->lastInsertId();
    notify_admin(
        $config,
        'New BCE ' . ($type === 'crew' ? 'crew' : 'project') . ' submission #' . $id,
        "A new submission is waiting for review.\n\nTitle: {$title}\nCompany: " . post('company_name') . "\nContact: " . post('contact_name') . "\n\nOpen the admin panel to review and publish it."
    );
    redirect_with_message('../index.php', 'submission-received');
} catch (InvalidArgumentException $e) {
    $_SESSION['form_error'] = $e->getMessage();
    header('Location: ../index.php?form=' . rawurlencode(post('type', 20)) . '#submit');
    exit;
}
