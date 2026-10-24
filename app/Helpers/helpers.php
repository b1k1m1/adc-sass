<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


if (!function_exists('amount_in_words')) {
    function amount_in_words($number) {
        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $decimal = round(($number - floor($number)) * 100);
        $in_words = ucfirst($f->format(floor($number))) . ' and ' . str_pad($decimal, 2, '0', STR_PAD_LEFT) . '/100';
        return strtoupper($in_words); // Make all UPPERCASE
    }
}

if (!function_exists('t')) {
    function t(string $key, ?string $default = null): string
    {
        $locale = app()->getLocale();
        $default = $default ?? $key;

        // English is your "default language": show default directly
        if ($locale === 'en') {
            return $default;
        }

        $map = Cache::remember("tr_map_$locale", 3600, function () use ($locale) {
            return DB::table('translations')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
        });

        $val = $map[$key] ?? null;

        return ($val !== null && $val !== '') ? $val : $default;
    }
}

?>
