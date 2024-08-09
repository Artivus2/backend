<?php


namespace app\modules\api\controllers;

use Exception;
use app\modules\api\controllers\Assistant;
use app\models\chat\ChatCacheModel;
use app\models\chat\ChatDatabaseModel;
use app\models\chat\ChatMember;
use app\models\chat\ChatMessageFavorites;
use app\models\chat\ChatMessagePinned;
use app\models\chat\ChatMessageReciever;
use app\models\chat\ChatRecieverHistory;
use app\models\chat\ChatRoom;
use app\models\User;
use Throwable;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;


class CHAT_ROLE
{
    const ADMIN = 1;        // администратор
    const MEMBER = 2;       // участник
    const WATCHER = 3;      // наблюдатель
}

class ChatController extends BaseController
{
    // методы чата
    
    
    // actionAddMemberToChat                - Добавление пользователя к чату
    // actionGetMessagesByRoom              - Получение сообщений по идентификатору комнаты чата
    // actionNewRoom                        - Создание группы (комнаты) чата
    // actionNewMessage                     - Добавление нового сообщения в БД
    public function actionIndex($post_json = null) {

        return $this->render('index');
    }

    /**
     * actionNewMessage - Добавление нового сообщения в БД и кэш
     *
     * Необходимые POST поля:
     *   text               - текст сообщения. В случае если есть вложение, то в нем хранится имя файла
     *   id                 - ключ сообщения
     *   sender_user_id   - идентификатор пользователя-отправителя сообщения
     *   chat_room_id       - идентификатор комнаты чата
     *   attachment_type    - тип вложения. Может быть пустым
     *   attachment         - При получении (либо BLOB, либо текст, либо пустое) Путь к вложению при возврате. Может быть пустым
     *   attachment_title   - Название вложения
     *
     * @param null $post_json - строка с параметрами метода в json формате
     *
     * @return array
     *   Items - идентификаторы записей из таблицы chat_message_reciever (связки сообщения и его получателя)
     * @example Воркер с идентификатором 123 отправляет сообщение в чат с идентификатором 4
     *   без вложений

     *
     */

