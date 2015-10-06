<?php
header("Content-Type: text/html; charset=utf-8");

require './vendor/autoload.php';
use Tracy\Debugger;
use Slim\Slim;
use Nette\Neon;

// slim
$config = \Nette\Neon\Neon::decode(file_get_contents('config/db.local.neon'));
$app = new Slim($config);

if ($app->config('mode' == 'development')) {
	Debugger::enable();
}

// database
$dsn = "{$app->config('db.driver')}:host={$app->config('db.hostname')};dbname={$app->config('db.dbname')};charset=utf8";
$pdo = new PDO($dsn, $app->config('db.username'), $app->config('db.password'));
$db = new NotORM($pdo);

// mailer
$transport = Swift_MailTransport::newInstance();
$mailer = Swift_Mailer::newInstance($transport);

require './app/api/router.php';
$router = new Router($app, $db, $mailer);

$router->run();

// run the Slim app
$app->run();
