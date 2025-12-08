<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Helpers\SystemVersions;

class SystemController extends Controller
{
    public function versions()
    {
        AuthMiddleware::requireAuth();
        
        $versions = SystemVersions::get();
        $this->renderTwig('admin/system/versions', array_merge(compact('versions'), AdminHelper::getUserData('system')));
    }

    public function info()
    {
        AuthMiddleware::requireAuth();
        $info = [
            'SO'         => php_uname(),
            'PHP Path'   => PHP_BINARY,
            'Root Path'  => $_SERVER['DOCUMENT_ROOT'],
            'Timezone'   => date_default_timezone_get(),
            'Memory'     => ini_get('memory_limit'),
        ];

        $this->renderTwig('admin/system/info', array_merge(compact('info'), AdminHelper::getUserData('system')));
    }
}
