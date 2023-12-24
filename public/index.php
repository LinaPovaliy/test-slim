<?php

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Faker\Factory as FakerFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$twig = Twig::create(__DIR__ . '/../templates', ['file_extension' => 'twig']);

$faker = FakerFactory::create();


$container = new Container();
AppFactory::setContainer($container);

$app = AppFactory::createFromContainer($container);

$container->set('twig', function () {
    return Twig::create(__DIR__ . '/../templates', ['file_extension' => 'twig']);
});

$app->add(TwigMiddleware::createFromContainer($app, 'twig'));


$app->get('/welcome', function ($request, $response) {
    return $this->get('twig')->render($response, 'welcome.twig');
});

$app->get('/api/users', function ($request, $response) {
    $queryParams = $request->getQueryParams();
    $limit = isset($queryParams['limit']) ? min($queryParams['limit'], 10) : 5;

    try {
        $faker = FakerFactory::create();
        $users = [];

        for ($i = 1; $i <= $limit; $i++) {
            $users[] = [
                'id'    => $i,
                'name'  => $faker->name,
                'email' => $faker->email,
                'image' => $faker->imageUrl,
            ];
        }

        return $response->withJson($users);
    } catch (\Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/api/users/{userId}', function ($request, $response, $args) use ($faker) {
    $userId = $args['userId'];

    $user = [
        'id'    => $userId,
        'name'  => $faker->name,
        'email' => $faker->email,
        'image' => $faker->imageUrl,
    ];

    return $response->withJson($user);
});

$app->delete('/api/users/{userId}', function ($request, $response, $args) {
    $userId = $args['userId'];

    return $response->withStatus(204);
});

$app->get('/users', function ($request, $response) use ($twig) {
    $limit = min($request->getQueryParams()['limit'] ?? 5, 10);
    $faker = Faker\Factory::create();

    $users = [];
    for ($i = 1; $i <= $limit; $i++) {
        $users[] = [
            'id'    => $i,
            'name'  => $faker->name,
            'email' => $faker->email,
            'image' => $faker->imageUrl,
        ];
    }

    return $twig->render($response, 'users.twig', ['users' => $users]);
});

$app->get('/users/{userId}', function ($request, $response, $args) use ($twig) {
    $userId = $args['userId'];
    $faker = Faker\Factory::create();

    $user = [
        'id'    => $userId,
        'name'  => $faker->name,
        'email' => $faker->email,
        'image' => $faker->imageUrl,
    ];

    return $twig->render($response, 'user.twig', ['user' => $user]);
});

$app->run();