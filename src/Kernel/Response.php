<?php
declare(strict_types=1);

namespace App\Kernel;

final class Response
{
    public static function json(array $data, int $status=200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }

    public static function html(string $html, int $status=200): void {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    public static function error(string $message, int $status=400): void {
        self::json(['ok'=>false, 'error'=>$message], $status);
    }
}
