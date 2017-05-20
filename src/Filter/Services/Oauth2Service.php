<?php

namespace Oxhexspeak\OauthFilter\Services;

use yii\base\Configurable;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\caching\Cache;
use yii\helpers\Json;
use GuzzleHttp\Client;

/**
 * Class Oauth2Service.
 *
 * @package Oxhexspeak\OauthFilter\Services
 */
class Oauth2Service implements Configurable
{
    /**
     * @var Client $httpClient
     */
    protected $httpClient;

    /**
     * @var string $authUrl
     */
    protected $authUrl;

    public function __construct(Client $httpClient, array $config = [])
    {
        $this->httpClient = $httpClient;
        $this->authUrl    = $config['authUrl'];
    }

    /**
     * Requests token info via service oauth.
     *
     * @param string $token
     * @param Cache $cache
     * @return array
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     */
    public function requestTokenInfo($token, Cache $cache)
    {
        $cacheKey = "{$token}info";

        if (($response = $cache->get($cacheKey))) {
            return $response;
        }

        try {
            $response = $this->httpClient->get($this->composeUrl('/tokeninfo'), [
                'headers' => [
                    'Authorization' => "Bearer $token"
                ],
            ]);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException("Auth service unavailable.");
        }

        $contents  = $response->getBody()->getContents();
        $tokenInfo = Json::decode($contents);

        if ($this->validate($tokenInfo)) {
            $cache->set($cacheKey, $tokenInfo, $tokenInfo['expires_in'] - time());
            return $tokenInfo;
        }

        throw new UnauthorizedHttpException("Token is not valid.");
    }

    /**
     * Validates an access token.
     *
     * @param string $accessToken
     * @return bool
     */
    public function validate($accessToken)
    {
        $this->checkTokenExpiration($accessToken['expires_in']);
        $this->checkControllerScopeAllowance($accessToken['scope']);

        return true;
    }

    /**
     * Checks whether an access token is expired.
     *
     * @param string $timestamp
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function checkTokenExpiration($timestamp)
    {
        if (time() > (int) $timestamp) {
            throw new ForbiddenHttpException(
                'You are unable to perform this action. Access token was expired.'
            );
        }

        return true;
    }

    /**
     * Checks controller's scope allowance.
     *
     * @param string $scopes
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function checkControllerScopeAllowance($scopes)
    {
        $allowedScopes = explode(' ', $scopes);
        foreach ($allowedScopes as $scope) {
            if (Inflector::pluralize(\Yii::$app->controller->id) === $scope) {
                return true;
            }
        }

        throw new ForbiddenHttpException(
            'You are not allowed to perform this action. Check an access token.'
        );
    }

    /**
     * Concatenates server url with path.
     *
     * @param string $path
     * @return string
     */
    protected function composeUrl($path = '')
    {
        return $this->authUrl . $path;
    }
}
