<?php

namespace Oxhexspeak\OauthFilter\Models;

use yii\base\Model;
use yii\web\IdentityInterface;
use Oxhexspeak\OauthFilter\Services\Oauth2Service;

/**
 * Class Client.
 *
 * @package Oxhexspeak\OauthFilter\Models
 */
class Client extends Model implements IdentityInterface
{
    /**
     * @var string $user_id
     */
    public $user_id;

    /**
     * @var string $client_id
     */
    public $client_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'client_id'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->user_id ? $this->user_id : $this->client_id;
    }

    /**
     * Returns is_client sign.
     *
     * @return bool
     */
    public function isClient()
    {
        return (boolean) $this->client_id;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $tokenInfo = \Yii::createObject(
            [
                'class'   => Oauth2Service::class,
                'authUrl' => getenv('AUTH_URL')
            ]
        )->requestTokenInfo($token, \Yii::$app->cache);

        $identity = new static;
        $identity->load($tokenInfo, '');

        return $identity;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }
}
