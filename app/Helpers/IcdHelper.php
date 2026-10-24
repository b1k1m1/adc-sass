<?php
namespace App\Helpers;
use Illuminate\Support\Facades\DB;

class IcdHelper
{
    public static function normalizeIcd10(?string $code): array
    {
        if (!$code) {
            return [
                'raw'         => null,
                'normalized'  => null,
                'valid'       => false,
                'description' => null,
            ];
        }

        $raw = trim($code);

        // 1. Uppercase
        $normalized = strtoupper($raw);

        // 2. Remove dots, dashes, spaces, slashes, unicode spaces
        $normalized = str_replace(
            [' ', '.', '-', '/', "\u{00A0}", "\u{2002}", "\u{2003}", "\u{2009}"],
            '',
            $normalized
        );

        // 3. If empty → invalid
        if ($normalized === '') {
            return [
                'raw'         => $raw,
                'normalized'  => null,
                'valid'       => false,
                'description' => null,
            ];
        }

        // 4. Validate against icd10_codes table
        $row = DB::table('icd10_codes')
            ->where('code', $normalized)
            ->first();

        if ($row) {
            return [
                'raw'         => $raw,
                'normalized'  => $normalized,
                'valid'       => true,
                'description' => $row->short_description,
            ];
        }

        // Not valid
        return [
            'raw'         => $raw,
            'normalized'  => $normalized,
            'valid'       => false,
            'description' => null,
        ];
    }
}
