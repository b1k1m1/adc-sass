<?php

namespace App\Http\Controllers;


use App\Models\Attendance;
use App\Models\Member;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Exports\AttendanceExport;
use App\Exports\AttendanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Exports\AttendanceReportExportNew;
use App\Exports\AttendanceReportExportBilling;
use App\Exports\AttendanceReportExportBillingPA;
use App\Helpers\HasTransFlag;
use App\Exports\MemberWiseAttendanceExport;
use App\Exports\MemberYearlyAttendanceExport;
use App\Exports\HmoAttendanceCrosstabExport;


class AttendanceController extends Controller
{
    use HasTransFlag;

    public function AttendanceDashboard(){

        return view('attendance.attendance_index',  ['title' => 'Attendance', 'module' => 'Attendance']);
    }

    /*
    public function AttendanceAllDates(){

        $allDates = Attendance::select('CalenderDate')->groupBy('CalenderDate')->orderBy('CalenderDate','desc')->get();

        return view('attendance.attendance_date_list', compact('allDates'));

    } */

    public function AttendanceDates($type)
    {

    // Get 'A' or 'N' from query or route, with validation/fallback
    //$type  = $this->transType($request); // 'A' or 'N'

    $title = $title ?? ($type === 'N'
    ? 'Noon Shift Attendance Dates'
    : 'Morning Shift Attendance Dates');

    $allDates = DB::table('partattendence as a')
        ->join('members as m', 'm.ParticipantId', '=', 'a.ParticipantId')
        ->where('m.Trans_flag', '=', $type)
        ->select('a.CalenderDate')
        ->distinct()
        ->orderBy('a.CalenderDate', 'desc')
        ->get();

    return view('attendance.attendance_date_list', compact('allDates', 'title', 'type'));

    }

    public function AttendanceEntryDaily($CaldenderDate, Request $request)
    {

        // Read type from query string; default to 'A'
        $type = strtoupper($request->query('type', 'A'));
        if (!in_array($type, ['A','N'], true)) {
            $type = 'A';
        }

        $members = Member::select("ParticipantId","LastName","FirstName","MiddleName","Trans_flag","medicaid_PA_End")
                            ->where("Trans_flag",$type )
                            ->orderBy('ParticipantId')
                            ->get();

        $existingAttendance = Attendance::where('CalenderDate', $CaldenderDate)
                            ->pluck('AttendanceYN', 'ParticipantId')
                            ->toArray();

        return view('attendance.attendance_edit_daily', ['members' =>$members, 'date'=>$CaldenderDate, 'existingAttendance'=> $existingAttendance]);

    }

    // public function index(Request $request, $type = 'A')
    // {
    //     $type = strtoupper($type ?? $request->query('type', 'A'));

    //     if (!in_array($type, ['A', 'N'], true)) {
    //         $type = 'A';
    //     }

    //     $date = $request->input('date')
    //         ? \Carbon\Carbon::parse($request->input('date'))->format('Y-m-d')
    //         : now()->format('Y-m-d');

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Step 1: Get existing attendance for selected date + selected shift
    //     |--------------------------------------------------------------------------
    //     */
    //     $existingRows = DB::table('partattendence')
    //         ->whereDate('CalenderDate', $date)
    //         ->where('Trans_flag', $type)
    //         ->select('ParticipantId', 'AttendanceYN')
    //         ->get();

    //     $existingAttendance = $existingRows->mapWithKeys(function ($row) {
    //         $pid = str_pad(trim($row->ParticipantId), 3, '0', STR_PAD_LEFT);
    //         return [$pid => $row->AttendanceYN];
    //     })->toArray();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Step 2: Get participant IDs already saved for this date + shift
    //     |--------------------------------------------------------------------------
    //     | This is important because some Morning members may be saved under Noon shift.
    //     */
    //     $existingParticipantIds = $existingRows
    //         ->pluck('ParticipantId')
    //         ->map(fn ($id) => trim($id))
    //         ->unique()
    //         ->values()
    //         ->toArray();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Step 3: Load members
    //     |--------------------------------------------------------------------------
    //     | Show default shift members OR members already saved in partattendence
    //     | for this date + selected shift.
    //     */
    //     $members = DB::table('members')
    //         ->select(
    //             'ParticipantId',
    //             'LastName',
    //             'FirstName',
    //             'MiddleName',
    //             'Trans_flag',
    //             'medicaid_PA_End'
    //         )
    //         ->where(function ($query) use ($type, $existingParticipantIds) {
    //             $query->where('Trans_flag', $type);

    //             if (!empty($existingParticipantIds)) {
    //                 $query->orWhereIn('ParticipantId', $existingParticipantIds);
    //             }
    //         })
    //         ->orderBy('LastName')
    //         ->orderBy('FirstName')
    //         ->get();

    //     return view('attendance.attendance_daily', compact(
    //         'members',
    //         'date',
    //         'existingAttendance',
    //         'type'
    //     ));

    // } // End Method

