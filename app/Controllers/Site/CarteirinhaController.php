<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\DigitalCardModel;

class CarteirinhaController extends Controller
{
    private DigitalCardModel $cards;

    public function __construct()
    {
        $this->cards = new DigitalCardModel();
    }

    public function index(): void
    {
        header('Location: ' . BASE_URL . 'institucional');
        exit;
    }

    public function validar(string $token = ''): void
    {
        $token = trim($token);
        $card = null;
        $status = 'invalida';

        if ($token !== '' && $this->cards->isAvailable()) {
            $card = $this->cards->buscarPorToken($token);
            if ($card) {
                $status = (string) ($card['status'] ?? 'invalida');
            }
        }

        $snapshot = [];
        if ($card && !empty($card['snapshot_json'])) {
            $decoded = json_decode((string) $card['snapshot_json'], true);
            if (is_array($decoded)) {
                $snapshot = $decoded;
            }
        }

        $avatarPath = (string) ($snapshot['foto'] ?? ($card['associado_foto'] ?? ''));
        $avatarUrl = $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png');

        $this->renderTwig('site/pages/carteirinha_validacao', [
            'card' => $card,
            'status' => $status,
            'snapshot' => $snapshot,
            'avatarUrl' => $avatarUrl,
        ]);
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return BASE_URL . ltrim($value, '/');
    }
}

