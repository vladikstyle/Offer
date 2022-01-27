<?php

use app\helpers\Url;
use youdate\helpers\Icon;

/** @var $this \app\base\View */

$cookieName = 'modalUploadPhoto';
if ($this->getParam('user.showUploadPhotoRequest') !== false) {
    $this->registerJs("
        if (Cookies.get('$cookieName') != 1) {
            $('.modal-upload-photo').modal()
        }
    ");
}
?>
<div class="modal modal-upload-photo fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <?= \youdate\widgets\EmptyState::widget([
                    'icon' => 'fe fe-image',
                    'title' => Yii::t('youdate', 'You don\'t have a photo'),
                    'subTitle' => Yii::t('youdate', 'Please upload your photo'),
                ]) ?>
            </div>
            <div class="modal-footer">
                <a href="<?= Url::to(['/settings/upload']) ?>"
                   data-cookie-name="<?= $cookieName ?>"
                   class="btn btn-primary">
                    <?= Icon::fa('image', ['class' => 'mr-2']) ?>
                    <?= Yii::t('youdate', 'Upload photo') ?>
                </a>
                <button type="button" class="btn btn-secondary btn-later" data-dismiss="modal"
                        data-cookie-name="<?= $cookieName ?>">
                    <?= Yii::t('youdate', 'Later') ?>
                </button>
            </div>
        </div>
    </div>
</div>
