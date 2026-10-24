<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\IdHelper;
use Carbon\Carbon;

class PhysicianController extends Controller
{
    public function index()
    {
        $rows = DB::table('physicianmaster as p')
        ->leftJoin('physiciancategory as c','c.phy_cat_id','=','p.speciality')
        ->select('p.*','c.phy_cat_name')
        ->orderBy('p.doctor_id')
        ->get();
        return view('doctors.physician_master_index', compact('rows'));

    } // End Method

    public function create()
    {
        $nextId = IdHelper::nextId('physicianmaster','doctor_id',4);
        $cats = DB::table('physiciancategory')->where('trans_flag','A')->orderBy('phy_cat_name')->get();

         // defaults for every field used in the form
        $defaults = [
            'doctor_id' => $nextId,
            'speciality' => '',
            'lastname' => '',
            'firstname' => '',
            'cadd1' => '', 'cadd2' => '', 'ccity' => '', 'cstate' => '', 'czip' => '',
            'cphone1' => '', 'cphone2' => '', 'cext1' => '', 'cext2' => '',
            'ccell' => '', 'cfax' => '',
            'oadd1' => '', 'oadd2' => '', 'ocity' => '', 'ostate' => '', 'ozip' => '',
            'ophone1' => '', 'ophone2' => '',
            'trans_flag' => 'A',
        ];
        return view('doctors.physician_master_form', [
                'mode'=>'create',
                'row'  => (object) $defaults,
                'cats'=>$cats
                ]);
    } // End Method

    public function store(Request $req)
    {
        $data = $req->validate([
        'doctor_id' => 'required|string|max:4',
        'speciality'=> 'nullable|string|max:10',
        'lastname' => 'nullable|string|max:15',
        'firstname' => 'nullable|string|max:15',
        'cadd1' => 'nullable|string|max:25', 'cadd2' => 'nullable|string|max:25',
        'ccity' => 'nullable|string|max:25', 'cstate' => 'nullable|string|max:2', 'czip' => 'nullable|string|max:10',
        'cphone1' => 'nullable|string|max:10', 'cphone2' => 'nullable|string|max:10',
        'cext1'=>'nullable|string|max:5','cext2'=>'nullable|string|max:5',
        'ccell'=>'nullable|string|max:10','cfax'=>'nullable|string|max:10',
        'oadd1'=>'nullable|string|max:25','oadd2'=>'nullable|string|max:25',
        'ocity'=>'nullable|string|max:25','ostate'=>'nullable|string|max:2','ozip'=>'nullable|string|max:10',
        'ophone1'=>'nullable|string|max:10','ophone2'=>'nullable|string|max:10',
        'trans_flag'=>'required|in:A,H,D',
        ]);
        $data += [
            'user_cd' => '', //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
            'company_id' => session('company_id','0000'),
            'update_flag' => 'Y',
            'update_dt' => Carbon::now(),
            ];
        DB::table('physicianmaster')->insert($data);
        return redirect()->route('phys.index')->with('success','Physician created.');

    } // End Method

    public function show(string $id)
    {
        $row = DB::table('physicianmaster')->where('doctor_id',$id)->first();
        abort_unless($row, 404);
        $cats = DB::table('physiciancategory')->orderBy('phy_cat_name')->get();
        return view('doctors.physician_master_form', ['mode'=>'view','row'=>$row,'cats'=>$cats]);

    } // End Method

    public function edit(string $id)
    {
        $row = DB::table('physicianmaster')->where('doctor_id',$id)->first();
        abort_unless($row, 404);
        $cats = DB::table('physiciancategory')->orderBy('phy_cat_name')->get();
        return view('doctors.physician_master_form', ['mode'=>'edit','row'=>$row,'cats'=>$cats]);

    } // End Method

    public function update(Request $req, string $id)
    {
        $data = $req->validate([
            'speciality'=> 'nullable|string|max:10',
            'lastname' => 'nullable|string|max:15',
            'firstname' => 'nullable|string|max:15',
            // (other fields same rules as store)
            'cadd1' => 'nullable|string|max:25', 'cadd2' => 'nullable|string|max:25',
            'ccity' => 'nullable|string|max:25', 'cstate' => 'nullable|string|max:2', 'czip' => 'nullable|string|max:10',
            'cphone1' => 'nullable|string|max:10', 'cphone2' => 'nullable|string|max:10',
            'cext1'=>'nullable|string|max:5','cext2'=>'nullable|string|max:5',
            'ccell'=>'nullable|string|max:10','cfax'=>'nullable|string|max:10',
            'oadd1'=>'nullable|string|max:25','oadd2'=>'nullable|string|max:25',
            'ocity'=>'nullable|string|max:25','ostate'=>'nullable|string|max:2','ozip'=>'nullable|string|max:10',
            'ophone1'=>'nullable|string|max:10','ophone2'=>'nullable|string|max:10',
            'trans_flag'=>'required|in:A,H,D',
            ]);

        $data += [ 'update_flag'=>'Y',
                   'update_dt'=>now(),

                 ];

        DB::table('physicianmaster')->where('doctor_id', $id)->update($data);

        return redirect()->route('phys.index')->with('success','Physician updated.');
    }

}
