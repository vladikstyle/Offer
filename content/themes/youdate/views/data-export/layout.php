<?php

use app\helpers\Html;
use app\helpers\Url;
use youdate\helpers\Icon;

/** @var $content string */
/** @var $this \app\base\View */

\youdate\assets\DataExportAsset::register($this);
$bodyClass = isset($this->params['body.cssClass']) ? $this->params['body.cssClass'] : 'body-default';
$rtlEnabled = isset($this->params['rtlEnabled']) && $this->params['rtlEnabled'];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" dir="<?= $rtlEnabled ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Language" content="en" />
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#4188c9">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,700">
    <title><?= Html::encode($this->title) ?></title>
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
</head>
<body class="<?= $rtlEnabled ? 'rtl' : 'ltr' ?> <?= $bodyClass ?>">
<?php $this->beginBody() ?>
<div class="page">
    <div class="page-main">
        <div class="header py-4">
            <div class="container">
                <div class="d-flex">
                    <a class="header-brand" href="<?= Url::to(['/'], true) ?>">
                        <?= Html::img(Url::to([$this->themeSetting('logoUrl', '@themeUrl/static/images/logo@2x.png')], true), [
                            'class' => 'header-brand-img', 'alt' => $this->frontendSetting('siteName', 'YouDate')
                        ]) ?>
                    </a>
                    <div class="d-flex order-lg-2 ml-auto">
                        <div class="nav-item">
                            <a href="index.html" class="btn btn-sm btn-outline-secondary">
                                <?= Icon::fe('menu') ?>
                                <?= Yii::t('youdate', 'Menu') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="my-3 my-md-5">
            <div class="container">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
