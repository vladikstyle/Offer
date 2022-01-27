<?php

use app\helpers\Html;
use youdate\widgets\ActiveForm;
use youdate\widgets\Connect;

/** @var \app\base\View $this */
/** @var \app\forms\LoginForm $loginForm */

$loginForm = $this->getLoginForm();
?>
<div class="modal modal-auth fade" tabindex="-1" role="dialog" id="modal-auth">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?= Yii::t('youdate', 'Log in') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= Yii::t('youdate', 'Close') ?>"></button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'action' => ['/security/login'],
                    'enableAjaxValidation' => false,
                    'enableClientValidation' => true,
                    'validateOnBlur' => false,
                    'validateOnType' => false,
                    'validateOnChange' => false,
                ]) ?>
                <?= $form->field($loginForm, 'login', [
                    'inputOptions' => [
                        'autofocus' => 'autofocus',
                        'class' => 'form-control',
                        'tabindex' => '1',
                    ],
                ])->label(Yii::t('youdate', 'E-mail or Username')) ?>

                <?= $form->field($loginForm, 'password', [
                        'inputOptions' => [
                            'class' => 'form-control',
                            'tabindex' => '2',
                        ]
                    ])
                    ->passwordInput()
                    ->hint(Html::a(Yii::t('youdate', 'Forgot password?'),
                        ['/recovery/request'],
                        ['tabindex' => '5']
                    )
                    )
                ?>

                <?php if ($loginForm->isCaptchaRequired()): ?>
                    <?= $form->field($loginForm, 'captcha', ['inputOptions' => [
                        'tabindex' => '3',
                        'class' => 'form-control',
                    ]])->widget(\yii\captcha\Captcha::class) ?>
                <?php endif; ?>

                <?= $form->field($loginForm, 'rememberMe')->checkbox(['tabindex' => '4']) ?>

                <?= Html::submitButton(Yii::t('youdate', 'Sign in'),
                    ['class' => 'btn btn-primary btn-block', 'tabindex' => '4']
                ) ?>

                <?php ActiveForm::end(); ?>

                <div class="divider"><span><?= Yii::t('youdate', 'or login using social account') ?></span></div>

                <?= Connect::widget([
                    'baseAuthUrl' => ['/security/auth'],
                ]) ?>
            </div>
        </div>
    </div>
</div>