     public static function actionNewMessage()
    {
        $status = 1;
        $errors = array();
        $warnings = array();
        $new_message_id = -1;
        $added_messages_ids = array();
        $post_json = $_POST;
        $files = $_FILES ?? null;
        
        
        
        try {
            $warnings[] = __FUNCTION__ . '. Начало метода, параметры: ' . print_r($post_json, true);
            /**=================================================================
             * Валидация входных данных
             * ===============================================================*/
            if ($post_json === null || $post_json === '') {
                throw new Exception(__FUNCTION__ . '. Данные с фронта не получены');
            }

            //$post = json_decode($post_json, true);
            $post = $post_json;
            if ($post === null) {
                throw new Exception(__FUNCTION__ . '. Получен невалидный json: ' . $post_json);
            }

            // проверка на наличие текста сообщения, отправителя, ключа комнаты и типа сообщения как параметров
            $post_valid = isset($post['text'], $post['sender_user_id'], $post['chat_room_id'], $post['attachment_type']);
            if (!$post_valid) {
                throw new Exception(__FUNCTION__ . '. Не все входные параметры инициализированы');
            }

            // проверка на наличие заполенного отправителя
            if (empty($post['sender_user_id'])) {
                throw new Exception(__FUNCTION__ . '. Не передан идентификатор воркера отправителя');
            }

            // проверка на наличие заполенного ключа комнаты
            if (empty($post['chat_room_id'])) {
                throw new Exception(__FUNCTION__ . '. Не передан идентификатор комнаты чата');
            }

            // проверка на наличие заполенного текстового сообщения и типа сообщения
            if ($post['text'] === '' && empty($post['attachment_type'])) {
                throw new Exception(__FUNCTION__ . '. Нельзя отправить пустое сообщение');
            }

            $attachment = "";                                                                                           // вложение, при типе вложения 1/2/3/4(изображение, видео, аудио, файл) - ожидается BLOB, 5(цитата) - ожидается текст
            $attachment_title = "greenaviUnknownFile";                                                                    // вложение, при типе вложения 1/2/3/4(изображение, видео, аудио, файл) - ожидается BLOB, 5(цитата) - ожидается текст
            if (isset($files['attachment'])) {
                $attachment = $files['attachment'];                                                                      // вложение с фронта BLOB
                $attachment_title = $post['attachment_title'];                                                          // название вложения
            }

            $text = $post['text'];                                                                                      // само текстовое сообщение
            $sender_user_id = $post['sender_user_id'];                                                              // отправитель текстового сообщения
            $chat_room_id = $post['chat_room_id'];                                                                      // ключ комнаты
            $chat_attachment_type_id = $post['attachment_type'];                                                        // тип вложения 1 - изображение, 2 - видео, 3 - аудио, 4 - файлы, 5 цитата, null или "" - без вложения

            /**
             * Проверка на наличие вложения и загрузка файла в систему при его наличие
             * если тип вложения 1 или 2, проверяем на наличие самого вложения, а затем сохраняем его на сервер, получаем путь
             * и записываем его в само вложение
             */
            if ($chat_attachment_type_id == 1 or $chat_attachment_type_id == 2 or
                $chat_attachment_type_id == 3 or $chat_attachment_type_id == 4) {
                if (!$attachment) {
                    throw new Exception(__FUNCTION__ . '. Отсутствует отправляемое вложение');
                }
                
                $attachment = Assistant::UploadFileChat($attachment, $attachment_title, 'chat_message');
            }


            /**=================================================================
             * Проверка роли отправителя сообщения
             * ===============================================================*/
            $chat_member = ChatMember::find()
                ->with('chatRole')
                //->joinWith('user.employee')
                ->where([
                    'chat_room_id' => $chat_room_id,
                    'user_id' => $sender_user_id
                ])
                ->limit(1)
                ->one();
            if ($chat_member === null) {
                throw new Exception(__FUNCTION__ . '. В чате нет такого участника');
            }
            if ($chat_member->chatRole->title === 'Наблюдатель') {
                throw new Exception(__FUNCTION__ . '. Наблюдатели не могут общаться в этой комнате');
            }
            $warnings[] = __FUNCTION__ . '. Проверена роль отправителя';

            $current_date = Assistant::GetDateTimeNow();
            $chat_database = new ChatDatabaseModel();
            //$chat_cache = new ChatCacheModel();
            /**=================================================================
             * Добавление нового сообщения в БД
             * ===============================================================*/
            try {
                $user_full_name = $chat_member->user->fio;
                $new_message_id = $chat_database->newMessage($text, $sender_user_id, $chat_room_id, $current_date, $chat_attachment_type_id, $attachment);
            } catch (Throwable $exception) {
                $errors[] = __FUNCTION__ . '. Ошибка при добавлении сообщения в БД';
                throw $exception;
            }
            $warnings[] = __FUNCTION__ . '. Сообщение добавлено в БД';

            /**=================================================================
             * Добавление нового сообщения в кэш
             * ===============================================================*/
            /*try {
                $result  = $chat_cache->newMessage($text, $sender_user_id, $chat_room_id, $current_date, $new_message_id, $chat_attachment_type_id, $attachment);
            } catch (\Throwable $exception) {
                $errors[] = __FUNCTION__ . '. Ошибка при добавлении сообщения в кэш';
                throw $exception;
            }
            $warnings[] = __FUNCTION__ . '. Сообщение добавлено в кэш';*/

            /**=================================================================
             * Добавление получателя сообщения и запись статуса в историю
             * ===============================================================*/
            // $recievers_ids = $chat_database->getChatActiveMembers($chat_room_id);
            // if ($recievers_ids !== false) {
            //     $recievers_ids = ArrayHelper::getColumn($recievers_ids, 'user_id');
            // } else {
            //     throw new Exception(__FUNCTION__ . '. В группе нет участников');
            // }

            // foreach ($recievers_ids as $reciever_id) {
            //     if ($reciever_id != $sender_user_id) {
            //         try {
            //             $message_reciever_id = $chat_database->newMessageReciever($new_message_id, $reciever_id, 29/*StatusEnumController::MSG_SENDED*/, $chat_room_id);
            //             $added_messages_ids[] = $chat_database->newMessageStatus($message_reciever_id, 29/*StatusEnumController::MSG_SENDED*/, $current_date);
            //         } catch (Throwable $exception) {
            //             $errors[] = __FUNCTION__ . '. Ошибка добавления получателя сообщения или его статуса';
            //             throw $exception;
            //         }
            //     }
            // }
            // $warnings[] = __FUNCTION__ . '. Получатели сообщения и статусы сохранены';

            /**=================================================================
             * Отправка сообщения на вебсокет
             * ===============================================================*/
            
            //  try {
            //     $ws_msg = json_encode(array(
            //         'ClientType' => 'server',
            //         'ActionType' => 'publish',
            //         'ClientId' => 'server',
            //         'Subscribes' => [$chat_room_id],
            //         'type' => 'login',
            //         'room_id' => $chat_room_id,                                                                 // ключ комнаты
            //         'id' => $new_message_id,                                                                    // ключ сообщения
            //         'sender_user_id' => $sender_user_id,                                                    // ключ отправителя
            //         'user_full_name' => $user_full_name,                                                    // Фамилия И.О. пользователя
            //         'primary_message' => $text,                                                                 // текст сообщения
            //         'chat_attachment_type_id' => $chat_attachment_type_id,                                      // тип сообщения (изображение, видео, цитата)
            //         'attachment' => $attachment,                                                                // вложение
            //         'date_time' => $current_date                                                                // дата и время сообщения
            //         )

            //             , JSON_UNESCAPED_UNICODE);
                    
            //     //));
            //     WebsocketController::actionSendMsg('ws://127.0.0.1:7272', $ws_msg);

            // } catch (Throwable $exception) {
            //     $errors[] = __FUNCTION__ . '. Ошибка отправки сообщения на вебсокет сервер';
            //     throw $exception;
            // }


            $warnings[] = __FUNCTION__ . '. Конец метода';
        } catch (Throwable $exception) {
            $status = 0;
            $errors['Method parameters'] = $post_json;
            $errors[] = $exception->getMessage();
            $errors[] = $exception->getLine();
        }


        

        return var_dump(['Items' => $added_messages_ids, 'message_id' => $new_message_id, 'status' => $status, 'warnings' => $warnings, 'errors' => $errors]);
    }



