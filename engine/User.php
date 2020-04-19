<?php

/**
 * @link https://github.com/SergeyASidorenko
 * @copyright Sergey Sidorenko
 * @license https://github.com/SergeyASidorenko/license/
 */

namespace engine;

use engine\tables\User as UserTable;

/**
 * Класс, представлящий сведения о текущем пользователе приложения
 *
 * @author Sergey Sidorenko <carotage@mail.ru>
 */
class User
{
    /**
     * @var integer идентификатор пользователя.
     */
    private $id;
    /**
     * @var string имя пользователя.
     */
    private $userName;
    /**
     * Конструктор 
     * @param engine\tables\User $user объект, описывающий пользователя в БД.
     * @return engine\User
     */
    public function __construct(UserTable $user = null)
    {
        $this->id = 0;
        $this->userName = '';
        if ($user !== null) {
            $this->id = $user->getID();
            $this->userName = $user->name;
        }
    }
    /**
     * Метод возвращает статус пользователя (гость или зарегистрированный)
     * @return bool если гость - возвращает true
     */
    public function isGuest()
    {
        return $this->id <= 0;
    }
    /**
     * Метод возвращает логин текущего пользователя
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }
}
