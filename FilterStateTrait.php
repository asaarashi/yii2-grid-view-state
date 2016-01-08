<?php

namespace thrieu\grid;

use Yii;
use yii\helpers\ArrayHelper;

trait FilterStateTrait {
    public function init() {
        parent::init();
        /** @var \yii\grid\GridView $this */
        $this->trigger(FilterStateInterface::EVENT_INIT);
    }

    public static function getFilterStateParams($id = null) {
        $state = Yii::$app->session->get(FilterStateBehavior::buildKey($id !== null ? $id : ''));
        return $state !== null ? $state : [];
    }

    public static function getMergedFilterStateParams($id = null, $params = null) {
        if($params === null) {
            $params = Yii::$app->request->get();
        }
        return ArrayHelper::merge(static::getFilterStateParams($id), $params);
    }
}