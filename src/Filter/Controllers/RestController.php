<?php

namespace Oxhexspeak\OauthFilter\Controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\Response;
use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use Oxhexspeak\OauthFilter\Models\Client;
use Oxhexspeak\OauthFilter\Filters\HttpBearerOauth;

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
            'authenticator' => [
                'class' => HttpBearerOauth::class,
                'allow' => [
                    'any' => [],
                    'client' => [],
                    'customer' => [],
                    'application' => [],
                ],
            ],
            'corsFilter' => [
                'class' => Cors::className(),
                'cors'  => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [
                        'X-Pagination-Current-Page',
                        'X-Pagination-Page-Count',
                        'X-Pagination-Per-Page',
                        'X-Pagination-Total-Count'
                    ],
                ]
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ]
        ];
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

    /**
     * Shortcut to access user identity from the controller instance.
     *
     * @return null|IdentityInterface|Client
     */
    protected function getUserIdentity()
    {
        return Yii::$app->getUser()->getIdentity();
    }

    /**
     * Checks validity of access for requested resource id.
     *
     * @param int|string $id
     * @return boolean
     * @throws ForbiddenHttpException
     */
    protected function isResourceOwner($resourceId)
    {
        if ($this->getUserIdentity()->isClient() || $this->getUserIdentity()->getId() == $resourceId) {
            return true;
        }
        throw new ForbiddenHttpException('You have no rights to access requested resource');
    }
}
