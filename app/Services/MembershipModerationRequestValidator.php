<?php

namespace App\Services;

use App\Core\Request;

class MembershipModerationRequestValidator
{
    private const ALLOWED_ASSOCIATIVE_STATUSES = [
        'provisorio',
        'efetivo',
        'honorario',
        'fundador',
        'benemerito',
        'veterano',
        'aluno',
    ];

    /**
     * @return array{ok:bool,data?:array<string,string|null>,toast?:array{type:string,message:string}}
     */
    public function validateApproval(Request $request, MembershipActionMessageService $messages): array
    {
        $statusAssociativo = trim((string) $request->post('status_associativo', 'provisorio'));
        $adminNote = trim((string) $request->post('observacoes_admin', ''));

        if (!in_array($statusAssociativo, self::ALLOWED_ASSOCIATIVE_STATUSES, true)) {
            return [
                'ok' => false,
                'toast' => $messages->invalidAssociativeStatus(),
            ];
        }

        return [
            'ok' => true,
            'data' => [
                'status_associativo' => $statusAssociativo,
                'observacoes_admin' => $adminNote !== '' ? $adminNote : null,
            ],
        ];
    }

    /**
     * @return array{ok:bool,data:array{observacoes_admin:string|null}}
     */
    public function validateRejection(Request $request): array
    {
        $adminNote = trim((string) $request->post('observacoes_admin', ''));

        return [
            'ok' => true,
            'data' => [
                'observacoes_admin' => $adminNote !== '' ? $adminNote : null,
            ],
        ];
    }

    /**
     * @return array{ok:bool,data?:array{observacoes_admin:string},toast?:array{type:string,message:string}}
     */
    public function validateComplementation(Request $request, MembershipActionMessageService $messages): array
    {
        $adminNote = trim((string) $request->post('observacoes_admin', ''));
        if ($adminNote === '') {
            return [
                'ok' => false,
                'toast' => $messages->missingComplementationNote(),
            ];
        }

        return [
            'ok' => true,
            'data' => [
                'observacoes_admin' => $adminNote,
            ],
        ];
    }
}

