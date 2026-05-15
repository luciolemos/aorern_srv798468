<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\Post;
use App\Models\PostCategoryModel;

class BlogController extends Controller {
    public function index() {
        $postModel = new Post();
        $categoryModel = new PostCategoryModel();

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $searchTerm = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $categoryId = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
        if ($categoryId !== null && $categoryId <= 0) {
            $categoryId = null;
        }

        $perPage = 7;
        $filtersBusca = $searchTerm !== '' ? $searchTerm : null;
        $filtersActive = ($searchTerm !== '' || $categoryId !== null);
        $result = $postModel->listarPublico($filtersBusca, $categoryId, $page, $perPage);

        // Hero e painel devem sempre refletir a primeira página filtrada
        $heroResult = $postModel->listarPublico($filtersBusca, $categoryId, 1, $perPage);
        $featuredPost = $heroResult['data'][0] ?? null;
        $heroRecentPosts = array_slice($heroResult['data'], 0, 4);

        $cardPosts = $result['data'];
        if (!$filtersActive && $page === 1 && $featuredPost) {
            $cardPosts = array_values(array_filter($cardPosts, function ($post) use ($featuredPost) {
                return ($post['id'] ?? null) !== ($featuredPost['id'] ?? null);
            }));
        }

        $queryParams = [];
        if ($searchTerm !== '') {
            $queryParams['q'] = $searchTerm;
        }
        if ($categoryId) {
            $queryParams['categoria'] = $categoryId;
        }

        $this->renderTwig('site/pages/blog', [
            'card_posts' => $cardPosts,
            'featured_post' => $featuredPost,
            'hero_recent_posts' => $heroRecentPosts,
            'pagination' => $result['meta'],
            'categories' => $categoryModel->listar(),
            'filters' => [
                'q' => $searchTerm,
                'categoria' => $categoryId,
            ],
            'baseQueryString' => http_build_query($queryParams),
        ]);
    }

    public function post($slug = null) {
        if (!$slug) {
            $this->renderTwig('site/pages/404');
            return;
        }

        $postModel = new Post();
        $post = $postModel->encontrarPorSlug($slug);

        if (!$post) {
            $this->renderTwig('site/pages/404');
            return;
        }

        $referenceDate = $post['published_at'] ?? $post['criado_em'];
        $postId = (int) ($post['id'] ?? 0);
        $canonicalUrl = BASE_URL . 'blog/' . rawurlencode((string) ($post['slug'] ?? ''));

        $previousPost = $postModel->encontrarAnterior($referenceDate, $postId);
        $nextPost = $postModel->encontrarProximo($referenceDate, $postId);

        $this->renderTwig('site/pages/post', [
            'post' => $post,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'meta_canonical' => $canonicalUrl,
            'meta_og_url' => $canonicalUrl,
            'meta_og_type' => 'article',
            'meta_description' => $this->buildMetaDescription($post),
            'meta_og_title' => (string) ($post['titulo'] ?? 'AORE/RN'),
            'meta_og_description' => $this->buildMetaDescription($post),
            'meta_og_image' => $this->resolveAbsoluteUrl((string) ($post['capa_url'] ?? '')),
            'fb_app_id' => defined('FACEBOOK_APP_ID') ? FACEBOOK_APP_ID : '',
        ]);
    }

    private function buildMetaDescription(array $post): string
    {
        $source = trim(strip_tags((string) ($post['conteudo'] ?? '')));
        if ($source === '') {
            return 'Conteúdo institucional publicado no portal da AORE/RN.';
        }

        if (function_exists('mb_substr')) {
            $description = mb_substr($source, 0, 180, 'UTF-8');
            if (mb_strlen($source, 'UTF-8') > 180) {
                $description .= '...';
            }

            return $description;
        }

        $description = substr($source, 0, 180);
        if (strlen($source) > 180) {
            $description .= '...';
        }

        return $description;
    }

    private function resolveAbsoluteUrl(string $url): string
    {
        $value = trim($url);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return BASE_URL . ltrim($value, '/');
    }
}
