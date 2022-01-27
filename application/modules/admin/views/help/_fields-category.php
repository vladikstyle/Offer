<?php

/** @var $helpCategory \app\models\HelpCategory */
/** @var $form \app\widgets\ActiveForm */
/** @var $languageCode string */
/** @var $languageTitle string */
?>

<?= $form->field($helpCategory, 'title' . ($languageCode !== false ? '_' . $languageCode : ''))
    ->textInput()
    ->label($helpCategory->getAttributeLabel('title') . ($languageTitle ? ' - ' . $languageTitle : '')) ?>
