<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;


class EmployeeController extends Controller
{
    public function EmployeeDashboard(){

         return view('employee.employee_index', ['title' => 'Employee Management','module' => 'employee' ]);

    }// End Method

    // public function AllEmployee(){

    //      $employees = Employee::orderBy(DB::raw('CAST(staffid AS UNSIGNED)'))->get();
    //      return view('employee.employees_all',compact('employees'),['title' => 'Employee Management', 'module' => 'employee' ]);

    // }// End Method

    public function AllEmployee(Request $request)
    {
        $selectedStatus = $request->input('employee_status_filter', 'A');

        /*
        |--------------------------------------------------------------------------
        | Employee columns available for user selection
        |--------------------------------------------------------------------------
        */
        $availableColumns = [
            'staffid' => 'ID',
            'stafflastname' => 'Last Name',
            'stafffirstname' => 'First Name',
            'staffaddress1' => 'Address',
            'staffcity' => 'City',
            'staffzip' => 'Zip Code',
            'staffcell' => 'Cell #',
            'staffphone' => 'Phone',
            'staffemergency' => 'Emergency #',
            'drivelicense' => 'Driver License',
            'dateofexp' => 'DL Expiration',
            'dateofhire' => 'Hire Date',
            'dateoftermination' => 'Termination Date',
            'qualification' => 'Qualification',
            'employee_type' => 'Employee Type',
            'pay_period' => 'Pay Period',
            'pay_rate_type' => 'Pay Rate Type',
            'pay_rate' => 'Pay Rate',
            'overtime_eligible' => 'Overtime',
            'payroll_status' => 'Payroll Status',
            'trans_flag' => 'Status',
        ];

        /*
        |--------------------------------------------------------------------------
        | Default visible columns
        |--------------------------------------------------------------------------
        */
        $defaultColumns = [
            'staffid',
            'stafflastname',
            'stafffirstname',
            'staffcell',
            'employee_type',
            'pay_period',
            'pay_rate_type',
            'pay_rate',
            'trans_flag',
        ];

        /*
        |--------------------------------------------------------------------------
        | Load saved user preference from existing user_table_preferences table
        |--------------------------------------------------------------------------
        */
        $userCd = Auth::user()->user_cd;

        $preference = DB::table('user_table_preferences')
            ->where('user_cd', $userCd)
            ->where('table_name', 'employees')
            ->first();

        if ($preference && !empty($preference->visible_columns)) {
            $savedColumns = json_decode($preference->visible_columns, true);

            $selectedColumns = is_array($savedColumns)
                ? array_values(array_intersect($savedColumns, array_keys($availableColumns)))
                : $defaultColumns;
        } else {
            $selectedColumns = $defaultColumns;
        }

        if (empty($selectedColumns)) {
            $selectedColumns = $defaultColumns;
        }

        /*
        |--------------------------------------------------------------------------
        | Status dropdown
        |--------------------------------------------------------------------------
        */
        $statuses = DB::table('gnmsstatus')
            ->select('Id', 'name')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Employee query
        |--------------------------------------------------------------------------
        */
        $query = Employee::query();

        if ($selectedStatus !== '' && $selectedStatus !== null) {
            $query->where('trans_flag', $selectedStatus);
        }

        $employees = $query
            ->orderBy(DB::raw('CAST(staffid AS UNSIGNED)'))
            ->get();

        return view('employee.employees_all', compact(
            'employees',
            'statuses',
            'selectedStatus',
            'availableColumns',
            'selectedColumns'
        ), [
            'title' => 'Employee Management',
            'module' => 'employee'
        ]);

    } // End Method

    // public function AddEmployee(){

    //     $staffid = Employee::selectRaw('MAX(CAST(staffid AS UNSIGNED)) as maxid')->value('maxid') + 1;
    //     return view('employee.employee_add', compact('staffid'),['title' => 'Employee Management', 'module' => 'employee' ]);

