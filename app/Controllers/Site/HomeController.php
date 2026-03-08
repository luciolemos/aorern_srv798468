<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\InstitutionalDocumentModel;
use App\Models\PatrocinadorModel;

class HomeController extends Controller
{
    public function index(): void
    {
        $documentModel = new InstitutionalDocumentModel();
        $documents = array_slice(
            $documentModel->paginar(1, 3, ['status' => 'published'])['data'] ?? [],
            0,
            3
        );
        $colaboradores = [];
        try {
            $patrocinadorModel = new PatrocinadorModel();
            $colaboradores = array_map(function (array $item): array {
                $logoPath = trim((string) ($item['logo_path'] ?? ''));
                $bannerPath = trim((string) ($item['banner_path'] ?? ''));
                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'nome' => (string) ($item['nome'] ?? ''),
                    'telefone' => $this->formatPhone((string) ($item['telefone'] ?? '')),
                    'whatsapp' => preg_replace('/\D+/', '', (string) ($item['whatsapp'] ?? '')),
                    'site' => (string) ($item['site'] ?? ''),
                    'instagram' => $this->normalizeInstagramUrl((string) ($item['instagram'] ?? '')),
                    'descricao_curta' => (string) ($item['descricao_curta'] ?? ''),
                    'logo_url' => $logoPath !== '' ? BASE_URL . ltrim($logoPath, '/') : null,
                    'banner_url' => $bannerPath !== '' ? BASE_URL . ltrim($bannerPath, '/') : null,
                    'exibir_texto_banner' => (int) ($item['exibir_texto_banner'] ?? 1) === 1,
                    'texto_cor_titulo' => $this->sanitizeHexColor((string) ($item['texto_cor_titulo'] ?? '#FFFFFF'), '#FFFFFF'),
                    'texto_cor_descricao' => $this->sanitizeHexColor((string) ($item['texto_cor_descricao'] ?? '#FFFFFF'), '#FFFFFF'),
                    'icone_cor' => $this->sanitizeHexColor((string) ($item['icone_cor'] ?? '#FFFFFF'), '#FFFFFF'),
                ];
            }, $patrocinadorModel->listarAtivos(40));
        } catch (\Throwable $exception) {
            $colaboradores = [];
        }

        $this->renderTwig('site/pages/home', [
            'official_documents' => $documents,
            'colaboradores' => $colaboradores,
        ]);
    }

    private function formatPhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 13 && str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
        }

        return $value;
    }

    private function sanitizeHexColor(string $value, string $fallback): string
    {
        $value = trim($value);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return strtoupper($value);
        }

        return strtoupper($fallback);
    }

    private function normalizeInstagramUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'https://www.instagram.com/aore.rn/';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $username = ltrim($value, '@');
        $username = preg_replace('/[^a-zA-Z0-9._]/', '', $username) ?? '';
        if ($username === '') {
            return 'https://www.instagram.com/aore.rn/';
        }

        return 'https://www.instagram.com/' . $username . '/';
    }
}
