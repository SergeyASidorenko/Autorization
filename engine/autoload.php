<?php

/**
 * @link https://github.com/SergeyASidorenko
 * @copyright Sergey Sidorenko
 * @license https://github.com/SergeyASidorenko/license/
 */
function __autoload($classname)
{
    $classPath = $_SERVER['DOCUMENT_ROOT'];
    $arrClassPath = explode('\\', $classname);
    foreach ($arrClassPath as $classPathElem) {
        $classPath .= "/$classPathElem";
    }
    require_once("$classPath.php");
}
