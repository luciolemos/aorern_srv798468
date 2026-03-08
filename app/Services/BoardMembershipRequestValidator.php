<?php

namespace App\Services;

use App\Core\Request;
use App\Models\BoardMembershipModel;
use App\Models\FuncaoModel;
use App\Models\PessoalModel;

class BoardMembershipRequestValidator
{
    private const ALLOWED_ACCESS_ROLES = ['operador', 'gerente'];

    /**
     * @return array{ok:bool,data?:array<string,int|string|null>,toast?:array{type:string,message:string}}
     */
    public function validate(
        Request $request,
        BoardMembershipModel $memberships,
        FuncaoModel $funcoes,
        PessoalModel $pessoal,
        ?int $excludeId = null
    ): array {
        $boardGroup = trim((string) $request->post('grupo', ''));
        $termId = $this->sanitizeInteger($request->post('term_id'));
        $pessoalId = $this->sanitizeInteger($request->post('pessoal_id'));
        $funcaoId = $this->sanitizeInteger($request->post('funcao_id'));
        $ordem = (int) $request->post('ordem', '0');
        $ativo = $request->post('is_active', '1') === '1' ? 1 : 0;
        $accessRole = $this->sanitizeAccessRole($request->post('access_role'));
        $notes = trim((string) $request->post('observacoes', ''));

        if (!$funcaoId) {
            return [
                'ok' => false,
                'toast' => ['type' => 'danger', 'message' => 'Selecione a função base.'],
            ];
        }

        $funcao = $funcoes->buscar($funcaoId);
        if (!$funcao || trim((string) ($funcao['nome'] ?? '')) === '') {
            return [
                'ok' => false,
                'toast' => ['type' => 'danger', 'message' => 'Função base inválida.'],
            ];
        }
        $cargo = trim((string) $funcao['nome']);

        if ($accessRole !== null) {
            if (!$pessoalId) {
                return [
                    'ok' => false,
                    'toast' => ['type' => 'danger', 'message' => 'Selecione um associado antes de conceder perfil administrativo.'],
                ];
            }

            $associado = $pessoal->buscar($pessoalId);
            if (!$associado) {
                return [
                    'ok' => false,
                    'toast' => ['type' => 'danger', 'message' => 'Associado não encontrado para concessão de acesso administrativo.'],
                ];
            }

            if (empty($associado['user_id'])) {
                return [
                    'ok' => false,
                    'toast' => ['type' => 'danger', 'message' => 'O associado selecionado ainda não possui credencial de acesso. Crie primeiro a credencial básica no cadastro do associado.'],
                ];
            }
        }

        if ($termId !== null && $memberships->existeFuncaoNoMandato($termId, $funcaoId, $excludeId)) {
            return [
                'ok' => false,
                'toast' => ['type' => 'danger', 'message' => 'Esta função base já está vinculada ao mandato selecionado.'],
            ];
        }

        return [
            'ok' => true,
            'data' => [
                'term_id' => $termId,
                'pessoal_id' => $pessoalId,
                'funcao_id' => $funcaoId,
                'cargo' => $cargo,
                'grupo' => $boardGroup !== '' ? $boardGroup : null,
                'ordem' => max(0, $ordem),
                'is_active' => $ativo,
                'access_role' => $accessRole,
                'observacoes' => $notes !== '' ? $notes : null,
            ],
        ];
    }

    private function sanitizeInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $string = (string) $value;
        return ctype_digit($string) ? (int) $string : null;
    }

    private function sanitizeAccessRole($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $role = (string) $value;
        return in_array($role, self::ALLOWED_ACCESS_ROLES, true) ? $role : null;
    }
}
