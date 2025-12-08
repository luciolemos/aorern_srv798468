<?php

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Valida campo obrigatório
     * 
     * @param string $field Nome do campo
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function required(string $field, ?string $message = null): self
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field][] = $message ?? "O campo {$field} é obrigatório.";
        }
        return $this;
    }

    /**
     * Valida email
     * 
     * @param string $field Nome do campo
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function email(string $field, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "O campo {$field} deve ser um email válido.";
        }
        return $this;
    }

    /**
     * Valida tamanho mínimo
     * 
     * @param string $field Nome do campo
     * @param int $min Tamanho mínimo
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function min(string $field, int $min, ?string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = $message ?? "O campo {$field} deve ter no mínimo {$min} caracteres.";
        }
        return $this;
    }

    /**
     * Valida tamanho máximo
     * 
     * @param string $field Nome do campo
     * @param int $max Tamanho máximo
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function max(string $field, int $max, ?string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = $message ?? "O campo {$field} deve ter no máximo {$max} caracteres.";
        }
        return $this;
    }

    /**
     * Valida valor numérico
     * 
     * @param string $field Nome do campo
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function numeric(string $field, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?? "O campo {$field} deve ser numérico.";
        }
        return $this;
    }

    /**
     * Valida URL
     * 
     * @param string $field Nome do campo
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function url(string $field, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = $message ?? "O campo {$field} deve ser uma URL válida.";
        }
        return $this;
    }

    /**
     * Valida correspondência entre campos
     * 
     * @param string $field Nome do campo
     * @param string $matchField Campo que deve corresponder
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function match(string $field, string $matchField, ?string $message = null): self
    {
        if (isset($this->data[$field], $this->data[$matchField])) {
            if ($this->data[$field] !== $this->data[$matchField]) {
                $this->errors[$field][] = $message ?? "O campo {$field} deve ser igual ao campo {$matchField}.";
            }
        }
        return $this;
    }

    /**
     * Valida padrão regex
     * 
     * @param string $field Nome do campo
     * @param string $pattern Padrão regex
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function pattern(string $field, string $pattern, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field][] = $message ?? "O campo {$field} não está no formato correto.";
        }
        return $this;
    }

    /**
     * Valida valor dentro de lista
     * 
     * @param string $field Nome do campo
     * @param array $values Valores permitidos
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function in(string $field, array $values, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field][] = $message ?? "O campo {$field} contém um valor inválido.";
        }
        return $this;
    }

    /**
     * Valida data
     * 
     * @param string $field Nome do campo
     * @param string $format Formato da data (padrão: Y-m-d)
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function date(string $field, string $format = 'Y-m-d', ?string $message = null): self
    {
        if (isset($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field][] = $message ?? "O campo {$field} deve ser uma data válida no formato {$format}.";
            }
        }
        return $this;
    }

    /**
     * Valida idade mínima
     * 
     * @param string $field Nome do campo (data de nascimento)
     * @param int $minAge Idade mínima
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function minAge(string $field, int $minAge, ?string $message = null): self
    {
        if (isset($this->data[$field])) {
            $birthDate = new \DateTime($this->data[$field]);
            $today = new \DateTime('today');
            $age = $birthDate->diff($today)->y;

            if ($age < $minAge) {
                $this->errors[$field][] = $message ?? "Você deve ter no mínimo {$minAge} anos.";
            }
        }
        return $this;
    }

    /**
     * Valida tamanho de arquivo
     * 
     * @param string $field Nome do campo de arquivo
     * @param int $maxSize Tamanho máximo em bytes
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function fileSize(string $field, int $maxSize, ?string $message = null): self
    {
        if (isset($_FILES[$field]) && $_FILES[$field]['size'] > $maxSize) {
            $maxMB = round($maxSize / 1048576, 2);
            $this->errors[$field][] = $message ?? "O arquivo não pode exceder {$maxMB}MB.";
        }
        return $this;
    }

    /**
     * Valida tipo de arquivo
     * 
     * @param string $field Nome do campo de arquivo
     * @param array $allowedTypes Tipos MIME permitidos
     * @param string|null $message Mensagem de erro customizada
     * @return self
     */
    public function fileType(string $field, array $allowedTypes, ?string $message = null): self
    {
        if (isset($_FILES[$field])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES[$field]['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes, true)) {
                $this->errors[$field][] = $message ?? "O tipo de arquivo não é permitido.";
            }
        }
        return $this;
    }

    /**
     * Validação customizada com callback
     * 
     * @param string $field Nome do campo
     * @param callable $callback Função de validação
     * @param string|null $message Mensagem de erro
     * @return self
     */
    public function custom(string $field, callable $callback, ?string $message = null): self
    {
        if (isset($this->data[$field])) {
            if (!$callback($this->data[$field])) {
                $this->errors[$field][] = $message ?? "O campo {$field} não passou na validação customizada.";
            }
        }
        return $this;
    }

    /**
     * Verifica se passou na validação
     * 
     * @return bool
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Verifica se falhou na validação
     * 
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Retorna todos os erros
     * 
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna erros de um campo específico
     * 
     * @param string $field
     * @return array
     */
    public function getErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Retorna primeira mensagem de erro de um campo
     * 
     * @param string $field
     * @return string|null
     */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Retorna dados validados (sanitizados)
     * 
     * @return array
     */
    public function validated(): array
    {
        return array_map(function ($value) {
            return is_string($value) ? htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8') : $value;
        }, $this->data);
    }

    /**
     * Helper estático para validação rápida
     * 
     * @param array $data Dados a validar
     * @param array $rules Regras de validação
     * @return self
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);

        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

            foreach ($ruleList as $rule) {
                if (is_string($rule)) {
                    $parts = explode(':', $rule);
                    $method = $parts[0];
                    $params = isset($parts[1]) ? explode(',', $parts[1]) : [];

                    if (method_exists($validator, $method)) {
                        $validator->$method($field, ...$params);
                    }
                }
            }
        }

        return $validator;
    }
}