    // }// End Method
    public function AddEmployee()
    {
        /*
        |--------------------------------------------------------------------------
        | Generate next Employee ID - always 3 digits
        |--------------------------------------------------------------------------
        | Example:
        | 1   -> 001
        | 6   -> 006
        | 25  -> 025
        | 125 -> 125
        */

        $maxId = Employee::selectRaw(
            'MAX(CAST(staffid AS UNSIGNED)) as maxid'
        )->value('maxid');

        $nextId = ((int) $maxId) + 1;

        $staffid = str_pad($nextId, 3, '0', STR_PAD_LEFT);


        /*
        |--------------------------------------------------------------------------
        | Staff Categories
        |--------------------------------------------------------------------------
        */

        $staffCategories = DB::table('staffcategory')
            ->select(
                'staffcategoryid',
                'staffcategory'
            )
            ->orderBy('staffcategory')
            ->get();


        return view(
            'employee.employee_add',
            compact('staffid', 'staffCategories'),
            [
                'title'  => 'Employee Management',
                'module' => 'employee'
            ]
        );

    } // End Method

    // public function EditEmployee($id){

    //      $employee = Employee::findOrFail($id);
    //      return view('employee.employee_edit', compact('employee'),['title' => 'Employee Management', 'module' => 'employee' ] );

    // }// End Method

    public function EditEmployee($id)
    {
        $employee = Employee::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Staff Categories
        |--------------------------------------------------------------------------
        */

        $staffCategories = DB::table('staffcategory')
            ->select(
                'staffcategoryid',
                'staffcategory'
            )
            ->orderBy('staffcategory')
            ->get();

            /*
        |--------------------------------------------------------------------------
        | Legacy Style Record Navigation
        |--------------------------------------------------------------------------
        | Navigate numerically by staffid.
        | This works with both old IDs like 6 and new IDs like 006.
        |--------------------------------------------------------------------------
        */

        $navTransFlag = 'A';

        $currentStaffId = (int) $employee->staffid;


        /*
        |--------------------------------------------------------------------------
        | First Employee
        |--------------------------------------------------------------------------
        */

        $firstId = Employee::where('trans_flag', $navTransFlag)
            ->orderByRaw('CAST(staffid AS UNSIGNED) ASC')
            ->value('staffid');


        /*
        |--------------------------------------------------------------------------
        | Last Employee
        |--------------------------------------------------------------------------
        */

        $lastId = Employee::where('trans_flag', $navTransFlag)
            ->orderByRaw('CAST(staffid AS UNSIGNED) DESC')
            ->value('staffid');


        /*
        |--------------------------------------------------------------------------
        | Previous Employee
        |--------------------------------------------------------------------------
        */

        $previousId = Employee::where('trans_flag', $navTransFlag)
            ->whereRaw('CAST(staffid AS UNSIGNED) < ?', [$currentStaffId])
            ->orderByRaw('CAST(staffid AS UNSIGNED) DESC')
            ->value('staffid');


        /*
        |--------------------------------------------------------------------------
        | Next Employee
        |--------------------------------------------------------------------------
        */

        $nextId = Employee::where('trans_flag', $navTransFlag)
            ->whereRaw('CAST(staffid AS UNSIGNED) > ?', [$currentStaffId])
            ->orderByRaw('CAST(staffid AS UNSIGNED) ASC')
            ->value('staffid');


        return view('employee.employee_edit', compact(
            'employee',
            'staffCategories',
            'firstId',
            'lastId',
            'previousId',
            'nextId'
        ), [
            'title'  => 'Employee Management',
            'module' => 'employee'
        ]);

    } // End Method

