<?php
// src/Security/Exceptions/UnauthorizedException.php
declare(strict_types=1);

namespace App\Security\Exceptions;

class UnauthorizedException extends AuthException
{
    public function __construct(string $message = 'No autorizado')
    {
        parent::__construct($message, 'UNAUTHORIZED', 401);
    }
}