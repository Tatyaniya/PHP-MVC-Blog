<?php

namespace App\Controllers;

use App\Models\DB;
use App\Models\User;
use App\Models\Message;

class FrontController extends BaseController
{
    /**
     * отображение форм
     */
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /message');
            exit;
        }
        $this->render('login');
    }

    /**
     * валидация
     * @param $data
     * @return array
     */
    public function valider($data)
    {
        $errors = [];
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Некорректный email";
        }
        if (mb_strlen($data['password']) < 4) {
            $errors[] = "Длина пароля должна быть не менее 4-х символов";
        }
        if ($data['password'] !== $data['password2']) {
            $errors[] = "Пароли не совпадают";
        }
        return $errors;
    }

    /**
     * регистрация
     * @param array $data
     */
    public function register(array $data)
    {
        $errors = $this->valider($data);
        if (sizeof($errors) > 0) {
            header('Location: /?error=1');
            exit();
        }
        $model = new User();
        $model->add($data);

        $email = $data['email'];
        $passwordInput = $model->getPasswordHash($data['password']);

        $user = $model->get($email, $passwordInput);
        $_SESSION['user_id'] = $user['id'];
        header('Location: /message');

        exit;
    }

    /**
     * авторизация пользователя
     * @param array $data
     */
    public function login(array $data)
    {
        $model = new User();

        $email = $data['email'];
        $passwordInput = $model->getPasswordHash($data['password']);

        $user = $model->get($email, $passwordInput);

        if (!$user) {
            echo 'Неверный логин или пароль';
            $this->render('login');
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        header('Location: /message');

        exit();
    }

    /**
     * отображает сообщения
     */
    public function view()
    {

        $isAdmin = ($_SESSION['user_id'] == ADMIN);

        $info = array_reverse($this->getAllMessages());

        $this->render('blog', [
            'items' => $info,
            'is_admin' => $isAdmin
        ]);

    }

    /**
     * выводим имя пользователя над формой
     * @return mixed
     */
    public function displayName()
    {
        $user = new User();
        return $user->getUserName($_SESSION['user_id']);
    }

    /**
     * получаем все сообщения из базы вместе с имнами пользователей
     * @return array
     */
    public function getAllMessages()
    {
        $message = new Message();

        $messagesAll = $message->getMessages();
        $usersALL = $message->getUsers();

        $AllDisplayMessages = [];
        for ($i = 0; $i < sizeof($messagesAll); $i++) {

            for ($j = 0; $j < sizeof($usersALL); $j++) {

                if ($messagesAll[$i]['user_id'] === $usersALL[$j]['id']) {
                    $AllDisplayMessages[] = [
                        'id' => $messagesAll[$i]['id'],
                        'name' => $usersALL[$j]['name'],
                        'date' => $messagesAll[$i]['date'],
                        'image' => $messagesAll[$i]['image'],
                        'text' => $messagesAll[$i]['text']
                    ];
                }
            }
        }

        return $AllDisplayMessages;
    }

    /**
     * добавляет сообщения
     * @param array $data
     */
    public function add(array $data)
    {
        $userId = $_SESSION['user_id'];

        $message = new Message();

        $img = $this->addImage();
        $message->loadImage($img);
        $file = $message->getImage();


        $message->addMes($userId, $data, $file);

        header('Location: /message');
        exit();
    }

    /**
     * берем картинку из формы
     * @return mixed
     */
    public function addImage()
    {
        if (!empty($_FILES['image']['tmp_name'])) {
            return $_FILES['image']['tmp_name'];
        }
    }

    /**
     * разлогиниваемся
     */
    public function logout()
    {
        $_SESSION['user_id'] = null;
        header('Location: /');
        exit();
    }

    /**
     * возвращает 20 последних сообщений 1 пользователя в json
     * @return string
     */
    public function api()
    {
        $userId = (int)$_GET['user_id'] ?? 0;

        if (!$userId) {
            echo 'такого пользователя нет';
            exit();
        }

        $message = new Message();
        $info = $message->getUserMes($userId, 20);

        $lastMessages = [];
        for ($i = 0; $i < sizeof($info); $i++) {
            $lastMessages[] = [
                'name' => $info[$i]['name'],
                'date' => $info[$i]['date'],
                'image' => $info[$i]['image'],
                'text' => $info[$i]['text']
            ];
        }

        echo json_encode($lastMessages);

        if (!$info) {
            return 'Сообщений нет';
        }
    }
}