<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class MemberWiseAttendanceExport implements FromView
{
    protected string $participantId;
    protected string $startDateInput;
    protected string $endDateInput;

    public function __construct(string $participantId, string $startDateInput, string $endDateInput)
    {
        $this->participantId   = $participantId;
        $this->startDateInput  = $startDateInput;
        $this->endDateInput    = $endDateInput;

    } // End Method

    public function view(): View
    {
        $startDate = Carbon::createFromFormat('m/d/Y', $this->startDateInput)->startOfDay();
        $endDate   = Carbon::createFromFormat('m/d/Y', $this->endDateInput)->endOfDay();

        $member = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'HMO_enrolled',
                'medicaid_HMO_id',
                'medicaid_PA_no'
            )
            ->where('ParticipantId', $this->participantId)
            ->first();

        $coverageHistory = DB::table('member_coverage_history')
            ->select(
                'ParticipantId',
                'effective_from',
                'effective_to',
                'HMO_enrolled',
                'medicaid_HMO_id',
                'medicaid_PA_no'
            )
            ->where('ParticipantId', $this->participantId)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $startDate->toDateString());
            })
            ->where('effective_from', '<=', $endDate->toDateString())
            ->orderBy('effective_from')
            ->get();

        $attendanceRows = DB::table('partattendence')
        ->select('CalenderDate', 'AttendanceYN')
        ->where('ParticipantId', $this->participantId)
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

        return view('attendance.member_wise_report_excel', [
            'member'        => $member,
            'rows'          => $rows,
            'dateRangeText' => $dateRangeText,
            'totalDaysPresent'  => $totalDaysPresent,
            'totalDaysInRange'  => $totalDaysInRange,
        ]);

    } // End Method
}
