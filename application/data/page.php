<?php

use app\helpers\Html;

// Layout is required. See content/themes/youdate/views/layouts for more layouts
$this->context->layout = 'page-main';
$this->title = Yii::t('youdate', '{{pageTitle}}');
?>
<div class="card">
    <div class="card-body">
        <div class="text-wrap p-lg-6">
            <h2 class="mt-0 mb-4">
                <?= Html::encode($this->title) ?>
            </h2>
            Page content
        </div>
    </div>
</div>
