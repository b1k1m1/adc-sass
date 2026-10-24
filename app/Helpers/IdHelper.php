<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class IdHelper
    {
    /**
    * Get next numeric varchar id from a table column (max+1) padded to $pad.
    * If no rows, returns str_pad('1').
    */
    public static function nextId(string $table, string $column, int $pad = 4): string
    {
    $max = DB::table($table)
    ->selectRaw("MAX(CAST($column AS UNSIGNED)) AS mx")
    ->value('mx');
    $next = ((int) $max) + 1;
    return str_pad((string)$next, $pad, '0', STR_PAD_LEFT);
    }
}
