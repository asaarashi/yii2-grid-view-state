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

    /** @var \yii\grid\GridView $gridView */
    protected $gridView;
    protected $state;

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

        $this->gridView = $this->owner;

        $this->readAndSaveState();
    }

    /**
     * Retrieves state params from GridView and save it to session, including filter params, sort params and pagination params.
     */
    public function readAndSaveState() {
        $session = Yii::$app->session;
        /** @var \yii\grid\GridView $gridView */
        $gridView = $this->gridView;
        $this->state = FilterStateTrait::getFilterStateParams($this->id);
        // Filter
        /** @var \yii\grid\DataColumn $column */
        foreach ($gridView->columns as $column) {
            if (!$column instanceof DataColumn
                || $column->attribute === null
                || $column->filter == false) {
                continue;
            }

            $this->composeFilterState($column);
        }
        // Sort
        if ($gridView->dataProvider->getSort() !== false) {
            $sort = $gridView->dataProvider->getSort();
            if (($sortValue = ArrayHelper::getValue($sort->params, $sort->sortParam)) !== null) {
                $this->state[$sort->sortParam] = $sortValue;
            } else {
                unset($this->state[$sort->sortParam]);
            }
        }
        // Pagination
        if ($gridView->dataProvider->getPagination() !== false) {
            $pagination = $gridView->dataProvider->getPagination();
            if (($pageValue = ArrayHelper::getValue($pagination->params, $pagination->pageParam)) !== null) {
                $this->state[$pagination->pageParam] = $pageValue;
            } else {
                unset($this->state[$pagination->pageParam]);
            }
            if (($pageSizeValue = ArrayHelper::getValue($pagination->params, $pagination->pageSizeParam)) !== null) {
                $this->state[$pagination->pageSizeParam] = $pageSizeValue;
            } else {
                unset($this->state[$pagination->pageSizeParam]);
            }
        }

        $session->set(static::buildKey($this->id), $this->state);
    }

    /**
     * Set filter state params, which is going to be set to session.
     * @param $column
     */
    public function composeFilterState($column) {
        if (!preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $column->attribute, $matches)) {
            throw new InvalidParamException('Attribute name must contain word characters only.');
        }

        $formName = $this->gridView->filterModel->formName();
        $value = Html::getAttributeValue($this->gridView->filterModel, $column->attribute);
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

        $s = &$this->state;
        foreach ($keys as $key) {
            if (end($keys) === $key) {
                if($value === null) {
                    $s[$key] = null;
                } else if(is_array($s)) {
                    $s[$key] = $value;
                }
            } else {
                $s[$key] = isset($s[$key]) ? $s[$key] : [];
                $s = &$s[$key];
            }
        }
    }

    /**
     * Removes state params from session
     * @param string $id
     */
    public static function clearState($id = null) {
        Yii::$app->session->remove(FilterStateBehavior::buildKey($id !== null ? $id : ''));
    }

    /**
     * Builds a unique key for the GridView.
     * It determines uniqueness of the GridView by a glue string of the current action route and a specified ID.
     * @param string $id
     * @return string
     */
    public static function buildKey($id) {
        return static::KEY_PREFIX . '_' . md5(Yii::$app->controller->route.$id);
    }

    /**
     * Retrieve state params from session.
     * @param string $id
     * @return array
     */
    public static function getState($id = null) {
        $state = Yii::$app->session->get(FilterStateBehavior::buildKey($id !== null ? $id : ''));
        return $state !== null ? $state : [];
    }
}
