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

    // PUBLICATION
    // Helper function to get searchable fields based on publication category
    private function getSearchableFields(string $category): array
    {
        switch ($category) {
            case 'google':
                return ['title', 'journal', 'creators'];
            case 'scopus':
                return ['identifier', 'title', 'publication_name', 'creators'];
            default:
                return ['title', 'creators'];
        }
    }

    // PUBLICATION
    // Helper function to get selectable fields based on publication publication category
    private function getSelectableFields(string $category): array
    {
        if ($category === 'google') {
            return ['id', 'accreditation', 'title', 'journal', 'year', 'citation', 'category'];
        }

        return ['id', 'identifier', 'quartile', 'title', 'publication_name', 'year', 'citation', 'category'];
    }
}