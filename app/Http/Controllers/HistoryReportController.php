<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryReportController extends Controller
{
    public function index()
    {
        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D'])
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        return view('members.members_history_index', compact('members'));

    } // End Method

    public function attendanceHistory(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        | H    = Hold
        | D    = Discharged
        | BOTH = Hold + Discharged
        */
        $status = strtoupper($request->status ?? 'BOTH');

        if (!in_array($status, ['H', 'D', 'BOTH'], true)) {
            $status = 'BOTH';
        }

        $participantId = $request->participant_id ?? '';

        /*
        |--------------------------------------------------------------------------
        | Date Handling
        |--------------------------------------------------------------------------
        | When user opens the page from menu, start_date and end_date are blank.
        | So we use default current month date range.
        */
        try {
            $startDateCarbon = $request->filled('start_date')
                ? Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth()->startOfDay();

            $endDateCarbon = $request->filled('end_date')
                ? Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();

        } catch (\Exception $e) {
            return back()->with('error', 'Invalid date format. Please enter date as mm/dd/yyyy.');
        }

        $startDate = $startDateCarbon->format('Y-m-d');
        $endDate   = $endDateCarbon->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | Get Historical Members For Dropdown - No Joins
        |--------------------------------------------------------------------------
        | This list is only for the member dropdown.
        | Do NOT filter this by participant_id.
        */
        $dropdownQuery = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D']);

        if ($status === 'H') {
            $dropdownQuery->where('Trans_flag', 'H');
        } elseif ($status === 'D') {
            $dropdownQuery->where('Trans_flag', 'D');
        }

        //$dropdownMembers = $dropdownQuery
        //    ->orderBy('LastName')
        //    ->orderBy('FirstName')
        //    ->get();

        $dropdownMembers = $dropdownQuery
        ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Get Historical Members For Report - No Joins
        |--------------------------------------------------------------------------
        | This list is for report data and can be filtered by selected participant.
        */
        $membersQuery = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D']);

        if ($status === 'H') {
            $membersQuery->where('Trans_flag', 'H');
        } elseif ($status === 'D') {
            $membersQuery->where('Trans_flag', 'D');
        }

        if (!empty($participantId)) {
            $membersQuery->where('ParticipantId', $participantId);
        }

        $members = $membersQuery
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $memberIds    = $members->pluck('ParticipantId')->toArray();
        $membersById  = $members->keyBy('ParticipantId');

        /*
        |--------------------------------------------------------------------------
        | Get Attendance Records - No Joins
        |--------------------------------------------------------------------------
        */
        $records = collect();

        if (!empty($memberIds)) {
            $records = DB::table('partattendence')
                ->select(
                    'ParticipantId',
                    'CalenderDate',
                    'AttendanceYN',
                    'Trans_flag'
                )
                ->whereIn('ParticipantId', $memberIds)
                ->whereBetween('CalenderDate', [$startDate, $endDate])
                ->where('AttendanceYN', 'Y')
                ->orderBy('CalenderDate')
                ->get()
                ->map(function ($row) use ($membersById) {
                    $member = $membersById[$row->ParticipantId] ?? null;

                    $row->LastName = $member->LastName ?? '';
                    $row->FirstName = $member->FirstName ?? '';
                    $row->member_status = $member->Trans_flag ?? '';
                    $row->attendance_shift = $row->Trans_flag ?? '';

                    return $row;
                })
                ->sortBy([
                    ['LastName', 'asc'],
                    ['FirstName', 'asc'],
                    ['CalenderDate', 'asc'],
                ])
                ->values();
        }

        return view('members.members_history_attendance', compact(
            'records',
            'members',
            'dropdownMembers',
            'startDate',
            'endDate',
            'status',
            'participantId'
        ));

    } // End Method

    public function vitalsHistory(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        | H    = Hold
        | D    = Discharged
        | BOTH = Hold + Discharged
        */
        $status = strtoupper($request->status ?? 'BOTH');

        if (!in_array($status, ['H', 'D', 'BOTH'], true)) {
            $status = 'BOTH';
        }

        $participantId = $request->participant_id ?? '';

        /*
        |--------------------------------------------------------------------------
        | Date Handling
        |--------------------------------------------------------------------------
        | When user opens the page from menu, dates are blank.
        | So use current month as default.
        */
        try {
            $startDateCarbon = $request->filled('start_date')
                ? Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth()->startOfDay();

            $endDateCarbon = $request->filled('end_date')
                ? Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();

        } catch (\Exception $e) {
            return back()->with('error', 'Invalid date format. Please enter date as mm/dd/yyyy.');
        }

        $startDate = $startDateCarbon->format('Y-m-d');
        $endDate   = $endDateCarbon->format('Y-m-d');
        /*
        |--------------------------------------------------------------------------
        | Get Historical Members For Dropdown - No Joins
        |--------------------------------------------------------------------------
        | This list is only for the member dropdown.
        | Do NOT filter this by participant_id.
        */
        $dropdownQuery = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D']);

        if ($status === 'H') {
            $dropdownQuery->where('Trans_flag', 'H');
        } elseif ($status === 'D') {
            $dropdownQuery->where('Trans_flag', 'D');
        }

        //$dropdownMembers = $dropdownQuery
        //    ->orderBy('LastName')
        //    ->orderBy('FirstName')
        //    ->get();

        $dropdownMembers = $dropdownQuery
        ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Get Historical Members For Report - No Joins
        |--------------------------------------------------------------------------
        | This list is for report data and can be filtered by selected participant.
        */
        $membersQuery = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D']);

        if ($status === 'H') {
            $membersQuery->where('Trans_flag', 'H');
        } elseif ($status === 'D') {
            $membersQuery->where('Trans_flag', 'D');
        }

        if (!empty($participantId)) {
            $membersQuery->where('ParticipantId', $participantId);
        }

        $members = $membersQuery
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $memberIds    = $members->pluck('ParticipantId')->toArray();
        $membersById  = $members->keyBy('ParticipantId');


        /*
        |--------------------------------------------------------------------------
        | Get Vitals Records - No Joins
        |--------------------------------------------------------------------------
        */

       $records = collect();

        if (!empty($memberIds)) {
            $records = DB::table('partbp')
                ->select(
                    'ParticipantId',
                    'CalenderDate',

                    'LowBP',
                    'HighBP',
                    'Sugar',
                    'Pulse',
                    'Weight',
                    'Temperature',

                    'BathingYN',
                    'ToiletYN',
                    'TransferYN',
                    'LocomotionYN',
                    'EatingYN'
                )
                ->whereIn('ParticipantId', $memberIds)
                ->whereBetween('CalenderDate', [$startDate, $endDate])
                ->orderBy('CalenderDate')
                ->get()
                ->map(function ($row) use ($membersById) {
                    $member = $membersById[$row->ParticipantId] ?? null;

                    $row->LastName = $member->LastName ?? '';
                    $row->FirstName = $member->FirstName ?? '';
                    $row->member_status = $member->Trans_flag ?? '';

                    return $row;
                })
                ->sortBy([
                    ['LastName', 'asc'],
                    ['FirstName', 'asc'],
                    ['CalenderDate', 'asc'],
                ])
                ->values();
        }

        return view('members.members_history_vitals', compact(
            'records',
            'members',
            'dropdownMembers',
            'startDate',
            'endDate',
            'status',
            'participantId'
        ));

    } // End Method

   public function adlHistory(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        | H    = Hold
        | D    = Discharged
        | BOTH = Hold + Discharged
        */
        $status = strtoupper($request->status ?? 'BOTH');

        if (!in_array($status, ['H', 'D', 'BOTH'], true)) {
            $status = 'BOTH';
        }

        $participantId = $request->participant_id ?? '';

        /*
        |--------------------------------------------------------------------------
        | Date Handling
        |--------------------------------------------------------------------------
        | When user opens the page from menu, dates are blank.
        | So use current month as default.
        */
        try {
            $startDateCarbon = $request->filled('start_date')
                ? Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth()->startOfDay();

            $endDateCarbon = $request->filled('end_date')
                ? Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();

        } catch (\Exception $e) {
            return back()->with('error', 'Invalid date format. Please enter date as mm/dd/yyyy.');
        }

        $startDate = $startDateCarbon->format('Y-m-d');
        $endDate   = $endDateCarbon->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | Get Historical Members For Dropdown - No Joins
        |--------------------------------------------------------------------------
        | This list is only for the member dropdown.
        | Do NOT filter this by participant_id.
        */
        $dropdownQuery = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D']);

        if ($status === 'H') {
            $dropdownQuery->where('Trans_flag', 'H');
        } elseif ($status === 'D') {
            $dropdownQuery->where('Trans_flag', 'D');
        }

        //$dropdownMembers = $dropdownQuery
        //    ->orderBy('LastName')
        //    ->orderBy('FirstName')
        //    ->get();

        $dropdownMembers = $dropdownQuery
        ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Get Historical Members For Report - No Joins
        |--------------------------------------------------------------------------
        | This list is for report data and can be filtered by selected participant.
        */
        $membersQuery = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D']);

        if ($status === 'H') {
            $membersQuery->where('Trans_flag', 'H');
        } elseif ($status === 'D') {
            $membersQuery->where('Trans_flag', 'D');
        }

        if (!empty($participantId)) {
            $membersQuery->where('ParticipantId', $participantId);
        }

        $members = $membersQuery
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $memberIds    = $members->pluck('ParticipantId')->toArray();
        $membersById  = $members->keyBy('ParticipantId');

        /*
        |--------------------------------------------------------------------------
        | Get ADL Records - No Joins
        |--------------------------------------------------------------------------
        */
        $records = collect();

        if (!empty($memberIds)) {
            $records = DB::table('partadl')
                ->select(
                    'ParticipantId',
                    'CalenderDate',
                    'toiletYN',
                    'transferYN',
                    'locomotionYN',
                    'eatingYN',
                    'bathingYN'
                )
                ->whereIn('ParticipantId', $memberIds)
                ->whereBetween('CalenderDate', [$startDate, $endDate])
                ->orderBy('CalenderDate')
                ->get()
                ->map(function ($row) use ($membersById) {
                    $member = $membersById[$row->ParticipantId] ?? null;

                    $row->LastName = $member->LastName ?? '';
                    $row->FirstName = $member->FirstName ?? '';
                    $row->member_status = $member->Trans_flag ?? '';

                    return $row;
                })
                ->sortBy([
                    ['LastName', 'asc'],
                    ['FirstName', 'asc'],
                    ['CalenderDate', 'asc'],
                ])
                ->values();
        }

        return view('members.members_history_adl', compact(
            'records',
            'members',
            'dropdownMembers',
            'startDate',
            'endDate',
            'status',
            'participantId'
        ));

    } // End Method

    private function getHistoricalMembers(Request $request)
    {
        $status = $request->status ?? 'BOTH';

        $query = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'Trans_flag')
            ->whereIn('Trans_flag', ['H', 'D']);

        if ($status === 'H') {
            $query->where('Trans_flag', 'H');
        } elseif ($status === 'D') {
            $query->where('Trans_flag', 'D');
        }

        if ($request->filled('participant_id')) {
            $query->where('ParticipantId', $request->participant_id);
        }

        return $query
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

    } // End Method

    private function getHistoricalDateRange(Request $request)
    {
        try {
            $startDateCarbon = $request->filled('start_date')
                ? Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth()->startOfDay();

            $endDateCarbon = $request->filled('end_date')
                ? Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();

        } catch (\Exception $e) {
            $startDateCarbon = Carbon::now()->startOfMonth()->startOfDay();
            $endDateCarbon   = Carbon::now()->endOfDay();
        }

        return [
            'startDate'        => $startDateCarbon->format('Y-m-d'),
            'endDate'          => $endDateCarbon->format('Y-m-d'),
            'displayStartDate' => $startDateCarbon->format('m/d/Y'),
            'displayEndDate'   => $endDateCarbon->format('m/d/Y'),
        ];

    } // End Method

    public function attendanceHistoryPdf(Request $request)
    {
        $status = strtoupper($request->status ?? 'BOTH');

        if (!in_array($status, ['H', 'D', 'BOTH'], true)) {
            $status = 'BOTH';
        }

        $participantId = $request->participant_id ?? '';

        $dates = $this->getHistoricalDateRange($request);

        $members = $this->getHistoricalMembers($request);

        $memberIds   = $members->pluck('ParticipantId')->toArray();
        $membersById = $members->keyBy('ParticipantId');

        $records = collect();

        if (!empty($memberIds)) {
            $records = DB::table('partattendence')
                ->select(
                    'ParticipantId',
                    'CalenderDate',
                    'AttendanceYN',
                    'Trans_flag'
                )
                ->whereIn('ParticipantId', $memberIds)
                ->whereBetween('CalenderDate', [$dates['startDate'], $dates['endDate']])
                ->where('AttendanceYN', 'Y')
                ->orderBy('CalenderDate')
                ->get()
                ->map(function ($row) use ($membersById) {
                    $member = $membersById[$row->ParticipantId] ?? null;

                    $row->LastName = $member->LastName ?? '';
                    $row->FirstName = $member->FirstName ?? '';
                    $row->member_status = $member->Trans_flag ?? '';
                    $row->attendance_shift = $row->Trans_flag ?? '';

                    return $row;
                })
                ->sortBy([
                    ['LastName', 'asc'],
                    ['FirstName', 'asc'],
                    ['CalenderDate', 'asc'],
                ])
                ->values();
        }

        $company = config('company');

        $html = view('members.members_history_attendance_pdf', [
            'records'          => $records,
            'members'          => $members,
            'status'           => $status,
            'participantId'    => $participantId,
            'startDate'        => $dates['startDate'],
            'endDate'          => $dates['endDate'],
            'displayStartDate' => $dates['displayStartDate'],
            'displayEndDate'   => $dates['displayEndDate'],
            'company'          => $company,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A4-L',
            'margin_left' => 8,
            'margin_right'=> 8,
            'margin_top'  => 8,
            'margin_bottom'=> 8,
            'tempDir'     => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('attendance-history.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');

    } // End Method

    public function vitalsHistoryPdf(Request $request)
    {
        $status = strtoupper($request->status ?? 'BOTH');

        if (!in_array($status, ['H', 'D', 'BOTH'], true)) {
            $status = 'BOTH';
        }

        $participantId = $request->participant_id ?? '';

        $dates = $this->getHistoricalDateRange($request);

        $members = $this->getHistoricalMembers($request);

        $memberIds   = $members->pluck('ParticipantId')->toArray();
        $membersById = $members->keyBy('ParticipantId');

        $records = collect();

        if (!empty($memberIds)) {
            $records = DB::table('partbp')
                ->select(
                    'ParticipantId',
                    'CalenderDate',

                    'LowBP',
                    'HighBP',
                    'Sugar',
                    'Pulse',
                    'Weight',
                    'Temperature',

                    'BathingYN',
                    'ToiletYN',
                    'TransferYN',
                    'LocomotionYN',
                    'EatingYN'
                )
                ->whereIn('ParticipantId', $memberIds)
                ->whereBetween('CalenderDate', [$dates['startDate'], $dates['endDate']])
                ->orderBy('CalenderDate')
                ->get()
                ->map(function ($row) use ($membersById) {
                    $member = $membersById[$row->ParticipantId] ?? null;

                    $row->LastName = $member->LastName ?? '';
                    $row->FirstName = $member->FirstName ?? '';
                    $row->member_status = $member->Trans_flag ?? '';

                    return $row;
                })
                ->sortBy([
                    ['LastName', 'asc'],
                    ['FirstName', 'asc'],
                    ['CalenderDate', 'asc'],
                ])
                ->values();
        }

        $company = config('company');

        $html = view('members.members_history_vitals_pdf', [
            'records'          => $records,
            'members'          => $members,
            'status'           => $status,
            'participantId'    => $participantId,
            'startDate'        => $dates['startDate'],
            'endDate'          => $dates['endDate'],
            'displayStartDate' => $dates['displayStartDate'],
            'displayEndDate'   => $dates['displayEndDate'],
            'company'          => $company,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A4-L',
            'margin_left' => 6,
            'margin_right'=> 6,
            'margin_top'  => 8,
            'margin_bottom'=> 8,
            'tempDir'     => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('vitals-history.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');

    } // End Method


    public function adlHistoryPdf(Request $request)
    {
        $status = strtoupper($request->status ?? 'BOTH');

        if (!in_array($status, ['H', 'D', 'BOTH'], true)) {
            $status = 'BOTH';
        }

        $participantId = $request->participant_id ?? '';

        $dates = $this->getHistoricalDateRange($request);

        $members = $this->getHistoricalMembers($request);

        $memberIds   = $members->pluck('ParticipantId')->toArray();
        $membersById = $members->keyBy('ParticipantId');

        $records = collect();

        if (!empty($memberIds)) {
            $records = DB::table('partadl')
                ->select(
                    'ParticipantId',
                    'CalenderDate',
                    'toiletYN',
                    'transferYN',
                    'locomotionYN',
                    'eatingYN',
                    'bathingYN'
                )
                ->whereIn('ParticipantId', $memberIds)
                ->whereBetween('CalenderDate', [$dates['startDate'], $dates['endDate']])
                ->orderBy('CalenderDate')
                ->get()
                ->map(function ($row) use ($membersById) {
                    $member = $membersById[$row->ParticipantId] ?? null;

                    $row->LastName = $member->LastName ?? '';
                    $row->FirstName = $member->FirstName ?? '';
                    $row->member_status = $member->Trans_flag ?? '';

                    return $row;
                })
                ->sortBy([
                    ['LastName', 'asc'],
                    ['FirstName', 'asc'],
                    ['CalenderDate', 'asc'],
                ])
                ->values();
        }

        $company = config('company');

        $html = view('members.members_history_adl_pdf', [
            'records'          => $records,
            'members'          => $members,
            'status'           => $status,
            'participantId'    => $participantId,
            'startDate'        => $dates['startDate'],
            'endDate'          => $dates['endDate'],
            'displayStartDate' => $dates['displayStartDate'],
            'displayEndDate'   => $dates['displayEndDate'],
            'company'          => $company,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A4-L',
            'margin_left' => 8,
            'margin_right'=> 8,
            'margin_top'  => 8,
            'margin_bottom'=> 8,
            'tempDir'     => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('adl-history.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');

    } // End Method

}
