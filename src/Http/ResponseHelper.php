<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Http;

use function json_encode;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Small PSR-7 response factory helper for redirects and JSON responses.
 */
final class ResponseHelper
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    public function redirect(string $url, int $status = 302): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse($status)
            ->withHeader('Location', $url);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function json(array $data, int $status = 200): ResponseInterface
    {
        $stream = $this->streamFactory->createStream((string) json_encode($data));

        return $this->responseFactory
            ->createResponse($status)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withBody($stream);
    }

    public function notFound(): ResponseInterface
    {
        return $this->responseFactory->createResponse(404);
    }
}
