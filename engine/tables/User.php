<?php

/**
 * @link https://github.com/SergeyASidorenko
 * @copyright Sergey Sidorenko
 * @license https://github.com/SergeyASidorenko/license/
 */

namespace engine\tables;

use engine\Application as App;
use engine\errors\SystemError;
use engine\errors\WebError;

/**
 * Класс, представлящий запись в таблице user
 *
 * @author Sergey Sidorenko <carotage@mail.ru>
 */
class User extends DBTable
{
    /**
     * {@inheritdoc}
     */
    protected static $tableName = 'user';
    /**
     * {@inheritdoc}
     */
    protected $fields = ['name' => '', 'password' => '', 'token' => ''];
    /**
     * Метод возвращает значение поля записи
     * @param string $username имя пользователя.
     * @param string $password пароль пользователя.
     * @param bool $isValidate валимировать ли поля при создании объекта.
     * @return User
     */
    public function __construct($username = '', $password = '', $isValidate = false)
    {
        $this->name = $username;
        if ($isValidate) {
            $this->password = $password;
            $this->validate();
        }
        $this->password = App::getInstance()->hashPassword($password);
    }
    /**
     * {@inheritdoc}
     */
    public static function find(array $params)
    {
        $user = null;
        $db = App::getInstance()->db;
        $exec_array = [];
        $query = 'SELECT * FROM user WHERE ';
        $condition = '';
        foreach ($params as $key => $value) {
            if (!empty($condition)) {
                $condition .= ' AND ';
            }
            $condition .= "$key = :$key";
            $exec_array[":$key"] = $value;
        }
        $query .= $condition;
        $stmt = $db->prepare($query);
        if ($stmt  === false) {
            throw new SystemError(sprintf("Ошибка выборки записи из таблицы %s: %s", self::tableName(), $db->errorInfo()[2]));
        }
        if ($stmt->execute($exec_array) === false) {
            throw new SystemError(sprintf("Ошибка выборки записи из таблицы %s: %s", self::tableName(), $db->errorInfo()[2]));
        }
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result !== false) {
            $user = new User();
            foreach ($result as $key => $value) {
                $user->$key = $value;
            }
        }
        return $user;
    }
    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $db = App::getInstance()->db;
        $this->validate();
        $isInsert = false;
        if ($this->id) {
            $query = "UPDATE user SET %s WHERE id={$this->id}";
            $queryParams = '';
            foreach ($this->fields as $name => $field) {
                if ($queryParams != '') {
                    $queryParams .= ', ';
                }
                $queryParams .= "$name='$field'";
            }
            $query = sprintf($query, $queryParams);
        } else {
            $isInsert = true;
            $query = sprintf("INSERT INTO user(name, password) VALUES('%s','%s')", $this->name, $this->password);
            if (isset($this->token)) {
                $query = sprintf("INSERT INTO user(name, password, token) VALUES('%s','%s','%s')", $this->name, $this->password, $this->token);
            }
        }
        if ($db->query($query) === false) {
            throw new SystemError(sprintf("Ошибка сохранения записи в таблицу %s: %s", self::tableName(), $db->errorInfo()[2]));
        }
        if ($isInsert) {
            $this->id = $db->lastInsertId();
        }
    }
    /**
     * {@inheritdoc}
     *
     */
    public function validate()
    {
        $this->name = preg_replace('/^[^\w]$/', '', $this->name);
        if (!preg_match('/[^\x00-\x1f]{2,}/', $this->name)) {
            throw new WebError("Поле \"Имя пользователя\" заполнено неверно! Имя не должно быть короче 2 символов");
        }
        if (!preg_match('/[^\x00-\x1f]{6,}/', $this->password)) {
            throw new WebError("Поле \"Пароль\" заполнено неверно! Пароль короче 6 символов");
        }
    }
}
