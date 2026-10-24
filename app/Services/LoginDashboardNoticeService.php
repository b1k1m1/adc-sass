<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LoginDashboardNoticeService
{
    public function build(): array
    {
        $today = Carbon::today();

        // Week = Sunday to Saturday
        $weekStart = $today->copy()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $weekEnd   = $today->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Doctor's Appointments This Week
        |--------------------------------------------------------------------------
        */
        $appointmentRows = DB::connection('mysql')
            ->table('appointment')
            ->whereBetween('appdate', [
                $weekStart->toDateTimeString(),
                $weekEnd->toDateTimeString()
            ])
            ->orderBy('appdate')
            ->get();

        $participantIds = $appointmentRows
            ->pluck('participantid')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $doctorIds = $appointmentRows
            ->pluck('appwith')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $membersMap = collect();
        if (!empty($participantIds)) {
            $membersMap = DB::connection('mysql')
                ->table('members')
                ->whereIn('ParticipantId', $participantIds)
                ->where('Trans_flag', 'A')
                ->select('ParticipantId', 'LastName', 'FirstName')
                ->get()
                ->keyBy('ParticipantId');
        }

        $physiciansMap = collect();
        if (!empty($doctorIds)) {
            $physiciansMap = DB::connection('mysql')
                ->table('physicianmaster')
                ->whereIn('doctor_id', $doctorIds)
                ->select('doctor_id', 'lastname', 'firstname')
                ->get()
                ->keyBy('doctor_id');
        }

        $appointments = $appointmentRows->map(function ($row) use ($membersMap, $physiciansMap) {
            $member = $membersMap->get($row->participantid);
            $doctor = $physiciansMap->get($row->appwith);

            return (object) [
                'ParticipantId' => $row->participantid,
                'LastName'      => $member->LastName ?? '',
                'FirstName'     => $member->FirstName ?? '',
                'appdate'       => $row->appdate,
                'reason'        => $row->reason ?? '',
                'doctor_name'   => $this->resolveDoctorName($doctor),
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | PA Expiring This Week
        |--------------------------------------------------------------------------
        */
        $paExpiring = DB::connection('mysql')
            ->table('members')
            ->where('Trans_flag', 'A')
            ->whereNotNull('medicaid_PA_End')
            ->whereBetween('medicaid_PA_End', [
                $weekStart->toDateTimeString(),
                $weekEnd->toDateTimeString()
            ])
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'medicaid_PA_End as expire_date'
            )
            ->orderBy('medicaid_PA_End')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | HNP Expiring This Week
        |--------------------------------------------------------------------------
        */
        $hnpExpiring = DB::connection('mysql')
            ->table('members')
            ->where('Trans_flag', 'A')
            ->whereNotNull('HNP_Expiry_date')
            ->whereBetween('HNP_Expiry_date', [
                $weekStart->toDateTimeString(),
                $weekEnd->toDateTimeString()
            ])
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'HNP_Expiry_date as expire_date'
            )
            ->orderBy('HNP_Expiry_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Birthdays This Week
        |--------------------------------------------------------------------------
        | Compare month-day only so birthdays repeat every year
        |--------------------------------------------------------------------------
        */
        $birthdays = $this->getBirthdaysForWeek($weekStart, $weekEnd);

        return [
            'today' => $today->toDateString(),
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),

            'appointments_count' => $appointments->count(),
            'pa_count' => $paExpiring->count(),
            'hnp_count' => $hnpExpiring->count(),
            'birthday_count' => $birthdays->count(),

            'appointments_preview' => $this->buildAppointmentsPreview($appointments),
            'pa_preview' => $this->buildExpiryPreview($paExpiring),
            'hnp_preview' => $this->buildExpiryPreview($hnpExpiring),
            'birthday_preview' => $this->buildBirthdayPreview($birthdays),
        ];
    }

    public function hasAnything(array $data): bool
    {
        return ($data['appointments_count'] ?? 0) > 0
            || ($data['pa_count'] ?? 0) > 0
            || ($data['hnp_count'] ?? 0) > 0
            || ($data['birthday_count'] ?? 0) > 0;
    }

    protected function buildAppointmentsPreview(Collection $rows): array
    {
        return $rows->take(5)->map(function ($row) {
            return [
                'ParticipantId'    => $row->ParticipantId ?? '',
                'name'             => trim(($row->LastName ?? '') . ', ' . ($row->FirstName ?? ''), ', '),
                'appointment_date' => !empty($row->appdate)
                    ? Carbon::parse($row->appdate)->format('Y-m-d')
                    : '',
                'appointment_time' => !empty($row->appdate)
                    ? Carbon::parse($row->appdate)->format('g:i A')
                    : '',
                'reason'           => $row->reason ?? '',
                'physician_name'   => $row->doctor_name ?? '',
            ];
        })->values()->all();
    }

    protected function buildExpiryPreview(Collection $rows): array
    {
        return $rows->take(5)->map(function ($row) {
            return [
                'ParticipantId' => $row->ParticipantId ?? '',
                'name'          => trim(($row->LastName ?? '') . ', ' . ($row->FirstName ?? ''), ', '),
                'expire_date'   => !empty($row->expire_date)
                    ? Carbon::parse($row->expire_date)->format('Y-m-d')
                    : '',
            ];
        })->values()->all();
    }

    protected function buildBirthdayPreview(Collection $rows): array
    {
        return $rows->take(5)->map(function ($row) {
            $dob = !empty($row->birth_date) ? Carbon::parse($row->birth_date) : null;
            $birthdayThisYear = !empty($row->birthday_this_year) ? Carbon::parse($row->birthday_this_year) : null;

            $age = null;
            if ($dob && $birthdayThisYear) {
                $age = $dob->diffInYears($birthdayThisYear);
            }

            return [
                'ParticipantId'      => $row->ParticipantId ?? '',
                'name'               => trim(($row->FirstName ?? '') . ', ' . ($row->LastName ?? ''), ', '),
                'birth_date'         => $dob ? $dob->format('Y-m-d') : '',
                'birthday_this_year' => $birthdayThisYear ? $birthdayThisYear->format('Y-m-d') : '',
                'birthday_age'       => $age,
                'birthday_age_text'  => $age ? $this->ordinal($age) : '',
            ];
        })->values()->all();
    }
    protected function getBirthdaysForWeek(Carbon $weekStart, Carbon $weekEnd): Collection
    {
        $rows = DB::connection('mysql')
            ->table('members')
            ->where('Trans_flag', 'A')
            ->whereNotNull('DateOfBirth')
            ->select('ParticipantId', 'LastName', 'FirstName', 'DateOfBirth')
            ->get();

        $items = collect();

        foreach ($rows as $row) {
            try {
                $dob = Carbon::parse($row->DateOfBirth);

                $birthdayThisYear = Carbon::create(
                    $weekStart->year,
                    $dob->month,
                    $dob->day,
                    0, 0, 0
                );

                if ($birthdayThisYear->betweenIncluded($weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay())) {
                    $items->push((object) [
                        'ParticipantId' => $row->ParticipantId,
                        'LastName' => $row->LastName,
                        'FirstName' => $row->FirstName,
                        'birth_date' => $row->DateOfBirth,
                        'birthday_this_year' => $birthdayThisYear->toDateString(),
                    ]);
                }
            } catch (\Throwable $e) {
                // ignore bad date rows
            }
        }

        return $items->sortBy('birthday_this_year')->values();
    }

    protected function resolveDoctorName($doctor): string
    {
        if (!$doctor) {
            return '';
        }

        $last = trim((string) ($doctor->lastname ?? ''));
        $first = trim((string) ($doctor->firstname ?? ''));

        if ($last !== '' && $first !== '') {
            return $last . ', ' . $first;
        }

        if ($last !== '') {
            return $last;
        }

        if ($first !== '') {
            return $first;
        }

        return '';
    }

    protected function ordinal(?int $number): string
    {
        if (!$number) {
            return '';
        }

        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }

        return $number . $ends[$number % 10];
    }
}
