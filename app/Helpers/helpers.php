<?php


if (!function_exists('formatOnlyNumber')) {
    function formatOnlyNumber($val){
        return trim(preg_replace('/[^0-9]/', '', $val));
    }
}

if (!function_exists('formatCurrencyValue')) {
    function formatCurrencyValue($val){
        return number_format($val, 2, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    function formatDate($val, $format = 'd/m/Y'){
        return date($format, strtotime($val));
    }
}

if (!function_exists('formatCrediCard')) {
    function formatCrediCard($oCreditCard){
        if (is_array($oCreditCard)) {
            $oCreditCard = (object)$oCreditCard;
        }
        if ($oCreditCard->creditCardBrand == 'UNKNOWN') {
            $brand = '';
        } else {
            $brand = $oCreditCard->creditCardBrand;
        }
        return "(...{$oCreditCard->creditCardNumber}) {$brand}";
    }
}
