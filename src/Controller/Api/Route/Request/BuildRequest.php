<?php

namespace App\Controller\Api\Route\Request;

use Symfony\Component\Validator\Constraints as Assert;

class BuildRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public int $destinationId,
    )
    {}
}