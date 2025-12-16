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

        $previousPost = $postModel->encontrarAnterior($referenceDate, $postId);
        $nextPost = $postModel->encontrarProximo($referenceDate, $postId);

        $this->renderTwig('site/pages/post', [
            'post' => $post,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
        ]);
    }
}
