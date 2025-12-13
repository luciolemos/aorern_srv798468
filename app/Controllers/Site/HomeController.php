<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\PessoalModel;
use App\Models\LivroOcorrenciaModel;

class HomeController extends Controller {
    public function index() {
        $pessoalModel = new PessoalModel();
        $totalProfissionais = $pessoalModel->contar();

        $livroModel = new LivroOcorrenciaModel();
        $ocorrenciasPorTipo = $livroModel->contarPorTipo(8, 'concluida');

        $metaTexts = [
            'Busca e Salvamento' => 'Equipes mobilizadas para localizar e extrair vítimas.',
            'Resgate Marítimo' => 'Chamados envolvendo litoral, rios e operações embarcadas.',
            'Resgate Maritimo' => 'Chamados envolvendo litoral, rios e operações embarcadas.',
            'Incêndio Urbano' => 'Atendimentos com combate a fogo em áreas habitadas.',
            'Incêndio Florestal' => 'Ocorrências em vegetação ou áreas de plantio.',
            'Defesa Civil' => 'Ações de apoio preventivo e resposta a desastres.',
            'Prevenção' => 'Fiscalizações e vistorias técnicas em andamento.'
        ];

        $ocorrenciasPorTipo = array_map(function (array $item) use ($metaTexts) {
            $tipo = $item['tipo'] ?? 'Não classificada';
            $tipoLower = function_exists('mb_strtolower') ? mb_strtolower($tipo, 'UTF-8') : strtolower($tipo);
            $meta = $metaTexts[$tipo] ?? ('Atendimentos relacionados a ' . $tipoLower);
            $item['meta'] = $meta;
            return $item;
        }, $ocorrenciasPorTipo);
        
        $this->renderTwig('site/pages/home', [
            'totalProfissionais' => $totalProfissionais,
            'ocorrenciasPorTipo' => $ocorrenciasPorTipo
        ]);
    }
}
