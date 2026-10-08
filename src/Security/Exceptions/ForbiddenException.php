<?php
// src/Security/Exceptions/ForbiddenException.php
declare(strict_types=1);

namespace App\Security\Exceptions;

class ForbiddenException extends AuthException
{
    public function __construct(string $message = 'Acceso denegado')
    {
        parent::__construct($message, 'FORBIDDEN', 403);
    }
}