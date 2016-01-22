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
            $params = $request instanceof Request ? $request->getQueryParams() : [];
        }

        if(isset($params[$this->clearStateParam]) && $params[$this->clearStateParam] === '1') {
            FilterStateTrait::clearFilterStateParams($this->id);

            if(Yii::$app->request->isAjax && $this->exitIfAjax) {
                Yii::$app->end();
            } else {
                $params = Yii::$app->request->getQueryParams();
                unset($params[$this->clearStateParam]);
                Yii::$app->request->setQueryParams($params);
            }
        }
    }
}