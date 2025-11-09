<?php

namespace App;

use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\OrganizationController;
use App\Controllers\AdminController;
use App\Core\Config;
use App\Core\Database;
use App\Core\Router;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Repositories\SocialLinkRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationMemberRepository;
use App\Repositories\AdminLogRepository;
use App\Services\AuthService;
use App\Services\ProfileService;
use App\Services\SessionService;
use App\Services\OrganizationService;
use App\Services\AdminService;
use App\Services\ExportService;
use App\Support\Hasher;
use App\Support\MediaStorage;
use App\Support\Validator;

class Application
{
    private Config $config;
    private Database $database;
    private Router $router;
    private SessionService $sessionService;

    public function __construct(string $envPath)
    {
        $this->config = new Config($envPath);
        $this->database = new Database($this->config);
        $this->router = new Router();

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        $dbConn = $this->database->getConnection();
        $userRepository = new UserRepository($dbConn);
        $profileRepository = new ProfileRepository($dbConn);
        $socialLinkRepository = new SocialLinkRepository($dbConn);
        $organizationRepository = new OrganizationRepository($dbConn);
        $organizationMemberRepository = new OrganizationMemberRepository($dbConn);
        $adminLogRepository = new AdminLogRepository($dbConn);
        $this->sessionService = new SessionService($dbConn);
        $validator = new Validator();

        $uploadsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
        $mediaStorage = new MediaStorage($uploadsPath);
        $exportService = new ExportService($uploadsPath);

        $authService = new AuthService($userRepository, $profileRepository, $this->sessionService, new Hasher());
        $profileService = new ProfileService($profileRepository, $socialLinkRepository, $mediaStorage, $exportService);
        $organizationService = new OrganizationService($organizationRepository, $organizationMemberRepository, $userRepository, $mediaStorage);
        $adminService = new AdminService($userRepository, $organizationRepository, $adminLogRepository);

        $authController = new AuthController($authService, $profileService, $validator);
        $profileController = new ProfileController($profileService, $validator);
        $organizationController = new OrganizationController($organizationService, $validator);
        $adminController = new AdminController($adminService);

        $this->router->post('/api/v1/auth/register', function (Request $request, Response $response) use ($authController) {
            $authController->register($request, $response);
        });

        $this->router->post('/api/v1/auth/login', function (Request $request, Response $response) use ($authController) {
            $authController->login($request, $response);
        });

        $this->router->post('/api/v1/auth/logout', function (Request $request, Response $response) use ($authController) {
            $authController->logout($request, $response);
        });

        $this->router->get('/api/v1/me/profile', function (Request $request, Response $response) use ($profileController) {
            $profileController->me($request, $response);
        });

        $this->router->put('/api/v1/me/profile', function (Request $request, Response $response) use ($profileController) {
            $profileController->update($request, $response);
        });

        $this->router->post('/api/v1/me/profile/avatar', function (Request $request, Response $response) use ($profileController) {
            $profileController->uploadAvatar($request, $response);
        });

        $this->router->post('/api/v1/me/profile/background', function (Request $request, Response $response) use ($profileController) {
            $profileController->uploadBackground($request, $response);
        });

        $this->router->get('/api/v1/me/profile/social-links', function (Request $request, Response $response) use ($profileController) {
            $profileController->listSocialLinks($request, $response);
        });

        $this->router->post('/api/v1/me/profile/social-links', function (Request $request, Response $response) use ($profileController) {
            $profileController->createSocialLink($request, $response);
        });

        $this->router->put('/api/v1/me/profile/social-links/{link_id}', function (Request $request, Response $response) use ($profileController) {
            $profileController->updateSocialLink($request, $response);
        });

        $this->router->delete('/api/v1/me/profile/social-links/{link_id}', function (Request $request, Response $response) use ($profileController) {
            $profileController->deleteSocialLink($request, $response);
        });

        $this->router->post('/api/v1/me/profile/export/qr', function (Request $request, Response $response) use ($profileController) {
            $profileController->exportQr($request, $response);
        });

        $this->router->post('/api/v1/me/profile/export/pdf', function (Request $request, Response $response) use ($profileController) {
            $profileController->exportPdf($request, $response);
        });

        $this->router->get('/api/v1/profiles/@{slug}', function (Request $request, Response $response) use ($profileController) {
            $profileController->public($request, $response);
        });

        $this->router->get('/api/v1/me/organizations', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->list($request, $response);
        });

        $this->router->post('/api/v1/organizations', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->create($request, $response);
        });

        $this->router->post('/api/v1/organizations/join', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->join($request, $response);
        });

        $this->router->get('/api/v1/organizations/{organization_id}', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->show($request, $response);
        });

        $this->router->put('/api/v1/organizations/{organization_id}', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->update($request, $response);
        });

        $this->router->post('/api/v1/organizations/{organization_id}/logo', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->uploadLogo($request, $response);
        });

        $this->router->post('/api/v1/organizations/{organization_id}/invite/refresh', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->refreshInvite($request, $response);
        });

        $this->router->get('/api/v1/organizations/{organization_id}/members', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->listMembers($request, $response);
        });

        $this->router->put('/api/v1/organizations/{organization_id}/members/{member_id}', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->updateMember($request, $response);
        });

        $this->router->delete('/api/v1/organizations/{organization_id}/members/{member_id}', function (Request $request, Response $response) use ($organizationController) {
            $organizationController->removeMember($request, $response);
        });

        $this->router->get('/api/v1/admin/users', function (Request $request, Response $response) use ($adminController) {
            $adminController->listUsers($request, $response);
        });

        $this->router->get('/api/v1/admin/organizations', function (Request $request, Response $response) use ($adminController) {
            $adminController->listOrganizations($request, $response);
        });

        $this->router->put('/api/v1/admin/organizations/{organization_id}/status', function (Request $request, Response $response) use ($adminController) {
            $adminController->updateOrganizationStatus($request, $response);
        });

        $this->router->delete('/api/v1/admin/users/{target_user_id}', function (Request $request, Response $response) use ($adminController) {
            $adminController->deleteUser($request, $response);
        });

        $this->router->get('/api/v1/admin/logs', function (Request $request, Response $response) use ($adminController) {
            $adminController->listLogs($request, $response);
        });
    }

    public function run(): void
    {
        $request = Request::capture();
        $sessionToken = $request->cookie('bion_session');
        if ($sessionToken) {
            $userId = $this->sessionService->resolveUserId($sessionToken);
            if ($userId !== null) {
                $request->setAttribute('user_id', $userId);
                $request->setAttribute('session_token', $sessionToken);
            }
        }
        $response = new Response();
        $this->router->dispatch($request, $response);
    }
}