    /**
     * actionNewRoom - Создание группы (комнаты) чата
     * Необходимые POST поля:
     *   title - название комнаты чата
     *   users_ids - идентификаторы участников
     * @param null $post_json - строка с параметрами метода в json формате
     *
     *
     */
    public static function actionNewRoom($post_json = null)
    {
        //$log = new LoggreenaviFront("actionNewRoom");
        $log = null;
        $result = null;
        $post_json = $_POST;
        Yii::$app->response->format = Response::FORMAT_JSON;

        //try {
            //$log->addLog("Начал выполнение метода");
            //$session = Yii::$app->session;
            /**=================================================================
             * Валидация входных данных
             * ===============================================================*/
            if ($post_json === null || $post_json === '') {
                throw new Exception('Данные с фронта не получены');
            }

            $post = $post_json;
            if ($post === null) {
                throw new Exception('Получен невалидный json: ' . $post_json);
            }

            $post_valid = isset($post['title'], $post['users_ids']);
            if (!$post_valid) {
                throw new Exception('Не все входные параметры инициализированы');
            }

            if (empty($post['title'])) {
                throw new Exception('Не передано название комнаты');
            }

            if (empty($post['users_ids'])) {
                throw new Exception('Не переданы идентификаторы участников');
            }

            $title = $post['title'];
            $user_ids = $post['users_ids'];
            $user_ids = explode(",", $user_ids);
            //return var_dump($user_ids);
            $current_date = Assistant::GetDateTimeNow();

            $chat_database = new ChatDatabaseModel();
            //$chat_cache = new ChatCacheModel();

            /**=================================================================
             * Создание комнаты в БД
             * ===============================================================*/
            $chat_id = $chat_database->newRoom($title, 2 /*групповой*/, $current_date);

            $result = (int)$chat_id;

            /**=================================================================
             * Создание комнаты в кэше
             * ===============================================================*/
//                $chat_cache->newRoom($title, 2 /*групповой*/, $current_date, $chat_id);
            print_r($result);

            /**=================================================================
             * Создание участников
             * ===============================================================*/
            // в группе получателей не должен быть администратор
            //$member_id = $chat_database->newMember($chat_id, $session['user_id'], $current_date, 1, 1 /*Администратор*/);
            //unset($user_ids[$session['user_id']]);
            foreach ($user_ids as $user) {
                //print_r($user);
                $member_id = $chat_database->newMember($chat_id, $user, $current_date, 1, 2 /*Участник*/);
                //$chat_cache->newMember($member_id, $chat_id, $user_id, $current_date, 1, 2 /*Участник*/);
            }

        // } catch (Throwable $ex) {
        //     //$log->addError($ex->getMessage(), $ex->getLine());
        //     print_r("Ошибка");
        // }

        //$log->addLog("Окончание выполнения метода");

        return array_merge(['Items' => $result]);
    }


