<?php

namespace App\Services;

use App\Core\Database;
use App\Models\FuncaoModel;
use App\Models\MembershipApplicationModel;
use App\Models\PessoalModel;
use PDO;

class MembershipApplicationWorkflowService
{
    private const DEFAULT_ASSOCIADO_FUNCAO = 'Associado AORE/RN';

    public function __construct(
        private readonly MembershipApplicationModel $applications,
        private readonly PessoalModel $pessoal,
        private readonly FuncaoModel $funcoes,
        private readonly ?PDO $db = null
    ) {
    }

    /**
     * Aprova uma solicitação de filiação dentro de transação.
     *
     * @return array{pessoal_id:int,user_id:int|null}
     */
    public function approve(int $solicitacaoId, array $solicitacao, string $statusAssociativo, ?string $adminNote = null): array
    {
        $db = $this->db ?? Database::connect();

        try {
            $db->beginTransaction();

            $pessoalId = null;
            $associadoExistente = $this->pessoal->buscarPorCpf((string) ($solicitacao['cpf'] ?? ''));

            if ($associadoExistente) {
                $pessoalId = (int) $associadoExistente['id'];
                $this->pessoal->atualizarStatusAssociativo($pessoalId, $statusAssociativo);
                if (empty($associadoExistente['foto']) && !empty($solicitacao['avatar'])) {
                    $this->pessoal->atualizarFoto($pessoalId, (string) $solicitacao['avatar']);
                }
            } else {
                $funcaoId = $this->ensureAssociadoRoleId();
                $pessoalId = $this->pessoal->salvarERetornarId([
                    'staff_id' => $this->generateAssociadoStaffId($solicitacao),
                    'nome' => $solicitacao['nome_completo'] ?? '',
                    'cpf' => $solicitacao['cpf'] ?? '',
                    'nascimento' => $solicitacao['data_nascimento'] ?: null,
                    'telefone' => preg_replace('/\D/', '', (string) ($solicitacao['telefone'] ?? '')),
                    'foto' => $solicitacao['avatar'] ?: null,
                    'user_id' => $associadoExistente['user_id'] ?? null,
                    'funcao_id' => $funcaoId,
                    'obra_id' => null,
                    'data_admissao' => date('Y-m-d'),
                    'status' => 'Ativo',
                    'status_associativo' => $statusAssociativo,
                    'jornada' => null,
                    'observacoes' => $this->buildAssociadoNotes($solicitacao, $statusAssociativo),
                ]);

                if (!$pessoalId) {
                    throw new \RuntimeException('Falha ao criar o cadastro do associado.');
                }
            }

            $this->applications->atualizar($solicitacaoId, [
                'status' => 'aprovada',
                'status_associativo' => $statusAssociativo,
                'user_id' => $associadoExistente['user_id'] ?? null,
                'pessoal_id' => $pessoalId,
                'observacoes_admin' => $adminNote !== null && trim($adminNote) !== '' ? trim($adminNote) : null,
                'aprovado_em' => date('Y-m-d H:i:s'),
                'rejeitado_em' => null,
            ]);

            $db->commit();

            return [
                'pessoal_id' => (int) $pessoalId,
                'user_id' => isset($associadoExistente['user_id']) ? (int) $associadoExistente['user_id'] : null,
            ];
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    public function reject(int $solicitacaoId, ?string $adminNote = null): void
    {
        $note = $adminNote !== null && trim($adminNote) !== '' ? trim($adminNote) : null;

        $this->applications->atualizar($solicitacaoId, [
            'status' => 'rejeitada',
            'observacoes_admin' => $note,
            'rejeitado_em' => date('Y-m-d H:i:s'),
            'aprovado_em' => null,
        ]);
    }

    public function requestComplementation(int $solicitacaoId, string $adminNote): void
    {
        $note = trim($adminNote);
        if ($note === '') {
            throw new \InvalidArgumentException('Observação para complementação é obrigatória.');
        }

        $this->applications->atualizar($solicitacaoId, [
            'status' => 'complementacao',
            'observacoes_admin' => $note,
            'aprovado_em' => null,
            'rejeitado_em' => null,
        ]);
    }

    private function ensureAssociadoRoleId(): int
    {
        $targetName = self::DEFAULT_ASSOCIADO_FUNCAO;

        foreach ($this->funcoes->listar() as $funcao) {
            if ($this->mbLower((string) $funcao['nome']) === $this->mbLower($targetName)) {
                return (int) $funcao['id'];
            }
        }

        $this->funcoes->salvar([
            'staff_id' => method_exists($this->funcoes, 'gerarProximoStaffIdAore')
                ? $this->funcoes->gerarProximoStaffIdAore()
                : ('FUNC-' . date('YmdHis')),
            'nome' => $targetName,
        ]);

        foreach ($this->funcoes->listar() as $funcao) {
            if ($this->mbLower((string) $funcao['nome']) === $this->mbLower($targetName)) {
                return (int) $funcao['id'];
            }
        }

        foreach ($this->funcoes->listar() as $funcao) {
            if ($this->mbLower((string) $funcao['nome']) === 'associado') {
                return (int) $funcao['id'];
            }
        }

        throw new \RuntimeException('Falha ao provisionar a função base de associado (Associado AORE/RN).');
    }

    private function generateAssociadoStaffId(array $solicitacao): string
    {
        $ano = preg_replace('/\D/', '', (string) ($solicitacao['ano_npor'] ?? ''));
        $numero = preg_replace('/\D/', '', (string) ($solicitacao['numero_militar'] ?? ''));

        if (strlen($ano) !== 4 || strlen($numero) !== 2) {
            return 'ASSOC-' . date('YmdHis') . '-' . substr((string) mt_rand(), 0, 4);
        }

        $base = 'SE-' . $ano . $numero;
        $candidate = $base;
        $sequence = 2;

        while ($this->pessoal->buscarPorStaffId($candidate) !== null) {
            $candidate = $base . '-' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $sequence++;
        }

        return $candidate;
    }

    private function buildAssociadoNotes(array $solicitacao, string $statusAssociativo): string
    {
        $parts = [
            'Solicitação de filiação aprovada via portal institucional.',
            'Status associativo inicial: ' . $this->formatAssociativeStatus($statusAssociativo) . '.',
            'NPOR: ' . (($solicitacao['ano_npor'] ?? '') !== '' ? $solicitacao['ano_npor'] : 'não informado'),
        ];

        if (!empty($solicitacao['turma_npor'])) {
            $parts[] = 'Turma: ' . $solicitacao['turma_npor'];
        }

        if (!empty($solicitacao['arma_quadro'])) {
            $parts[] = 'Arma/Quadro: ' . $solicitacao['arma_quadro'];
        }

        $location = $this->formatLocationLabel($solicitacao['cidade'] ?? null, $solicitacao['uf'] ?? null);
        if ($location !== '-') {
            $parts[] = 'Cidade: ' . $location;
        }

        if (!empty($solicitacao['cam'])) {
            $parts[] = 'CAM: ' . $solicitacao['cam'];
        }

        if (!empty($solicitacao['rg'])) {
            $parts[] = 'RG: ' . $solicitacao['rg'];
        }

        if (!empty($solicitacao['nome_mae'])) {
            $parts[] = 'Nome da mãe: ' . $solicitacao['nome_mae'];
        }

        if (!empty($solicitacao['nome_pai'])) {
            $parts[] = 'Nome do pai: ' . $solicitacao['nome_pai'];
        }

        if (!empty($solicitacao['posto_graduacao'])) {
            $parts[] = 'Posto/Graduação: ' . $solicitacao['posto_graduacao'];
        }

        if (!empty($solicitacao['numero_militar'])) {
            $parts[] = 'Nº: ' . $solicitacao['numero_militar'];
        }

        if (!empty($solicitacao['nome_guerra'])) {
            $parts[] = 'Nome de guerra: ' . $solicitacao['nome_guerra'];
        }

        if (!empty($solicitacao['observacoes'])) {
            $parts[] = 'Observações do solicitante: ' . $solicitacao['observacoes'];
        }

        return implode("\n", $parts);
    }

    private function formatAssociativeStatus(string $status): string
    {
        return match ($status) {
            'efetivo' => 'Sócio Efetivo',
            'honorario' => 'Sócio Honorário',
            'fundador' => 'Sócio Fundador',
            'benemerito' => 'Sócio Benemérito',
            'veterano' => 'Sócio Veterano',
            'aluno' => 'Sócio Aluno',
            default => 'Sócio Provisório',
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

    private function mbLower(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }
}
