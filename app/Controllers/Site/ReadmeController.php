<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class ReadmeController extends Controller {
    public function index() {
        $this->renderTwig('readme');
    }
}
