<?php

declare(strict_types=1);

// PHPUnit bootstrap: Composer autoloader + optional local .env for sandbox runs.
// Dotenv::createImmutable never overrides real environment variables, so CI
// secret envs always win over file values. No .env file = tests skip cleanly.

require __DIR__ . '/../vendor/autoload.php';

if (is_file(__DIR__ . '/../.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
    // phpdotenv populates $_SERVER/$_ENV but not the process environment, and
    // this PHP runs with variables_order=GPCS, so getenv() would miss it.
    $token = $_SERVER['ENVIATODO_TOKEN'] ?? null;
    if (\is_string($token) && false === getenv('ENVIATODO_TOKEN')) {
        putenv('ENVIATODO_TOKEN=' . $token);
    }
}
