<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;

readonly class JwtClientDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    )
    {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $pathUserAuthentication = new Model\PathItem(
            ref: 'JWT Token',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Client'],
                responses: [
                    '200' => [
                        'description' => 'Get Client JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => [
                                            'type' => 'string',
                                            'readOnly' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Get Client JWT token to login.',
                requestBody: new Model\RequestBody(
                    description: 'Generate Client new JWT Token',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'username' => [
                                        'type' => 'string',
                                        'example' => 'username',
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                        'example' => 'password',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );

        $pathAccessTokenRefresh = new Model\PathItem(
            ref: 'New access Token',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Login Check'],
                responses: [
                    '200' => [
                        'description' => 'Get access token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => [
                                            'type' => 'string',
                                            'readOnly' => true,
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Get access token by refresh token.',
                requestBody: new Model\RequestBody(
                    description: 'Generate client new access token',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'refresh_token' => [
                                        'type' => 'string',
                                    ]
                                ]
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );

        $openApi->getPaths()->addPath('/api/authentication_token', $pathUserAuthentication);
        $openApi->getPaths()->addPath('/api/token/refresh', $pathAccessTokenRefresh);

        return $openApi;
    }
}
