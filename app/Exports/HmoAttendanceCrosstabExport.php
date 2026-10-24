<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class HmoAttendanceCrosstabExport implements FromView
{
    protected string $selectedHmo;
    protected string $startDateInput;
    protected string $endDateInput;

    public function __construct(string $selectedHmo, string $startDateInput, string $endDateInput)
    {
        $this->selectedHmo = $selectedHmo;
        $this->startDateInput = $startDateInput;
        $this->endDateInput = $endDateInput;
    }

    public function view(): View
    {
        $startDate = Carbon::createFromFormat('m/d/Y', $this->startDateInput)->startOfDay();
        $endDate   = Carbon::createFromFormat('m/d/Y', $this->endDateInput)->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Step 1: get member ids who had this HMO during selected range
        |--------------------------------------------------------------------------
        */
        $historyMemberIds = DB::table('member_coverage_history')
            ->where('HMO_enrolled', $this->selectedHmo)
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
        | Step 2: fallback current members table
        |--------------------------------------------------------------------------
        */
        $currentMemberIds = DB::table('members')
            ->where('HMO_enrolled', $this->selectedHmo)
            ->pluck('ParticipantId')
            ->toArray();

        $allMemberIds = array_values(array_unique(array_merge($historyMemberIds, $currentMemberIds)));

        if (empty($allMemberIds)) {
            return view('attendance.hmo_crosstab_report_excel', [
                'selectedHmo'    => $this->selectedHmo,
                'startDateInput' => $this->startDateInput,
                'endDateInput'   => $this->endDateInput,
                'rows'           => [],
                'dateHeaders'    => [],
                'columnTotals'   => [],
                'grandTotal'     => 0,
            ]);
        }

        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName', 'HMO_enrolled')
            ->whereIn('ParticipantId', $allMemberIds)
            ->where('Trans_flag', 'A')
            ->orderBy('ParticipantId')
            ->get();

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

                $activeHmo = $matchedHistory->HMO_enrolled ?? $member->HMO_enrolled ?? '';

                $value = ($attendanceValue === 1 && $activeHmo === $this->selectedHmo) ? 1 : '';
                $days[$idx] = $value;

                if ($activeHmo === $this->selectedHmo) {
                    $memberHasSelectedHmoInRange = true;
                }

                if ($value === 1) {
                    $rowTotal++;
                    $columnTotals[$idx]++;
                    $grandTotal++;
                }
            }

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

        return view('attendance.hmo_crosstab_report_excel', [
            'selectedHmo'    => $this->selectedHmo,
            'startDateInput' => $this->startDateInput,
            'endDateInput'   => $this->endDateInput,
            'rows'           => $rows,
            'dateHeaders'    => $dateHeaders,
            'columnTotals'   => $columnTotals,
            'grandTotal'     => $grandTotal,
        ]);
    }
}
