<?php
declare(strict_types=1);

use App\Config\Config;
use App\Security\Exceptions\AuthException;

require __DIR__ . '/../vendor/autoload.php';

Config::load(__DIR__ . '/..');

header('Content-Type: application/json; charset=utf-8');

try {
    // Aquí va el front controller / router.
    // Por ahora solo demostración:
    // $router->dispatch();
    echo json_encode(['status' => 'ok']);
} catch (AuthException $e) {
    http_response_code($e->getStatusCode());
    echo json_encode([
        'error' => [
            'code'    => $e->getErrorCode(),
            'message' => $e->getMessage(),
            'details' => [],
        ],
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => [
            'code'    => 'INTERNAL_ERROR',
            'message' => Config::get('app_env') === 'development'
                ? $e->getMessage()
                : 'Error interno',
            'details' => [],
        ],
    ]);
}