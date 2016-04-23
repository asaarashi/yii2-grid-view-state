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
            } else if(isset($params[$this->redirectToParam])) {
                Yii::$app->response->redirect($params[$this->redirectToParam]);
            }
            return false;
        }
        return true;
    }
}