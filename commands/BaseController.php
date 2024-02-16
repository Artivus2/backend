<?php

namespace app\commands;

use Yii;
use yii\console\Controller;


class BaseController extends Controller
{
    protected function checkProcess($text)
    {
        $process = shell_exec('ps ax | grep yii');
        return mb_substr_count(mb_strtolower($process), $text) > 2;
    }

    protected function consoleLog($text) {
        echo date("[d.m.y H:i:s]" ) . " " . $text . "\n";
    }
}
