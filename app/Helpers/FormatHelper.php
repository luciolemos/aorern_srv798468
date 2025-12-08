<?php

namespace App\Helpers;

class FormatHelper {

    public static function cpf($cpf) {
        $cpf = preg_replace('/\D/', '', $cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    public static function telefone($telefone) {
        $tel = preg_replace('/\D/', '', $telefone);
        return preg_replace('/(\d{2})(\d{1})(\d{4})(\d{4})/', '($1) $2-$3-$4', $tel);
    }

    public static function cep($cep) {
        $cep = preg_replace('/\D/', '', $cep);
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
    }

    public static function data($data, $formato = 'd/m/Y') {
        if (!$data) return '';
        return date($formato, strtotime($data));
    }
}
