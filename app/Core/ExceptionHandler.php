<?php

namespace App\Core;

use Throwable;

class ExceptionHandler
{
    private static bool $registered = false;
    private static string $logPath = '';

    /**
     * Registra handlers de exceção e erro
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$logPath = __DIR__ . '/../../logs/';
        
        // Cria diretório de logs se não existir
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }

        // Handler de exceções não capturadas
        set_exception_handler([self::class, 'handleException']);

        // Handler de erros fatais
        set_error_handler([self::class, 'handleError']);

        // Handler de shutdown (erros fatais)
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * Trata exceções não capturadas
     */
    public static function handleException(Throwable $exception): void
    {
        self::logException($exception);

        http_response_code(500);

        if (self::isDevelopment()) {
            self::renderDevelopmentError($exception);
        } else {
            self::renderProductionError();
        }

        exit(1);
    }

    /**
     * Trata erros PHP
     */
    public static function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        // Respeita error_reporting
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Trata erros fatais no shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError($error);

            http_response_code(500);

            if (self::isDevelopment()) {
                self::renderDevelopmentFatalError($error);
            } else {
                self::renderProductionError();
            }
        }
    }

    /**
     * Loga exceção em arquivo
     */
    private static function logException(Throwable $exception): void
    {
        $logFile = self::$logPath . date('Y-m-d') . '-errors.log';
        
        $message = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        error_log($message, 3, $logFile);
    }

    /**
     * Loga erro fatal
     */
    private static function logError(array $error): void
    {
        $logFile = self::$logPath . date('Y-m-d') . '-errors.log';
        
        $message = sprintf(
            "[%s] Error [%d]: %s in %s:%d\n\n",
            date('Y-m-d H:i:s'),
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        error_log($message, 3, $logFile);
    }

    /**
     * Renderiza erro em ambiente de desenvolvimento
     */
    private static function renderDevelopmentError(Throwable $exception): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro - <?= get_class($exception) ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #f8f9fa; 
                    color: #333; 
                    padding: 20px;
                }
                .container { max-width: 1200px; margin: 0 auto; }
                .error-header { 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white; 
                    padding: 30px; 
                    border-radius: 8px;
                    margin-bottom: 20px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .error-header h1 { font-size: 24px; margin-bottom: 10px; }
                .error-header p { font-size: 16px; opacity: 0.9; }
                .error-body { 
                    background: white; 
                    padding: 30px; 
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .error-section { margin-bottom: 30px; }
                .error-section h2 { 
                    font-size: 18px; 
                    margin-bottom: 15px; 
                    color: #667eea;
                    border-bottom: 2px solid #667eea;
                    padding-bottom: 5px;
                }
                .error-message { 
                    background: #fff3cd; 
                    border-left: 4px solid #ffc107;
                    padding: 15px; 
                    margin-bottom: 20px;
                    border-radius: 4px;
                }
                .error-file { 
                    background: #f8f9fa; 
                    padding: 15px; 
                    border-radius: 4px;
                    font-family: 'Courier New', monospace;
                    margin-bottom: 20px;
                }
                .stack-trace { 
                    background: #2d2d2d; 
                    color: #f8f8f2; 
                    padding: 20px; 
                    border-radius: 4px;
                    overflow-x: auto;
                    font-family: 'Courier New', monospace;
                    font-size: 13px;
                    line-height: 1.6;
                }
                .stack-item { margin-bottom: 10px; }
                .stack-number { color: #ff79c6; }
                .stack-file { color: #8be9fd; }
                .stack-line { color: #50fa7b; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-header">
                    <h1>❌ <?= get_class($exception) ?></h1>
                    <p>Uma exceção foi lançada durante a execução</p>
                </div>
                
                <div class="error-body">
                    <div class="error-section">
                        <h2>Mensagem</h2>
                        <div class="error-message">
                            <strong><?= htmlspecialchars($exception->getMessage()) ?></strong>
                        </div>
                    </div>

                    <div class="error-section">
                        <h2>Localização</h2>
                        <div class="error-file">
                            <strong>Arquivo:</strong> <?= htmlspecialchars($exception->getFile()) ?><br>
                            <strong>Linha:</strong> <?= $exception->getLine() ?>
                        </div>
                    </div>

                    <div class="error-section">
                        <h2>Stack Trace</h2>
                        <div class="stack-trace">
                            <?php foreach ($exception->getTrace() as $index => $trace): ?>
                                <div class="stack-item">
                                    <span class="stack-number">#<?= $index ?></span>
                                    <?php if (isset($trace['file'])): ?>
                                        <span class="stack-file"><?= htmlspecialchars($trace['file']) ?></span>
                                        <span class="stack-line">(<?= $trace['line'] ?? '?' ?>)</span>:
                                    <?php endif; ?>
                                    <?php if (isset($trace['class'])): ?>
                                        <?= htmlspecialchars($trace['class'] . $trace['type'] . $trace['function']) ?>()
                                    <?php elseif (isset($trace['function'])): ?>
                                        <?= htmlspecialchars($trace['function']) ?>()
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Renderiza erro fatal em desenvolvimento
     */
    private static function renderDevelopmentFatalError(array $error): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro Fatal</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #f8f9fa; 
                    padding: 20px;
                }
                .container { max-width: 800px; margin: 50px auto; }
                .error-box { 
                    background: white; 
                    padding: 40px; 
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    border-top: 4px solid #dc3545;
                }
                h1 { color: #dc3545; margin-bottom: 20px; }
                .error-message { 
                    background: #f8d7da; 
                    padding: 15px; 
                    border-radius: 4px;
                    margin-bottom: 20px;
                    color: #721c24;
                }
                .error-location { 
                    background: #f8f9fa; 
                    padding: 15px; 
                    border-radius: 4px;
                    font-family: monospace;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-box">
                    <h1>⚠️ Erro Fatal</h1>
                    <div class="error-message">
                        <strong><?= htmlspecialchars($error['message']) ?></strong>
                    </div>
                    <div class="error-location">
                        <strong>Arquivo:</strong> <?= htmlspecialchars($error['file']) ?><br>
                        <strong>Linha:</strong> <?= $error['line'] ?>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Renderiza erro genérico para produção
     */
    private static function renderProductionError(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro no Servidor</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    color: white;
                    text-align: center;
                    padding: 20px;
                }
                .error-container { max-width: 600px; }
                h1 { font-size: 72px; margin-bottom: 20px; }
                h2 { font-size: 32px; margin-bottom: 20px; font-weight: 300; }
                p { font-size: 18px; margin-bottom: 30px; opacity: 0.9; }
                a { 
                    display: inline-block;
                    background: white; 
                    color: #667eea; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 25px;
                    font-weight: 600;
                    transition: transform 0.2s;
                }
                a:hover { transform: translateY(-2px); }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>500</h1>
                <h2>Erro no Servidor</h2>
                <p>Desculpe, algo deu errado. Nossa equipe foi notificada e está trabalhando para resolver o problema.</p>
                <a href="<?= BASE_URL ?>">Voltar para Home</a>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Verifica se está em ambiente de desenvolvimento
     */
    private static function isDevelopment(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'production') === 'dev' 
            || getenv('APP_ENV') === 'dev'
            || (defined('APP_ENV') && APP_ENV === 'dev');
    }

    /**
     * Loga mensagem customizada
     */
    public static function log(string $message, string $level = 'INFO'): void
    {
        $logFile = self::$logPath . date('Y-m-d') . '-app.log';
        
        $logMessage = sprintf(
            "[%s] [%s] %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );

        error_log($logMessage, 3, $logFile);
    }
}
