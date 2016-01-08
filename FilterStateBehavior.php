<?php

namespace thrieu\grid;

use Yii;
use yii\base\Behavior;
use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class FilterStateBehavior extends Behavior {
    const KEY_PREFIX = 'FilterStateBehavior';
    public $id;

    /** @var \yii\grid\GridView $_gridView */
    protected $_gridView;
    protected $_state;

    public function events() {
        return [
            FilterStateInterface::EVENT_INIT => 'afterInit',
        ];
    }

    public function afterInit() {
        $session = Yii::$app->session;
        if( ! $session->isActive) {
            $session->open();
        }
        $this->saveState();
    }

    public function saveState() {
        $session = Yii::$app->session;
        /** @var \yii\grid\GridView $gridView */
        $this->_gridView = $gridView = $this->owner;
        $this->_state = FilterStateTrait::getFilterStateParams($this->id);
        // Filter
        /** @var \yii\grid\DataColumn $column */
        foreach ($gridView->columns as $column) {
            if (!$column instanceof DataColumn) {
                continue;
            }
            if ($column->filter !== false) {
                $this->composeFilterState($column);
            }
        }
        // Sort
        if ($gridView->dataProvider->getSort() !== false) {
            $sort = $gridView->dataProvider->getSort();
            if (($sortValue = ArrayHelper::getValue($sort->params, $sort->sortParam)) !== null) {
                $this->_state[$sort->sortParam] = $sortValue;
            } else {
                unset($this->_state[$sort->sortParam]);
            }
        }
        // Pagination
        if ($gridView->dataProvider->getPagination() !== false) {
            $pagination = $gridView->dataProvider->getPagination();
            if (($pageValue = ArrayHelper::getValue($pagination->params, $pagination->pageParam)) !== null) {
                $this->_state[$pagination->pageParam] = $pageValue;
            } else {
                unset($this->_state[$pagination->pageParam]);
            }
            if (($pageSizeValue = ArrayHelper::getValue($pagination->params, $pagination->pageSizeParam)) !== null) {
                $this->_state[$pagination->pageSizeParam] = $pageSizeValue;
            } else {
                unset($this->_state[$pagination->pageSizeParam]);
            }
        }

        $session->set(static::buildKey($this->id), $this->_state);
    }

    /**
     * @param $column
     * @return void
     */
    public function composeFilterState($column) {
        if (!preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $column->attribute, $matches)) {
            throw new InvalidParamException('Attribute name must contain word characters only.');
        }

        $formName = $this->_gridView->filterModel->formName();
        $value = Html::getAttributeValue($this->_gridView->filterModel, $column->attribute);
        $keys = [$formName];
        if ($matches[1] === '') {
            $keys[] = $matches[2];
            if ($matches[3] !== '') {
                $keys[] = $matches[3];
            }
        } else {
            $keys[] = $matches[1];
            $keys[] = $matches[2];
            if ($matches[3] !== '') {
                $keys[] = $matches[3];
            }
        }

        $s = &$this->_state;
        foreach ($keys as $key) {
            if (end($keys) === $key) {
                is_array($s) and $s[$key] = $value;
            } else {
                $s[$key] = isset($s[$key]) ? $s[$key] : [];
                $s = &$s[$key];
            }
        }
    }

    /**
     * @param $id
     * @return string
     */
    public static function buildKey($id) {
        return static::KEY_PREFIX . '_' . md5(Yii::$app->controller->route.$id);
    }
}