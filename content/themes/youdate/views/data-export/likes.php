<?php

use app\helpers\Html;
use app\helpers\Url;
use app\managers\LikeManager;

/** @var $content string */
/** @var $title string */
/** @var $likes array */

$blocks = [
    LikeManager::TYPE_FROM_CURRENT_USER => Yii::t('youdate', 'People you like'),
    LikeManager::TYPE_TO_CURRENT_USER => Yii::t('youdate', 'People who likes you'),
    LikeManager::TYPE_MUTUAL => Yii::t('youdate', 'Mutual likes'),
];
?>
<?php $this->beginContent('@theme/views/data-export/layout.php'); ?>

<?php foreach ($blocks as $type => $title): ?>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <?= $title ?>
            </h4>
        </div>
        <div class="card-body">
            <?php if (count($likes[$type])): ?>
                <div class="row">
                    <?php foreach ($likes[$type] as $user): ?>
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a class="d-block text-center text-gray" href="<?= Url::to(['/profile/view', 'username' => $user->username]) ?>">
                                <div class="avatar avatar-xxl" style="background-image: url('<?= $user->profile->getAvatarUrl() ?>')"></div>
                                <h5 class="name mt-2 text-gray-dark"><?= Html::encode($user->profile->getDisplayName()) ?></h5>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted"><?= Yii::t('youdate', 'No data') ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php $this->endContent(); ?>
