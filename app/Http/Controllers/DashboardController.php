<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\LoginDashboardNoticeService;
use App\Models\User;



class DashboardController extends Controller
{
    public function index_old()
    {
        // ================================
        // Members Statistics by Shift
        // A = Morning Shift
        // N = Noon Shift
        // ================================

        $morningMale = DB::table('members')
            ->where('Sex', 'M')
            ->where('Trans_flag', 'A')
            ->count();

        $morningFemale = DB::table('members')
            ->where('Sex', 'F')
            ->where('Trans_flag', 'A')
            ->count();

        $morningTotal = $morningMale + $morningFemale;


        $noonMale = DB::table('members')
            ->where('Sex', 'M')
            ->where('Trans_flag', 'N')
            ->count();

        $noonFemale = DB::table('members')
            ->where('Sex', 'F')
            ->where('Trans_flag', 'N')
            ->count();

        $noonTotal = $noonMale + $noonFemale;


        // Overall totals for chart / summary if needed
        $overallMale = $morningMale + $noonMale;
        $overallFemale = $morningFemale + $noonFemale;
        $overallTotal = $overallMale + $overallFemale;


        $stats = [
            'morning' => [
                'label'  => 'Morning Shift',
                'male'   => $morningMale,
                'female' => $morningFemale,
                'total'  => $morningTotal,
            ],
            'noon' => [
                'label'  => 'Noon Shift',
                'male'   => $noonMale,
                'female' => $noonFemale,
                'total'  => $noonTotal,
            ],
            'overall' => [
                'label'  => 'All Members',
                'male'   => $overallMale,
                'female' => $overallFemale,
                'total'  => $overallTotal,
            ],

            // Backward compatibility, if any old code still uses these keys
            'male'   => $overallMale,
            'female' => $overallFemale,
            'total'  => $overallTotal,
        ];


        // Build weekly arrays Mon..Sun
        $start  = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $labels = [];

        for ($d = $start->copy(); $d->lt($start->copy()->addDays(7)); $d->addDay()) {
            $labels[] = $d->format('D');
        }

        $weeklyAlerts = app(LoginDashboardNoticeService::class)->build();

        return view('dashboard', [
            'stats'        => $stats,
            'weekly'       => ['labels' => $labels],
            'monthLabel'   => now()->format('F Y'),
            'monthEvents'  => [],
            'weeklyAlerts' => $weeklyAlerts,
        ]);
    } // End Method

    // public function index_old1()
    // {
    //     // =========================================================
    //     // Members Statistics by Shift
    //     // A = Morning Shift
    //     // N = Noon Shift
    //     // =========================================================

    //     $morningMale = DB::table('members')
    //         ->where('Sex', 'M')
    //         ->where('Trans_flag', 'A')
    //         ->count();

    //     $morningFemale = DB::table('members')
    //         ->where('Sex', 'F')
    //         ->where('Trans_flag', 'A')
    //         ->count();

    //     $morningTotal = $morningMale + $morningFemale;


    //     $noonMale = DB::table('members')
    //         ->where('Sex', 'M')
    //         ->where('Trans_flag', 'N')
    //         ->count();

    //     $noonFemale = DB::table('members')
    //         ->where('Sex', 'F')
    //         ->where('Trans_flag', 'N')
    //         ->count();

    //     $noonTotal = $noonMale + $noonFemale;


    //     // Overall totals for chart / summary
    //     $overallMale   = $morningMale + $noonMale;
    //     $overallFemale = $morningFemale + $noonFemale;
    //     $overallTotal  = $overallMale + $overallFemale;


    //     $stats = [
    //         'morning' => [
    //             'label'  => 'Morning Shift',
    //             'male'   => $morningMale,
    //             'female' => $morningFemale,
    //             'total'  => $morningTotal,
    //         ],

    //         'noon' => [
    //             'label'  => 'Noon Shift',
    //             'male'   => $noonMale,
    //             'female' => $noonFemale,
    //             'total'  => $noonTotal,
    //         ],

    //         'overall' => [
    //             'label'  => 'All Members',
    //             'male'   => $overallMale,
    //             'female' => $overallFemale,
    //             'total'  => $overallTotal,
    //         ],

    //         // Backward compatibility
    //         'male'   => $overallMale,
    //         'female' => $overallFemale,
    //         'total'  => $overallTotal,
    //     ];


    //     // =========================================================
    //     // Build Weekly Arrays Monday through Sunday
    //     // =========================================================

