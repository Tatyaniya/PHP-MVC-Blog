<?php

namespace App\Controllers;

use App\Models\Config;


class BaseController
{
    protected function render($template, $data = [])
    {
        if ($data != []) {
            extract($data);
        }

        include __DIR__ . '/../views/' . $template . '.php';
    }
}