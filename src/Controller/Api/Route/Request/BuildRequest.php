<?php

namespace App\Controller\Api\Route\Request;

class BuildRequest
{
    public function __construct(
        public int $startId,
        public int $endId,
    )
    {}
}