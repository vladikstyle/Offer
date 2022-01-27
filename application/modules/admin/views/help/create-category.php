<?php

/** @var $this \yii\web\View */
/** @var $helpCategory \app\models\HelpCategory */
/** @var $languages array */

$this->title = Yii::t('app', 'Create help category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Manage help'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_form-category', [
    'helpCategory' => $helpCategory,
    'languages' => $languages,
]);
