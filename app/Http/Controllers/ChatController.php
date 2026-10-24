<?php

namespace App\Http\Controllers;


use App\Services\AIService;
use App\Services\ChatIntentService;
use App\Services\TableSchemaService;
use App\Services\TableAccessValidator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request){

        //old code
        //$suggestions = $this->suggestions();
        //return view('chat.chat_index', compact('suggestions'));


        $suggestionGroups = $this->suggestionGroups();

        $user = $request->user();   // ✅ safest
        $name = trim(($user->firstname ?? $user->name ?? ''));
        $hour = (int) now()->format('H');
        $greet =
            $hour < 12 ? 'Good morning' :
            ($hour < 17 ? 'Good afternoon' : 'Good evening');

        $welcome = $name ? "{$greet}, {$name}!" : "{$greet}!";

        // ✅ iframe/embed mode
        if ($request->boolean('embed')) {
            return view('chat.chat_index_embed', compact('suggestionGroups', 'welcome'));
        }

        return view('chat.chat_index', compact('suggestionGroups', 'welcome'));

    } // End Method

    public function ask(Request $request){

        $q = trim((string)$request->input('message', ''));

        if ($q === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Please type a question.',
                'suggestions' => $this->suggestions(),
            ]);
        }

        if ($pending = $this->getVitalsPending()) {

            // auto-expire pending after 5 minutes (safety)
            if (!empty($pending['ts']) && (now()->timestamp - (int)$pending['ts']) > 300) {
                $this->clearVitalsPending();
            } else {
                $my = $this->parseMonthYear($q);

                if ($my) {
                    $this->clearVitalsPending();

                    // ✅ run your existing vitals flow by building an "intent"

                    $intent = [
                        'supported' => true,
                        'intent' => 'vitals_monthly',
                        'participant_id' => (string)$pending['participant_id'],
                        'year' => (int)$my['year'],
                        'month' => (int)$my['month'],
                        'title' => 'Monthly Vitals Report',
                    ];


                    // IMPORTANT: let the rest of your ask() handle it normally
                    // We'll just "pretend" detectIntent returned this.
                } else {
                    return response()->json([
                        'ok' => true,
                        'type' => 'unsupported',
                        'message' => "For which month and year? Example: 2026-02 or Feb 2026",
                        'suggestions' => $this->suggestions(),
                    ]);
                }
            }
        }

        // ✅ Attendance follow-up
        if ($pending = $this->getAttendancePending()) {

            // auto-expire after 5 minutes
            if (!empty($pending['ts']) && (now()->timestamp - (int)$pending['ts']) > 300) {
                $this->clearAttendancePending();
            } else {

                // Try date range first
                if (preg_match('/\b(\d{1,2}\/\d{1,2}\/\d{4})\s*(to|\-)\s*(\d{1,2}\/\d{1,2}\/\d{4})\b/i', $q, $m)) {

                    $this->clearAttendancePending();

                    $intent = [
                        'supported' => true,
                        'intent' => 'attendance_totals_range',
                        'from' => $m[1],
                        'to'   => $m[3],
                        'title' => "Attendance totals ({$m[1]} - {$m[3]})",
                    ];
                }

                // Try month/year
                elseif ($my = $this->parseMonthYear($q)) {

                    $this->clearAttendancePending();

                    $label = \Carbon\Carbon::create($my['year'], $my['month'], 1)->format('F Y');

                    $intent = [
                        'supported' => true,
                        'intent' => 'attendance_totals_month',
                        'year' => (int)$my['year'],
                        'month' => (int)$my['month'],
                        'title' => "Attendance totals ({$label})",
                    ];
                }

                else {
                    return response()->json([
                        'ok' => true,
                        'type' => 'unsupported',
                        'message' => "Please enter month (Jan 2026) or date range (10/01/2025 to 10/31/2025).",
                        'suggestions' => $this->suggestions(),
                    ]);
                }
            }
        }


        //$intent = $this->detectIntent($q);
        $intent = $intent ?? $this->detectIntent($q);
        $intent['supported'] = $intent['supported'] ?? false;

        if (!$intent['supported']) {
            // Try AI fallback for unsupported intents
            $chatIntentService = new ChatIntentService(
                new AIService(),
                new TableSchemaService(),
                new TableAccessValidator()
            );

            if ($chatIntentService->shouldUseAI($q)) {
                $aiResult = $chatIntentService->processWithAI($q);

                // If AI returns structured data (table, counts, etc.)
                if (isset($aiResult['ok']) && $aiResult['ok']) {
                    return response()->json([
                        'ok' => true,
                        'type' => $aiResult['type'] ?? 'ai',
                        'title' => $aiResult['title'] ?? 'AI Query Result',
                        'columns' => $aiResult['columns'] ?? [],
                        'data' => $aiResult['data'] ?? [],
                        'ai_powered' => true,
                        'suggestions' => $this->suggestions(),
                    ]);
                }

                // If AI returns a text message
                return response()->json([
                    'ok' => true,
                    'type' => $aiResult['type'] ?? 'ai',
                    'message' => $aiResult['response'] ?? 'Processing your request...',
                    'ai_powered' => true,
                    'suggestions' => $this->suggestions(),
                ]);
            }

            // No AI match - return original unsupported message
            return response()->json([
                'ok' => true,
                'type' => 'unsupported',
                'message' => $intent['message'],
                'suggestions' => $this->suggestions(),
            ]);
        }

        if ($intent['intent'] === 'count_by_sex') {
            $rows = $this->countBySex($intent['shift']); // 'A' or 'N'
            return response()->json([
                'ok' => true,
                'type' => 'counts',
                'title' => $intent['title'],
                'data' => $rows,
                'suggestions' => $this->suggestions(),
            ]);
        }

        if ($intent['intent'] === 'birthdays_in_month') {
            $rows = $this->birthdaysInMonth($intent['month']); // 1..12
            return response()->json([
                'ok' => true,
                'type' => 'table',
                'title' => $intent['title'],
                'columns' => ['ParticipantId', 'MemberName', 'DateOfBirth', 'Age'],
                'data' => $rows,
                'suggestions' => $this->suggestions(),
                'export' => ['mode' => 'month', 'month' => (int)$intent['month']],
            ]);
        }

        if ($intent['intent'] === 'birthdays_next_days') {
            $rows = $this->birthdaysNextDays((int)$intent['days']);

            return response()->json([
            'ok' => true,
            'type' => 'table',
            'title' => $intent['title'],
            'columns' => ['ParticipantId', 'MemberName', 'DateOfBirth','NextBirthday','Age'],
            'data' => $rows,
            'export' => [
                'mode' => 'next_days',
                'days' => (int)$intent['days'],
            ],
            'suggestions' => $this->suggestions(),
        ]);
    }

        if ($intent['intent'] === 'pa_expires_this_month') {
        $rows = $this->paExpiresThisMonth();
        $actions = collect($rows)->mapWithKeys(function ($r) {
            $pid = str_pad(trim((string)$r->ParticipantId), 3, '0', STR_PAD_LEFT);
            return [$r->ParticipantId => [
                'pa' => [
                    'label' => 'Update Medical Info',
                    'url'   => route('all.members', [], false) . '?open=pa&id=' . urlencode($pid),
                ],
            ]];
        })->toArray();


        return response()->json([
            'ok' => true,
            'type' => 'table',
            'title' => $intent['title'],
            'columns' => ['ParticipantId', 'FirstName', 'LastName', 'medicaid_PA_no', 'medicaid_PA_End'],
            'data' => $rows,
            'actions' => $actions,   // ✅ add this
            'suggestions' => $this->suggestions(),
        ]);
    }

        if ($intent['intent'] === 'pa_expires_next_days') {
            $days = (int)($intent['days'] ?? 30);
            $rows = $this->paExpiresNextDays($days);
            $actions = collect($rows)->mapWithKeys(function ($r) {
                $pid = str_pad(trim((string)$r->ParticipantId), 3, '0', STR_PAD_LEFT);

                    return [$r->ParticipantId => [
                        'pa' => [
                            'label' => 'Update Medical Info',
                            'url'   => route('all.members', [], false) . '?open=pa&id=' . urlencode($pid),
                        ],
                    ]];
                })->toArray();

            return response()->json([
                'ok' => true,
                'type' => 'table',
                'title' => $intent['title'],
                'columns' => ['ParticipantId', 'FirstName', 'LastName','HMO_enrolled', 'medicaid_PA_no', 'medicaid_PA_End','DaysLeft'],
                'data' => $rows,
                'actions' => $actions,   // ✅ add this

                'suggestions' => $this->suggestions(),
            ]);
    }

        if ($intent['intent'] === 'hnp_expires_this_month') {
        $rows = $this->HNPExpiresThisMonth();
        $actions = collect($rows)->mapWithKeys(function ($r) {
            $pid = str_pad(trim((string)$r->ParticipantId), 3, '0', STR_PAD_LEFT);
            return [$r->ParticipantId => [
                'pa' => [
                    'label' => 'Update Medical Info',
                    'url'   => route('all.members', [], false) . '?open=pa&id=' . urlencode($pid),
                ],
            ]];
        })->toArray();


        return response()->json([
            'ok' => true,
            'type' => 'table',
            'title' => $intent['title'],
            'columns' => ['ParticipantId', 'FirstName', 'LastName', 'HNP_Expiry_date' ],
            'data' => $rows,
            'actions' => $actions,   // ✅ add this
            'suggestions' => $this->suggestions(),
        ]);

    }

        if ($intent['intent'] === 'hnp_expires_next_days') {
            $days = (int)($intent['days'] ?? 30);
            $rows = $this->HNPExpiresNextDays($days);
             $actions = collect($rows)->mapWithKeys(function ($r) {
                $pid = str_pad(trim((string)$r->ParticipantId), 3, '0', STR_PAD_LEFT);

                    return [$r->ParticipantId => [
                        'pa' => [
                            'label' => 'Update Medical Info',
                            'url'   => route('all.members', [], false) . '?open=pa&id=' . urlencode($pid),
                        ],
                    ]];
                })->toArray();

            return response()->json([
                'ok' => true,
                'type' => 'table',
                'title' => $intent['title'],
                'columns' => ['ParticipantId', 'FirstName', 'LastName','HNP_Expiry_date', 'DaysLeft'],
                'data' => $rows,
                'actions' => $actions,   // ✅ add this
                'suggestions' => $this->suggestions(),
            ]);
    }

    if ($intent['intent'] === 'vitals_monthly') {

        $pid  = (string)$intent['participant_id'];
        $rows = $this->vitalsMonthly($pid, $intent['year'], $intent['month']);

        $label = \Carbon\Carbon::create($intent['year'], $intent['month'], 1)->format('F Y');
        $name  = $this->memberNameById($pid);
        $nameText = $name ? " - {$name}" : "";

        $monthYear = sprintf('%04d-%02d', (int)$intent['year'], (int)$intent['month']);

        // decide type (A/N) from user text (optional)
        // if you don't want type logic yet, just hardcode 'A'
        $type = $intent['type'] ?? 'A';

        // Build edit screen URL
        $editUrl = route('nursing.edit_bpdata', [
            'ParticipantId' => $pid,
            'monthYear'     => $monthYear,
            'type'          => $type,
        ]);


        return response()->json([
            'ok' => true,
            'type' => 'table',
            'title' => "Vitals for ID {$pid}{$nameText} – {$label}",

            'columns' => [
                'CalenderDate',
                'LowBP','HighBP','Sugar',
                'Pulse','Temperature',
                'Weight'
            ],

            'data' => $rows,

            // ✅ add export info (so buttons can appear)
            'export' => [
                'type' => 'vitals_monthly',
                'participant_id' => $pid,
                'year' => (int)$intent['year'],
                'month' => (int)$intent['month'],
            ],

            'edit' => [
                'label' => 'Open Edit Screen',
                'url'   => $editUrl,
            ],

            'suggestions' => $this->suggestions(),
        ]);
    }

    if ($intent['intent'] === 'vitals_monthly_needs_month') {

        $pid = (string)$intent['participant_id'];
        $this->setVitalsPending($pid);

        return response()->json([
            'ok' => true,
            'type' => 'unsupported',
            'message' => "For which month and year? Example: 2026-02 or Feb 2026",
            'suggestions' => $this->suggestions(),
        ]);
    }


    if ($intent['intent'] === 'last_attendance_by_shift') {
        $rows = $this->lastAttendanceSummary();

        return response()->json([
            'ok' => true,
            'type' => 'table',
            'title' => $intent['title'],
            'columns' => ['Shift', 'LastAttendanceDate', 'PresentCount', 'TotalMembers'],
            'data' => $rows,
            'suggestions' => $this->suggestions(),
        ]);
    }

    if ($intent['intent'] === 'attendance_totals_month') {

        $year  = (int)$intent['year'];
        $month = (int)$intent['month'];

        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
        $end   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $rows = $this->attendanceTotalsByShift($start, $end);

        return response()->json([
            'ok' => true,
            'type' => 'table',
            'title' => $intent['title'],
            'columns' => ['Shift', 'PresentCount'],
            'data' => $rows,
            'suggestions' => $this->suggestions(),
        ]);
    }

    if ($intent['intent'] === 'attendance_totals_range') {

        try {
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $intent['from'])->startOfDay();
            $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $intent['to'])->endOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'ok' => true,
                'type' => 'unsupported',
                'message' => "Date format should be MM/DD/YYYY. Example: 10/01/2025 to 10/31/2025",
                'suggestions' => $this->suggestions(),
            ]);
        }

        if ($end->lt($start)) { [$start, $end] = [$end, $start]; }

        // Optional safety: limit range (so query stays fast)
        if ($start->diffInDays($end) > 366) {
            return response()->json([
                'ok' => true,
                'type' => 'unsupported',
                'message' => "Please keep date range within 1 year.",
                'suggestions' => $this->suggestions(),
            ]);
        }

        $title = "Attendance totals (" . $start->format('m/d/Y') . " - " . $end->format('m/d/Y') . ")";
        $rows  = $this->attendanceTotalsByShift($start, $end);

        return response()->json([
            'ok' => true,
            'type' => 'table',
            'title' => $title,
            'columns' => ['Shift', 'PresentCount'],
            'data' => $rows,
            'suggestions' => $this->suggestions(),
        ]);
    }

    if ($intent['intent'] === 'attendance_needs_date') {

        $this->setAttendancePending();

        return response()->json([
            'ok' => true,
            'type' => 'unsupported',
            'message' => "For which month or date range? Example: January 2026 or 10/01/2025 to 10/31/2025",
            'suggestions' => $this->suggestions(),
        ]);
    }

    return response()->json([
        'ok' => true,
        'type' => 'unsupported',
        'message' => 'I can’t answer that yet.',
        'suggestions' => $this->suggestions(),
    ]);

    } // End Method

    private function suggestions(): array
    {
        return [
            "How many male and female members in Morning shift?",
            "How many male and female members in Noon shift?",
            "Which members have birthdays this month?",
            "Birthdays in March",
            "Upcoming birthdays (next 30 days)",
            "Birthdays this week",
            "PA expires this month",
            "PA expires in 15 days",
            "PA expires within 30 days",
            "Last attendance date for Morning and Noon shifts",
            "Attendance for January 2026",
            "Attendance from 10/01/2025 to 10/31/2025"
        ];

    } // End Method

    private function detectIntent(string $q)
    {
        $qLower = mb_strtolower($q);

        // Shift detection
        $shift = null;
        if (str_contains($qLower, 'morning')) $shift = 'A';
        if (str_contains($qLower, 'noon'))    $shift = 'N';

        // Gender count intent
        $mentionsGender = str_contains($qLower, 'male') || str_contains($qLower, 'female')
            || str_contains($qLower, 'gender') || str_contains($qLower, 'sex');

        $mentionsCount = str_contains($qLower, 'how many') || str_contains($qLower, 'count')
            || str_contains($qLower, 'total');

        if ($mentionsGender && $mentionsCount && $shift) {
            return [
                'supported' => true,
                'intent' => 'count_by_sex',
                'shift' => $shift,
                'title' => $shift === 'A' ? 'Morning shift: Male/Female count' : 'Noon shift: Male/Female count',
            ];
        }

        // Birthdays intent
        if (str_contains($qLower, 'birthday') || str_contains($qLower, 'birth day') || str_contains($qLower, 'birthdays')) {

            // this week (next 7 days)
            if (str_contains($qLower, 'this week') || str_contains($qLower, 'next 7') || str_contains($qLower, 'next seven')) {
                return [
                    'supported' => true,
                    'intent' => 'birthdays_next_days',
                    'days'  => 7,
                    'title' => 'Birthdays in the next 7 days',
                ];
            }

        // upcoming birthdays (default 30 days)
        if (str_contains($qLower, 'upcoming') || str_contains($qLower, 'next 30') || str_contains($qLower, 'next thirty')) {
                return [
                    'supported' => true,
                    'intent' => 'birthdays_next_days',
                    'days'  => 30,
                    'title' => 'Upcoming birthdays (next 30 days)',
                ];
            }

            // month-based birthdays (existing)
            $month = $this->extractMonth($qLower) ?? (int) now()->month;
            $monthName = now()->setMonth($month)->format('F');

            return [
                'supported' => true,
                'intent' => 'birthdays_in_month',
                'month' => $month,
                'title' => "Birthdays in {$monthName}",
            ];
        }

        // PA expiry intents
        if (str_contains($qLower, 'pa') && str_contains($qLower, 'expire')) {

            // "this month"
            if (str_contains($qLower, 'this month') || str_contains($qLower, 'current month')) {
                return [
                    'supported' => true,
                    'intent' => 'pa_expires_this_month',
                    'title' => 'PA expires this month',
                ];
            }


            // PA expires in/within/next X days  (X is user-entered)
            if (preg_match('/\b(in|within|next)\s+(\d{1,3})\s+day(s)?\b/', $qLower, $m)) {
                $days = (int)$m[2];
                $days = max(1, min(365, $days)); // safety clamp

                return [
                    'supported' => true,
                    'intent' => 'pa_expires_next_days',
                    'days' => $days,
                    'title' => "PA expires within {$days} days",
                ];
            }

            // fallback help
            return [
                'supported' => false,
                'message' => "Try: 'PA expires this month' or 'PA expires in 30 days' (you can change 30).",
            ];
        }

        // HNP expiry intents
        if (str_contains($qLower, 'hnp') && str_contains($qLower, 'expire')) {

            // "this month"
            if (str_contains($qLower, 'this month') || str_contains($qLower, 'current month')) {
                return [
                    'supported' => true,
                    'intent' => 'hnp_expires_this_month',
                    'title' => 'HNP expires this month',
                ];
            }


            // HNP expires in/within/next X days  (X is user-entered)
            if (preg_match('/\b(in|within|next)\s+(\d{1,3})\s+day(s)?\b/', $qLower, $m)) {
                $days = (int)$m[2];
                $days = max(1, min(365, $days)); // safety clamp

                return [
                    'supported' => true,
                    'intent' => 'hnp_expires_next_days',
                    'days' => $days,
                    'title' => "HNP expires within {$days} days",
                ];
            }

            // fallback help
            return [
                'supported' => false,
                'message' => "Try: 'HNP expires this month' or 'HNP expires in 30 days' (you can change 30).",
            ];
        }

        // Vitals monthly intent
        /*
        if (
            (str_contains($qLower, 'vital') || str_contains($qLower, 'bp') || str_contains($qLower, 'health'))
            && (str_contains($qLower, 'month') || preg_match('/\b\d{4}-\d{2}\b/', $qLower))
        ) {

        // Extract YYYY-MM (example: 2026-02)
        if (preg_match('/\b(\d{4})-(\d{2})\b/', $qLower, $m)) {
            $year  = (int)$m[1];
            $month = (int)$m[2];
        } else {
            // Default = current month
            $year  = now()->year;
            $month = now()->month;
        }

        // Extract ParticipantId
        if (preg_match('/\b(id|participant)\s*(\d+)\b/', $qLower, $m2)) {
            $pid = $m2[2];
        } else {
            return [
                'supported' => false,
                'message' => "Try: Vitals for ID 101 for 2026-02",
            ];

        }

        return [
            'supported' => true,
            'intent' => 'vitals_monthly',
            'participant_id' => $pid,
            'year' => $year,
            'month' => $month,
            'title' => "Monthly Vitals Report",
        ];

    } */

    // Vitals monthly intent (allow without month/year for follow-up)
    if (str_contains($qLower, 'vital') || str_contains($qLower, 'bp') || str_contains($qLower, 'health')) {

        $type = null;
        if (str_contains($qLower, 'morning') || str_contains($qLower, 'shift a') || preg_match('/\b(a)\b/', $qLower)) $type = 'A';
        if (str_contains($qLower, 'noon')    || str_contains($qLower, 'shift n') || preg_match('/\b(n)\b/', $qLower)) $type = 'N';


        // Extract ParticipantId (required)
        if (!preg_match('/\b(id|participant)\s*(\d+)\b/', $qLower, $m2)) {
            return [
                'supported' => false,
                'message' => "Try: Vitals for ID 101 for 2026-02",
            ];
        }

        $pid = $m2[2];

        // If YYYY-MM exists
        if (preg_match('/\b(\d{4})-(\d{2})\b/', $qLower, $m)) {
            $year  = (int)$m[1];
            $month = (int)$m[2];

            // safety clamp
            $month = max(1, min(12, $month));
            $year  = max(2000, min(2100, $year));

            return [
                'supported' => true,
                'intent' => 'vitals_monthly',
                'type' => $type ?? 'A',
                'participant_id' => $pid,
                'year' => $year,
                'month' => $month,
                'title' => "Monthly Vitals Report",
            ];

        }

        // ✅ No month/year provided → ask follow-up
        return [
            'supported' => true,
            'intent' => 'vitals_monthly_needs_month',
            'participant_id' => $pid,
            'title' => "Monthly Vitals Report",
        ];
    }

    // Attendance "last taken" intent
    if (
        str_contains($qLower, 'last attendance')
        || (str_contains($qLower, 'attendance') && str_contains($qLower, 'last'))
        || str_contains($qLower, 'last attendance taken')
    ) {
        return [
            'supported' => true,
            'intent' => 'last_attendance_by_shift',
            'title' => 'Last attendance taken (Morning vs Noon)',
        ];
    }

    // Attendance totals (with follow-up)
    if (str_contains($qLower, 'attendance')) {

        // Range
        if (preg_match('/\b(\d{1,2}\/\d{1,2}\/\d{4})\s*(to|\-)\s*(\d{1,2}\/\d{1,2}\/\d{4})\b/i', $q, $m)) {
            return [
                'supported' => true,
                'intent' => 'attendance_totals_range',
                'from' => $m[1],
                'to'   => $m[3],
                'title' => "Attendance totals ({$m[1]} - {$m[3]})",
            ];
        }

        // Month
        if ($my = $this->parseMonthYear($q)) {

            $label = \Carbon\Carbon::create($my['year'], $my['month'], 1)->format('F Y');

            return [
                'supported' => true,
                'intent' => 'attendance_totals_month',
                'year' => (int)$my['year'],
                'month' => (int)$my['month'],
                'title' => "Attendance totals ({$label})",
            ];
        }

        // ✅ Ask follow-up
        return [
            'supported' => true,
            'intent' => 'attendance_needs_date',
            'title' => 'Attendance totals',
        ];
    }



    } // End Method

    private function extractMonth(string $qLower): ?int
    {
        if (str_contains($qLower, 'this month') || str_contains($qLower, 'current month')) {
            return (int) now()->month;
        }

        $months = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        ];

        foreach ($months as $name => $num) {
            if (str_contains($qLower, $name)) return $num;
        }

        if (preg_match('/\bmonth\s+(\d{1,2})\b/', $qLower, $m)) {
            $n = (int)$m[1];
            if ($n >= 1 && $n <= 12) return $n;
        }

        return null;

    } // End Method

    private function countBySex(string $shift)
    {
        return DB::table('members')
            ->select('Sex', DB::raw('COUNT(*) as total'))
            ->where('Trans_flag', $shift)
            ->groupBy('Sex')
            ->orderBy('Sex')
            ->get();

    } // End Method

    private function birthdaysInMonth(int $month)
    {
        return DB::table('members')
            ->select(
                'ParticipantId',

                DB::raw("
                    TRIM(CONCAT(
                        COALESCE(FirstName,''), ' ',
                        COALESCE(NULLIF(MiddleName,''), ''),
                        CASE WHEN MiddleName IS NULL OR MiddleName='' THEN '' ELSE ' ' END,
                        COALESCE(LastName,'')
                    )) as MemberName
                "),

                 // ✅ Format as MM/DD/YYYY
                DB::raw("DATE_FORMAT(DateOfBirth, '%m/%d/%Y') as DateOfBirth"),
                // ✅ Age this year
                DB::raw("(YEAR(CURDATE()) - YEAR(DateOfBirth)) as Age")

            )
            ->whereNotNull('DateOfBirth')
            ->whereMonth('DateOfBirth', $month)   // ✅ works on DATETIME
            ->where('Trans_flag','A')
            ->orderByRaw('DAY(DateOfBirth) ASC')  // ✅ sort by day
            ->limit(300)
            ->get();

    } // End Method

    private function birthdaysNextDays(int $days)
    {
        // next_bday = birthday date in this year or next year (if already passed)
        $nextBdayExpr = "
            CASE
            WHEN DATE_FORMAT(DateOfBirth, '%m-%d') < DATE_FORMAT(CURDATE(), '%m-%d')
                THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(DateOfBirth, '%m-%d')), '%Y-%m-%d')
            ELSE
                STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(DateOfBirth, '%m-%d')), '%Y-%m-%d')
            END
        ";

        return DB::table('members')
            ->select(
                'ParticipantId',
                DB::raw("
                    TRIM(CONCAT(
                        COALESCE(FirstName,''), ' ',
                        COALESCE(NULLIF(MiddleName,''), ''),
                        CASE WHEN MiddleName IS NULL OR MiddleName='' THEN '' ELSE ' ' END,
                        COALESCE(LastName,'')
                    )) as MemberName
                "),
                DB::raw("DATE_FORMAT(DateOfBirth, '%m/%d/%Y') as DateOfBirth"),   // display
                //DB::raw("$nextBdayExpr as NextBirthday")
                DB::raw("DATE_FORMAT($nextBdayExpr, '%m/%d/%Y') as NextBirthday"),
                // ✅ Age on next birthday
                DB::raw("(YEAR($nextBdayExpr) - YEAR(DateOfBirth)) as Age")
            )
            ->whereNotNull('DateOfBirth')
            ->whereRaw("$nextBdayExpr BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)", [$days])
            ->where('Trans_flag','A')
            ->orderByRaw("$nextBdayExpr ASC")
            ->limit(300)
            ->get();

    } // End Method

    public function birthdaysExcel(Request $request)
    {
        $mode = $request->query('mode', 'month');

        if ($mode === 'next_days') {
            $days = max(1, min(365, (int)$request->query('days', 30)));
            $rows = $this->birthdaysNextDays($days);
            $title = "Upcoming Birthdays (Next {$days} Days)";
        } else {
            $month = (int)$request->query('month', now()->month);
            $month = max(1, min(12, $month));
            $rows = $this->birthdaysInMonth($month);
            $title = "Birthdays in " . now()->setMonth($month)->format('F');
        }

        // Simple inline export class (no separate file)
        $export = new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private $rows) {}
            public function collection() {
                return collect($this->rows)->map(fn($r) => (array)$r);
            }
            public function headings(): array {
                $first = collect($this->rows)->first();
                return $first ? array_keys((array)$first) : [];
            }
        };

        return Excel::download($export, $title . '.xlsx');

    } // End Method

    public function birthdaysPdf(Request $request)
    {
        $mode = $request->query('mode', 'month');

        if ($mode === 'next_days') {
            $days = max(1, min(365, (int)$request->query('days', 30)));
            $rows = $this->birthdaysNextDays($days);
            $title = "Upcoming Birthdays (Next {$days} Days)";
        } else {
            $month = (int)$request->query('month', now()->month);
            $month = max(1, min(12, $month));
            $rows = $this->birthdaysInMonth($month);
            $title = "Birthdays in " . now()->setMonth($month)->format('F');
        }

        $pdf = Pdf::loadView('chat.exports.birthday', compact('rows', 'title'))
            ->setPaper('letter', 'portrait');

        return $pdf->download($title . '.pdf');

    } // End Method

    private function paExpiresThisMonth()
    {
        return DB::table('members')
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName',
                'HMO_enrolled',
                'medicaid_PA_no',
                DB::raw("DATE_FORMAT(medicaid_PA_End, '%m/%d/%Y') as medicaid_PA_End")
            )
            ->whereNotNull('medicaid_PA_End')
            ->whereYear('medicaid_PA_End', now()->year)
            ->whereMonth('medicaid_PA_End', now()->month)
            ->where('Trans_flag','A')
            ->orderBy('medicaid_PA_End')
            ->limit(500)
            ->get();

    } // End Method

    private function paExpiresNextDays(int $days)
    {
        $start = now()->startOfDay();
        $end   = now()->addDays($days)->endOfDay();

        return DB::table('members')
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName',
                'HMO_enrolled',
                'medicaid_PA_no',
                  // display format
                DB::raw("DATE_FORMAT(medicaid_PA_End, '%m/%d/%Y') as medicaid_PA_End"),

                // ✅ urgent if expires within next 7 days (including today)
                DB::raw("CASE WHEN medicaid_PA_End <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END as Urgent"),

                // optional: days remaining (very useful)
                DB::raw("DATEDIFF(DATE(medicaid_PA_End), CURDATE()) as DaysLeft")
            )
            ->whereNotNull('medicaid_PA_End')
            ->whereBetween('medicaid_PA_End', [$start, $end])
            ->orderBy('medicaid_PA_End')
            ->where('Trans_flag','A')
            ->limit(500)
            ->get();

    } // End Method


    private function HNPExpiresThisMonth()
    {
        return DB::table('members')
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName',
                DB::raw("DATE_FORMAT(HNP_Expiry_date, '%m/%d/%Y') as HNP_Expiry_date")
            )
            ->whereNotNull('HNP_Expiry_date')
            ->whereYear('HNP_Expiry_date', now()->year)
            ->whereMonth('HNP_Expiry_date', now()->month)
            ->where('Trans_flag','A')
            ->orderBy('HNP_Expiry_date')
            ->limit(500)
            ->get();

    } // End Method

    private function HNPExpiresNextDays(int $days)
    {
        $start = now()->startOfDay();
        $end   = now()->addDays($days)->endOfDay();

        return DB::table('members')
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName',
                // display format
                DB::raw("DATE_FORMAT(HNP_Expiry_date, '%m/%d/%Y') as HNP_Expiry_date"),

                // ✅ urgent if expires within next 7 days (including today)
                DB::raw("CASE WHEN HNP_Expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END as Urgent"),

                // optional: days remaining (very useful)
                DB::raw("DATEDIFF(DATE(HNP_Expiry_date), CURDATE()) as DaysLeft")
            )
            ->whereNotNull('HNP_Expiry_date')
            ->whereBetween('HNP_Expiry_date', [$start, $end])
            ->orderBy('HNP_Expiry_date')
            ->where('Trans_flag','A')
            ->limit(500)
            ->get();

    } // End Method

    private function vitalsMonthly(string $participantId, int $year, int $month)
    {
        $start = now()->setDate($year, $month, 1)->startOfMonth()->startOfDay();
        $end   = now()->setDate($year, $month, 1)->endOfMonth()->endOfDay();

        return DB::table('partbp')
            ->select(
                'ParticipantId',

                DB::raw("DATE_FORMAT(CalenderDate, '%m/%d/%Y') as CalenderDate"),

                'LowBP',
                'HighBP',
                'Sugar',
                'Pulse',
                'Temperature',
                'Weight',
            )
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$start, $end])
            ->orderBy('CalenderDate')
            ->limit(100)
            ->get();

    } // End Method

    private function suggestionGroups(): array
    {
        return [
            'Members' => [
                "How many male and female members in Morning shift?",
                "How many male and female members in Noon shift?",
            ],
            'Birthdays' => [
                "Which members have birthdays this month?",
                "Birthdays in March",
                "Upcoming birthdays (next 30 days)",
                "Birthdays this week",
            ],
            'Prior Authorization' => [
                "PA expires this month",
                "PA expires in 15 days",
                "PA expires within 30 days",
            ],
            'HNP Monitoring' => [
                "HNP expires this month",
                "HNP expires in 15 days",
                "HNP expires within 30 days",
            ],

            'Vitals' => [
                "Vitals for ID 101 for 2026-02",
                "Monthly vitals for participant 101 2026-02",
                "Show vitals id 101 2026-02",
            ],

            'Attendance' => [
                "Last attendance date for Morning and Noon shifts",
                "Attendance for January 2026",
                "Attendance from 10/01/2025 to 10/31/2025"
            ],
        ];

    } // End Method

    private function memberNameById(string $participantId): ?string
    {
        $m = DB::table('members')
            ->select('FirstName', 'LastName')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$m) return null;

        return trim(($m->FirstName ?? '') . ' ' . ($m->LastName ?? ''));

    } // End Method

    public function vitalsExcel(Request $request)
    {
        $pid   = trim((string)$request->query('participant_id', ''));
        $year  = (int)$request->query('year', now()->year);
        $month = (int)$request->query('month', now()->month);

        $month = max(1, min(12, $month));
        $year  = max(2000, min(2100, $year));

        $rows = $this->vitalsMonthly($pid, $year, $month);

        $label = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        $name  = $this->memberNameById($pid);
        $file  = "Vitals_{$pid}_" . \Carbon\Carbon::create($year, $month, 1)->format('Y-m') . ".xlsx";

        $export = new class($rows) implements
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function __construct(private $rows) {}
            public function collection() { return collect($this->rows)->map(fn($r) => (array)$r); }
            public function headings(): array {
                $first = collect($this->rows)->first();
                return $first ? array_keys((array)$first) : [];
            }
        };

        return Excel::download($export, $file);

    } // End Method

    public function vitalsPdf(Request $request)
    {
        $pid   = trim((string)$request->query('participant_id', ''));
        $year  = (int)$request->query('year', now()->year);
        $month = (int)$request->query('month', now()->month);

        $month = max(1, min(12, $month));
        $year  = max(2000, min(2100, $year));

        $rows = $this->vitalsMonthly($pid, $year, $month);

        $label = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        $name  = $this->memberNameById($pid);

        $title = "Monthly Vitals – ID {$pid}" . ($name ? " - {$name}" : "") . " – {$label}";
        $file  = "Vitals_{$pid}_" . \Carbon\Carbon::create($year, $month, 1)->format('Y-m') . ".pdf";

        $pdf = Pdf::loadView('chat.exports.vitals_monthly', compact('rows', 'title'))
            ->setPaper('letter', 'landscape');  // vitals fits better

        return $pdf->download($file);

    } // End Method

    private function totalMembersInShift(string $shift): int
    {
        return (int) DB::table('members')
            ->where('Trans_flag', $shift)
            ->count();

    } //End method

    private function lastAttendanceForShift(string $shift): array
    {
        // Subquery: members in this shift
        $membersSub = DB::table('members')
            ->select('ParticipantId')
            ->where('Trans_flag', $shift);

        // Find the latest attendance datetime for this shift
        $lastDt = DB::table('partattendence')
            ->whereIn('ParticipantId', $membersSub)
            ->max('CalenderDate');

        $totalMembers = $this->totalMembersInShift($shift);

        if (!$lastDt) {
            return [
                'Shift' => $shift === 'A' ? 'Morning (A)' : 'Noon (N)',
                'LastAttendanceDate' => '',
                'PresentCount' => 0,
                'TotalMembers' => $totalMembers,
            ];
        }

        // Count present (AttendanceYN == 'Y') on that date (ignore empty/NULL automatically)
        $present = (int) DB::table('partattendence')
            ->whereIn('ParticipantId', $membersSub)
            ->whereDate('CalenderDate', \Carbon\Carbon::parse($lastDt)->toDateString())
            ->where('AttendanceYN', 'Y')
            ->count();

        return [
            'Shift' => $shift === 'A' ? 'Morning (A)' : 'Noon (N)',
            'LastAttendanceDate' => \Carbon\Carbon::parse($lastDt)->format('m/d/Y'),
            'PresentCount' => $present,
            'TotalMembers' => $totalMembers,
        ];

    } // End Method

    private function lastAttendanceSummary(): array
    {
        return [
            $this->lastAttendanceForShift('A'),
            $this->lastAttendanceForShift('N'),
        ];

    } // End Method

    public function indexFull(Request $request)
    {
        $suggestionGroups = $this->suggestionGroups();
        $user = $request->user();   // ✅ safest
        $name = trim(($user->firstname ?? $user->name ?? ''));
        $hour = (int) now()->format('H');
        $greet =
            $hour < 12 ? 'Good morning' :
            ($hour < 17 ? 'Good afternoon' : 'Good evening');

        $welcome = $name ? "{$greet}, {$name}!" : "{$greet}!";

        return view('chat.chat_index_full', compact('suggestionGroups', 'welcome'));

    } // End Method

    private function setVitalsPending(string $pid): void
        {
            session(['chat_pending_vitals' => [
                'participant_id' => $pid,
                'ts' => now()->timestamp,
            ]]);

        } // End Method

    private function getVitalsPending(): ?array
        {
            $p = session('chat_pending_vitals');
            return is_array($p) ? $p : null;

        } // End Method

    private function clearVitalsPending(): void
        {
            session()->forget('chat_pending_vitals');

        } // End Method

    private function parseMonthYear(string $text): ?array
    {
        $t = mb_strtolower(trim($text));

        // Format: YYYY-MM (ex: 2026-02)
        if (preg_match('/\b(20\d{2})-(0[1-9]|1[0-2])\b/', $t, $m)) {
            return ['year' => (int)$m[1], 'month' => (int)$m[2]];
        }

        // Format: "Feb 2026" or "February 2026"
        if (preg_match('/\b(jan|feb|mar|apr|may|jun|jul|aug|sep|sept|oct|nov|dec)[a-z]*\s+(20\d{2})\b/', $t, $m)) {
            $map = [
                'jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,
                'jul'=>7,'aug'=>8,'sep'=>9,'sept'=>9,'oct'=>10,'nov'=>11,'dec'=>12,
            ];

            $mon = substr($m[1], 0, 4) === 'sept' ? 'sept' : substr($m[1], 0, 3);
            $month = (int)($map[$mon] ?? 0);

            if ($month < 1 || $month > 12) {
                return null;
            }

            return ['year' => (int)$m[2], 'month' => $month];
        }

        return null;

    } // End Method

    private function attendanceTotalsByShift(\Carbon\Carbon $start, \Carbon\Carbon $end): array
    {
        // ✅ Force full-day range
        $start = $start->copy()->startOfDay();
        $end   = $end->copy()->endOfDay();

        // Morning (A)
        $aCount = DB::table('partattendence')
            ->whereBetween('CalenderDate', [$start, $end])
            ->where('AttendanceYN', 'Y')
            ->whereIn('ParticipantId', function ($q) {
                $q->select('ParticipantId')
                ->from('members')
                ->where('Trans_flag', 'A');
            })
            ->count();

        // Noon (N)
        $nCount = DB::table('partattendence')
            ->whereBetween('CalenderDate', [$start, $end])
            ->where('AttendanceYN', 'Y')
            ->whereIn('ParticipantId', function ($q) {
                $q->select('ParticipantId')
                ->from('members')
                ->where('Trans_flag', 'N');
            })
            ->count();

        return [
            ['Shift' => 'Morning (A)', 'PresentCount' => (int)$aCount],
            ['Shift' => 'Noon (N)',    'PresentCount' => (int)$nCount],
        ];

    } // End Method

    private function setAttendancePending(): void
    {
        session(['chat_pending_attendance' => [
            'ts' => now()->timestamp,
        ]]);

    } //

    private function getAttendancePending(): ?array
    {
        $p = session('chat_pending_attendance');
        return is_array($p) ? $p : null;

    } // End Method

    private function clearAttendancePending(): void
    {
        session()->forget('chat_pending_attendance');

    } // End Method


}
