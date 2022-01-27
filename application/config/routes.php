<?php

return [
    // site routes
    'page/<view>' => 'site/page',

    // language
    'language/<language>' => 'site/change-language',

    // users
    'profile' => 'profile/index',
    'profile/<username>' => 'profile/view',
    'profile/<username>/request-access' => 'profile/request-access',

    // auth and signup
    'login' => 'security/login',
    'signup' => 'registration/register',
    [
        'pattern' => 'auth/<authclient>',
        'route' => 'security/auth',
        'dontOverwrite' => true,
    ],

    // dashboard
    'dashboard' => 'dashboard/index',

    // directory
    'browse' => 'directory/index',

    // groups
    [
        'pattern' => 'your-groups',
        'route' => 'group/index',
        'defaults' => ['forCurrentUser' => 1],
    ],
    'groups' => 'group/index',
    'groups/<alias>' => 'group/view',
    'groups/<alias>/management' => 'group/management-update',
    'groups/<alias>/management/users' => 'group/management-users',
    'groups/<alias>/join' => 'group/join',
    'groups/<alias>/leave' => 'group/leave',
    'groups/<alias>/management/users/approve/<groupUserId>' => 'group/approve',
    'groups/<alias>/management/users/decline/<groupUserId>' => 'group/decline',
    'groups/<alias>/management/users/toggle-ban/<groupUserId>' => 'group/toggle-ban',
    'groups/<alias>/management/users/toggle-admin/<groupUserId>' => 'group/toggle-admin',
    [
        'pattern' => 'groups/<alias>/<subPage>',
        'route' => 'group/view',
    ],

    // photo manager
    'manage' => 'photo-manage/index',
    'upload' => 'photo-manage/upload',

    // like manager
    'connections/likes/<type:(from-you|to-you|mutual)>' => 'connections/likes',
    'connections/likes' => 'connections/likes',

    // messages manager
    'messages' => 'messages/index',

    // block
    'block' => 'block/create',
    'unblock' => 'block/delete',

    // notifications
    'notifications' => 'notifications/index',

    // news
    'news' => 'news/index',
    'news/<alias>' => 'news/view',

    // help
    'help/<category>' => 'help/index',
    'help' => 'help/index',

    // admin
    [
        'pattern' => env('ADMIN_PREFIX'),
        'route' => env('ADMIN_PREFIX') . '/default/index',
        'dontOverwrite' => true,
    ],
];
