<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validador simple basado en reglas string: required|email|min:3|max:50|in:a,b,c|date|numeric|unique:table,col[,ignoreId]
 */
final class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $messages;

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }

    public function validate(): bool
    {
        foreach ($this->rules as $field => $rawRules) {
            $rules = is_array($rawRules) ? $rawRules : explode('|', $rawRules);
            $value = $this->data[$field] ?? null;
            $isRequired = in_array('required', $rules, true);
            $isEmpty = $value === null || $value === '' || (is_array($value) && count($value) === 0);

            if (!$isRequired && $isEmpty) {
                continue;
            }

            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $method = 'rule' . ucfirst($name);
                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $param);
                    if (isset($this->errors[$field])) {
                        break; // un error por campo
                    }
                }
            }
        }
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function addError(string $field, string $defaultMessage): void
    {
        $this->errors[$field] = $this->messages[$field] ?? $defaultMessage;
    }

    private function ruleRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            $this->addError($field, "El campo $field es obligatorio.");
        }
    }

    private function ruleEmail(string $field, mixed $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo $field debe ser un email válido.");
        }
    }

    private function ruleMin(string $field, mixed $value, ?string $param): void
    {
        $min = (int) $param;
        $rules = is_array($this->rules[$field]) ? $this->rules[$field] : explode('|', (string)$this->rules[$field]);
        $isNumericContext = in_array('numeric', $rules, true) || in_array('integer', $rules, true);
        
        if ($isNumericContext && is_numeric($value)) {
            if ((float) $value < $min) {
                $this->addError($field, "El campo $field debe ser mayor o igual a $min.");
            }
        } elseif (mb_strlen((string) $value) < $min) {
            $this->addError($field, "El campo $field debe tener al menos $min caracteres.");
        }
    }

    private function ruleMax(string $field, mixed $value, ?string $param): void
    {
        $max = (int) $param;
        $rules = is_array($this->rules[$field]) ? $this->rules[$field] : explode('|', (string)$this->rules[$field]);
        $isNumericContext = in_array('numeric', $rules, true) || in_array('integer', $rules, true);

        if ($isNumericContext && is_numeric($value)) {
            if ((float) $value > $max) {
                $this->addError($field, "El campo $field debe ser menor o igual a $max.");
            }
        } elseif (mb_strlen((string) $value) > $max) {
            $this->addError($field, "El campo $field no puede superar los $max caracteres.");
        }
    }

    private function ruleNumeric(string $field, mixed $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($field, "El campo $field debe ser numérico.");
        }
    }

    private function ruleInteger(string $field, mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "El campo $field debe ser entero.");
        }
    }

    private function ruleDate(string $field, mixed $value): void
    {
        $d = date_create((string) $value);
        if (!$d) {
            $this->addError($field, "El campo $field debe ser una fecha válida.");
        }
    }

    private function ruleIn(string $field, mixed $value, ?string $param): void
    {
        $allowed = explode(',', (string) $param);
        if (!in_array((string) $value, $allowed, true)) {
            $this->addError($field, "El valor de $field no es válido.");
        }
    }

    private function ruleRegex(string $field, mixed $value, ?string $param): void
    {
        if (!$param || !preg_match($param, (string) $value)) {
            $this->addError($field, "El campo $field tiene formato inválido.");
        }
    }

    private function ruleUnique(string $field, mixed $value, ?string $param): void
    {
        if (!$param) {
            return;
        }
        [$table, $column, $ignoreId] = array_pad(explode(',', $param), 3, null);
        $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = :v";
        $bindings = [':v' => $value];
        if ($ignoreId !== null) {
            $primary = 'id';
            if (str_contains($ignoreId, ':')) {
                [$primary, $ignoreId] = explode(':', $ignoreId, 2);
            }
            $sql .= " AND `$primary` <> :id";
            $bindings[':id'] = $ignoreId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        if ((int) $stmt->fetchColumn() > 0) {
            $this->addError($field, "El campo $field ya está registrado.");
        }
    }

    private function ruleConfirmed(string $field, mixed $value): void
    {
        $confirmKey = $field . '_confirmation';
        if (($this->data[$confirmKey] ?? null) !== $value) {
            $this->addError($field, "La confirmación de $field no coincide.");
        }
    }
}
