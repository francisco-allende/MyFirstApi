<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';

require_once './middlewares/VerificadorLoginMiddleware.php';
require_once './middlewares/IdExisteMiddleware.php';

//tokens. Se me ocurre que solo un admin o dev puede verlos como perfil. Valido antes con middleware
require_once './middlewares/VerificarJWT.php';
require_once './middlewares/CrearToken.php';
require_once './middlewares/DevolverPayload.php';
require_once './middlewares/DevolverDatos.php';
require_once './middlewares/VerificarToken.php';
require_once './middlewares/EsAdmin.php';



// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/repo_heroku/slim-php-mysql-heroku/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos'); //corchete opcional
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno'); //llaves es variable
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('[/]', \UsuarioController::class . ':ModificarUno')->add(new IdExisteMiddleware());
    $group->put('/{usuario}', \UsuarioController::class . ':ModificarClavePorNombre'); 
    $group->delete('[/]', \UsuarioController::class . ':BorrarUno')->add(new IdExisteMiddleware());
    $group->post('/login', \UsuarioController::class . ':Login')->add(new VerificadorLoginMiddleware());
})->add(new VerificarJWT()); // valido que tenga un jwt en la cabecera. Debe ir aca asi no llega al controller sin valdiacion

$app->group('/credenciales', function(RouteCollectorProxy $group){
            $group->get('/{usuario}', \UsuarioController::class . ':VerificarCredenciales');
            $group->post('[/]', \UsuarioController::class . ':VerificarCredencialesPost')->add(new EsAdmin());
})->add(new VerificarJWT());



















// JWT test routes. Son de testeo, la onda es usarla por dentro como middlewares, como el crear en el login y el verificar en todas als de usuario
$app->group('/jwt', function (RouteCollectorProxy $group) {
  $group->post('/crearToken', new CrearToken()); 
  $group->get('/devolverPayLoad', new DevolverPayLoad()); 
  $group->get('/devolverDatos', new DevolverDatos()); 
  $group->get('/verificarToken', new VerificarToken()); 
});


$app->get('[/]', function (Request $request, Response $response) {    
    $response->getBody()->write("Slim Framework 4 PHP Francisco Allende");
    return $response;

});

$app->run();


