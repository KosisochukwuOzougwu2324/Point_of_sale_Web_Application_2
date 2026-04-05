<?php

namespace App;

class Config
{
    public const DB_HOST = 'mysql';
    public const DB_NAME = 'developmentdb';
    public const DB_USERNAME = 'root';
    public const DB_PASSWORD = 'secret123';

    // JWT Configuration
    public const JWT_SECRET = 'pos_system_jwt_secret_key_2026';
    public const JWT_ISSUER = 'pos-system';
    public const JWT_EXPIRY = 3600; // 1 hour in seconds

    // Stripe Configuration (Test Mode)
    public const STRIPE_SECRET_KEY = 'sk_test_51TIwJgFpCucv7zFUnmAfoi0ZjQf5HkJIxroXHRDFt8vSjC6Y8UqRfEyVlhvZKTojJxxXk6fGcVXztMggFMkPlUef00MDbMMpiQ';

    // Mail Configuration
    public const MAIL_HOST = 'smtp.gmail.com';
    public const MAIL_PORT = 587;
    public const MAIL_USERNAME = '';
    public const MAIL_PASSWORD = '';
    public const MAIL_FROM = 'noreply@pos-system.com';
    public const MAIL_FROM_NAME = 'POS System';
}
