<?php

/**
 * @link https://github.com/SergeyASidorenko
 * @copyright Sergey Sidorenko
 * @license https://github.com/SergeyASidorenko/license/
 */

namespace engine\tables;

use engine\errors\SystemError;

/**
 * Класс, описывающий сущность таблицы в БД
 *
 * @author Sergey Sidorenko <carotage@mail.ru>
 */
abstract class DBTable
{
    /**
     * @var array список полей таблицы.
     */
    protected $fields;
    /**
     * @var integer идентификатор записи
     */
    protected $id;
    /**
     * @var string имя таблицы.
     */
    protected static $tableName;

    /**
     * Метод возвращает идентификатор записи
     * @return integer идентификатор записи
     */
    public function getID()
    {
        return $this->id;
    }
    /**
     * Метод возвращает имя таблицы
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return static::$tableName;
    }
    /**
     * Метод возвращает значение поля записи
     * @param string $property имя поля.
     * @return string значение поля
     */
    public function __get($property)
    {
        $property = strtolower($property);
        if (array_key_exists($property, $this->fields)) {
            return $this->fields[$property];
        } else {
            throw new SystemError(Sprintf("Доступ к несуществующему полю %s таблицы %s", $property, self::tableName()));
        }
    }
    /**
     * Метод установки занчения поля записи
     * @param string $property имя поля.
     * @param string $value значение поля
     */
    public function __set($property, $value)
    {
        $property = strtolower($property);
        if (array_key_exists($property, $this->fields)) {
            $this->fields[$property] = $value;
        } else {
            throw new SystemError(Sprintf("Доступ к несуществующему полю %s таблицы %s", $property, self::tableName()));
        }
    }
    /**
     * Метод валидации полей записи
     */
    abstract public function validate();
    /**
     * Метод добавления/обновления записи
     */
    abstract public function save();
    /**
     * Метод производит поиск записи типа User по заданным параметрам
     * @param array $params массив параметров.
     * @return User значение поля
     */
    abstract public static function find(array $params);
}
