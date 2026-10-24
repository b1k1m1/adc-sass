<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingMemberController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $sourceShift = trim((string) $request->get('source_shift', ''));
        $status = trim((string) $request->get('status', 'active'));

        $query = DB::table('billing_members');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ParticipantId', 'like', "%{$search}%")
                    ->orWhere('LastName', 'like', "%{$search}%")
                    ->orWhere('FirstName', 'like', "%{$search}%")
                    ->orWhere('medicaid_HMO_id', 'like', "%{$search}%")
                    ->orWhere('HMO_Enrolled', 'like', "%{$search}%");
            });
        }

        if ($sourceShift !== '') {
            $query->where('source_shift', $sourceShift);
        }

        if ($status === 'active') {
            $query->where('active', 1);
        } elseif ($status === 'inactive') {
            $query->where('active', 0);
        }

        $members = $query
            ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
            ->get();

        return view(
            'billing_members.index',
            compact(
                'members',
                'search',
                'sourceShift',
                'status'
            )
        );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $insuranceCompanies = DB::table('insurance_companies')
            ->where('active', 1)
            ->orderBy('code')
            ->get();

        return view(
            'billing_members.create',
            compact('insuranceCompanies')
        );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $this->validateBillingMember($request);

        $participantId = trim(
            (string) $request->ParticipantId
        );

        $duplicate = DB::table('billing_members')
            ->where('ParticipantId', $participantId)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Participant ID ' . $participantId . ' already exists.'
                );
        }

        $data = $this->billingMemberData($request);

        $data['created_at'] = now();

        DB::table('billing_members')
            ->insert($data);

        return redirect()
            ->route('billing.members.index')
            ->with(
                'success',
                'Billing member added successfully.'
            );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $member = DB::table('billing_members')
            ->where('id', $id)
            ->first();

        if (!$member) {
            abort(404);
        }

        $insuranceCompanies = DB::table('insurance_companies')
            ->where('active', 1)
            ->orderBy('code')
            ->get();

        return view(
            'billing_members.edit',
            compact(
                'member',
                'insuranceCompanies'
            )
        );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $member = DB::table('billing_members')
            ->where('id', $id)
            ->first();

        if (!$member) {
            abort(404);
        }

        $this->validateBillingMember($request);

        $participantId = trim(
            (string) $request->ParticipantId
        );

        $duplicate = DB::table('billing_members')
            ->where('ParticipantId', $participantId)
            ->where('id', '<>', $id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Participant ID ' . $participantId . ' already exists.'
                );
        }

        $data = $this->billingMemberData($request);

        DB::table('billing_members')
            ->where('id', $id)
            ->update($data);

        return redirect()
            ->route('billing.members.index')
            ->with(
                'success',
                'Billing member updated successfully.'
            );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Toggle Active
    |--------------------------------------------------------------------------
    */

    public function toggleActive($id)
    {
        $member = DB::table('billing_members')
            ->where('id', $id)
            ->first();

        if (!$member) {
            abort(404);
        }

        $newStatus =
            ((int) $member->active === 1)
                ? 0
                : 1;

        DB::table('billing_members')
            ->where('id', $id)
            ->update([
                'active' => $newStatus,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('billing.members.index')
            ->with(
                'success',
                $newStatus
                    ? 'Billing member activated.'
                    : 'Billing member deactivated.'
            );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateBillingMember(Request $request): void
    {
        $request->validate([
            'ParticipantId' => 'required|string|max:4',
            'FirstName' => 'required|string|max:15',
            'LastName' => 'required|string|max:15',
            'MiddleName' => 'nullable|string|max:1',
            'DateOfBirth' => 'required|date',
            'Sex' => 'nullable|string|max:1',

            'Address1' => 'required|string|max:25',
            'Address2' => 'nullable|string|max:25',
            'City' => 'required|string|max:15',
            'State' => 'required|string|max:2',
            'ZipCode' => 'required|string|max:10',

            'MedicaidNo' => 'nullable|string|max:15',
            'medicare_no' => 'nullable|string|max:20',
            'new_medicare_no' => 'nullable|string|max:25',

            'HMO_Enrolled' => 'nullable|string|max:45',
            'medicaid_HMO_id' => 'nullable|string|max:20',
            'medicaid_HMO_grp' => 'nullable|string|max:20',

            'medicaid_PA_no' => 'nullable|string|max:20',
            'medicaid_PA_Start' => 'nullable|date',
            'medicaid_PA_End' => 'nullable|date',

            'renew1_HMO_enrolled' => 'nullable|string|max:45',
            'renew1_HMO_id' => 'nullable|string|max:20',
            'renew1_HMO_grp' => 'nullable|string|max:20',
            'renew1_PA_no' => 'nullable|string|max:20',
            'renew1_PA_Start' => 'nullable|date',
            'renew1_PA_End' => 'nullable|date',

            'renew2_HMO_enrolled' => 'nullable|string|max:45',
            'renew2_HMO_id' => 'nullable|string|max:20',
            'renew2_HMO_grp' => 'nullable|string|max:20',
            'renew2_PA_no' => 'nullable|string|max:20',
            'renew2_PA_Start' => 'nullable|date',
            'renew2_PA_End' => 'nullable|date',

            'PrimaryDiagnosis1' => 'nullable|string|max:15',
            
            'SecondaryDiag1' => 'nullable|string|max:15',
            

            'primary_doctor_nm' => 'nullable|string|max:35',
            'primary_doctor_NPI' => 'nullable|string|max:35',

            'source_shift' => 'required|string|max:10',
            'source_system' => 'nullable|string|max:20',
            'Comments' => 'nullable|string|max:255',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Build Data Array
    |--------------------------------------------------------------------------
    */

    private function billingMemberData(Request $request): array
    {
        return [

            'ParticipantId' =>
                trim((string) $request->ParticipantId),

            'FirstName' =>
                trim((string) $request->FirstName),

            'LastName' =>
                trim((string) $request->LastName),

            'MiddleName' =>
                $request->filled('MiddleName')
                    ? strtoupper(trim((string) $request->MiddleName))
                    : null,

            'DateOfBirth' =>
                $request->DateOfBirth,

            'Sex' =>
                $request->filled('Sex')
                    ? strtoupper(trim((string) $request->Sex))
                    : null,

            'Address1' =>
                trim((string) $request->Address1),

            'Address2' =>
                $request->filled('Address2')
                    ? trim((string) $request->Address2)
                    : null,

            'City' =>
                trim((string) $request->City),

            'State' =>
                strtoupper(trim((string) $request->State)),

            'ZipCode' =>
                trim((string) $request->ZipCode),

            'MedicaidNo' =>
                $request->filled('MedicaidNo')
                    ? trim((string) $request->MedicaidNo)
                    : null,

            'medicare_no' =>
                $request->filled('medicare_no')
                    ? trim((string) $request->medicare_no)
                    : null,

            'new_medicare_no' =>
                $request->filled('new_medicare_no')
                    ? trim((string) $request->new_medicare_no)
                    : null,

            'HMO_Enrolled' =>
                $request->filled('HMO_Enrolled')
                    ? trim((string) $request->HMO_Enrolled)
                    : null,

            'medicaid_HMO_id' =>
                $request->filled('medicaid_HMO_id')
                    ? trim((string) $request->medicaid_HMO_id)
                    : null,

            'medicaid_HMO_grp' =>
                $request->filled('medicaid_HMO_grp')
                    ? trim((string) $request->medicaid_HMO_grp)
                    : null,

            'medicaid_PA_no' =>
                $request->filled('medicaid_PA_no')
                    ? trim((string) $request->medicaid_PA_no)
                    : null,

            'medicaid_PA_Start' =>
                $request->filled('medicaid_PA_Start')
                    ? $request->medicaid_PA_Start
                    : null,

            'medicaid_PA_End' =>
                $request->filled('medicaid_PA_End')
                    ? $request->medicaid_PA_End
                    : null,

            'renew1_HMO_enrolled' =>
                $request->filled('renew1_HMO_enrolled')
                    ? trim((string) $request->renew1_HMO_enrolled)
                    : null,

            'renew1_HMO_id' =>
                $request->filled('renew1_HMO_id')
                    ? trim((string) $request->renew1_HMO_id)
                    : null,

            'renew1_HMO_grp' =>
                $request->filled('renew1_HMO_grp')
                    ? trim((string) $request->renew1_HMO_grp)
                    : null,

            'renew1_PA_no' =>
                $request->filled('renew1_PA_no')
                    ? trim((string) $request->renew1_PA_no)
                    : null,

            'renew1_PA_Start' =>
                $request->filled('renew1_PA_Start')
                    ? $request->renew1_PA_Start
                    : null,

            'renew1_PA_End' =>
                $request->filled('renew1_PA_End')
                    ? $request->renew1_PA_End
                    : null,

            'renew2_HMO_enrolled' =>
                $request->filled('renew2_HMO_enrolled')
                    ? trim((string) $request->renew2_HMO_enrolled)
                    : null,

            'renew2_HMO_id' =>
                $request->filled('renew2_HMO_id')
                    ? trim((string) $request->renew2_HMO_id)
                    : null,

            'renew2_HMO_grp' =>
                $request->filled('renew2_HMO_grp')
                    ? trim((string) $request->renew2_HMO_grp)
                    : null,

            'renew2_PA_no' =>
                $request->filled('renew2_PA_no')
                    ? trim((string) $request->renew2_PA_no)
                    : null,

            'renew2_PA_Start' =>
                $request->filled('renew2_PA_Start')
                    ? $request->renew2_PA_Start
                    : null,

            'renew2_PA_End' =>
                $request->filled('renew2_PA_End')
                    ? $request->renew2_PA_End
                    : null,

            'PrimaryDiagnosis1' =>
                $request->filled('PrimaryDiagnosis1')
                    ? strtoupper(trim((string) $request->PrimaryDiagnosis1))
                    : null,

            

            'SecondaryDiag1' =>
                $request->filled('SecondaryDiag1')
                    ? strtoupper(trim((string) $request->SecondaryDiag1))
                    : null,


            'primary_doctor_nm' =>
                $request->filled('primary_doctor_nm')
                    ? trim((string) $request->primary_doctor_nm)
                    : null,

            'primary_doctor_NPI' =>
                $request->filled('primary_doctor_NPI')
                    ? trim((string) $request->primary_doctor_NPI)
                    : null,

            'source_shift' =>
                strtoupper(trim((string) $request->source_shift)),

            'source_system' =>
                $request->filled('source_system')
                    ? strtoupper(trim((string) $request->source_system))
                    : 'MANUAL',

            'active' =>
                $request->boolean('active') ? 1 : 0,

            'Comments' =>
                $request->filled('Comments')
                    ? trim((string) $request->Comments)
                    : null,

            'updated_at' =>
                now(),
        ];
    }
}