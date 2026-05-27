<?php

return [
    'name' => 'Partner',
    'type' => 'domain',
    'enabled' => true,
    'permissions' => [
        'view_partner',
        'create_partner',
        'edit_partner',
        'delete_partner',
    ],
    'tables' =>
    array(
        0 => 'partners',
    ),
];
