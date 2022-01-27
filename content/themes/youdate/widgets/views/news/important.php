<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var $newsModel \app\models\News */

?>
<div class="alert alert-primary alert-news mb-0">
    <div class="container">
        <a href="<?= Url::to(['news/view', 'alias' => $newsModel->alias]) ?>">
            <?= Html::encode($newsModel->title) ?>
        </a>
        <button type="button" class="close" data-dismiss="alert"
                data-toggle-visibility-target=".alert-news"
                data-toggle-visibility-cookie="news<?= $newsModel->id ?>">
        </button>
    </div>
</div>
