<?php

/**
 * @link https://github.com/SergeyASidorenko
 * @copyright Sergey Sidorenko
 * @license https://github.com/SergeyASidorenko/license/
 */

namespace engine;

use engine\User;
use engine\tables\User as UserTable;
use engine\errors\SystemError;

/**
 * Класс, представлящий веб-приложение
 *
 * @author Sergey Sidorenko <carotage@mail.ru>
 */
class Application
{
    // Экземпляр веб-приложения
    private static $app;
    // Объект подключения к базе данных
    public $db;
    // Сведения о текущем пользователе
    private $user;
    // Путь к фалу лога ошибок
    private $logPath;
    /**
     * Метод создания объекта приложения
     * @param User сведения о текущем пользователе веб-приложения
     * @return Application
     */
    private function __construct(User $user)
    {
        if ($user == null) {
            throw new SystemError("Ошибка инициализации сведений о пользователе приложения");
        }
        $this->user = $user;
    }
    /**
     * Метод возвращает или создает, если не создан ранее, объект веб-приложения
     * @return Application
     */
    public static function getInstance()
    {
        if (self::$app === null) {
            // Читаем содержимое фацла настроек
            $settings_data = file_get_contents(__DIR__ . '/config/config.json');
            if ($settings_data === false) {
                throw new SystemError('Не удалось считать файл настроек');
            }
            $settings = json_decode($settings_data, true);
            if (!isset($settings['db']) || !isset($settings['log'])) {
                throw new SystemError('Файл настроек приложения имеет неверный формат');
            }
            $db = $settings['db'];
            self::$app = new Application(new User());
            try {
                self::$app->db = new \PDO(sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4', $db['hostname'], $db['dbname'], $db['port']), $db['user'], $db['password']);
            } catch (\Error $e) {
                throw new SystemError($e->getMessage());
            }
            self::$app->logPath = $settings['log'];
            // Установка пути к логу ошибок
            ini_set('error_log', self::$app->getLogPath());
        }
        return self::$app;
    }
    /**
     * Метод объект описания текущего пользователя
     * @return engine\User
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * Метод создает токен или секретный ключ на основе идентификатора пользователя для авторизации по куки
     * @param Integer $user_id имя пользователя.
     * @return string
     */
    public function createTokenByUserID($user_id)
    {
        return md5(random_bytes(0x1F) . $user_id);
    }
    /**
     * Метод для хэширования пароля
     * @param string $password имя пользователя.
     * @return string
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    /**
     * Метод хэширования данных секретным ключом
     * @param string $data дынне для хэширования.
     * @param string $key секретный ключ.
     * @return string
     */
    public function hashDataByKey($data, $key)
    {
        return md5($data . $key);
    }
    /**
     * Метод регистрации входа порльзователя в систему
     */
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            $user = UserTable::find(['id' => $_SESSION['user_id']]);
            if ($user) {
                $this->user = new User($user);
            }
        } elseif (isset($_COOKIE['user_id']) && isset($_COOKIE['secret'])) {
            $user = UserTable::find(['id' => $_COOKIE['user_id']]);
            if ($user) {
                if ($this->hashDataByKey($user->password, $user->token) === $_COOKIE['secret']) {
                    $this->user = new User($user);
                }
            }
        }
    }
    /**
     * Метод очистки регистрации пользователя в системе веб-приложения
     */
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }
        $this->redirect('/', true);
    }
    /**
     * Метод возвращает путь к папке, содержащей лог ошибок
     * @return string путь к логу ошибок
     */
    public function getLogPath()
    {
        return $this->logPath;
    }
    /**
     * Метод отображает страницу при возникновении HTTP ответа с кодом 500
     */
    public function showInternalErrorPage(\Error $err)
    {
        error_log(sprintf("%s Ошибка: %s, файл %s, строка: %s;", date('d-m-Y h:i:s'), $err->getMessage(), $err->getFile(), $err->getLine()));
        http_response_code(500);
        include(HTML_PAGES_DIR . '/500.php');
        exit();
    }
    /**
     * Метод отображает страницу при возникновении HTTP ответа с кодом 404
     */
    public function showPageNotFoundError()
    {
        http_response_code(404);
        include(HTML_PAGES_DIR . '/404.php');
        exit();
    }
    /**
     * Метод перенаправления HTTP-запроса на указанный адрес
     * @param string $url адрес перенаправления
     * @param bool $isLogOut разлогировать пользователя или нет
     */
    public function redirect($url, $isLogOut = false)
    {
        if ($isLogOut) {
            setcookie('user_id', "", time() - 3600);
            setcookie('secret', "", time() - 3600);
        } else {
            http_response_code(301);
        }
        header("Location: $url");
        exit();
    }
}