    //     $start  = Carbon::now()->startOfWeek(Carbon::MONDAY);
    //     $labels = [];

    //     for (
    //         $d = $start->copy();
    //         $d->lt($start->copy()->addDays(7));
    //         $d->addDay()
    //     ) {
    //         $labels[] = $d->format('D');
    //     }


    //     // =========================================================
    //     // Existing Weekly Alerts
    //     // =========================================================

    //     $weeklyAlerts = app(LoginDashboardNoticeService::class)->build();


    //     // =========================================================
    //     // PA Expiration Reminder Rules
    //     //
    //     // IMPORTANT:
    //     // Replace these insurance names with the exact values stored
    //     // in members.HMO_Enrolled.
    //     //
    //     // Matching is not case-sensitive and supports partial names.
    //     // Example:
    //     // "UNITED HEALTHCARE" will match a rule containing "UHC"
    //     // only if UHC actually appears in the stored value.
    //     // =========================================================

    //     $insuranceReminderRules = [

    //         'AETNA BETTER HEALTH' => 30,
    //         'AMERI GROUP NJ' => 30,
    //         'HEALTH FIRST' => 30,
    //         'HORIZON NJ HEALTH' => 30,
    //         'UNITED HEALTH CARE' => 7,
    //         'WELLCARE' => 15,
    //         'WELLPOINT' => 30,

    //     ];

    //     // Insurance companies not listed above will use this value.
    //     $defaultReminderDays = 30;

    //     $today = Carbon::today();


    //     // =========================================================
    //     // Retrieve Active Morning and Noon Members with PA End Date
    //     // =========================================================

    //     $paMembers = DB::table('members')
    //         ->select(
    //             'ParticipantId',
    //             'FirstName',
    //             'LastName',
    //             'Trans_flag',
    //             'HMO_Enrolled',
    //             'medicaid_HMO_id',
    //             'medicaid_PA_no',
    //             'medicaid_PA_Start',
    //             'medicaid_PA_End'
    //         )
    //         ->whereIn('Trans_flag', ['A', 'N'])
    //         ->whereNotNull('HMO_Enrolled')
    //         ->whereRaw("TRIM(COALESCE(HMO_Enrolled, '')) <> ''")
    //         ->whereNotNull('medicaid_PA_End')
    //         ->orderBy('medicaid_PA_End')
    //         ->orderBy('LastName')
    //         ->orderBy('FirstName')
    //         ->get();


    //     // =========================================================
    //     // Apply Insurance-Specific Reminder Period
    //     // =========================================================

    //     $paExpirationReminders = $paMembers
    //         ->map(function ($member) use (
    //             $today,
    //             $insuranceReminderRules,
    //             $defaultReminderDays
    //         ) {
    //             $insurance = strtoupper(trim((string) $member->HMO_Enrolled));

    //             $reminderDays = $defaultReminderDays;

    //             foreach ($insuranceReminderRules as $insuranceName => $days) {
    //                 if (
    //                     $insurance !== '' &&
    //                     str_contains($insurance, strtoupper($insuranceName))
    //                 ) {
    //                     $reminderDays = (int) $days;
    //                     break;
    //                 }
    //             }

    //             try {
    //                 $expirationDate = Carbon::parse(
    //                     $member->medicaid_PA_End
    //                 )->startOfDay();
    //             } catch (\Throwable $e) {
    //                 return null;
    //             }

    //             /*
    //             * Signed number of days:
    //             *
    //             * Positive = days remaining
    //             * Zero     = expires today
    //             * Negative = already expired
    //             */
    //             $daysRemaining = $today->diffInDays(
    //                 $expirationDate,
    //                 false
    //             );

    //             /*
    //             * Include:
    //             *
    //             * 1. Already expired PA records
    //             * 2. PA records expiring within the insurance-specific
    //             *    reminder period
    //             */
    //             if ($daysRemaining > $reminderDays) {
    //                 return null;
    //             }

    //             if ($daysRemaining < 0) {
    //                 $status = 'expired';
    //             } elseif ($daysRemaining <= 7) {
    //                 $status = 'urgent';
    //             } else {
    //                 $status = 'upcoming';
    //             }

    //             $daysIntoReminderWindow = $reminderDays - $daysRemaining;

    //             return [
    //                 'ParticipantId' => $member->ParticipantId,

    //                 'FirstName' => $member->FirstName,
    //                 'LastName'  => $member->LastName,

