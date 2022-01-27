<?php

use dosamigos\chartjs\ChartJs;
use app\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this \app\base\View */
/* @var $counters array */
/* @var $info array */
/* @var $charts array */

$this->title = Yii::t('app', 'Dashboard');
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['index']];

$defaultChartOptions = [
    'type' => 'bar',
    'options' => [
        'height' => 250,
        'width' => 600,
    ],
    'clientOptions' => [
        'legend' => ['display' => false],
        'scales' => [
            'yAxes' => [
                [
                    'gridLines' => ['color' => 'rgba(0, 0, 0, 0.05)'],
                    'ticks' => [
                        'suggestedMin' => 0,
                        'stepSize' => 1
                    ],
                ]
            ],
            'xAxes' => [
                [
                    'gridLines' => ['color' => 'rgba(0, 0, 0, 0.05)'],
                ]
            ],
        ],
        'plugins' => [
            'filler' => ['propagate' => true],
        ],
    ],
    'data' => [
        'labels' => $charts['dailyLabels'],
        'datasets' => []
    ]
];
?>

<?php if ($this->session->hasFlash('updateSuccess')): ?>
    <div class="alert alert-success">
        <?= Html::encode($this->session->getFlash('updateSuccess')) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-user"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Users') ?></span>
                <span class="info-box-number"><?= $counters['users'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Online') ?></span>
                <span class="info-box-number"><?= $counters['usersOnline'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-photo"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Photos') ?></span>
                <span class="info-box-number"><?= $counters['photos'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-photo"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= Yii::t('app', 'Photos to verify') ?></span>
                <span class="info-box-number"><?= $counters['photosUnverified'] ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Users') ?></h3>
            </div>
            <div class="box-body">
                <?= ChartJs::widget(ArrayHelper::merge($defaultChartOptions, [
                    'data' => [
                        'datasets' => [
                            [
                                'label' => Yii::t('app', 'New users'),
                                'backgroundColor' => 'rgba(57,156,105,0.5)',
                                'borderColor' => 'rgba(94,186,0,1)',
                                'pointBackgroundColor' => 'rgb(94,186,0)',
                                'pointBorderColor' => '#fff',
                                'pointHoverBackgroundColor' => '#fff',
                                'pointHoverBorderColor' => 'rgba(94,186,0,1)',
                                'data' => $charts['newUsersData'],
                            ],
                        ]
                    ]
                ])) ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Earnings') ?></h3>
            </div>
            <div class="box-body">
                <?= ChartJs::widget(ArrayHelper::merge($defaultChartOptions, [
                    'data' => [
                        'datasets' => [
                            [
                                'label' => Yii::t('app', 'Profit'),
                                'backgroundColor' => 'rgba(57,105,156,0.5)',
                                'borderColor' => 'rgba(94,0,186,1)',
                                'pointBackgroundColor' => 'rgb(94,0,186)',
                                'pointBorderColor' => '#fff',
                                'pointHoverBackgroundColor' => '#fff',
                                'pointHoverBorderColor' => 'rgba(94,0,186,1)',
                                'data' => $charts['profitData'],
                            ],
                        ]
                    ]
                ])) ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Information') ?></h3>
                <div class="pull-right">
                    <?php if ($info['debug']): ?>
                        <span class="badge bg-red ml-1">debug</span>
                    <?php endif; ?>
                    <span class="badge bg-blue ml-1"><?= $info['environment'] ?></span>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <td colspan="3">YouDate</td>
                        <td class="text-right"><span class="badge bg-blue"><?= $info['version'] ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="3">Yii</td>
                        <td class="text-right"><span class="badge bg-gray"><?= $info['frameworkVersion'] ?></span></td>
                    </tr>
                    <tr>
                        <td>PHP</td>
                        <td class="text-right">
                            <?php if ($info['phpVersionOutdated']): ?>
                                <a href="https://www.php.net/supported-versions.php"
                                   class="badge bg-red"
                                   target="_blank"
                                   title="<?= Yii::t('app', 'Outdated PHP version') ?>">
                                    <?= $info['phpVersion'] ?> <i class="fa fa-external-link"></i>
                                </a>
                            <?php else: ?>
                                <span class="badge bg-gray"><?= $info['phpVersion'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>MySQL</td>
                        <td class="text-right"><span class="badge bg-gray"><?= $info['mysqlVersion'] ?></span></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Cron and Queue') ?></h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-box-tool btn-default" type="button" data-toggle="modal" data-target="#cron-setup">
                        <?= Yii::t('app', 'Cron setup') ?>
                    </button>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <td><?= Yii::t('app', 'Last hourly cron') ?></td>
                        <td class="text-right">
                            <?php if ($info['cronHourly']): ?>
                                <span class="badge bg-green">
                                <?= date("Y-m-d H:i:s", $info['cronHourly']) ?>
                            </span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= Yii::t('app', 'never') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?= Yii::t('app', 'Last daily cron') ?></td>
                        <td class="text-right">
                            <?php if ($info['cronDaily']): ?>
                                <span class="badge bg-green">
                                <?= date("Y-m-d H:i:s", $info['cronDaily']) ?>
                            </span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= Yii::t('app', 'never') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?= Yii::t('app', 'Queued jobs') ?></td>
                        <td class="text-right">
                            <?php if ($info['queueSize']): ?>
                                <span class="badge bg-gray"><?= $info['queueSize'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-green"><?= Yii::t('app', 'all done') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'System Info') ?></h3>
            </div>
            <div class="box-body no-padding">
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <td><?= Yii::t('app', 'Time limit') ?></td>
                        <td class="text-right"><?= $info['timeLimit'] ?></td>
                    </tr>
                    <tr>
                        <td><?= Yii::t('app', 'Upload max filesize') ?></td>
                        <td class="text-right"><?= $info['uploadMaxFilesize'] ?></td>
                    </tr>
                    <tr>
                        <td><?= Yii::t('app', 'Post max size') ?></td>
                        <td class="text-right"><?= $info['postMaxSize'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cron-setup"
     tabindex="-1" role="dialog" aria-labelledby="cron-setup-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="cron-setup-title">
                    <?= Yii::t('app', 'Cron setup') ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('app', 'Daily cron command') ?>: <code>yii cron/daily</code></p>
                <p><?= Yii::t('app', 'Hourly cron command') ?>: <code>yii cron/hourly</code></p>
                <br>
                <p><strong><?= Yii::t('app', 'Example') ?>:</strong></p>
                <code style="display: block">
                    30 * * * * /usr/bin/php /path/to/youdate/application/yii cron/hourly >/dev/null 2>&1<br>
                    0 18 * * * /usr/bin/php /path/to/youdate/application/yii cron/daily >/dev/null 2>&1<br>
                    * * * * * /usr/bin/php /path/to/youdate/application/yii queue/run >/dev/null 2>&1
                </code>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= Yii::t('app', 'OK') ?>
                </button>
            </div>
        </div>
    </div>
</div>

