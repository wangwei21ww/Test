<?php

/**
 * This file is part of the Less
 * @author Stephen Lee <stephen.lee@lesscloud.com>
 * @link https://lesscloud.com/
 * @license All copyright and license information, please visit the web page
 *           https://lesscloud.com/license
 * @version $Id: Bootstrap.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

define('DS', DIRECTORY_SEPARATOR);

defined('LESS_PATH') or define('LESS_PATH', __DIR__ . '/Less/');

/**
 * Less start running time.
 */
defined('LESS_START_TIME') or define("LESS_START_TIME", microtime(true));

/**
 * Define the framework debugging whether is enable or not,
 * default setting to false, means not enable debug.
 */
defined('LESS_DEBUG') or define("LESS_DEBUG", false);

/**
 * Application running model, you should be sure that you have to define the constant RUNNING_MODE
 * If not, the system will automatically set the running model as production
 */
defined('RUNNING_MODE') or define('RUNNING_MODE', 'production');

assert_options(ASSERT_ACTIVE, RUNNING_ENV == 'dev');

include_once __DIR__ . DS . 'vendor' . DS . 'autoload.php';
include_once LESS_PATH . 'Less.php';

$appImport = $GLOBALS['app_conf']['import'];

$import = [
    LESS_PATH . 'Db/*.php',
    LESS_PATH . 'Foundation/*.php',
    LESS_PATH . 'Helper/*.php',
    LESS_PATH . 'Validator/*.php',
    LESS_PATH . 'Websocket/*.php'
];

$import = array_merge($import, $appImport);

\Less::autoload($import);
