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
        $result = $postModel->listarPublico($searchTerm !== '' ? $searchTerm : null, $categoryId, $page, $perPage);

        $queryParams = [];
        if ($searchTerm !== '') {
            $queryParams['q'] = $searchTerm;
        }
        if ($categoryId) {
            $queryParams['categoria'] = $categoryId;
        }

        $this->renderTwig('site/pages/blog', [
            'posts' => $result['data'],
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

        $previousPost = $postModel->encontrarAnterior($post['criado_em']);
        $nextPost = $postModel->encontrarProximo($post['criado_em']);

        $this->renderTwig('site/pages/post', [
            'post' => $post,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
        ]);
    }
}
