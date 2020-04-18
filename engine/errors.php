<?php

/**
 * @link https://github.com/SergeyASidorenko
 * @copyright Sergey Sidorenko
 * @license https://github.com/SergeyASidorenko/license/
 */

namespace engine\errors;

/**
 * Абстрактный класс хранения ошибки.
 * @author Sergey Sidorenko <carotage@mail.ru>
 */
abstract class AppError extends \Error
{
    /**
     * Логирование ошибок в файл
     */
    public function log()
    {
        error_log($this->getMessage());
    }
}
/**
 * Класс хранения пользовательсикх ошибок.
 * @author Sergey Sidorenko <carotage@mail.ru>
 */
class WebError extends AppError
{
}
/**
 * Класс хранения системных ошибок.
 * @author Sergey Sidorenko <carotage@mail.ru>
 */
class SystemError extends AppError
{
}
