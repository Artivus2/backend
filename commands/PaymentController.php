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

class PaymentController extends BaseController
{

    public function actionIndex()
    {
        set_time_limit(0);

        if($this->checkProcess($this->route)) {
            return ExitCode::OK;
        }

            // 0: Pending
            // 1: Paid
            // 2: Underpaid
            // 3: Over Paid
            // 4: Expired
            // 5: Cancelled

 
        // $params = [
        //     'coin'=>'TCN', //coin for which you want to use this object.
        //     'api_key'=> Yii::$app->params['API_KEY_COINREMITTER'], //api key from coinremitter wallet
        //     'password'=>Yii::$app->params['API_KEY_PASSWORD'] //password for selected wallet
        // ];
        // $obj = new CoinRemitter($params);
        $api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427';
        
        // $input_offers = History::find()->where(['wallet_direct_id' => 12, 'status' => -4])->all();
        // foreach ($input_offers as $item) {

            
            

        //     $item->status = -1;
        //     $wallet = Wallet::findOne(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "type" => 0]);
        //         if(!$wallet) {
        //             $wallet = new Wallet(["user_id" => $item->user_id, "chart_id" => $item->start_chart_id, "balance" => 0, "type" => 0,  'balance' => $paid_amount]);
        //         }
        //         $wallet->balance += $paid_amount;
        //         $wallet->save();
        //     $item->save();

        // }
            
            


        
        

            



        
        
       
    }
}
