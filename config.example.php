<?php
declare(strict_types=1);

return [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=YOUR_DATABASE;charset=utf8mb4',
        'user' => 'YOUR_DATABASE_USER',
        'password' => 'YOUR_DATABASE_PASSWORD',
    ],
    'app' => [
        'base_url' => 'https://balticcrewexchange.eu',
        'notification_email' => 'andrejs@balticcrewexchange.eu',
        'admin_user' => 'andrejs',
        // Generate with: php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
        'admin_password_hash' => 'REPLACE_WITH_PASSWORD_HASH',
    ],
];
