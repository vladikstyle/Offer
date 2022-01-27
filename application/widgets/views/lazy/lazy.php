<?php

use yii\widgets\Pjax;

/** @var $this \app\base\View */
/** @var $id string */
/** @var $lazyView string */
/** @var $lazyParams string */

$pjaxId = 'pjax-lazy-load-' . $id;
$this->registerJs("
    setTimeout(function() {
        $.pjax.reload({ container: '#{$pjaxId}', async: false });
    }, 150);
", \app\base\View::POS_READY);
?>
<?php Pjax::begin([
    'id' => 'pjax-lazy-load-' . $id,
    'enablePushState' => false,
    'enableReplaceState' => false,
    'options' => ['class' => 'no-progress'],
]) ?>
<?= $this->render($lazyView, $lazyParams) ?>
<?php Pjax::end() ?>