    public function index(Request $request, $type = 'A')
    {
        /*
        |--------------------------------------------------------------------------
        | Shift type:
        | A = Morning Shift
        | N = Noon Shift
        |--------------------------------------------------------------------------
        */
        $type = strtoupper($type ?? $request->query('type', 'A'));

        if (!in_array($type, ['A', 'N'], true)) {
            $type = 'A';
        }

        /*
        |--------------------------------------------------------------------------
        | Selected attendance date
        |--------------------------------------------------------------------------
        */
        $date = $request->input('date')
            ? \Carbon\Carbon::parse($request->input('date'))->format('Y-m-d')
            : now()->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | Opposite shift
        |--------------------------------------------------------------------------
        */
        $oppositeType = ($type === 'A') ? 'N' : 'A';

        /*
        |--------------------------------------------------------------------------
        | Temporary members selected from dropdown
        |--------------------------------------------------------------------------
        */
        $tempMemberIds = $request->input('temp_members', []);

        if (!is_array($tempMemberIds)) {
            $tempMemberIds = [];
        }

        $newTempMember = $request->input('temp_member');

        if (!empty($newTempMember)) {
            $tempMemberIds[] = $newTempMember;
        }

        $tempMemberIds = collect($tempMemberIds)
            ->map(function ($id) {
                return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Step 1: Get existing attendance for selected date + selected shift
        |--------------------------------------------------------------------------
        */
        $existingRows = DB::table('partattendence')
            ->whereDate('CalenderDate', $date)
            ->where('Trans_flag', $type)
            ->select('ParticipantId', 'AttendanceYN')
            ->get();

        $existingAttendance = $existingRows->mapWithKeys(function ($row) {
            $pid = str_pad(trim((string) $row->ParticipantId), 3, '0', STR_PAD_LEFT);

            return [$pid => $row->AttendanceYN];
        })->toArray();

        /*
        |--------------------------------------------------------------------------
        | Step 2: Participant IDs already saved for this date + selected shift
        |--------------------------------------------------------------------------
        */
        $existingParticipantIds = $existingRows
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
            })
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Step 3: Include temporary members + already saved members
        |--------------------------------------------------------------------------
        */
        $includeParticipantIds = collect($tempMemberIds)
            ->merge($existingParticipantIds)
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Step 4: Load members
        |--------------------------------------------------------------------------
        | Show default shift members OR temporary/previously saved members.
        */
        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'Trans_flag',
                'medicaid_PA_End'
            )
            ->where(function ($query) use ($type, $includeParticipantIds) {
                $query->where('Trans_flag', $type);

                if (!empty($includeParticipantIds)) {
                    $query->orWhereIn('ParticipantId', $includeParticipantIds);
                }
            })
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Step 5: Check opposite-shift attendance for displayed members
        |--------------------------------------------------------------------------
        */
        $memberIdsForCheck = $members
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
            })
            ->unique()
            ->values()
            ->toArray();

        $otherShiftRows = collect();

        if (!empty($memberIdsForCheck)) {
            $otherShiftRows = DB::table('partattendence')
                ->select(
                    'ParticipantId',
                    'AttendanceYN',
                    'Trans_flag',
                    DB::raw("DATE_FORMAT(CalenderDate, '%Y-%m-%d') as CalenderDate")
                )
                ->whereDate('CalenderDate', $date)
                ->where('Trans_flag', $oppositeType)
                ->where('AttendanceYN', 'Y')
                ->whereIn('ParticipantId', $memberIdsForCheck)
                ->get();
        }

        $otherShiftAttendance = [];

        foreach ($otherShiftRows as $row) {
            $pid = str_pad(trim((string) $row->ParticipantId), 3, '0', STR_PAD_LEFT);

            $otherShiftAttendance[$pid] = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Step 6: Temporary member dropdown list
        |--------------------------------------------------------------------------
        | If screen is Morning, dropdown shows Noon members.
        | If screen is Noon, dropdown shows Morning members.
        */
        $alreadyInGridIds = $members
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
            })
            ->toArray();

        $temporaryMembers = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'Trans_flag'
            )
            ->where('Trans_flag', $oppositeType)
            ->whereNotIn('ParticipantId', $alreadyInGridIds)
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        return view('attendance.attendance_daily', compact(
            'members',
            'date',
            'existingAttendance',
            'type',
            'oppositeType',
            'otherShiftAttendance',
            'temporaryMembers',
            'tempMemberIds'
        ));

    } // End Method

    // public function saveAttendance(Request $request)
    // {
    //     $date = $request->input('date');
    //     $formattedDate = Carbon::parse($date)->format('Y-m-d');

    //     $update_flag = 'Y';
    //     $user_cd     = Auth::user()->user_cd ?? null;
    //     $update_dt   = now();
    //     $company_id  = 'DS02';

    //     $validated = $request->validate([
    //         'date' => 'required|date',
    //         'AttendanceYN' => 'required|array',
    //         'AttendanceYN.*' => 'in:Y,N',
    //     ]);

    //     try {

    //         DB::beginTransaction();

    //         /*
    //         * Get current max SerialNo.
    //         * We will increase it only when inserting new rows.
    //         */
    //         $nextid = (int) DB::table('partattendence')->max('SerialNo');

    //         foreach ($validated['AttendanceYN'] as $ParticipantId => $status) {

    //             /*
    //             * Your database stores ParticipantId like 005,
    //             * so force it to 3 digits before save/update.
    //             */
    //             $ParticipantId = str_pad(trim((string) $ParticipantId), 3, '0', STR_PAD_LEFT);

    //             /*
    //             * Check if attendance already exists for this member and date.
    //             * Do not use update() return value to decide insert,
    //             * because update() can return 0 when value is unchanged.
    //             */
    //             $exists = DB::table('partattendence')
    //                 ->where('ParticipantId', $ParticipantId)
    //                 ->whereDate('CalenderDate', $formattedDate)
    //                 ->exists();

    //             if ($exists) {

    //                 DB::table('partattendence')
    //                     ->where('ParticipantId', $ParticipantId)
    //                     ->whereDate('CalenderDate', $formattedDate)
    //                     ->update([
    //                         'AttendanceYN' => $status,
    //                         'update_flag'  => $update_flag,
    //                         'user_cd'      => $user_cd,
    //                         'update_dt'    => $update_dt,
    //                         'company_id'   => $company_id,
    //                     ]);

    //             } else {

    //                 $nextid++;

    //                 DB::table('partattendence')->insert([
    //                     'SerialNo'      => $nextid,
    //                     'ParticipantId' => $ParticipantId,
    //                     'CalenderDate'  => $formattedDate,
    //                     'AttendanceYN'  => $status,
    //                     'update_flag'   => $update_flag,
    //                     'user_cd'       => $user_cd,
    //                     'update_dt'     => $update_dt,
    //                     'company_id'    => $company_id,
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return redirect()->back()->with([
    //             'message'    => 'Attendance saved successfully!',
    //             'alert-type' => 'success',
    //         ]);

    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         Log::error('Attendance save failed: ' . $e->getMessage());

    //         return redirect()->back()->with([
    //             'message'    => 'Attendance save failed. Please check laravel.log.',
    //             'alert-type' => 'error',
    //         ]);
    //     }
    // }

    public function saveAttendance(Request $request)
    {
        $date = $request->input('date');
        $formattedDate = Carbon::parse($date)->format('Y-m-d');
        $formattedDateTime = $formattedDate . ' 00:00:00';

        /*
        * Selected screen shift:
        * A = Morning Shift
        * N = Noon Shift
        */
        $type = strtoupper($request->input('type', 'A'));

        if (!in_array($type, ['A', 'N'], true)) {
            return redirect()->back()->with([
                'message'    => 'Please select Morning Shift or Noon Shift before saving.',
                'alert-type' => 'error',
            ]);
        }

        /*
        * Opposite shift:
        * Used to protect existing Y attendance from being overwritten.
        */
        $oppositeType = ($type === 'A') ? 'N' : 'A';

        $update_flag = 'Y';
        $user_cd     = Auth::user()->user_cd ?? null;
        $update_dt   = now();
        $company_id  = 'DS02';

        $validated = $request->validate([
            'date'           => 'required|date',
            'type'           => 'required|in:A,N',
            'AttendanceYN'   => 'required|array',
            'AttendanceYN.*' => 'in:Y,N',
        ]);

        $skippedOtherShiftCount = 0;

        try {

            DB::beginTransaction();

            /*
            * Get current max SerialNo.
            * We will increase it only when inserting new rows.
            */
            $nextid = (int) DB::table('partattendence')->max('SerialNo');

            foreach ($validated['AttendanceYN'] as $ParticipantId => $status) {

                /*
                * Database stores ParticipantId like 005.
                * For 4 digit IDs like 7180, this will keep 7180 as-is.
                */
                $ParticipantId = str_pad(trim((string) $ParticipantId), 3, '0', STR_PAD_LEFT);

                $status = ($status === 'Y') ? 'Y' : 'N';

                /*
                |--------------------------------------------------------------------------
                | Check existing row by actual primary key:
                |--------------------------------------------------------------------------
                | Your table primary key is ParticipantId + CalenderDate.
                | So we must check by only these two fields first.
                */
                $existingRow = DB::table('partattendence')
                    ->select(
                        'ParticipantId',
                        'CalenderDate',
                        'AttendanceYN',
                        'trans_flag')
                    ->where('ParticipantId', $ParticipantId)
                    ->whereDate('CalenderDate', $formattedDate)
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Protection:
                |--------------------------------------------------------------------------
                | If this member/date already has Y attendance in the opposite shift,
                | do not overwrite it from this screen.
                */
                    $existingTransFlag = $existingRow->trans_flag
                    ?? $existingRow->trans_flag
                    ?? '';

                    $existingAttendanceYN = $existingRow->AttendanceYN
                    ?? $existingRow->attendanceyn
                    ?? '';

                if (
                    $existingRow &&
                    $existingTransFlag === $oppositeType &&
                    $existingAttendanceYN === 'Y'
                ) {
                    $skippedOtherShiftCount++;
                    continue;
                }
                /*
                --------------------------------------------------------------------------
                | CASE 1: User checked the box
                |--------------------------------------------------------------------------
                */
                if ($status === 'Y') {

                    if ($existingRow) {

                        /*
                        * Row already exists for this member/date.
                        * Since it is not protected opposite-shift Y,
                        * update it to selected/current shift.
                        */
                        DB::table('partattendence')
                            ->where('ParticipantId', $ParticipantId)
                            ->whereDate('CalenderDate', $formattedDate)
                            ->update([
                                'AttendanceYN' => 'Y',
                                'trans_flag'   => $type,
                                'update_flag'  => $update_flag,
                                'user_cd'      => $user_cd,
                                'update_dt'    => $update_dt,
                                'company_id'   => $company_id,
                            ]);

                    } else {

                        /*
                        * No row exists, insert new current-shift attendance.
                        */
                        $nextid++;

                        DB::table('partattendence')->insert([
                            'SerialNo'      => $nextid,
                            'ParticipantId' => $ParticipantId,
                            'CalenderDate'  => $formattedDateTime,
                            'AttendanceYN'  => 'Y',
                            'trans_flag'    => $type,
                            'update_flag'   => $update_flag,
                            'user_cd'       => $user_cd,
                            'update_dt'     => $update_dt,
                            'company_id'    => $company_id,
                        ]);
                    }

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CASE 2: User unchecked the box
                |--------------------------------------------------------------------------
                | Only update to N if existing row belongs to current selected shift.
                | This prevents current screen from changing opposite shift records.
                */
                if (
                    $existingRow &&
                    (
                        $existingTransFlag === $type ||
                        $existingTransFlag === '' ||
                        is_null($existingTransFlag)
                    )
                ) {
                    DB::table('partattendence')
                        ->where('ParticipantId', $ParticipantId)
                        ->whereDate('CalenderDate', $formattedDate)
                        ->update([
                            'AttendanceYN' => 'N',
                            'trans_flag'   => $type,
                            'update_flag'  => $update_flag,
                            'user_cd'      => $user_cd,
                            'update_dt'    => $update_dt,
                            'company_id'   => $company_id,
                        ]);
                }
            }

            DB::commit();

            $message = 'Attendance saved successfully!';

            if ($skippedOtherShiftCount > 0) {
                $message .= ' Some records were skipped because attendance already exists in the other shift.';
            }

            return redirect()
                ->route('attendance.index', [
                    'type'         => $type,
                    'date'         => Carbon::parse($formattedDate)->format('m/d/Y'),
                    'temp_members' => $request->input('temp_members', []),
                ])
                ->with([
                    'message'    => $message,
                    'alert-type' => 'success',
                ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Attendance save failed: ' . $e->getMessage());

            return redirect()->back()->with([
                'message'    => 'Attendance save failed. Please check laravel.log.',
                'alert-type' => 'error',
            ]);
        }

    } // End Method


    public function AttendanceEntryWeekly(){

        return view('attendance.attendance_weekly');

    } //End Method

    public function AttendanceEntryWeeklyCheck(){

        return view('attendance.attendance_weekly_check');

    } //End Method


    // Show this form for entering Weekly attendance

    public function weeklyAttendance(Request $request){

        $weekStartDate = $request->input('week_start_date');

       if ($weekStartDate) {
        $weekStartDate = Carbon::parse($weekStartDate)
            ->subDays(Carbon::parse($weekStartDate)->dayOfWeek) // Go back to Sunday
            ->format('Y-m-d');
        } else {
        $weekStartDate = now()->subDays(now()->dayOfWeek)->format('Y-m-d');
        }

        $weekDates = [];
        $startDate = Carbon::parse($weekStartDate);



        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
        }

        // Load all participants (Members table)
        $participants =  Member::select("ParticipantId","LastName","FirstName","MiddleName","Trans_flag","medicaid_PA_End")
                        ->where("Trans_flag","A" )
                        ->orderBy('ParticipantId')
                        ->get();

        // Load existing attendances for the week
       // $attendances = DB::table('partattendence')
        //    ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
        //    ->get()
        //    ->groupBy('ParticipantId');

        $attendances = DB::table('partattendence')
                    ->select(
                    'ParticipantId',
                    'AttendanceYN',
                    DB::raw("DATE_FORMAT(CalenderDate, '%Y-%m-%d') as CalenderDate")
                    )
                    ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
                    ->get()
                    ->groupBy('ParticipantId');

    //return view('attendance.attendance_weekly_entry_checkbox', compact('weekDates', 'participants', 'attendances', 'weekStartDate' ));
    return view('attendance.attendance_weekly_entry', compact('weekDates', 'participants', 'attendances', 'weekStartDate' ));

    } // End Method

    /*
    public function weeklyAttendanceCheckmark(Request $request){

        $weekStartDate = $request->input('week_start_date');

       if ($weekStartDate) {
        $weekStartDate = Carbon::parse($weekStartDate)
            ->subDays(Carbon::parse($weekStartDate)->dayOfWeek) // Go back to Sunday
            ->format('Y-m-d');
        } else {
        $weekStartDate = now()->subDays(now()->dayOfWeek)->format('Y-m-d');
        }

        $weekDates = [];
        $startDate = Carbon::parse($weekStartDate);



        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
        }

        // Load all participants (Members table)
        $participants =  Member::select("ParticipantId","LastName","FirstName","MiddleName","Trans_flag","medicaid_PA_End")
                        ->where("Trans_flag","A" )
                        ->orderBy('ParticipantId')
                        ->get();

        // Load existing attendances for the week
       // $attendances = DB::table('partattendence')
        //    ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
        //    ->get()
        //    ->groupBy('ParticipantId');

        $attendances = DB::table('partattendence')
                    ->select(
                    'ParticipantId',
                    'AttendanceYN',
                    DB::raw("DATE_FORMAT(CalenderDate, '%Y-%m-%d') as CalenderDate")
                    )
                    ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
                    ->get()
                    ->groupBy('ParticipantId');

    //return view('attendance.attendance_weekly_entry_checkbox', compact('weekDates', 'participants', 'attendances', 'weekStartDate' ));
    return view('attendance.attendance_weekly_entry_checkbox', compact('weekDates', 'participants', 'attendances', 'weekStartDate' ));

    } // End Method

    */


    // public function weeklyAttendanceCheckmark(Request $request)
    // {
    //     /*
    //     * Shift type:
    //     * A = Morning Shift
    //     * N = Noon Shift
    //     *
    //     * Initially, no shift is selected, so we do not load participants.
    //     */
    //     $type = strtoupper($request->input('type', ''));

    //     if (!in_array($type, ['A', 'N'])) {
    //         $type = '';
    //     }

    //     $weekStartDate = $request->input('week_start_date');

    //     if ($weekStartDate) {
    //         $weekStartDate = Carbon::parse($weekStartDate)
    //             ->subDays(Carbon::parse($weekStartDate)->dayOfWeek) // Go back to Sunday
    //             ->format('Y-m-d');
    //     } else {
    //         $weekStartDate = now()->subDays(now()->dayOfWeek)->format('Y-m-d');
    //     }

    //     $weekDates = [];
    //     $startDate = Carbon::parse($weekStartDate);

    //     for ($i = 0; $i < 7; $i++) {
    //         $weekDates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
    //     }

    //     /*
    //     * Do not load members until user selects Morning or Noon shift.
    //     */
    //     $participants = collect();
    //     $attendances = collect();

    //     if ($type !== '') {

    //         // Load participants based on selected shift
    //         $participants = Member::select(
    //                 "ParticipantId",
    //                 "LastName",
    //                 "FirstName",
    //                 "MiddleName",
    //                 "Trans_flag",
    //                 "medicaid_PA_End"
    //             )
    //             ->where("Trans_flag", $type)
    //             ->orderBy('ParticipantId')
    //             ->get();

    //         // Load existing attendances for selected week
    //         $attendances = DB::table('partattendence')
    //             ->select(
    //                 'ParticipantId',
    //                 'AttendanceYN',
    //                 DB::raw("DATE_FORMAT(CalenderDate, '%Y-%m-%d') as CalenderDate")
    //             )
    //             ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
    //             ->get()
    //             ->groupBy('ParticipantId');
    //     }

    //     return view('attendance.attendance_weekly_entry_checkbox', compact(
    //         'weekDates',
    //         'participants',
    //         'attendances',
    //         'weekStartDate',
    //         'type'
    //     ));
    // }

    //

    public function weeklyAttendanceCheckmark(Request $request)
    {
        /*
        * Shift type:
        * A = Morning Shift
        * N = Noon Shift
        */
        $type = strtoupper($request->input('type', ''));

        if (!in_array($type, ['A', 'N'])) {
            $type = '';
        }

        $weekStartDate = $request->input('week_start_date');

        if ($weekStartDate) {
            $weekStartDate = Carbon::parse($weekStartDate)
                ->subDays(Carbon::parse($weekStartDate)->dayOfWeek)
                ->format('Y-m-d');
        } else {
            $weekStartDate = now()->subDays(now()->dayOfWeek)->format('Y-m-d');
        }

        $weekDates = [];
        $startDate = Carbon::parse($weekStartDate);

        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
        }

        $participants = collect();
        $attendances = collect();
        $temporaryMembers = collect();
        $tempMemberIds = [];
        $otherShiftAttendance = [];
        $oppositeType = '';

        if ($type !== '') {

            /*
            * Temporary members selected from dropdown.
            * Supports one or more temporary members.
            */
            $tempMemberIds = $request->input('temp_members', []);

            if (!is_array($tempMemberIds)) {
                $tempMemberIds = [];
            }

            $newTempMember = $request->input('temp_member');

            if (!empty($newTempMember)) {
                $tempMemberIds[] = $newTempMember;
            }

            $tempMemberIds = collect($tempMemberIds)
                ->map(function ($id) {
                    return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
                })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            /*
            * Members who already have attendance saved in this selected shift
            * for this week. This keeps temporary members visible after saving.
            */
            $attendanceParticipantIds = DB::table('partattendence')
                ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
                ->where('Trans_flag', $type)
                ->where('AttendanceYN', 'Y')
                ->pluck('ParticipantId')
                ->map(function ($id) {
                    return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
                })
                ->toArray();

            $includeParticipantIds = collect($tempMemberIds)
                ->merge($attendanceParticipantIds)
                ->unique()
                ->values()
                ->toArray();

            /*
            * Load regular shift members + temporary/previously-saved members.
            */
            $participantsQuery = Member::select(
                    "ParticipantId",
                    "LastName",
                    "FirstName",
                    "MiddleName",
                    "Trans_flag",
                    "medicaid_PA_End"
                )
                ->where(function ($q) use ($type, $includeParticipantIds) {
                    $q->where("Trans_flag", $type);

                    if (!empty($includeParticipantIds)) {
                        $q->orWhereIn('ParticipantId', $includeParticipantIds);
                    }
                });

            $participants = $participantsQuery
                ->orderBy('ParticipantId')
                ->get();

            /*
            * Load existing attendances for selected week and selected shift only.
            */
            $attendances = DB::table('partattendence')
                ->select(
                    'ParticipantId',
                    'AttendanceYN',
                    'Trans_flag',
                    DB::raw("DATE_FORMAT(CalenderDate, '%Y-%m-%d') as CalenderDate")
                )
                ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
                ->where('Trans_flag', $type)
                ->get()
                ->groupBy('ParticipantId');

            /*
            * Load existing attendances from opposite shift.
            * This is used only for visual warning/locking in the weekly grid.
            */
            $oppositeType = ($type === 'A') ? 'N' : 'A';

            $participantIdsForCheck = $participants
                ->pluck('ParticipantId')
                ->map(function ($id) {
                    return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
                })
                ->unique()
                ->values()
                ->toArray();

            $otherShiftRows = DB::table('partattendence')
                ->select(
                    'ParticipantId',
                    'AttendanceYN',
                    'Trans_flag',
                    DB::raw("DATE_FORMAT(CalenderDate, '%Y-%m-%d') as CalenderDate")
                )
                ->whereBetween('CalenderDate', [$weekDates[0], $weekDates[6]])
                ->where('Trans_flag', $oppositeType)
                ->where('AttendanceYN', 'Y')
                ->whereIn('ParticipantId', $participantIdsForCheck)
                ->get();

            $otherShiftAttendance = [];

            foreach ($otherShiftRows as $row) {
                $pid = str_pad(trim((string) $row->ParticipantId), 3, '0', STR_PAD_LEFT);
                $dateKey = $row->CalenderDate;

                $otherShiftAttendance[$pid][$dateKey] = true;
            }

            /*
            * Dropdown list:
            * If screen is Morning, show Noon members.
            * If screen is Noon, show Morning members.
            */
            //$oppositeType = ($type === 'A') ? 'N' : 'A';

            $alreadyInGridIds = $participants
                ->pluck('ParticipantId')
                ->map(function ($id) {
                    return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
                })
                ->toArray();

            $temporaryMembers = Member::select(
                    "ParticipantId",
                    "LastName",
                    "FirstName",
                    "MiddleName",
                    "Trans_flag"
                )
                ->where("Trans_flag", $oppositeType)
                ->whereNotIn('ParticipantId', $alreadyInGridIds)
                ->orderBy('ParticipantId')
                ->get();
        }

        return view('attendance.attendance_weekly_entry_checkbox', compact(
            'weekDates',
            'participants',
            'attendances',
            'weekStartDate',
            'type',
            'temporaryMembers',
            'tempMemberIds',
            'oppositeType',
            'otherShiftAttendance'
        ));

    } // End Method

//     public function saveWeeklyAttendance(Request $request)
//     {
//         $attendanceInput = $request->input('attendance', []);

//         $update_flag = 'Y';
//         $user_cd     = Auth::user()->user_cd ?? null;
//         $update_dt   = now();
//         $company_id  = 'DS02';

//         try {

//             $rows = [];

//             foreach ($attendanceInput as $participantId => $dates) {

//                 // Database stores ParticipantId like 005
//                 $participantId = str_pad(trim((string) $participantId), 3, '0', STR_PAD_LEFT);

//                 foreach ($dates as $date => $value) {

//                     // Keep CalenderDate consistent as datetime 00:00:00
//                     $formattedDate = Carbon::parse($date)->format('Y-m-d') . ' 00:00:00';

//                     // Accept only Y/N
//                     $status = ($value === 'Y') ? 'Y' : 'N';

//                     $rows[] = [
//                         'ParticipantId' => $participantId,
//                         'CalenderDate'  => $formattedDate,
//                         'AttendanceYN'  => $status,
//                         'update_flag'   => $update_flag,
//                         'user_cd'       => $user_cd,
//                         'update_dt'     => $update_dt,
//                         'company_id'    => $company_id,
//                     ];
//                 }
//             }

//             if (!empty($rows)) {

//                 /*
//                 * Because partattendence has PRIMARY KEY:
//                 * ParticipantId + CalenderDate
//                 *
//                 * upsert will:
//                 * - insert if record does not exist
//                 * - update if record already exists
//                 */
//                 foreach (array_chunk($rows, 500) as $chunk) {

//                     DB::table('partattendence')->upsert(
//                         $chunk,
//                         ['ParticipantId', 'CalenderDate'],
//                         [
//                             'AttendanceYN',
//                             'update_flag',
//                             'user_cd',
//                             'update_dt',
//                             'company_id',
//                         ]
//                     );
//                 }
//             }

//             return redirect()->back()->with([
//                 'message'    => 'Weekly Attendance saved successfully!',
//                 'alert-type' => 'success',
//             ]);

//         } catch (\Exception $e) {

//             Log::error('Weekly attendance save failed: ' . $e->getMessage());

//             return redirect()->back()->with([
//                 'message'    => 'Weekly Attendance save failed. Please check laravel.log.',
//                 'alert-type' => 'error',
//             ]);
//         }

//     } // End Method

//  // Helper function to get next serial number

//  private function getNextSerialNo()
// {
//     $lastSerial = DB::table('partattendence')->max('SerialNo');
//     return $lastSerial ? $lastSerial + 1 : 1;
// } // End Method


    // public function saveWeeklyAttendance(Request $request)
    // {
    //     $attendanceInput = $request->input('attendance', []);

    //     /*
    //     * Selected screen shift:
    //     * A = Morning Shift
    //     * N = Noon Shift
    //     */
    //     $type = strtoupper($request->input('type', ''));

    //     if (!in_array($type, ['A', 'N'])) {
    //         return redirect()->back()->with([
    //             'message'    => 'Please select Morning Shift or Noon Shift before saving.',
    //             'alert-type' => 'error',
    //         ]);
    //     }

    //     $update_flag = 'Y';
    //     $user_cd     = Auth::user()->user_cd ?? null;
    //     $update_dt   = now();
    //     $company_id  = 'DS02';

    //     try {

    //         DB::beginTransaction();

    //         foreach ($attendanceInput as $participantId => $dates) {

    //             // Database stores ParticipantId like 005
    //             $participantId = str_pad(trim((string) $participantId), 3, '0', STR_PAD_LEFT);

    //             foreach ($dates as $date => $value) {

    //                 $dateOnly = Carbon::parse($date)->format('Y-m-d');

    //                 // Keep CalenderDate consistent as datetime 00:00:00
    //                 $formattedDate = $dateOnly . ' 00:00:00';

    //                 // Accept only Y/N
    //                 $status = ($value === 'Y') ? 'Y' : 'N';

    //                 /*
    //                 * CASE 1:
    //                 * User checked the box.
    //                 *
    //                 * This means user intentionally wants this member present
    //                 * in the selected shift for this date.
    //                 *
    //                 * Because table primary key is ParticipantId + CalenderDate,
    //                 * this will move the attendance to current selected shift.
    //                 */
    //                 if ($status === 'Y') {

    //                     DB::table('partattendence')->updateOrInsert(
    //                         [
    //                             'ParticipantId' => $participantId,
    //                             'CalenderDate'  => $formattedDate,
    //                         ],
    //                         [
    //                             'AttendanceYN' => 'Y',
    //                             'Trans_flag'   => $type,
    //                             'update_flag'  => $update_flag,
    //                             'user_cd'      => $user_cd,
    //                             'update_dt'    => $update_dt,
    //                             'company_id'   => $company_id,
    //                         ]
    //                     );

    //                     continue;
    //                 }

    //                 /*
    //                 * CASE 2:
    //                 * User unchecked the box.
    //                 *
    //                 * Very important:
    //                 * Only update to N if the existing record already belongs
    //                 * to this selected shift.
    //                 *
    //                 * This prevents Morning screen unchecked values from
    //                 * overwriting Noon attendance, and vice versa.
    //                 */
    //                 DB::table('partattendence')
    //                     ->where('ParticipantId', $participantId)
    //                     ->whereDate('CalenderDate', $dateOnly)
    //                     ->where('Trans_flag', $type)
    //                     ->update([
    //                         'AttendanceYN' => 'N',
    //                         'update_flag'  => $update_flag,
    //                         'user_cd'      => $user_cd,
    //                         'update_dt'    => $update_dt,
    //                         'company_id'   => $company_id,
    //                     ]);
    //             }
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('attendance.weekly.entry_mark', [
    //                 'week_start_date' => $request->input('week_start_date'),
    //                 'type'            => $type,
    //                 'temp_members'    => $request->input('temp_members', []),
    //             ])
    //             ->with([
    //                 'message'    => 'Weekly Attendance saved successfully!',
    //                 'alert-type' => 'success',
    //             ]);

    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         Log::error('Weekly attendance save failed: ' . $e->getMessage());

    //         return redirect()->back()->with([
    //             'message'    => 'Weekly Attendance save failed. Please check laravel.log.',
    //             'alert-type' => 'error',
    //         ]);
    //     }

    // } // End Method

    public function saveWeeklyAttendance(Request $request)
    {
        $attendanceInput = $request->input('attendance', []);

        /*
        * Selected screen shift:
        * A = Morning Shift
        * N = Noon Shift
        */
        $type = strtoupper($request->input('type', ''));

        if (!in_array($type, ['A', 'N'])) {
            return redirect()->back()->with([
                'message'    => 'Please select Morning Shift or Noon Shift before saving.',
                'alert-type' => 'error',
            ]);
        }

        /*
        * Opposite shift:
        * If current screen is Morning, opposite is Noon.
        * If current screen is Noon, opposite is Morning.
        */
        $oppositeType = ($type === 'A') ? 'N' : 'A';

        $update_flag = 'Y';
        $user_cd     = Auth::user()->user_cd ?? null;
        $update_dt   = now();
        $company_id  = 'DS02';

        $skippedOtherShiftCount = 0;

        try {

            DB::beginTransaction();

            foreach ($attendanceInput as $participantId => $dates) {

                // Database stores ParticipantId like 005
                $participantId = str_pad(trim((string) $participantId), 3, '0', STR_PAD_LEFT);

                foreach ($dates as $date => $value) {

                    $dateOnly = Carbon::parse($date)->format('Y-m-d');

                    // Keep CalenderDate consistent as datetime 00:00:00
                    $formattedDate = $dateOnly . ' 00:00:00';

                    // Accept only Y/N
                    $status = ($value === 'Y') ? 'Y' : 'N';

                    /*
                    * Important safety check:
                    * If this member already has Y attendance in the opposite shift
                    * for the same date, do not overwrite/move that record.
                    *
                    * Example:
                    * Current screen = Morning
                    * Existing record = Noon, AttendanceYN = Y
                    * Then skip this member/date.
                    */
                    $existsInOtherShift = DB::table('partattendence')
                        ->where('ParticipantId', $participantId)
                        ->whereDate('CalenderDate', $dateOnly)
                        ->where('Trans_flag', $oppositeType)
                        ->where('AttendanceYN', 'Y')
                        ->exists();

                    if ($existsInOtherShift) {
                        $skippedOtherShiftCount++;
                        continue;
                    }

                    /*
                    * CASE 1:
                    * User checked the box.
                    *
                    * Save attendance only if there is no Y attendance
                    * already existing in the opposite shift.
                    */
                    if ($status === 'Y') {

                        DB::table('partattendence')->updateOrInsert(
                            [
                                'ParticipantId' => $participantId,
                                'CalenderDate'  => $formattedDate,
                            ],
                            [
                                'AttendanceYN' => 'Y',
                                'Trans_flag'   => $type,
                                'update_flag'  => $update_flag,
                                'user_cd'      => $user_cd,
                                'update_dt'    => $update_dt,
                                'company_id'   => $company_id,
                            ]
                        );

                        continue;
                    }

                    /*
                    * CASE 2:
                    * User unchecked the box.
                    *
                    * Only update to N if the existing record already belongs
                    * to this selected shift.
                    *
                    * This prevents Morning screen unchecked values from
                    * overwriting Noon attendance, and vice versa.
                    */
                    DB::table('partattendence')
                        ->where('ParticipantId', $participantId)
                        ->whereDate('CalenderDate', $dateOnly)
                        ->where('Trans_flag', $type)
                        ->update([
                            'AttendanceYN' => 'N',
                            'update_flag'  => $update_flag,
                            'user_cd'      => $user_cd,
                            'update_dt'    => $update_dt,
                            'company_id'   => $company_id,
                        ]);
                }
            }

            DB::commit();

            $message = 'Weekly Attendance saved successfully!';

            if ($skippedOtherShiftCount > 0) {
                $message .= ' Some records were skipped because attendance already exists in the other shift.';
            }

            return redirect()
                ->route('attendance.weekly.entry_mark', [
                    'week_start_date' => $request->input('week_start_date'),
                    'type'            => $type,
                    'temp_members'    => $request->input('temp_members', []),
                ])
                ->with([
                    'message'    => $message,
                    'alert-type' => 'success',
                ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Weekly attendance save failed: ' . $e->getMessage());

            return redirect()->back()->with([
                'message'    => 'Weekly Attendance save failed. Please check laravel.log.',
                'alert-type' => 'error',
            ]);
        }

    } // End Method

    // This is Report Part

   public function attendanceReport(Request $request, string $type = 'A'){

    $type = in_array(strtoupper($type), ['A','N'], true) ? strtoupper($type) : 'A';
    if (!$request->start_date || !$request->end_date) {

        return view('attendance.attendance_report' , compact('type') );

    }

    $startDate = Carbon::parse($request->start_date)->startOfDay();
    $endDate = Carbon::parse($request->end_date)->endOfDay();

    $data = $this->getAttendanceReportData_new($startDate, $endDate, $type);

    return view('attendance.attendance_report_view', $data + ['type' => $type]);

   } // End Method


   public function attendanceReportCheck(Request $request, string $type = 'A'){

    $type = in_array(strtoupper($type), ['A','N'], true) ? strtoupper($type) : 'A';

    if (!$request->start_date || !$request->end_date) {

        return view('attendance.attendance_report_check', compact('type') );

    }

    $startDate = Carbon::parse($request->start_date)->startOfDay();
    $endDate = Carbon::parse($request->end_date)->endOfDay();

    $data = $this->getAttendanceReportData_new($startDate, $endDate, $type);

    return view('attendance.attendance_report_view_checked', $data + ['type' => $type]);

   } // End Method



    public function exportPDF(Request $request, string $type = 'A')
{
    $type = in_array(strtoupper($type), ['A','N'], true) ? strtoupper($type) : 'A';
    $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

    $data = $this->getAttendanceReportData_new($startDate, $endDate, $type);

    // Restric to use only max  8 day for PDF Report
    if (count($data['dates']) > 8) {
        $data['dates'] = array_slice($data['dates'], 0, 8);

        // Also slice attendanceData for each participant to match 8 days
        foreach ($data['attendanceData'] as &$participant) {
            $participant['attendance'] = array_intersect_key(
                $participant['attendance'],
                array_flip($data['dates'])
            );
        }

        // Also slice columnTotals to match 8 days
        $data['columnTotals'] = array_intersect_key(
            $data['columnTotals'],
            array_flip($data['dates'])

        );

         // 🛠 Add this line also to fix the header:
        $data['startDate'] = $data['dates'][0];
        $data['endDate'] = $data['dates'][count($data['dates']) - 1];
    }

    $pdf = Pdf::loadView('exports.attendance_pdf',
            [
                'attendanceData' => $data['attendanceData'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'dates' => $data['dates'], // <- this is needed for the foreach
                'columnTotals' => $data['columnTotals'],
                'grandTotal' => array_sum($data['columnTotals']),
            ])->setPaper('A4', 'landscape')->setWarnings(false)
            ->setOptions(['font' => 'Helvetica',
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'defaultFont' => 'Helvetica',
                   'isRemoteEnabled' => true,
            ]);

    // Add custom footer by using the DomPDF options.
     $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

    // The footer will be included directly in the view.
    $pdfContent = $pdf->output(); // Generate PDF content.

    return $pdf->stream('attendance-report.pdf');


} // End Method


  public function exportPDFChecked(Request $request, string $type = 'A')
{
    $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

    $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $endDate   = \Carbon\Carbon::parse($request->end_date)->endOfDay();

    $data = $this->getAttendanceReportData_new($startDate, $endDate, $type);

    /*
     * Restrict to max 8 days for PDF Report.
     */
    if (count($data['dates']) > 8) {

        $data['dates'] = array_slice($data['dates'], 0, 8);

        foreach ($data['attendanceData'] as &$participant) {
            $participant['attendance'] = array_intersect_key(
                $participant['attendance'],
                array_flip($data['dates'])
            );
        }

        unset($participant);

        $data['columnTotals'] = array_intersect_key(
            $data['columnTotals'],
            array_flip($data['dates'])
        );

        $data['startDate'] = $data['dates'][0];
        $data['endDate']   = $data['dates'][count($data['dates']) - 1];
    }

    /*
     * Apply screen sorting to PDF.
     * These values come from hidden fields in report Blade.
     */
    $sortColumn = $request->input('sort_column', 'ParticipantId');
    $sortDir    = strtolower($request->input('sort_dir', 'asc'));

    $allowedSorts = [
        'ParticipantId',
        'LastName',
        'FirstName',
        'row_total',
    ];

    if (!in_array($sortColumn, $allowedSorts, true)) {
        $sortColumn = 'ParticipantId';
    }

    if (!in_array($sortDir, ['asc', 'desc'], true)) {
        $sortDir = 'asc';
    }

    $attendanceCollection = collect($data['attendanceData']);

    /*
     * ParticipantId should sort naturally as number where possible.
     * Example: 005, 010, 100.
     */
    if ($sortColumn === 'ParticipantId') {

        if ($sortDir === 'desc') {
            $attendanceCollection = $attendanceCollection
                ->sortByDesc(function ($row) {
                    return (int) ($row['ParticipantId'] ?? 0);
                })
                ->values();
        } else {
            $attendanceCollection = $attendanceCollection
                ->sortBy(function ($row) {
                    return (int) ($row['ParticipantId'] ?? 0);
                })
                ->values();
        }

    } elseif ($sortColumn === 'row_total') {

        if ($sortDir === 'desc') {
            $attendanceCollection = $attendanceCollection
                ->sortByDesc(function ($row) {
                    return (int) ($row['row_total'] ?? 0);
                })
                ->values();
        } else {
            $attendanceCollection = $attendanceCollection
                ->sortBy(function ($row) {
                    return (int) ($row['row_total'] ?? 0);
                })
                ->values();
        }

    } else {

        if ($sortDir === 'desc') {
            $attendanceCollection = $attendanceCollection
                ->sortByDesc(function ($row) use ($sortColumn) {
                    return strtoupper(trim((string) ($row[$sortColumn] ?? '')));
                })
                ->values();
        } else {
            $attendanceCollection = $attendanceCollection
                ->sortBy(function ($row) use ($sortColumn) {
                    return strtoupper(trim((string) ($row[$sortColumn] ?? '')));
                })
                ->values();
        }
    }

    $data['attendanceData'] = $attendanceCollection->all();

    $pdf = Pdf::loadView('exports.attendance_pdf_checked', [
            'attendanceData' => $data['attendanceData'],
            'startDate'      => $data['startDate'],
            'endDate'        => $data['endDate'],
            'dates'          => $data['dates'],
            'columnTotals'   => $data['columnTotals'],
            'grandTotal'     => array_sum($data['columnTotals']),
        ])
        ->setPaper('A4', 'landscape')
        ->setWarnings(false)
        ->setOptions([
            'font'                 => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => true,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => true,
        ]);

    return $pdf->stream('attendance-report-checked.pdf');
}


public function exportExcel(Request $request, string $type = 'A')
{
    $type = in_array(strtoupper($type), ['A','N'], true) ? strtoupper($type) : 'A';

    $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

    $data = $this->getAttendanceReportData_new($startDate, $endDate, $type);

    $grandTotal = is_array($data['columnTotals']) ? array_sum($data['columnTotals']) : 0;

    return Excel::download(
        new AttendanceReportExportNew(
            //$data['attendanceData'],
            //$data['dateRange'],
            //$startDate,
            //$endDate,
           // $data['columnTotals'],
            //array_sum($data['columnTotals'])

            $data['attendanceData'],          // Your array of rows
            $data['dateRange'],          // Array of date strings (Y-m-d)
            $data['columnTotals'],   // Associative array date => total
            $grandTotal,     // Grand total
            $compname = config('company.comp_nm'), // compname/title
            $startDate,      // Optional: date range
            $endDate
        ),
        'attendance_report.xlsx'
    );
} // End Method

public function exportExcelBilling(Request $request, string $type = 'A'){

    $type = in_array(strtoupper($type), ['A','N'], true) ? strtoupper($type) : 'A';
    $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
    $data = $this->getAttendanceReportData_new($startDate, $endDate, $type);
    $grandTotal = is_array($data['columnTotals']) ? array_sum($data['columnTotals']) : 0;

    return Excel::download(
        new AttendanceReportExportBilling(
            //$data['attendanceData'],
            //$data['dateRange'],
            //$startDate,
            //$endDate,
           // $data['columnTotals'],
            //array_sum($data['columnTotals'])

            $data['attendanceData'],          // Your array of rows
            $data['dateRange'],          // Array of date strings (Y-m-d)
            $data['columnTotals'],   // Associative array date => total
            $grandTotal,     // Grand total
            $compname = config('company.comp_nm'), // compname/title
            $startDate,      // Optional: date range
            $endDate
        ),
        'attendance_report_billing.xlsx'
    );


} // End Method

public static function getAttendanceReportData($dateRangeStart, $dateRangeEnd, $type)
{
    $startDate = $dateRangeStart;
    $endDate = $dateRangeEnd;

    // Get all participants
    $records = DB::table('members as m')
        ->leftJoin('partattendence as a', function ($join) {
            $join->on(DB::raw('TRIM(m.ParticipantId)'), '=', DB::raw('TRIM(a.ParticipantId)'));
        })
        ->where('m.Trans_flag','=', 'A')
        //->whereDate('a.CalenderDate', '>=', $startDate)
        //->whereDate('a.CalenderDate', '<=', $endDate)
        //->orWhereNull('a.CalenderDate')
        ->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('a.CalenderDate', [$startDate, $endDate])
                  ->orWhereNull('a.CalenderDate');
        })
        ->select(
            DB::raw('TRIM(m.ParticipantId) as ParticipantId'),
            DB::raw('TRIM(m.LastName) as LastName'),
            DB::raw('TRIM(m.FirstName) as FirstName'),
            'a.CalenderDate',
            'a.AttendanceYN'
        )
        ->orderBy('ParticipantId')
        ->get();

    // Build date range
    $dateRange = [];
    $currentDate = $startDate->copy();
    while ($currentDate <= $endDate) {
        $dateRange[] = $currentDate->toDateString();
        $currentDate->addDay();
    }

    // Initialize the report data array
    $attendanceData = [];
    $columnTotals = [];

    foreach ($records as $row) {
        $pid = trim($row->ParticipantId);

        if (!isset($attendanceData[$pid])) {
            $attendanceData[$pid] = [
                'ParticipantId' => $pid,
                'LastName' => $row->LastName,
                'FirstName' => $row->FirstName,
                'attendance' => [],
                'row_total' => 0,
            ];
        }

        if ($row->CalenderDate !== null) {
            $date = \Carbon\Carbon::parse($row->CalenderDate)->toDateString();
            $attendanceValue = $row->AttendanceYN === 'Y' ? 1 : 0;

            // Set attendance
            $attendanceData[$pid]['attendance'][$date] = $attendanceValue;

            // Update totals
            $attendanceData[$pid]['row_total'] += $attendanceValue;

            if (!isset($columnTotals[$date])) {
                $columnTotals[$date] = 0;
            }
            $columnTotals[$date] += $attendanceValue;
        }
    }

    sort($dateRange);

    foreach ($attendanceData as &$data) {
        foreach ($dateRange as $date) {
            if (!isset($data['attendance'][$date])) {
                $data['attendance'][$date] = 0;
            }
        }
        ksort($data['attendance']);
    }

    unset($data);

    //$dateRange = collect($dateRange)
    //->map(fn($date) => \Carbon\Carbon::parse($date))  // Convert strings to Carbon instances
    //->sort()  // Sort Carbon instances
    //->map(fn($date) => $date->format('Y-m-d'))  // Convert back to string format after sorting
    //->toArray();  // Convert back to array


    // Return the data array instead of a view
    return [
        'attendanceData' => $attendanceData,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'dateRange' => $dateRange,
        'columnTotals' => $columnTotals,
        'dates' => $dateRange, // <--- add this line for backward compatibility
    ];
   } // End Method


    // public static function getAttendanceReportData_new($dateRangeStart, $dateRangeEnd, $type)
    // {
    //     $startDate = $dateRangeStart;
    //     $endDate = $dateRangeEnd;

    //     // Step 1: Get all participants (active only)
    //     $members = DB::table('members')
    //         ->select(DB::raw('TRIM(ParticipantId) as ParticipantId'),
    //                 DB::raw('TRIM(LastName) as LastName'),
    //                 DB::raw('TRIM(FirstName) as FirstName'),
    //                 DB::raw('TRIM(MiddleName) as MiddleName'),
    //                 DB::raw('TRIM(HMO_enrolled) as HMO_enrolled'),
    //                 DB::raw('TRIM(medicaid_HMO_id) as medicaid_HMO_id'),
    //                 DB::raw('TRIM(medicaid_PA_no) as medicaid_PA_no'),
    //                 DB::raw('TRIM(medicaid_PA_Start) as medicaid_PA_Start'),
    //                 DB::raw('TRIM(medicaid_PA_End) as medicaid_PA_End'),
    //                 DB::raw('TRIM(PrimaryDiagnosis1) as PrimaryDiagnosis1'),
    //                 DB::raw('TRIM(SecondaryDiag1) as SecondaryDiag1'),
    //                 DB::raw('TRIM(DateOfBirth) as DateOfBirth'),
    //                 DB::raw('TRIM(SSNo) as SSNo'),
    //                 DB::raw('TRIM(Sex) as Sex')
    //             )
    //         ->where('Trans_flag', $type)
    //         ->orderBy('ParticipantId')
    //         ->get();

    //     // Step 2: Get all attendance for date range
    //     $attendances = DB::table('partattendence')
    //         ->select(DB::raw('TRIM(ParticipantId) as ParticipantId'), 'CalenderDate', 'AttendanceYN')
    //         ->whereBetween('CalenderDate', [$startDate, $endDate])
    //         ->get();

    //     // Step 3: Index attendance by ParticipantId + Date
    //     $attendanceMap = [];
    //     foreach ($attendances as $a) {
    //         $pid = trim($a->ParticipantId);
    //         $date = \Carbon\Carbon::parse($a->CalenderDate)->toDateString();
    //         $attendanceMap[$pid][$date] = ($a->AttendanceYN === 'Y') ? 1 : 0;
    //     }

    //     // Step 4: Build date range array
    //     $dateRange = [];
    //     $currentDate = $startDate->copy();
    //     while ($currentDate <= $endDate) {
    //         $dateRange[] = $currentDate->toDateString();
    //         $currentDate->addDay();
    //     }
    //     sort($dateRange);

    //     // Step 5: Build attendanceData
    //     $attendanceData = [];
    //     $columnTotals = array_fill_keys($dateRange, 0);

    //     foreach ($members as $m) {
    //         $pid = trim($m->ParticipantId);
    //         $row = [
    //             'ParticipantId' => $pid,
    //             'LastName' => $m->LastName,
    //             'FirstName' => $m->FirstName,
    //             'MiddleName' => $m->MiddleName,
    //             'HmoEnrolled' => $m->HMO_enrolled,
    //             'MedicaidHMOId' => $m->medicaid_HMO_id,
    //             'MedicaidPANo' => $m->medicaid_PA_no,
    //             'MedicaidPAStart' => $m->medicaid_PA_Start,
    //             'MedicaidPAEnd' => $m->medicaid_PA_End,
    //             'PrimaryICD' => $m->PrimaryDiagnosis1,
    //             'SecondaryICD' => $m->SecondaryDiag1,
    //             'DOB' => $m->DateOfBirth,
    //             'SS#' => $m->SSNo,
    //             'Sex' => $m->Sex,
    //             'attendance' => [],
    //             'row_total' => 0,
    //         ];
    //         foreach ($dateRange as $date) {
    //             $val = isset($attendanceMap[$pid][$date]) ? $attendanceMap[$pid][$date] : 0;
    //             $row['attendance'][$date] = $val;
    //             $row['row_total'] += $val;
    //             $columnTotals[$date] += $val;
    //         }
    //         $attendanceData[$pid] = $row;
    //     }

    //     return [
    //         'attendanceData' => $attendanceData,
    //         'startDate' => $startDate,
    //         'endDate' => $endDate,
    //         'dateRange' => $dateRange,
    //         'columnTotals' => $columnTotals,
    //         'dates' => $dateRange,
    //     ];

    // } //End Method

    public static function getAttendanceReportData_new($dateRangeStart, $dateRangeEnd, $type)
    {
        $startDate = $dateRangeStart;
        $endDate = $dateRangeEnd;

        $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

        /*
        * Step 1:
        * Get attendance for selected date range and selected ACTUAL shift.
        *
        * Important:
        * partattendence.Trans_flag = actual attended shift
        * members.Trans_flag       = default/home shift
        */
        $attendances = DB::table('partattendence')
            ->select(
                DB::raw('TRIM(ParticipantId) as ParticipantId'),
                'CalenderDate',
                'AttendanceYN',
                'Trans_flag'
            )
            ->whereBetween('CalenderDate', [$startDate, $endDate])
            ->where('Trans_flag', $type)
            ->get();

        /*
        * Step 2:
        * Get only participant IDs who have actual attendance
        * in this selected shift/date range.
        */
        $participantIds = $attendances
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return trim($id);
            })
            ->unique()
            ->values()
            ->toArray();

        /*
        * Step 3:
        * Load member details for only those participants.
        *
        * This allows Morning member temporarily attending Noon
        * to appear in Noon report.
        */
        $members = collect();

        if (!empty($participantIds)) {
            $members = DB::table('members')
                ->select(
                    DB::raw('TRIM(ParticipantId) as ParticipantId'),
                    DB::raw('TRIM(LastName) as LastName'),
                    DB::raw('TRIM(FirstName) as FirstName'),
                    DB::raw('TRIM(MiddleName) as MiddleName'),
                    DB::raw('TRIM(HMO_enrolled) as HMO_enrolled'),
                    DB::raw('TRIM(medicaid_HMO_id) as medicaid_HMO_id'),
                    DB::raw('TRIM(medicaid_PA_no) as medicaid_PA_no'),
                    DB::raw('TRIM(medicaid_PA_Start) as medicaid_PA_Start'),
                    DB::raw('TRIM(medicaid_PA_End) as medicaid_PA_End'),
                    //DB::raw('TRIM(renew1_HMO_id) as renew1_HMO_id'),
                    //DB::raw('TRIM(renew1_PA_no) as renew1_PA_no'),
                    //DB::raw('TRIM(renew1_PA_Start) as renew1_PA_Start'),
                    //DB::raw('TRIM(renew1_PA_End) as renew1_PA_End'),
                    DB::raw('TRIM(PrimaryDiagnosis1) as PrimaryDiagnosis1'),
                    DB::raw('TRIM(SecondaryDiag1) as SecondaryDiag1'),
                    DB::raw('TRIM(DateOfBirth) as DateOfBirth'),
                    DB::raw('TRIM(SSNo) as SSNo'),
                    DB::raw('TRIM(Sex) as Sex'),
                    DB::raw('TRIM(Trans_flag) as DefaultTransFlag')
                )
                ->whereIn('ParticipantId', $participantIds)
                ->orderBy('ParticipantId')
                ->get();
        }

        /*
        * Step 4:
        * Index attendance by ParticipantId + Date.
        */
        $attendanceMap = [];

        foreach ($attendances as $a) {
            $pid = trim($a->ParticipantId);
            $date = \Carbon\Carbon::parse($a->CalenderDate)->toDateString();

            $attendanceMap[$pid][$date] = ($a->AttendanceYN === 'Y') ? 1 : 0;
        }

        /*
        * Step 5:
        * Build date range array.
        */
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->toDateString();
            $currentDate->addDay();
        }

        sort($dateRange);

        /*
        * Step 6:
        * Build attendance data.
        */
        $attendanceData = [];
        $columnTotals = array_fill_keys($dateRange, 0);

        foreach ($members as $m) {
            $pid = trim($m->ParticipantId);

            $row = [
                'ParticipantId' => $pid,
                'LastName' => $m->LastName,
                'FirstName' => $m->FirstName,
                'MiddleName' => $m->MiddleName,
                'HmoEnrolled' => $m->HMO_enrolled,
                'MedicaidHMOId' => $m->medicaid_HMO_id,
                'MedicaidPANo' => $m->medicaid_PA_no,
                'MedicaidPAStart' => $m->medicaid_PA_Start,
                'MedicaidPAEnd' => $m->medicaid_PA_End,
                //'Renew1HMOId' => $m->renew1_HMO_id,
                //'Renew1PANo' => $m->renew1_PA_no,
                //'Renew1PAStart' => $m->renew1_PA_Start,
                //'Renew1PAEnd' => $m->renew1_PA_End,
                'PrimaryICD' => $m->PrimaryDiagnosis1,
                'SecondaryICD' => $m->SecondaryDiag1,
                'DOB' => $m->DateOfBirth,
                'SS#' => $m->SSNo,
                'Sex' => $m->Sex,

                /*
                * Optional:
                * This is member's default shift, not actual report shift.
                * Useful later if you want to highlight temporary-shift members.
                */
                'DefaultTransFlag' => $m->DefaultTransFlag,

                'attendance' => [],
                'row_total' => 0,
            ];

            foreach ($dateRange as $date) {
                $val = isset($attendanceMap[$pid][$date]) ? $attendanceMap[$pid][$date] : 0;

                $row['attendance'][$date] = $val;
                $row['row_total'] += $val;
                $columnTotals[$date] += $val;
            }

            $attendanceData[$pid] = $row;
        }

        return [
            'attendanceData' => $attendanceData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRange' => $dateRange,
            'columnTotals' => $columnTotals,
            'dates' => $dateRange,
        ];

    } // End Method

    public function memberWiseReport(Request $request)
    {
        $participantId = trim((string) $request->get('ParticipantId'));
        $startDateInput = trim((string) $request->get('start_date'));
        $endDateInput   = trim((string) $request->get('end_date'));

        $member = null;
        $rows = collect();
        $dateRangeText = '';
        $validated = false;

        if ($participantId !== '' || $startDateInput !== '' || $endDateInput !== '') {
            $request->validate([
                'ParticipantId' => ['required', 'string', 'max:20'],
                'start_date'    => ['required', 'date_format:m/d/Y'],
                'end_date'      => ['required', 'date_format:m/d/Y'],
            ], [
                'ParticipantId.required' => 'Please select a member.',
                'start_date.required'    => 'Start date is required.',
                'end_date.required'      => 'End date is required.',
            ]);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();
            $today     = now()->endOfDay();

            if ($endDate->lt($startDate)) {
                return back()
                    ->withErrors(['end_date' => 'End date cannot be less than start date.'])
                    ->withInput();
            }

            if ($startDate->gt($today) || $endDate->gt($today)) {
                return back()
                    ->withErrors(['end_date' => 'Future dates are not allowed.'])
                    ->withInput();
            }

            $member = DB::table('members')
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName',
                    'HMO_enrolled',
                    'medicaid_HMO_id',
                    'medicaid_PA_no'
                )
                ->where('ParticipantId', $participantId)
                ->first();

            if (!$member) {
                return back()
                    ->withErrors(['ParticipantId' => 'Selected member was not found.'])
                    ->withInput();
            }

            $coverageHistory = DB::table('member_coverage_history')
            ->select(
                'ParticipantId',
                'effective_from',
                'effective_to',
                'HMO_enrolled',
                'medicaid_HMO_id',
                'medicaid_PA_no'
            )
            ->where('ParticipantId', $participantId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereNull('effective_to')
                ->orWhere('effective_to', '>=', $startDate->toDateString());
            })
            ->where('effective_from', '<=', $endDate->toDateString())
            ->orderBy('effective_from')
            ->get();

            $attendanceRows = DB::table('partattendence')
            ->select('CalenderDate', 'AttendanceYN')
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->orderBy('CalenderDate', 'asc')
            ->get();

        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $key = Carbon::parse($row->CalenderDate)->format('Y-m-d');
            $attendanceMap[$key] = strtoupper((string) $row->AttendanceYN) === 'Y' ? '✓' : '';
        }

        $rows = collect();

        $currentDate = $startDate->copy()->startOfDay();
        $lastDate = $endDate->copy()->startOfDay();

        while ($currentDate->lte($endDate->copy()->startOfDay())) {
            $key = $currentDate->format('Y-m-d');
            $matchedCoverage = null;

                        foreach ($coverageHistory as $hist) {
                $from = Carbon::parse($hist->effective_from)->startOfDay();
                $to = !empty($hist->effective_to)
                    ? Carbon::parse($hist->effective_to)->endOfDay()
                    : null;

                if (
                    $currentDate->gte($from) &&
                    (is_null($to) || $currentDate->lte($to))
                ) {
                    $matchedCoverage = $hist;
                    break;
                }
            }

            $rows->push((object) [
                'report_date'      => $currentDate->format('m/d/Y'),
                'day_name'         => $currentDate->format('D'),   // Thu, Fri, Sat
                'attendance_mark'  => $attendanceMap[$key] ?? '',
                'insurance_name'   => $matchedCoverage->HMO_enrolled ?? $member->HMO_enrolled ?? '',
                'insurance_id'     => $matchedCoverage->medicaid_HMO_id ?? $member->medicaid_HMO_id ?? '',
                'prior_auth_no'    => $matchedCoverage->medicaid_PA_no ?? $member->medicaid_PA_no ?? '',
            ]);

            $currentDate->addDay();
        }
            $totalDaysPresent = $rows->where('attendance_mark', '✓')->count();
            $totalDaysInRange = $rows->count();
            $dateRangeText = $startDate->format('m/d/Y') . ' - ' . $endDate->format('m/d/Y');
            $validated = true;
        }

        return view('attendance.member_wise_report', [
            'member'         => $member,
            'rows'           => $rows,
            'validated'      => $validated,
            'participantId'  => $participantId,
            'startDateInput' => $startDateInput,
            'endDateInput'   => $endDateInput,
            'dateRangeText'  => $dateRangeText,
            'totalDaysPresent'  => $totalDaysPresent ?? 0,
            'totalDaysInRange'  => $totalDaysInRange ?? 0,
        ]);

    } // End Method

    public function searchMembersForAttendance(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('ParticipantId', 'like', "%{$term}%")
                    ->orWhere('LastName', 'like', "%{$term}%")
                    ->orWhere('FirstName', 'like', "%{$term}%");
                });
            })
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->limit(50)
            ->get();

        $results = $members->map(function ($m) {
            return [
                'id'   => $m->ParticipantId,
                'text' => $m->ParticipantId . ' - ' . $m->LastName . ', ' . $m->FirstName,
            ];
        });

        return response()->json(['results' => $results]);

    } // End Method

    public function memberWisePdf(Request $request)
    {
        $participantId = trim((string) $request->get('ParticipantId'));
        $startDateInput = trim((string) $request->get('start_date'));
        $endDateInput   = trim((string) $request->get('end_date'));

        $request->validate([
            'ParticipantId' => ['required', 'string', 'max:20'],
            'start_date'    => ['required', 'date_format:m/d/Y'],
            'end_date'      => ['required', 'date_format:m/d/Y'],
        ]);

        $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
        $endDate   = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();

        if ($endDate->lt($startDate)) {
            return back()->withErrors(['end_date' => 'End date cannot be less than start date.']);
        }

        if ($startDate->gt(now()) || $endDate->gt(now())) {
            return back()->withErrors(['end_date' => 'Future dates are not allowed.']);
        }

        $member = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'HMO_enrolled',
                'medicaid_HMO_id',
                'medicaid_PA_no'
            )
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return back()->withErrors(['ParticipantId' => 'Selected member was not found.']);
        }

        $coverageHistory = DB::table('member_coverage_history')
            ->select(
                'ParticipantId',
                'effective_from',
                'effective_to',
                'HMO_enrolled',
                'medicaid_HMO_id',
                'medicaid_PA_no'
            )
            ->where('ParticipantId', $participantId)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $startDate->toDateString());
            })
            ->where('effective_from', '<=', $endDate->toDateString())
            ->orderBy('effective_from')
            ->get();

        $attendanceRows = DB::table('partattendence')
            ->select('CalenderDate', 'AttendanceYN')
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->orderBy('CalenderDate', 'asc')
            ->get();

        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $key = Carbon::parse($row->CalenderDate)->format('Y-m-d');
            $attendanceMap[$key] = strtoupper((string) $row->AttendanceYN) === 'Y' ? '✓' : '';
        }

        $rows = collect();

        $currentDate = $startDate->copy()->startOfDay();
        $lastDate    = $endDate->copy()->startOfDay();

        while ($currentDate->lte($lastDate)) {
            $key = $currentDate->format('Y-m-d');
            $matchedCoverage = null;

            foreach ($coverageHistory as $hist) {
            $from = Carbon::parse($hist->effective_from)->startOfDay();
            $to = !empty($hist->effective_to)
                ? Carbon::parse($hist->effective_to)->endOfDay()
                : null;

            if (
                $currentDate->gte($from) &&
                (is_null($to) || $currentDate->lte($to))
            ) {
                $matchedCoverage = $hist;
                break;
            }
            }

            $rows->push((object) [
                'report_date'      => $currentDate->format('m/d/Y'),
                'day_name'         => $currentDate->format('D'),
                'attendance_mark'  => $attendanceMap[$key] ?? '',
                'insurance_name'   => $matchedCoverage->HMO_enrolled ?? $member->HMO_enrolled ?? '',
                'insurance_id'     => $matchedCoverage->medicaid_HMO_id ?? $member->medicaid_HMO_id ?? '',
                'prior_auth_no'    => $matchedCoverage->medicaid_PA_no ?? $member->medicaid_PA_no ?? '',
            ]);

            $currentDate->addDay();

        }

        $totalDaysPresent = $rows->where('attendance_mark', '✓')->count();
        $totalDaysInRange = $rows->count();
        $dateRangeText = $startDate->format('m/d/Y') . ' - ' . $endDate->format('m/d/Y');

        $html = view('attendance.member_wise_report_pdf', [
            'member'        => $member,
            'rows'          => $rows,
            'dateRangeText' => $dateRangeText,
            'totalDaysPresent'  => $totalDaysPresent,
            'totalDaysInRange'  => $totalDaysInRange,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'member_wise_attendance_' . $participantId . '.pdf';
        return response($mpdf->Output($fileName, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');

    } // End Method

    public function memberWiseExcel(Request $request)
    {
        $participantId = trim((string) $request->get('ParticipantId'));
        $startDateInput = trim((string) $request->get('start_date'));
        $endDateInput   = trim((string) $request->get('end_date'));

        $request->validate([
            'ParticipantId' => ['required', 'string', 'max:20'],
            'start_date'    => ['required', 'date_format:m/d/Y'],
            'end_date'      => ['required', 'date_format:m/d/Y'],
        ]);

        $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
        $endDate   = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();

        if ($endDate->lt($startDate)) {
            return back()->withErrors(['end_date' => 'End date cannot be less than start date.']);
        }

        if ($startDate->gt(now()) || $endDate->gt(now())) {
            return back()->withErrors(['end_date' => 'Future dates are not allowed.']);
        }

        return Excel::download(
            new MemberWiseAttendanceExport($participantId, $startDateInput, $endDateInput),
            'member_wise_attendance_' . $participantId . '.xlsx'
        );

    } // End Method

    public function memberRangeCrosstabReport(Request $request)
    {
        $participantId   = trim((string) $request->get('ParticipantId'));
        $startYearMonth  = trim((string) $request->get('start_month'));
        $endYearMonth    = trim((string) $request->get('end_month'));

        $member = null;
        $months = [];
        $dayTotals = array_fill(1, 31, 0);
        $grandTotal = 0;
        $validated = false;

        if ($participantId !== '' || $startYearMonth !== '' || $endYearMonth !== '') {
            $request->validate([
                'ParticipantId' => ['required', 'string', 'max:20'],
                'start_month'   => ['required', 'date_format:Y-m'],
                'end_month'     => ['required', 'date_format:Y-m'],
            ], [
                'ParticipantId.required' => 'Please select a member.',
                'start_month.required'   => 'Please select start year-month.',
                'end_month.required'     => 'Please select end year-month.',
            ]);

            $startDate = Carbon::createFromFormat('Y-m', $startYearMonth)->startOfMonth()->startOfDay();
            $endDate   = Carbon::createFromFormat('Y-m', $endYearMonth)->endOfMonth()->endOfDay();

            if ($endDate->lt($startDate)) {
                return back()
                    ->withErrors(['end_month' => 'End year-month cannot be less than start year-month.'])
                    ->withInput();
            }

            $member = DB::table('members')
                ->select('ParticipantId', 'LastName', 'FirstName')
                ->where('ParticipantId', $participantId)
                ->first();

            if (!$member) {
                return back()
                    ->withErrors(['ParticipantId' => 'Selected member was not found.'])
                    ->withInput();
            }

            [$months, $dayTotals, $grandTotal] = $this->buildMemberRangeAttendanceMatrix(
                $participantId,
                $startDate,
                $endDate
            );

            $validated = true;
        }

        return view('attendance.member_range_crosstab_report', [
            'member'          => $member,
            'months'          => $months,
            'dayTotals'       => $dayTotals,
            'grandTotal'      => $grandTotal,
            'validated'       => $validated,
            'participantId'   => $participantId,
            'startYearMonth'  => $startYearMonth,
            'endYearMonth'    => $endYearMonth,
        ]);

    } // End Method

    public function memberRangeCrosstabPdf(Request $request)
    {
        $participantId   = trim((string) $request->get('ParticipantId'));
        $startYearMonth  = trim((string) $request->get('start_month'));
        $endYearMonth    = trim((string) $request->get('end_month'));

        $request->validate([
            'ParticipantId' => ['required', 'string', 'max:20'],
            'start_month'   => ['required', 'date_format:Y-m'],
            'end_month'     => ['required', 'date_format:Y-m'],
        ]);

        $startDate = Carbon::createFromFormat('Y-m', $startYearMonth)->startOfMonth()->startOfDay();
        $endDate   = Carbon::createFromFormat('Y-m', $endYearMonth)->endOfMonth()->endOfDay();

        if ($endDate->lt($startDate)) {
            return back()->withErrors(['end_month' => 'End year-month cannot be less than start year-month.']);
        }

        $member = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return back()->withErrors(['ParticipantId' => 'Selected member was not found.']);
        }

        [$months, $dayTotals, $grandTotal] = $this->buildMemberRangeAttendanceMatrix(
            $participantId,
            $startDate,
            $endDate
        );

        $html = view('attendance.member_range_crosstab_report_pdf', [
            'member'          => $member,
            'months'          => $months,
            'dayTotals'       => $dayTotals,
            'grandTotal'      => $grandTotal,
            'startYearMonth'  => $startYearMonth,
            'endYearMonth'    => $endYearMonth,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'member_range_attendance_' . $participantId . '_' . $startYearMonth . '_to_' . $endYearMonth . '.pdf';

        return response($mpdf->Output($fileName, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');

    } // End Method

    public function memberRangeCrosstabExcel(Request $request)
    {
        $participantId   = trim((string) $request->get('ParticipantId'));
        $startYearMonth  = trim((string) $request->get('start_month'));
        $endYearMonth    = trim((string) $request->get('end_month'));

        $request->validate([
            'ParticipantId' => ['required', 'string', 'max:20'],
            'start_month'   => ['required', 'date_format:Y-m'],
            'end_month'     => ['required', 'date_format:Y-m'],
        ]);

        return Excel::download(
            new \App\Exports\MemberRangeAttendanceExport($participantId, $startYearMonth, $endYearMonth),
            'member_range_attendance_' . $participantId . '_' . $startYearMonth . '_to_' . $endYearMonth . '.xlsx'
        );

    } // End Method

    private function buildMemberRangeAttendanceMatrix(string $participantId, Carbon $startDate, Carbon $endDate): array
    {
        $attendanceRows = DB::table('partattendence')
            ->select('CalenderDate', 'AttendanceYN')
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->orderBy('CalenderDate')
            ->get();

        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $dateKey = Carbon::parse($row->CalenderDate)->format('Y-m-d');
            $attendanceMap[$dateKey] = strtoupper((string) $row->AttendanceYN) === 'Y' ? 1 : '';
        }

        $months = [];
        $dayTotals = array_fill(1, 31, 0);
        $grandTotal = 0;

        $cursor = $startDate->copy()->startOfMonth();
        $lastMonth = $endDate->copy()->startOfMonth();

        while ($cursor->lte($lastMonth)) {
            $year = $cursor->year;
            $month = $cursor->month;
            $daysInMonth = $cursor->daysInMonth;

            $row = [
                'month_key'   => $cursor->format('Y-m'),
                'month_name'  => $cursor->format('M Y'),
                'days'        => [],
                'row_total'   => 0,
            ];

            for ($day = 1; $day <= 31; $day++) {
                if ($day <= $daysInMonth) {
                    $dateKey = Carbon::create($year, $month, $day)->format('Y-m-d');
                    $value = $attendanceMap[$dateKey] ?? '';

                    $row['days'][$day] = $value;

                    if ($value === 1) {
                        $row['row_total']++;
                        $dayTotals[$day]++;
                        $grandTotal++;
                    }
                } else {
                    $row['days'][$day] = '';
                }
            }

            $months[] = $row;
            $cursor->addMonth();
        }

        return [$months, $dayTotals, $grandTotal];

    } // End Method


    public function hmoCrosstabReport(Request $request)
    {
        $selectedHmo    = trim((string) $request->get('hmo_enrolled'));
        $startDateInput = trim((string) $request->get('start_date'));
        $endDateInput   = trim((string) $request->get('end_date'));

        $hmoOptions = DB::table('hmodropdown')
            ->orderBy('name')
            ->get();

        $rows = [];
        $dateHeaders = [];
        $columnTotals = [];
        $grandTotal = 0;
        $validated = false;

        if ($selectedHmo !== '' || $startDateInput !== '' || $endDateInput !== '') {
            $request->validate([
                'hmo_enrolled' => ['required', 'string', 'max:100'],
                'start_date'   => ['required', 'date_format:m/d/Y'],
                'end_date'     => ['required', 'date_format:m/d/Y'],
            ], [
                'hmo_enrolled.required' => 'Please select HMO.',
                'start_date.required'   => 'Start date is required.',
                'end_date.required'     => 'End date is required.',
            ]);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
            $endDate   = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();

            if ($endDate->lt($startDate)) {
                return back()
                    ->withErrors(['end_date' => 'End date cannot be less than start date.'])
                    ->withInput();
            }

            if ($startDate->gt(now()) || $endDate->gt(now())) {
                return back()
                    ->withErrors(['end_date' => 'Future dates are not allowed.'])
                    ->withInput();
            }

            if ($startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) > 30) {
                return back()
                    ->withErrors(['end_date' => 'Date range cannot exceed 31 days.'])
                    ->withInput();
            }

            [$rows, $dateHeaders, $columnTotals, $grandTotal] = $this->buildHmoAttendanceCrosstab(
                $selectedHmo,
                $startDate,
                $endDate
            );

            $validated = true;
        }

        return view('attendance.hmo_crosstab_report', [
            'hmoOptions'      => $hmoOptions,
            'selectedHmo'     => $selectedHmo,
            'startDateInput'  => $startDateInput,
            'endDateInput'    => $endDateInput,
            'rows'            => $rows,
            'dateHeaders'     => $dateHeaders,
            'columnTotals'    => $columnTotals,
            'grandTotal'      => $grandTotal,
            'validated'       => $validated,
        ]);

    } // End Method

    public function hmoCrosstabPdf(Request $request)
    {
        $selectedHmo    = trim((string) $request->get('hmo_enrolled'));
        $startDateInput = trim((string) $request->get('start_date'));
        $endDateInput   = trim((string) $request->get('end_date'));

        $request->validate([
            'hmo_enrolled' => ['required', 'string', 'max:100'],
            'start_date'   => ['required', 'date_format:m/d/Y'],
            'end_date'     => ['required', 'date_format:m/d/Y'],
        ]);

        $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
        $endDate   = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();

        if ($endDate->lt($startDate)) {
            return back()->withErrors(['end_date' => 'End date cannot be less than start date.']);
        }

        if ($startDate->gt(now()) || $endDate->gt(now())) {
            return back()->withErrors(['end_date' => 'Future dates are not allowed.']);
        }

        if ($startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) > 30) {
            return back()->withErrors(['end_date' => 'Date range cannot exceed 31 days.']);
        }

        [$rows, $dateHeaders, $columnTotals, $grandTotal] = $this->buildHmoAttendanceCrosstab(
            $selectedHmo,
            $startDate,
            $endDate
        );

        $html = view('attendance.hmo_crosstab_report_pdf', [
            'selectedHmo'    => $selectedHmo,
            'startDateInput' => $startDateInput,
            'endDateInput'   => $endDateInput,
            'rows'           => $rows,
            'dateHeaders'    => $dateHeaders,
            'columnTotals'   => $columnTotals,
            'grandTotal'     => $grandTotal,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'hmo_attendance_crosstab.pdf';

        return response($mpdf->Output($fileName, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');

    } // End Method

   public function hmoCrosstabExcel(Request $request)
    {
        $selectedHmo    = trim((string) $request->get('hmo_enrolled'));
        $startDateInput = trim((string) $request->get('start_date'));
        $endDateInput   = trim((string) $request->get('end_date'));

        $request->validate([
            'hmo_enrolled' => ['required', 'string', 'max:100'],
            'start_date'   => ['required', 'date_format:m/d/Y'],
            'end_date'     => ['required', 'date_format:m/d/Y'],
        ]);

        return Excel::download(
            new HmoAttendanceCrosstabExport($selectedHmo, $startDateInput, $endDateInput),
            'hmo_attendance_crosstab.xlsx'
        );

    } // End Method

    private function buildHmoAttendanceCrosstab(string $selectedHmo, Carbon $startDate, Carbon $endDate): array
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1: get member ids who had this HMO during selected range
        |--------------------------------------------------------------------------
        */
        $historyMemberIds = DB::table('member_coverage_history')
            ->where('HMO_enrolled', $selectedHmo)
            ->where('effective_from', '<=', $endDate->toDateString())
            ->where(function ($q) use ($startDate) {
                $q->whereNull('effective_to')
                ->orWhere('effective_to', '>=', $startDate->toDateString());
            })
            ->distinct()
            ->pluck('ParticipantId')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Step 2: fallback current members table (for members with no history rows)
        |--------------------------------------------------------------------------
        */
        $currentMemberIds = DB::table('members')
            ->where('HMO_enrolled', $selectedHmo)
            ->pluck('ParticipantId')
            ->toArray();

        $allMemberIds = array_values(array_unique(array_merge($historyMemberIds, $currentMemberIds)));

        if (empty($allMemberIds)) {
            return [[], [], [], 0];
        }

        /*
        |--------------------------------------------------------------------------
        | Step 3: member master rows
        |--------------------------------------------------------------------------
        */
        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'HMO_enrolled')
            ->whereIn('ParticipantId', $allMemberIds)
            ->where('Trans_flag', 'A')
            ->orderBy('ParticipantId')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Step 4: attendance rows in selected range
        |--------------------------------------------------------------------------
        */
        $attendanceRows = DB::table('partattendence')
            ->select('ParticipantId', 'CalenderDate', 'AttendanceYN')
            ->whereBetween('CalenderDate', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->whereIn('ParticipantId', $members->pluck('ParticipantId')->toArray())
            ->orderBy('ParticipantId')
            ->orderBy('CalenderDate')
            ->get();

        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $pid = (string) $row->ParticipantId;
            $dateKey = Carbon::parse($row->CalenderDate)->format('Y-m-d');
            $attendanceMap[$pid][$dateKey] = strtoupper((string) $row->AttendanceYN) === 'Y' ? 1 : '';
        }

        /*
        |--------------------------------------------------------------------------
        | Step 5: coverage history rows for those members
        |--------------------------------------------------------------------------
        */
        $coverageHistoryRows = DB::table('member_coverage_history')
            ->select(
                'ParticipantId',
                'effective_from',
                'effective_to',
                'HMO_enrolled'
            )
            ->whereIn('ParticipantId', $members->pluck('ParticipantId')->toArray())
            ->where('effective_from', '<=', $endDate->toDateString())
            ->where(function ($q) use ($startDate) {
                $q->whereNull('effective_to')
                ->orWhere('effective_to', '>=', $startDate->toDateString());
            })
            ->orderBy('ParticipantId')
            ->orderBy('effective_from')
            ->get()
            ->groupBy('ParticipantId');

        /*
        |--------------------------------------------------------------------------
        | Step 6: build date headers
        |--------------------------------------------------------------------------
        */
        $dateHeaders = [];
        $columnTotals = [];
        $grandTotal = 0;

        $cursor = $startDate->copy()->startOfDay();
        $lastDate = $endDate->copy()->startOfDay();
        $dayIndex = 0;

        while ($cursor->lte($lastDate)) {
            $dayIndex++;
            $dateHeaders[$dayIndex] = [
                'date_key'  => $cursor->format('Y-m-d'),
                'day_no'    => $cursor->format('d'),
                'day_label' => $cursor->format('m/d'),
            ];
            $columnTotals[$dayIndex] = 0;
            $cursor->addDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Step 7: build rows
        |--------------------------------------------------------------------------
        */
        $rows = [];

        foreach ($members as $member) {
            $pid = (string) $member->ParticipantId;
            $rowTotal = 0;
            $days = [];
            $memberHasSelectedHmoInRange = false;

            foreach ($dateHeaders as $idx => $hdr) {
                $currentDate = Carbon::createFromFormat('Y-m-d', $hdr['date_key'])->startOfDay();
                $attendanceValue = $attendanceMap[$pid][$hdr['date_key']] ?? '';

                $matchedHistory = null;
                foreach (($coverageHistoryRows[$pid] ?? collect()) as $hist) {
                    $from = Carbon::parse($hist->effective_from)->startOfDay();
                    $to = !empty($hist->effective_to)
                        ? Carbon::parse($hist->effective_to)->endOfDay()
                        : null;

                    if (
                        $currentDate->gte($from) &&
                        (is_null($to) || $currentDate->lte($to))
                    ) {
                        $matchedHistory = $hist;
                        break;
                    }
                }

                // If history exists for date, use it. Otherwise fallback to current members.HMO_enrolled
                $activeHmo = $matchedHistory->HMO_enrolled ?? $member->HMO_enrolled ?? '';

                $value = ($attendanceValue === 1 && $activeHmo === $selectedHmo) ? 1 : '';
                $days[$idx] = $value;

                if ($activeHmo === $selectedHmo) {
                    $memberHasSelectedHmoInRange = true;
                }

                if ($value === 1) {
                    $rowTotal++;
                    $columnTotals[$idx]++;
                    $grandTotal++;
                }
            }

            // include row only if member had selected HMO at least once in selected range
            if ($memberHasSelectedHmoInRange) {
                $rows[] = [
                    'ParticipantId' => $member->ParticipantId,
                    'LastName'      => $member->LastName,
                    'FirstName'     => $member->FirstName,
                    'days'          => $days,
                    'row_total'     => $rowTotal,
                ];
            }
        }

        return [$rows, $dateHeaders, $columnTotals, $grandTotal];

    } //End Method

    public function attendanceStatisticalReport(Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');
        $shift          = strtoupper($request->input('shift', 'A'));

        if (!in_array($shift, ['A', 'N', 'ALL'], true)) {
            $shift = 'A';
        }

        /*
        * First load screen without report.
        */
        if (empty($startDateInput) || empty($endDateInput)) {
            return view('attendance.attendance_statistical_report', [
                'startDateInput' => $startDateInput,
                'endDateInput'   => $endDateInput,
                'shift'          => $shift,
                'reportLoaded'   => false,
            ]);
        }

        $startDate = Carbon::parse($startDateInput)->startOfDay();
        $endDate   = Carbon::parse($endDateInput)->endOfDay();

        if ($endDate->lt($startDate)) {
            return redirect()->back()->with([
                'message'    => 'End date cannot be before start date.',
                'alert-type' => 'error',
            ]);
        }

        /*
        * Total selected calendar days.
        * Example: 05/10 to 05/16 = 7 days.
        */
        $totalSelectedDays = $startDate->copy()
            ->startOfDay()
            ->diffInDays($endDate->copy()->startOfDay()) + 1;

        /*
        * Main attendance query.
        * We count distinct CalenderDate per ParticipantId.
        *
        * For ALL shift:
        * Count any Y attendance from Morning or Noon.
        *
        * For Morning/Noon:
        * Count only selected Trans_flag.
        */
        $attendanceQuery = DB::table('partattendence')
            ->select(
                DB::raw("TRIM(ParticipantId) as ParticipantId"),
                DB::raw("COUNT(DISTINCT DATE(CalenderDate)) as days_present")
            )
            ->whereBetween('CalenderDate', [
                $startDate->format('Y-m-d 00:00:00'),
                $endDate->format('Y-m-d 23:59:59'),
            ])
            ->where('AttendanceYN', 'Y');

        if ($shift !== 'ALL') {
            $attendanceQuery->where('Trans_flag', $shift);
        } else {
            $attendanceQuery->whereIn('Trans_flag', ['A', 'N']);
        }

        $attendanceSummary = $attendanceQuery
            ->groupBy(DB::raw("TRIM(ParticipantId)"))
            ->orderBy(DB::raw("TRIM(ParticipantId)"))
            ->get();

        /*
        * Total unique members present at least one day.
        */
        $totalMembersPresent = $attendanceSummary->count();

        /*
        * Distribution:
        * Example:
        * 3 days => 3 members
        * 4 days => 15 members
        */
        $distribution = $attendanceSummary
            ->groupBy('days_present')
            ->map(function ($rows, $daysPresent) {
                return [
                    'days_present'  => (int) $daysPresent,
                    'member_count'  => $rows->count(),
                ];
            })
            ->sortKeys()
            ->values();

        /*
        * Load member names separately.
        * No join, to avoid collation issues.
        */
        $participantIds = $attendanceSummary
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
            })
            ->unique()
            ->values()
            ->toArray();

        $members = DB::table('members')
            ->select(
                DB::raw("TRIM(ParticipantId) as ParticipantId"),
                'LastName',
                'FirstName',
                'MiddleName',
                'Sex',
                'Trans_flag',
                'HMO_enrolled',
                'medicaid_HMO_id',
                'medicaid_PA_no',
                'medicaid_PA_End'
            )
            ->whereIn('ParticipantId', $participantIds)
            ->get()
            ->keyBy(function ($row) {
                return str_pad(trim((string) $row->ParticipantId), 3, '0', STR_PAD_LEFT);
            });

        /*
        * Detail list:
        * Member-wise days present.
        */
         $details = $attendanceSummary->map(function ($row) use ($members, $startDate) {
            $pid = str_pad(trim((string) $row->ParticipantId), 3, '0', STR_PAD_LEFT);

            $member = $members[$pid] ?? null;

            $hmoEnrolled   = trim((string) ($member->HMO_enrolled ?? ''));
            $medicaidHmoId = trim((string) ($member->medicaid_HMO_id ?? ''));
            $medicaidPaNo  = trim((string) ($member->medicaid_PA_no ?? ''));
            //$paEndRaw      = trim((string) ($member->medicaid_PA_End ?? ''));
            $paEndRaw = trim((string) ($member->medicaid_PA_End ?? ''));
            $paEndDisplay = '';

            if ($paEndRaw !== '') {
                try {
                    $paEndDisplay = Carbon::parse($paEndRaw)->format('m/d/Y');
                } catch (\Exception $e) {
                    $paEndDisplay = $paEndRaw;
                }
            }

            $nonBillableReasons = [];

            if ($hmoEnrolled === '') {
                $nonBillableReasons[] = 'Missing HMO';
            }

            if (strtoupper($hmoEnrolled) === 'PRIVATE PAY') {
                $nonBillableReasons[] = 'Private Pay';
            }

            if ($medicaidHmoId === '') {
                $nonBillableReasons[] = 'Missing HMO ID';
            }

            if ($medicaidPaNo === '') {
                $nonBillableReasons[] = 'Missing PA No';
            }

            if ($paEndRaw === '') {
                $nonBillableReasons[] = 'Missing PA End Date';
            } else {
                try {
                    $paEndDate = Carbon::parse($paEndRaw)->endOfDay();

                    if ($paEndDate->lt($startDate->copy()->startOfDay())) {
                        $nonBillableReasons[] = 'PA Expired';
                    }
                } catch (\Exception $e) {
                    $nonBillableReasons[] = 'Invalid PA End Date';
                }
            }

            $billingStatus = count($nonBillableReasons) > 0 ? 'Non-Billable' : 'Billable';

            return [
                'ParticipantId'        => $pid,
                'LastName'             => $member->LastName ?? '',
                'FirstName'            => $member->FirstName ?? '',
                'MiddleName'           => $member->MiddleName ?? '',
                'Sex' => strtoupper(trim((string) ($member->Sex ?? ''))),
                'MemberShift'          => $member->Trans_flag ?? '',
                'days_present'         => (int) $row->days_present,

                // Billing fields
                'HMO_enrolled'         => $hmoEnrolled,
                'medicaid_HMO_id'      => $medicaidHmoId,
                'medicaid_PA_no'       => $medicaidPaNo,
                'medicaid_PA_End'      => $paEndDisplay,
                'billing_status'       => $billingStatus,
                'non_billable_reasons' => implode(', ', $nonBillableReasons),
            ];
        })->sortBy([
            ['billing_status', 'desc'],
            ['days_present', 'desc'],
            ['LastName', 'asc'],
            ['FirstName', 'asc'],
        ])->values();

        /*
        * Attendance statistics:
        * Average attendance = total present days / selected calendar days.
        * Male/Female count = unique members present at least one day.
        */
        $totalAttendanceDays = $details->sum('days_present');

        $averageAttendance = $totalSelectedDays > 0
            ? round($totalAttendanceDays / $totalSelectedDays, 2)
            : 0;

        $maleMemberCount = $details
            ->where('Sex', 'M')
            ->count();

        $femaleMemberCount = $details
            ->where('Sex', 'F')
            ->count();

        $unknownGenderMemberCount = $details
            ->filter(function ($row) {
                return !in_array($row['Sex'] ?? '', ['M', 'F'], true);
            })
            ->count();

        $maleAttendanceDays = $details
            ->where('Sex', 'M')
            ->sum('days_present');

        $femaleAttendanceDays = $details
            ->where('Sex', 'F')
            ->sum('days_present');

        $billableDetails = $details->where('billing_status', 'Billable')->values();

        $nonBillableDetails = $details->where('billing_status', 'Non-Billable')->values();

        $billableMemberCount = $billableDetails->count();

        $nonBillableMemberCount = $nonBillableDetails->count();

        $billableAttendanceDays = $billableDetails->sum('days_present');

        $nonBillableAttendanceDays = $nonBillableDetails->sum('days_present');

        $shiftLabel = match ($shift) {
            'A'     => 'Morning Shift',
            'N'     => 'Noon Shift',
            'ALL'   => 'All Shifts',
            default => '',
        };

        return view('attendance.attendance_statistical_report', [
            'startDateInput'      => $startDate->format('Y-m-d'),
            'endDateInput'        => $endDate->format('Y-m-d'),
            'startDateDisplay'    => $startDate->format('m/d/Y'),
            'endDateDisplay'      => $endDate->format('m/d/Y'),
            'shift'               => $shift,
            'shiftLabel'          => $shiftLabel,
            'reportLoaded'        => true,
            'totalSelectedDays'   => $totalSelectedDays,
            'totalMembersPresent' => $totalMembersPresent,
            'distribution'        => $distribution,
            'details'                   => $details,

            // Attendance statistics
            'totalAttendanceDays'       => $totalAttendanceDays,
            'averageAttendance'         => $averageAttendance,
            'maleMemberCount'           => $maleMemberCount,
            'femaleMemberCount'         => $femaleMemberCount,
            'unknownGenderMemberCount'  => $unknownGenderMemberCount,
            'maleAttendanceDays'        => $maleAttendanceDays,
            'femaleAttendanceDays'      => $femaleAttendanceDays,

            // Billing summary
            'billableMemberCount'       => $billableMemberCount,
            'nonBillableMemberCount'    => $nonBillableMemberCount,
            'billableAttendanceDays'    => $billableAttendanceDays,
            'nonBillableAttendanceDays' => $nonBillableAttendanceDays,
            'billableDetails'           => $billableDetails,
            'nonBillableDetails'        => $nonBillableDetails,
        ]);

    } // End Method

    public function attendanceStatisticalReportPdf(Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');
        $shift          = strtoupper($request->input('shift', 'A'));

        if (!in_array($shift, ['A', 'N', 'ALL'], true)) {
            $shift = 'A';
        }

        if (empty($startDateInput) || empty($endDateInput)) {
            return redirect()->route('attendance.statistical.report')->with([
                'message'    => 'Please select start date and end date before exporting PDF.',
                'alert-type' => 'error',
            ]);
        }

        $startDate = Carbon::parse($startDateInput)->startOfDay();
        $endDate   = Carbon::parse($endDateInput)->endOfDay();

        if ($endDate->lt($startDate)) {
            return redirect()->route('attendance.statistical.report')->with([
                'message'    => 'End date cannot be before start date.',
                'alert-type' => 'error',
            ]);
        }

        /*
        * Total selected calendar days.
        * Example: 05/10 to 05/16 = 7 days.
        */
        $totalSelectedDays = $startDate->copy()
            ->startOfDay()
            ->diffInDays($endDate->copy()->startOfDay()) + 1;

        /*
        * Main attendance query.
        */
        $attendanceQuery = DB::table('partattendence')
            ->select(
                DB::raw("TRIM(ParticipantId) as ParticipantId"),
                DB::raw("COUNT(DISTINCT DATE(CalenderDate)) as days_present")
            )
            ->whereBetween('CalenderDate', [
                $startDate->format('Y-m-d 00:00:00'),
                $endDate->format('Y-m-d 23:59:59'),
            ])
            ->where('AttendanceYN', 'Y');

        if ($shift !== 'ALL') {
            $attendanceQuery->where('Trans_flag', $shift);
        } else {
            $attendanceQuery->whereIn('Trans_flag', ['A', 'N']);
        }

        $attendanceSummary = $attendanceQuery
            ->groupBy(DB::raw("TRIM(ParticipantId)"))
            ->orderBy(DB::raw("TRIM(ParticipantId)"))
            ->get();

        $totalMembersPresent = $attendanceSummary->count();

        /*
        * Frequency distribution.
        */
        $distribution = $attendanceSummary
            ->groupBy('days_present')
            ->map(function ($rows, $daysPresent) {
                return [
                    'days_present' => (int) $daysPresent,
                    'member_count' => $rows->count(),
                ];
            })
            ->sortKeys()
            ->values();

        /*
        * Load members separately, no joins.
        */
        $participantIds = $attendanceSummary
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return str_pad(trim((string) $id), 3, '0', STR_PAD_LEFT);
            })
            ->unique()
            ->values()
            ->toArray();

        $members = collect();

        if (!empty($participantIds)) {
            $members = DB::table('members')
                ->select(
                    DB::raw("TRIM(ParticipantId) as ParticipantId"),
                    'LastName',
                    'FirstName',
                    'MiddleName',
                    'Sex',
                    'Trans_flag',
                    'HMO_enrolled',
                    'medicaid_HMO_id',
                    'medicaid_PA_no',
                    'medicaid_PA_End'
                )
                ->whereIn('ParticipantId', $participantIds)
                ->get()
                ->keyBy(function ($row) {
                    return str_pad(trim((string) $row->ParticipantId), 3, '0', STR_PAD_LEFT);
                });
        }

        /*
        * Member detail.
        */
        $details = $attendanceSummary->map(function ($row) use ($members, $startDate) {
                $pid = str_pad(trim((string) $row->ParticipantId), 3, '0', STR_PAD_LEFT);

                $member = $members[$pid] ?? null;

                $hmoEnrolled   = trim((string) ($member->HMO_enrolled ?? ''));
                $medicaidHmoId = trim((string) ($member->medicaid_HMO_id ?? ''));
                $medicaidPaNo  = trim((string) ($member->medicaid_PA_no ?? ''));
                $paEndRaw      = trim((string) ($member->medicaid_PA_End ?? ''));

                $paEndDisplay = '';

                if ($paEndRaw !== '') {
                    try {
                        $paEndDisplay = Carbon::parse($paEndRaw)->format('m/d/Y');
                    } catch (\Exception $e) {
                        $paEndDisplay = $paEndRaw;
                    }
                }

                $nonBillableReasons = [];

                if ($hmoEnrolled === '') {
                    $nonBillableReasons[] = 'Missing HMO';
                }

                if (strtoupper($hmoEnrolled) === 'PRIVATE PAY') {
                    $nonBillableReasons[] = 'Private Pay';
                }

                if ($medicaidHmoId === '') {
                    $nonBillableReasons[] = 'Missing HMO ID';
                }

                if ($medicaidPaNo === '') {
                    $nonBillableReasons[] = 'Missing PA No';
                }

                if ($paEndRaw === '') {
                    $nonBillableReasons[] = 'Missing PA End Date';
                } else {
                    try {
                        $paEndDate = Carbon::parse($paEndRaw)->endOfDay();

                        if ($paEndDate->lt($startDate->copy()->startOfDay())) {
                            $nonBillableReasons[] = 'PA Expired';
                        }
                    } catch (\Exception $e) {
                        $nonBillableReasons[] = 'Invalid PA End Date';
                    }
                }

                $billingStatus = count($nonBillableReasons) > 0 ? 'Non-Billable' : 'Billable';

                return [
                    'ParticipantId'        => $pid,
                    'LastName'             => $member->LastName ?? '',
                    'FirstName'            => $member->FirstName ?? '',
                    'MiddleName'           => $member->MiddleName ?? '',
                    'Sex'                  => strtoupper(trim((string) ($member->Sex ?? ''))),
                    'MemberShift'          => $member->Trans_flag ?? '',
                    'days_present'         => (int) $row->days_present,

                    'HMO_enrolled'         => $hmoEnrolled,
                    'medicaid_HMO_id'      => $medicaidHmoId,
                    'medicaid_PA_no'       => $medicaidPaNo,
                    'medicaid_PA_End'      => $paEndDisplay,
                    'billing_status'       => $billingStatus,
                    'non_billable_reasons' => implode(', ', $nonBillableReasons),
                ];
            })->sortBy([
                ['billing_status', 'desc'],
                ['days_present', 'desc'],
                ['LastName', 'asc'],
                ['FirstName', 'asc'],
            ])->values();



            $billableDetails = $details->where('billing_status', 'Billable')->values();

            $nonBillableDetails = $details->where('billing_status', 'Non-Billable')->values();

            $billableMemberCount = $billableDetails->count();

            $nonBillableMemberCount = $nonBillableDetails->count();

            $billableAttendanceDays = $billableDetails->sum('days_present');

            $nonBillableAttendanceDays = $nonBillableDetails->sum('days_present');

        $shiftLabel = match ($shift) {
            'A'     => 'Morning Shift',
            'N'     => 'Noon Shift',
            'ALL'   => 'All Shifts',
            default => '',
        };

        $data = [
            'startDateInput'      => $startDate->format('Y-m-d'),
            'endDateInput'        => $endDate->format('Y-m-d'),
            'startDateDisplay'    => $startDate->format('m/d/Y'),
            'endDateDisplay'      => $endDate->format('m/d/Y'),
            'shift'               => $shift,
            'shiftLabel'          => $shiftLabel,
            'totalSelectedDays'   => $totalSelectedDays,
            'totalMembersPresent' => $totalMembersPresent,
            'distribution'        => $distribution,
            'details'             => $details,

            'billableMemberCount'       => $billableMemberCount,
            'nonBillableMemberCount'    => $nonBillableMemberCount,
            'billableAttendanceDays'    => $billableAttendanceDays,
            'nonBillableAttendanceDays' => $nonBillableAttendanceDays,
            'billableDetails'           => $billableDetails,
            'nonBillableDetails'        => $nonBillableDetails,
        ];

        $html = view('attendance.attendance_statistical_report_pdf', $data)->render();

        $tempDir = storage_path('app/mpdf-temp');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'orientation'   => 'L',
                'margin_left'   => 6,
                'margin_right'  => 6,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir,
            ]);

        $mpdf->SetTitle('Attendance Statistical Report');

        $mpdf->WriteHTML($html);

        $fileName = 'attendance_statistical_report_' .
            $startDate->format('Ymd') . '_' .
            $endDate->format('Ymd') . '_' .
            strtolower($shift) . '.pdf';

        return response($mpdf->Output($fileName, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');

    } // End Method


    public static function getAttendanceReportData_newPA($dateRangeStart, $dateRangeEnd, $type)
    {
        $startDate = $dateRangeStart;
        $endDate = $dateRangeEnd;

        $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

        /*
        * Step 1:
        * Get attendance for selected date range and selected ACTUAL shift.
        *
        * Important:
        * partattendence.Trans_flag = actual attended shift
        * members.Trans_flag       = default/home shift
        */
        $attendances = DB::table('partattendence')
            ->select(
                DB::raw('TRIM(ParticipantId) as ParticipantId'),
                'CalenderDate',
                'AttendanceYN',
                'Trans_flag'
            )
            ->whereBetween('CalenderDate', [$startDate, $endDate])
            ->where('Trans_flag', $type)
            ->get();

        /*
        * Step 2:
        * Get only participant IDs who have actual attendance
        * in this selected shift/date range.
        */
        $participantIds = $attendances
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return trim($id);
            })
            ->unique()
            ->values()
            ->toArray();

        /*
        * Step 3:
        * Load member details for only those participants.
        *
        * This allows Morning member temporarily attending Noon
        * to appear in Noon report.
        */
        $members = collect();

        if (!empty($participantIds)) {
            $members = DB::table('members')
                ->select(
                    DB::raw('TRIM(ParticipantId) as ParticipantId'),
                    DB::raw('TRIM(LastName) as LastName'),
                    DB::raw('TRIM(FirstName) as FirstName'),
                    DB::raw('TRIM(MiddleName) as MiddleName'),
                    DB::raw('TRIM(HMO_enrolled) as HMO_enrolled'),
                    DB::raw('TRIM(medicaid_HMO_id) as medicaid_HMO_id'),
                    DB::raw('TRIM(medicaid_PA_no) as medicaid_PA_no'),
                    DB::raw('TRIM(medicaid_PA_Start) as medicaid_PA_Start'),
                    DB::raw('TRIM(medicaid_PA_End) as medicaid_PA_End'),
                    DB::raw('TRIM(renew1_HMO_id) as renew1_HMO_id'),
                    DB::raw('TRIM(renew1_PA_no) as renew1_PA_no'),
                    DB::raw('TRIM(renew1_PA_Start) as renew1_PA_Start'),
                    DB::raw('TRIM(renew1_PA_End) as renew1_PA_End'),
                    DB::raw('TRIM(PrimaryDiagnosis1) as PrimaryDiagnosis1'),
                    DB::raw('TRIM(SecondaryDiag1) as SecondaryDiag1'),
                    DB::raw('TRIM(DateOfBirth) as DateOfBirth'),
                    DB::raw('TRIM(SSNo) as SSNo'),
                    DB::raw('TRIM(Sex) as Sex'),
                    DB::raw('TRIM(Trans_flag) as DefaultTransFlag')
                )
                ->whereIn('ParticipantId', $participantIds)
                ->orderBy('ParticipantId')
                ->get();
        }

        /*
        * Step 4:
        * Index attendance by ParticipantId + Date.
        */
        $attendanceMap = [];

        foreach ($attendances as $a) {
            $pid = trim($a->ParticipantId);
            $date = \Carbon\Carbon::parse($a->CalenderDate)->toDateString();

            $attendanceMap[$pid][$date] = ($a->AttendanceYN === 'Y') ? 1 : 0;
        }

        /*
        * Step 5:
        * Build date range array.
        */
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->toDateString();
            $currentDate->addDay();
        }

        sort($dateRange);

        /*
        * Step 6:
        * Build attendance data.
        */
        $attendanceData = [];
        $columnTotals = array_fill_keys($dateRange, 0);

        foreach ($members as $m) {
            $pid = trim($m->ParticipantId);

            $row = [
                'ParticipantId' => $pid,
                'LastName' => $m->LastName,
                'FirstName' => $m->FirstName,
                'MiddleName' => $m->MiddleName,
                'HmoEnrolled' => $m->HMO_enrolled,
                'MedicaidHMOId' => $m->medicaid_HMO_id,
                'MedicaidPANo' => $m->medicaid_PA_no,
                'MedicaidPAStart' => $m->medicaid_PA_Start,
                'MedicaidPAEnd' => $m->medicaid_PA_End,
                'Renew1HMOId' => $m->renew1_HMO_id,
                'Renew1PANo' => $m->renew1_PA_no,
                'Renew1PAStart' => $m->renew1_PA_Start,
                'Renew1PAEnd' => $m->renew1_PA_End,
                'PrimaryICD' => $m->PrimaryDiagnosis1,
                'SecondaryICD' => $m->SecondaryDiag1,
                'DOB' => $m->DateOfBirth,
                'SS#' => $m->SSNo,
                'Sex' => $m->Sex,

                /*
                * Optional:
                * This is member's default shift, not actual report shift.
                * Useful later if you want to highlight temporary-shift members.
                */
                'DefaultTransFlag' => $m->DefaultTransFlag,

                'attendance' => [],
                'row_total' => 0,
            ];

            foreach ($dateRange as $date) {
                $val = isset($attendanceMap[$pid][$date]) ? $attendanceMap[$pid][$date] : 0;

                $row['attendance'][$date] = $val;
                $row['row_total'] += $val;
                $columnTotals[$date] += $val;
            }

            $attendanceData[$pid] = $row;
        }

        return [
            'attendanceData' => $attendanceData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRange' => $dateRange,
            'columnTotals' => $columnTotals,
            'dates' => $dateRange,
        ];

    } // End Method

    public function exportExcelBillingPA(Request $request, string $type = 'A')
    {
        $type = in_array(strtoupper($type), ['A','N'], true) ? strtoupper($type) : 'A';

        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

        $data = $this->getAttendanceReportData_newPA($startDate, $endDate, $type);

        $grandTotal = is_array($data['columnTotals'])
            ? array_sum($data['columnTotals'])
            : 0;

        return Excel::download(
            new AttendanceReportExportBillingPA(
                $data['attendanceData'],
                $data['dateRange'],
                $data['columnTotals'],
                $grandTotal,
                config('company.comp_nm'),
                $startDate,
                $endDate
            ),
            'attendance_report_billing_pa.xlsx'
        );

    } // End Method

}



