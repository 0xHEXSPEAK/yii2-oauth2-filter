<?php

namespace Oxhexspeak\OauthFilter\Controllers;

use Yii;
use yii\web\Response;
use yii\rest\ActiveController;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use Oxhexspeak\OauthFilter\Oauth;

/**
 * Class RestController.
 *
 * @package Oxhexspeak\OauthFilter
 */
class RestController extends ActiveController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            // Add Oauth filter.
            'Oauth' => [
                'class' => Oauth::class,
                'authUrl' => getenv('AUTH_URL')
            ],
            // Add content negotiator.
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
            // Add verb filter.
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::$app->response->headers->add('Access-Control-Allow-Origin', '*');
        Yii::$app->response->headers->add('Access-Control-Allow-Headers', 'Accept, Authorization');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function runAction($id, $params = [])
    {
        try {
            $result = parent::runAction($id, $params);
        } catch (\yii\web\HttpException $e) {
            if ($e->getCode() > 0) {
                throw $e;
            }
            $excClass  = get_class($e);
            $exception = ($excClass == 'yii\web\HttpException')
                ? new $excClass($e->statusCode, $e->getMessage(), $e->statusCode)
                : new $excClass($e->getMessage(), $e->statusCode)
                ;

            throw $exception;
        }

        return $result;
    }
}
