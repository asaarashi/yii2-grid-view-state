<?php

namespace thrieu\grid;

use Yii;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

trait FilterStateTrait
{

    public $usePrevNext = false;
    public $pageIdList = [];

    public function init(): void
    {
        parent::init();
        /** @var GridView $this */
        $this->trigger(FilterStateInterface::EVENT_INITIALIZATION);

        if ($this->usePrevNext) {
            $this->beforeRow = static function (/** @noinspection PhpUnusedParameterInspection */ $model, $key, $index, $t) {
                $t->pageIdList[$index] = $key;
            };
        }
    }

    public function afterRun($result)
    {
        if ($this->usePrevNext) {
            /**
             * save page records in cache
             */
            $prevNextPage = new PrevNextPage();
            $prevNextPage->cacheDataProvider($this->dataProvider);
            $prevNextPage->cachePageIdList($this->pageIdList);
        }
        return parent::afterRun($result);
    }

    public static function getFilterStateParams($id = null, $route = null)
    {
        return FilterStateBehavior::getState($id, $route);
    }

    public static function clearFilterStateParams($id = null)
    {
        FilterStateBehavior::clearState($id);
    }

    public static function getMergedFilterStateParams($id = null, $params = null, $route = null)
    {
        if ($params === null) {
            /** @noinspection PhpUndefinedClassInspection */
            $params = Yii::$app->request->get();
        }
        return ArrayHelper::merge(static::getFilterStateParams($id, $route), $params);
    }
}