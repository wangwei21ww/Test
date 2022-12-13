<?php

/**
 * run with command 
 * php start.php start
 */
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('LESS_PATH', BASE_PATH . 'src/Less/');
define('RUNNING_MODE', 'production');
define('APP_PATH', BASE_PATH . 'App/');
define('DEBUG_MODE', false);
define('RUNNING_ENV', 'prod'); // support dev, prod, local

$GLOBALS['app_conf'] = loadConfig();

function loadConfig()
{
    $envConfigFile = APP_PATH . 'config/config.' . RUNNING_ENV . '.php';
    $baseConfig = require_once(APP_PATH . 'config/config.php');
    if (!file_exists($envConfigFile)) {
        exit("The " . RUNNING_ENV . " config file not exists");
    }
    $envConfig = require_once($envConfigFile);
    return array_merge($baseConfig, $envConfig);
}

ini_set('display_errors', RUNNING_MODE == 'production' ? 'Off' : 'On');

use Workerman\Worker;

if (strpos(strtolower(PHP_OS), 'win') === 0) {
    exit("start.php not support windows, please use start_for_win.bat\n");
}

// 检查扩展
if (!extension_loaded('pcntl')) {
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if (!extension_loaded('posix')) {
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 标记是全局启动
define('GLOBAL_START', 1);

require_once dirname(LESS_PATH) . '/Bootstrap.php';
//require_once __DIR__ . '/App/FileMonitor/start.php';

Worker::$stdoutFile = __DIR__ . '/stdout.log';
Worker::$logFile = __DIR__ . '/workerman.log';

// 加载所有Applications/*/start.php，以便启动所有服务
foreach (glob(__DIR__ . '/services/start*.php') as $start_file) {
    require_once $start_file;
}
// 运行所有服务
Worker::runAll();

