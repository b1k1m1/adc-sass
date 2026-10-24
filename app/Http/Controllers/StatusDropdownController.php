<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StatusDropdownController extends Controller
{
    public function index()
    {
        return view('member_status.index');

    } // End Method

    public function data(Request $request)
    {
        $query = DB::table('statusdropdown')
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
                return '<a href="'.route('member_status.edit', $row->Id).'"
                        class="btn btn-sm btn-primary">Edit</a>';
            })
            ->rawColumns(['action'])
            ->make(true);

    } // End Method

    public function create()
    {
        return view('member_status.create');

    } // End Method

    public function store(Request $request)
    {
        $data = $request->validate([
            'Id' => 'required|string|max:10|unique:statusdropdown,Id',
            'name' => 'required|string|max:10',
        ]);

        DB::table('statusdropdown')->insert([
            'Id' => strtoupper(trim($data['Id'])),
            'name' => strtoupper(trim($data['name'])),
        ]);

        return redirect()->route('member_status.index')
            ->with('message', 'Member Status added successfully');

    } // End Method

    public function edit($id)
    {
        $row = DB::table('statusdropdown')->where('Id', $id)->first();
        abort_if(!$row, 404);

        return view('member_status.edit', compact('row'));

    } // End Method

    public function update(Request $request, $id)
    {
        $row = DB::table('statusdropdown')->where('Id', $id)->first();
        abort_if(!$row, 404);

        $data = $request->validate([
            'Id' => 'required|string|max:10',
            'name' => 'required|string|max:10',
        ]);

        $exists = DB::table('statusdropdown')
            ->where('Id', $data['Id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Member Status already exists'])->withInput();
        }

        DB::table('statusdropdown')->where('Id', $id)->update([
            'Id' => strtoupper(trim($data['Id'])),
            'name' => strtoupper(trim($data['name'])),
        ]);

        return redirect()->route('member_status.index')
            ->with('message', 'Member Status updated successfully');

    } // End Method
}
