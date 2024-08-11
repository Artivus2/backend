<?php

namespace app\controllers;

use Throwable;
use WebSocket\Client;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;

class WebsocketController extends Controller
{
    // actionWriteLogWs         - метод для записи логов websocket в бд (с помощью метода LogWebSocket)
    // actionSendMsgT           - Создание объекта вебсокета, отправка сообщения
    // actionWriteLogWs         - метод для записи логов websocket в бд (с помощью метода LogWebSocket)
    // actionSendMsg            - Создание объекта вебсокета, отправка сообщения
    // SendMessageToWebSocket   - метод отправки сообщения на вебсокет
    // actionTest               - тестовый метод для сохранения значения из json
    // actionTestWs             - метод отправки сообщения на вебсокет тестовых сообщений
    // actionWebSocket          - Тестовый метод для подключения к вебсокету и отправки текстовых сообщений пользователям по подпискам


    public function actionIndex()
    {
        return $this->render('index');
    }

    // actionWebSocket - Тестовый метод для подключения к вебсокету и отправки текстовых сообщений пользователям по подпискам
    // 127.0.0.1/websocket/web-socket?message="Hello"
    public function actionWebSocket()
    {
//        $url_websocket = 'wss://192.168.1.192:8282/ws';
        $url_websocket = 'ws://localhost:9000/ws';

        $status = 1;
        $warnings = array();
        $errors = array();
        $result = array();
        $client = null;
        try {
            $warnings[] = 'actionWebSocket. Начало выполнения метода';
            $post = Assistant::GetServerMethod();
            // проверка на наличие данных

            if (isset($post['message']) && $post['message'] != '') {
                $message = $post['message'];
            } else {
                throw new \Exception('actionMainStartOpc. Не передан входной параметер message');
            }

            // подготавливаем массив для отправки на вебсокет
            $send_message = array(
                'ClientType' => 'webBack',
                'ActionType' => 'publish',
                'SubscribeList' => array(
                    'webFront',
                    'unityFront',
                    'javaFront',
                ),
                'MessageToSend' => $message
            );
            $warnings[] = $send_message;

            $context = stream_context_create([
                "ssl" => [
                    "verify_peer" => true,                      //  Whether to verify the peer certificate
                    "verify_peer_name" => false,                //  Whether to verify peer_ Name, the default is true. If it is true, peer needs to be set at both the client and the server_ Name parameter
                    "allow_self_signed" => true,                //  Turn on when using self signed certificates

                ]
            ]);

            $client = new Client($url_websocket, [
                'context' => $context,
            ]);
//            $client = new Client($url_websocket);
            if ($client) {
                $client->send(json_encode($send_message));
            } else {
                throw new \Exception('actionWebSocket не смог подключиться к: ' . $url_websocket . '. Проверьте доступ к WebSocket');
            }
        } catch (Throwable $e) {
            $status = 0;
            $errors[] = 'actionStartOpc. Исключение: ';
            $errors[] = $e->getMessage();
            $errors[] = $e->getLine();
        }
        $warnings[] = 'actionStartOpc. Окончание метода';
        $result_main = array('Items' => $result, 'status' => $status, 'errors' => $errors, 'warnings' => $warnings);
        Yii::$app->response->format = Response::FORMAT_JSON;                                                            //формат возвращаемых данных json методом yii2
        Yii::$app->response->data = $result_main;

    }

    /**
     * actionSendMsgT - Создание объекта вебсокета, отправка сообщения
     *
     * @param string $ws_url адрес вебсокет-сервера
     * @param string $msg текст сообщения
     * @throws \WebSocket\BadOpcodeException
     * Пример вызова: http://192.168.1.5/websocket/send-msg?ws_url=%22ws://195.168.1.5/ws%22&msg=%22{%22ClientType%22:%20%22server%22,%22ActionType%22:%20%22publish%22,%22Subscribes%22:%20[%22agreementOrder%22],%22MessageToSend%22:%20{%22order_id%22:%20%22%22,%22order_status_id%22:%20%22%22,%22worker_id%22:%20%22%22}}%22
     */

    public static function actionSendMsgT()
    {
        $result = array();
        $error = array();
        $post = Assistant::GetServerMethod();

        try {

            if (isset($post["ws_url"]) && isset($post["msg"])) {
                $ws_url = $post["ws_url"];
                $msg = $post["msg"];
            } else {
                throw new Exception(__FUNCTION__ . 'не все параметры переданы');
            }
            $context = stream_context_create([
                "ssl" => [
                    "verify_peer" => true,                      //  Whether to verify the peer certificate
                    "verify_peer_name" => false,                //  Whether to verify peer_ Name, the default is true. If it is true, peer needs to be set at both the client and the server_ Name parameter
                    "allow_self_signed" => true,                //  Turn on when using self signed certificates

                ]
            ]);

            $client = new Client($ws_url, [
                'context' => $context,
            ]);
            if ($client) {
                $client->send($msg);

            } else {
                throw new \Exception(__FUNCTION__ . ' не смог подключиться к: ' . $ws_url . '. Проверьте доступ к WebSocket');
            }
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
        }
        $main_result = array('Result ' => $result, 'Error ' => $error);
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->data = $main_result;

    }

