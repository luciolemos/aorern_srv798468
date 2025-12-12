<?php

namespace App\Helpers;

class Toast
{
    private const TYPE_PRESETS = [
        'success' => ['icon' => 'check-circle-fill', 'title' => 'Tudo certo'],
        'danger' => ['icon' => 'x-circle-fill', 'title' => 'Erro'],
        'warning' => ['icon' => 'exclamation-triangle-fill', 'title' => 'Atenção'],
        'info' => ['icon' => 'info-circle-fill', 'title' => 'Aviso'],
    ];

    public static function render(): string
    {
        $queuedToasts = self::pullQueuedToasts();

        if (empty($queuedToasts)) {
            return '';
        }

        $items = array_filter(array_map(function (array $toast, int $index) {
            return self::renderSingleToast($toast, $index);
        }, $queuedToasts, array_keys($queuedToasts)));

        if (empty($items)) {
            return '';
        }

        return '<div class="toast-container position-fixed bottom-0 end-0 p-3">' . implode('', $items) . '</div>';
    }

    private static function pullQueuedToasts(): array
    {
        $queue = [];

        if (!empty($_SESSION['toasts']) && is_array($_SESSION['toasts'])) {
            $queue = $_SESSION['toasts'];
            unset($_SESSION['toasts']);
        } elseif (!empty($_SESSION['toast'])) {
            $queue[] = $_SESSION['toast'];
            unset($_SESSION['toast']);
        }

        return array_values(array_filter($queue, 'is_array'));
    }

    private static function renderSingleToast(array $toast, int $index): string
    {
        $message = trim((string) ($toast['message'] ?? ''));
        if ($message === '') {
            return '';
        }

        $type = self::sanitizeType($toast['type'] ?? 'info');
        $presets = self::TYPE_PRESETS[$type] ?? self::TYPE_PRESETS['info'];

        $title = (string) ($toast['title'] ?? $presets['title']);
        $icon = (string) ($toast['icon'] ?? $presets['icon']);
        $autohide = array_key_exists('autohide', $toast) ? filter_var($toast['autohide'], FILTER_VALIDATE_BOOLEAN) : true;
        $delay = isset($toast['delay']) ? max(1000, (int) $toast['delay']) : 5000;

        $titleHtml = self::escape($title);
        $messageHtml = self::escape($message);
        $iconHtml = self::escape($icon);
        $typeHtml = self::escape($type);
        $autohideAttr = $autohide ? 'true' : 'false';
        $offset = max(0, $index);

        return <<<HTML
        <div class="toast modern-toast toast-{$typeHtml} shadow-lg border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="{$autohideAttr}" data-bs-delay="{$delay}" style="--toast-index: {$offset};">
            <div class="toast-glow"></div>
            <div class="toast-body d-flex align-items-start gap-3">
                <span class="toast-icon" aria-hidden="true">
                    <i class="bi bi-{$iconHtml}"></i>
                </span>
                <div class="flex-grow-1">
                    <div class="toast-title">{$titleHtml}</div>
                    <div class="toast-message">{$messageHtml}</div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
HTML;
    }

    private static function sanitizeType(?string $type): string
    {
        $type = strtolower(preg_replace('/[^a-z]/', '', (string) $type));
        return array_key_exists($type, self::TYPE_PRESETS) ? $type : 'info';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
