# yii2-grid-view-state
Save filters from GridView to session, keep the filter state between pages.

## Features
1. Very flexible. Separate setting and setting.
2. Setting via behavior.
3. Determines the GridView uniqueness by action route and customized ID.

## Usage
1. Extend the GridView class, simply implement FilterStateInterface and FilterStateTrait.
```php
namespace \app\widgets;

use thrieu\grid\FilterStateInterface;
use thrieu\grid\FilterStateTrait;

class GridView extends \yii\grid\GridView implements FilterStateInterface {
    use FilterStateTrait;
}
```

2. Attach behavior to your GridView widget.
```php
GridView::widget([
...
    'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),
...
]);
```

3. Get the params which merging the GridView state params with GET query params and set it to the filter model and the DataProvider.
```php
// Filter model
$chrModel->load(GridView::getMergedFilterStateParams());
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

## Roadmap
The functionality of clearing the state would be added in the nearly future.
