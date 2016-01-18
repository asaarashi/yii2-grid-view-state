<?php

namespace thrieu\grid;

use Yii;
use yii\base\ActionFilter;
use yii\web\Request;

class ClearFilterStateBehavior extends ActionFilter {
    public $id;
    public $params;
    public $clearStateParam = 'clear-state';

    public function beforeAction($action) {
        if(($params = $this->params) === null) {
            $request = Yii::$app->getRequest();
            $params = $request instanceof Request ? $request->getQueryParams() : [];
        }

        if(isset($params[$this->clearStateParam]) && !in_array($params[$this->clearStateParam], ['0', ''], true)) {
            FilterStateTrait::clearFilterStateParams($this->id);
        }
    }
}