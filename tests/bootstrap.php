<?php
/**
 * PHPUnit bootstrap: loads the module's classes and its development dependencies.
 */
declare(strict_types=1);

// The module's files refuse to load outside PrestaShop; the tests stand in for it.
define('_PS_VERSION_', '0.0.0');

require __DIR__ . '/../src/vendor/autoload.php';
