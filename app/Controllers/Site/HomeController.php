<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\InstitutionalDocumentModel;
use App\Models\PatrocinadorModel;
use App\Models\PessoalModel;

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

        $aniversariantes = array_map(
            fn(array $item): array => $this->mapBirthdayMember($item),
            (new PessoalModel())->listarAniversariantesDoDia(date('m-d'), 8)
        );

        $this->renderTwig('site/pages/home', [
            'official_documents' => $documents,
            'colaboradores' => $colaboradores,
            'aniversariantes_hoje' => $aniversariantes,
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
            return INSTITUTIONAL_INSTAGRAM_URL !== '' ? INSTITUTIONAL_INSTAGRAM_URL : 'https://www.instagram.com/aore.rn/';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $username = ltrim($value, '@');
        $username = preg_replace('/[^a-zA-Z0-9._]/', '', $username) ?? '';
        if ($username === '') {
            return INSTITUTIONAL_INSTAGRAM_URL !== '' ? INSTITUTIONAL_INSTAGRAM_URL : 'https://www.instagram.com/aore.rn/';
        }

        return 'https://www.instagram.com/' . $username . '/';
    }

    private function mapBirthdayMember(array $item): array
    {
        $birthDate = trim((string) ($item['nascimento'] ?? ''));
        $age = null;
        if ($birthDate !== '') {
            try {
                $born = new \DateTimeImmutable($birthDate);
                $age = $born->diff(new \DateTimeImmutable('today'))->y;
            } catch (\Throwable $exception) {
                $age = null;
            }
        }

        $avatarPath = trim((string) ($item['foto'] ?? '')) ?: trim((string) ($item['user_avatar'] ?? ''));
        $location = trim((string) ($item['cidade'] ?? ''));
        $uf = strtoupper(trim((string) ($item['uf'] ?? '')));

        return [
            'nome' => (string) ($item['nome'] ?? ''),
            'nome_guerra' => trim((string) ($item['nome_guerra'] ?? '')) ?: 'Associado',
            'numero_militar' => trim((string) ($item['numero_militar'] ?? '')) ?: '-',
            'ano_npor' => trim((string) ($item['ano_npor'] ?? '')) ?: '-',
            'idade' => $age,
            'foto_url' => $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png'),
            'localidade' => $location !== '' ? $location . ($uf !== '' ? '/' . $uf : '') : ($uf !== '' ? $uf : ''),
        ];
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
