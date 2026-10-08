<?php
// src/Security/Exceptions/AuthException.php
declare(strict_types=1);

namespace App\Security\Exceptions;

class AuthException extends \RuntimeException
{
    protected string $errorCode;
    protected int $statusCode;

    public function __construct(string $message, string $errorCode = 'AUTH_ERROR', int $statusCode = 401)
    {
        parent::__construct($message);
        $this->errorCode  = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function getErrorCode(): string { return $this->errorCode; }
    public function getStatusCode(): int   { return $this->statusCode; }
}