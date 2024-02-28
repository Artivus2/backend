<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\P2pAds;
use app\models\B2bAds;
use app\models\P2pHistory;
use app\models\B2bHistory;
use app\models\Wallet;
use app\models\History;

class P2pController extends BaseController
{

    public function actionIndex()
    {
        set_time_limit(0);

        if($this->checkProcess($this->route)) {
            return ExitCode::OK;
        }

        // отмена заявок на вывод средств TO DO перенести в wallet
        //status 0 в обработке, 1 - выполнено, 2 - отменено strtotime("+3 day", $p2p_h->start_date)
        $history = History::find()->where(["<=", "date", strtotime("-3 day",time())])->andWhere(["status" => 0])->andwhere(['wallet_direct_id' => 10, 'type'=> 0])->all();
        foreach ($history as $item) { 
            $item->status = 2;
            $wallet = Wallet::findOne(['user_id' => $item->user_id, 'chart_id' => $item->start_chart_id,'type' => 0]);
            
            $wallet->balance += $item->start_price;
            $wallet->blocked = 0;
            $wallet->save();
            $item->save();

        }


        // автоотмена статус 1
        $history_seven = P2pHistory::find()->JoinWith(["ads"])->where(["<=", "end_date", time()])->andWhere(["p2p_history.status" => 1])->all();
        $history_seven_b2b = B2bHistory::find()->JoinWith(["ads"])->where(["<=", "end_date", time()])->andWhere(["b2b_history.status" => 1])->all();
        foreach ($history_seven as $item) {
            if($item->ads) {
                if($item->ads->type == 1) {
                    $item->ads->amount += $item->price;
                }
                if($item->ads->type == 2) {
                    if($item->ads->status == 6) {
                        $wallet_seller = Wallet::find()->where(["user_id" => $item->creator_id, "chart_id" => $item->ads->chart_id])->one();
                        $wallet_seller->balance += $item->price;
                        $wallet_seller->save();
                    }
                    if($item->ads->status == -1) {
                    $item->ads->amount += $item->price;
                    }
                }
                //$item->ads->status = -1;
                $item->ads->save();                
                $item->status = 7;
                $item->save();
            }
        }
        foreach ($history_seven_b2b as $item) {
            if($item->ads) {
                if($item->ads->type == 1) {
                    $item->ads->amount += $item->price;
                }
                if($item->ads->type == 2) {
                    if($item->ads->status == 6) {
                        $wallet_seller = Wallet::find()->where(["user_id" => $item->creator_id, "chart_id" => $item->ads->chart_id])->one();
                        $wallet_seller->balance += $item->price;
                        $wallet_seller->save();
                    }
                    if($item->ads->status == -1) {
                    $item->ads->amount += $item->price;
                    }
                }
                //$item->ads->status = -1;
                $item->ads->save();                
                $item->status = 7;
                $item->save();
            }
        }
	
        // автоотмена статус 2 -> 5 потои в админке доделать

        $history_appeal = P2pHistory::find()->JoinWith(["ads"])->where(["<=", "end_date", time()])->andWhere(["p2p_history.status" => 2])->all();

        foreach ($history_appeal as $item) {
            if($item->ads) {
                if($item->ads->type == 2) {
                $item->ads->status = 5;
                    //$item->ads->amount += $item->price;
                    $item->ads->save();
                }

                $item->status = 5;
                $item->save();
            }
        }

        $history_appeal_b2b = B2bHistory::find()->JoinWith(["ads"])->where(["<=", "end_date", time()])->andWhere(["b2b_history.status" => 2])->all();

        foreach ($history_appeal as $item) {
            if($item->ads) {
                if($item->ads->type == 2) {
                $item->ads->status = 5;
                    //$item->ads->amount += $item->price;
                    $item->ads->save();
                }

                $item->status = 5;
                $item->save();
            }
        }

        //закрытие ордера в случае amount < 500 р
 /*       $history_six = P2pAds::find()->Where(["status" => -1])->all();
        foreach ($history_six as $item) {
            if($item) {
                if ($item->currency_id == 1) {

                    if($item->amount * $item->course > 0 && $item->amount * $item->course < 500) {
                        
                            if($item->type == 2) {
                                $wallet_seller = Wallet::find()->where(["user_id" => $item->user_id, "chart_id" => $item->chart_id])->one();
                                $wallet_seller->balance += $item->amount;
                                $wallet_seller->save();
                            }
                            $item->status = 6;
                            $item->amount = 0;
                            $item->save();
                            
                        
                    }
                }
            }
        }
*/
       
    }
}
