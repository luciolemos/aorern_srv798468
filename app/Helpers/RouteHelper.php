<?php

namespace App\Helpers;

class RouteHelper
{
    public static function currentSegments(): array
    {
        $url = $_GET['url'] ?? 'admin/dashboard';
        $segments = explode('/', trim($url, '/'));

        $mainRoute = $segments[0] ?? '';
        $midRoute  = $segments[1] ?? '';
        $subRoute  = $segments[2] ?? $midRoute;

        // 🌐 Site público
        if ($mainRoute !== 'admin') {
            $controllerName = ucfirst($mainRoute) . 'Controller';
            $fullClass = "App\\Controllers\\Site\\$controllerName";
            $method = $segments[1] ?? 'index';
        }

        // 🔐 Admin (com suporte a subdiretórios como tools/)
        else {
            $ctrlPart = $segments[1] ?? 'dashboard'; // ex: tools, posts, docs
            $method = $segments[2] ?? 'index';

            $subSegments = explode('/', $ctrlPart); // caso seja algo como "tools/equipamentos"
            $namespaceParts = array_map('ucfirst', explode('/', $ctrlPart));
            $controllerBase = ucfirst(array_pop($namespaceParts)) . 'Controller';
            $namespace = implode('\\', $namespaceParts);

            $fullClass = "App\\Controllers\\Admin";
            if (!empty($namespace)) {
                $fullClass .= "\\$namespace";
            }
            $fullClass .= "\\$controllerBase";
        }

        return [
            'mainRoute'     => $mainRoute,
            'midRoute'      => $midRoute,
            'subRoute'      => $subRoute,
            'controller'    => $controllerBase ?? $controllerName ?? null,
            'controllerFQCN'=> $fullClass ?? null,
            'method'        => $method,
        ];
    }
}
