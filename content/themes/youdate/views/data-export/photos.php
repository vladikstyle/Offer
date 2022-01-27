<?php

use app\helpers\Html;

/** @var $content string */
/** @var $title string */
/** @var $photos array */

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
            <?php foreach ($photos as $photo): ?>
            <div class="col-4">
                <a href="<?= $photo['url'] ?>">
                    <img src="<?= $photo['thumbnail'] ?>" class="my-2">
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>
