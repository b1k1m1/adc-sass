<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Member;
use App\Traits\HasCommonDbFields;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MemberController extends Controller

{
    use HasCommonDbFields;

    public function MembersDashboard()
    {

        return view('members.members_index', ['title' => 'Members Management', 'module' => 'members']);
    }

    public function AllMembers(Request $request)
    {
        // =========================================================
        // 1. Selected member status
        // =========================================================
        $selectedStatus = $request->get('member_status_filter', 'A');

        // =========================================================
        // 2. Status dropdown
        // =========================================================
        $statuses = DB::table('statusdropdown')
            ->orderByRaw("FIELD(Id, 'A', 'N', 'H', 'G', 'D')")
            ->get();

        // =========================================================
        // 3. Columns users are allowed to select
        //    Do not expose every database column automatically.
        // =========================================================
        $availableColumns = [
            'ParticipantId'       => 'Member ID',
            'LastName'            => 'Last Name',
            'FirstName'           => 'First Name',
            'MiddleName'          => 'Middle Initial',
            'Trans_flag'          => 'Status',
            'DateOfBirth'         => 'Birth Date',
            'AdmissionDate'       => 'Admission Date',
            'Address1'            => 'Address',
            'City'                => 'City',
            'ZipCode'             => 'Zip Code',
            'Phone1'              => 'Home Phone',
            'Phone2'              => 'Cell Phone',
            'MedicaidNo'          => 'Medicaid Number',
            'HMO_Enrolled'        => 'HMO',
            'medicaid_HMO_id'     => 'Insurance ID',
            'medicaid_PA_no'      => 'PA Number',
            'medicaid_PA_Start'   => 'PA Start Date',
            'medicaid_PA_End'     => 'PA End Date',
            'HNP_Expiry_date'     => 'HNP Expiry Date',
            'primary_doctor_nm'   => 'Primary Doctor',

        ];

        // =========================================================
        // 4. Default columns for users without saved preferences
        // =========================================================
        $defaultColumns = [
            'ParticipantId',
            'LastName',
            'FirstName',
            'Address1',
            'City',
            'ZipCode',
            'Trans_flag',
        ];

        // =========================================================
        // 5. Load preference for logged-in user
        // =========================================================
        $userCd = Auth::user()->user_cd;

        $preference = DB::table('user_table_preferences')
            ->where('user_cd', $userCd)
            ->where('table_name', 'members')
            ->first();

        $selectedColumns = $defaultColumns;

        if ($preference && !empty($preference->visible_columns)) {
            $savedColumns = json_decode(
                $preference->visible_columns,
                true
            );

            if (is_array($savedColumns)) {
                // Only accept columns from our approved list
                $savedColumns = array_values(array_intersect(
                    $savedColumns,
                    array_keys($availableColumns)
                ));

                if (!empty($savedColumns)) {
                    $selectedColumns = $savedColumns;
                }
            }
        }

        // =========================================================
        // 6. Load members using DB query only
        // =========================================================
        $members = DB::table('members')
            ->select(array_keys($availableColumns))
            ->where('Trans_flag', $selectedStatus)
            ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
            ->get();

        return view('members.members_all', compact(
            'members',
            'statuses',
            'selectedStatus',
            'availableColumns',
            'selectedColumns'
        ), [
            'title'  => 'Members Management',
            'module' => 'members',
        ]);
    } // End Method

    public function AddMember()
    {

        $employees = DB::table('employees')
            ->select(
                'staffid',
                'stafflastname',
                'stafffirstname',
            )
            ->where('staffcategoryid', 'DRV')
            ->orderBy('stafflastname')
            ->orderBy('stafffirstname')
            ->get();

        $routes = DB::table('route')
            ->select(
                'Route_id',
                'Route_name'
            )
            ->orderBy('Route_name')
            ->get();


        //$employees=Employee::all();
        $nextid = Member::selectRaw('MAX(CAST(ParticipantId AS UNSIGNED)) as maxid')->value('maxid') + 1;

        // $nextid = str_pad(Member::max('ParticipantId')+1, 3,'0',STR_PAD_LEFT);

        return view('members.members_add_tabs_validated', compact('nextid', 'employees', 'routes'), ['title' => 'Members Management', 'module' => 'members']);
    }

    /*
    public function StoreMember(Request $request) {



        //Validation
        $request->validate([

            'ParticipantId' =>'required|unique:members|max:4',

        ]);

        $data = $request->all();
        $exclude = ['email', 'password', 'csrf_token', '_method', '_token', 'bloodpressure', 'bloodsugar',
                    'weight', 'Shift',];
         // Convert all string fields to uppercase
        foreach ($data as $key => $value) {
            if (is_string($value) && !in_array($key, $exclude)) {
                $data[$key] = strtoupper($value);
            }
        }

        // Normalize phone_number and ssn and other fields

        if (isset($data['DateOfBirth'])) {
        $data['DateOfBirth'] = Carbon::parse($data['DateOfBirth'])->format('Y-m-d');
        }
        if (isset($data['AdmissionDate'])) {
        $data['AdmissionDate'] = Carbon::parse($data['AdmissionDate'])->format('Y-m-d');
        }
        if (isset($data['DischargeDate'])) {
        $data['DischargeDate'] = Carbon::parse($data['DischargeDate'])->format('Y-m-d');
        }
        if (isset($data['hold_date'])) {
        $data['hold_date'] = Carbon::parse($data['hold_date'])->format('Y-m-d');
        }
        if (isset($data['HMO_Enrolled_date'])) {
        $data['HMO_Enrolled_date'] = Carbon::parse($data['HMO_Enrolled_date'])->format('Y-m-d');
        }
        if (isset($data['HNP_Expiry_date'])) {
        $data['HNP_Expiry_date'] = Carbon::parse($data['HNP_Expiry_date'])->format('Y-m-d');
        }
        if (isset($data['HNP_Renewal_date'])) {
        $data['HNP_Renewal_date'] = Carbon::parse($data['HNP_Renewal_date'])->format('Y-m-d');
        }
        if (isset($data['medicare_HMO_En_date'])) {
        $data['medicare_HMO_En_date'] = Carbon::parse($data['medicare_HMO_En_date'])->format('Y-m-d');
        }
        if (isset($data['medicaid_PA_Start'])) {
        $data['medicaid_PA_Start'] = Carbon::parse($data['medicaid_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['medicaid_PA_End'])) {
        $data['medicaid_PA_End'] = Carbon::parse($data['medicaid_PA_End'])->format('Y-m-d');
        }
            if (isset($data['medicare_PA_Start'])) {
        $data['medicare_PA_Start'] = Carbon::parse($data['medicare_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['medicare_PA_End'])) {
        $data['medicare_PA_End'] = Carbon::parse($data['medicare_PA_End'])->format('Y-m-d');
        }
        if (isset($data['renew1_PA_Start'])) {
        $data['renew1_PA_Start'] = Carbon::parse($data['renew1_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['renew1_PA_End'])) {
        $data['renew1_PA_End'] = Carbon::parse($data['renew1_PA_End'])->format('Y-m-d');
        }

        if (isset($data['SSNo'])) {
            $data['SSNo'] = preg_replace('/\D/', '', $data['SSNo']);
        }
         if (isset($data['Phone1'])) {
            $data['Phone1'] = preg_replace('/\D/', '', $data['Phone1']);
        }
         if (isset($data['Phone2'])) {
            $data['Phone2'] = preg_replace('/\D/', '', $data['Phone2']);
        }
        if (isset($data['emergency_no'])) {
            $data['emergency_no'] = preg_replace('/\D/', '', $data['emergency_no']);
        }
        if (isset($data['emergency_ext'])) {
            $data['emergency_ext'] = preg_replace('/\D/', '', $data['emergency_ext']);
        }
         if (isset($data['primary_doctor_ph'])) {
            $data['primary_doctor_ph'] = preg_replace('/\D/', '', $data['primary_doctor_ph']);
        }
         if (isset($data['primary_doctor_fax'])) {
            $data['primary_doctor_fax'] = preg_replace('/\D/', '', $data['primary_doctor_fax']);
        }

        $data = $this->addCommonFieldsForCreate($data);

        Member::create($data);

          $notification = array(
            'message' => 'Member Record Inserted Successfully',
            'aleart-type' =>'success',
        );

        return redirect()->back()->with($notification);
    }
    */
    public function StoreMember(Request $request)
    {
        $request->validate([

            'ParticipantId' => 'required|unique:members|max:4',
        ]);

        $data = $request->all();
        $exclude = [
            'email',
            'password',
            'csrf_token',
            '_method',
            '_token',
            'bloodpressure',
            'bloodsugar',
            'weight',
            'Shift',
        ];

        foreach ($data as $key => $value) {
            if (is_string($value) && !in_array($key, $exclude)) {
                $data[$key] = strtoupper($value);
            }
        }

        if (isset($data['DateOfBirth'])) {
            $data['DateOfBirth'] = Carbon::parse($data['DateOfBirth'])->format('Y-m-d');
        }
        if (isset($data['AdmissionDate'])) {
            $data['AdmissionDate'] = Carbon::parse($data['AdmissionDate'])->format('Y-m-d');
        }
        if (isset($data['DischargeDate'])) {
            $data['DischargeDate'] = Carbon::parse($data['DischargeDate'])->format('Y-m-d');
        }
        if (isset($data['hold_date'])) {
            $data['hold_date'] = Carbon::parse($data['hold_date'])->format('Y-m-d');
        }
        if (isset($data['HMO_Enrolled_date'])) {
            $data['HMO_Enrolled_date'] = Carbon::parse($data['HMO_Enrolled_date'])->format('Y-m-d');
        }
        if (isset($data['HNP_Expiry_date'])) {
            $data['HNP_Expiry_date'] = Carbon::parse($data['HNP_Expiry_date'])->format('Y-m-d');
        }
        if (isset($data['HNP_Renewal_date'])) {
            $data['HNP_Renewal_date'] = Carbon::parse($data['HNP_Renewal_date'])->format('Y-m-d');
        }
        if (isset($data['medicare_HMO_En_date'])) {
            $data['medicare_HMO_En_date'] = Carbon::parse($data['medicare_HMO_En_date'])->format('Y-m-d');
        }
        if (isset($data['medicaid_PA_Start'])) {
            $data['medicaid_PA_Start'] = Carbon::parse($data['medicaid_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['medicaid_PA_End'])) {
            $data['medicaid_PA_End'] = Carbon::parse($data['medicaid_PA_End'])->format('Y-m-d');
        }
        if (isset($data['medicare_PA_Start'])) {
            $data['medicare_PA_Start'] = Carbon::parse($data['medicare_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['medicare_PA_End'])) {
            $data['medicare_PA_End'] = Carbon::parse($data['medicare_PA_End'])->format('Y-m-d');
        }
        if (isset($data['renew1_PA_Start'])) {
            $data['renew1_PA_Start'] = Carbon::parse($data['renew1_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['renew1_PA_End'])) {
            $data['renew1_PA_End'] = Carbon::parse($data['renew1_PA_End'])->format('Y-m-d');
        }

        if (isset($data['SSNo'])) {
            $data['SSNo'] = preg_replace('/\D/', '', $data['SSNo']);
        }
        if (isset($data['Phone1'])) {
            $data['Phone1'] = preg_replace('/\D/', '', $data['Phone1']);
        }
        if (isset($data['Phone2'])) {
            $data['Phone2'] = preg_replace('/\D/', '', $data['Phone2']);
        }
        if (isset($data['emergency_no'])) {
            $data['emergency_no'] = preg_replace('/\D/', '', $data['emergency_no']);
        }
        if (isset($data['emergency_ext'])) {
            $data['emergency_ext'] = preg_replace('/\D/', '', $data['emergency_ext']);
        }
        if (isset($data['primary_doctor_ph'])) {
            $data['primary_doctor_ph'] = preg_replace('/\D/', '', $data['primary_doctor_ph']);
        }
        if (isset($data['primary_doctor_fax'])) {
            $data['primary_doctor_fax'] = preg_replace('/\D/', '', $data['primary_doctor_fax']);
        }

        if (isset($data['pharmacy_phone'])) {
            $data['pharmacy_phone'] = preg_replace('/\D/', '', $data['pharmacy_phone']);
        }

        if (isset($data['pharmacy_fax'])) {
            $data['pharmacy_fax'] = preg_replace('/\D/', '', $data['pharmacy_fax']);
        }

        $data = $this->addCommonFieldsForCreate($data);

        DB::transaction(function () use (&$data) {

            Member::create($data);

            $this->syncCoverageHistory(
                $data['ParticipantId'],
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | Keep Billing Master In Sync
            |--------------------------------------------------------------------------
            |
            | Do not require insurance / PA / diagnosis at member creation time.
            | Whatever is currently available in members is copied to
            | billing_members; missing fields remain NULL and can be updated later.
            |
            */

            $this->syncBillingMember(
                $data['ParticipantId']
            );
        });

        $notification = array(
            'message' => 'Member Record Inserted Successfully',
            'aleart-type' => 'success',
        );

        return redirect()->back()->with($notification);
    } // End Method

    public function EditMember($ParticipantId)
    {

        $employees = DB::table('employees')
            ->select(
                'staffid',
                'stafflastname',
                'stafffirstname',
            )
            ->where('staffcategoryid', 'DRV')
            ->orderBy('stafflastname')
            ->orderBy('stafffirstname')
            ->get();

        $routes = DB::table('route')
            ->select(
                'Route_id',
                'Route_name'
            )
            ->orderBy('Route_name')
            ->get();

        $coverageHistory = DB::table('member_coverage_history')
            ->where('ParticipantId', $ParticipantId)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        //$employees=Employee::all();
        $member = Member::findOrFail($ParticipantId);

        $updatedByName = null;

        if (!empty($member?->user_cd)) {
            $u = DB::table('users')
                ->select('firstname', 'lastname', 'user_cd')
                ->where('user_cd', $member->user_cd)
                ->first();


            if ($u) {
                $updatedByName = trim($u->firstname . ' ' . $u->lastname);
            }
        }

        return view('members.members_edit_tabs_validated', compact('member', 'employees', 'routes', 'updatedByName', 'coverageHistory'), ['title' => 'Members Management', 'module' => 'members']);
    } // End Method
    /*
    public function UpdateMember(Request $request){

        $data = $request->all();
        $exclude = ['email', 'password', 'csrf_token', '_method', '_token', 'bloodpressure', 'bloodsugar',
                    'weight', 'Shift',];

         // Convert all string fields to uppercase
        foreach ($data as $key => $value) {
            if (is_string($value) && !in_array($key, $exclude)) {
                $data[$key] = strtoupper($value);
            }
        }

        // Normalize phone_number and ssn and other fields

            if (isset($data['DateOfBirth'])) {
            $data['DateOfBirth'] = Carbon::parse($data['DateOfBirth'])->format('Y-m-d');
            }
            if (isset($data['AdmissionDate'])) {
            $data['AdmissionDate'] = Carbon::parse($data['AdmissionDate'])->format('Y-m-d');
            }
            if (isset($data['DischargeDate'])) {
            $data['DischargeDate'] = Carbon::parse($data['DischargeDate'])->format('Y-m-d');
            }
            if (isset($data['hold_date'])) {
            $data['hold_date'] = Carbon::parse($data['hold_date'])->format('Y-m-d');
            }
            if (isset($data['HMO_Enrolled_date'])) {
            $data['HMO_Enrolled_date'] = Carbon::parse($data['HMO_Enrolled_date'])->format('Y-m-d');
            }
            if (isset($data['HNP_Expiry_date'])) {
            $data['HNP_Expiry_date'] = Carbon::parse($data['HNP_Expiry_date'])->format('Y-m-d');
            }
            if (isset($data['HNP_Renewal_date'])) {
            $data['HNP_Renewal_date'] = Carbon::parse($data['HNP_Renewal_date'])->format('Y-m-d');
            }
            if (isset($data['medicare_HMO_En_date'])) {
            $data['medicare_HMO_En_date'] = Carbon::parse($data['medicare_HMO_En_date'])->format('Y-m-d');
            }
            if (isset($data['medicaid_PA_Start'])) {
            $data['medicaid_PA_Start'] = Carbon::parse($data['medicaid_PA_Start'])->format('Y-m-d');
            }
            if (isset($data['medicaid_PA_End'])) {
            $data['medicaid_PA_End'] = Carbon::parse($data['medicaid_PA_End'])->format('Y-m-d');
            }
             if (isset($data['medicare_PA_Start'])) {
            $data['medicare_PA_Start'] = Carbon::parse($data['medicare_PA_Start'])->format('Y-m-d');
            }
            if (isset($data['medicare_PA_End'])) {
            $data['medicare_PA_End'] = Carbon::parse($data['medicare_PA_End'])->format('Y-m-d');
            }
            if (isset($data['renew1_PA_Start'])) {
            $data['renew1_PA_Start'] = Carbon::parse($data['renew1_PA_Start'])->format('Y-m-d');
            }
            if (isset($data['renew1_PA_End'])) {
            $data['renew1_PA_End'] = Carbon::parse($data['renew1_PA_End'])->format('Y-m-d');
            }

        if (isset($data['SSNo'])) {
            $data['SSNo'] = preg_replace('/\D/', '', $data['SSNo']);
        }
         if (isset($data['Phone1'])) {
            $data['Phone1'] = preg_replace('/\D/', '', $data['Phone1']);
        }
         if (isset($data['Phone2'])) {
            $data['Phone2'] = preg_replace('/\D/', '', $data['Phone2']);
        }
        if (isset($data['emergency_no'])) {
            $data['emergency_no'] = preg_replace('/\D/', '', $data['emergency_no']);
        }
        if (isset($data['emergency_ext'])) {
            $data['emergency_ext'] = preg_replace('/\D/', '', $data['emergency_ext']);
        }
         if (isset($data['primary_doctor_ph'])) {
            $data['primary_doctor_ph'] = preg_replace('/\D/', '', $data['primary_doctor_ph']);
        }
         if (isset($data['primary_doctor_fax'])) {
            $data['primary_doctor_fax'] = preg_replace('/\D/', '', $data['primary_doctor_fax']);
        }

        $member = Member::findOrFail($request->ParticipantId);

        $data = $this->addCommonFieldsForUpdate($data);

        $member->update($data);

         $notification = array(
            'message' => 'Member Record Updated Successfully',
            'aleart-type' =>'success',
        );

        return redirect()->back()->with($notification);

    }// End Method
    */

    public function UpdateMember(Request $request)
    {
        $data = $request->all();
        $exclude = [
            'email',
            'password',
            'csrf_token',
            '_method',
            '_token',
            'bloodpressure',
            'bloodsugar',
            'weight',
            'Shift',
        ];

        foreach ($data as $key => $value) {
            if (is_string($value) && !in_array($key, $exclude)) {
                $data[$key] = strtoupper($value);
            }
        }

        if (isset($data['DateOfBirth'])) {
            $data['DateOfBirth'] = Carbon::parse($data['DateOfBirth'])->format('Y-m-d');
        }
        if (isset($data['AdmissionDate'])) {
            $data['AdmissionDate'] = Carbon::parse($data['AdmissionDate'])->format('Y-m-d');
        }
        if (isset($data['DischargeDate'])) {
            $data['DischargeDate'] = Carbon::parse($data['DischargeDate'])->format('Y-m-d');
        }
        if (isset($data['hold_date'])) {
            $data['hold_date'] = Carbon::parse($data['hold_date'])->format('Y-m-d');
        }
        if (isset($data['HMO_Enrolled_date'])) {
            $data['HMO_Enrolled_date'] = Carbon::parse($data['HMO_Enrolled_date'])->format('Y-m-d');
        }
        if (isset($data['HNP_Expiry_date'])) {
            $data['HNP_Expiry_date'] = Carbon::parse($data['HNP_Expiry_date'])->format('Y-m-d');
        }
        if (isset($data['HNP_Renewal_date'])) {
            $data['HNP_Renewal_date'] = Carbon::parse($data['HNP_Renewal_date'])->format('Y-m-d');
        }
        if (isset($data['medicare_HMO_En_date'])) {
            $data['medicare_HMO_En_date'] = Carbon::parse($data['medicare_HMO_En_date'])->format('Y-m-d');
        }
        if (isset($data['medicaid_PA_Start'])) {
            $data['medicaid_PA_Start'] = Carbon::parse($data['medicaid_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['medicaid_PA_End'])) {
            $data['medicaid_PA_End'] = Carbon::parse($data['medicaid_PA_End'])->format('Y-m-d');
        }
        if (isset($data['medicare_PA_Start'])) {
            $data['medicare_PA_Start'] = Carbon::parse($data['medicare_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['medicare_PA_End'])) {
            $data['medicare_PA_End'] = Carbon::parse($data['medicare_PA_End'])->format('Y-m-d');
        }
        if (isset($data['renew1_PA_Start'])) {
            $data['renew1_PA_Start'] = Carbon::parse($data['renew1_PA_Start'])->format('Y-m-d');
        }
        if (isset($data['renew1_PA_End'])) {
            $data['renew1_PA_End'] = Carbon::parse($data['renew1_PA_End'])->format('Y-m-d');
        }

        if (isset($data['SSNo'])) {
            $data['SSNo'] = preg_replace('/\D/', '', $data['SSNo']);
        }
        if (isset($data['Phone1'])) {
            $data['Phone1'] = preg_replace('/\D/', '', $data['Phone1']);
        }
        if (isset($data['Phone2'])) {
            $data['Phone2'] = preg_replace('/\D/', '', $data['Phone2']);
        }
        if (isset($data['emergency_no'])) {
            $data['emergency_no'] = preg_replace('/\D/', '', $data['emergency_no']);
        }
        if (isset($data['emergency_ext'])) {
            $data['emergency_ext'] = preg_replace('/\D/', '', $data['emergency_ext']);
        }
        if (isset($data['primary_doctor_ph'])) {
            $data['primary_doctor_ph'] = preg_replace('/\D/', '', $data['primary_doctor_ph']);
        }
        if (isset($data['primary_doctor_fax'])) {
            $data['primary_doctor_fax'] = preg_replace('/\D/', '', $data['primary_doctor_fax']);
        }

        if (isset($data['pharmacy_phone'])) {
            $data['pharmacy_phone'] = preg_replace('/\D/', '', $data['pharmacy_phone']);
        }

        if (isset($data['pharmacy_fax'])) {
            $data['pharmacy_fax'] = preg_replace('/\D/', '', $data['pharmacy_fax']);
        }

        $member = Member::findOrFail($request->ParticipantId);

        $data = $this->addCommonFieldsForUpdate($data);

        DB::transaction(function () use ($member, $data, $request) {

            $member->update($data);

            $this->syncCoverageHistory(
                $request->ParticipantId,
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | Keep Billing Master In Sync
            |--------------------------------------------------------------------------
            */

            $this->syncBillingMember(
                $request->ParticipantId
            );
        });

        $notification = array(
            'message' => 'Member Record Updated Successfully',
            'aleart-type' => 'success',
        );

        return redirect()->back()->with($notification);
    } // End Method

    public function getMedicalInfo($id)
    { {
            //Log::info("We are here in GetMedicalInfo");
            // $id = str_pad($id, 3, '0', STR_PAD_LEFT); // if IDs are like "001"

            //Log::info("Fetching info for ID: $id");

            $member = DB::table('members')
                ->whereRaw('TRIM(ParticipantId) = ?', [$id])
                ->first();

            if (!$member) {
                return response()->json(['error' => 'Member not found'], 404);
            }

            return response()->json($member);
        }
    } // End Method

    /*
    public function updateMedicalInfo(Request $request){

    $participantId = trim($request->ParticipantId);

    DB::table('members')
        ->where('ParticipantId', $participantId)
        ->update([
            'LastName' => trim($request->LastName),
            'FirstName' => trim($request->FirstName),
            'HMO_Enrolled' => $request->HMO_Enrolled,
            'medicaid_HMO_id' => trim($request->medicaid_HMO_id),
            'medicaid_PA_no' => trim($request->medicaid_PA_no),
            'medicaid_PA_Start' => Carbon::createFromFormat('m/d/Y', $request->medicaid_PA_Start)->format('Y-m-d'),
            'medicaid_PA_End' => Carbon::createFromFormat('m/d/Y', $request->medicaid_PA_End)->format('Y-m-d'),
        ] + $this->commonFields());

        return response()->json(['success' => true]);
        } // End Mtehod
        */

    /*
        public function updateMedicalInfo(Request $request)
        {

            $request->validate([
                'ParticipantId'     => 'required',
                'LastName'          => 'required|string|max:255',
                'FirstName'         => 'required|string|max:255',
                'HMO_Enrolled'      => 'nullable|string|max:255',
                'medicaid_HMO_id'   => 'nullable|string|max:255',
                'medicaid_PA_no'    => 'nullable|string|max:255',
                'medicaid_PA_Start' => 'nullable|date_format:m/d/Y',
                'medicaid_PA_End'   => 'nullable|date_format:m/d/Y',
                'PrimaryDiagnosis1' => 'nullable|string|max:10',
                'SecondaryDiag1'    => 'nullable|string|max:10',
                'HNP_Expiry_date'   => 'nullable|date_format:m/d/Y',
            ]);

            $participantId = trim($request->ParticipantId);

            $update = [
                'LastName'        => trim($request->LastName),
                'FirstName'       => trim($request->FirstName),
                'HMO_Enrolled'    => $request->HMO_Enrolled ? trim($request->HMO_Enrolled) : null,
                'medicaid_HMO_id' => trim((string) $request->medicaid_HMO_id),
                'medicaid_PA_no'  => trim((string) $request->medicaid_PA_no),
                'PrimaryDiagnosis1' => $request->filled('PrimaryDiagnosis1') ? trim($request->PrimaryDiagnosis1) : null,
                'SecondaryDiag1'    => $request->filled('SecondaryDiag1') ? trim($request->SecondaryDiag1) : null,

                ]+ $this->commonFields();

            // Only parse dates if they exist
            if (!empty($request->medicaid_PA_Start)) {
                $update['medicaid_PA_Start'] = Carbon::createFromFormat('m/d/Y', $request->medicaid_PA_Start)->format('Y-m-d');
            }
            if (!empty($request->medicaid_PA_End)) {
                $update['medicaid_PA_End'] = Carbon::createFromFormat('m/d/Y', $request->medicaid_PA_End)->format('Y-m-d');
            }

            if (!empty($request->HNP_Expiry_date)) {
                $update['HNP_Expiry_date'] = Carbon::createFromFormat('m/d/Y', $request->HNP_Expiry_date)->format('Y-m-d');
            }

            $updated = DB::table('members')
                ->where('ParticipantId', $participantId)
                ->update($update);

            if ($updated === 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No record updated. Check ParticipantId ($participantId).",
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'PA information saved successfully.',
            ]);
        }
        */
    public function updateMedicalInfo(Request $request)
    {
        $request->validate([
            'ParticipantId'     => 'required',
            'LastName'          => 'required|string|max:255',
            'FirstName'         => 'required|string|max:255',
            'HMO_Enrolled'      => 'nullable|string|max:255',
            'medicaid_HMO_id'   => 'nullable|string|max:255',
            'medicaid_PA_no'    => 'nullable|string|max:255',
            'medicaid_PA_Start' => 'nullable|date_format:m/d/Y',
            'medicaid_PA_End'   => 'nullable|date_format:m/d/Y',
            'PrimaryDiagnosis1' => 'nullable|string|max:10',
            'SecondaryDiag1'    => 'nullable|string|max:10',
            'HNP_Expiry_date'   => 'nullable|date_format:m/d/Y',
        ]);

        $participantId = trim($request->ParticipantId);

        $update = [
            'LastName'          => strtoupper(trim($request->LastName)),
            'FirstName'         => strtoupper(trim($request->FirstName)),
            'HMO_Enrolled'      => $request->filled('HMO_Enrolled') ? strtoupper(trim($request->HMO_Enrolled)) : null,
            'medicaid_HMO_id'   => $request->filled('medicaid_HMO_id') ? strtoupper(trim($request->medicaid_HMO_id)) : null,
            'medicaid_PA_no'    => $request->filled('medicaid_PA_no') ? strtoupper(trim($request->medicaid_PA_no)) : null,
            'PrimaryDiagnosis1' => $request->filled('PrimaryDiagnosis1') ? strtoupper(trim($request->PrimaryDiagnosis1)) : null,
            'SecondaryDiag1'    => $request->filled('SecondaryDiag1') ? strtoupper(trim($request->SecondaryDiag1)) : null,
        ] + $this->commonFields();

        $update['medicaid_PA_Start'] = !empty($request->medicaid_PA_Start)
            ? Carbon::createFromFormat('m/d/Y', $request->medicaid_PA_Start)->format('Y-m-d')
            : null;

        $update['medicaid_PA_End'] = !empty($request->medicaid_PA_End)
            ? Carbon::createFromFormat('m/d/Y', $request->medicaid_PA_End)->format('Y-m-d')
            : null;

        $update['HNP_Expiry_date'] = !empty($request->HNP_Expiry_date)
            ? Carbon::createFromFormat('m/d/Y', $request->HNP_Expiry_date)->format('Y-m-d')
            : null;

        DB::transaction(function () use ($participantId, $update) {
            $updated = DB::table('members')
                ->where('ParticipantId', $participantId)
                ->update($update);

            if ($updated === 0) {
                throw new \RuntimeException("No record updated. Check ParticipantId ({$participantId}).");
            }

            $this->syncCoverageHistory(
                $participantId,
                $update
            );

            /*
                |--------------------------------------------------------------------------
                | Keep Billing Master In Sync
                |--------------------------------------------------------------------------
                |
                | This method is commonly used when insurance / authorization /
                | diagnosis information becomes available after the member was
                | originally created.
                |
                */

            $this->syncBillingMember(
                $participantId
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'PA information saved successfully.',
        ]);
    } // End Method

    // public function UploadMemberImage(){

    //     return view('settings.upload_members_image', ['title' => 'Members Management','module' => 'members' ]);


    // } // End Method

    // public function StoreImage(Request $request)
    // {
    //     $request->validate([
    //         'image' => 'required|image|mimes:jpg,jpeg,bmp,png|max:5120',
    //         'new_filename' => 'required|string|max:255',
    //     ]);

    //     $mediaFolder = session('media_folder');

    //     if (empty($mediaFolder)) {
    //         return back()->with('error', 'Organization media folder is not configured.');
    //     }

    //     $file = $request->file('image');

    //     $baseName = preg_replace(
    //         '/[^A-Za-z0-9_-]/',
    //         '_',
    //         trim($request->new_filename)
    //     );

    //     $newName = $baseName . '.jpg';

    //     $folder = public_path(
    //         "uploads/{$mediaFolder}/members/images"
    //     );

    //     if (!is_dir($folder)) {
    //         mkdir($folder, 0775, true);
    //     }

    //     $path = $folder . DIRECTORY_SEPARATOR . $newName;

    //     Image::make($file)
    //         ->fit(400, 400)
    //         ->encode('jpg', 75)
    //         ->save($path);

    //     // Delete old cached thumbnail so it is recreated next time
    //     $thumbPath = public_path(
    //         "uploads/{$mediaFolder}/members/images/thumbs/{$newName}"
    //     );

    //     if (is_file($thumbPath)) {
    //         @unlink($thumbPath);
    //     }

    //     return back()->with(
    //         'success',
    //         'Image uploaded successfully as ' . $newName
    //     );

    // } // End Method

    public function UploadMemberImage(Request $request)
    {
        $shift = $request->get('shift', 'A');

        // Only allow Morning or Noon
        if (!in_array($shift, ['A', 'N'])) {
            $shift = 'A';
        }

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName'
            )
            ->where('Trans_flag', $shift)
            ->orderBy('ParticipantId')
            ->get();

        return view('settings.upload_members_image', [
            'title'   => 'Members Management',
            'module'  => 'members',
            'members' => $members,
            'shift'   => $shift,
        ]);
    } // End Method


    public function StoreImage(Request $request)
    {
        $request->validate([
            'image'          => 'required|image|mimes:jpg,jpeg,bmp,png|max:5120',
            'participant_id' => 'required|exists:members,ParticipantId',
        ]);

        $mediaFolder = session('media_folder');

        if (empty($mediaFolder)) {
            return back()->with(
                'error',
                'Organization media folder is not configured.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Participant ID
        |--------------------------------------------------------------------------
        */
        $participantId = trim($request->participant_id);

        /*
        |--------------------------------------------------------------------------
        | Optional Safety Check
        |--------------------------------------------------------------------------
        | Make sure the participant belongs to Morning or Noon shift.
        */
        $member = DB::table('members')
            ->select('ParticipantId', 'Trans_flag')
            ->where('ParticipantId', $participantId)
            ->whereIn('Trans_flag', ['A', 'N'])
            ->first();

        if (!$member) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected member was not found or is not in Morning/Noon shift.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | File Name
        |--------------------------------------------------------------------------
        | ParticipantId becomes the image filename.
        | Example: 0012 => 0012.jpg
        */
        $newName = $participantId . '.jpg';

        /*
        |--------------------------------------------------------------------------
        | Main Image Folder
        |--------------------------------------------------------------------------
        */
        $folder = public_path(
            "uploads/{$mediaFolder}/members/images"
        );

        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        /*
        |--------------------------------------------------------------------------
        | Thumbnail Folder
        |--------------------------------------------------------------------------
        */
        $thumbFolder = public_path(
            "uploads/{$mediaFolder}/members/images/thumbs"
        );

        if (!is_dir($thumbFolder)) {
            mkdir($thumbFolder, 0775, true);
        }

        /*
        |--------------------------------------------------------------------------
        | Full Image Path
        |--------------------------------------------------------------------------
        */
        $path = $folder . DIRECTORY_SEPARATOR . $newName;

        /*
        |--------------------------------------------------------------------------
        | Thumbnail Path
        |--------------------------------------------------------------------------
        */
        $thumbPath = $thumbFolder . DIRECTORY_SEPARATOR . $newName;

        /*
        |--------------------------------------------------------------------------
        | Delete Existing Main Image
        |--------------------------------------------------------------------------
        | This guarantees the previous photo is replaced.
        */
        if (is_file($path)) {
            @unlink($path);
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Existing Thumbnail
        |--------------------------------------------------------------------------
        | The thumbnail will be recreated later by your normal thumbnail process.
        */
        if (is_file($thumbPath)) {
            @unlink($thumbPath);
        }

        /*
        |--------------------------------------------------------------------------
        | Process and Save New Image
        |--------------------------------------------------------------------------
        */
        try {

            $file = $request->file('image');

            Image::make($file)
                ->fit(400, 400)
                ->encode('jpg', 75)
                ->save($path);
        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to upload the image. Please try again.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */
        return back()->with(
            'success',
            'Photo uploaded successfully for Member ID '
                . $participantId
                . ' as '
                . $newName
        );
    } // End Method

    // Members List
    //public function AllMemberList(){
    //
    //           $members = Member::orderBy('ParticipantId')->get();
    //
    //         return view('members.members_list_all', compact('members'),['title' => 'Members Management', 'module' => 'members' ]);
    //
    //       }
    // End Method

    public function AllMemberList(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Selected status
        |--------------------------------------------------------------------------
        |
        | Default status is Active.
        | Empty value means show members from all statuses.
        |
        */
        $selectedStatus = trim(
            (string) $request->get('member_status_filter', 'A')
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Columns allowed on this screen
        |--------------------------------------------------------------------------
        */
        $availableColumns = [
            'ParticipantId'       => 'Member ID',
            'LastName'            => 'Last Name',
            'FirstName'           => 'First Name',
            'MiddleName'          => 'Middle Initial',
            'Trans_flag'          => 'Status',
            'DateOfBirth'         => 'Birth Date',
            'AdmissionDate'       => 'Admission Date',
            'Address1'            => 'Address',
            'City'                => 'City',
            'ZipCode'             => 'Zip Code',
            'Phone1'              => 'Home Phone',
            'Phone2'              => 'Cell Phone',
            'MedicaidNo'          => 'Medicaid Number',
            'HMO_Enrolled'        => 'HMO',
            'medicaid_HMO_id'     => 'Insurance ID',
            'medicaid_PA_no'      => 'PA Number',
            'medicaid_PA_Start'   => 'PA Start Date',
            'medicaid_PA_End'     => 'PA End Date',
            'HNP_Expiry_date'     => 'HNP Expiry Date',
            'primary_doctor_nm'   => 'Primary Doctor',
        ];

        /*
        |--------------------------------------------------------------------------
        | 3. Default columns
        |--------------------------------------------------------------------------
        */
        $defaultColumns = [
            'ParticipantId',
            'LastName',
            'FirstName',
            'Address1',
            'City',
            'ZipCode',
            'Trans_flag',
        ];

        /*
        |--------------------------------------------------------------------------
        | 4. Load logged-in user's saved preference
        |--------------------------------------------------------------------------
        |
        | Important:
        | This screen uses members_list_all.
        |
        | The other members screen can continue using table_name = members.
        |
        */
        $userCd = Auth::user()->user_cd;

        $preference = DB::table('user_table_preferences')
            ->where('user_cd', $userCd)
            ->where('table_name', 'members_list_all')
            ->first();

        $selectedColumns = $defaultColumns;

        if ($preference && !empty($preference->visible_columns)) {
            $savedColumns = json_decode(
                $preference->visible_columns,
                true
            );

            if (is_array($savedColumns)) {
                /*
                * Only allow approved columns.
                * This also preserves the user's saved column order.
                */
                $savedColumns = array_values(array_intersect(
                    $savedColumns,
                    array_keys($availableColumns)
                ));

                if (!empty($savedColumns)) {
                    $selectedColumns = $savedColumns;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Status dropdown
        |--------------------------------------------------------------------------
        */
        $statuses = DB::table('statusdropdown')
            ->orderByRaw("FIELD(Id, 'A', 'N', 'H', 'G', 'D')")
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 6. Load members
        |--------------------------------------------------------------------------
        |
        | All approved columns are loaded because DataTables needs the columns
        | available in the HTML before JavaScript can show or hide them.
        |
        */
        $membersQuery = DB::table('members')
            ->select(array_keys($availableColumns));

        if ($selectedStatus !== '') {
            $membersQuery->where(
                'Trans_flag',
                $selectedStatus
            );
        }

        $members = $membersQuery
            ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 7. Return blade
        |--------------------------------------------------------------------------
        */
        return view('members.members_list_all', compact(
            'members',
            'statuses',
            'selectedStatus',
            'availableColumns',
            'defaultColumns',
            'selectedColumns'
        ), [
            'title'  => 'Members Management',
            'module' => 'members',
        ]);
    } // End Method

    public function exportPdf(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Columns allowed in this report
        |--------------------------------------------------------------------------
        */
        $availableColumns = [
            'ParticipantId'       => 'Member ID',
            'LastName'            => 'Last Name',
            'FirstName'           => 'First Name',
            'MiddleName'          => 'Middle Initial',
            'Trans_flag'          => 'Status',
            'DateOfBirth'         => 'Birth Date',
            'AdmissionDate'       => 'Admission Date',
            'Address1'            => 'Address',
            'City'                => 'City',
            'ZipCode'             => 'Zip Code',
            'Phone1'              => 'Home Phone',
            'Phone2'              => 'Cell Phone',
            'MedicaidNo'          => 'Medicaid Number',
            'HMO_Enrolled'        => 'HMO',
            'medicaid_HMO_id'     => 'Insurance ID',
            'medicaid_PA_no'      => 'PA Number',
            'medicaid_PA_Start'   => 'PA Start Date',
            'medicaid_PA_End'     => 'PA End Date',
            'HNP_Expiry_date'     => 'HNP Expiry Date',
            'primary_doctor_nm'   => 'Primary Doctor',
        ];

        /*
        |--------------------------------------------------------------------------
        | 2. Default columns
        |--------------------------------------------------------------------------
        */
        $defaultColumns = [
            'ParticipantId',
            'LastName',
            'FirstName',
            'Address1',
            'City',
            'ZipCode',
            'Trans_flag',
        ];

        /*
        |--------------------------------------------------------------------------
        | 3. Selected status
        |--------------------------------------------------------------------------
        |
        | Empty status means all members.
        |
        */
        $status = trim(
            (string) $request->get('status', '')
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Columns sent by the blade
        |--------------------------------------------------------------------------
        */
        $requestedColumns = $request->input('columns', []);

        if (!is_array($requestedColumns)) {
            $requestedColumns = [];
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Validate requested columns
        |--------------------------------------------------------------------------
        */
        $selectedColumns = array_values(array_intersect(
            $requestedColumns,
            array_keys($availableColumns)
        ));

        /*
        |--------------------------------------------------------------------------
        | 6. Fall back to saved preference
        |--------------------------------------------------------------------------
        |
        | This report uses its own preference name:
        | members_list_all
        |
        */
        if (empty($selectedColumns)) {
            $preference = DB::table('user_table_preferences')
                ->where('user_cd', Auth::user()->user_cd)
                ->where('table_name', 'members_list_all')
                ->first();

            $selectedColumns = $defaultColumns;

            if ($preference && !empty($preference->visible_columns)) {
                $savedColumns = json_decode(
                    $preference->visible_columns,
                    true
                );

                if (is_array($savedColumns)) {
                    $savedColumns = array_values(array_intersect(
                        $savedColumns,
                        array_keys($availableColumns)
                    ));

                    if (!empty($savedColumns)) {
                        $selectedColumns = $savedColumns;
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Load members
        |--------------------------------------------------------------------------
        */
        $membersQuery = DB::table('members')
            ->select($selectedColumns);

        if ($status !== '') {
            $membersQuery->where(
                'Trans_flag',
                $status
            );
        }

        $members = $membersQuery
            ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 8. Status description for PDF heading
        |--------------------------------------------------------------------------
        */
        $statusNames = [
            ''  => 'All Status',
            'A' => 'Active',
            'N' => 'Noon',
            'H' => 'Hold',
            'G' => 'Guest',
            'D' => 'Discharged',
        ];

        $statusLabel = $statusNames[$status] ?? $status;

        /*
        |--------------------------------------------------------------------------
        | 9. PDF orientation
        |--------------------------------------------------------------------------
        |
        | Use landscape when many columns are selected.
        |
        */
        $orientation = count($selectedColumns) > 6
            ? 'landscape'
            : 'portrait';

        /*
        |--------------------------------------------------------------------------
        | 10. Generate PDF
        |--------------------------------------------------------------------------
        */
        $pdf = Pdf::loadView(
            'members.members_list_all_pdf',
            compact(
                'members',
                'availableColumns',
                'selectedColumns',
                'status',
                'statusLabel'
            )
        )->setPaper('a4', $orientation);

        return $pdf->stream('members-list.pdf');
    } // End Method

    // public function membersSignSheet(Request $request)
    // {

    //     $rows = DB::table('members')
    //         ->select(
    //             'ParticipantId',
    //             'lastname as last',
    //             'firstname as first',
    //             'Address1 as address1',
    //             'City as city'
    //         )
    //         ->where('Trans_flag', 'A')
    //         ->orderBy('ParticipantId')
    //         ->get();


    //     return view('members.members_census_sheet', [
    //         //'compname' => config('app.name', 'Company Name'),
    //         'compname' => config('company.comp_nm'),
    //         'title'    => 'Daily Attendance Record',
    //         'rows'     => $rows,
    //         'pdfUrl'   => route('members_census_sheet_pdf'),
    //     ]);
    // } // End Method

    public function membersSignSheet(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Detect current tenant database
        |--------------------------------------------------------------------------
        */

        //dd(DB::connection()->getDatabaseName());

        $databaseName = strtolower(
            DB::connection()->getDatabaseName()
        );

        $isAdcKor = $databaseName === 'adc_kor';


        /*
        |--------------------------------------------------------------------------
        | Get members
        |--------------------------------------------------------------------------
        */

        $query = DB::table('members')
            ->select(
                'ParticipantId',
                'lastname as last',
                'firstname as first',
                'Address1 as address1',
                'City as city'
            );


        /*
        |--------------------------------------------------------------------------
        | ADC_KOR only
        |--------------------------------------------------------------------------
        | comments contains Korean Name
        */

        if ($isAdcKor) {
            $query->addSelect('comments');
        }


        $rows = $query
            ->where('Trans_flag', 'A')
            ->orderBy('ParticipantId')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Choose Blade according to tenant
        |--------------------------------------------------------------------------
        */

        $viewName = $isAdcKor
            ? 'members.members_census_sheet_kor'
            : 'members.members_census_sheet';


        return view($viewName, [
            'compname' => config('company.comp_nm'),
            'title'    => 'Daily Attendance Record',
            'rows'     => $rows,
            'pdfUrl'   => route('members_census_sheet_pdf'),
        ]);
    } // End Method

    // public function membersSignSheetPdf(Request $request)
    // {
    //     // 1) Query data
    //     $rows = DB::table('members')
    //         ->select(
    //             'ParticipantId',
    //             'lastname as last',
    //             'firstname as first',
    //             'Address1 as address1',
    //             'City as city'
    //         )
    //         ->where('Trans_flag', 'A')
    //         ->orderBy('ParticipantId')
    //         ->get();

    //     // 2) Get organization-specific media folder
    //     $mediaFolder = session('media_folder');

    //     if (empty($mediaFolder)) {
    //         abort(403, 'Organization image folder is not configured.');
    //     }

    //     // Main tenant-specific member image folder
    //     $memberImageDir = public_path(
    //         "uploads/{$mediaFolder}/members/images"
    //     );

    //     // Tenant-specific thumbnail folder
    //     $thumbDir = public_path(
    //         "uploads/{$mediaFolder}/members/images/thumbs"
    //     );

    //     if (!is_dir($thumbDir)) {
    //         if (!mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
    //             abort(500, 'Unable to create member thumbnail directory.');
    //         }
    //     }

    //     // Shared fallback image
    //     $fallback = public_path('members/images/no_image.jpg');

    //     if (!is_file($fallback)) {
    //         abort(500, 'Fallback member image was not found.');
    //     }

    //     // 3) Local thumbnail creator
    //     $makeThumb = function (
    //         string $srcPath,
    //         string $dstPath,
    //         int $tw = 60,
    //         int $th = 60,
    //         int $quality = 70
    //     ): bool {
    //         if (!is_file($srcPath)) {
    //             return false;
    //         }

    //         if (!function_exists('imagecreatetruecolor')) {
    //             return false;
    //         }

    //         $info = @getimagesize($srcPath);

    //         if (!$info) {
    //             return false;
    //         }

    //         switch ($info[2]) {
    //             case IMAGETYPE_JPEG:
    //                 $src = @imagecreatefromjpeg($srcPath);
    //                 break;

    //             case IMAGETYPE_PNG:
    //                 $src = @imagecreatefrompng($srcPath);
    //                 break;

    //             case IMAGETYPE_GIF:
    //                 $src = @imagecreatefromgif($srcPath);
    //                 break;

    //             default:
    //                 return false;
    //         }

    //         if (!$src) {
    //             return false;
    //         }

    //         $sw = imagesx($src);
    //         $sh = imagesy($src);

    //         if ($sw <= 0 || $sh <= 0) {
    //             imagedestroy($src);
    //             return false;
    //         }

    //         $dst = imagecreatetruecolor($tw, $th);

    //         if (!$dst) {
    //             imagedestroy($src);
    //             return false;
    //         }

    //         // White background, useful when source is transparent PNG/GIF
    //         $white = imagecolorallocate($dst, 255, 255, 255);
    //         imagefill($dst, 0, 0, $white);

    //         // Center-crop and scale
    //         $srcAspect = $sw / max(1, $sh);
    //         $dstAspect = $tw / max(1, $th);

    //         if ($srcAspect > $dstAspect) {
    //             // Source is wider: crop left and right
    //             $cw = (int) round($sh * $dstAspect);
    //             $ch = $sh;
    //             $sx = (int) max(0, ($sw - $cw) / 2);
    //             $sy = 0;
    //         } else {
    //             // Source is taller: crop top and bottom
    //             $cw = $sw;
    //             $ch = (int) round($sw / $dstAspect);
    //             $sx = 0;
    //             $sy = (int) max(0, ($sh - $ch) / 2);
    //         }

    //         $copied = imagecopyresampled(
    //             $dst,
    //             $src,
    //             0,
    //             0,
    //             $sx,
    //             $sy,
    //             $tw,
    //             $th,
    //             $cw,
    //             $ch
    //         );

    //         $saved = false;

    //         if ($copied) {
    //             $saved = @imagejpeg($dst, $dstPath, $quality);
    //         }

    //         imagedestroy($src);
    //         imagedestroy($dst);

    //         return $saved && is_file($dstPath);
    //     };

    //     // 4) Resolve each member image and create/use thumbnail
    //     $rows = $rows->map(function ($m) use (
    //         $thumbDir,
    //         $fallback,
    //         $makeThumb,
    //         $memberImageDir
    //     ) {
    //         $id = trim((string) $m->ParticipantId);

    //         /*
    //         * Support both naming styles:
    //         * ParticipantId 051 may have source photo 051.jpg or 51.jpg.
    //         */
    //         $numericId = $id;

    //         if ($id !== '' && ctype_digit($id)) {
    //             $numericId = ltrim($id, '0');

    //             // Prevent an ID consisting only of zeroes from becoming blank
    //             if ($numericId === '') {
    //                 $numericId = '0';
    //             }
    //         }

    //         $baseNames = array_values(array_unique([
    //             $id,
    //             $numericId,
    //         ]));

    //         $extensions = [
    //             'jpg',
    //             'jpeg',
    //             'png',
    //             'gif',
    //             'JPG',
    //             'JPEG',
    //             'PNG',
    //             'GIF',
    //         ];

    //         $candidates = [];

    //         foreach ($baseNames as $baseName) {
    //             if ($baseName === '') {
    //                 continue;
    //             }

    //             foreach ($extensions as $extension) {
    //                 $candidates[] =
    //                     $memberImageDir .
    //                     DIRECTORY_SEPARATOR .
    //                     $baseName .
    //                     '.' .
    //                     $extension;
    //             }
    //         }

    //         // This part was missing in your current method
    //         $src = null;

    //         foreach ($candidates as $candidate) {
    //             if (is_file($candidate)) {
    //                 $src = $candidate;
    //                 break;
    //             }
    //         }

    //         // Use shared fallback if no member image exists
    //         if ($src === null) {
    //             $src = $fallback;
    //         }

    //         // Keep thumbnail name based on exact ParticipantId, such as 051.jpg
    //         $safeId = preg_replace('/[^A-Za-z0-9_-]/', '_', $id);

    //         if ($safeId === '') {
    //             $safeId = 'unknown';
    //         }

    //         $thumbPath = $thumbDir . DIRECTORY_SEPARATOR . $safeId . '.jpg';

    //         /*
    //         * Recreate thumbnail if:
    //         * - it does not exist, or
    //         * - source image is newer than existing thumbnail.
    //         */
    //         $needsThumbnail =
    //             !is_file($thumbPath) ||
    //             (
    //                 is_file($src) &&
    //                 filemtime($src) > filemtime($thumbPath)
    //             );

    //         if ($needsThumbnail) {
    //             if (is_file($thumbPath)) {
    //                 @unlink($thumbPath);
    //             }

    //             $made = $makeThumb($src, $thumbPath);

    //             if (!$made) {
    //                 $thumbPath = $src;
    //             }
    //         }

    //         if (!is_file($thumbPath)) {
    //             $thumbPath = $src;
    //         }

    //         // Windows-safe path for mPDF
    //         $normalizedPath = str_replace('\\', '/', $thumbPath);

    //         /*
    //         * On Windows:
    //         * C:/xampp/... becomes file:///C:/xampp/...
    //         */
    //         if (preg_match('/^[A-Za-z]:\//', $normalizedPath)) {
    //             $m->photo_uri = 'file:///' . $normalizedPath;
    //         } else {
    //             $m->photo_uri = 'file://' . $normalizedPath;
    //         }

    //         return $m;
    //     });

    //     // 5) Header and view data
    //     $compname = config('company.comp_nm');
    //     $title = 'Daily Attendance Record';

    //     // 6) Render lightweight PDF Blade
    //     $html = view('members.members_census_sheet_pdf', [
    //         'compname' => $compname,
    //         'title'    => $title,
    //         'rows'     => $rows,
    //     ])->render();

    //     // Ensure mPDF temporary directory exists
    //     $mpdfTempDir = storage_path('app/mpdf');

    //     if (!is_dir($mpdfTempDir)) {
    //         if (!mkdir($mpdfTempDir, 0775, true) && !is_dir($mpdfTempDir)) {
    //             abort(500, 'Unable to create mPDF temporary directory.');
    //         }
    //     }

    //     // 7) Configure mPDF
    //     $mpdf = new Mpdf([
    //         'tempDir'         => $mpdfTempDir,
    //         'format'          => 'A4-L',
    //         'margin_top'      => 18,
    //         'margin_bottom'   => 10,
    //         'margin_left'     => 8,
    //         'margin_right'    => 8,
    //         'isRemoteEnabled' => false,
    //     ]);

    //     $mpdf->simpleTables = true;
    //     $mpdf->packTableData = true;
    //     $mpdf->shrink_tables_to_fit = 1;

    //     // 8) Header and footer
    //     $mpdf->SetHTMLHeader('
    //         <table width="100%"
    //             style="border-bottom:1px solid #000;
    //                 font-family:sans-serif;
    //                 font-size:11px;
    //                 padding-bottom:2px;">
    //             <tr>
    //                 <td width="33%" style="text-align:left;">
    //                     <strong>' . htmlspecialchars($compname) . '</strong>
    //                 </td>
    //                 <td width="34%" style="text-align:center; font-weight:bold;">
    //                     ' . htmlspecialchars($title) . '
    //                 </td>
    //                 <td width="33%" style="text-align:right;">
    //                     <strong>Date:</strong> ____________________
    //                 </td>
    //             </tr>
    //         </table>
    //     ');

    //     $mpdf->SetHTMLFooter(
    //         '<div style="text-align:right; font-size:10px;">
    //             Page {PAGENO} of {nbpg}
    //         </div>'
    //     );

    //     // 9) Generate and save PDF
    //     if (ob_get_length()) {
    //         @ob_end_clean();
    //     }

    //     $mpdf->WriteHTML($html);

    //     $reportsDir = storage_path('app/reports');

    //     if (!is_dir($reportsDir)) {
    //         if (!mkdir($reportsDir, 0775, true) && !is_dir($reportsDir)) {
    //             abort(500, 'Unable to create reports directory.');
    //         }
    //     }

    //     $filename = 'members_census_' . now()->format('Ymd_His') . '.pdf';
    //     $filepath = $reportsDir . DIRECTORY_SEPARATOR . $filename;

    //     $mpdf->Output(
    //         $filepath,
    //         \Mpdf\Output\Destination::FILE
    //     );

    //     return response()->file($filepath, [
    //         'Content-Type'  => 'application/pdf',
    //         'Cache-Control' => 'private, max-age=0, must-revalidate',
    //         'Pragma'        => 'public',
    //     ]);

    // } // End Method

    // public function membersSignSheetPdf(Request $request)
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | 1. Retrieve active morning-shift members
    //     |--------------------------------------------------------------------------
    //     */

    //     $rows = DB::table('members')
    //         ->select(
    //             'ParticipantId',
    //             'lastname as last',
    //             'firstname as first',
    //             'Address1 as address1',
    //             'City as city',
    //             'insulin_yn'
    //         )
    //         ->where('Trans_flag', 'A')
    //         ->orderBy('ParticipantId')
    //         ->get();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 2. Get organization-specific media folder
    //     |--------------------------------------------------------------------------
    //     */

    //     $mediaFolder = session('media_folder');

    //     if (empty($mediaFolder)) {
    //         abort(403, 'Organization image folder is not configured.');
    //     }

    //     $memberImageDir = public_path(
    //         "uploads/{$mediaFolder}/members/images"
    //     );

    //     $thumbDir = public_path(
    //         "uploads/{$mediaFolder}/members/images/thumbs"
    //     );

    //     if (!is_dir($thumbDir)) {
    //         if (!mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
    //             abort(500, 'Unable to create member thumbnail directory.');
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Shared fallback image
    //     |--------------------------------------------------------------------------
    //     */

    //     $fallback = public_path('members/images/no_image.jpg');

    //     if (!is_file($fallback)) {
    //         abort(500, 'Fallback member image was not found.');
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 3. Local thumbnail creator
    //     |--------------------------------------------------------------------------
    //     */

    //     $makeThumb = function (
    //         string $srcPath,
    //         string $dstPath,
    //         int $tw = 90,
    //         int $th = 70,
    //         int $quality = 75
    //     ): bool {
    //         if (!is_file($srcPath)) {
    //             return false;
    //         }

    //         if (!function_exists('imagecreatetruecolor')) {
    //             return false;
    //         }

    //         $info = @getimagesize($srcPath);

    //         if (!$info) {
    //             return false;
    //         }

    //         switch ($info[2]) {
    //             case IMAGETYPE_JPEG:
    //                 $src = @imagecreatefromjpeg($srcPath);
    //                 break;

    //             case IMAGETYPE_PNG:
    //                 $src = @imagecreatefrompng($srcPath);
    //                 break;

    //             case IMAGETYPE_GIF:
    //                 $src = @imagecreatefromgif($srcPath);
    //                 break;

    //             default:
    //                 return false;
    //         }

    //         if (!$src) {
    //             return false;
    //         }

    //         $sw = imagesx($src);
    //         $sh = imagesy($src);

    //         if ($sw <= 0 || $sh <= 0) {
    //             imagedestroy($src);

    //             return false;
    //         }

    //         $dst = imagecreatetruecolor($tw, $th);

    //         if (!$dst) {
    //             imagedestroy($src);

    //             return false;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | White background for transparent PNG/GIF files
    //         |--------------------------------------------------------------------------
    //         */

    //         $white = imagecolorallocate($dst, 255, 255, 255);
    //         imagefill($dst, 0, 0, $white);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Center-crop and scale
    //         |--------------------------------------------------------------------------
    //         */

    //         $srcAspect = $sw / max(1, $sh);
    //         $dstAspect = $tw / max(1, $th);

    //         if ($srcAspect > $dstAspect) {
    //             /*
    //             | Source is wider: crop left and right.
    //             */

    //             $cropWidth = (int) round($sh * $dstAspect);
    //             $cropHeight = $sh;

    //             $sourceX = (int) max(0, ($sw - $cropWidth) / 2);
    //             $sourceY = 0;
    //         } else {
    //             /*
    //             | Source is taller: crop top and bottom.
    //             */

    //             $cropWidth = $sw;
    //             $cropHeight = (int) round($sw / $dstAspect);

    //             $sourceX = 0;
    //             $sourceY = (int) max(0, ($sh - $cropHeight) / 2);
    //         }

    //         $copied = imagecopyresampled(
    //             $dst,
    //             $src,
    //             0,
    //             0,
    //             $sourceX,
    //             $sourceY,
    //             $tw,
    //             $th,
    //             $cropWidth,
    //             $cropHeight
    //         );

    //         $saved = false;

    //         if ($copied) {
    //             $saved = @imagejpeg(
    //                 $dst,
    //                 $dstPath,
    //                 $quality
    //             );
    //         }

    //         imagedestroy($src);
    //         imagedestroy($dst);

    //         return $saved && is_file($dstPath);
    //     };

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 4. Resolve each member photo
    //     |--------------------------------------------------------------------------
    //     */

    //     $rows = $rows->map(function ($member) use (
    //         $thumbDir,
    //         $fallback,
    //         $makeThumb,
    //         $memberImageDir
    //     ) {
    //         $id = trim((string) $member->ParticipantId);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Support both photo naming styles
    //         |--------------------------------------------------------------------------
    //         |
    //         | Participant ID 051 may use:
    //         |
    //         | 051.jpg
    //         | 51.jpg
    //         |
    //         */

    //         $numericId = $id;

    //         if ($id !== '' && ctype_digit($id)) {
    //             $numericId = ltrim($id, '0');

    //             if ($numericId === '') {
    //                 $numericId = '0';
    //             }
    //         }

    //         $baseNames = array_values(
    //             array_unique([
    //                 $id,
    //                 $numericId,
    //             ])
    //         );

    //         $extensions = [
    //             'jpg',
    //             'jpeg',
    //             'png',
    //             'gif',
    //             'JPG',
    //             'JPEG',
    //             'PNG',
    //             'GIF',
    //         ];

    //         $candidates = [];

    //         foreach ($baseNames as $baseName) {
    //             if ($baseName === '') {
    //                 continue;
    //             }

    //             foreach ($extensions as $extension) {
    //                 $candidates[] =
    //                     $memberImageDir .
    //                     DIRECTORY_SEPARATOR .
    //                     $baseName .
    //                     '.' .
    //                     $extension;
    //             }
    //         }

    //         $sourcePhoto = null;

    //         foreach ($candidates as $candidate) {
    //             if (is_file($candidate)) {
    //                 $sourcePhoto = $candidate;
    //                 break;
    //             }
    //         }

    //         if ($sourcePhoto === null) {
    //             $sourcePhoto = $fallback;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Thumbnail filename uses the exact Participant ID
    //         |--------------------------------------------------------------------------
    //         */

    //         $safeId = preg_replace(
    //             '/[^A-Za-z0-9_-]/',
    //             '_',
    //             $id
    //         );

    //         if ($safeId === '') {
    //             $safeId = 'unknown';
    //         }

    //         $thumbPath =
    //             $thumbDir .
    //             DIRECTORY_SEPARATOR .
    //             $safeId .
    //             '_attendance.jpg';

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Recreate thumbnail when source photo is newer
    //         |--------------------------------------------------------------------------
    //         */

    //         $needsThumbnail =
    //             !is_file($thumbPath) ||
    //             (
    //                 is_file($sourcePhoto) &&
    //                 filemtime($sourcePhoto) > filemtime($thumbPath)
    //             );

    //         if ($needsThumbnail) {
    //             if (is_file($thumbPath)) {
    //                 @unlink($thumbPath);
    //             }

    //             $thumbnailCreated = $makeThumb(
    //                 $sourcePhoto,
    //                 $thumbPath
    //             );

    //             if (!$thumbnailCreated) {
    //                 $thumbPath = $sourcePhoto;
    //             }
    //         }

    //         if (!is_file($thumbPath)) {
    //             $thumbPath = $sourcePhoto;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Windows-safe local file path for mPDF
    //         |--------------------------------------------------------------------------
    //         */

    //         $normalizedPath = str_replace(
    //             '\\',
    //             '/',
    //             $thumbPath
    //         );

    //         if (preg_match('/^[A-Za-z]:\//', $normalizedPath)) {
    //             $member->photo_uri = 'file:///' . $normalizedPath;
    //         } else {
    //             $member->photo_uri = 'file://' . $normalizedPath;
    //         }

    //         return $member;
    //     });

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 5. Report heading
    //     |--------------------------------------------------------------------------
    //     */

    //     $companyName = config('company.comp_nm');
    //     $title = 'Daily Attendance Record';

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 6. Render PDF Blade
    //     |--------------------------------------------------------------------------
    //     */

    //     $html = view('members.members_census_sheet_pdf', [
    //         'compname' => $companyName,
    //         'title'    => $title,
    //         'rows'     => $rows,
    //     ])->render();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 7. Create mPDF temporary directory
    //     |--------------------------------------------------------------------------
    //     */

    //     $mpdfTempDir = storage_path('app/mpdf');

    //     if (!is_dir($mpdfTempDir)) {
    //         if (!mkdir($mpdfTempDir, 0775, true) && !is_dir($mpdfTempDir)) {
    //             abort(500, 'Unable to create mPDF temporary directory.');
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 8. Configure mPDF
    //     |--------------------------------------------------------------------------
    //     */

    //    $mpdf = new Mpdf([
    //         'tempDir'         => $mpdfTempDir,
    //         'format'          => 'A4-L',

    //         // Reserve enough room for the report header
    //         'margin_top'      => 16,

    //         // No footer is being used, so keep this small
    //         'margin_bottom'   => 4,

    //         'margin_left'     => 5,
    //         'margin_right'    => 5,
    //         'isRemoteEnabled' => false,
    //     ]);

    //     $mpdf->simpleTables = false;
    //     $mpdf->packTableData = true;
    //     $mpdf->shrink_tables_to_fit = 1;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 9. PDF header
    //     |--------------------------------------------------------------------------
    //     */

    //     $mpdf->SetHTMLHeader(
    //         '
    //         <table
    //             width="100%"
    //             cellpadding="0"
    //             cellspacing="0"
    //             style="
    //                 width:100%;
    //                 border-bottom:1px solid #000;
    //                 font-family:\'Microsoft Sans Serif\', \'MS Sans Serif\', Arial, sans-serif;
    //                 padding-bottom:2px;
    //             "
    //         >
    //             <tr>
    //                 <td
    //                     width="30%"
    //                     style="
    //                         text-align:left;
    //                         white-space:nowrap;
    //                         font-family:\'Microsoft Sans Serif\', \'MS Sans Serif\', Arial, sans-serif;
    //                         font-size:12pt;
    //                         font-weight:bold;
    //                         font-style:italic;
    //                         text-decoration:underline;
    //                         vertical-align:bottom;
    //                     "
    //                 >
    //                     ' .
    //                         htmlspecialchars(
    //                             (string) $companyName,
    //                             ENT_QUOTES,
    //                             'UTF-8'
    //                         ) .
    //                     '
    //                 </td>

    //                 <td
    //                     width="40%"
    //                     style="
    //                         text-align:center;
    //                         white-space:nowrap;
    //                         font-family:\'Microsoft Sans Serif\', \'MS Sans Serif\', Arial, sans-serif;
    //                         font-size:12pt;
    //                         font-weight:bold;
    //                         vertical-align:bottom;
    //                     "
    //                 >
    //                     ' .
    //                         htmlspecialchars(
    //                             $title,
    //                             ENT_QUOTES,
    //                             'UTF-8'
    //                         ) .
    //                     '
    //                 </td>

    //                 <td
    //                     width="30%"
    //                     style="
    //                         text-align:right;
    //                         white-space:nowrap;
    //                         font-family:\'Microsoft Sans Serif\', \'MS Sans Serif\', Arial, sans-serif;
    //                         font-size:12pt;
    //                         vertical-align:bottom;
    //                     "
    //                 >
    //                     Date: ____________________

    //                     &nbsp;&nbsp;&nbsp;

    //                     <span style="font-size:9pt;">
    //                         Page {PAGENO} of {nbpg}
    //                     </span>
    //                 </td>
    //             </tr>
    //         </table>
    //         '
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 10. PDF footer
    //     |--------------------------------------------------------------------------
    //     */

    //     $mpdf->SetHTMLFooter('');

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 11. Generate PDF
    //     |--------------------------------------------------------------------------
    //     */

    //     if (ob_get_length()) {
    //         @ob_end_clean();
    //     }

    //     $mpdf->WriteHTML($html);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 12. Save generated PDF
    //     |--------------------------------------------------------------------------
    //     */

    //     $reportsDir = storage_path('app/reports');

    //     if (!is_dir($reportsDir)) {
    //         if (!mkdir($reportsDir, 0775, true) && !is_dir($reportsDir)) {
    //             abort(500, 'Unable to create reports directory.');
    //         }
    //     }

    //     $filename =
    //         'members_attendance_signature_' .
    //         now()->format('Ymd_His') .
    //         '.pdf';

    //     $filepath =
    //         $reportsDir .
    //         DIRECTORY_SEPARATOR .
    //         $filename;

    //     $mpdf->Output(
    //         $filepath,
    //         \Mpdf\Output\Destination::FILE
    //     );

    //     return response()->file($filepath, [
    //         'Content-Type'  => 'application/pdf',
    //         'Cache-Control' => 'private, max-age=0, must-revalidate',
    //         'Pragma'        => 'public',
    //     ]);

    // } // End Method

    public function membersSignSheetPdf(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | 1. Retrieve active Morning members
    |--------------------------------------------------------------------------
    */

        /*
    |--------------------------------------------------------------------------
    | 1. Detect current tenant
    |--------------------------------------------------------------------------
    */

        $databaseName = strtolower(
            DB::connection()->getDatabaseName()
        );

        $isAdcKor = $databaseName === 'adc_kor';


        /*
        |--------------------------------------------------------------------------
        | 2. Retrieve active Morning members
        |--------------------------------------------------------------------------
        */

        $query = DB::table('members')
            ->select(
                'ParticipantId',
                'lastname as last',
                'firstname as first',
                'Address1 as address1',
                'City as city',
                'insulin_yn'
            );


        /*
        |--------------------------------------------------------------------------
        | ADC_KOR only
        |--------------------------------------------------------------------------
        */

        if ($isAdcKor) {
            $query->addSelect('comments');
        }


        $rows = $query
            ->where('Trans_flag', 'A')
            ->orderBy('ParticipantId')
            ->get();

        //dd($rows->pluck('comments')->take(10));

        // $rows = DB::table('members')
        //     ->select(
        //         'ParticipantId',
        //         'lastname as last',
        //         'firstname as first',
        //         'Address1 as address1',
        //         'City as city',
        //         'insulin_yn'
        //     )
        //     ->where('Trans_flag', 'A')
        //     ->orderBy('ParticipantId')
        //     ->get();

        /*
    |--------------------------------------------------------------------------
    | 2. Tenant-specific image locations
    |--------------------------------------------------------------------------
    */

        $mediaFolder = session('media_folder');

        if (empty($mediaFolder)) {
            abort(403, 'Organization image folder is not configured.');
        }

        $memberImageDir = public_path(
            "uploads/{$mediaFolder}/members/images"
        );

        $thumbDir = public_path(
            "uploads/{$mediaFolder}/members/images/thumbs"
        );

        if (!is_dir($thumbDir)) {
            if (!mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
                abort(500, 'Unable to create member thumbnail directory.');
            }
        }

        $fallback = public_path('members/images/no_image.jpg');

        if (!is_file($fallback)) {
            abort(500, 'Fallback member image was not found.');
        }

        /*
    |--------------------------------------------------------------------------
    | 3. Thumbnail generator
    |--------------------------------------------------------------------------
    */

        $makeThumb = function (
            string $sourcePath,
            string $destinationPath,
            int $targetWidth = 90,
            int $targetHeight = 72,
            int $quality = 75
        ): bool {
            if (!is_file($sourcePath)) {
                return false;
            }

            if (!function_exists('imagecreatetruecolor')) {
                return false;
            }

            $imageInfo = @getimagesize($sourcePath);

            if (!$imageInfo) {
                return false;
            }

            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = @imagecreatefromjpeg($sourcePath);
                    break;

                case IMAGETYPE_PNG:
                    $sourceImage = @imagecreatefrompng($sourcePath);
                    break;

                case IMAGETYPE_GIF:
                    $sourceImage = @imagecreatefromgif($sourcePath);
                    break;

                default:
                    return false;
            }

            if (!$sourceImage) {
                return false;
            }

            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);

            if ($sourceWidth <= 0 || $sourceHeight <= 0) {
                imagedestroy($sourceImage);

                return false;
            }

            $destinationImage = imagecreatetruecolor(
                $targetWidth,
                $targetHeight
            );

            if (!$destinationImage) {
                imagedestroy($sourceImage);

                return false;
            }

            /*
        |--------------------------------------------------------------------------
        | White background
        |--------------------------------------------------------------------------
        */

            $white = imagecolorallocate(
                $destinationImage,
                255,
                255,
                255
            );

            imagefill(
                $destinationImage,
                0,
                0,
                $white
            );

            /*
        |--------------------------------------------------------------------------
        | Center crop
        |--------------------------------------------------------------------------
        */

            $sourceAspect = $sourceWidth / max(1, $sourceHeight);
            $targetAspect = $targetWidth / max(1, $targetHeight);

            if ($sourceAspect > $targetAspect) {
                $cropWidth = (int) round(
                    $sourceHeight * $targetAspect
                );

                $cropHeight = $sourceHeight;
                $sourceX = (int) max(
                    0,
                    ($sourceWidth - $cropWidth) / 2
                );

                $sourceY = 0;
            } else {
                $cropWidth = $sourceWidth;

                $cropHeight = (int) round(
                    $sourceWidth / $targetAspect
                );

                $sourceX = 0;

                $sourceY = (int) max(
                    0,
                    ($sourceHeight - $cropHeight) / 2
                );
            }

            $copied = imagecopyresampled(
                $destinationImage,
                $sourceImage,
                0,
                0,
                $sourceX,
                $sourceY,
                $targetWidth,
                $targetHeight,
                $cropWidth,
                $cropHeight
            );

            $saved = false;

            if ($copied) {
                $saved = @imagejpeg(
                    $destinationImage,
                    $destinationPath,
                    $quality
                );
            }

            imagedestroy($sourceImage);
            imagedestroy($destinationImage);

            return $saved && is_file($destinationPath);
        };

        /*
    |--------------------------------------------------------------------------
    | 4. Resolve member photos and convert to Base64
    |--------------------------------------------------------------------------
    |
    | Base64 avoids Windows path and DomPDF chroot problems.
    |
    */

        $rows = $rows->map(function ($member) use (
            $memberImageDir,
            $thumbDir,
            $fallback,
            $makeThumb
        ) {
            $participantId = trim(
                (string) $member->ParticipantId
            );

            $numericId = $participantId;

            if (
                $participantId !== '' &&
                ctype_digit($participantId)
            ) {
                $numericId = ltrim($participantId, '0');

                if ($numericId === '') {
                    $numericId = '0';
                }
            }

            $baseNames = array_values(
                array_unique([
                    $participantId,
                    $numericId,
                ])
            );

            $extensions = [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'JPG',
                'JPEG',
                'PNG',
                'GIF',
            ];

            $sourcePhoto = null;

            foreach ($baseNames as $baseName) {
                if ($baseName === '') {
                    continue;
                }

                foreach ($extensions as $extension) {
                    $candidate =
                        $memberImageDir .
                        DIRECTORY_SEPARATOR .
                        $baseName .
                        '.' .
                        $extension;

                    if (is_file($candidate)) {
                        $sourcePhoto = $candidate;
                        break 2;
                    }
                }
            }

            if ($sourcePhoto === null) {
                $sourcePhoto = $fallback;
            }

            $safeId = preg_replace(
                '/[^A-Za-z0-9_-]/',
                '_',
                $participantId
            );

            if ($safeId === '') {
                $safeId = 'unknown';
            }

            $thumbPath =
                $thumbDir .
                DIRECTORY_SEPARATOR .
                $safeId .
                '_attendance_dompdf.jpg';

            $thumbnailNeedsUpdate =
                !is_file($thumbPath) ||
                (
                    is_file($sourcePhoto) &&
                    filemtime($sourcePhoto) > filemtime($thumbPath)
                );

            if ($thumbnailNeedsUpdate) {
                if (is_file($thumbPath)) {
                    @unlink($thumbPath);
                }

                $thumbnailCreated = $makeThumb(
                    $sourcePhoto,
                    $thumbPath
                );

                if (!$thumbnailCreated) {
                    $thumbPath = $sourcePhoto;
                }
            }

            if (!is_file($thumbPath)) {
                $thumbPath = $sourcePhoto;
            }

            /*
        |--------------------------------------------------------------------------
        | Convert image to Base64 data URI
        |--------------------------------------------------------------------------
        */

            $member->photo_uri = null;

            if (is_file($thumbPath)) {
                $imageContents = @file_get_contents($thumbPath);

                if ($imageContents !== false) {
                    $mimeType = @mime_content_type($thumbPath);

                    if (!$mimeType) {
                        $mimeType = 'image/jpeg';
                    }

                    $member->photo_uri =
                        'data:' .
                        $mimeType .
                        ';base64,' .
                        base64_encode($imageContents);
                }
            }

            return $member;
        });

        /*
    |--------------------------------------------------------------------------
    | 5. Report information
    |--------------------------------------------------------------------------
    */

        $companyName = config('company.comp_nm');
        $title = 'Daily Attendance Record';

        /*
    |--------------------------------------------------------------------------
    | 6. Prepare fixed pages
    |--------------------------------------------------------------------------
    |
    | Each page contains exactly 17 lines.
    |
    */

        $rowsPerPage = 16;
        $pages = $rows->chunk($rowsPerPage);

        if ($pages->isEmpty()) {
            $pages = collect([
                collect()
            ]);
        }

        $totalPages = $pages->count();

        /*
    |--------------------------------------------------------------------------
    | 7. Generate PDF using DomPDF
    |--------------------------------------------------------------------------
    */

        // $pdf = Pdf::loadView(
        //     'members.members_census_sheet_pdf',
        //     [
        //         'companyName' => $companyName,
        //         'title'       => $title,
        //         'pages'       => $pages,
        //         'totalPages'  => $totalPages,
        //         'rowsPerPage' => $rowsPerPage,
        //     ]
        // );

        // $pdfView = $isAdcKor
        //     ? 'members.members_census_sheet_pdf_kor'
        //     : 'members.members_census_sheet_pdf';


        // $pdf = Pdf::loadView(
        //     $pdfView,
        //     [
        //         'companyName' => $companyName,
        //         'title'       => $title,
        //         'pages'       => $pages,
        //         'totalPages'  => $totalPages,
        //         'rowsPerPage' => $rowsPerPage,
        //     ]
        // );

        // $pdf->setPaper('a4', 'landscape');

        // $pdf->setOptions([
        //     'isRemoteEnabled'           => false,
        //     'isHtml5ParserEnabled'      => true,
        //     'isPhpEnabled'              => false,
        //     'defaultFont'               => 'Arial',
        //     'isFontSubsettingEnabled'   => true,
        //     'dpi'                       => 96,
        //     'fontHeightRatio'           => 1.0,
        // ]);

        // $filename =
        //     'members_attendance_signature_' .
        //     now()->format('Ymd_His') .
        //     '.pdf';

        // return $pdf->stream($filename);

        /*
|--------------------------------------------------------------------------
| 7. Generate PDF
|--------------------------------------------------------------------------
*/

        if ($isAdcKor) {

            $html = view(
                'members.members_census_sheet_pdf_kor',
                [
                    'companyName' => $companyName,
                    'title'       => $title,
                    'pages'       => $pages,
                    'totalPages'  => $totalPages,
                    'rowsPerPage' => $rowsPerPage,
                ]
            )->render();

            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',

                'margin_left'   => 0,
                'margin_right'  => 0,
                'margin_top'    => 5,
                'margin_bottom' => 5,

                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts'),
                ]),

                'fontdata' => $fontData + [
                    'notosanskr' => [
                        'R' => 'NotoSansKR-Regular.ttf',
                        'B' => 'NotoSansKR-Bold.ttf',
                    ],
                ],

                'default_font' => 'sans-serif',
            ]);

            $mpdf->SetAutoPageBreak(false);

            $mpdf->WriteHTML($html);

            $filename =
                'members_attendance_signature_' .
                now()->format('Ymd_His') .
                '.pdf';

            return response(
                $mpdf->Output('', 'S'),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' =>
                    'inline; filename="' . $filename . '"',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Non-Korean tenants: existing DomPDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'members.members_census_sheet_pdf',
            [
                'companyName' => $companyName,
                'title'       => $title,
                'pages'       => $pages,
                'totalPages'  => $totalPages,
                'rowsPerPage' => $rowsPerPage,
            ]
        );

        $pdf->setPaper('a4', 'landscape');

        $pdf->setOptions([
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => false,
            'defaultFont'          => 'Arial',
            'dpi'                  => 96,
            'fontHeightRatio'      => 1.0,
        ]);

        $filename =
            'members_attendance_signature_' .
            now()->format('Ymd_His') .
            '.pdf';

        return $pdf->stream($filename);
    }

    private function normalizeCoverageValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : strtoupper($value);
    } // End Method

    private function normalizeCoverageDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    } // End Method

    private function buildCoveragePayload(array $data): array
    {
        return [
            'HMO_Enrolled'      => $this->normalizeCoverageValue($data['HMO_Enrolled'] ?? null),
            'medicaid_HMO_id'   => $this->normalizeCoverageValue($data['medicaid_HMO_id'] ?? null),
            'PrimaryDiagnosis1' => $this->normalizeCoverageValue($data['PrimaryDiagnosis1'] ?? null),
            'SecondaryDiag1'    => $this->normalizeCoverageValue($data['SecondaryDiag1'] ?? null),
            'medicaid_PA_no'    => $this->normalizeCoverageValue($data['medicaid_PA_no'] ?? null),
            'medicaid_PA_Start' => $this->normalizeCoverageDate($data['medicaid_PA_Start'] ?? null),
            'medicaid_PA_End'   => $this->normalizeCoverageDate($data['medicaid_PA_End'] ?? null),
        ];
    } // End Method

    private function hasAnyCoverageValue(array $coverage): bool
    {
        foreach ($coverage as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    } // End Method

    private function coverageChanged($currentRow, array $newCoverage): bool
    {
        if (!$currentRow) {
            return $this->hasAnyCoverageValue($newCoverage);
        }

        $currentCoverage = [
            'HMO_Enrolled'      => $this->normalizeCoverageValue($currentRow->HMO_Enrolled ?? null),
            'medicaid_HMO_id'   => $this->normalizeCoverageValue($currentRow->medicaid_HMO_id ?? null),
            'PrimaryDiagnosis1' => $this->normalizeCoverageValue($currentRow->PrimaryDiagnosis1 ?? null),
            'SecondaryDiag1'    => $this->normalizeCoverageValue($currentRow->SecondaryDiag1 ?? null),
            'medicaid_PA_no'    => $this->normalizeCoverageValue($currentRow->medicaid_PA_no ?? null),
            'medicaid_PA_Start' => $this->normalizeCoverageDate($currentRow->medicaid_PA_Start ?? null),
            'medicaid_PA_End'   => $this->normalizeCoverageDate($currentRow->medicaid_PA_End ?? null),
        ];

        return $currentCoverage != $newCoverage;
    } // End Method

    private function syncCoverageHistory(string $participantId, array $data): void
    {
        $participantId = trim($participantId);
        $newCoverage = $this->buildCoveragePayload($data);

        // nothing to save in history
        if (!$this->hasAnyCoverageValue($newCoverage)) {
            return;
        }

        $current = DB::table('member_coverage_history')
            ->where('ParticipantId', $participantId)
            ->where('is_current', 'Y')
            ->orderByDesc('id')
            ->first();

        if (!$this->coverageChanged($current, $newCoverage)) {
            return;
        }

        $effectiveFrom = $newCoverage['medicaid_PA_Start'] ?: now()->toDateString();

        DB::transaction(function () use ($participantId, $newCoverage, $current, $effectiveFrom) {

            if ($current) {
                $closeDate = Carbon::parse($effectiveFrom)->subDay()->format('Y-m-d');

                DB::table('member_coverage_history')
                    ->where('id', $current->id)
                    ->update([
                        'effective_to' => $closeDate,
                        'is_current'   => 'N',
                        'updated_at'   => now(),
                    ]);
            }

            DB::table('member_coverage_history')->insert([
                'ParticipantId'      => $participantId,
                'HMO_Enrolled'       => $newCoverage['HMO_Enrolled'],
                'medicaid_HMO_id'    => $newCoverage['medicaid_HMO_id'],
                'PrimaryDiagnosis1'  => $newCoverage['PrimaryDiagnosis1'],
                'SecondaryDiag1'     => $newCoverage['SecondaryDiag1'],
                'medicaid_PA_no'     => $newCoverage['medicaid_PA_no'],
                'medicaid_PA_Start'  => $newCoverage['medicaid_PA_Start'],
                'medicaid_PA_End'    => $newCoverage['medicaid_PA_End'],
                'effective_from'     => $effectiveFrom,
                'effective_to'       => null,
                'is_current'         => 'Y',
                'remarks'            => 'Auto-saved from members update',
                'user_cd'            => $data['user_cd'] ?? Auth::user()->user_cd ?? null,
                'company_id'         => $data['company_id'] ?? config('company.company_id') ?? null,
                'update_dt'          => now(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        });
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Synchronize Member To Billing Master
    |--------------------------------------------------------------------------
    |
    | IMPORTANT BUSINESS RULE:
    |
    | A member may be created before insurance, authorization or diagnosis
    | information is available. Therefore this routine does NOT perform claim
    | readiness validation and does NOT require those fields.
    |
    | It simply mirrors the billing-related fields that currently exist in
    | members into billing_members.
    |
    | Later, when staff updates the member after approval / authorization,
    | the same billing_members record is updated automatically.
    |
    | Doctor / physician information is intentionally NOT synchronized.
    |--------------------------------------------------------------------------
    */

    private function syncBillingMember(string $participantId): void
    {
        $participantId = trim((string) $participantId);

        if ($participantId === '') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Read Member From CURRENT Tenant Database
        |--------------------------------------------------------------------------
        |
        | DB::table() uses the current/default database.
        |
        | Examples:
        |
        | project3 -> Morning / Noon
        | adc_kor  -> Afternoon
        |
        */

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | 2. Determine Source Database / System
        |--------------------------------------------------------------------------
        */

        $currentDatabase = strtolower(
            trim(
                (string) DB::connection()->getDatabaseName()
            )
        );

        $isAdcKor = ($currentDatabase === 'adc_kor');


        /*
        |--------------------------------------------------------------------------
        | 3. Source System
        |--------------------------------------------------------------------------
        */

        $sourceSystem = $isAdcKor
            ? 'ADC_KOR'
            : 'ADC';


        /*
        |--------------------------------------------------------------------------
        | 4. Member Status
        |--------------------------------------------------------------------------
        |
        | Trans_flag remains the member's operational status.
        |
        | A = Active / Morning
        | N = Noon
        | G = Guest
        | H = Hold
        | D = Discharged
        |
        */

        $memberStatus = strtoupper(
            trim(
                (string) ($member->Trans_flag ?? '')
            )
        );


        /*
        |--------------------------------------------------------------------------
        | 5. Billing Source Shift
        |--------------------------------------------------------------------------
        |
        | project3:
        |     A -> Morning
        |     N -> Noon
        |
        | ADC_KOR:
        |     All billing members belong to Afternoon,
        |     therefore source_shift = P.
        |
        */

        if ($isAdcKor) {

            $sourceShift = 'P';
        } else {

            $sourceShift = $memberStatus;
        }


        /*
        |--------------------------------------------------------------------------
        | 6. Active / Inactive
        |--------------------------------------------------------------------------
        |
        | Do NOT delete billing members when they go Hold or Discharged.
        | Historical claims and ERA information must remain intact.
        |--------------------------------------------------------------------------
        */

        $active = in_array(
            $memberStatus,
            ['A', 'N', 'G'],
            true
        ) ? 1 : 0;


        /*
        |--------------------------------------------------------------------------
        | 7. Billing Data
        |--------------------------------------------------------------------------
        |
        | Insurance / PA / diagnosis fields are intentionally nullable.
        |
        | A member may be entered before insurance authorization is available.
        | Later updates will automatically refresh this same billing record.
        |
        | Doctor / physician information is intentionally excluded.
        |--------------------------------------------------------------------------
        */

        $billingData = [

            /*
            |--------------------------------------------------------------------------
            | Demographics
            |--------------------------------------------------------------------------
            */

            'FirstName' =>
            $member->FirstName,

            'LastName' =>
            $member->LastName,

            'MiddleName' =>
            $member->MiddleName,

            'DateOfBirth' =>
            $member->DateOfBirth,

            'Sex' =>
            $member->Sex,


            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'Address1' =>
            $member->Address1,

            'Address2' =>
            $member->Address2,

            'City' =>
            $member->City,

            'State' =>
            $member->State,

            'ZipCode' =>
            $member->ZipCode,


            /*
            |--------------------------------------------------------------------------
            | Medicaid / Medicare Numbers
            |--------------------------------------------------------------------------
            */

            'MedicaidNo' =>
            $member->MedicaidNo,

            'medicare_no' =>
            $member->medicare_no,

            'new_medicare_no' =>
            $member->new_medicare_no,


            /*
            |--------------------------------------------------------------------------
            | Current Medicaid HMO / Authorization
            |--------------------------------------------------------------------------
            */

            'HMO_Enrolled' =>
            $member->HMO_Enrolled,

            'HMO_Enrolled_date' =>
            $member->HMO_Enrolled_date,

            'medicaid_HMO_id' =>
            $member->medicaid_HMO_id,

            'medicaid_HMO_grp' =>
            $member->medicaid_HMO_grp,

            'medicaid_PA_no' =>
            $member->medicaid_PA_no,

            'medicaid_PA_Start' =>
            $member->medicaid_PA_Start,

            'medicaid_PA_End' =>
            $member->medicaid_PA_End,


            /*
            |--------------------------------------------------------------------------
            | Medicare HMO / Authorization
            |--------------------------------------------------------------------------
            */

            'medicare_HMO_Enrolled' =>
            $member->medicare_HMO_Enrolled,

            'medicare_HMO_id' =>
            $member->medicare_HMO_id,

            'medicare_HMO_grp' =>
            $member->medicare_HMO_grp,

            'medicare_PA_no' =>
            $member->medicare_PA_no,

            'medicare_PA_Start' =>
            $member->medicare_PA_Start,

            'medicare_PA_End' =>
            $member->medicare_PA_End,


            /*
            |--------------------------------------------------------------------------
            | Renewal Coverage #1
            |--------------------------------------------------------------------------
            */

            'renew1_HMO_enrolled' =>
            $member->renew1_HMO_enrolled,

            'renew1_HMO_id' =>
            $member->renew1_HMO_id,

            'renew1_HMO_grp' =>
            $member->renew1_HMO_grp,

            'renew1_PA_no' =>
            $member->renew1_PA_no,

            'renew1_PA_Start' =>
            $member->renew1_PA_Start,

            'renew1_PA_End' =>
            $member->renew1_PA_End,


            /*
            |--------------------------------------------------------------------------
            | Renewal Coverage #2
            |--------------------------------------------------------------------------
            */

            'renew2_HMO_enrolled' =>
            $member->renew2_HMO_enrolled,

            'renew2_HMO_id' =>
            $member->renew2_HMO_id,

            'renew2_HMO_grp' =>
            $member->renew2_HMO_grp,

            'renew2_PA_no' =>
            $member->renew2_PA_no,

            'renew2_PA_Start' =>
            $member->renew2_PA_Start,

            'renew2_PA_End' =>
            $member->renew2_PA_End,


            /*
            |--------------------------------------------------------------------------
            | Diagnosis
            |--------------------------------------------------------------------------
            */

            'PrimaryDiagnosis1' =>
            $member->PrimaryDiagnosis1,

            'PrimaryDiagnosis2' =>
            $member->PrimaryDiagnosis2,

            'PrimaryDiagnosis3' =>
            $member->PrimaryDiagnosis3,

            'PrimaryDiagnosis4' =>
            $member->PrimaryDiagnosis4,

            'SecondaryDiag1' =>
            $member->SecondaryDiag1,

            'SecondaryDiag2' =>
            $member->SecondaryDiag2,

            'SecondaryDiag3' =>
            $member->SecondaryDiag3,

            'SecondaryDiag4' =>
            $member->SecondaryDiag4,


            /*
            |--------------------------------------------------------------------------
            | Billing Control
            |--------------------------------------------------------------------------
            */

            'source_shift' =>
            $sourceShift,

            'source_system' =>
            $sourceSystem,

            'active' =>
            $active,

            'Comments' =>
            $member->Comments,

            'updated_at' =>
            now(),

        ];


        /*
        |--------------------------------------------------------------------------
        | 8. Select Billing Database
        |--------------------------------------------------------------------------
        |
        | MULTI_TENANT_ENABLED=false
        |     -> current/default database
        |
        | MULTI_TENANT_ENABLED=true
        |     -> fixed central billing database (project3)
        |--------------------------------------------------------------------------
        */

        $billingDb = $this->billingDb();


        /*
        |--------------------------------------------------------------------------
        | 9. Update Existing Billing Member Or Insert New One
        |--------------------------------------------------------------------------
        */

        $existingBillingMember = $billingDb
            ->table('billing_members')
            ->where('ParticipantId', $participantId)
            ->first();


        if ($existingBillingMember) {

            $billingDb
                ->table('billing_members')
                ->where('ParticipantId', $participantId)
                ->update($billingData);
        } else {

            $billingDb
                ->table('billing_members')
                ->insert(
                    array_merge(
                        [
                            'ParticipantId' =>
                            $participantId,

                            'created_at' =>
                            now(),
                        ],
                        $billingData
                    )
                );
        }
    } // End Method

    public function importForm()
    {
        return view('members.members_import');
    } // End Method

    public function importPreview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx',
        ]);

        $file = $request->file('excel_file');

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return back()->with('error', 'Excel file does not contain data.');
        }

        $headerRow = array_shift($rows);

        $headers = [];
        foreach ($headerRow as $col => $header) {
            $headers[$col] = trim((string) $header);
        }

        $map = [
            'Par#'             => 'ParticipantId',
            'Last Name'        => 'LastName',
            'First Name'       => 'FirstName',
            'Mi'               => 'MiddleName',
            'Address1'         => 'Address1',
            'Address2'         => 'Address2',
            'City'             => 'City',
            'State'            => 'State',
            'ZipCode'          => 'ZipCode',
            'Shift'            => 'Shift',
            'phone1'           => 'Phone1',
            'Status'           => 'Trans_flag',
            'Insurance Co'     => 'HMO_Enrolled',
            'Member ID'        => 'medicaid_HMO_id',
            'Auth No.'         => 'medicaid_PA_no',
            'Auth Start Dt'    => 'medicaid_PA_Start',
            'Auth End Dt'      => 'medicaid_PA_End',
            'Principal ICD Cd' => 'PrimaryDiagnosis1',
            'Secondary ICD Cd' => 'SecondaryDiag1',
            'DOB'              => 'DateOfBirth',
            'SS #'             => 'SSNo',
            'Sex'              => 'Sex',
        ];

        $batchId = (string) Str::uuid();

        DB::table('member_import_previews')
            ->where('created_at', '<', now()->subDay())
            ->delete();

        $maxExistingId = DB::table('members')
            ->selectRaw('MAX(CAST(ParticipantId AS UNSIGNED)) as max_id')
            ->where('Trans_flag', 'A')
            ->value('max_id');

        $nextParticipantId = $maxExistingId ? ((int) $maxExistingId + 1) : 1001;

        foreach ($rows as $index => $row) {

            $emptyRow = true;
            foreach ($row as $cellValue) {
                if (trim((string) $cellValue) !== '') {
                    $emptyRow = false;
                    break;
                }
            }

            if ($emptyRow) {
                continue;
            }

            $data = [
                'batch_id'      => $batchId,
                'row_no'        => $index + 2,
                'ParticipantId' => (string) $nextParticipantId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            $nextParticipantId++;

            foreach ($headers as $col => $excelHeader) {
                if (isset($map[$excelHeader])) {
                    $field = $map[$excelHeader];

                    if ($field === 'ParticipantId') {
                        continue;
                    }

                    $value = $row[$col] ?? null;
                    if (in_array($field, ['DateOfBirth', 'medicaid_PA_Start', 'medicaid_PA_End'])) {
                        $value = $this->excelDateToMysql($value);
                    } else {
                        $value = trim((string)$value);
                        if ($value === '') {
                            $value = null;
                        } else {

                            // Clean phone and SSN: keep numbers only
                            if (in_array($field, ['Phone1', 'SSNo'])) {
                                $value = preg_replace('/\D+/', '', $value);
                            }
                            // Do NOT uppercase these fields
                            $excludeUppercase = [
                                'Phone1',
                                'SSNo',
                                'Shift',
                                'medicaid_HMO_id',
                                'ZipCode',
                            ];

                            if (!in_array($field, $excludeUppercase)) {
                                $value = mb_strtoupper($value, 'UTF-8');
                            }
                        }
                    }
                    $data[$field] = $value;
                }
            }

            $errors = [];

            if (empty($data['LastName'])) {
                $errors[] = 'Last Name is missing';
            }

            if (empty($data['FirstName'])) {
                $errors[] = 'First Name is missing';
            }

            if (!empty($data['ParticipantId'])) {
                $exists = DB::table('members')
                    ->where('ParticipantId', $data['ParticipantId'])
                    ->exists();

                if ($exists) {
                    $errors[] = 'Generated ParticipantId already exists';
                }
            }

            if (!empty($errors)) {
                $data['import_status'] = 'ERROR';
                $data['error_message'] = implode(', ', $errors);
            } else {
                $data['import_status'] = 'READY';
                $data['error_message'] = null;
            }

            DB::table('member_import_previews')->insert($data);
        }

        return redirect()
            ->route('members.import.form', ['batch_id' => $batchId])
            ->with('success', 'Excel file loaded. Please review before importing.');
    } // End Method

    public function importConfirm(Request $request)
    {
        $request->validate([
            'batch_id' => 'required',
        ]);

        $batchId = $request->batch_id;

        $rows = DB::table('member_import_previews')
            ->where('batch_id', $batchId)
            ->where('import_status', 'READY')
            ->orderBy('row_no')
            ->get();

        if ($rows->isEmpty()) {
            return back()->with('error', 'No valid rows found to import.');
        }

        DB::transaction(function () use ($rows, $batchId) {

            foreach ($rows as $row) {

                $exists = DB::table('members')
                    ->where('ParticipantId', $row->ParticipantId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('members')->insert([
                    'ParticipantId'       => $row->ParticipantId,
                    'LastName'            => $row->LastName,
                    'FirstName'           => $row->FirstName,
                    'MiddleName'          => $row->MiddleName,
                    'Address1'            => $row->Address1,
                    'Address2'            => $row->Address2,
                    'City'                => $row->City,
                    'State'               => $row->State,
                    'ZipCode'             => $row->ZipCode,
                    'Shift'               => $row->Shift,
                    'Phone1'              => $row->Phone1,
                    'Trans_flag'          => $row->Trans_flag,
                    'HMO_Enrolled'        => $row->HMO_Enrolled,
                    'medicaid_HMO_id'     => $row->medicaid_HMO_id,
                    'medicaid_PA_no'      => $row->medicaid_PA_no,
                    'medicaid_PA_Start'   => $row->medicaid_PA_Start,
                    'medicaid_PA_End'     => $row->medicaid_PA_End,
                    'PrimaryDiagnosis1'   => $row->PrimaryDiagnosis1,
                    'SecondaryDiag1'      => $row->SecondaryDiag1,
                    'DateOfBirth'         => $row->DateOfBirth,
                    'SSNo'                => $row->SSNo,
                    'Sex'                 => $row->Sex,
                    'company_id'          => 'DS02',
                    'update_flag'         => 'Y',
                    'user_cd'             => Auth::user()->user_cd ?? Auth::user()->name ?? 'SYSTEM',
                    'update_dt'           => now(),

                    //'created_at'          => now(),
                    //'updated_at'          => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Keep Billing Master In Sync For Excel Imports
                |--------------------------------------------------------------------------
                */

                $this->syncBillingMember(
                    $row->ParticipantId
                );
            }

            DB::table('member_import_previews')
                ->where('batch_id', $batchId)
                ->delete();
        });

        return redirect()
            ->route('members.dashboard')
            ->with('success', 'Members imported successfully.');
    } // End Method

    public function importCancel(Request $request)
    {
        $request->validate([
            'batch_id' => 'required',
        ]);

        DB::table('member_import_previews')
            ->where('batch_id', $request->batch_id)
            ->delete();

        return redirect()
            ->route('members.import.form')
            ->with('success', 'Import cancelled.');
    } // End Method

    private function excelDateToMysql($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    } // End Method


    public function showImportGuide()
    {
        $filePath = public_path('docs/Members_Import_Guide.pdf');

        if (!file_exists($filePath)) {
            abort(404, 'Import guide not found.');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Members_Import_Guide.pdf"',
        ]);
    } // End Method

    public function getTablePreferences()
    {
        $userCd = Auth::user()->user_cd;

        $preference = DB::table('user_table_preferences')
            ->where('user_cd', $userCd)
            ->where('table_name', 'members')
            ->first();

        $columns = [];

        if ($preference && !empty($preference->visible_columns)) {
            $columns = json_decode($preference->visible_columns, true) ?? [];
        }

        return response()->json([
            'columns' => $columns,
        ]);
    } // End Method

    public function saveTablePreferences(Request $request)
    {
        $userCd = Auth::user()->user_cd;

        $allowedColumns = [
            'ParticipantId',
            'LastName',
            'FirstName',
            'MiddleName',
            'Trans_flag',
            'DateOfBirth',
            'AdmissionDate',
            'DischargeDate',
            'Address1',
            'Address2',
            'City',
            'State',
            'ZipCode',
            'Phone1',
            'Phone2',
            'MedicaidNo',
            'HMO_Enrolled',
            'medicaid_HMO_id',
            'medicaid_PA_no',
            'medicaid_PA_Start',
            'medicaid_PA_End',
            'HNP_Expiry_date',
            'primary_doctor_nm',
            'pharmacy_nm',
            'driverid',
            'route',
        ];

        /*
        |--------------------------------------------------------------------------
        | Allowed preference names
        |--------------------------------------------------------------------------
        |
        | members          = existing allmembers screen
        | members_list_all = new printable/exportable list screen
        |
        */
        $allowedTableNames = [
            'members',
            'members_list_all',
        ];

        $validated = $request->validate([
            'table_name'  => ['required', 'string', 'in:members,members_list_all'],
            'columns'     => ['nullable', 'array'],
            'columns.*'   => ['string'],
        ]);

        $tableName = $validated['table_name'];

        $selectedColumns = array_values(array_intersect(
            $validated['columns'] ?? [],
            $allowedColumns
        ));

        /*
        |--------------------------------------------------------------------------
        | Prevent saving an empty column selection
        |--------------------------------------------------------------------------
        */
        if (empty($selectedColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one column.',
            ], 422);
        }

        DB::table('user_table_preferences')->updateOrInsert(
            [
                'user_cd'    => $userCd,
                'table_name' => $tableName,
            ],
            [
                'visible_columns' => json_encode($selectedColumns),
                'updated_at'      => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Column preferences saved successfully.',
        ]);
    } // End Method

    public function dietaryRestrictionReport(Request $request, string $type = 'A')
    {
        $type = strtoupper(trim($type));

        if (!in_array($type, ['A', 'N'], true)) {
            $type = 'A';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $reportMonthYear = \Carbon\Carbon::create(
            $year,
            $month,
            1
        )->format('F Y');

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'DateOfBirth',
                DB::raw(
                    'DiateryRestriction1 as dietary_restriction_1'
                ),
                DB::raw(
                    'DiateryRestriction3 as dietary_restriction_3'
                )
            )
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $companyName = config('company.comp_nm', 'Company Name');

        $shiftName = $type === 'N'
            ? 'Noon Shift'
            : 'Morning Shift';

        return view(
            'members.members_dietary-restrictions',
            compact(
                'members',
                'type',
                'month',
                'year',
                'reportMonthYear',
                'companyName',
                'shiftName'
            )
        );
    } // End Method

    public function dietaryRestrictionReportPdf(Request $request, string $type = 'A')
    {
        $type = strtoupper(trim($type));

        if (!in_array($type, ['A', 'N'], true)) {
            $type = 'A';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $reportMonthYear = \Carbon\Carbon::create(
            $year,
            $month,
            1
        )->format('F Y');

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'DateOfBirth',
                DB::raw(
                    'DiateryRestriction1 as dietary_restriction_1'
                ),
                DB::raw(
                    'DiateryRestriction3 as dietary_restriction_3'
                )
            )
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $companyName = config('company.comp_nm', 'Company Name');

        $shiftName = $type === 'N'
            ? 'Noon Shift'
            : 'Morning Shift';

        $html = view(
            'members.members_dietary-restrictions-pdf',
            compact(
                'members',
                'type',
                'month',
                'year',
                'reportMonthYear',
                'companyName',
                'shiftName'
            )
        )->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'Letter-L',
            'orientation'   => 'L',
            'margin_left'   => 7,
            'margin_right'  => 7,
            'margin_top'    => 8,
            'margin_bottom' => 10,
            'margin_header' => 4,
            'margin_footer' => 4,
            'tempDir'       => storage_path('app/mpdf'),
        ]);

        $mpdf->SetTitle(
            'Dietary Restriction Report for the month of '
                . $reportMonthYear
        );

        $mpdf->SetAuthor($companyName);

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-size:8pt;">
                <tr>
                    <td width="33%">
                        Printed: '
                . now()->format('m/d/Y h:i A')
                . '
                    </td>

                    <td width="34%" align="center">
                        Dietary Restriction Report
                    </td>

                    <td width="33%" align="right">
                        Page {PAGENO} of {nbpg}
                    </td>
                </tr>
            </table>'
        );

        $mpdf->WriteHTML($html);

        $fileName = 'dietary-restriction-report-'
            . strtolower($type)
            . '-'
            . $year
            . '-'
            . str_pad($month, 2, '0', STR_PAD_LEFT)
            . '.pdf';

        return response(
            $mpdf->Output($fileName, 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',

                'Content-Disposition' =>
                'inline; filename="' . $fileName . '"',
            ]
        );
    } // End Method

    public function allergyReport(Request $request, string $type = 'A')
    {
        $type = strtoupper(trim($type));

        if (!in_array($type, ['A', 'N'], true)) {
            $type = 'A';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $reportMonthYear = \Carbon\Carbon::create(
            $year,
            $month,
            1
        )->format('F Y');

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'DateOfBirth',
                DB::raw(
                    'DiateryRestriction2 as allergy'
                )
            )
            ->where('Trans_flag', $type)
            ->whereNotNull('DiateryRestriction2')
            ->whereRaw(
                "TRIM(COALESCE(DiateryRestriction2, '')) <> ''"
            )
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $companyName = config('company.comp_nm', 'Company Name');

        $shiftName = $type === 'N'
            ? 'Noon Shift'
            : 'Morning Shift';

        return view(
            'members.members_allergy-report',
            compact(
                'members',
                'type',
                'month',
                'year',
                'reportMonthYear',
                'companyName',
                'shiftName'
            )
        );
    } // End Method

    public function allergyReportPdf(Request $request, string $type = 'A')
    {
        $type = strtoupper(trim($type));

        if (!in_array($type, ['A', 'N'], true)) {
            $type = 'A';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $reportMonthYear = \Carbon\Carbon::create(
            $year,
            $month,
            1
        )->format('F Y');

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'DateOfBirth',
                DB::raw(
                    'DiateryRestriction2 as allergy'
                )
            )
            ->where('Trans_flag', $type)
            ->whereNotNull('DiateryRestriction2')
            ->whereRaw(
                "TRIM(COALESCE(DiateryRestriction2, '')) <> ''"
            )
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $companyName = config('company.comp_nm', 'Company Name');

        $shiftName = $type === 'N'
            ? 'Noon Shift'
            : 'Morning Shift';

        $html = view(
            'members.members_allergy-report-pdf',
            compact(
                'members',
                'type',
                'month',
                'year',
                'reportMonthYear',
                'companyName',
                'shiftName'
            )
        )->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'Letter-L',
            'orientation'   => 'L',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 8,
            'margin_bottom' => 10,
            'margin_header' => 4,
            'margin_footer' => 4,
            'tempDir'       => storage_path('app/mpdf'),
        ]);

        $mpdf->SetTitle(
            'Allergy Report for the month of '
                . $reportMonthYear
        );

        $mpdf->SetAuthor($companyName);

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-size:8pt;">
                <tr>
                    <td width="33%">
                        Printed: '
                . now()->format('m/d/Y h:i A')
                . '
                    </td>

                    <td width="34%" align="center">
                        Allergy Report
                    </td>

                    <td width="33%" align="right">
                        Page {PAGENO} of {nbpg}
                    </td>
                </tr>
            </table>'
        );

        $mpdf->WriteHTML($html);

        $fileName = 'allergy-report-'
            . strtolower($type)
            . '-'
            . $year
            . '-'
            . str_pad($month, 2, '0', STR_PAD_LEFT)
            . '.pdf';

        return response(
            $mpdf->Output($fileName, 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',

                'Content-Disposition' =>
                'inline; filename="' . $fileName . '"',
            ]
        );
    } // End Method

    private function billingDb()
    {
        if (config('adc.multi_tenant_enabled')) {
            return DB::connection('billing');
        }

        return DB::connection();
    } // End Method


}
