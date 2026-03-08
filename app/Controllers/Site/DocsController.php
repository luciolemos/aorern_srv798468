<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;

class DocsController extends Controller
{
    private string $projectRoot;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->projectRoot = rtrim(dirname(__DIR__, 3), '/');
    }

    public function index(): void
    {
        $documents = $this->buildDocumentIndex();
        $selected = $documents[0] ?? null;

        $this->renderTwig('site/pages/docs', [
            'documents' => $documents,
            'selectedDoc' => $selected,
            'selectedDocContent' => $selected ? $this->readDocument($selected['relative_path']) : '',
        ]);
    }

    public function doc(string $slug = ''): void
    {
        $documents = $this->buildDocumentIndex();
        $selected = null;

        foreach ($documents as $document) {
            if ($document['slug'] === $slug) {
                $selected = $document;
                break;
            }
        }

        if (!$selected) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'message' => 'Documento não encontrado no índice da documentação.',
            ];
            header('Location: ' . BASE_URL . 'docs');
            exit;
        }

        $this->renderTwig('site/pages/docs', [
            'documents' => $documents,
            'selectedDoc' => $selected,
            'selectedDocContent' => $this->readDocument($selected['relative_path']),
        ]);
    }

    private function buildDocumentIndex(): array
    {
        $files = array_merge(
            glob($this->projectRoot . '/*.md') ?: [],
            glob($this->projectRoot . '/sql/*.md') ?: []
        );

        $documents = [];
        $usedSlugs = [];

        foreach ($files as $path) {
            $real = realpath($path);
            if ($real === false || !str_starts_with($real, $this->projectRoot . '/')) {
                continue;
            }

            $relative = ltrim(str_replace($this->projectRoot, '', $real), '/');
            $slugBase = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($relative)), '-');
            $slug = $slugBase;
            $index = 2;
            while (isset($usedSlugs[$slug])) {
                $slug = $slugBase . '-' . $index;
                $index++;
            }
            $usedSlugs[$slug] = true;

            $folder = dirname($relative);
            $category = $folder === '.' ? 'Projeto' : strtoupper($folder);
            $title = $this->humanizeTitle(pathinfo($relative, PATHINFO_FILENAME));

            $documents[] = [
                'slug' => $slug,
                'title' => $title,
                'category' => $category,
                'relative_path' => $relative,
            ];
        }

        usort($documents, static function (array $a, array $b): int {
            if ($a['category'] === $b['category']) {
                return strcmp($a['title'], $b['title']);
            }

            return strcmp($a['category'], $b['category']);
        });

        return $documents;
    }

    private function readDocument(string $relativePath): string
    {
        $path = $this->projectRoot . '/' . ltrim($relativePath, '/');
        $real = realpath($path);

        if ($real === false || !str_starts_with($real, $this->projectRoot . '/') || !is_file($real)) {
            return 'Documento indisponível.';
        }

        $content = file_get_contents($real);
        if ($content === false) {
            return 'Não foi possível carregar o conteúdo do documento.';
        }

        return $content;
    }

    private function humanizeTitle(string $baseName): string
    {
        $title = str_replace(['_', '-'], ' ', $baseName);
        $title = preg_replace('/\s+/', ' ', (string) $title) ?? $baseName;
        return ucwords(trim($title));
    }
}
