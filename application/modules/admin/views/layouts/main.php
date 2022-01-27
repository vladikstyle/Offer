<?php

use app\modules\admin\widgets\Alert;
use app\modules\admin\widgets\AdminSearchWidget;
use app\helpers\Html;

/** @var $this \yii\web\View */
/** @var $content string */

\app\modules\admin\assets\AdminAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="baseUrl" content="<?= \app\helpers\Url::to('/', true) ?>">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <?php $this->head() ?>
</head>

<body class="hold-transition skin-youdate fixed sidebar-mini">
<?php $this->beginBody() ?>

<div class="wrapper">

    <header class="main-header">
        <!-- Logo -->
        <a href="<?= Yii::$app->homeUrl ?>" class="logo">
            <span class="logo-mini">YouDate</span>
            <span class="logo-lg">YouDate</span>
        </a>

        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu position-relative">
                <?= AdminSearchWidget::widget() ?>
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="glyphicon glyphicon-user"></i>
                            <span><?= Yii::$app->user->identity->username ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header bg-dark-purple">
                                <?= Html::img(Yii::$app->user->identity->profile->getAvatarUrl(180, 180), [
                                    'alt' => Yii::$app->user->identity->username
                                ]) ?>
                                <p>
                                    <?= Yii::$app->user->identity->username ?>
                                    <small><?= Yii::$app->user->identity->email ?></small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="<?= \app\helpers\Url::to(['/profile/index']) ?>"
                                       class="btn btn-default btn-flat" rel="tooltip" title="<?= Yii::t('app', 'Settings') ?>">
                                        <i class="fa fa-user"></i>
                                    </a>
                                    <a href="<?= \app\helpers\Url::to(['/settings/profile']) ?>"
                                       class="btn btn-default btn-flat" rel="tooltip" title="<?= Yii::t('app', 'Profile') ?>">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                </div>
                                <div class="pull-right">
                                    <a href="<?= \app\helpers\Url::to(['/security/logout']) ?>"
                                       class="btn btn-default btn-flat" data-method="post"><?= Yii::t('app', 'Sign out') ?></a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <?= $this->render('_menu') ?>
        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1><?= Html::encode($this->title) ?></h1>
            <?= \yii\widgets\Breadcrumbs::widget([
                'tag' => 'ol',
                'homeLink' => ['label' => Yii::t('app', 'Administration'), 'url' => '/' . env('ADMIN_PREFIX')],
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
        </section>

        <!-- Main content -->
        <section class="content">
            <?= Alert::widget() ?>
            <?= $content ?>
        </section>
        <!-- /.content -->

        <div class="content-fade"></div>
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
        Powered by <strong><a href="https://youdate.website">YouDate</a></strong>
    </footer>
</div>
<!-- ./wrapper -->
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
