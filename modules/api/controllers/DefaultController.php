<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     host="app.greenavi.com",
 *     schemes={"https"},
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *     @SWG\Info(
 *      version="1.0",
 *      title="Greenavicash App API",
 *     ),
 *     @SWG\SecurityScheme(
 *      securityDefinition="access_token",
*       securityDefinition="Bearer",
*       type="apiKey",
 *      in="header",
 *      name="Authorization"
 *     ),
 *     @SWG\Definition(
 *         definition="Result",
 *         required={"success", "message"},
 *         @SWG\Property(
 *             property="success",
 *             type="boolean",
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     ),
 *     @SWG\Definition(
 *         definition="Token",
 *         required={"access_token"},
 *         @SWG\Property(
 *             property="access_token",
 *             type="string"
 *         )
 *     )
 * )
 */
class DefaultController extends Controller
{
    public function actions()
    {
        return [
            'swagger' => [
                'class' => 'yii2mod\swagger\SwaggerUIRenderer',
                'restUrl' => Url::to(['swagger-json']),
            ],
            'swagger-json' => [
                'class' => 'yii2mod\swagger\OpenAPIRenderer',
                // Тhe list of directories that contains the swagger annotations.
                'scanDir' => [
                    Yii::getAlias('@app/modules/api/controllers'),
                    Yii::getAlias('@app/models'),
                ],
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->cache->flush();
        return [];
    }
}
