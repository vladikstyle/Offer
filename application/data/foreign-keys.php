<?php return [
    'admin' => [
        'fk_admin_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'balance' => [
        'fk_balance_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'balance_transaction' => [
        'fk_balance_transaction_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'ban' => [],
    'block' => [
        'fk_block_blocked_user' => [
            0 => 'user',
            'blocked_user_id' => 'id',
        ],
        'fk_block_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
    ],
    'country' => [],
    'country_translation' => [
        'fk_country_translation_country' => [
            0 => 'country',
            'country' => 'country',
        ],
    ],
    'currency' => [],
    'data_request' => [
        'fk_data_request_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'encounter' => [
        'fk_encounter_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
        'fk_encounter_to_user' => [
            0 => 'user',
            'to_user_id' => 'id',
        ],
    ],
    'geoname' => [
        'fk_geoname_country' => [
            0 => 'country',
            'country' => 'country',
        ],
    ],
    'geoname_translation' => [
        'fk_geoname_translation_geoname' => [
            0 => 'geoname',
            'geoname_id' => 'geoname_id',
        ],
    ],
    'gift' => [
        'fk_gift_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
        'fk_gift_item' => [
            0 => 'gift_item',
            'gift_item_id' => 'id',
        ],
        'fk_gift_to_user' => [
            0 => 'user',
            'to_user_id' => 'id',
        ],
    ],
    'gift_category' => [],
    'gift_item' => [
        'fk_gift_category' => [
            0 => 'gift_category',
            'category_id' => 'id',
        ],
    ],
    'group' => [
        'fk_group_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'group_post' => [
        'fk_group_post_group' => [
            0 => 'group',
            'group_id' => 'id',
        ],
        'fk_group_post_post' => [
            0 => 'post',
            'post_id' => 'id',
        ],
    ],
    'group_user' => [
        'fk_group_user_group' => [
            0 => 'group',
            'group_id' => 'id',
        ],
        'fk_group_user_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'guest' => [
        'fk_guest_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
        'fk_guest_visited_user' => [
            0 => 'user',
            'visited_user_id' => 'id',
        ],
    ],
    'language' => [],
    'language_source' => [],
    'language_translate' => [
        'language_translate_ibfk_1' => [
            0 => 'language',
            'language' => 'language_id',
        ],
        'language_translate_ibfk_2' => [
            0 => 'language_source',
            'id' => 'id',
        ],
    ],
    'like' => [
        'fk_like_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
        'fk_like_to_user' => [
            0 => 'user',
            'to_user_id' => 'id',
        ],
    ],
    'log' => [],
    'message' => [
        'fk_message_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
        'fk_message_to_user' => [
            0 => 'user',
            'to_user_id' => 'id',
        ],
    ],
    'message_attachment' => [
        'fk_message_attachment_message' => [
            0 => 'message',
            'message_id' => 'id',
        ],
    ],
    'migration' => [],
    'news' => [
        'fk_news_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'notification' => [
        'fk_notification_sender_user' => [
            0 => 'user',
            'sender_user_id' => 'id',
        ],
        'fk_notification_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'oauth2_access_token' => [
        'fk_access_token_oauth2_client_client_id' => [
            0 => 'oauth2_client',
            'client_id' => 'client_id',
        ],
    ],
    'oauth2_authorization_code' => [
        'fk_authorization_code_oauth2_client_client_id' => [
            0 => 'oauth2_client',
            'client_id' => 'client_id',
        ],
    ],
    'oauth2_client' => [],
    'oauth2_refresh_token' => [
        'fk_refresh_token_oauth2_client_client_id' => [
            0 => 'oauth2_client',
            'client_id' => 'client_id',
        ],
    ],
    'order' => [
        'fk_order_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'payment_customer' => [
        'fk_payment_customer_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'photo' => [
        'fk_photo_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'photo_access' => [
        'fk_photo_access_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
        'fk_photo_access_to_user' => [
            0 => 'user',
            'to_user_id' => 'id',
        ],
    ],
    'post' => [
        'fk_post_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'post_attachment' => [
        'fk_post_attachment_post' => [
            0 => 'post',
            'post_id' => 'id',
        ],
    ],
    'profile' => [
        'fk_profile_photo' => [
            0 => 'photo',
            'photo_id' => 'id',
        ],
        'fk_user_profile' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'profile_extra' => [
        'fk_profile_extra_field' => [
            0 => 'profile_field',
            'field_id' => 'id',
        ],
        'fk_profile_extra_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'profile_field' => [
        'fk_profile_field_category' => [
            0 => 'profile_field_category',
            'category_id' => 'id',
        ],
    ],
    'profile_field_category' => [],
    'queue' => [],
    'report' => [
        'fk_report_from_user' => [
            0 => 'user',
            'from_user_id' => 'id',
        ],
        'fk_report_reported_user' => [
            0 => 'user',
            'reported_user_id' => 'id',
        ],
    ],
    'setting' => [],
    'sex' => [],
    'social_account' => [
        'fk_user_account' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'spotlight' => [
        'fk_spotlight_photo' => [
            0 => 'photo',
            'photo_id' => 'id',
        ],
        'fk_spotlight_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'token' => [
        'fk_user_token' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'upload' => [
        'fk_upload_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'user' => [],
    'user_boost' => [
        'fk_user_boost_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'user_premium' => [
        'fk_user_premium_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'verification' => [
        'fk_verification_user' => [
            0 => 'user',
            'user_id' => 'id',
        ],
    ],
    'vote' => [],
    'vote_aggregate' => [],
];
