<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class AboutController extends Controller {
    public function index() {
        $this->renderTwig('site/pages/about');
    }
}