    //                 'name' => trim(
    //                     $member->FirstName . ' ' . $member->LastName
    //                 ),

    //                 'Trans_flag' => $member->Trans_flag,

    //                 'insurance' => $member->HMO_Enrolled,

    //                 'hmo_id' => $member->medicaid_HMO_id,

    //                 'pa_no' => $member->medicaid_PA_no,

    //                 'pa_start_date' => $member->medicaid_PA_Start,

    //                 'expire_date' => $expirationDate->format('Y-m-d'),

    //                 'days_remaining' => $daysRemaining,

    //                 'reminder_days' => $reminderDays,

    //                 'days_into_reminder_window' => max(0, $daysIntoReminderWindow),

    //                 'status' => $status,

    //             ];
    //         })
    //         ->filter()
    //         ->sortBy(function ($row) {
    //             /*
    //             * Sort order:
    //             * 1. Most overdue first
    //             * 2. Expiring today
    //             * 3. Upcoming expiration dates
    //             */
    //             return [
    //                 $row['days_remaining'],
    //                 $row['ParticipantId'],
    //             ];
    //         })
    //         ->values();


    //     $paExpirationReminderCount = $paExpirationReminders->count();


    //     // =========================================================
    //     // Return Dashboard View
    //     // =========================================================

    //     return view('dashboard', [
    //         'stats'        => $stats,

    //         'weekly' => [
    //             'labels' => $labels,
    //         ],

    //         'monthLabel'  => now()->format('F Y'),
    //         'monthEvents' => [],

    //         'weeklyAlerts' => $weeklyAlerts,

    //         // New PA expiration reminder variables
    //         'paExpirationReminders'     => $paExpirationReminders,
    //         'paExpirationReminderCount' => $paExpirationReminderCount,
    //     ]);

    // } // End Method

    public function index()
    {
        // =========================================================
        // Members Statistics by Shift
        // A = Morning Shift
        // N = Noon Shift
        // =========================================================

        $morningMale = DB::table('members')
            ->where('Sex', 'M')
            ->where('Trans_flag', 'A')
            ->count();

        $morningFemale = DB::table('members')
            ->where('Sex', 'F')
            ->where('Trans_flag', 'A')
            ->count();

        $morningTotal = $morningMale + $morningFemale;


        $noonMale = DB::table('members')
            ->where('Sex', 'M')
            ->where('Trans_flag', 'N')
            ->count();

        $noonFemale = DB::table('members')
            ->where('Sex', 'F')
            ->where('Trans_flag', 'N')
            ->count();

        $noonTotal = $noonMale + $noonFemale;


        // =========================================================
        // Overall totals
        // =========================================================

        $overallMale   = $morningMale + $noonMale;
        $overallFemale = $morningFemale + $noonFemale;
        $overallTotal  = $overallMale + $overallFemale;


        $stats = [
            'morning' => [
                'label'  => 'Morning Shift',
                'male'   => $morningMale,
                'female' => $morningFemale,
                'total'  => $morningTotal,
            ],

            'noon' => [
                'label'  => 'Noon Shift',
                'male'   => $noonMale,
                'female' => $noonFemale,
                'total'  => $noonTotal,
            ],

            'overall' => [
                'label'  => 'All Members',
                'male'   => $overallMale,
                'female' => $overallFemale,
                'total'  => $overallTotal,
            ],

            // Backward compatibility
            'male'   => $overallMale,
            'female' => $overallFemale,
            'total'  => $overallTotal,
        ];


        // =========================================================
        // Weekly labels Monday through Sunday
        // =========================================================

        $start  = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $labels = [];

        for (
            $d = $start->copy();
            $d->lt($start->copy()->addDays(7));
            $d->addDay()
        ) {
            $labels[] = $d->format('D');
        }


        // =========================================================
        // Existing Weekly Alerts
        // =========================================================

        $weeklyAlerts = app(
            LoginDashboardNoticeService::class
        )->build();


        // =========================================================
        // PA Renewal Reminder Rules
        // =========================================================

        $insuranceReminderRules = [
            'AETNA BETTER HEALTH' => 30,
            'AMERI GROUP NJ'      => 30,
            'HEALTH FIRST'        => 30,
            'HORIZON NJ HEALTH'   => 30,
            'UNITED HEALTH CARE'  => 7,
            'WELLCARE'            => 15,
            'WELLPOINT'           => 30,
        ];

        $defaultReminderDays = 30;
        $today = Carbon::today();


        // =========================================================
        // Retrieve members whose PA renewal has not been applied
        // =========================================================

        $paMembers = DB::table('members')
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName',
                'Trans_flag',
                'HMO_Enrolled',
                'medicaid_HMO_id',
                'medicaid_PA_no',
                'medicaid_PA_Start',
                'medicaid_PA_End',
                'renew1_PA_no'
            )
            ->whereIn('Trans_flag', ['A', 'N'])
            ->whereNotNull('HMO_Enrolled')
            ->whereRaw(
                "TRIM(COALESCE(HMO_Enrolled, '')) <> ''"
            )
            ->whereNotNull('medicaid_PA_End')

