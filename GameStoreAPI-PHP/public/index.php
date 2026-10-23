<?php

declare(strict_types=1);

use GameStore\API\Controllers\AuthController;
use GameStore\API\Controllers\CategoriesController;
use GameStore\API\Controllers\CustomersController;
use GameStore\API\Controllers\GamesController;
use GameStore\API\Controllers\LibraryController;
use GameStore\API\Controllers\PurchasesController;
use GameStore\API\Controllers\ReviewsController;
use GameStore\API\Controllers\WishlistController;
use GameStore\API\Http\Request;
use GameStore\API\Middleware\AuthMiddleware;
use GameStore\API\Middleware\CorsMiddleware;
use GameStore\API\Middleware\ExceptionHandler;
use GameStore\API\Router;
use GameStore\Application\Services\AchievementService;
use GameStore\Application\Services\AuthService;
use GameStore\Application\Services\CategoryService;
use GameStore\Application\Services\CustomerService;
use GameStore\Application\Services\GameService;
use GameStore\Application\Services\LibraryService;
use GameStore\Application\Services\PurchaseService;
use GameStore\Application\Services\ReviewService;
use GameStore\Application\Services\WishlistService;
use GameStore\Infrastructure\Database\Connection;
use GameStore\Infrastructure\Repositories\UnitOfWork;
use GameStore\Support\Env;

// Prefer Composer's autoloader if the developer ran `composer install`;
// otherwise fall back to the bundled zero-dependency PSR-4 autoloader.
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    require __DIR__ . '/../autoload.php';
}

Env::load(__DIR__ . '/../.env');

date_default_timezone_set('UTC');
ini_set('display_errors', Env::get('APP_DEBUG', false) ? '1' : '0');

// CORS primeiro: responde o preflight (OPTIONS) sem tocar no banco e
// garante os headers até nas respostas de erro.
CorsMiddleware::handle();

try {
    // --- Configuração JWT ---------------------------------------------------
    $jwtSecret = (string) Env::get('JWT_SECRET', '');
    $jwtIssuer = (string) Env::get('JWT_ISSUER', 'GameStoreAPI');
    $jwtAudience = (string) Env::get('JWT_AUDIENCE', 'GameStoreAPI.Clients');

    $secretIsWeak = strlen($jwtSecret) < 32 || str_starts_with($jwtSecret, 'troque-esta-chave');
    if ($secretIsWeak) {
        if ((string) Env::get('APP_ENV', 'production') !== 'local') {
            throw new RuntimeException('JWT_SECRET is missing, too short (<32 chars) or still the example value.');
        }
        error_log('[GameStoreAPI] AVISO: JWT_SECRET fraco ou igual ao exemplo. Gere um novo antes de publicar.');
    }

    // --- Composition root (injeção de dependências manual) ------------------
    $db = Connection::get();
    $uow = new UnitOfWork($db);

    $achievementService = new AchievementService($uow);
    $authService = new AuthService($uow, $jwtSecret, $jwtIssuer, $jwtAudience);
    $gameService = new GameService($uow);
    $categoryService = new CategoryService($uow);
    $purchaseService = new PurchaseService($uow, $achievementService);
    $libraryService = new LibraryService($uow);
    $wishlistService = new WishlistService($uow);
    $reviewService = new ReviewService($uow, $achievementService);
    $customerService = new CustomerService($uow);

    $authController = new AuthController($authService);
    $gamesController = new GamesController($gameService);
    $categoriesController = new CategoriesController($categoryService);
    $purchasesController = new PurchasesController($purchaseService);
    $libraryController = new LibraryController($libraryService);
    $wishlistController = new WishlistController($wishlistService);
    $reviewsController = new ReviewsController($reviewService);
    $customersController = new CustomersController($customerService);

    $router = new Router(new AuthMiddleware($jwtSecret));

    // --- Auth ---------------------------------------------------------------
    $router->post('/api/auth/register', [$authController, 'register']);
    $router->post('/api/auth/login', [$authController, 'login']);
    $router->post('/api/auth/change-password', [$authController, 'changePassword'], auth: true);

    // --- Games (catálogo público + gestão por Admin) ------------------------
    $router->get('/api/games', [$gamesController, 'index']);
    $router->get('/api/games/recommendations', [$gamesController, 'recommendations'], auth: true);
    $router->get('/api/games/{id}', [$gamesController, 'show']);
    $router->post('/api/games', [$gamesController, 'store'], auth: true, roles: ['Admin']);
    $router->put('/api/games/{id}', [$gamesController, 'update'], auth: true, roles: ['Admin']);
    $router->patch('/api/games/{id}/status', [$gamesController, 'changeStatus'], auth: true, roles: ['Admin']);
    $router->patch('/api/games/{id}/discount', [$gamesController, 'applyDiscount'], auth: true, roles: ['Admin']);
    $router->delete('/api/games/{id}/discount', [$gamesController, 'removeDiscount'], auth: true, roles: ['Admin']);
    $router->delete('/api/games/{id}', [$gamesController, 'destroy'], auth: true, roles: ['Admin']);

    // --- Categories ---------------------------------------------------------
    $router->get('/api/categories', [$categoriesController, 'index']);
    $router->get('/api/categories/{id}', [$categoriesController, 'show']);
    $router->post('/api/categories', [$categoriesController, 'store'], auth: true, roles: ['Admin']);
    $router->put('/api/categories/{id}', [$categoriesController, 'update'], auth: true, roles: ['Admin']);
    $router->delete('/api/categories/{id}', [$categoriesController, 'destroy'], auth: true, roles: ['Admin']);

    // --- Purchases ----------------------------------------------------------
    $router->get('/api/purchases', [$purchasesController, 'index'], auth: true);
    $router->get('/api/purchases/{id}', [$purchasesController, 'show'], auth: true);
    $router->post('/api/purchases', [$purchasesController, 'store'], auth: true);
    $router->post('/api/purchases/{id}/refund', [$purchasesController, 'refund'], auth: true);

    // --- Library ------------------------------------------------------------
    $router->get('/api/library', [$libraryController, 'index'], auth: true);
    $router->post('/api/library/play-session', [$libraryController, 'recordPlaySession'], auth: true);

    // --- Wishlist -----------------------------------------------------------
    $router->get('/api/wishlist', [$wishlistController, 'index'], auth: true);
    $router->post('/api/wishlist', [$wishlistController, 'store'], auth: true);
    $router->delete('/api/wishlist/{gameId}', [$wishlistController, 'destroy'], auth: true);

    // --- Reviews ------------------------------------------------------------
    $router->get('/api/games/{gameId}/reviews', [$reviewsController, 'indexForGame']);
    $router->post('/api/reviews', [$reviewsController, 'store'], auth: true);
    $router->put('/api/reviews/{id}', [$reviewsController, 'update'], auth: true);
    $router->delete('/api/reviews/{id}', [$reviewsController, 'destroy'], auth: true);

    // --- Customers (perfil e conquistas) ------------------------------------
    $router->get('/api/customers/me', [$customersController, 'me'], auth: true);
    $router->put('/api/customers/me', [$customersController, 'updateMe'], auth: true);
    $router->get('/api/customers/me/achievements', [$customersController, 'achievements'], auth: true);

    $router->dispatch(Request::capture());
} catch (Throwable $e) {
    // Falhas na inicialização (ex.: banco fora do ar) também respondem em JSON, com CORS.
    ExceptionHandler::handle($e);
}
