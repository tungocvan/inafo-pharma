<?php

return [
    'name' => 'User',
    'type' => 'shell',
    'enabled' => true,
    'permissions' => [
        'view_user',
        'create_user',
        'edit_user',
        'delete_user',
        'import_user',
        'export_user',
    ],
];
