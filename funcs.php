<?php

    function debug ($data) 
    {
        echo '<pre>' . print_r($data, 1) . '</pre>';
    }

    function registration(): bool 
    {

        global $pdo;

        $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
        $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';

        if(empty($login) || empty($pass)) {
            $quote = 'Поля "I`мя/Логін" обов`язкові!';
            $_SESSION['errors'] = quotemeta($quote);
            return false;
        }

        $res = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
        $res->execute([$login]);

        if ($res->fetchColumn()) {
            $_SESSION['errors'] = 'Це ім`я використовується!';
            return false;
        }

        $pass = password_hash($pass, PASSWORD_DEFAULT);
        $res = $pdo->prepare("INSERT INTO users (login, pass) VALUES (?, ?)");
        if ($res->execute([$login, $pass])) {
            $_SESSION['success'] = 'Успішна регістрація!';
            return true;
        }else{
            $_SESSION['errors'] = 'Помилка регістрації!';
            return false;
        }

    }

    function login(): bool
    {
        global $pdo;

        $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
        $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';

        if(empty($login) || empty($pass)) {
            $_SESSION['errors'] = 'Поля "І`мя/Логін" обов`язкові!';
            return false;
        }

        $res = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $res->execute([$login]);
        if(!$user = $res->fetch()) {
            $_SESSION['errors'] = '"Логін/Пароль" введені не корректно';
            return false;
        }

        if(!password_verify($pass, $user['pass'])) {
            $_SESSION['errors'] = '"Логін/Пароль" введені не корректно';
            return false;
        }else{
            $_SESSION['success'] = 'Успішна авторизація! ';
            $_SESSION['user']['name'] = $user['login'];
            $_SESSION['user']['id'] = $user['id'];
            return true;
        }
    }

    function saveMessage(): bool
    {
        global $pdo;

        $message = !empty($_POST['message']) ? trim($_POST['message']): '';

        if(!isset($_SESSION['user']['name'])) {
            $_SESSION['errors'] = 'Увійдіть або зареєструйтесь!';
            return false;
        }

        if(empty($message)) {
            $_SESSION['errors'] = 'Ви не ввели повідомлення!';
            return false;
        }

        $res = $pdo->prepare("INSERT INTO messages (name, message) VALUES (?, ?)");
        if($res->execute([$_SESSION['user']['name'], $message])) {
            $_SESSION['success'] = 'Повідомлення добавлено!';
            return true;
        } else {
            $_SESSION['success'] = 'Помилка додавання повідомлення!';
            return false;
        }

    }

    function getMassage(): array
    {

        global $pdo;

        $res = $pdo->query("SELECT * FROM messages");
        return $res->fetchAll();
        
    }