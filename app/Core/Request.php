<?php

namespace App\Core;

class Request
{
    private array $query;
    private array $post;
    private array $server;
    private array $files;
    private array $cookies;
    private ?array $json = null;

    public function __construct()
    {
        $this->query = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->server = $_SERVER ?? [];
        $this->files = $_FILES ?? [];
        $this->cookies = $_COOKIE ?? [];
        
        // Parse JSON body se Content-Type for application/json
        if ($this->isJson()) {
            $this->json = json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }

    /**
     * Retorna valor do query string ($_GET)
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    /**
     * Retorna valor do POST
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    /**
     * Retorna valor de qualquer método (POST > GET > JSON)
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public function input(?string $key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($this->query, $this->post, $this->json ?? []);
        }

        return $this->post[$key] 
            ?? $this->query[$key] 
            ?? ($this->json[$key] ?? $default);
    }

    /**
     * Retorna apenas os campos especificados
     * 
     * @param array $keys Campos desejados
     * @return array
     */
    public function only(array $keys): array
    {
        $data = $this->input();
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * Retorna todos exceto os campos especificados
     * 
     * @param array $keys Campos a excluir
     * @return array
     */
    public function except(array $keys): array
    {
        $data = $this->input();
        return array_diff_key($data, array_flip($keys));
    }

    /**
     * Verifica se um campo existe na request
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    /**
     * Verifica se todos os campos existem
     * 
     * @param array $keys
     * @return bool
     */
    public function hasAll(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Retorna arquivos enviados
     * 
     * @param string|null $key
     * @return mixed
     */
    public function file(?string $key = null)
    {
        if ($key === null) {
            return $this->files;
        }
        return $this->files[$key] ?? null;
    }

    /**
     * Verifica se tem arquivo
     * 
     * @param string $key
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Retorna cookie
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function cookie(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->cookies;
        }
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Retorna método HTTP
     * 
     * @return string
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Verifica se é método GET
     * 
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Verifica se é método POST
     * 
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Verifica se é método PUT
     * 
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method() === 'PUT';
    }

    /**
     * Verifica se é método DELETE
     * 
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method() === 'DELETE';
    }

    /**
     * Verifica se é método PATCH
     * 
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->method() === 'PATCH';
    }

    /**
     * Verifica se é requisição AJAX
     * 
     * @return bool
     */
    public function isAjax(): bool
    {
        return !empty($this->server['HTTP_X_REQUESTED_WITH']) 
            && strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verifica se é requisição JSON
     * 
     * @return bool
     */
    public function isJson(): bool
    {
        return isset($this->server['CONTENT_TYPE']) 
            && str_contains($this->server['CONTENT_TYPE'], 'application/json');
    }

    /**
     * Verifica se espera resposta JSON
     * 
     * @return bool
     */
    public function wantsJson(): bool
    {
        return isset($this->server['HTTP_ACCEPT']) 
            && str_contains($this->server['HTTP_ACCEPT'], 'application/json');
    }

    /**
     * Retorna URL atual
     * 
     * @return string
     */
    public function url(): string
    {
        $protocol = $this->isSecure() ? 'https://' : 'http://';
        return $protocol . $this->server['HTTP_HOST'] . $this->server['REQUEST_URI'];
    }

    /**
     * Retorna path da URL (sem query string)
     * 
     * @return string
     */
    public function path(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    /**
     * Verifica se é conexão HTTPS
     * 
     * @return bool
     */
    public function isSecure(): bool
    {
        return !empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off';
    }

    /**
     * Retorna IP do cliente
     * 
     * @return string
     */
    public function ip(): string
    {
        if (!empty($this->server['HTTP_CLIENT_IP'])) {
            return $this->server['HTTP_CLIENT_IP'];
        }
        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $this->server['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Retorna User Agent
     * 
     * @return string
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Retorna referer
     * 
     * @return string|null
     */
    public function referer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }

    /**
     * Retorna header específico
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, $default = null)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }

    /**
     * Retorna todos os headers
     * 
     * @return array
     */
    public function headers(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = str_replace('_', '-', substr($key, 5));
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }

    /**
     * Retorna dados JSON parseados
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function json(?string $key = null, $default = null)
    {
        if ($this->json === null) {
            return $default;
        }

        if ($key === null) {
            return $this->json;
        }

        return $this->json[$key] ?? $default;
    }

    /**
     * Sanitiza valor (remove tags HTML e trim)
     * 
     * @param string $value
     * @return string
     */
    public function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Retorna todos os dados com sanitização
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->input();
    }

    /**
     * Cria instância estática para uso global
     * 
     * @return self
     */
    public static function capture(): self
    {
        return new self();
    }
}