    /**
     * actionGetMessagesByRoom - Получение сообщений по идентификатору комнаты чата
     * Необходимые POST поля:
     *   chat_room_id - идентификатор комнаты чата
     *   user_id - идентификатор пользователя-пользователя по которому будут
     *               искаться статусы сообщений.
     *
     * Если не указывать параметр даты, то берётся текущая дата и время
     *
     * @param null $post_json строка с параметрами метода в json формате
     *
     */
    public static function actionGetMessagesByRoom($post_json = null)
    {
        $status = 1;
        $errors = array();
        $warnings = array();
        $messages = array();
        $post_json = $_POST;
        Yii::$app->response->format = Response::FORMAT_JSON;
        //return print_r($post_json);
        
        try {
            /**=================================================================
             * Валидация входных данных
             * ===============================================================*/
            $warnings[] = __FUNCTION__ . '. Начало метода. Параметры: ' . print_r($post_json, true);
            if ($post_json === null || $post_json === '') {
                throw new Exception(__FUNCTION__ . '. Данные с фронта не получены');
            }

            $post = $post_json;
            if ($post === null) {
                throw new Exception(__FUNCTION__ . '. Получен невалидный json: ' . $post_json);
            }

            $post_valid = isset($post['chat_room_id']/*, $post['user_id']*/);
            if (!$post_valid) {
                throw new Exception(__FUNCTION__ . '. Не все входные параметры инициализированы'.print_r($post_json));
            }

            if (empty($post['chat_room_id'])) {
                throw new Exception(__FUNCTION__ . '. Не передан идентификатор чата');
            }

            /*if (empty($post['user_id'])) {
                throw new \Exception(__FUNCTION__ . '. Не передан идентификатор пользователя - пользователя чата');
            }*/

            $chat_room_id = $post['chat_room_id'];
            //$user_id = $post['user_id'];

            // TODO: нужна ли проверка?
            // Если убрать проверку, то при отсутствии чата возвращает пустой массив
            // в поле messages результирующего массива
            if (!ChatRoom::find()->where(['id' => $chat_room_id])->exists()) {
                throw new Exception(__FUNCTION__ . ". Нет чата с таким идентификатором $chat_room_id");
            }

            $chat_database = new ChatDatabaseModel();



            /**=================================================================
             * Получение сообщений из БД
             * ===============================================================*/
            //if ($messages === false) {
            try {
                //$messages = $chat_database->getMessagesByRoom($chat_room_id);
                $messages = $chat_database->getMessagesWithStatusesByRoomUser($chat_room_id/*, $user_id*/);
                $warnings[] = __FUNCTION__ . '. Сообщения получены из БД';
//                $warnings[] = $messages;
            } catch (Throwable $exception) {
                $errors[] = __FUNCTION__ . '. Ошибка получения сообщений из БД';
                throw $exception;
            }
            //}

            $warnings[] = __FUNCTION__ . '. Конец метода';
        } catch (Throwable $exception) {
            $status = 0;
            $errors['Method parameters'] = $post_json;
            $errors[] = $exception->getMessage();
            $errors[] = $exception->getLine();
        }

        
        return array('Items' => $messages, 'status' => $status, 'warnings' => $warnings, 'errors' => $errors);

    }


