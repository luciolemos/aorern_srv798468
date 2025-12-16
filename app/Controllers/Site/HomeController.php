<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\PessoalModel;
use App\Models\LivroOcorrenciaModel;
use App\Models\LivroTipoModel;
use DateTimeImmutable;

class HomeController extends Controller {
    public function index() {
        $pessoalModel = new PessoalModel();
        $totalProfissionais = $pessoalModel->contar();

        $livroModel = new LivroOcorrenciaModel();
        $livroTipoModel = new LivroTipoModel();
        $subgrupamentoFiltro = "2\xC2\xBA SGB"; // ordinal indicator (\xC2\xBA) preserves ASCII while targeting the 2nd SGB scope
        $fechamentoFinal = new DateTimeImmutable('now');
        $anoCorrente = (int) $fechamentoFinal->format('Y');
        $fechamentoInicial = $fechamentoFinal
            ->setDate($anoCorrente, 1, 1)
            ->setTime(0, 0, 0);

        $ocorrenciasPorTipo = $livroModel->contarPorTipoPorFechamento(
            8,
            $fechamentoInicial->format('Y-m-d H:i:s'),
            $fechamentoFinal->format('Y-m-d H:i:s'),
            $subgrupamentoFiltro
        );

        $ocorrenciasPeriodoDescricao = sprintf('Fechamentos de %d', $anoCorrente);

        if (!$ocorrenciasPorTipo) {
            $ocorrenciasPorTipo = $livroModel->contarPorTipoPorFechamento(8, null, null, $subgrupamentoFiltro);
            $ocorrenciasPeriodoDescricao = 'Fechamentos registrados';
        }

        $mapYear = $anoCorrente;
        $mapYearOptions = [$anoCorrente, $anoCorrente - 1, $anoCorrente - 2];
        $mapTipoOptions = $livroTipoModel->listarAtivos();
        $mapStatusOptions = [
            'aberta' => 'Aberta',
            'concluida' => 'Concluída',
        ];

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
            'ocorrenciasPorTipo' => $ocorrenciasPorTipo,
            'ocorrenciasPeriodoDescricao' => $ocorrenciasPeriodoDescricao,
            'mapYear' => $mapYear,
            'mapYearOptions' => $mapYearOptions,
            'mapTipoOptions' => $mapTipoOptions,
            'mapStatusOptions' => $mapStatusOptions,
        ]);
    }
}
