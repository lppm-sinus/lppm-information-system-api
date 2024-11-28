<?php

namespace App\Traits;

use Illuminate\Support\Number;

trait FunctionalMethod
{

    // Currency Format
    public function currencyFormat($number)
    {
        return Number::currency($number, 'IDR', 'id');
    }

    // Helper function to generate random colors
    private function generateColors($count)
    {
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }
        return $colors;
    }
}