            /*
         * Renewal has not yet been applied.
         */
            ->where(function ($query) {
                $query->whereNull('renew1_PA_no')
                    ->orWhereRaw(
                        "TRIM(COALESCE(renew1_PA_no, '')) = ''"
                    );
            })

            ->orderBy('medicaid_PA_End')
            ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
            ->get();


        // =========================================================
        // Apply provider-specific reminder period
        // =========================================================

        $paExpirationReminders = $paMembers
            ->map(function ($member) use (
                $today,
                $insuranceReminderRules,
                $defaultReminderDays
            ) {
                $insurance = strtoupper(
                    trim((string) $member->HMO_Enrolled)
                );

                $reminderDays = $defaultReminderDays;

                foreach (
                    $insuranceReminderRules
                    as $insuranceName => $days
                ) {
                    if (
                        $insurance !== '' &&
                        str_contains(
                            $insurance,
                            strtoupper($insuranceName)
                        )
                    ) {
                        $reminderDays = (int) $days;
                        break;
                    }
                }

                try {
                    $expirationDate = Carbon::parse(
                        $member->medicaid_PA_End
                    )->startOfDay();
                } catch (\Throwable $e) {
                    return null;
                }


                /*
             * Date when the renewal application should begin.
             *
             * Example:
             * PA End          = 06/30/2026
             * Reminder Window = 30 days
             * Renewal Start   = 05/31/2026
             */
                $renewalStartDate = $expirationDate
                    ->copy()
                    ->subDays($reminderDays);


                /*
             * Do not display the member before the provider's
             * reminder window begins.
             */
                if ($today->lt($renewalStartDate)) {
                    return null;
                }


                /*
             * Positive = days until expiration
             * Zero     = expires today
             * Negative = already expired
             */
                $daysRemaining = $today->diffInDays(
                    $expirationDate,
                    false
                );


                /*
             * Number of days since renewal should have been started.
             */
                $renewalPendingDays = $renewalStartDate
                    ->diffInDays($today);


                if ($daysRemaining < 0) {
                    $status = 'expired';
                } elseif ($daysRemaining <= 7) {
                    $status = 'urgent';
                } else {
                    $status = 'upcoming';
                }


                return [
                    'ParticipantId' => $member->ParticipantId,

                    'FirstName' => $member->FirstName,
                    'LastName'  => $member->LastName,

                    'name' => trim(
                        $member->FirstName . ' ' .
                            $member->LastName
                    ),

                    'Trans_flag' => $member->Trans_flag,

                    'insurance' => $member->HMO_Enrolled,

                    'hmo_id' => $member->medicaid_HMO_id,

                    'pa_no' => $member->medicaid_PA_no,

                    'pa_start_date' =>
                    $member->medicaid_PA_Start,

                    'expire_date' =>
                    $expirationDate->format('Y-m-d'),

                    'renewal_start_date' =>
                    $renewalStartDate->format('Y-m-d'),

                    'days_remaining' =>
                    (int) $daysRemaining,

                    'reminder_days' =>
                    (int) $reminderDays,

                    /*
                 * The dashboard blade currently uses this key
                 * as "Pending for X days".
                 */
                    'days_into_reminder_window' =>
                    (int) $renewalPendingDays,

                    'renewal_pending_days' =>
                    (int) $renewalPendingDays,

                    'status' => $status,
                ];
            })
            ->filter()
            ->sortBy(function ($row) {
                /*
             * Most overdue and earliest expiration first.
             */
                return [
                    $row['days_remaining'],
                    (int) $row['ParticipantId'],
                ];
            })
            ->values();


        $paExpirationReminderCount =
            $paExpirationReminders->count();


