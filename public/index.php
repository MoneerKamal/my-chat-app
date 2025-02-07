<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Controllers\GroupController;
use App\Controllers\MessageController;

require __DIR__ . '/../vendor/autoload.php';

// Create the Slim App
$app = AppFactory::create();

// You could add Middleware here (e.g., error handling, CORS, etc.)
// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Define routes
$app->post('/groups', [new GroupController(), 'createGroup']);
$app->post('/groups/{id}/join', [new GroupController(), 'joinGroup']);
$app->post('/groups/{id}/messages', [new MessageController(), 'sendMessage']);
$app->get('/groups/{id}/messages', [new MessageController(), 'listMessages']);

// Example health-check route
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['status' => 'OK']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