    public function StoreEmployee(Request $request){

        $request->validate([

           'staffid' =>'required|unique:employees|max:3',

           'staffcategoryid' => [
           'required',
           'string',
           'max:3',
           'exists:staffcategory,staffcategoryid',
           ],

       ]);

        $cleanedCell = preg_replace('/\D/', '', $request->input('staffcell'));
        $cleanedPhone = preg_replace('/\D/', '', $request->input('staffphone'));
        $cleanedEmergency = preg_replace('/\D/', '', $request->input('staffemergency'));

        $cleanedSSno = preg_replace('/\D/', '', $request->input('staffssno'));

        $todaydate = now()->format('Y-m-d');
        $todayDate = Carbon::parse($todaydate)->format('Y-m-d');

        $dateofissue = $request->input('dateofissue');
        //$formatteddateofissue = Carbon::parse($dateofissue)->format('Y-m-d');
        $dateofexp = $request->input('dateofexp');
        //$formatteddateofexp = Carbon::parse($dateofexp)->format('Y-m-d');
        $dateofhire = $request->input('dateofhire');
        //$formatteddateofhire = Carbon::parse($dateofhire)->format('Y-m-d');
        $dateoftermination = $request->input('dateoftermination');
        //$formatteddateoftermination = Carbon::parse($dateoftermination)->format('Y-m-d');
        $formatteddateofissue = $request->filled('dateofissue')
            ? Carbon::parse($request->dateofissue)->format('Y-m-d')
            : null;

        $formatteddateofexp = $request->filled('dateofexp')
            ? Carbon::parse($request->dateofexp)->format('Y-m-d')
            : null;

        $formatteddateofhire = $request->filled('dateofhire')
            ? Carbon::parse($request->dateofhire)->format('Y-m-d')
            : null;

        $formatteddateoftermination = $request->filled('dateoftermination')
            ? Carbon::parse($request->dateoftermination)->format('Y-m-d')
            : null;

            Employee::create([

            'staffid' => str_pad((int) $request->staffid, 3, '0', STR_PAD_LEFT),
            'staffcategoryid' => strtoupper($request->staffcategoryid),
            'stafffirstname' => strtoupper($request->stafffirstname),
            'stafflastname' => strtoupper($request->stafflastname),
            'staffssno' => $cleanedSSno,
            'staffaddress1' => strtoupper($request->staffaddress1),
            'staffaddress2' => strtoupper($request->staffaddress2),
            'staffcity' => strtoupper($request->staffcity),
            'staffzip' => $request->staffzip,
            'staffcell' => $cleanedCell,
            'staffphone' => $cleanedPhone,
            'staffemergency' => $cleanedEmergency,
            'drivelicense' => strtoupper($request->drivelicense),
            'dateofissue' => $formatteddateofissue,
            'dateofexp' => $formatteddateofexp,
            'dateofhire' => $formatteddateofhire,
            'qualification' => strtoupper($request->qualification),
            'dateoftermination' => $formatteddateoftermination,
            'trans_flag' => strtoupper($request->trans_flag),
            'employee_type' => strtoupper($request->employee_type),
            'pay_period' => strtoupper($request->pay_period),
            'pay_rate_type' => strtoupper($request->pay_rate_type),
            'pay_rate' => $request->pay_rate,
            'overtime_eligible' => strtoupper($request->overtime_eligible ?? 'N'),
            'payroll_status' => strtoupper($request->payroll_status ?? 'A'),

            'update_dt' => $todayDate,

        ]);

        //event(new Registered($employee));

        $notification = array(
            'message' => 'Employee Record Inserted Successfully',
            'aleart-type' =>'success',
        );

        return redirect()->back()->with($notification);

    }// End Method

    public function UpdateEmployee(Request $request){

         $request->validate([

        'staffcategoryid' => [
            'required',
            'string',
            'max:3',
            'exists:staffcategory,staffcategoryid',
           ],

        ]);


        $cleanedCell = preg_replace('/\D/', '', $request->input('staffcell'));
        $cleanedPhone = preg_replace('/\D/', '', $request->input('staffphone'));
        $cleanedEmergency = preg_replace('/\D/', '', $request->input('staffemergency'));
        $cleanedSSno = preg_replace('/\D/', '', $request->input('staffssno'));

        $todaydate = now()->format('Y-m-d');

        $todayDate = Carbon::parse($todaydate)->format('Y-m-d');

        $dateofissue = $request->input('dateofissue');
        //$formatteddateofissue = Carbon::parse($dateofissue)->format('Y-m-d');
        $dateofexp = $request->input('dateofexp');
        //$formatteddateofexp = Carbon::parse($dateofexp)->format('Y-m-d');
        $dateofhire = $request->input('dateofhire');
        //$formatteddateofhire = Carbon::parse($dateofhire)->format('Y-m-d');
        $dateoftermination = $request->input('dateoftermination');
        //$formatteddateoftermination = Carbon::parse($dateoftermination)->format('Y-m-d');

        $formatteddateofissue = $request->filled('dateofissue')
            ? Carbon::parse($request->dateofissue)->format('Y-m-d')
            : null;

        $formatteddateofexp = $request->filled('dateofexp')
            ? Carbon::parse($request->dateofexp)->format('Y-m-d')
            : null;

        $formatteddateofhire = $request->filled('dateofhire')
            ? Carbon::parse($request->dateofhire)->format('Y-m-d')
            : null;

        $formatteddateoftermination = $request->filled('dateoftermination')
            ? Carbon::parse($request->dateoftermination)->format('Y-m-d')
            : null;

        $data = Employee::find($request->id);
        $data->staffcategoryid = strtoupper($request->staffcategoryid);
        $data->stafffirstname = strtoupper($request->stafffirstname);
        $data->stafflastname = strtoupper($request->stafflastname);
        $data->staffssno = $cleanedSSno;
        $data->staffaddress1 = strtoupper($request->staffaddress1);
        $data->staffaddress2 = strtoupper($request->staffaddress2);
        $data->staffcity = strtoupper($request->staffcity);
        $data->staffzip = $request->staffzip;
        $data->staffcell= $cleanedCell;
        $data->staffphone = $cleanedPhone;
        $data->staffemergency = $cleanedEmergency;
        $data->drivelicense = strtoupper($request->drivelicense);
        $data->dateofissue = $formatteddateofissue;
        $data->dateofexp = $formatteddateofexp;
        $data->dateofhire = $formatteddateofhire;
        $data->qualification = strtoupper($request->qualification);
        $data->dateoftermination = $formatteddateoftermination;
        $data->trans_flag = strtoupper($request->trans_flag);
        $data->employee_type = $request->filled('employee_type')
            ? strtoupper($request->employee_type)
            : null;

        $data->pay_period = $request->filled('pay_period')
            ? strtoupper($request->pay_period)
            : null;

        $data->pay_rate_type = $request->filled('pay_rate_type')
            ? strtoupper($request->pay_rate_type)
            : null;

        $data->pay_rate = $request->filled('pay_rate')
            ? $request->pay_rate
            : null;

        $data->overtime_eligible = strtoupper($request->overtime_eligible ?? 'N');
        $data->payroll_status = strtoupper($request->payroll_status ?? 'A');

        $data->save();

        $notification = array(
            'message' => 'Employee Record Updated Successfully',
            'aleart-type' =>'success',
        );

        return redirect()->back()->with($notification);


    }// End Method

