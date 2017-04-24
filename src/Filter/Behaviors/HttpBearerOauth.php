<?php

namespace Oxhexspeak\OauthFilter\Behaviors;

use Oxhexspeak\OauthFilter\Models\Client;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;

/**
 * AuthMethod is a base class implementing the [[AuthInterface]] interface.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class HttpBearerOauth extends HttpBearerAuth
{
    /**
     * List allowed
     * @var array
     */
    public $allow;


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $response = $this->response ? : Yii::$app->getResponse();

        try {
            /**
             * @var Client $identity
             */
            $identity = $this->authenticate(
                $this->user ? : Yii::$app->getUser(),
                $this->request ? : Yii::$app->getRequest(),
                $response
            );
        } catch (UnauthorizedHttpException $e) {
            if ($this->isOptional($action)) {
                return true;
            }

            throw $e;
        }

        if (
            (
                $identity !== null &&
                $this->isAllowed($identity, $action)
            ) ||
            $this->isOptional($action)
        ) {
            return true;
        } else {
            $this->challenge($response);
            $this->handleFailure($response);
            return false;
        }
    }

    /**
     * @param Client $identity
     * @param $action
     * @return bool
     */
    protected function isAllowed(Client $identity, $action)
    {
        if ($identity->isClient()) {
            return $this->searchMatchId(array_merge(
                $this->allow['client'],
                $this->allow['any']
            ), $action);
        }
        return $this->searchMatchId(array_merge(
            $this->allow['customer'],
            $this->allow['any']
        ), $action);
    }

    /**
     * @inheritdoc
     */
    protected function isOptional($action)
    {
        return $this->searchMatchId($this->optional, $action);
    }

    /**
     * @param $data
     * @param $action
     * @return bool
     */
    protected function searchMatchId($data, $action)
    {
        $id = $this->getActionId($action);
        foreach ($data as $pattern) {
            if (fnmatch($pattern, $id)) {
                return true;
            }
        }
        return false;
    }
}
