<?php

/**
 * @link https://github.com/SergeyASidorenko
 * @copyright Sergey Sidorenko
 * @license https://github.com/SergeyASidorenko/license/
 */
session_start();

use engine\Application as App;
use engine\errors\SystemError;

require_once(__DIR__ . '/environment.php');
require_once(__DIR__ . '/engine/autoload.php');
require_once(ENGINE_DIR . '/errors.php');
try {
    $App = App::getInstance();
    $App->login();
} catch (SystemError $e) {
    $App->showInternalErrorPage($e);
}
// Путь к Html странице, которую в результате необходимо выдать в браузер
$htmlPagePath = '';
// Запрашиваемый URL адрес без параметров
$rawQuery = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$queryPaths = explode('/', trim($rawQuery, "\/"));
foreach ($queryPaths as $path) {
    if (!empty($path)) {
        if ($htmlPagePath != '') {
            $htmlPagePath .= DIRECTORY_SEPARATOR;
        }
        $htmlPagePath .= $path;
    }
}
if (preg_match('/^logout/', $htmlPagePath)) {
    $App->logout();
}
// далее формируем маршрут, учитывая, что в адресе могут 
// содержаться как имена папок, так и имена физически существующих файлов
$isAdminRequest = preg_match('/^admin/', $htmlPagePath);
$isUrlWithFile = preg_match('/.+\.(php|html|htm)$/', $htmlPagePath);
if ($isAdminRequest && $App->getUser()->isGuest()) {
    $App->redirect('/signin');
}
if ($isAdminRequest) {
    $htmlPagePath = realpath($htmlPagePath);
} else {
    $htmlPagePath = HTML_PAGES_DIR . DIRECTORY_SEPARATOR . $htmlPagePath;
}
if (!$isUrlWithFile && is_file($htmlPagePath . '.php')) {
    $htmlPagePath .= '.php';
}
if (!$isUrlWithFile && is_dir($htmlPagePath)) {
    $htmlPagePath .= DIRECTORY_SEPARATOR . 'index.php';
}
// если ничего не найдено - формируем страницу 404
if (!is_dir($htmlPagePath) && !is_file($htmlPagePath)) {
    $App->showPageNotFoundError();
}
if (!$App->getUser()->isGuest() && !$isAdminRequest) {
    $App->redirect('/admin');
}
include($htmlPagePath);
