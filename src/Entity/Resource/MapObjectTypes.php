<?php

namespace App\Entity\Resource;

enum MapObjectTypes: string
{
    case YOU_HERE = 'you here';
    case HOTEL = 'hotel';
    case RESTAURANT = 'restaurant and social gathering';
    case SIGHT = 'sight';
    case PROJECT = 'project';
}