     public function UploadEmployeeImage(){

        return view('settings.upload_employees_image');

    }// End Method

     public function StoreImage(Request $request){

         $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,bmp|max:2048', // 2MB max size (optional) ',
                'new_filename' => 'required|string|max:255'
            ]);

            $file = $request->file('image');
            $newName = $request->new_filename . '.jpg';


            // Save directly to public/members/images
            $file->move(public_path('employees/images'), $newName);
            return back()->with('success', 'Image uploaded as ' . $newName);


    }// End Method

     public function AllEmployeeList(){

            //$employees = Employee::orderBy('staffid')->get();
            $employees = Employee::orderBy(DB::raw('CAST(staffid AS UNSIGNED)'))->get();

            return view('employee.employees_list_all', compact('employees'),['title' => 'Employee Management', 'module' => 'employee' ]);

    }// End Method

    public function exportPdf(Request $request){

         $status = $request->status;

            $query = DB::table('employees'); // or Member::query()

            if ($status !== null && $status !== '') {
                $query->where('trans_flag', $status);
    }

                $employees = $query->get();

                $pdf = Pdf::loadView('employee.employee_list_pdf', compact('employees', 'status'))
                ->setPaper('A4', 'landscape');

                return $pdf->download('employees_report.pdf');

    } // End Method

    public function saveEmployeeTablePreferences(Request $request)
    {
        $userCd = Auth::user()->user_cd;

        $allowedColumns = [
            'staffid',
            'stafflastname',
            'stafffirstname',
            'staffaddress1',
            'staffcity',
            'staffzip',
            'staffcell',
            'staffphone',
            'staffemergency',
            'drivelicense',
            'dateofexp',
            'dateofhire',
            'dateoftermination',
            'qualification',
            'employee_type',
            'pay_period',
            'pay_rate_type',
            'pay_rate',
            'overtime_eligible',
            'payroll_status',
            'trans_flag',
        ];

        $validated = $request->validate([
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string'],
        ]);

        $selectedColumns = array_values(array_intersect(
            $validated['columns'] ?? [],
            $allowedColumns
        ));

        $existing = DB::table('user_table_preferences')
            ->where('user_cd', $userCd)
            ->where('table_name', 'employees')
            ->exists();

        if ($existing) {
            DB::table('user_table_preferences')
                ->where('user_cd', $userCd)
                ->where('table_name', 'employees')
                ->update([
                    'visible_columns' => json_encode($selectedColumns),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('user_table_preferences')->insert([
                'user_cd' => $userCd,
                'table_name' => 'employees',
                'visible_columns' => json_encode($selectedColumns),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Employee column preferences saved successfully.',
        ]);

    } // End Method



}
