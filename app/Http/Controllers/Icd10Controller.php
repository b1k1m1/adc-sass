<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Icd10Controller extends Controller
{
    // public function search(Request $request)
    // {
    //     $q = trim((string) $request->get('q', ''));
    //     $limit = (int) $request->get('limit', 25);
    //     $limit = max(5, min($limit, 50)); // clamp 5..50

    //     if (mb_strlen($q) < 2) {
    //         return response()->json([
    //             'results' => [],
    //         ]);
    //     }

    //     // normalize query a bit (optional)
    //     $qUpper = mb_strtoupper($q);

    //     $query = DB::table('icd10_codes')
    //         ->select([
    //             'code',
    //             'short_description',
    //         ]);

    //     // If user types something like "E11" or "E11.9", prioritize code prefix search
    //     $looksLikeCode = preg_match('/^[A-Za-z][0-9A-Za-z\.]{1,10}$/', $qUpper);

    //     if ($looksLikeCode) {
    //         $query->where('code', 'like', $qUpper . '%')
    //               ->orderBy('code');
    //     } else {
    //         // Full-text search on description
    //         $query->whereRaw('MATCH(short_description) AGAINST (? IN NATURAL LANGUAGE MODE)', [$q])
    //               ->orderByRaw('MATCH(short_description) AGAINST (? IN NATURAL LANGUAGE MODE) DESC', [$q]);
    //     }

    //     $rows = $query->limit($limit)->get();

    //     $results = $rows->map(function ($r) {
    //         return [
    //             'id'   => $r->code, // we store code in members table
    //             'text' => $r->code . ' - ' . $r->short_description,
    //         ];
    //     });

    //     return response()->json([
    //         'results' => $results,
    //     ]);

    // } // End Method

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $limit = (int) $request->get('limit', 25);
        $limit = max(5, min($limit, 50));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'results' => [],
            ]);
        }

        $qUpper = mb_strtoupper($q);

        $rows = DB::table('icd10_codes')
            ->select([
                'code',
                'short_description',
            ])
            ->where(function ($query) use ($q, $qUpper) {

                // Search by ICD-10 code
                $query->where('code', 'like', $qUpper . '%')

                    // OR search anywhere in description
                    ->orWhere('short_description', 'like', '%' . $q . '%');
            })

            // Put exact/prefix code matches first
            ->orderByRaw(
                "CASE
                    WHEN code = ? THEN 0
                    WHEN code LIKE ? THEN 1
                    ELSE 2
                END",
                [
                    $qUpper,
                    $qUpper . '%'
                ]
            )

            ->orderBy('code')
            ->limit($limit)
            ->get();

        $results = $rows->map(function ($r) {
            return [
                'id'   => $r->code,
                'text' => $r->code . ' - ' . $r->short_description,
            ];
        });

        return response()->json([
            'results' => $results,
        ]);

    } // End Method

    public function show(string $code)
    {
        $row = DB::table('icd10_codes')
            ->select('code', 'short_description')
            ->where('code', $code)
            ->first();

        if (!$row) {
            return response()->json(null, 404);
        }

        return response()->json([
            'id' => $row->code,
            'text' => $row->code . ' - ' . $row->short_description,
        ]);

    } // End Method

}
