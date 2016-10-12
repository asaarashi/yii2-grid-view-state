# yii2-grid-view-state
Save filters from GridView to session, keep the filter state between pages.

## Features
1. Very flexible. Separate setting and getting.
2. Setting via behavior.
3. Determines uniqueness by the action route and a customizable ID.

## Installation 
1.  The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

    Add the following section to your `composer.json`
    ```
    "repositories":[
        {
            "type": "git",
            "url": "https://github.com/thrieu/yii2-grid-view-state.git"
        }
    ]
    ```
2.  Then either run
    ```
    php composer.phar require --prefer-dist thrieu/yii2-grid-view-state "dev-master"
    ```
    
    or add
    
    ```
    "thrieu/yii2-grid-view-state": "dev-master"
    ```
    
    to the require section of your `composer.json` file and then run `composer update`.

## Usage
### Step 1
Extend `GridView` class, simply implement `FilterStateInterface` and `FilterStateTrait`.
```php
namespace \app\widgets;

use thrieu\grid\FilterStateInterface;
use thrieu\grid\FilterStateTrait;

class GridView extends \yii\grid\GridView implements FilterStateInterface {
    use FilterStateTrait;
}
```
### Step 2
Attach the filter behavior to your `GridView` widget.
```php
GridView::widget([
...
    'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),
...
]);
```
### Step 3
Get the params which is merged with GridView state params and GET query params, and then set it to filter model and `DataProvider`.
```php
// Filter model
$model->load(GridView::getMergedFilterStateParams());
// DataProvider
$dataProvider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'params' => GridView::getMergedFilterStateParams(),
    ],
    'sort' => [
        'attributes' => $attributeOrders,
        'params' => GridView::getMergedFilterStateParams(),
    ],
]);
```

### Clear state
Add `ClearFilterStateBehavior` to your `behaviors()` of your controller.
```php
    public function behaviors()
    {
        return [
            ...
            'clearFilterState' => ClearFilterStateBehavior::className(),
            ...
        ];
    }

```
And then add a form to your frontend page.
```php
        $form = Html::beginForm();
        $form.= Html::hiddenInput('clear-state', '1');
        $form.= Html::hiddenInput('redirect-to', '');
        $form.= Button::widget([
            'label' => Yii::t('app', 'Reset filter'),
        ]);
        $form.= Html::endForm();
```

Enjoy it.
