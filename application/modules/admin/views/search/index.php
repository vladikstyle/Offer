<?php

use app\widgets\LazyWidget;

/* @var $this \yii\web\View */
/* @var $query string */
/* @var $searchProviders array */

$this->title = Yii::t('app', 'Search');
$this->params['breadcrumbs'][] = $this->title;
$this->params['hideAdminSearch'] = true;
?>

<?= \app\modules\admin\widgets\AdminSearchWidget::widget(['viewName' => '/search/_form']) ?>

<?php foreach ($searchProviders as $alias => $searchProvider): ?>
    <?php $lazyView = $searchProvider['lazyView'] ?? "/search/_search-$alias-lazy" ?>
    <?php $realView = $searchProvider['realView'] ?? "/search/_search-$alias-real" ?>
    <div class="box no-border">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $searchProvider['title'] ?></h3>
        </div>
        <div class="box-body no-padding">
            <?= LazyWidget::widget([
                'id' => 'search-' . $alias,
                'lazyView' => $lazyView,
                'view' => $realView,
                'viewParams' => function () use ($query, $searchProvider) {
                    return [
                        'query' => $query,
                        'dataProvider' => $searchProvider['dataProvider'],
                    ];
                }
            ]) ?>
        </div>
    </div>
<?php endforeach; ?>
