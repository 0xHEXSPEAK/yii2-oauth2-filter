<?php

namespace Oxhexspeak\OauthFilter\Services;

use yii\web\Request;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\helpers\Json;
use GuzzleHttp\Client;

/**
 * Class AuthorizationServiceTrait.
 *
 * @package Oxhexspeak\OauthFilter\Services
 */
trait AuthorizationServiceTrait
{

    /**
     * Defines auth server endpoint.
     *
     * @var string $authUrl
     */
    public $authUrl;

    /**
     * Defines http client.
     *
     * @var Client $httpClient
     */
    protected $httpClient;

    /**
     * Requests token info via service oauth.
     *
     * @param string $token
     * @return array
     * @throws ServerErrorHttpException
     */
    public function requestTokenInfo($token)
    {
        try {
            $response = $this->httpClient->post($this->composeUrl('/tokeninfo'), [
                'body' => [
                    'access_token' => $token,
                ]
            ]);
        } catch (\HttpException $e) {
            throw new ServerErrorHttpException("Service unavailable. {$e->getMessage()}");
        }

        $contents = $response->getBody()->getContents();

        return Json::decode($contents);
    }

    /**
     * Retrieves access token from authorization header.
     *
     * @param Request $request
     * @return string
     * @throws UnauthorizedHttpException
     */
    public function retrieveAccessToken(Request $request)
    {
        $accessToken = $request->getHeaders()->get('Authorization');
        if ( ! $accessToken) {
            throw new UnauthorizedHttpException('Missed authorization header.');
        }

        return $accessToken;
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
        $isAllowed     = false;

        $allowedScopes = explode(' ', $scopes);
        foreach ($allowedScopes as $scope) {
            if (\Yii::$app->controller->id === $scope) {
                $isAllowed = true;
            }
        }

        if ( ! $isAllowed) {
            throw new ForbiddenHttpException(
                'You are not allowed to perform this action. Check access token.'
            );
        }

        return $isAllowed;
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
