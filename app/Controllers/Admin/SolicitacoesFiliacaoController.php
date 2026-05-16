<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\FuncaoModel;
use App\Models\MembershipApplicationModel;
use App\Models\PessoalModel;
use App\Services\MembershipActionMessageService;
use App\Services\MembershipAssociativeStatusStateMachine;
use App\Services\MembershipApplicationWorkflowService;
use App\Services\MembershipModerationRequestValidator;
use App\Services\MembershipNotificationService;
use App\Services\MembershipPresentationService;
use App\Services\MembershipStatusStateMachine;

class SolicitacoesFiliacaoController extends Controller
{
    private MembershipApplicationModel $applications;
    private PessoalModel $pessoal;
    private MembershipApplicationWorkflowService $workflow;
    private MembershipNotificationService $notifications;
    private MembershipPresentationService $presentation;
    private MembershipActionMessageService $messages;
    private MembershipModerationRequestValidator $validator;
    private MembershipStatusStateMachine $statusStateMachine;
    private MembershipAssociativeStatusStateMachine $associativeStatusStateMachine;

    public function __construct()
    {
        $this->applications = new MembershipApplicationModel();
        $this->pessoal = new PessoalModel();
        $this->workflow = new MembershipApplicationWorkflowService(
            $this->applications,
            $this->pessoal,
            new FuncaoModel()
        );
        $this->notifications = new MembershipNotificationService();
        $this->presentation = new MembershipPresentationService();
        $this->messages = new MembershipActionMessageService();
        $this->validator = new MembershipModerationRequestValidator();
        $this->statusStateMachine = new MembershipStatusStateMachine();
        $this->associativeStatusStateMachine = new MembershipAssociativeStatusStateMachine();
        AuthMiddleware::requireAuth();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('users:list');

        $request = Request::capture();
        $page = max(1, (int) $request->query('page', 1));
        $q = trim($request->query('q', ''));
        $status = trim($request->query('status', ''));
        $statusAssociativo = trim($request->query('status_associativo', ''));
        $uf = strtoupper(trim($request->query('uf', '')));
        $cidade = trim($request->query('cidade', ''));

        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $result = $this->applications->paginar($page, $perPage, [
            'q' => $q !== '' ? $q : null,
            'status' => $status !== '' ? $status : null,
            'status_associativo' => $statusAssociativo !== '' ? $statusAssociativo : null,
            'uf' => $uf !== '' ? $uf : null,
            'cidade' => $cidade !== '' ? $cidade : null,
        ]);

        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/solicitacoes-filiacao',
            'query' => array_filter([
                'q' => $q,
                'status' => $status,
                'status_associativo' => $statusAssociativo,
                'uf' => $uf,
                'cidade' => $cidade,
                'per_page' => $perPageQueryValue,
            ], static fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/solicitacoes_filiacao/index', array_merge(
            AdminHelper::getUserData('solicitacoes-filiacao'),
            [
                'solicitacoes' => array_map(function (array $solicitacao): array {
                    $solicitacao = $this->syncAssociativeStatusFromPessoal($solicitacao);
                    return $this->presentation->enrichForList($solicitacao);
                }, $result['data']),
                'pagination' => $pagination,
                'q' => $q,
                'status' => $status,
                'statusAssociativo' => $statusAssociativo,
                'uf' => $uf,
                'cidade' => $cidade,
                'ufs' => $this->applications->listarUfs(),
                'cidades' => $this->applications->listarCidades(),
                'perPageOptions' => PaginationHelper::options($defaultPerPage),
                'perPageSelection' => $perPageSelection,
            ]
        ));
    }

    public function aprovar(int $id): void
    {
        PermissionMiddleware::authorize('users:approve');

        $solicitacao = $this->applications->buscar($id);
        if (
            !$solicitacao
            || !$this->statusStateMachine->canTransition(
                (string) ($solicitacao['status'] ?? ''),
                MembershipStatusStateMachine::STATUS_APROVADA
            )
        ) {
            $_SESSION['toast'] = $this->messages->requestNotFoundOrProcessed();
            header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
            exit;
        }

        $request = Request::capture();
        $validation = $this->validator->validateApproval($request, $this->messages);
        if (!$validation['ok']) {
            $_SESSION['toast'] = $validation['toast'];
            header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
            exit;
        }

        $adminNote = $validation['data']['observacoes_admin'];
        $statusAssociativo = $validation['data']['status_associativo'];
        $statusAssociativoAtual = (string) ($solicitacao['status_associativo'] ?? MembershipAssociativeStatusStateMachine::PROVISORIO);

        if (!$this->associativeStatusStateMachine->canTransition($statusAssociativoAtual, $statusAssociativo)) {
            $_SESSION['toast'] = $this->messages->invalidAssociativeStatusTransition($statusAssociativoAtual, $statusAssociativo);
            header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
            exit;
        }

        try {
            $this->workflow->approve($id, $solicitacao, $statusAssociativo, $adminNote ?? null);
            [$emailEnviado, $emailErro] = $this->notifications->sendApproval($solicitacao, $statusAssociativo);
            $this->registrarStatusNotificacao($id, 'aprovacao', $emailEnviado, $emailErro);

            $_SESSION['toast'] = $this->messages->approvalResult($emailEnviado);
        } catch (\Throwable $exception) {
            error_log('Erro ao aprovar solicitação de filiação: ' . $exception->getMessage());
            $_SESSION['toast'] = $this->messages->approvalFailure();
        }

        header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
        exit;
    }

    public function rejeitar(int $id): void
    {
        PermissionMiddleware::authorize('users:approve');

        $solicitacao = $this->applications->buscar($id);
        if (
            !$solicitacao
            || !$this->statusStateMachine->canTransition(
                (string) ($solicitacao['status'] ?? ''),
                MembershipStatusStateMachine::STATUS_REJEITADA
            )
        ) {
            $_SESSION['toast'] = $this->messages->requestNotFoundOrProcessed();
            header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
            exit;
        }

        $request = Request::capture();
        $validation = $this->validator->validateRejection($request);
        $adminNote = $validation['data']['observacoes_admin'];
        try {
            $this->workflow->reject($id, $adminNote, (string) ($solicitacao['status'] ?? ''));

            [$emailEnviado, $emailErro] = $this->notifications->sendRejection($solicitacao, $adminNote ?? '');
            $this->registrarStatusNotificacao($id, 'rejeicao', $emailEnviado, $emailErro);

            $_SESSION['toast'] = $this->messages->rejectionResult($emailEnviado);
        } catch (\Throwable $exception) {
            error_log('Erro ao rejeitar solicitação de filiação: ' . $exception->getMessage());
            $_SESSION['toast'] = $this->messages->rejectionFailure();
        }

        header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
        exit;
    }

    public function solicitarComplementacao(int $id): void
    {
        PermissionMiddleware::authorize('users:approve');

        $solicitacao = $this->applications->buscar($id);
        if (
            !$solicitacao
            || !$this->statusStateMachine->canTransition(
                (string) ($solicitacao['status'] ?? ''),
                MembershipStatusStateMachine::STATUS_COMPLEMENTACAO
            )
        ) {
            $_SESSION['toast'] = $this->messages->requestNotAvailableForComplementation();
            header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
            exit;
        }

        $request = Request::capture();
        $validation = $this->validator->validateComplementation($request, $this->messages);
        if (!$validation['ok']) {
            $_SESSION['toast'] = $validation['toast'];
            header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
            exit;
        }

        $adminNote = $validation['data']['observacoes_admin'];
        try {
            $this->workflow->requestComplementationFromStatus($id, $adminNote, (string) ($solicitacao['status'] ?? ''));

            [$emailEnviado, $emailErro] = $this->notifications->sendComplementation($solicitacao, $adminNote);
            $this->registrarStatusNotificacao($id, 'complementacao', $emailEnviado, $emailErro);

            $_SESSION['toast'] = $this->messages->complementationResult($emailEnviado);
        } catch (\Throwable $exception) {
            error_log('Erro ao solicitar complementação de filiação: ' . $exception->getMessage());
            $_SESSION['toast'] = $this->messages->complementationFailure();
        }

        header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
        exit;
    }

    private function registrarStatusNotificacao(int $id, string $tipo, bool $enviado, ?string $erro = null): void
    {
        $this->applications->atualizar($id, [
            'last_notification_type' => $tipo,
            'last_notification_status' => $enviado ? 'sent' : 'failed',
            'last_notification_at' => date('Y-m-d H:i:s'),
            'last_notification_error' => $erro,
        ]);
    }

    private function syncAssociativeStatusFromPessoal(array $solicitacao): array
    {
        $statusAtual = trim((string) ($solicitacao['status_associativo'] ?? ''));
        $statusSolicitacao = trim((string) ($solicitacao['status'] ?? ''));
        $pessoal = null;

        // Regra de negócio: enquanto a solicitação não estiver aprovada,
        // o status associativo exibido e persistido deve permanecer provisório.
        if (in_array($statusSolicitacao, [
            MembershipStatusStateMachine::STATUS_PENDENTE,
            MembershipStatusStateMachine::STATUS_COMPLEMENTACAO,
        ], true)) {
            if ($statusAtual !== MembershipAssociativeStatusStateMachine::PROVISORIO && !empty($solicitacao['id'])) {
                $this->applications->atualizar((int) $solicitacao['id'], [
                    'status_associativo' => MembershipAssociativeStatusStateMachine::PROVISORIO,
                ]);
            }
            $solicitacao['status_associativo'] = MembershipAssociativeStatusStateMachine::PROVISORIO;
            return $solicitacao;
        }

        $pessoalId = (int) ($solicitacao['pessoal_id'] ?? 0);
        if ($pessoalId > 0) {
            $pessoal = $this->pessoal->buscar($pessoalId);
        } elseif (!empty($solicitacao['cpf'])) {
            $pessoal = $this->pessoal->buscarPorCpf((string) $solicitacao['cpf']);
        }

        $statusPessoal = trim((string) ($pessoal['status_associativo'] ?? ''));
        if ($statusPessoal === '') {
            return $solicitacao;
        }

        // Solicitações já aprovadas devem refletir o status associativo oficial do cadastro pessoal.
        if ($statusSolicitacao === MembershipStatusStateMachine::STATUS_APROVADA) {
            if ($statusAtual !== $statusPessoal && !empty($solicitacao['id'])) {
                $this->applications->atualizar((int) $solicitacao['id'], [
                    'status_associativo' => $statusPessoal,
                ]);
            }
            $solicitacao['status_associativo'] = $statusPessoal;
        }

        return $solicitacao;
    }

}