    /**
     * actionWriteLogWs - метод для записи логов websocket в бд (с помощью метода LogWebSocket)
     * входные параметры:
     * text_error - текст ошибки
     * date_time - время возникновении ошибок
     * пример вызова: 127.0.0.1:98/websocket/write-log-ws?text_error=text&date_time=2019.10.14
     * разработал: Fayzulloev A.
     */
    public static function actionWriteLogWs()
    {

        $status = 1;
        $warnings = array();
        $errors = array();
        $result = array();

        try {
            $warnings[] = "actionWriteLogWs. start";
            $post = Assistant::GetServerMethod();

            /**
             * блок валидация входных данных
             * */

            $post_valid = isset($post['text_error'], $post['date_time']);
            if (!$post_valid) {
                throw new \Exception(__FUNCTION__ . '. Не все входные параметры инициализированы');
            }

            if (empty($post['text_error'])) {
                throw new \Exception(__FUNCTION__ . '. Не передан текст ошибки');
            }

            if (empty($post['date_time'])) {
                throw new \Exception(__FUNCTION__ . '. Не передано время события');
            }

            $error = $post['text_error'];
            $date_time = $post['date_time'];
            // вызов метода для сохранение значение в бд
            LogGR::LogWebSocket($error, $date_time);
        } catch (Throwable $exception) {
            $status = 0;
            $errors[] = "actionWriteLogWs. Исключение";
            $errors[] = $exception->getMessage();
            $errors[] = $exception->getLine();
        }
        $warnings[] = "actionWriteLogWs. ended";
        $result = array('status' => $status, 'warnings' => $warnings, 'errors' => $errors);
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->data = $result;
    }

    // actionTest - тестовый метод для сохранение значение из json
    public static function actionTest()
    {

        $status = 1;
        $warnings = array();
        $errors = array();
        $result = array();
        try {
            $warnings[] = "actionWriteLogWs. start";
            $post = Assistant::GetServerMethod();

            /**
             * блок валидация входных данных
             */
            $post_valid = isset($post['value']);
            if (!$post_valid) {
                throw new \Exception('. Не все входные параметры инициализированы');
            }

            $value = json_decode($post['value']);
            if ($value === null) {
                throw new \Exception(__FUNCTION__ . '. Получен невалидный json: ' . $post['value']);
            }

            if (!isset($value->text_error)) {
                throw new \Exception(__FUNCTION__ . '. Не передан текст ошибки');
            }

            if (!isset($value->date_time)) {
                throw new \Exception(__FUNCTION__ . '. Не передана дата');
            }

            // вызов метода для сохранение значение в бд
            $response = LogGR::LogWebSocket($value->text_error, $value->date_time);
            if ($response['status'] == 1) {
                $warnings[] = $response['warnings'];
            } else {
                $errors[] = $response['errors'];
                throw new \Exception(__FUNCTION__ . '. Ошибка записи лога в БД');
            }
        } catch (Throwable $exception) {
            $status = 0;
            $errors[] = "actionWriteLogWs. Исключение";
            $errors[] = $exception->getMessage();
            $errors[] = $exception->getLine();
        }
        $warnings[] = "actionWriteLogWs. ended";
        $result = array('status' => $status, 'warnings' => $warnings, 'errors' => $errors);
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->data = $result;
    }

    /**
     * actionSendMsg - Создание объекта вебсокета, отправка сообщения
     *
     * @param string $ws_url адрес вебсокет-сервера
     * @param string $msg текст сообщения
     * @throws \WebSocket\BadOpcodeException
     *
     */
    public static function actionSendMsg($ws_url, $msg)
    {

        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,                      //  Whether to verify the peer certificate
                "verify_peer_name" => false,                //  Whether to verify peer_ Name, the default is true. If it is true, peer needs to be set at both the client and the server_ Name parameter
                "allow_self_signed" => true,                //  Turn on when using self signed certificates
//                "local_cert" => true,                //  Turn on when using self signed certificates
//                "local_pk" => true,                //  Turn on when using self signed certificates
            ]
        ]);

        $client = new Client($ws_url, [
            'context' => $context,
        ]);

        $client->send($msg);

