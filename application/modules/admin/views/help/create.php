<?php

/** @var $this \yii\web\View */
/** @var $help \app\models\Help */
/** @var $helpCategories \app\models\HelpCategory[] */
/** @var $languages array */

$this->title = Yii::t('app', 'Create help item');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Manage help'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_form', [
    'help' => $help,
    'helpCategories' => $helpCategories,
    'languages' => $languages,
]);
