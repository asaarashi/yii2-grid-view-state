<?php

namespace thrieu\grid;

use Yii;
use yii\base\ActionFilter;
use yii\web\Request;

class ClearFilterStateBehavior extends ActionFilter {
    public $id;
    public $params;
    public $clearStateParam = 'clear-state';
    public $redirectToParam = 'redirect-to';
    public $exitIfAjax = true;

    public function beforeAction($action) {
        if(($params = $this->params) === null) {
            $request = Yii::$app->getRequest();
            $params = $request instanceof Request ? $request->post() : [];
        }

        if(isset($params[$this->clearStateParam]) && $params[$this->clearStateParam] != '0') {
            FilterStateTrait::clearFilterStateParams($this->id);

            if(Yii::$app->request->getIsAjax() && $this->exitIfAjax) {
                Yii::$app->end();
            } else {
                $redirectTo = isset($params[$this->redirectToParam]) ?
                    $params[$this->redirectToParam] : Yii::$app->controller->getRoute();
                Yii::$app->response->redirect($redirectTo);
            }
            return false;
        }
        return true;
    }
}