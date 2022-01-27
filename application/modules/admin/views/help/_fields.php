<?php

/** @var $help \app\models\Help */
/** @var $form \app\widgets\ActiveForm */
/** @var $languageCode string */
/** @var $languageTitle string */

echo $form->field($help, 'title' . ($languageCode !== false ? '_' . $languageCode : ''))
    ->textInput()
    ->label($help->getAttributeLabel('title') . ($languageTitle ? ' - ' . $languageTitle : ''));

echo $this->render('/partials/field-editor', [
    'form' => $form,
    'model' => $help,
    'attribute' => 'content' . ($languageCode !== false ? '_' . $languageCode : ''),
    'label' => $help->getAttributeLabel('content') . ($languageTitle ? ' - ' . $languageTitle : ''),
]);
