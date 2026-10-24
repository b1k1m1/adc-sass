<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class MemberRangeAttendanceExport implements FromView
{
    protected string $participantId;
    protected string $startYearMonth;
    protected string $endYearMonth;

    public function __construct(string $participantId, string $startYearMonth, string $endYearMonth)
    {
        $this->participantId = $participantId;
        $this->startYearMonth = $startYearMonth;
        $this->endYearMonth = $endYearMonth;

    } // End Method

    public function view(): View
    {
        $member = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('ParticipantId', $this->participantId)
            ->first();

        $startDate = Carbon::createFromFormat('Y-m', $this->startYearMonth)->startOfMonth()->startOfDay();
        $endDate   = Carbon::createFromFormat('Y-m', $this->endYearMonth)->endOfMonth()->endOfDay();

        $attendanceRows = DB::table('partattendence')
            ->select('CalenderDate', 'AttendanceYN')
            ->where('ParticipantId', $this->participantId)
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

        return view('attendance.member_range_crosstab_report_excel', [
            'member'          => $member,
            'months'          => $months,
            'dayTotals'       => $dayTotals,
            'grandTotal'      => $grandTotal,
            'startYearMonth'  => $this->startYearMonth,
            'endYearMonth'    => $this->endYearMonth,
        ]);

    } // End Method
}
