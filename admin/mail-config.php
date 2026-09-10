<?php
/**
 * Gmail SMTP configuration.
 * Use a Gmail App Password, not the normal Gmail account password.
 */
return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => getenv('LP_SMTP_USER') ?: 'linkexhibits3@gmail.com',
    'password' => getenv('LP_SMTP_APP_PASSWORD') ?: 'zqvoygscvhwiilev',
    'from_email' => getenv('LP_SMTP_FROM_EMAIL') ?: 'linkexhibits3@gmail.com',
    'from_name' => getenv('LP_SMTP_FROM_NAME') ?: 'Link Promotions and Exhibits',
    'to_email' => getenv('LP_SMTP_TO_EMAIL') ?: 'linkexhibits3@gmail.com',
];
