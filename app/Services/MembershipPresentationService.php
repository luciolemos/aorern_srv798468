<?php

namespace App\Services;

class MembershipPresentationService
{
    public function enrichForList(array $solicitacao): array
    {
        $solicitacao['documentos'] = $this->normalizeDocuments(
            $this->decodeDocuments($solicitacao['documentos_json'] ?? null)
        );
        $solicitacao['avatar_url'] = $this->resolveAssetUrl($solicitacao['avatar'] ?? null);
        $solicitacao['preview_documento'] = $this->extractPreviewImage($solicitacao['documentos']);
        $solicitacao['notification_type_label'] = $this->formatNotificationType($solicitacao['last_notification_type'] ?? null);
        $solicitacao['localizacao_label'] = $this->formatLocationLabel($solicitacao['cidade'] ?? null, $solicitacao['uf'] ?? null);

        return $solicitacao;
    }

    private function formatNotificationType(?string $type): string
    {
        return match ($type) {
            'aprovacao' => 'Aprovação',
            'rejeicao' => 'Rejeição',
            'complementacao' => 'Complementação',
            default => 'Notificação',
        };
    }

    private function formatLocationLabel(?string $cidade, ?string $uf): string
    {
        $city = trim((string) $cidade);
        $state = strtoupper(trim((string) $uf));

        if ($city === '' && $state === '') {
            return '-';
        }

        if ($state !== '') {
            if (preg_match('#/[A-Z]{2}$#', $city)) {
                $city = preg_replace('#/[A-Z]{2}$#', '', $city) ?? $city;
                $city = trim($city);
            }

            return $city !== '' ? "{$city}/{$state}" : $state;
        }

        return $city !== '' ? $city : '-';
    }

    private function decodeDocuments(?string $json): array
    {
        if (!$json) {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeDocuments(array $documents): array
    {
        $normalized = [];

        foreach ($documents as $index => $document) {
            $mimeType = (string) ($document['mime_type'] ?? '');
            $path = (string) ($document['path'] ?? '');
            $normalized[] = [
                'name' => (string) ($document['name'] ?? ('Documento ' . ($index + 1))),
                'path' => $path,
                'url' => $this->resolveAssetUrl($path),
                'mime_type' => $mimeType,
                'is_image' => $this->isImageMimeType($mimeType),
            ];
        }

        return $normalized;
    }

    private function extractPreviewImage(array $documents): ?array
    {
        foreach ($documents as $document) {
            if (!empty($document['is_image']) && !empty($document['url'])) {
                return $document;
            }
        }

        return null;
    }

    private function isImageMimeType(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true);
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '/';
        return $baseUrl . ltrim($path, '/');
    }
}
