<?php

use yii\widgets\Pjax;

/** @var $this \app\base\View */
/** @var $id string */
/** @var $view string */
/** @var $viewParams array */

$pjaxId = 'pjax-lazy-load-' . $id;
?>
<?php Pjax::begin([
    'id' => 'pjax-lazy-load-' . $id,
    'enablePushState' => false,
    'enableReplaceState' => false,
    'linkSelector' => false,
]) ?>
<?= $this->render($view, $viewParams) ?>
<?php Pjax::end() ?>
