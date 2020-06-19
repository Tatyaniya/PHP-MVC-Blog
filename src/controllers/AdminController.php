<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Message;

class AdminController extends BaseController
{

    public function __construct()
    {
        if ($_SESSION['user_id'] != ADMIN) {
            throw new \Exception();
        }
    }

    /**
     * если админ - можем удалять сообщения
     */
    public function delete()
    {
        $id = (int)$_GET['id'] ?? 0;

        $model = new Message();

        $message = $model->getOneMes($id);
        if (!empty($message)) {
            $file = 'images/' . $message['image'];

            if (file_exists($file)) {
                unlink($file);
            }
            $model->delete($id);
        }

        header('Location: /message');
        exit();
    }
}