<?php

namespace WPK\Tests;

define( 'TESTS_DIR', __DIR__ );

$dir = __DIR__;

require_once $dir . '/../../vendor/autoload.php';

$testsDir = __DIR__ . '/Suite/tests/phpunit';

if ( ! file_exists( $testsDir ) ) {
    printf( 'Error! You need to provide tests suite in %s.', __DIR__ . '/Suite' );

    return 1;
}

// Disable revisions
define( 'WP_POST_REVISIONS', false );

// Give access to tests_add_filter() function.
require_once $testsDir . '/includes/functions.php';

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
    xdebug_disable();
}

// Start up the WP testing environment.
require $testsDir . '/includes/bootstrap.php';


