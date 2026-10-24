<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LookupController extends Controller
{
    public function accountmaster(Request $request)
    {
        $term = trim($request->get('q', ''));

        $rows = DB::table('accountmasters')
            ->select('party_cd', 'party_nm')
            ->when($term !== '', function ($q) use ($term) {
                $q->where('party_cd', 'like', $term.'%')
                  ->orWhere('party_nm', 'like', '%'.$term.'%');
            })
            ->orderBy('party_nm')
            ->limit(30)
            ->get();

        return response()->json([
            'results' => $rows->map(fn($r) => [
                'id' => $r->party_cd,
                'party_nm' => $r->party_nm,   // ✅ needed for Pay To Name field
                'text' => $r->party_cd.' - '.$r->party_nm,
            ]),
        ]);

    } // End Method
}
