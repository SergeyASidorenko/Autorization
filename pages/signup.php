<?php

use engine\Application as App;
use engine\errors\WebError;
use engine\tables\User;

// идентификатор формы, чтобы обрабатывать сведения именно о нашей форме
$signupFormID = '';
if (isset($_SESSION['forms']['signup_form_id'])) {
    $signupFormID = $_SESSION['forms']['signup_form_id'];
}
if (!isset($_SESSION['forms'])) {
    $_SESSION['forms'] = [];
}
$_SESSION['forms']['signup_form_id'] = bin2hex(random_bytes(0x20));
if (
    $_SERVER['REQUEST_METHOD'] == 'POST'        &&
    isset($_POST['signup_form_token'])          &&
    $_POST['signup_form_token'] == $signupFormID
) {
    try {
        $user = new User($_POST['username'], $_POST['password'], true);
        if (User::find(['name' => $user->name])) {
            throw new WebError("Пользователь с таким именем уже зарегистрирован!");
        }
        $user->save();
        // очистка вспомогательны данных при заполнении даной формы
        unset($_SESSION['forms']);
        $_SESSION['user_id'] = $user->getID();
        // все авторизация прошла успешно - переходим в административную панель
        App::getInstance()->redirect('admin');
    } catch (WebError $e) {
        $_SESSION['forms']['signup_error'] = $e->getMessage();
        $_SESSION['forms']['signup_username'] = $_POST['username'];
        if ($_POST['password'] != '') {
            $_SESSION['forms']['signin_password'] = $_POST['password'];
        }
        App::getInstance()->redirect('signup');
    }
}
?>
<? if (isset($_SESSION['forms']['signup_error'])) : ?>
    <p class="error"><?= $_SESSION['forms']['signup_error'] ?></p>
    <? unset($_SESSION['forms']['signup_error']); ?>
<? endif; ?>
<? require 'header.php'; ?>
<h3>Регистрация</h3>
<form id="signup_form" method="post">
    <input placeholder="Имя пользователя" name="username" type="text" value="<?= isset($_SESSION['forms']['signup_username']) ? $_SESSION['forms']['signup_username'] : '' ?>">
    <input placeholder="Пароль" name="password" type="password" value="<?= isset($_SESSION['forms']['signup_password']) ? $_SESSION['forms']['signup_password'] : '' ?>">
    <input type="hidden" name="signup_form_token" value="<?= $_SESSION['forms']['signup_form_id'] ?>">
    <input type="submit" value="Зарегистрироваться">
</form>