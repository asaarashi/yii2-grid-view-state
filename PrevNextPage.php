<?php

namespace thrieu\grid;

use Yii;
use yii\data\ActiveDataProvider;

class PrevNextPage
{

    private $route;

    public $pageIdList;

    public function __construct($route = null)
    {
        $this->route = $route;
    }

    /**
     * @param ActiveDataProvider $dataProvider
     */
    public function cacheDataProvider($dataProvider): void
    {
        $key = $this->getDataProviderKey();

        $dataProvider = clone $dataProvider;

        /**
         * clear models for optimising
         */
        $dataProvider->refresh();
        /** @noinspection PhpUndefinedClassInspection */
        Yii::$app->cache->set($key, $dataProvider, 3600);
    }

    public function cachePageIdList(array $pageIdList = []): void
    {
        if ($pageIdList) {
            $this->pageIdList = $pageIdList;
        }
        $key = $this->getPageIdListKey();
        /** @noinspection PhpUndefinedClassInspection */
        Yii::$app->cache->set($key, $this->pageIdList, 3600);
    }

    private function loadDataProvider()
    {
        $key = $this->getDataProviderKey();
        /** @noinspection PhpUndefinedClassInspection */
        return Yii::$app->cache->get($key);

    }

    public function getPrevPage(int $id)
    {
        return $this->getPrevNextPage($id, true);
    }

    public function getNextPage(int $id)
    {
        return $this->getPrevNextPage($id, false);
    }

    private function getPrevNextPage(int $id, bool $directionPrev, bool $isSecondCall = false)
    {
        $key = $this->getPageIdListKey();
        /** @noinspection PhpUndefinedClassInspection */
        if (!$this->pageIdList && !$this->pageIdList = Yii::$app->cache->get($key)) {
            return false;
        }
        $prevId = false;
        reset($this->pageIdList);
        foreach ($this->pageIdList as $index => $listId) {
            if ((int)$listId === $id) {
                if ($directionPrev && $prevId) {
                    return $prevId;
                }

                if (!$directionPrev && isset($this->pageIdList[$index + 1])) {
                    return $this->pageIdList[$index + 1];
                }
                if ($isSecondCall) {
                    return false;
                }
                if (!$this->loadPage($directionPrev)) {
                    return false;
                }
                return $this->getPrevNextPage($id, $directionPrev, true);

            }
            $prevId = $listId;
        }
        return false;
    }

    /**
     * @return string
     */
    private function getPageIdListKey(): string
    {
        return FilterStateBehavior::buildKey('', $this->route)
            . Yii::$app->user->getId()
            . 'PageIdList';

    }

    /**
     * @return string
     */
    private function getDataProviderKey(): string
    {
        return FilterStateBehavior::buildKey('', $this->route)
            . Yii::$app->user->getId()
            . 'DataProvider';
    }

    /**
     * @param bool $directionPrev
     * @return bool
     */
    private function loadPage(bool $directionPrev): bool
    {
        /**
         * @var ActiveDataProvider $dataProvider
         */
        if (!$dataProvider = $this->loadDataProvider()) {
            return false;
        }

        $page = $dataProvider->pagination->getPage();
        if ($directionPrev && $page === 0) {
            return false;
        }

        if ($directionPrev) {
            $page--;
        } else {
            $page++;
        }
        $dataProvider->pagination->setPage($page);
        $dataProvider->getModels();

        if (!$newPageIdList = $dataProvider->getKeys()) {
            return false;
        }

        if ($directionPrev) {
            $this->pageIdList = array_merge($newPageIdList, $this->pageIdList);
        } else {
            $this->pageIdList = array_merge($this->pageIdList, $newPageIdList);
        }

        $this->cachePageIdList();
        $this->cacheDataProvider($dataProvider);
        return true;
    }
}