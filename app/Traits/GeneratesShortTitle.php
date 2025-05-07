<?php

namespace App\Traits;

trait GeneratesShortTitle
{
    public function generateShortTitle($fullTitle, $brandName)
    {
        $cleanTitle = trim($fullTitle);
        $brand = trim($brandName);

        if (stripos($cleanTitle, $brand) === 0) {
            $words = explode(' ', $cleanTitle);
            return implode(' ', array_slice($words, 0, 3));
        }

        if (stripos($cleanTitle, $brand) !== false) {
            $start = stripos($cleanTitle, $brand);
            $afterBrand = substr($cleanTitle, $start);
            $words = explode(' ', $afterBrand);
            return implode(' ', array_slice($words, 0, 3));
        }

        return implode(' ', array_slice(explode(' ', $cleanTitle), 0, 3));
    }
}
