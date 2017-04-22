<?php

namespace Oxhexspeak\OauthFilter\Models;

use yii\base\Model;
use yii\web\IdentityInterface;
use Oxhexspeak\OauthFilter\Services\Oauth2Service;

class Client extends Model implements IdentityInterface
{
    public $user_id;

    public $client_id;

    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'client_id'
                ],
                'safe'
            ]
        ];
    }

    public function getId()
    {
        return $this->user_id ? $this->user_id : $this->client_id;
    }

    public function isClient()
    {
        return (boolean) $this->client_id;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $tokenInfo = \Yii::createObject([
            'class' => Oauth2Service::class,
            'authUrl' => getenv('AUTH_URL')
        ])->requestTokenInfo($token);

        $indentity = new static;
        $indentity->load($tokenInfo, '');
        return $indentity;
    }

    public static function findIdentity($id)
    {
        return false;
    }

    public function getAuthKey()
    {
        return false;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }
}
