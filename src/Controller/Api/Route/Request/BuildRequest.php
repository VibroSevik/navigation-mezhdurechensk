<?php

namespace App\Controller\Api\Route\Request;

use Symfony\Component\Validator\Constraints as Assert;

class BuildRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public int $startId,

        #[Assert\NotBlank]
        public int $endId,
    )
    {}
}