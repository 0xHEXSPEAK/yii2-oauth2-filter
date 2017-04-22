<?php

namespace Oxhexspeak\OauthFilter\Models;

use yii\base\Model;
use yii\web\IdentityInterface;
use Oxhexspeak\OauthFilter\Services\Oauth2Service;

class Client extends Model implements IdentityInterface
{
    public $expires_in;

    public $user_id;

    public $owner_id;

    public function rules()
    {
        return [
            [['expires_in'], 'safe']
        ];
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

    public function getId()
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
