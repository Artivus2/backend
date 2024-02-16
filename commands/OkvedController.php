<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\B2bAds;
use app\models\Okveds;


class OkvedController extends BaseController
{

    public function actionIndex()
    {
        set_time_limit(0);

        if($this->checkProcess($this->route)) {
            return ExitCode::OK;
        }

        // загрузка оквед
        $token = "add0edb65bd1fb92dc50a3da6f904e29d57f4775";
        $dadata = new \Dadata\DadataClient($token, null);
        $result = $dadata->findById("okved2", "51.22.3");
        
        return $result;
       
    }
}
