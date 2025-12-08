<?php
namespace App\Helpers;

class Toast {
    public static function render(): string {
        if (!empty($_SESSION['toast'])) {
            $toast = $_SESSION['toast'];
            unset($_SESSION['toast']);

            $type = htmlspecialchars($toast['type']);
            $message = htmlspecialchars($toast['message']);
            $icon = $type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';

            return <<<HTML
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-{$type} text-white">
                        <strong class="me-auto">Notificação</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <i class="bi bi-{$icon} me-2"></i>
                        {$message}
                    </div>
                </div>
            </div>
HTML;
        }

        return '';
    }
}
