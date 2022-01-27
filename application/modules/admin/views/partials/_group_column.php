<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var $group \app\models\Group */

?>
<div class="group-column">
    <a href="<?= Url::to(['group/update', 'id' => $group->id]) ?>" data-pjax="0">
        <?php if ($group->photo_path): ?>
            <?= Html::img($group->getPhotoThumbnail(128, 128), [
                'class' => 'img-thumbnail',
                'width' => 64,
                'height' => 64,
            ]) ?>
        <?php else: ?>
            <div class="no-photo">
                <i class="fa fa-image"></i>
            </div>
        <?php endif; ?>
    </a>
</div>
