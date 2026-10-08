<?php
declare(strict_types=1);

namespace App\Support;

final class Validator
{
    private array $errors = [];

    public function __construct(private array $data) {}

    public function required(string $field, ?string $label = null): self
    {
        $label ??= $field;
        if (!isset($this->data[$field]) || $this->data[$field] === '' || $this->data[$field] === null) {
            $this->errors[$field][] = "{$label} es obligatorio";
        }
        return $this;
    }

    public function string(string $field, int $min = 1, int $max = 255, ?string $label = null): self
    {
        $label ??= $field;
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!is_string($value)) {
            $this->errors[$field][] = "{$label} debe ser texto";
            return $this;
        }
        $len = mb_strlen($value);
        if ($len < $min) $this->errors[$field][] = "{$label} debe tener al menos {$min} caracteres";
        if ($len > $max) $this->errors[$field][] = "{$label} no debe superar {$max} caracteres";

        return $this;
    }

    public function email(string $field, ?string $label = null): self
    {
        $label ??= $field;
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '') return $this;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "{$label} no es un correo válido";
        }
        return $this;
    }

    public function date(string $field, ?string $label = null): self
    {
        $label ??= $field;
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '') return $this;
        $dt = \DateTime::createFromFormat('Y-m-d', (string) $value);
        if (!$dt || $dt->format('Y-m-d') !== $value) {
            $this->errors[$field][] = "{$label} debe tener formato YYYY-MM-DD";
        }
        return $this;
    }

    public function in(string $field, array $allowed, ?string $label = null): self
    {
        $label ??= $field;
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field][] = "{$label} debe ser uno de: " . implode(', ', $allowed);
        }
        return $this;
    }

    public function bool(string $field, ?string $label = null): self
    {
        $label ??= $field;
        if (!array_key_exists($field, $this->data)) return $this;
        if (!is_bool($this->data[$field])) {
            $this->errors[$field][] = "{$label} debe ser booleano";
        }
        return $this;
    }

    public function fails(): bool   { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
}