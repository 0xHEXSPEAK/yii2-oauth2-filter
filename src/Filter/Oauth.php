<?php

namespace Oxhexspeak\OauthFilter;

use yii\base\ActionFilter;

/**
 * Class Oauth.
 *
 * @package Oxhexspeak\OauthFilter
 */
class Oauth extends ActionFilter
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        echo 'Im here.';
        return parent::beforeAction($action);
    }
}
