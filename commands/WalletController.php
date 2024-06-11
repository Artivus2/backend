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
use app\models\ChartChain;

class WalletController extends BaseController
{

    public function actionIndex()
    {
        set_time_limit(0);

        if($this->checkProcess($this->route)) {
            return ExitCode::OK;
        }

        // $api_key='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiTVRrNE5UWT0iLCJ0eXBlIjoicHJvamVjdCIsInYiOiI2M2QzNDYyZjRhY2I0NjUzZGEyYTIwNGQ2YTlmZGJjYmZiZjIyY2NiZjIwYWVlOWI0MWIxODc2Njc4ZTA1Mjk5IiwiZXhwIjo4ODExMDU4MTQ0OH0.X0R_PfjNs2QeecNutTS2EKGwtf0r_LWnf8CKqQA7IUc';
        // $shop_id='CghDrxpwxUVFXbq3';
        $api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427';


        $curl = curl_init();
        // https://api.nowpayments.io/v1/auth
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/auth',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
              "email": "test.greenavi@mail.ru",
              "password": "M354at790!" 
          }',
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json'
            ),
          ));
          
          $response = curl_exec($curl);
          $auth = json_decode($response, true);
          $token = $auth["token"];
          curl_close($curl);

          //проверка payment по ид
        $input_offers = History::find()->where(['wallet_direct_id' => 12])->andWhere(['status' => 0])->all();
        foreach ($input_offers as $item) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/?invoiceId='.(int)$item->ipn_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'x-api-key: '.$api_key
                ),
              ));
            
            $response = curl_exec($curl);
            curl_close($curl);
            $data = json_decode($response, true);
            if ($data["data"]) {
                $paid_amount = $data["data"][0]["actually_paid"] ?? 0;
                $total_amount = $item->start_price;
                $payment_id = $data["data"][0]["payment_id"];
                $status = $data["data"][0]["payment_status"];

                //$total_amount = $invoice["data"]["total_amount"][$coin] ?? 0;
                if ($status == "waiting") {
                    echo "waiting".$payment_id;
                }
            

                if ($status == "failed") {
                    $item->status = 5;
                    $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                        if(!$wallet) {
                            $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
                        }
                        //$wallet->balance += $paid_amount;
                        $wallet->save();
                    $item->save();
                }

                if ($status == "expired") {
                    $item->status = 4;
                    $item->save();
                }
                
                if ($status == "finished") {
                    
                    $item->status = -1;
                    $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                    if(!$wallet) {
                        $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                    }
                    $wallet->balance += $paid_amount;
                    $wallet->save();
                    $item->save();
                }
                
                if ($status == "partially_paid") {
                    //недоплачен ждем просрочки
                    
                    $item->status = 1;
                    //потом 1
                    
                    $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                    if(!$wallet) {
                        $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
                    }
                    $wallet->balance += $paid_amount;
                    $wallet->save();
                    $item->save();
                    
                    
                }

                if ($status == "overpaid") {
                    //добавляем но надо смотреть
                    $item->status = 3;
                    
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
        // $history = History::find()->where(["<=", "date", strtotime("-1 day",time())])->andWhere(["status" => 0])->andwhere(['wallet_direct_id' => [10,13], 'type'=> [0,1]])->all();
        // foreach ($history as $item) { 
        //     $item->status = 2;
        //     $wallet = Wallet::findOne(['user_id' => $item->user_id, 'chart_id' => $item->start_chart_id,'type' => $item->type == 0 ? 0 : 1]);
            
        //     $wallet->balance += $item->start_price;
        //     $wallet->blocked = 0;
        //     $wallet->save();
        //     $item->save();

        // }       


        $output_offers = History::find()->where(['wallet_direct_id' => 10])->andWhere(['>=','status',0])->all();
        $output_offers = [];
        if ($output_offers) {
            //to do validate adress

        //create payout


        //проверка payout
        foreach ($output_offers as $item) {
            $currency = ChartChain::findOne(['id' => $item->payment_id]);
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payout/'.$item->ipn_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
              'x-api-key: '.$api_key
            ),
          ));
          $response = curl_exec($curl);

            curl_close($curl);

            $data = json_decode($response, true);
                
            $status = $data["withdrawals"][0]["status"];
            $amount = $data["withdrawals"][0]["amount"];

            if ($status == "FINISHED") {
                
            $item->status = 1;
            $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
            if ($wallet->balance < $wallet->blocked) {
                $item->status = 2;
            } else {
                $wallet->blocked -= $amount;
            }
            
            $wallet->save();
            $item->save();
            }

      
        
            }
        

        }
        
        
       
    }
}
