<?php

namespace app\events;

use app\models\MessageAttachment;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\events
 */
class MessageAttachmentEvent extends MessageEvent
{
    /**
     * @var MessageAttachment
     */
    public $attachment;
}
