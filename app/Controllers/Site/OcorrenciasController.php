<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\MunicipioGeoHelper;
use App\Models\LivroOcorrenciaModel;

class OcorrenciasController extends Controller
{
    private const STATUS_ALLOWED = ['aberta', 'concluida'];
    private const SUBGRUPAMENTO_FOCO = '2º SGB';
    private LivroOcorrenciaModel $livro;

    public function __construct()
    {
        $this->livro = new LivroOcorrenciaModel();
    }

    public function mapaMunicipios(): void
    {
        $request = Request::capture();
        $yearParam = (int) $request->query('year', date('Y'));
        $year = $this->sanitizeYear($yearParam);

        $tipoId = $this->sanitizeTipo($request->query('tipo'));
        $status = $this->sanitizeStatus($request->query('status'));

        $periodoInicial = sprintf('%d-01-01 00:00:00', $year);
        $periodoFinal = sprintf('%d-12-31 23:59:59', $year);

        $totais = $this->livro->contarTotaisPorMunicipio(
            $periodoInicial,
            $periodoFinal,
            $tipoId,
            $status,
            self::SUBGRUPAMENTO_FOCO
        );

        $items = array_map(static function (array $row) {
            $codigo = isset($row['municipio_codigo']) ? (int) $row['municipio_codigo'] : null;
            $geo = MunicipioGeoHelper::get($codigo);

            return [
                'codigo' => $codigo,
                'municipio' => $row['municipio_nome'] ?? 'Município não informado',
                'total' => isset($row['total']) ? (int) $row['total'] : 0,
                'lat' => $geo['lat'] ?? null,
                'lng' => $geo['lng'] ?? null,
            ];
        }, $totais);

        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=300');

        echo json_encode([
            'year' => $year,
            'count' => count($items),
            'filters' => [
                'tipo' => $tipoId,
                'status' => $status,
            ],
            'items' => $items,
        ]);
        exit;
    }

    private function sanitizeYear(int $year): int
    {
        $current = (int) date('Y');
        if ($year < 2015 || $year > $current + 1) {
            return $current;
        }

        return $year;
    }

    private function sanitizeTipo($value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        $tipo = (int) $value;
        return $tipo > 0 ? $tipo : null;
    }

    private function sanitizeStatus($value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return in_array($value, self::STATUS_ALLOWED, true) ? $value : null;
    }
}
