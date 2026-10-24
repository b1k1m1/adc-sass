<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\IdHelper;
use Carbon\Carbon;

class PhysicianCategoryController extends Controller
{
    public function index()
    {
        $rows = DB::table('physiciancategory')->orderBy('phy_cat_id')->get();
        return view('doctors.physician_cat_index', compact('rows'));

    } // End Method

    public function create()
    {
            $nextId = IdHelper::nextId('physiciancategory','phy_cat_id', 2); // adjust pad
            return view('doctors.physician_cat_form', [
                'mode' => 'create',
                'row' => (object)[
                'phy_cat_id' => $nextId,
                'phy_cat_name' => '',
                'phy_name' => '',
                'trans_flag' => 'A',
                ]
            ]);

    } // End Method

    public function store(Request $req)
    {
        $data = $req->validate([
            'phy_cat_id' => 'required|string|max:10',
            'phy_cat_name' => 'nullable|string|max:25',
            'phy_name' => 'nullable|string|max:30',
            'trans_flag' => 'required|in:A,H,D',
            ]);
        $data += [
            'user_cd' => '', //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
            'company_id' => session('company_id','0000'),
            'update_flag' => 'Y',
            'update_dt' => Carbon::now(),
            ];
        DB::table('physiciancategory')->insert($data);
        return redirect()->route('physcat.index')->with('success','Category created.');

    } // End Method

    public function show(string $id)
    {
        $row = DB::table('physiciancategory')->where('phy_cat_id',$id)->first();
        abort_unless($row, 404);
        return view('doctors.physician_cat_form', ['mode'=>'view','row'=>$row]);

    } // End Method

    public function edit(string $id)
    {
        $row = DB::table('physiciancategory')->where('phy_cat_id',$id)->first();
        abort_unless($row, 404);
        return view('doctors.physician_cat_form', ['mode'=>'edit','row'=>$row]);

    } // End Method

    public function update(Request $req, string $id)
    {
        $data = $req->validate([
            'phy_cat_name' => 'nullable|string|max:25',
            'phy_name' => 'nullable|string|max:30',
            'trans_flag' => 'required|in:A,H,D',
            ]);

        $data += [
            'update_flag' => 'Y',
            'update_dt' => now(),
            ];
        DB::table('physiciancategory')->where('phy_cat_id',$id)->update($data);
        return redirect()->route('physcat.index')->with('success','Category updated.');

    } // End Method

}