//        $ws_url = str_replace("wss://", "ssl://", $ws_url);
//        $context = stream_context_create();
//        stream_context_set_option($context, 'ssl', 'verify_peer', false);
//        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
//        stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
//
//        $socket = stream_socket_client($ws_url, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
//
//        fwrite($socket, $msg);
//
//        fclose($socket);

//        try {
//            $client = new Client($ws_url);
//            if ($client) {
//                $client->send($msg);
//            } else {
//                throw new \Exception(__FUNCTION__ . ' не смог подключиться к: ' . $ws_url . '. Проверьте доступ к WebSocket');
//            }
//        } catch (Throwable $exception) {
//            //TODO добавить обработку ошибок через централизованный лог
//        }
    }

    // SendMessageToWebSocket - метод отправки сообщения на вебсокет
    // входные параметры:
    //      subPubList      - подписка(текстовый параметр)
    //      messageToSend   - массив или объект для отправки сообщений на вебсокет
    // разработал Якимов М.Н.
    public static function SendMessageToWebSocket($subPubList, $messageToSend)
    {
        $warnings = array();
        $errors = array();
        $status = 1;
        try {
            $warnings[] = "SendMessageToWebSocket. Начал отправку на вебсокет";
            if (!empty($messageToSend)) {
                $warnings[] = "SendMessageToWebSocket. Отправляю на веб сокет";
                $ws_msg = json_encode(array(
                    'ClientType' => 'server',
                    'ActionType' => 'publish',
                    'ClientId' => 'server',
                    'Subscribes' => [$subPubList],                                                                      // новое событие в журнале событий
                    'MessageToSend' => json_encode($messageToSend)
                ));
                self::actionSendMsg('ws://' . GR_CONNECT_STRING_WEBSOCKET . '/ws', $ws_msg);
                if (COD and COD_WEB_SOCKET) {
                    self::actionSendMsg('wss://' . GR_CONNECT_STRING_WEBSOCKET_VORG . '/ws', $ws_msg);
                    self::actionSendMsg('wss://' . GR_CONNECT_STRING_WEBSOCKET_ZAPOL . '/ws', $ws_msg);
                    self::actionSendMsg('wss://' . GR_CONNECT_STRING_WEBSOCKET_VORKUT . '/ws', $ws_msg);
                    self::actionSendMsg('wss://' . GR_CONNECT_STRING_WEBSOCKET_KOMSA . '/ws', $ws_msg);
                }
            }
        } catch (Throwable $exception) {
            $status = 0;
            $errors[] = "SendMessageToWebSocket. Исключение";
            $errors[] = $exception->getMessage();
            $errors[] = $exception->getLine();
        }
        $warnings[] = "SendMessageToWebSocket. Закончил отправку на вебсокет";

        return array('status' => $status, 'warnings' => $warnings, 'errors' => $errors);
    }

    // actionTestWs - метод отправки сообщения на вебсокет тестовый сообщений
    // входные параметры:
    //      subPubList      - подписка(текстовый параметр)
    //      messageToSend   - массив или объект для отправки сообщений на вебсокет
    //
    public static function actionTestWs($subPubList, $messageToSend = 'test')
    {
        $warnings = array();
        $errors = array();
        $status = 1;
        try {
            $warnings[] = "SendMessageToWebSocket. Начал отправку на вебсокет";
            if (!empty($messageToSend)) {
                $warnings[] = "SendMessageToWebSocket. Отправляю на веб сокет";
                $ws_msg = json_encode(array(
                    'ClientType' => 'server',
                    'ActionType' => 'publish',
                    'ClientId' => 'server',
                    'Subscribes' => ['GR_CONNECT_STRING_WEBSOCKET'], // новое событие в журнале событий
                    // 'Subscribes' => ['GR_CONNECT_STRING_WEBSOCKET_INNER'], // новое событие в журнале событий
                    //  'Subscribes' => ['GR_CONNECT_STRING_WEBSOCKET_OUTER'], // новое событие в журнале событий
                    'MessageToSend' => json_encode($messageToSend)
                ));
                self::actionSendMsg('wss://' . GR_CONNECT_STRING_WEBSOCKET . '/ws', $ws_msg);
                //   self::actionSendMsg('wss://' . GR_CONNECT_STRING_WEBSOCKET_INNER . '/ws', $ws_msg);
                //   self::actionSendMsg('wss://' . GR_CONNECT_STRING_WEBSOCKET_OUTER . '/ws', $ws_msg);
            }
        } catch (Throwable $exception) {
            $status = 0;
            $errors[] = "SendMessageToWebSocket. Исключение";
            $errors[] = $exception->getMessage();
            $errors[] = $exception->getLine();
        }
        $warnings[] = "SendMessageToWebSocket. Закончил отправку на вебсокет";
        $result = array('status' => $status, 'warnings' => $warnings, 'errors' => $errors);
        return $result;
    }
}
