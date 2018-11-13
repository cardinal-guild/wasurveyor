<?php
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

require __DIR__.'/../vendor/autoload.php';

// The check is to ensure we don't use .env in production
if (!getenv('APP_ENV')) {
    (new Dotenv())->load(__DIR__.'/../.env');
}

if (getenv('APP_DEBUG')) {
    // WARNING: You should setup permissions the proper way!
    // REMOVE the following PHP line and read
    // https://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup
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
