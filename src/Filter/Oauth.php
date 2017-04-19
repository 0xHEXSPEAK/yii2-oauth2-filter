<?php

namespace Oxhexspeak\OauthFilter;

use yii\base\ActionFilter;
use Oxhexspeak\OauthFilter\Services\AuthorizationServiceTrait;
use GuzzleHttp\Client;

/**
 * Class Oauth.
 *
 * @package Oxhexspeak\OauthFilter
 */
class Oauth extends ActionFilter
{
    use AuthorizationServiceTrait;

    /**
     * Oauth constructor.
     *
     * @param Client $httpClient
     * @param array $config
     */
    public function __construct(Client $httpClient, array $config = [])
    {
        $this->httpClient = $httpClient;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $accessToken = $this->requestTokenInfo(
            $this->retrieveAccessToken(\Yii::$app->getRequest())
        );

        $this->validate($accessToken);

        return parent::beforeAction($action);
    }
}
