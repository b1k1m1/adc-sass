<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HMODropdownController extends Controller
{
    public function index()
    {
        return view('hmo.index');

    } // End Method

    public function data(Request $request)
    {
        $query = DB::table('hmodropdown')
            ->select('Id', 'name');

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                $search = trim($request->input('search.value'));

                if ($search !== '') {
                    $q->where(function ($w) use ($search) {
                        $w->where('Id', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('action', function ($row) {
                return '<a href="'.route('hmo.edit', $row->Id).'"
                        class="btn btn-sm btn-primary">Edit</a>';
            })
            ->rawColumns(['action'])
            ->make(true);

    } // End Method

    public function create()
    {
        return view('hmo.create');

    } // End Method

    public function store(Request $request)
    {
        $data = $request->validate([
            'Id' => 'required|string|max:30|unique:hmodropdown,Id',
            'name' => 'required|string|max:30',
        ]);

        DB::table('hmodropdown')->insert([
            'Id' => strtoupper(trim($data['Id'])),
            'name' => strtoupper(trim($data['name'])),
        ]);

        return redirect()->route('hmo.index')
            ->with('message', 'HMO added successfully');

    } // End Method

    public function edit($id)
    {
        $row = DB::table('hmodropdown')->where('Id', $id)->first();
        abort_if(!$row, 404);

        return view('hmo.edit', compact('row'));

    } // End Method

    public function update(Request $request, $id)
    {
        $row = DB::table('hmodropdown')->where('Id', $id)->first();
        abort_if(!$row, 404);

        $data = $request->validate([
            'Id' => 'required|string|max:30',
            'name' => 'required|string|max:30',
        ]);

        $exists = DB::table('hmodropdown')
            ->where('Id', $data['Id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'HMO already exists'])->withInput();
        }

        DB::table('hmodropdowns')->where('Id', $id)->update([
            'Id' => strtoupper(trim($data['Id'])),
            'name' => strtoupper(trim($data['name'])),
        ]);

        return redirect()->route('hmo.index')
            ->with('message', 'HMO updated successfully');

    } // End Method
}