    /**
     * actionAddAdminToChat - Добавление пользователя к чату
     * Необходимые POST поля:
     *      user_id       - идентификатор пользователя
     *      chat_room_id    - идентификатор комнаты чата
     *      chat_role_id    - ключ роли чата пользователя
     * @param null $post_json
     * @return array
     *
     * @example
     */
    public static function actionAddAdminToChat($post_json = null)
    {
        $status = 1;
        $warnings = array();
        $errors = array();
        $chat_member_id = -1;
        $post_json = $_POST;
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $warnings[] = __FUNCTION__ . '. Начало метода. Параметры: ' . print_r($post_json, true);
            /**=================================================================
             * Валидация входных данных
             * ===============================================================*/
            if ($post_json === null || $post_json === '') {
                throw new Exception(__FUNCTION__ . '. Данные с фронта не получены');
            }

            $post = $post_json;
            if ($post === null) {
                throw new Exception(__FUNCTION__ . '. Получен невалидный json: ' . $post_json);
            }

            $post_valid = isset($post['user_id'], $post['chat_room_id'], $post['chat_role_id']);
            if (!$post_valid) {
                throw new Exception(__FUNCTION__ . '. Не все входные параметры инициализированы');
            }

            if (empty($post['user_id'])) {
                throw new Exception(__FUNCTION__ . '. Не передан идентификатор пользователя');
            }

            if (empty($post['chat_room_id'])) {
                throw new Exception(__FUNCTION__ . '. Не передан идентификатор чата');
            }

            if (empty($post['chat_role_id'])) {
                throw new Exception(__FUNCTION__ . '. Не передан идентификатор роли члена группового чата');
            }

            $user_id = $post['user_id'];
            $chat_room_id = $post['chat_room_id'];
            $chat_role_id = $post['chat_role_id'];

            /**=================================================================
             * Добавление воркера в чат в БД
             * ===============================================================*/
            $curr_date = Assistant::GetDateTimeNow();
            $chat_database = new ChatDatabaseModel();
            try {
                $chat_member_id = $chat_database->newMember($chat_room_id, $user_id, $curr_date, 1, $chat_role_id);
            } catch (Throwable $exception) {
                $errors[] = __FUNCTION__ . '. Ошибка добавления участника в чат в БД';
                throw $exception;
            }
            $warnings[] = __FUNCTION__ . '. ID нового участника в БД: ' . $chat_member_id;

            /**=================================================================
             * Смена типа чата на групповой, если нужно
             * ===============================================================*/
            // TODO: вынести в ChatDatabaseModel
            $chat = ChatRoom::findOne($chat_room_id);
            if ($chat) {
                if ($chat->chat_type_id == 1/*Индивидуальный*/) {
                    // Смена типа
                    $chat->chat_type_id = 2; // Групповой

                    if (!$chat->save()) {
                        throw new Exception(__FUNCTION__ . '. Ошибка изменения чата в БД при добавлении нового участника');
                    }
                    $warnings[] = __FUNCTION__ . '. Тип чата c ID ' . $chat->id . ' изменён на групповой';
                }
            }

            $warnings[] = __FUNCTION__ . '. Конец метода';
        } catch (Throwable $exception) {
            $status = 0;
            $errors['Method_parameters'] = $post_json;
            $errors[] = $exception->getMessage();
            $errors[] = $exception->getLine();
        }

        return array('Items' => $chat_member_id, 'status' => $status, 'warnings' => $warnings, 'errors' => $errors);
    }




    



}
