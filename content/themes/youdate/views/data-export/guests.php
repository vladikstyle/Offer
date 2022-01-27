<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var $content string */
/** @var $title string */
/** @var $guests \app\models\Guest[] */
?>
<?php $this->beginContent('@theme/views/data-export/layout.php'); ?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <?= Html::encode($title) ?>
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($guests as $guest): ?>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <a class="d-block text-center text-gray" href="<?= Url::to(['/profile/view', 'username' => $guest->fromUser->username]) ?>">
                        <div class="avatar avatar-xxl" style="background-image: url('<?= $guest->fromUser->profile->getAvatarUrl() ?>')"></div>
                        <h5 class="name mt-2 text-gray-dark"><?= Html::encode($guest->fromUser->profile->getDisplayName()) ?></h5>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>
