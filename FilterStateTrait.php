<?php

namespace thrieu\grid;

use Yii;
use yii\helpers\ArrayHelper;

trait FilterStateTrait {
    public function init() {
        parent::init();
        /** @var \yii\grid\GridView $this */
        $this->trigger(FilterStateInterface::EVENT_INITIALIZATION);
    }

    public static function getFilterStateParams($id = null, $route = null) {
        return FilterStateBehavior::getState($id, $route);
    }

    public static function clearFilterStateParams($id = null) {
        FilterStateBehavior::clearState($id);
    }

    public static function getMergedFilterStateParams($id = null, $params = null, $route = null) {
        if($params === null) {
            $params = Yii::$app->request->get();
        }
        return ArrayHelper::merge(static::getFilterStateParams($id, $route), $params);
    }
}