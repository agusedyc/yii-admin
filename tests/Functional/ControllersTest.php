<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Functional;

use Agusedyc\Admin\Controller\Role\IndexAction as RoleIndexAction;
use Agusedyc\Admin\Controller\Role\PermissionAction;
use Agusedyc\Admin\Controller\Role\ViewAction as RoleViewAction;
use Agusedyc\Admin\Controller\Route\IndexAction as RouteIndexAction;
use Agusedyc\Admin\Controller\Route\SyncAction;
use Agusedyc\Admin\Http\ResponseHelper;
use Agusedyc\Admin\Repository\DbRouteRepository;
use Agusedyc\Admin\Route\RouterRouteProvider;
use Agusedyc\Admin\Sync\RouteSynchronizer;
use Agusedyc\Admin\Tests\Support\CsrfTokenStub;
use Agusedyc\Admin\Tests\Support\FlashStub;
use Agusedyc\Admin\Tests\Support\UrlGeneratorStub;
use Agusedyc\Admin\View\AdminViewRenderer;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequest;
use HttpSoft\Message\StreamFactory;

use function json_decode;
use function str_contains;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Role;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;
use Yiisoft\View\WebView;

final class ControllersTest extends FunctionalTestCase
{
    private Manager $manager;
    private ItemsStorageInterface $itemsStorage;
    private DbRouteRepository $repository;
    private AdminViewRenderer $view;
    private CurrentRoute $currentRoute;
    private ResponseHelper $response;
    private FlashStub $flash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRbacSchema();
        $this->createRouteSchema();

        $this->manager = new Manager(new ItemsStorage($this->db), new AssignmentsStorage($this->db));
        $this->itemsStorage = new ItemsStorage($this->db);
        $this->repository = new DbRouteRepository($this->db);

        (new RouteSynchronizer(
            $this->manager,
            new RouterRouteProvider($this->createRouteCollection()),
            $this->repository,
        ))->sync();

        $this->manager->addRole((new Role('editor'))->withDescription('Editor'));

        $this->flash = new FlashStub();
        $this->currentRoute = new CurrentRoute();
        $this->response = new ResponseHelper(new ResponseFactory(), new StreamFactory());
        $this->view = new AdminViewRenderer(
            new ResponseFactory(),
            new StreamFactory(),
            new Aliases(),
            new WebView(),
            new CsrfTokenStub(),
            $this->flash,
            new UrlGeneratorStub(),
            $this->currentRoute,
            $this->manager,
            $this->createMock(\Yiisoft\Session\SessionInterface::class),
            $this->createMock(\Agusedyc\Admin\Access\UserIdProviderInterface::class),
        );
    }

    private function createRouteCollection(): RouteCollection
    {
        $collector = new RouteCollector();
        $collector->addRoute(
            Route::get('/admin/route')->name('admin/route/index'),
            Route::post('/admin/route/sync')->name('admin/route/sync'),
            Route::get('/admin/route/{name}')->name('admin/route/edit'),
            Route::get('/admin/role')->name('admin/role/index'),
            Route::get('/admin/role/{name}')->name('admin/role/view'),
            Route::post('/admin/role/{name}/permission')->name('admin/role/permission'),
            Route::get('/post')->name('post/index'),
            Route::post('/post')->name('post/create'),
        );

        return new RouteCollection($collector);
    }

    private function setRouteArgument(string $name): void
    {
        $this->currentRoute->setRouteWithArguments(
            Route::get('/admin')->name('test-route'),
            ['name' => $name],
        );
    }

    public function testRouteIndexRenders(): void
    {
        $action = new RouteIndexAction($this->repository, $this->view);
        $response = $action(new ServerRequest(method: 'GET', uri: '/admin/route'));

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue(str_contains((string) $response->getBody(), 'post/index'));
    }

    public function testRoleIndexRenders(): void
    {
        $action = new RoleIndexAction($this->manager, $this->itemsStorage, $this->repository, $this->view);
        $response = $action(new ServerRequest(method: 'GET', uri: '/admin/role'));

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue(str_contains((string) $response->getBody(), 'editor'));
    }

    public function testRoleViewRendersPermissions(): void
    {
        $this->manager->addChild('editor', 'post/index');
        $this->setRouteArgument('editor');

        $action = new RoleViewAction(
            $this->manager,
            $this->itemsStorage,
            $this->repository,
            $this->currentRoute,
            $this->view,
            $this->response,
        );
        $response = $action(new ServerRequest(method: 'GET', uri: '/admin/role/editor'));

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue(str_contains((string) $response->getBody(), 'post/index'));
    }

    public function testPermissionToggle(): void
    {
        $this->setRouteArgument('editor');

        $action = new PermissionAction($this->manager, $this->currentRoute, $this->response);
        $request = (new ServerRequest(method: 'POST', uri: '/admin/role/editor/permission'))
            ->withParsedBody(['permission' => 'post/index']);

        $response = $action($request);
        $data = json_decode((string) $response->getBody(), true);

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($data['granted']);
        self::assertTrue($this->manager->hasChild('editor', 'post/index'));

        // Toggling again revokes.
        $data = json_decode((string) $action($request)->getBody(), true);

        self::assertFalse($data['granted']);
        self::assertFalse($this->manager->hasChild('editor', 'post/index'));
    }

    public function testSyncRedirects(): void
    {
        $this->setRouteArgument('n/a');

        $action = new SyncAction(
            new RouteSynchronizer(
                $this->manager,
                new RouterRouteProvider($this->createRouteCollection()),
                $this->repository,
            ),
            new UrlGeneratorStub(),
            $this->response,
            $this->flash,
        );

        $response = $action(new ServerRequest(method: 'POST', uri: '/admin/route/sync'));

        self::assertSame(302, $response->getStatusCode());
        self::assertTrue($this->flash->has('success'));
    }
}
