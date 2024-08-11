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
use \GatewayWorker\Register;

require_once __DIR__ . '/../../vendor/autoload.php';

// Служба регистрации должна быть текстовым протоколом. В качестве адреса прослушивания используйте внутренний IP-адрес или 127.0.0.1.
// По соображениям безопасности регистр не может прослушивать 0.0.0.0, то есть служба регистрации не может быть доступна внешней сети.
$register = new Register('text://127.0.0.1:1236');

// Если он не запущен в корневом каталоге, запустите метод runAll.
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

