<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

require_once __DIR__ . '/../../vendor/autoload.php';

// Рабочий процесс
$worker = new BusinessWorker();
// worker名称
$worker->name = 'ChatBusinessWorker';
// bussinessWorker количество процессов
$worker->count = 4;
// Адрес регистрации услуги
$worker->registerAddress = '127.0.0.1:1236';

// Если он не запущен в корневом каталоге, запустите метод runAll.
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

