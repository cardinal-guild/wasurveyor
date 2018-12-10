<?php
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

require dirname(__DIR__).'/config/bootstrap.php';
if ($_SERVER['APP_DEBUG']) {
    umask(0000);
    Debug::enable();
}

// Request::setTrustedProxies(['0.0.0.0/0'], Request::HEADER_FORWARDED);

$kernel = new Kernel(getenv('APP_ENV'), getenv('APP_DEBUG'));
$request = Request::createFromGlobals();
// tell Symfony about your reverse proxy
Request::setTrustedProxies(
    ['127.0.0.1', ' 172.17.0.0/8', '172.17.0.6', $request->server->get('REMOTE_ADDR')],
    Request::HEADER_X_FORWARDED_ALL
);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
