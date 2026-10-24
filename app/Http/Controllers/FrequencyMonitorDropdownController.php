<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FrequencyMonitorDropdownController extends Controller
{
    public function index()
    {
        return view('frequencymonitor.index');

    } // End Method

    public function data(Request $request)
    {
        $query = DB::table('monitordropdown')
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
                return '<a href="'.route('frequency.edit', $row->Id).'"
                        class="btn btn-sm btn-primary">Edit</a>';
            })
            ->rawColumns(['action'])
            ->make(true);

    } // End Method

    public function create()
    {
        return view('frequencymonitor.create');

    } // End Method

    public function store(Request $request)
    {
        $data = $request->validate([
            'Id' => 'required|string|max:15|unique:monitordropdown,Id',
            'name' => 'required|string|max:15',
        ]);

        DB::table('monitordropdown')->insert([
            'Id' => strtoupper(trim($data['Id'])),
            'name' => strtoupper(trim($data['name'])),
        ]);

        return redirect()->route('frequency.index')
            ->with('message', 'Frequency added successfully');

    } // End Method

    public function edit($id)
    {
        $row = DB::table('monitordropdown')->where('Id', $id)->first();
        abort_if(!$row, 404);

        return view('frequencymonitor.edit', compact('row'));

    } // End Method

    public function update(Request $request, $id)
    {
        $row = DB::table('monitordropdown')->where('Id', $id)->first();
        abort_if(!$row, 404);

        $data = $request->validate([
            'Id' => 'required|string|max:15',
            'name' => 'required|string|max:15',
        ]);

        $exists = DB::table('monitordropdown')
            ->where('Id', $data['Id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Frequency already exists'])->withInput();
        }

        DB::table('monitordropdown')->where('Id', $id)->update([
            'Id' => strtoupper(trim($data['Id'])),
            'name' => strtoupper(trim($data['name'])),
        ]);

        return redirect()->route('frequency.index')
            ->with('message', 'Frequency updated successfully');

    } // End Method
}