        // =========================================================
        // Provider colors and short codes
        // =========================================================

        $hmoColors = DB::table('hmodropdown')
            ->where('use_flag', 'Y')
            ->get()
            ->keyBy(function ($item) {
                return trim((string) $item->Id);
            });


        // =========================================================
        // Return Dashboard
        // =========================================================

        return view('dashboard', [
            'stats' => $stats,

            'weekly' => [
                'labels' => $labels,
            ],

            'monthLabel'  => now()->format('F Y'),
            'monthEvents' => [],

            'weeklyAlerts' => $weeklyAlerts,

            'paExpirationReminders' =>
            $paExpirationReminders,

            'paExpirationReminderCount' =>
            $paExpirationReminderCount,

            'hmoColors' => $hmoColors,
        ]);
    } // End Method


    /* ---------- JSON feeds ---------- */

    public function weeklyAttendance()
    {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $end   = (clone $start)->endOfWeek(Carbon::SUNDAY);

        $labels = $present = $absent = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $labels[] = $d->format('D');
            $date     = $d->toDateString();

            $present[] = DB::table('partattendence')
                ->whereDate('CalenderDate', $date)
                ->where('AttendanceYN', 'Y')
                ->count();

            $absent[]  = DB::table('partattendence')
                ->whereDate('CalenderDate', $date)
                ->where('AttendanceYN', 'N')
                ->count();
        }

        return response()->json([
            'labels'  => $labels,
            'present' => $present,
            'absent'  => $absent,
        ]);
    } // End Method

    public function calendarEvents(Request $request)
    {
        $startStr = $request->query('start') ?: now()->startOfMonth()->toDateString();
        $endStr   = $request->query('end')   ?: now()->endOfMonth()->toDateString();

        $start = \Carbon\Carbon::parse($startStr)->startOfDay();
        $end   = \Carbon\Carbon::parse($endStr)->endOfDay();

        // Build the set of ALL months covered in the visible range
        $months = collect();
        for ($cursor = $start->copy()->startOfMonth(); $cursor->lte($end); $cursor->addMonth()) {
            $months->push($cursor->month);
        }
        $months = $months->unique()->values();

        // (Option A) Keep month filter but use ALL months in range
        $members = DB::table('members')
            ->select('FirstName', 'LastName', 'DateOfBirth')
            ->where('Trans_flag', 'A')
            ->whereNotNull('DateOfBirth')
            ->whereIn(DB::raw('MONTH(DateOfBirth)'), $months->all())
            ->get();

        // (Option B) If your members table is small, you can skip the month filter entirely:
        // $members = \DB::table('members')
        //     ->select('FirstName', 'LastName', 'DOB')
        //     ->where('Trans_flag', 'A')
        //     ->whereNotNull('DOB')
        //     ->get();

        $years  = collect([$start->year, $end->year])->unique()->values();
        $events = collect();

        foreach ($members as $m) {
            $dob = \Carbon\Carbon::parse($m->DateOfBirth);

            foreach ($years as $yr) {
                $month = (int)$dob->month;
                $day   = (int)$dob->day;

                // Handle Feb 29 in non-leap years
                if ($month === 2 && $day === 29 && !\Carbon\Carbon::isLeapYear($yr)) {
                    $day = 28;
                }

                $thisYear = \Carbon\Carbon::create($yr, $month, $day);
                if ($thisYear->betweenIncluded($start, $end)) {
                    $events->push([
                        'title'   => trim("{$m->FirstName} {$m->LastName}"),
                        'start'   => $thisYear->toDateString(),
                        'allDay'  => true,
                        'display' => 'list-item', // lets FC render a dot
                        'color'   => '#ffd36e',
                        'extendedProps' => ['type' => 'birthday'],
                    ]);
                }
            }
        }

        return response()->json($events->values());
    } // End Method

    public function eventsByDay(Request $request)
    {
        $date = $request->query('date'); // yyyy-mm-dd from FullCalendar

        //Log::info('eventsByDay HIT', ['date' => $request->query('date')]);
        if (!$date) {
            return response()->json([]);
        }

        // Extract month and day only, since DOB includes year
        $month = date('m', strtotime($date));
        $day   = date('d', strtotime($date));

        // Get members whose birthday matches the selected date
        $birthdays = DB::table('members')
            ->select('FirstName', 'LastName', 'DateOfBirth')
            ->whereRaw('MONTH(DateOfBirth) = ? AND DAY(DateOfBirth) = ?', [$month, $day])
            ->where('Trans_flag', 'A')
            ->get()
            ->map(function ($m) {
                return [
                    'title' => trim("{$m->FirstName} {$m->LastName}"),
                    'type'  => 'birthday',
                    'note'  => 'Birthday 🎂',
                    'time'  => '',
                ];
            });

        // Return birthday for now
        //return response()->json([$birthdays]);
        return response()->json($birthdays);
    } // End Method

    public function apptEvents(\Illuminate\Http\Request $request)
    {

        $start = $request->query('start') ?: now()->startOfMonth()->toDateString();
        $end   = $request->query('end')   ?: now()->endOfMonth()->toDateString();

        $appts = DB::table('appointment as a')
            ->join('members as m', 'm.ParticipantId', '=', 'a.ParticipantId')
            ->leftJoin('physicianmaster as p', 'p.doctor_id', '=', 'a.appwith')
            ->where('m.Trans_flag', 'A')
            ->whereBetween(DB::raw('DATE(a.appdate)'), [$start, $end])
            ->select([
                'a.ParticipantId',
                'a.appdate',
                'a.reason',
                'a.appwith as doctor_id',
                'm.FirstName',
                'm.LastName',
                'p.firstname as doc_first',
                'p.lastname as doc_last',
            ])
            ->orderBy('a.appdate')
            ->get();

        // stable per-doctor color palette
        $palette = ['#60a5fa', '#34d399', '#f59e0b', '#f43f5e', '#a78bfa', '#22c55e', '#f97316'];
        $doctorColor = fn($id) => $palette[abs(crc32((string)$id)) % count($palette)];

        $events = $appts->map(function ($r) use ($doctorColor) {
            $startDt = \Carbon\Carbon::parse($r->appdate);
            $endDt   = $startDt->copy()->addMinutes(30);

            $patient = trim(($r->FirstName ?? '') . ' ' . ($r->LastName ?? ''));
            $docName = trim('Dr. ' . trim(($r->doc_first ?? '') . ' ' . ($r->doc_last ?? '')));

            // ✅ unique per patient+start+doctor (or: $id = (string) Str::uuid();)
            $id = sha1(
                (string)$r->ParticipantId . '|' .
                    $startDt->format('YmdHis') . '|' .
                    (string)($r->doctor_id ?? '0')
            );

            return [
                'id'    => $id,
                'title' => $patient ?: 'Appointment',
                'start' => $startDt->toIso8601String(),
                'end'   => $endDt->toIso8601String(),
                'allDay' => false,
                'color' => $doctorColor($r->doctor_id),
                'extendedProps' => [
                    'type'          => 'appointment',
                    'patient'       => $patient,
                    'doctor'        => $docName,
                    'time'          => $startDt->format('g:i A'),
                    'reason'        => $r->reason,
                    'participantid' => $r->ParticipantId,
                    'appdate_iso'   => $startDt->format('Y-m-d H:i:s'),
                    'doctor_id'     => $r->doctor_id,
                ],
            ];
        })->values();
        return response()->json($events);
    } // End Method

    public function apptByDay(\Illuminate\Http\Request $request)
    {
        $date = $request->query('date');
        if (!$date) return response()->json([]);

        $rows = DB::table('appointment as a')
            ->join('members as m', 'm.ParticipantId', '=', 'a.ParticipantId')
            ->leftJoin('physicianmaster as p', 'p.doctor_id', '=', 'a.appwith')
            ->where('m.Trans_flag', 'A')
            ->whereDate('a.appdate', $date)
            ->orderBy('a.appdate')
            ->select([
                'a.appdate',
                'a.reason',
                'm.FirstName',
                'm.LastName',
                'p.firstname as doc_first',
                'p.lastname as doc_last',
            ])
            ->get();

        return response()->json(
            $rows->map(function ($r) {
                $time = \Carbon\Carbon::parse($r->appdate)->format('g:i A');
                $patient = trim(($r->FirstName ?? '') . ' ' . ($r->LastName ?? ''));
                $doc = trim('Dr. ' . trim(($r->doc_first ?? '') . ' ' . ($r->doc_last ?? '')));
                return [
                    'title' => ($patient ?: 'Appointment') . ' with ' . $doc,
                    'time'  => $time,
                    'note'  => $r->reason ? ('Reason: ' . $r->reason) : '',
                ];
            })->values()
        );
    } // End Method
}
