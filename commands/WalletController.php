<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Wallet;
use app\models\History;
use CoinRemitter\CoinRemitter;

class WalletController extends BaseController
{

    public function actionIndex()
    {
        set_time_limit(0);

        if($this->checkProcess($this->route)) {
            return ExitCode::OK;
        }

        $api_key='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiTVRrNE5UWT0iLCJ0eXBlIjoicHJvamVjdCIsInYiOiI2M2QzNDYyZjRhY2I0NjUzZGEyYTIwNGQ2YTlmZGJjYmZiZjIyY2NiZjIwYWVlOWI0MWIxODc2Njc4ZTA1Mjk5IiwiZXhwIjo4ODExMDU4MTQ0OH0.X0R_PfjNs2QeecNutTS2EKGwtf0r_LWnf8CKqQA7IUc';
        $shop_id='CghDrxpwxUVFXbq3';






        // $params = [
        //     'coin'=>'TCN', //coin for which you want to use this object.
        //     'api_key'=> Yii::$app->params['API_KEY_COINREMITTER'], //api key from coinremitter wallet
        //     'password'=>Yii::$app->params['API_KEY_PASSWORD'] //password for selected wallet
        // ];
        // $obj = new CoinRemitter($params);
        


        
        
        $input_offers = History::find()->where(['wallet_direct_id' => 12])->andWhere(['>=','status',0])->all();
        foreach ($input_offers as $item) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.cryptocloud.plus/v2/invoice/merchant/info");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                "uuids" => array($item->ipn_id)
            )));
            $headers = array(
                "Authorization: Token ".$api_key,
                "Content-Type: application/json"
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $result = [];
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($statusCode == 200) {
                    curl_close($ch);
                    $data = json_decode($response, true);
                    
                    foreach ($data as $item){
                    $result[] = $item;
                    }
                } else {
                    break;
                }
            }
 
            //При отправке запроса на создание счета всегда равен created. 
            //Так же есть статусы paid (оплачен), 
            //partial (оплачен частично), 
            //overpaid (переплачен) и 
            //canceled (отменен).
            
            //$coin = $result[1][0]
            //var_dump($invoice["data"]["status_code"]);
            // $coin = $invoice["data"]["coin"];
            // $base_currency = $invoice["data"]["base_currency"];
            // $paid_amount = $invoice["data"]["paid_amount"][$coin] ?? 0;
            // $total_amount = $invoice["data"]["total_amount"][$coin] ?? 0;
            $coin = $result[1][0]["currency"]["code"];
            $paid_amount = $result[1][0]["amount"] ?? 0;
            $total_amount = $result[1][0]["amount"] ?? 0;

            //$total_amount = $invoice["data"]["total_amount"][$coin] ?? 0;
            if ($result) {

                if ($result[1][0]["status"] == "canceled") {
                    $item->status = -4;
                    $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                        if(!$wallet) {
                            $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
                        }
                        //$wallet->balance += $paid_amount;
                        //$wallet->save();
                    $item->save();
                }

                if ($result[1][0]["status"] == "canceled") {
                    $item->status = -1;
                    $item->save();
                }
                
                if ($result[1][0]["status"] == "paid") {
                    
                    $item->status = -1;
                    $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                    if(!$wallet) {
                        $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => $paid_amount, "type" => 0]);
                    }
                    $wallet->balance += $paid_amount;
                    $wallet->save();
                    $item->save();
                }
                
                if ($result[1][0]["status"] == "partial") {
                    //недоплачен ждем просрочки
                    
                    $item->status = -1;
                    //потом 1
                    
                    $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                    if(!$wallet) {
                        $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
                    }
                    $wallet->balance += $paid_amount;
                    $wallet->save();
                    $item->save();
                    
                    
                }

                if ($result[1][0]["status"] == "overpaid") {
                    //добавляем но надо смотреть
                    $item->status = -1;
                    
                    $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                    if(!$wallet) {
                        $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
                    }
                    $wallet->balance += $paid_amount;
                    $item->end_price = $paid_amount;
                    $wallet->save();
                    $item->save();
                }
            }
        }

        //status 0 в обработке, 1 - выполнено, 2 - отменено strtotime("+3 day", $p2p_h->start_date) общий
        $history = History::find()->where(["<=", "date", strtotime("-1 day",time())])->andWhere(["status" => 0])->andwhere(['wallet_direct_id' => [10,13], 'type'=> [0,1]])->all();
        foreach ($history as $item) { 
            $item->status = 2;
            $wallet = Wallet::findOne(['user_id' => $item->user_id, 'chart_id' => $item->start_chart_id,'type' => $item->type == 0 ? 0 : 1]);
            
            $wallet->balance += $item->start_price;
            $wallet->blocked = 0;
            $wallet->save();
            $item->save();

        }       
        
        
       
    }
}
