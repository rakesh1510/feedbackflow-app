<?php
// FeedbackFlow Configuration
// Edit these values to match your server setup

define('DB_HOST', 'localhost');
define('DB_NAME', 'feedbackflow');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App settings
define('APP_NAME', 'FeedbackFlow');
define('APP_URL', 'http://localhost/feedbackflow');   // No trailing slash
define('APP_VERSION', '1.0.0');

// Security
define('SECRET_KEY', 'CHANGE_THIS_TO_A_RANDOM_STRING_64_CHARS_LONG_PLEASE');

// File uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'zip']);

// Email (SMTP)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'noreply@yourapp.com');
define('SMTP_FROM_NAME', APP_NAME);
define('SMTP_SECURE', 'tls'); // tls or ssl

// AI Features (OpenAI)
define('OPENAI_API_KEY', '');      // Add your key to enable AI features
define('OPENAI_MODEL', 'gpt-4o-mini');
define('AI_ENABLED', !empty(OPENAI_API_KEY));

// Slack
define('SLACK_WEBHOOK_URL', '');

// Session
define('SESSION_LIFETIME', 60 * 60 * 24 * 30); // 30 days

// Timezone
date_default_timezone_set('UTC');

// Environment
define('DEBUG_MODE', false);

// Mail alias (same as SMTP_FROM but shorter)
define('MAIL_FROM', SMTP_FROM);
define('MAIL_FROM_NAME', SMTP_FROM_NAME);

// Stripe (optional)
define('STRIPE_SECRET_KEY', '');
define('STRIPE_PUBLISHABLE_KEY', '');
define('STRIPE_WEBHOOK_SECRET', '');

// Default language
define('DEFAULT_LANG', 'en');

// Rate limiting
define('RATE_LIMIT_ENABLED', true);
