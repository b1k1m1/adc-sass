<?php

namespace App\Helpers;

use Illuminate\Http\Request;

trait HasTransFlag
{
    /**
     * Get Trans_flag (A/N) safely from query or request
     */
    protected function transType(Request $request, string $default = 'A'): string
    {
        $type = strtoupper($request->query('type', $default));
        return in_array($type, ['A', 'N'], true) ? $type : $default;
    }
}
