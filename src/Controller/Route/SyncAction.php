<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Controller\Route;

use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Sync\RouteSynchronizer;

use function count;
use function implode;

use Psr\Http\Message\ResponseInterface;

use function sprintf;

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class SyncAction
{
    public function __construct(
        private readonly RouteSynchronizer $synchronizer,
        private readonly UrlGeneratorInterface $url,
        private readonly ResponseHelper $response,
        private readonly FlashInterface $flash,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $result = $this->synchronizer->sync();
        $parts = [];
        if ($result->getAddedPermissions() !== []) {
            $parts[] = sprintf('%d permission baru', count($result->getAddedPermissions()));
        }
        if ($result->getAddedRoutes() !== []) {
            $parts[] = sprintf('%d route diaktifkan', count($result->getAddedRoutes()));
        }
        if ($result->getAddedGroups() !== []) {
            $parts[] = sprintf('%d grup dibuat', count($result->getAddedGroups()));
        }
        if ($result->getDisabled() !== []) {
            $parts[] = sprintf('%d route dinonaktifkan', count($result->getDisabled()));
        }
        if ($result->getRemovedPermissions() !== []) {
            $parts[] = sprintf('%d permission dihapus', count($result->getRemovedPermissions()));
        }
        $message = $parts === []
            ? 'Tidak ada perubahan route.'
            : 'Sinkronisasi selesai: ' . implode(', ', $parts) . '.';
        $this->flash->set('success', $message);

        return $this->response->redirect($this->url->generate('admin/route/index'));
    }
}
