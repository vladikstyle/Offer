<?php

use app\helpers\Html;
use app\helpers\Url;
use youdate\helpers\Icon;

/** @var $this \app\base\View */
/** @var $currentBalance integer */
/** @var $stripePublishableKey string */
/** @var $rate string */
/** @var $currency \app\models\Currency */
/** @var $prices \app\models\Price[] */
/** @var $siteName string */
/** @var $userId integer */
/** @var $userEmail string */
/** @var $stripeEnabled boolean */
/** @var $paypalEnabled boolean */
/** @var $robokassaEnabled bool */

$this->registerAssetBundle(youdate\assets\PaymentAsset::class);
$this->title = Yii::t('youdate', 'Buy credits');

?>

<?php $this->beginContent('@theme/views/balance/_layout.php', ['currentBalance' => $currentBalance]) ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?= Yii::t('youdate', 'Buy credits') ?>
        </h3>
    </div>
    <div class="card-body">
        <div class="payment-add">
            <?php $form = \youdate\widgets\ActiveForm::begin([
                'method' => 'post',
                'options' => [
                    'class' => 'payment-form',
                    'data-stripe-create-session' => Url::to(['stripe-create-session'], true),
                    'data-action-paypal' => Url::to(['process-paypal'], true),
                    'data-action-robokassa' => Url::to(['process-robokassa'], true),
                ],
            ]) ?>

            <?= $this->render('/_alert') ?>

            <div class="custom-controls-stacked mb-5">
                <div class="row">
                    <?php foreach ($prices as $price): ?>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="custom-control custom-radio payment-amount-variant d-flex align-items-center">
                                <input type="radio" class="custom-control-input credits-input"
                                       name="credits"
                                       checked="checked"
                                       data-amount="<?= $price->getActualPrice() ?>"
                                       value="<?= $price->credits ?>">
                                <span class="custom-control-label credits-count">
                                    <span class="credits-count">
                                        <?= Yii::t('youdate', '{count} credits', ['count' => $price->credits]) ?>
                                    </span>
                                    <span class="credits-amount">
                                        <?= Yii::t('youdate', 'for {amount} {currency}', [
                                            'amount' => sprintf(Html::encode($currency->format), $price->getActualPrice()),
                                            'currency' => '',
                                        ]) ?>
                                    </span>
                                </span>
                                <?php if (!empty($price->discount)): ?>
                                    <span class="text-right badge badge-primary badge-discount ml-auto">
                                        -<?= $price->discount ?>
                                    </span>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($stripeEnabled): ?>
                <?php $this->registerJsFile('https://js.stripe.com/v3/', ['position' => \app\base\View::POS_HEAD]) ?>
                <button type="button"
                        data-key="<?= $stripePublishableKey ?>"
                        data-currency="<?= $currency ?>"
                        data-name="<?= Html::encode($siteName) ?>"
                        data-description="<?= Yii::t('youdate', 'Credits purchase') ?>: "
                        data-color="white"
                        data-email="<?= Html::encode($userEmail) ?>"
                        data-image="<?= Url::to(['@themeUrl/static/images/logo-stripe.png'], true) ?>"
                        class="btn btn-outline-primary btn-stripe btn-lg mb-3 mb-md-0">
                    <?= Icon::fa('cc-stripe', ['class' => 'mr-2']) ?>
                    <?= Yii::t('youdate', 'Stripe') ?>
                </button>
            <?php endif; ?>

            <?php if ($paypalEnabled): ?>
                <button type="submit"
                        class="btn btn-outline-primary btn-paypal btn-lg mb-3 mb-md-0">
                    <?= Icon::fa('cc-paypal', ['class' => 'mr-2']) ?>
                    <?= Yii::t('youdate', 'PayPal') ?>
                </button>
            <?php endif; ?>

            <?php if ($robokassaEnabled): ?>
                <button type="submit"
                        class="btn btn-outline-primary btn-robokassa btn-lg mb-3 mb-md-0">
                    <?= Icon::fa('credit-card', ['class' => 'mr-2']) ?>
                    <?= Yii::t('youdate', 'Robokassa') ?>
                </button>
            <?php endif; ?>

            <?php \youdate\widgets\ActiveForm::end() ?>
        </div>
        <div class="payment-loader hidden pt-5 pb-5 mt-5 mb-5">
            <div class="dimmer active">
                <div class="loader"></div>
            </div>
        </div>
    </div>
</div>

<?php $this->endContent() ?>
