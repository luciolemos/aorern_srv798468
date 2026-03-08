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
use App\Services\MembershipApplicationWorkflowService;
use App\Services\MembershipModerationRequestValidator;
use App\Services\MembershipNotificationService;
use App\Services\MembershipPresentationService;

class SolicitacoesFiliacaoController extends Controller
{
    private MembershipApplicationModel $applications;
    private MembershipApplicationWorkflowService $workflow;
    private MembershipNotificationService $notifications;
    private MembershipPresentationService $presentation;
    private MembershipActionMessageService $messages;
    private MembershipModerationRequestValidator $validator;

    public function __construct()
    {
        $this->applications = new MembershipApplicationModel();
        $this->workflow = new MembershipApplicationWorkflowService(
            $this->applications,
            new PessoalModel(),
            new FuncaoModel()
        );
        $this->notifications = new MembershipNotificationService();
        $this->presentation = new MembershipPresentationService();
        $this->messages = new MembershipActionMessageService();
        $this->validator = new MembershipModerationRequestValidator();
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
        if (!$solicitacao || !in_array(($solicitacao['status'] ?? null), ['pendente', 'complementacao'], true)) {
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
        if (!$solicitacao || !in_array(($solicitacao['status'] ?? null), ['pendente', 'complementacao'], true)) {
            $_SESSION['toast'] = $this->messages->requestNotFoundOrProcessed();
            header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
            exit;
        }

        $request = Request::capture();
        $validation = $this->validator->validateRejection($request);
        $adminNote = $validation['data']['observacoes_admin'];
        $this->workflow->reject($id, $adminNote);

        [$emailEnviado, $emailErro] = $this->notifications->sendRejection($solicitacao, $adminNote ?? '');
        $this->registrarStatusNotificacao($id, 'rejeicao', $emailEnviado, $emailErro);

        $_SESSION['toast'] = $this->messages->rejectionResult($emailEnviado);
        header('Location: ' . BASE_URL . 'admin/solicitacoes-filiacao');
        exit;
    }

    public function solicitarComplementacao(int $id): void
    {
        PermissionMiddleware::authorize('users:approve');

        $solicitacao = $this->applications->buscar($id);
        if (!$solicitacao || !in_array(($solicitacao['status'] ?? null), ['pendente', 'complementacao'], true)) {
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
        $this->workflow->requestComplementation($id, $adminNote);

        [$emailEnviado, $emailErro] = $this->notifications->sendComplementation($solicitacao, $adminNote);
        $this->registrarStatusNotificacao($id, 'complementacao', $emailEnviado, $emailErro);

        $_SESSION['toast'] = $this->messages->complementationResult($emailEnviado);
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

}
