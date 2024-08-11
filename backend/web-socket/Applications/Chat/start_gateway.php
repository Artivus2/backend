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
use \GatewayWorker\Gateway;
use \Workerman\Autoloader;

require_once __DIR__ . '/../../vendor/autoload.php';

// gateway 进程
$gateway = new Gateway("Websocket://0.0.0.0:7272");
// Установите имя для удобного просмотра во время статуса.
$gateway->name = 'ChatGateway';
// Установите количество процессов. Обычно двух процессов достаточно.
$gateway->count = 2;
// При распределенном развертывании укажите IP-адрес интрасети (не 127.0.0.1).
$gateway->lanIp = '127.0.0.1';
// Начальный порт внутренней связи. Если $gateway->count=2, начальный порт — 2300.
// Обычно 2 порта 2300 и 2301 используются в качестве внутренних портов связи.
$gateway->startPort = 2300;
// интервал сердцебиения
$gateway->pingInterval = 10;
// данные сердцебиения
$gateway->pingData = '{"type":"ping"}';
// Адрес регистрации услуги
$gateway->registerAddress = '127.0.0.1:1236';

/* 
// Когда клиент подключается, установите для соединения onWebSocketConnect, который является обратным вызовом во время рукопожатия веб-сокета.
$gateway->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection , $http_header)
    {
        // Здесь вы можете определить, является ли источник соединения легальным. Если он нелегален, закройте соединение.
        // $_SERVER['HTTP_ORIGIN'] идентифицирует ссылку веб-сокета, инициированную страницей, с которой сайт
        if($_SERVER['HTTP_ORIGIN'] != 'http://chat.workerman.net')
        {
            $connection->close();
        }
        // onWebSocketConnect Внутри $_GET доступен $_SERVER. Vnutri $_GET dostupen $_SERVER.
        // var_dump($_GET, $_SERVER);
    };
}; 
*/

// Если он не запущен в корневом каталоге, запустите метод runAll.
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

