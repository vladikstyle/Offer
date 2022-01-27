<?php

/* @var $this yii\web\View */
/* @var $admin \app\models\Admin */

$this->title = Yii::t('app', 'Add administrator or moderator');
?>
<?php $this->beginContent('@app/modules/admin/views/settings/_layout.php') ?>
<?= $this->render('_form', ['admin' => $admin]) ?>
<?php $this->endContent() ?>
