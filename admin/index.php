<?php

use engine\Application as App;

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестовое приложение</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>

<body>
    <section class="content">
        <a href="/logout" class="logout">Выйти</a>
        <h3><?= htmlentities(App::getInstance()->getUser()->getUserName()) . ', '; ?>добро пожаловать в административную часть</h3>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.
            Excepturi reiciendis, non magnam enim tempore magni reprehenderit
            saepe asperiores deserunt debitis error et quia.
            Velit itaque ullam odit quis necessitatibus exercitationem?</p>
    </section>
</body>

</html>