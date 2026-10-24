<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class Icd10MasterController extends Controller
{
    public function index()
    {
        return view('icd10.index');

    } // End Method

    public function data(Request $request)
    {
        $query = DB::table('icd10_codes')
            ->select('id', 'code', 'short_description');

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                $search = trim($request->input('search.value'));

                if ($search !== '') {
                    $q->where(function ($w) use ($search) {
                        $w->where('code', 'like', "%{$search}%")
                          ->orWhere('short_description', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('action', function ($row) {
                return '<a href="'.route('icd10.edit', $row->id).'"
                        class="btn btn-sm btn-primary">Edit</a>';
            })
            ->rawColumns(['action'])
            ->make(true);

    } // End Method

    public function create()
    {
        return view('icd10.create');

    } // End Method

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:icd10_codes,code',
            'short_description' => 'required|string|max:255',
        ]);

        DB::table('icd10_codes')->insert([
            'code' => strtoupper(trim($data['code'])),
            'short_description' => strtoupper(trim($data['short_description'])),
        ]);

        return redirect()->route('icd10.index')
            ->with('message', 'ICD-10 code added successfully');

    } // End Method

    public function edit($id)
    {
        $row = DB::table('icd10_codes')->where('id', $id)->first();
        abort_if(!$row, 404);

        return view('icd10.edit', compact('row'));

    } // End Method

    public function update(Request $request, $id)
    {
        $row = DB::table('icd10_codes')->where('id', $id)->first();
        abort_if(!$row, 404);

        $data = $request->validate([
            'code' => 'required|string|max:10',
            'short_description' => 'required|string|max:255',
        ]);

        $exists = DB::table('icd10_codes')
            ->where('code', $data['code'])
            ->where('id', '<>', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'ICD-10 code already exists'])->withInput();
        }

        DB::table('icd10_codes')->where('id', $id)->update([
            'code' => strtoupper(trim($data['code'])),
            'short_description' => strtoupper(trim($data['short_description'])),
        ]);

        return redirect()->route('icd10.index')
            ->with('message', 'ICD-10 updated successfully');

    } // End Method
}
