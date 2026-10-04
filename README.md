# Baltic Crew Exchange MVP

Production-oriented PHP/MySQL MVP for a managed two-sided B2B marketplace.

## Included

- crew provider submissions;
- project requirement submissions;
- automatic anonymous draft generation;
- private admin review, editing and one-click publication;
- public supply and demand cards;
- introduction / crew offer requests tied to a listing ID;
- private company details separated from public listing data;
- CSRF protection, honeypot and basic rate limiting;
- responsive desktop/mobile interface.

## Nano.lv deployment

1. Create a MySQL database and user in the hosting panel.
2. Import `schema.sql` using phpMyAdmin.
3. Copy `config.example.php` to `config.php` and enter the database credentials.
4. Generate an admin password hash:
   `php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"`
5. Upload the project contents to the domain's public web directory.
6. Ensure PHP 8.1+ and PDO MySQL are enabled.
7. Issue a Let's Encrypt certificate and force HTTPS.
8. Sign in at `/admin/login.php` and test with non-production submissions.

## Publishing workflow

1. Company submits a crew or project requirement.
2. A private pending record is created and the admin receives an email.
3. Admin reviews and edits the automatically generated anonymous card.
4. `Approve & publish` assigns `BCE-C###` or `BCE-R###` and makes it public.
5. Interested companies submit a private request linked to the public card.

Never commit `config.php` or real database credentials.
