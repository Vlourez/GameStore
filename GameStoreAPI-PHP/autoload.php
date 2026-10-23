<?php

declare(strict_types=1);

/**
 * Registers a minimal PSR-4 autoloader mapping the GameStore\ namespace to
 * the src/ directory. This lets the project run on a plain XAMPP install
 * even if the developer never ran `composer install` — running it is still
 * recommended (composer.json is provided) but not required, since the
 * project itself has zero third-party dependencies.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'GameStore\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relativePath;

    if (is_file($file)) {
        require $file;
    }
});
