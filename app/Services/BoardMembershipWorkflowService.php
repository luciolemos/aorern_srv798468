<?php

namespace App\Services;

use App\Models\BoardMembershipModel;
use App\Models\PessoalModel;
use App\Models\User;

class BoardMembershipWorkflowService
{
    public function __construct(
        private readonly BoardMembershipModel $memberships,
        private readonly PessoalModel $pessoal,
        private readonly User $users
    ) {
    }

    public function create(array $payload): void
    {
        $this->memberships->salvar($payload);

        if (!empty($payload['pessoal_id'])) {
            $this->syncAssociadoAdministrativeRole((int) $payload['pessoal_id']);
        }
    }

    public function update(int $id, array $payload): void
    {
        $before = $this->memberships->buscar($id);
        $beforePessoalId = !empty($before['pessoal_id']) ? (int) $before['pessoal_id'] : null;

        $this->memberships->atualizar($id, $payload);

        $afterPessoalId = !empty($payload['pessoal_id']) ? (int) $payload['pessoal_id'] : null;

        if ($beforePessoalId !== null) {
            $this->syncAssociadoAdministrativeRole($beforePessoalId);
        }
        if ($afterPessoalId !== null && $afterPessoalId !== $beforePessoalId) {
            $this->syncAssociadoAdministrativeRole($afterPessoalId);
        }
    }

    public function delete(array $registro): void
    {
        $this->memberships->deletar((int) $registro['id']);

        if (!empty($registro['pessoal_id'])) {
            $this->syncAssociadoAdministrativeRole((int) $registro['pessoal_id']);
        }
    }

    private function syncAssociadoAdministrativeRole(int $pessoalId): void
    {
        $associado = $this->pessoal->buscar($pessoalId);
        if (!$associado || empty($associado['user_id'])) {
            return;
        }

        $roles = $this->memberships->listarPerfisAtivosPorPessoal($pessoalId);
        $effectiveRole = $this->resolveEffectiveAdministrativeRole($roles);

        $this->users->atualizar((int) $associado['user_id'], [
            'role' => $effectiveRole,
            'ativo' => 1,
            'status' => 'ativo',
        ]);
    }

    private function resolveEffectiveAdministrativeRole(array $roles): string
    {
        if (in_array('gerente', $roles, true)) {
            return 'gerente';
        }

        if (in_array('operador', $roles, true)) {
            return 'operador';
        }

        return 'usuario';
    }
}
