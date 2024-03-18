<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Currency;
use app\models\Chart;
use app\models\ChartFavourite;
use app\models\User;
use app\models\Wallet;
use app\models\WalletInv;
use app\models\WalletTrade;
use app\models\ChartChain;
use app\models\WalletAddress;

class ChartController extends BaseController
{
    /**
     * @SWG\Post(
     *    path = "/chart/favorite",
     *    tags = {"Chart"},
     *    summary = "Добавить в избранные криптовалюту",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Криптовалюта успешно добавлено в избранное",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionFavorite()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $chart_id = Yii::$app->request->post("chart_id");

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $chart = Chart::findOne($chart_id);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $favourite = ChartFavourite::findOne(["user_id" => $this->user->id, "chart_id" => $chart->id]);
        if($favourite) {
            $favourite->delete();
            return ["success" => true, "message" => "Удален из избранного"];
        } else {
            $favourite = new ChartFavourite(["user_id" => $this->user->id, "chart_id" => $chart->id]);
            $favourite->save();
            return ["success" => true, "message" => "Добавлен в избранные"];
        }
    }
    
    /**
     * @SWG\Get(
     *    path = "/chart/list",
     *    tags = {"Chart"},
     *    summary = "Список криптовалют",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p",
     *      in="path",
     *      type="integer",
     *      description="Используется в p2p (флаг 1 выведет для p2p)",
     *      @SWG\Schema(type="integer")
     *     ), 
     *    @SWG\Parameter(
     *      name="b2b",
     *      in="path",
     *      type="integer",
     *      description="Используется в b2b (флаг 1 выведет для b2b)",
     *      @SWG\Schema(type="integer")
     *     ), 
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список криптовалют",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Chart")
     *      ),
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $requestfromp2p = Yii::$app->request->get('p2p');
        
        $request=["cryptomus" => 1];
        if($requestfromp2p) {
            
            $request=["p2p" => 1];
            }


        $requestfromb2b = Yii::$app->request->get('b2b');
        
        if($requestfromb2b) {
            
            $request=["b2b" => 1];

            
            }

        $charts = [];
        $symbols = [];
        $chart_query = Chart::find()->joinWith(["favourite" => function ($query)  {
            $query->onCondition(['chart_favourite.user_id' => $this->user->id]);
        }])->where(["active" => 1])->andWhere(["cryptomus" => 1])->andwhere($request)->all();
        
        if (!$chart_query) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Список валют не найден"];
        }
        
        foreach ($chart_query as $chart) {
            if($chart->symbol == "RUB") {
                continue;
            }
            if($chart->symbol == "TCN") {
                continue;
            }
            
            if($chart->symbol != "USDT") {
                  
                $symbol = $chart->symbol . "USDT";
                $symbols[] = $symbol;
                $charts[$symbol] = $chart;
                }   
            
            
            }
        
        //return ["success" => false, "message" => "Список валют не найден", $symbols];
        $data = [];

        if ($requestfromb2b) {
            $data[] = [
                "id" => 2024,
                "name" => 'RUB',
                "symbol" => 'RUB',
                "price" => 1,
                "lowPrice" => 1,
                "highPrice" => 1,
                "percent" => 1,
                "icon" => Url::to(["/images/icons/RUB.png"], "https"),
                "chart_image" => Url::to(["/charts/RUB.png"], "https"),
            ];
        }

        $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($res);

        foreach ($result as $item) {
            $chart = $charts[$item->symbol];

            if($item->symbol == "USDCUSDT") {
                $_chart = Chart::find()->joinWith(["favourite" => function ($query)  {
                    $query->onCondition(['chart_favourite.user_id' => $this->user->id]);
                }])->where(["active" => 1, "symbol" => "USDT"])->one();

                if($_chart) {
                    $data[] = [
                        "id" => $_chart->id,
                        "name" => $_chart->name,
                        "symbol" => $_chart->symbol,
                        "price" => $item->lastPrice,
                        "lowPrice" => $item->lowPrice,
                        "highPrice" => $item->highPrice,
                        "percent" => $item->priceChangePercent,
                        "favorite" => isset($_chart->favourite),
                        "icon" => Url::to(["/images/icons/" . $_chart->symbol . ".png"], "https"),
                        "chart_image" => Url::to(["/charts/" . $_chart->symbol . ".png"], "https"),
                    ];
                }
            }

            $data[] = [
                "id" => $chart->id,
                "name" => $chart->name,
                "symbol" => $chart->symbol,
                "price" => $item->lastPrice,
                "lowPrice" => $item->lowPrice,
                "highPrice" => $item->highPrice,
                "percent" => $item->priceChangePercent,
                "favorite" => isset($chart->favourite),
                "icon" => Url::to(["/images/icons/" . $chart->symbol . ".png"], "https"),
                "chart_image" => Url::to(["/charts/" . $chart->symbol . ".png"], "https"),
            ];
        }

        $data[] = [
            "id" => 2707,
            "name" => 'TCN',
            "symbol" => 'TCN',
            "price" => 1,
            "lowPrice" => 1,
            "highPrice" => 1,
            "percent" => 1,
            "icon" => Url::to(["/images/icons/XMR.png"], "https"),
            "chart_image" => Url::to(["/charts/XMR.png"], "https"),
        ];

        

        return $data;
    }

    /**
     * @SWG\Get(
     *    path = "/chart/сurrency",
     *    tags = {"Chart"},
     *    summary = "Список валют",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p",
     *      in="path",
     *      type="integer",
     *      description="Используется в p2p (флаг 1 выведет для p2p)",
     *      @SWG\Schema(type="integer")
     *     ), 
     *    @SWG\Parameter(
     *      name="b2b",
     *      in="path",
     *      type="integer",
     *      description="Используется в b2b (флаг 1 выведет для b2b)",
     *      @SWG\Schema(type="integer")
     *     ), 
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список валют",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Currency")
     *      ),
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionCurrency()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        $requestfromp2p = Yii::$app->request->get('p2p');
        
        $request=["active" => 1];
        if($requestfromp2p) {
            
            $request=["p2p" => 1];
            }


        $requestfromb2b = Yii::$app->request->get('b2b');
        
        if($requestfromb2b) {
            
            $request=["b2b" => 1];
            
            }
        $data = [];
        $currency_query = Currency::find()->where(["active" => 1])->andwhere($request)->all();

        foreach ($currency_query as $currency_item) {
            $data[] = [
                "id" => $currency_item->id,
                "name" => $currency_item->name,
                "symbol" => $currency_item->symbol,
                "icon" => Url::to([$currency_item->getImage()->getUrl()], "https")
            ];
        }

        return $data;
    }

    /**
     * @SWG\Get(
     *    path = "/chart/list-item",
     *    tags = {"Chart"},
     *    summary = "Список криптовалют сокращенный",
     *    security={{"access_token":{}}},
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список криптовалют сокращенный",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Chart")
     *      ),
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionListItem()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $chart_query = Chart::find()->where(["active" => 1])->andWhere(["cryptomus" => 1])->all();

        $data = [];

        foreach ($chart_query as $chart) {
            $data[] = [
                "id" => $chart->id,
                "name" => $chart->name,
                "symbol" => $chart->symbol,
                "icon" => Url::to(["/images/icons/" . $chart->symbol . ".png"], "https"),
            ];

        }

        return $data;
    }

    /**
     * @SWG\Get(
     *    path = "/chart/price",
     *    tags = {"Chart"},
     *    summary = "Курс криптовалют",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="path",
     *      type="integer",
     *      description="ид криптовалюты",
     *      @SWG\Schema(type="integer")
     *     ), 
     *    @SWG\Parameter(
     *      name="currency_id",
     *      in="path",
     *      type="integer",
     *      description="ид валюты",
     *      @SWG\Schema(type="integer")
     *     ), 
     *	  @SWG\Response(
     *      response = 200,
     *      description = "price",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionPrice()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $chart_id = Yii::$app->request->get("chart_id");
        $currency_id = Yii::$app->request->get("currency_id", 1);

        $currency = Currency::findOne($currency_id);
        $chart = Chart::findOne($chart_id);
        if(!$currency) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Валюта не указана"];
            }
        if(!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Криптовалюта не указана"];
        }
        
        
        
        return ["price" => $this->price($chart->symbol, $currency->symbol)];
    }

    protected function price($chart1, $chart2){
        //$data = ["price" => 0];


        $curl = curl_init();
    
        if ($chart1 == "TCN") {
            $chart1 = "USDT";
        

        }
        curl_setopt_array($curl, array(
    
            CURLOPT_URL => "https://api.coinbase.com/v2/prices/".$chart1."-".$chart2."/spot",
            
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));
        curl_close($curl);

        if ((float)$result->data->amount >= 1) {
            $result = number_format($result->data->amount, 2,'.','');
        }
        else {
            $result = number_format($$result->data->amount, 10,'.','');
            $result = rtrim($result, '0');
        }
        
        return $result;
    }

    /**
     * @SWG\Post(
     *    path = "/chart/exchange-price",
     *    tags = {"Chart"},
     *    summary = "Получить курс обмена",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="from_chart_id",
     *      in="body",
     *      description="ID валюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="to_chart_id",
     *      in="body",
     *      description="ID валюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Получить курс",
     *      @SWG\Definition(
     *         required={"price"},
     *         @SWG\Property(
     *             property="price",
     *             type="number"
     *         )
     *      )
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionExchangePrice()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $from_chart_id = Yii::$app->request->post("from_chart_id");
        $to_chart_id = Yii::$app->request->post("to_chart_id");

        $from_chart = Chart::findOne($from_chart_id);
        if(!$from_chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }
        $to_chart = Chart::findOne($to_chart_id);
        if(!$to_chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $price = 0;
        $balance = 0;

        $wallet = Wallet::findOne(["chart_id" => $from_chart->id, "type" => 0]);
        if($wallet) {
            $balance = $wallet->balance;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $from_chart->symbol . $to_chart->symbol,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        if(empty($result->price)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $to_chart->symbol . $from_chart->symbol,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
            ));

            $result = json_decode(curl_exec($curl));
            curl_close($curl);

            if(empty($result->price)) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Котировка не найдена"];
            }

            $price = 1 / $result->price;
        } else {
            $price = $result->price;
        }

        return [
            "balance" => (float) $balance,
            "price" => (float)$price
        ];
    }

    /**
     * @SWG\Post(
     *    path = "/chart/history",
     *    tags = {"Chart"},
     *    summary = "Свечи криптовалюты",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="interval",
     *      in="body",
     *      description="Интервал свечи",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Свечи криптавалюты",
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionHistory()
    {
        
        Yii::$app->response->headers->set('Access-Control-Allow-Origin', '*');

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $chart_id = Yii::$app->request->post("chart_id", Yii::$app->request->get("chart_id"));
        $interval = Yii::$app->request->post("interval", Yii::$app->request->get("interval"));

        $data = [];

        $chart = Chart::findOne($chart_id);
        if (!$chart) return $data;

        $symbol = $chart->symbol == "USDT" ? "USDCUSDT" : ($chart->symbol . "USDT");
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/klines?symbol=" . $symbol . "&interval=" . $interval,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        foreach ($result as $item) {
            $data[] = [
                "t" => (int)$item[0] / 1000,
                "o" => (float)$item[1],
                "h" => (float)$item[2],
                "l" => (float)$item[3],
                "c" => (float)$item[4],
            ];
        }


        return $data;
    }


    /**
     * @SWG\Get(
     *    path = "/chart/chain-list",
     *    tags = {"Chart"},
     *    summary = "Список сетей криптавалюты",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="path",
     *      description="ID криптовалюты",
	 *   type="integer",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список сетей критавалюты",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Chain")
     *      ),
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionChainList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $chart = Chart::findOne(Yii::$app->request->get("chart_id"));
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $data = [];
        $chain_query = ChartChain::find()
        ->where(["cryptomus" => 1])
        ->andwhere(["chart_id" => $chart])
        ->all();

        foreach ($chain_query as $chain) {
            $data[] = [
                "id" => $chain->id,
                "name" => $chain->symbol
            ];
        }

        return $data;
    }


}
