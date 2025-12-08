<?php

namespace App\Helpers;

class SystemVersions
{
    public static function get(): array
    {
        return [
            'php'        => phpversion(),
            'mysql'      => (new \mysqli(DB_HOST, DB_USER, DB_PASS))->server_info ?? '—',
            'composer'   => trim(shell_exec('composer --version 2>/dev/null')) ?: '—',
            'node'       => trim(shell_exec('node -v 2>/dev/null')) ?: '—',
            'npm'        => trim(shell_exec('npm -v 2>/dev/null')) ?: '—',
            'git'        => trim(shell_exec('git --version 2>/dev/null')) ?: '—',
            'git cli'    => strtok(trim(shell_exec('gh --version 2>/dev/null')), "\n") ?: '—',
            'apache'     => trim(shell_exec('apache2 -v | grep "Server version" 2>/dev/null')) ?: '—',
            'xdebug'     => phpversion('xdebug') ?: 'Não instalado',
            'ubuntu'     => self::getUbuntuVersion(),
        ];
    }

    private static function getUbuntuVersion(): string
    {
        if (file_exists('/etc/os-release')) {
            $content = file_get_contents('/etc/os-release');
            if (preg_match('/PRETTY_NAME="(.+?)"/', $content, $matches)) {
                return $matches[1];
            }
        }
        return '—';
    }
}
