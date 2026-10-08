<?php
declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function error(string $code, string $message, int $status = 400, array $details = []): void
    {
        self::json([
            'error' => [
                'code'    => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }

    public static function noContent(): void
    {
        http_response_code(204);
    }

    public static function paginated(array $data, int $page, int $limit, int $total): void
    {
        self::json([
            'data' => $data,
            'meta' => [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $total,
                'totalPages' => (int) ceil($total / max($limit, 1)),
            ],
        ]);
    }
}