<?php
/**
 * DELICACY - Root entrypoint for local development.
 *
 * Allows `php -S localhost:8000` from the project root to serve the
 * same landing page used by the public document root.
 */

require_once __DIR__ . '/public/index.php';
