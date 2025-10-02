<?php

namespace App\Entity\Resource;

enum MapObjectTypes: string
{
    case YOU_HERE = 'you here';
    case HOTEL = 'hotel';
    case RESTAURANT = 'restaurant and social gathering';
    case SIGHT = 'sight';
    case PROJECT = 'project';

    public const array EASY_ADMIN_TYPES = [
        'Вы здесь' => MapObjectTypes::YOU_HERE->value,
        'Гостиницы и отели' => MapObjectTypes::HOTEL->value,
        'Рестораны и места общения' => MapObjectTypes::RESTAURANT->value,
        'Достопримечательности' => MapObjectTypes::SIGHT->value,
        'Проекты' => MapObjectTypes::PROJECT->value
    ];
}