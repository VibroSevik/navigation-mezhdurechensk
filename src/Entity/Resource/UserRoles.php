<?php

namespace App\Entity\Resource;

enum UserRoles: string
{
    case ADMIN = 'ROLE_ADMIN';

    public const array ALL = [
        UserRoles::ADMIN->value
    ];

    public const array EASY_ADMIN_ROLES = [
        'Администратор' => UserRoles::ADMIN->value
    ];

}