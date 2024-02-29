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
        $params = [
            'coin'=>'TCN', //coin for which you want to use this object.
            'api_key'=> Yii::$app->params['API_KEY_COINREMITTER'], //api key from coinremitter wallet
            'password'=>Yii::$app->params['API_KEY_PASSWORD'] //password for selected wallet
        ];
        $obj = new CoinRemitter($params);
        

        $input_offers = History::find()->where(['wallet_direct_id' => 12])->andWhere(['>=','status',0])->all();
                foreach ($input_offers as $item) {
                    $param = [
                        'invoice_id'=>$item->ipn_id
                    ];
                    
                    $invoice = $obj->get_invoice($param);
                    //просрочен

                    //var_dump($invoice["data"]["status_code"]);
                    $coin = $invoice["data"]["coin"];
                    $base_currency = $invoice["data"]["base_currency"];
                    $paid_amount = $invoice["data"]["paid_amount"][$coin] ?? 0;
                    $total_amount = $invoice["data"]["total_amount"][$coin] ?? 0;

                   
                    if ((int)$invoice["data"]["status_code"] == 4) {


                        $item->status = -4;
                        $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                            if(!$wallet) {
                                $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
                            }
                            //$wallet->balance += $paid_amount;
                            //$wallet->save();
                        $item->save();
                    }

                    if ((int)$invoice["data"]["status_code"] == 5) {
                        $item->status = -1;
                        $item->save();
                    }
                    
                    if ((int)$invoice["data"]["status_code"] == 1) {
                        
                        $item->status = -1;
                        $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                        if(!$wallet) {
                            $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => $paid_amount, "type" => 0]);
                        }
                        $wallet->balance += $paid_amount;
                        $wallet->save();
                        $item->save();
                    }
                    
                    if ((int)$invoice["data"]["status_code"] == 2) {
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

                    if ((int)$invoice["data"]["status_code"] == 3) {
                        //добавляем но надо смотреть
                        $item->status = -1;
                        
                        $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
                        if(!$wallet) {
                            $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
                        }
                        $wallet->balance += $paid_amount;
                        $item->start_price = $paid_amount;
                        $wallet->save();
                        $item->save();
                    }
                }
                
        
       
    }
}
