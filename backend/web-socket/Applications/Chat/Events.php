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

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;

class Events
{
   
   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
        
        // Клиент передает данные JSON
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        
        // Выполнение различных услуг в зависимости от типа
        switch($message_data['type'])
        {
            // Клиент реагирует на пульс сервера
            case 'pong':
                return;
            // Формат сообщения для входа клиента: {тип: логин, имя: xx, room_id: 1}, добавляется к клиенту, транслируется всем клиентам xx для входа в чат.
            case 'login':
                // Определить, есть ли номер комнаты
                if(!isset($message_data['room_id']))
                {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                
                // Введите номер комнаты и никнейм в сеанс.
                $room_id = $message_data['room_id'];
                $client_name = htmlspecialchars($message_data['worker_full_name']);
                $_SESSION['room_id'] = $room_id;
                $_SESSION['worker_full_name'] = $client_name;
              
                // Получить список всех пользователей в комнате
                $clients_list = Gateway::getClientSessionsByGroup($room_id);
                foreach($clients_list as $tmp_client_id=>$item)
                {
                    $clients_list[$tmp_client_id] = $item['sender_worker_id'];
                }
                $clients_list[$client_id] = $client_name;
                
                // Пересылается всем клиентам в текущей комнате, xx входит в сообщение чата {type:login, client_id:xx, name:xx}
                $new_message = array('type'=>$message_data['type'], 'client_id'=>$client_id, 'worker_full_name'=>htmlspecialchars($client_name), 'date_time'=>date('Y-m-d H:i:s'));
                Gateway::sendToGroup($room_id, json_encode($new_message));
                Gateway::joinGroup($client_id, $room_id);
               
                // Отправить список пользователей текущему пользователю 
                $new_message['client_list'] = $clients_list;
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
                
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'chat_message_send':
                // 非法请求
                if(!isset($_SESSION['room_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['worker_full_name'];
                
                // 私聊
                if($message_data['to_client_id'] != 'all')
                {
                    $new_message = array(
                        'type'=>'chat_message_send',
                        'sender_worker_id'=>$client_id, 
                        'worker_full_name' =>$client_name,
                        'to_client_id'=>$message_data['ClientId'],
                        'primary_message'=>"<b>Сказать тебе: </b>".nl2br(htmlspecialchars($message_data['primary_message'])),
                        'date_time'=>date('Y-m-d H:i:s'),
                    );
                    Gateway::sendToClient($message_data['ClientId'], json_encode($new_message));
                    $new_message['content'] = "<b>nы прав".htmlspecialchars($message_data['to_client_name'])."объяснять: </b>".nl2br(htmlspecialchars($message_data['content']));
                    return Gateway::sendToCurrentClient(json_encode($new_message));
                }
                
                $new_message = array(
                    'type'=>'say', 
                    'from_client_id'=>$client_id,
                    'from_client_name' =>$client_name,
                    'to_client_id'=>'all',
                    'primary_message'=>nl2br(htmlspecialchars($message_data['primary_message'])),
                    'date_time'=>date('Y-m-d H:i:s'),
                );
                return Gateway::sendToGroup($room_id ,json_encode($new_message));
        }
   }
   
   /**
    * когда клиент отключается
    * @param integer $client_id
    */
   public static function onClose($client_id)
   {
       // debug
       echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
       
       // Удалить из списка клиентов рума
       if(isset($_SESSION['room_id']))
       {
           $room_id = $_SESSION['room_id'];
           $new_message = array('type'=>'logout', 'sender_worker_id'=>$client_id, 'worker_full_name'=>$_SESSION['worker_full_name'], 'date_time'=>date('Y-m-d H:i:s'));
           Gateway::sendToGroup($room_id, json_encode($new_message));
       }
   }
  
}
