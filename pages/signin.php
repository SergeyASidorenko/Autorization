<?php

use engine\Application as App;
use engine\errors\WebError;
use engine\tables\User;

// идентификатор формы, чтобы обрабатывать сведения именно о нашей форме
$signinFormID = '';
$isRememberMe = false;
if (isset($_SESSION['forms']['signin_form_id'])) {
    $signinFormID = $_SESSION['forms']['signin_form_id'];
}
if (!isset($_SESSION['forms'])) {
    $_SESSION['forms'] = [];
}
$_SESSION['forms']['signin_form_id'] = bin2hex(random_bytes(0x20));
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin_form_token']) && $_POST['signin_form_token'] == $signinFormID) {
    try {
        $user = User::find(['name' => $_POST['username']]);
        if (!password_verify($_POST['password'], $user->password)) {
            throw new WebError("Неверные имя пользователя или пароль!");
        }
        $userID = $user->getID();
        $isRememberMe = isset($_POST['remember_me']);
        // если пользователь выставил галочку "Запомнить меня"
        if ($isRememberMe) {
            // формируем ключ пользователя для последующей идентификации его по кукам
            $user->token = $App->createTokenByUserID($userID);
            $user->save();
            // Запомнить пароль на 2 часа
            $time = time() + 3600 * 2;
            setcookie('user_id', $userID, $time);
            setcookie('secret', $App->hashDataByKey($user->password,  $user->token), $time);
        }
        // очистка вспомогательны данных при заполнении даной формы
        unset($_SESSION['forms']);
        $_SESSION['user_id'] = $userID;
        // все авторизация прошла успешно - переходим в административную панель
        App::getInstance()->redirect('admin');
    } catch (WebError $e) {
        $_SESSION['forms']['signin_error'] = $e->getMessage();
        $_SESSION['forms']['signin_username'] = $_POST['username'];
        if ($_POST['password'] != '') {
            $_SESSION['forms']['signin_password'] = $_POST['password'];
        }
        if ($isRememberMe) {
            $_SESSION['forms']['remember_me'] = $isRememberMe;
        }
        App::getInstance()->redirect('signin');
    }
}
?>
<? if (isset($_SESSION['forms']['signin_error'])) : ?>
    <p class="error"><?= $_SESSION['forms']['signin_error'] ?></p>
    <? unset($_SESSION['forms']['signin_error']); ?>
<? endif; ?>
<? require 'header.php'; ?>
<h3>Вход в систему</h3>
<form id="signin_form" method="post">
    <input placeholder="Имя пользователя" name="username" type="text" value="<?= isset($_SESSION['forms']['signin_username']) ? $_SESSION['forms']['signin_username'] : '' ?>">
    <input placeholder="Пароль" name="password" type="password" value="<?= isset($_SESSION['forms']['signin_password']) ? $_SESSION['forms']['signin_username'] : '' ?>">
    <label for="remember_me">Запомнить меня</label><input id="remember_me" type="checkbox" name="remember_me" <?= $isRememberMe !== false ? 'checked' : '' ?>>
    <input type="hidden" name="signin_form_token" value="<?= $_SESSION['forms']['signin_form_id'] ?>">
    <input type="submit" value="Войти">
</form>