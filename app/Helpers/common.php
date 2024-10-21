<?php

if (!function_exists('format_currency')) {
    function format_currency($number)
    {
        return number_format($number, 0, '.', ',') . ' VND';
    }
}

if (!function_exists('remove_format')) {
    function remove_format($formattedNumber)
    {
        return preg_replace('/[^\d]/', '', $formattedNumber);
    }
}
