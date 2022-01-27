<?php

use app\helpers\Html;
use youdate\widgets\Sidebar;
use youdate\widgets\EmptyState;

/** @var $category string */
/** @var $helpCategory \app\models\HelpCategory */
/** @var $helpCategories \app\models\HelpCategory[] */
/** @var $helpItems \app\models\Help[] */

$this->context->layout = 'page-main';
$this->title = Yii::t('youdate', 'Help') . ' - ' . $helpCategory->title;
?>
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-help', 'linkSelector' => '.sidebar a']) ?>
<div class="page-content">
    <div class="row">
        <div class="col-lg-4 col-xl-3 mb-4 sidebar">
            <?= Sidebar::widget([
                'header' => Yii::t('youdate', 'Help'),
                'items' => Html::prepareHelpCategories($helpCategories, $category),
            ]) ?>
        </div>
        <div class="col-lg-8 col-xl-9">
            <?php if (count($helpItems)): ?>
                <?php foreach ($helpItems as $help): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= Html::encode($help->title) ?></h3>
                        </div>
                        <div class="card-body help-content">
                            <?= $help->content ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <?= EmptyState::widget([
                            'icon' => 'fe fe-file-text',
                            'title' => Yii::t('youdate', 'Ah, no help yet'),
                            'subTitle' => Yii::t('youdate', 'Soon there will be help articles'),
                        ]) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php \yii\widgets\Pjax::end() ?>

