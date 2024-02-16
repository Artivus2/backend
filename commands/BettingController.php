<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Wallet;
use app\models\Betting;

class BettingController extends BaseController
{

    public function actionIndex()
    {
        set_time_limit(0);

        if($this->checkProcess($this->route)) {
            return ExitCode::OK;
        }

        while (true) {
            $betting = Betting::find()->JoinWith(["chart", "wallet"])->where(["<=", "end_date", time()])->andWhere(["betting.status" => 0])->all();

            foreach ($betting as $item) {
                if($item->chart && $item->wallet && $item->chart->symbol != "USDT") {
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $item->chart->symbol . "USDT",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
                    ));

                    $result = json_decode(curl_exec($curl));

                    curl_close($curl);

                    $item->end_price = $result->price;
                    if($item->start_price > $item->end_price && $item->type == 0) {
                        $item->status = 1;
                        $item->wallet->balance += $item->amount * 2;
                        $item->wallet->save();
                    } else if($item->start_price < $item->end_price && $item->type == 1) {
                        $item->status = 1;
                        $item->wallet->balance += $item->amount * 2;
                        $item->wallet->save();
                    } else if($item->start_price == $item->end_price) {
                        $item->status = 2;
                        $item->wallet->balance += $item->amount;
                        $item->wallet->save();
                    } else {
                        $item->status = 3;
                    }
                    $item->save();
                }
            }
        }
    }
}
