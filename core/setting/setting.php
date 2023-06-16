<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

if(phpversion() < 5.4){
    echo "Error! Use PHP version 5.4 or more. Your version of PHP is ".phpversion(); exit();
}

if(!ini_get('short_open_tag')){
    echo "Error! PHP param short_open_tag must be set as on in php.ini"; exit();
}

define('_DOCUMENT_ROOT_', realpath(dirname(__FILE__) . '/../../'));

define('_BASE_DIR_APP_',        _DOCUMENT_ROOT_ . '/app/');
define('_BASE_DIR_CORE_',       _DOCUMENT_ROOT_ . '/core/');
define('_BASE_DIR_STORAGE_',    _DOCUMENT_ROOT_ . '/storage/');
define('_BASE_DIR_TEMPLATE_',   _DOCUMENT_ROOT_ . '/template/');



if(file_exists(_BASE_DIR_APP_.'setting/setting.php')){
    include(_BASE_DIR_APP_ . 'setting/setting.php');
}

//must deprecateted
if(!defined('_SECRET_')) {
    define('_SECRET_', 'test');
}

//if enabled all new words will be added to file with translations
if(!defined('_TRANSLATE_MODE_ENABLE_')) {
    define('_TRANSLATE_MODE_ENABLE_', true);
}

//DB default prefix
if(!defined('_DB_PREFIX_')) {
    define("_DB_PREFIX_", "bm_");
}

if(!defined('_SITE_PORT_')){
    define('_SITE_PORT_', isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '80' );
}

if(!defined('_SITE_PROTOCOL_')) {
    define('_SITE_PROTOCOL_', '' . (isset($_SERVER['HTTPS']) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ? 'https' : 'http') . '://');
}

if(!defined('_SITE_DOMINE_')) {
    define('_SITE_DOMINE_', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
}

define('_SITE_URL_', _SITE_PROTOCOL_ . _SITE_DOMINE_);
spl_autoload_register('core_autoload');
function core_autoload($namespace_class)
{

    $class_data = explode('\\', $namespace_class);
    $class_name = $class_data[count($class_data) - 1] . '.php';
    $path       = implode('/', $class_data);
    $path .= '.php';

     echo '<br>'.$namespace_class;

    if (substr($class_name, 0, 1) == 'IFace') {
        // echo $path;
    } else {
        if (strpos($class_name, 'Controller')) {
            $dir = _BASE_DIR_APP_ . 'controllers/';
            require_once($dir . $path);
        } else {
            if (file_exists(_BASE_DIR_APP_ . 'classes/' . $path)) {
                require_once(_BASE_DIR_APP_ . 'classes/' . $path);
            } else if (file_exists(_BASE_DIR_CORE_ . 'classes/' . $path)) {
                require_once(_BASE_DIR_CORE_ . 'classes/' . $path);
            }
        }
    }
}



