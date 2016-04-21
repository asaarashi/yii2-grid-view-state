<?php

namespace thrieu\grid;

use Yii;
use yii\base\ActionFilter;
use yii\web\Request;

class ClearFilterStateBehavior extends ActionFilter {
    public $id;
    public $params;
    public $clearStateParam = 'clear-state';
    public $exitIfAjax = true;

    public function beforeAction($action) {
        if(($params = $this->params) === null) {
            $request = Yii::$app->getRequest();
            $params = $request instanceof Request ? $request->post() : [];
        }

        if(isset($params[$this->clearStateParam]) && $params[$this->clearStateParam] === '1') {
            FilterStateTrait::clearFilterStateParams($this->id);

            if(Yii::$app->request->getIsAjax() && $this->exitIfAjax) {
                Yii::$app->end();
            } else {
                Yii::$app->response->refresh();
            }
            return false;
        }
        return true;
    }
}