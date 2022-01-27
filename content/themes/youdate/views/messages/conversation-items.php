<?php

use youdate\helpers\Icon;

?>
<div class="conversation-items d-flex">
    <div class="wrapper-items w-100" scroll-glue>
        <div class="items p-5">
            <div ng-cloak ng-repeat="item in messages track by item.id">
                <div class="date" ng-show="{{ checkMessageDatetime(item.datetime) }}">
                    <span>{{ getCurrentDate() }}</span>
                </div>
                <div class="item {{ item.type }} {{ getItemClasses(item) }}"
                     ng-mouseover="onMessageHover(item)"
                     ng-click="toggleMessage(item.id)"
                     scroll-glue-anchor>
                    <div class="item-body d-flex flex-row align-items-center {{ item.type == 'sent' ? 'flex-row-reverse' : '' }}">
                        <span class="avatar" ng-style="{'background-image': 'url(' + item.user.avatar + ')'}"></span>
                        <div ng-show="item.text.length > 0" class="text {{ item.type == 'sent' ? 'bg-azure text-white' : 'bg-gray-lightest text-gray-dark' }} p-2 rounded">
                            {{ item.text }}
                        </div>
                        <div class="images">
                            <div class="image"
                                 ng-repeat="attachment in item.attachments track by attachment.id"
                                 ng-show="attachment.type == 'image'">
                                <a href="{{ attachment.url }}" ng-click="showModal(item.attachments, $index, $event)">
                                    <img ng-src="{{ attachment.thumbnail }}" alt="" class="d-block">
                                </a>
                            </div>
                        </div>
                        <small class="time text-gray">{{ getTime(item.datetime) }}</small>
                        <span class="spinner" ng-show="isMessagePending(item)">
                            <?= Icon::fa('spinner', ['class' => 'fa-spin']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
