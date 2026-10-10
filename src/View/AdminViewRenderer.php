<?php

declare(strict_types=1);

namespace Agusedyc\Admin\View;

use Agusedyc\Admin\Access\ActiveRoleAccessChecker;
use Agusedyc\Admin\Access\UserIdProviderInterface;

use function dirname;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Renders admin views with the package layout and injects helpers (CSRF token, URL generator,
 * flash messages, current route) into every template and the layout.
 */
final class AdminViewRenderer implements CommonParametersInjectionInterface
{
    private readonly WebViewRenderer $renderer;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        private readonly Aliases $aliases,
        WebView $webView,
        private readonly CsrfTokenInterface $csrfToken,
        private readonly FlashInterface $flash,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CurrentRoute $currentRoute,
        private readonly ManagerInterface $manager,
        private readonly SessionInterface $session,
        private readonly UserIdProviderInterface $userIdProvider,
        private readonly array $options = [],
    ) {
        $viewPath = $this->viewPath();

        $layout = $this->options['layout'] ?? 'layout.php';
        $layoutPath = $layout === null ? null : $viewPath . '/' . $layout;

        $this->renderer = (new WebViewRenderer(
            $responseFactory,
            $streamFactory,
            $aliases,
            $webView,
            $viewPath,
            $layoutPath,
        ))->withAddedInjections($this);
    }

    public function render(string $view, array $parameters = []): ResponseInterface
    {
        return $this->renderer->render($view, $parameters);
    }

    public function renderPartial(string $view, array $parameters = []): ResponseInterface
    {
        return $this->renderer->renderPartial($view, $parameters);
    }

    public function getCommonParameters(): array
    {
        $userId = $this->userIdProvider->getUserId();
        $appRoles = [];
        if ($userId !== null) {
            $assignments = $this->manager->getRolesByUserId($userId);
            $appRoles = array_keys($assignments);
        }

        return [
            'csrfToken' => $this->csrfToken->getValue(),
            'csrfParam' => '_csrf',
            'url' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
            'flash' => $this->flash,
            'adminParams' => $this->options,
            'appRoles' => $appRoles,
            'activeAdminRole' => $this->session->get(ActiveRoleAccessChecker::SESSION_KEY),
        ];
    }

    private function viewPath(): string
    {
        $viewPath = $this->options['viewPath'] ?? null;

        return $viewPath === null || $viewPath === ''
            ? dirname(__DIR__, 2) . '/views'
            : $this->aliases->get((string) $viewPath);
    }
}
