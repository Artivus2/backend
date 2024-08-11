<?php

namespace app\helpers;

class FcmPushNotify
{
    public static function push($title, $message, $fcm) {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = [
            'registration_ids' => $fcm,
            'data' => [
                'title' => $title,
                "message" => $message
            ]
        ];
        $fields = json_encode ($fields);

        $headers = array (
            'Authorization: key=' . "AAAAZSpheEw:APA91bGrthBdzVNPFpkV45L5aWLQPX3LoVhvPTOe25BF-pUs9SfXtNxUgiaWNcoAUXT8FTfrXO-kpEl7Tvq8avscfXHNC3kquzLzOXb-cezBuMnM5z8bg7p7K0EthRhtkRUp-lTOAXS5",
            'Content-Type: application/json'
        );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

        $result = curl_exec ($ch);

        if(!$result) {
            return ["success" => false, "message" => curl_error($ch)];
        }
        curl_close ( $ch );
    }
}
