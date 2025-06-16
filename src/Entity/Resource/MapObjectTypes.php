<?php

namespace App\Entity\Resource;

enum MapObjectTypes: string
{
    case HOTEL = 'hotel';
    case RESTAURANT = 'restaurant and social gathering';
    case SIGHT = 'sight';
    case PROJECT = 'project';
}