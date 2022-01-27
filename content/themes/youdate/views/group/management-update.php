<?php

use youdate\widgets\GroupHeader;
use youdate\widgets\ActiveForm;
use youdate\widgets\Upload;
use app\helpers\Html;
use app\models\Group;

/* @var $this \app\base\View */
/* @var $group \app\models\Group */
/* @var $groupUser \app\models\GroupUser */
/* @var $user \app\models\User */

$this->title = Yii::t('youdate', 'Update group info');
$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-group-management-update';
?>
<?= GroupHeader::widget([
    'group' => $group,
    'groupUser' => $groupUser,
    'user' => $user,
    'canManage' => true,
    'showCover' => false,
    'showBackButton' => true,
]) ?>
<div class="page-content">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <?= $this->render('_management_navigation', ['group' => $group]) ?>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('youdate', 'Update group info') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->render('_form', ['group' => $group]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
