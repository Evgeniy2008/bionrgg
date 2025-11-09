<?php
declare(strict_types=1);

use App\Bootstrap;
use App\Controllers\AuthController;
use App\Controllers\CompanyController;
use App\Exceptions\HttpException;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Http\Router;
use Throwable;

require_once dirname(__DIR__, 1) . '/../src/Bootstrap.php';

Bootstrap::init();

$request = Request::fromGlobals();
$router = new Router();

$authController = new AuthController();
$companyController = new CompanyController();

$router->add('POST', '/auth/register', static fn (Request $req) => $authController->register($req));
$router->add('POST', '/auth/login', static fn (Request $req) => $authController->login($req));

$router->add('POST', '/companies', static fn (Request $req) => $companyController->create($req));
$router->add('POST', '/companies/join', static fn (Request $req) => $companyController->join($req));
$router->add('GET', '/companies/{id}', static fn (Request $req, array $params) => $companyController->show($req, $params));
$router->add('PATCH', '/companies/{id}/settings', static fn (Request $req, array $params) => $companyController->updateSettings($req, $params));
$router->add('PATCH', '/companies/{id}/design', static fn (Request $req, array $params) => $companyController->updateDesign($req, $params));
$router->add('DELETE', '/companies/{id}', static fn (Request $req, array $params) => $companyController->destroy($req, $params));
$router->add('DELETE', '/companies/{id}/members/{member}', static fn (Request $req, array $params) => $companyController->removeMember($req, $params));

try {
    $response = $router->dispatch($request);
    if ($response instanceof JsonResponse) {
        $response->send();
    }
} catch (HttpException $httpException) {
    JsonResponse::fromThrowable($httpException)->send();
} catch (Throwable $throwable) {
    JsonResponse::fromThrowable($throwable)->send();
}

