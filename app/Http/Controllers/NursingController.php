<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Partadl;
use App\Models\Partbp;
use App\Traits\HasCommonDbFields;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Exports\QuarterlyAssessmentMembersExport;

    function formatPhone($number)
    {
        $number = preg_replace('/\D/', '', $number); // remove non-digits

        if (strlen($number) == 10) {
            return '(' . substr($number, 0, 3) . ') ' .
                substr($number, 3, 3) . '-' .
                substr($number, 6);
        }

        return $number; // fallback
    }

class NursingController extends Controller
{
    use HasCommonDbFields;

    public function NursingDashboard(){

        return view('nursing.nursing_index',['title' => 'Nursing Management', 'module' => 'nursing']);

    } // End Method

    public function NursingAllMembers($type)
    {
        $type = in_array(strtoupper($type), ['A', 'N', 'G'], true) ? strtoupper($type) : 'A';

        $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
            default => 'Morning Shift',
        };

        $title = $shiftTitle . ' Members Attendance Dates';

        $members = Member::select(
                "ParticipantId",
                "LastName",
                "FirstName",
                "MiddleName",
                "Trans_flag",
                "HMO_Enrolled",
                "medicaid_PA_End"
            )
            ->where("Trans_flag", $type)
            ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
            ->get();

        $hmoColors = DB::table('hmodropdown')
            ->where('use_flag', 'Y')
            ->get()
            ->keyBy(function ($item) {
                return trim($item->Id);
            });

        $currentMonth = now()->format('M-Y');

        return view(
            'nursing.nursing_allmembers',
            compact('members', 'currentMonth', 'hmoColors', 'type', 'shiftTitle', 'title'),
            [
                'module' => 'nursing',
            ]
        );

    } // End Method

    public function NursingEditBPData($ParticipantId, $monthYear, $type){

        $member = Member::findOrFail($ParticipantId);

        try {
            $date = Carbon::createFromFormat('Y-m', $monthYear);
        } catch (\Exception $e) {
            abort(400, 'Invalid month/year format.');
        }

        $daysInMonth = $date->daysInMonth;

        // Get existing data for the month
        $startDate = $date->copy()->startOfMonth();

        $endDate = $date->copy()->endOfMonth();

        /*
        $existingData = Partbp::where('ParticipantId', $ParticipantId)
            ->whereBetween('CalenderDate', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->CalenderDate)->day;
            });
        */

        $cacheKey = "bp_{$ParticipantId}_{$monthYear}";

        $existingData = Cache::remember($cacheKey, 300, function () use ($ParticipantId, $startDate, $endDate) {

        return Partbp::where('ParticipantId', $ParticipantId)
            ->whereBetween('CalenderDate', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return (int) substr($item->CalenderDate, 8, 2);
            });

        });

        return view('nursing.nursing_bpdata', compact('member', 'monthYear', 'daysInMonth', 'existingData', 'type'), ['title' => 'Nursing Management', 'module' => 'nursing' ] );

    } // End Method

    public function StoreBPData(Request $request, $ParticipantId, $monthYear, $type)
    {

        //$date = Carbon::createFromFormat('M-Y', $monthYear);
        $date = \Carbon\Carbon::createFromFormat('Y-m', $monthYear);

        $daysInMonth = $date->daysInMonth;

        $nextSerial = Partbp::max('SerialNo') ?? 1000000; // Default starting number if null

        $common = $this->commonFields();

        for ($day = 1; $day <= $daysInMonth; $day++) {

            //create proper Carbon date object first
            //$recordDate = $date->copy()->day($day);
            $recordDate = $date->copy()->day($day)->format('Y-m-d');


            $record = Partbp::firstOrNew(
                [   'ParticipantId' => $ParticipantId,
                    'CalenderDate' => $recordDate,
            ]);

                if (!$record->exists) {
                    $nextSerial++;
                    $record->SerialNo = $nextSerial; // Optional, only for tracking
                }

                $record->fill(array_merge([
                'LowBP' => $request->input("LowBP.$day"),
                'HighBP' => $request->input("HighBP.$day"),
                'Sugar' => $request->input("Sugar.$day"),
                'Pulse' => $request->input("Pulse.$day"),
                'Temperature' => $request->input("Temperature.$day"),
                'Weight' => $request->input("Weight.$day"),
                // Dressing checkbox stores in BathingYN
                'BathingYN'    => $request->input("BathingYN.$day", 'N'),
                'ToiletYN' => $request->input("ToiletYN.$day", 'N'),
                'TransferYN' => $request->input("TransferYN.$day", 'N'),
                'LocomotionYN' => $request->input("LocomotionYN.$day", 'N'),
                'EatingYN' => $request->input("EatingYN.$day", 'N'),
                ], $common));

                $record->save();

        }

        $cacheKey = "bp_{$ParticipantId}_{$monthYear}";
        Cache::forget($cacheKey);
        return redirect()->route('nursing.members', $type)->with('success', 'Physical data saved successfully!');

    } // End Method

    public function printView(Request $request){

        // Expect: ?ParticipantId=XXX&monthYear=YYYY-MM
        $ParticipantId = $request->query('ParticipantId');
        $monthYear     = $request->query('monthYear');

        // If ParticipantId is NOT the PK, swap to ->where('ParticipantId', $ParticipantId)->firstOrFail()
        $member = Member::findOrFail($ParticipantId);

        try {
            $date = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        } catch (\Exception $e) {
            abort(400, 'Invalid month/year format.');
        }

        $daysInMonth = $date->daysInMonth;
        $startDate   = $date->copy()->startOfMonth();
        $endDate     = $date->copy()->endOfMonth();
        /*
        $existingData = Partbp::where('ParticipantId', $ParticipantId)
            ->whereBetween('CalenderDate', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->CalenderDate)->day);
        */

        $cacheKey = "bp_{$ParticipantId}_{$monthYear}";

        $existingData = Cache::remember($cacheKey, 300, function () use ($ParticipantId, $startDate, $endDate) {

            return Partbp::where('ParticipantId', $ParticipantId)
                ->whereBetween('CalenderDate', [$startDate, $endDate])
                ->get()
                ->keyBy(fn ($item) => (int) substr($item->CalenderDate, 8, 2));

        });

        $phone = formatPhone(config('company.complc_ph') ?? '');
        $fax   = formatPhone(config('company.complc_fax') ?? '');

        $compname = config('company.comp_nm');
        $compaddr = config('company.complc_addr1');
        //$phonefax = 'Phone: (000) 000-0000 • Fax: (000) 000-0001';
        $phonefax = "Phone: {$phone} • Fax: {$fax}";

        return view('nursing.nursing_print_bpdata', compact(
            'member','monthYear','date','daysInMonth','existingData','compname','compaddr','phonefax'
        ));

    } // End Method

   public function exportMonthlyVitalsPdf(Request $request)
    {
        $validated = $request->validate([
            'type'          => ['required', 'in:A,N,G'],
            'ParticipantId' => ['required', 'string'],
            'monthYear'     => ['required', 'date_format:Y-m'],
        ], [
            'type.required'          => 'Please select Morning, Noon, or Guest.',
            'type.in'                => 'The selected member category is invalid.',
            'ParticipantId.required' => 'Please select a member.',
            'monthYear.required'     => 'Please select a month and year.',
            'monthYear.date_format'  => 'The selected month and year are invalid.',
        ]);

        $type          = strtoupper($validated['type']);
        $ParticipantId = trim($validated['ParticipantId']);
        $monthYear     = $validated['monthYear'];

        $typeName = match ($type) {
            'A' => 'Morning',
            'N' => 'Noon',
            'G' => 'Guest',
        };

        /*
        |--------------------------------------------------------------------------
        | Preserve leading zeros
        |--------------------------------------------------------------------------
        | Example: 51 becomes 051
        */
        if (ctype_digit($ParticipantId)) {
            $ParticipantId = str_pad($ParticipantId, 3, '0', STR_PAD_LEFT);
        }

        /*
        |--------------------------------------------------------------------------
        | Confirm member exists and belongs to selected category
        |--------------------------------------------------------------------------
        */
        $member = Member::query()
            ->where('ParticipantId', $ParticipantId)
            ->where('Trans_flag', $type)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nursing.reports.monthly-vitals', [
                    'type'          => $type,
                    'ParticipantId' => $ParticipantId,
                    'monthYear'     => $monthYear,
                ])
                ->with(
                    'warning',
                    'The selected participant could not be found in the ' .
                    $typeName . ' member list.'
                );
        }

        try {
            $date = Carbon::createFromFormat('Y-m', $monthYear)
                ->startOfMonth();
        } catch (\Exception $e) {
            return redirect()
                ->route('nursing.reports.monthly-vitals', [
                    'type' => $type,
                ])
                ->with('warning', 'The selected month and year are invalid.');
        }

        $daysInMonth = $date->daysInMonth;
        $startDate   = $date->copy()->startOfMonth();
        $endDate     = $date->copy()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Separate cache for category, participant, and month
        |--------------------------------------------------------------------------
        */
        $cacheKey = "monthly_vitals_{$type}_{$ParticipantId}_{$monthYear}";

        $existingData = Cache::remember(
            $cacheKey,
            300,
            function () use ($ParticipantId, $startDate, $endDate) {
                return Partbp::where('ParticipantId', $ParticipantId)
                    ->whereBetween('CalenderDate', [$startDate, $endDate])
                    ->orderBy('CalenderDate')
                    ->get()
                    ->keyBy(function ($item) {
                        return Carbon::parse($item->CalenderDate)->day;
                    });
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Stop PDF generation when no records exist
        |--------------------------------------------------------------------------
        */
        if ($existingData->isEmpty()) {
            $memberName = trim(
                ($member->LastName ?? '') . ', ' .
                ($member->FirstName ?? '')
            );

            return redirect()
                ->route('nursing.reports.monthly-vitals', [
                    'type'          => $type,
                    'ParticipantId' => $ParticipantId,
                    'monthYear'     => $monthYear,
                ])
                ->with(
                    'warning',
                    'There are no monthly vitals records for ' .
                    $memberName . ' for ' .
                    $date->format('F Y') . '.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Company information
        |--------------------------------------------------------------------------
        */
        $compname = config('company.comp_nm');
        $compaddr = config('company.complc_addr1');

        $phone = formatPhone(config('company.complc_ph') ?? '');
        $fax   = formatPhone(config('company.complc_fax') ?? '');

        $phonefax = "Phone: {$phone} • Fax: {$fax}";

        /*
        |--------------------------------------------------------------------------
        | Render existing monthly vitals PDF Blade
        |--------------------------------------------------------------------------
        */
        $html = view('nursing.nursing_print_bpdata', [
            'member'       => $member,
            'monthYear'    => $monthYear,
            'date'         => $date,
            'daysInMonth'  => $daysInMonth,
            'existingData' => $existingData,
            'compname'     => $compname,
            'compaddr'     => $compaddr,
            'phonefax'     => $phonefax,
            'type'         => $type,
            'typeName'     => $typeName,
            'forPdf'       => true,
        ])->render();

        $mpdf = new Mpdf([
            'tempDir'       => storage_path('app/mpdf'),
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 45,
            'margin_bottom' => 22,
            'margin_left'   => 10,
            'margin_right'  => 10,
            'default_font'  => 'dejavusans',
        ]);

        $mpdf->WriteHTML(
            $this->pdfCss(),
            \Mpdf\HTMLParserMode::HEADER_CSS
        );

        $memberName = trim(
            ($member->LastName ?? '') . ' ' .
            ($member->FirstName ?? '')
        );

        $displayMonth = $date->format('F Y');

        $formattedParticipantId = ctype_digit($ParticipantId)
            ? str_pad($ParticipantId, 3, '0', STR_PAD_LEFT)
            : $ParticipantId;

        /*
        |--------------------------------------------------------------------------
        | PDF header
        |--------------------------------------------------------------------------
        */
        $mpdf->SetHTMLHeader('
            <div style="text-align:center; font-weight:bold; font-size:12pt;">
                ' . e($compname) . ' — Monthly Vitals Report
            </div>

            <div style="text-align:center; font-size:9pt;">
                ' . e($compaddr) . ' • ' . e($phonefax) . '
            </div>

            <div style="text-align:center; font-size:9pt; margin-top:2px;">
                Member: ' . e($memberName) . '
                &nbsp;|&nbsp;
                Member ID: ' . e($formattedParticipantId) . '
                &nbsp;|&nbsp;
                Category: ' . e($typeName) . '
                &nbsp;|&nbsp;
                Month: ' . e($displayMonth) . '
                &nbsp;|&nbsp;
                Printed: ' . now()->format('m/d/Y h:i A') . '
            </div>

            <hr style="margin:6px 0 0 0;">
        ');

        /*
        |--------------------------------------------------------------------------
        | PDF footer
        |--------------------------------------------------------------------------
        */
        $mpdf->SetHTMLFooter('
            <hr style="margin:0 0 4px 0;">

            <table width="100%" style="font-size:9pt;">
                <tr>
                    <td width="50%">
                        © ' . date('Y') . ' ' . e($compname) . '
                    </td>

                    <td width="50%" align="right">
                        Page {PAGENO} of {nbpg}
                    </td>
                </tr>
            </table>
        ');

        $mpdf->WriteHTML(
            $html,
            \Mpdf\HTMLParserMode::HTML_BODY
        );

        $safeMemberName = preg_replace(
            '/[^A-Za-z0-9_-]+/',
            '_',
            $memberName
        );

        $filename = 'monthly_vitals_' .
            strtolower($typeName) . '_' .
            $formattedParticipantId . '_' .
            $safeMemberName . '_' .
            $monthYear . '.pdf';

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="' . $filename . '"'
            );
    } // End Method

    private function pdfCss(): string
    {
        return <<<CSS
        body { font-family: DejaVu Sans, sans-serif; }
        table { width:100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        th, td { border:1px solid #000; padding:4px; font-size:11px; text-align:center; }
        .text-left { text-align:left; }
        tr { page-break-inside: avoid; }
        .weekend-row { background: rgba(33, 199, 33, 0.15); }
        CSS;
    }


    // ADL Data

    public function NursingEditADLData($ParticipantId, $monthYear, $type){

        $member = Member::findOrFail($ParticipantId);

        try {
            $date = Carbon::createFromFormat('Y-m', $monthYear);
        } catch (\Exception $e) {
            abort(400, 'Invalid month/year format.');
        }

        $daysInMonth = $date->daysInMonth;

         // Get existing data for the month
         $startDate = $date->copy()->startOfMonth();

         $endDate = $date->copy()->endOfMonth();


         $existingData = Partadl::where('participantid', $ParticipantId)
         ->whereBetween('calenderDate', [$startDate, $endDate])
         ->get()
         ->keyBy(function ($item) {
             return Carbon::parse($item->calenderDate)->day;
         });


        return view('nursing.nursing_adldata', compact('member', 'monthYear', 'daysInMonth', 'existingData', 'type'), ['title' => 'Nursing Management', 'module' => 'nursing' ] );

    } // End Method

    public function printADLView(Request $request){

        // Expect: ?ParticipantId=XXX&monthYear=YYYY-MM
        $ParticipantId = $request->query('participantid');
        $monthYear     = $request->query('monthYear');

        // If ParticipantId is NOT the PK, swap to ->where('ParticipantId', $ParticipantId)->firstOrFail()
        $member = Member::findOrFail($ParticipantId);

        try {
            $date = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        } catch (\Exception $e) {
            abort(400, 'Invalid month/year format.');
        }

        $daysInMonth = $date->daysInMonth;
        $startDate   = $date->copy()->startOfMonth();
        $endDate     = $date->copy()->endOfMonth();


        $existingData = Partadl::where('participantid', $ParticipantId)
            ->whereBetween('calenderDate', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->calenderDate)->day);

        $compname = config('company.comp_nm', '');
        $compaddr = '';
        $phonefax = 'Phone: (000) 000-0000 • Fax: (000) 000-0001';

        return view('nursing.nursing_print_adldata', compact(
            'member','monthYear','date','daysInMonth','existingData','compname','compaddr','phonefax'
        ));

    } // End Method

    public function exportADLPdf(Request $request){

        $ParticipantId = $request->query('participantid');
        $monthYear     = $request->query('monthYear');

        $member = Member::findOrFail($ParticipantId);

        try {
            $date = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        } catch (\Exception $e) {
            abort(400, 'Invalid month/year format.');
        }

        $daysInMonth = $date->daysInMonth;
        $startDate   = $date->copy()->startOfMonth();
        $endDate     = $date->copy()->endOfMonth();

        $existingData = Partadl::where('participantid', $ParticipantId)
            ->whereBetween('calenderDate', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->calenderDate)->day);

        $compname = config('app.name', 'Company Name');
        $compaddr = '123 Main St, City, ST 00000';
        $phonefax = 'Phone: (000) 000-0000 • Fax: (000) 000-0001';

        $html = view('nursing.nursing_print_adldata', [
                'member'      => $member,
                'monthYear'   => $monthYear,
                'date'        => $date,
                'daysInMonth' => $daysInMonth,
                'existingData'=> $existingData,
                'compname'    => $compname,
                'compaddr'    => $compaddr,
                'phonefax'    => $phonefax,
                'forPdf'      => true,             // 👈 add this
                ])->render();

        $mpdf = new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 45,
            'margin_bottom' => 22,
            'margin_left'   => 10,
            'margin_right'  => 10,
            // 'tempDir'     => storage_path('app/mpdf/tmp'), // uncomment on Linux if needed
            'default_font'  => 'dejavusans', // supports ✓ and multilingual text
        ]);

        $mpdf->WriteHTML($this->pdfCss(), \Mpdf\HTMLParserMode::HEADER_CSS);

        $mpdf->SetHTMLHeader('
            <div style="text-align:center; font-weight:bold; font-size:12pt;">'.$compname.' —  ADL Report</div>
            <div style="text-align:center; font-size:9pt;">'.$compaddr.' • '.$phonefax.'</div>
            <div style="text-align:center; font-size:9pt; margin-top:2px;">
                Member: '.e(($member->LastName ?? '').' '.($member->FirstName ?? '')).'
                &nbsp;|&nbsp; Month: '.e($monthYear).'
                &nbsp;|&nbsp; Printed: '.now()->format('d/m/Y H:i').'
            </div>
            <hr style="margin:6px 0 0 0;">
        ');

        $mpdf->SetHTMLFooter('
            <hr style="margin:0 0 4px 0;">
            <table width="100%" style="font-size:9pt;">
                <tr>
                    <td width="50%">© '.date('Y').' '.$compname.'</td>
                    <td width="50%" align="right">Page {PAGENO} of {nbpg}</td>
                </tr>
            </table>
        ');

        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $filename = 'ADL_'.$ParticipantId.'_'.str_replace(':','-',$monthYear).'.pdf';
        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');


    } // End Method

    public function bpReportMember(Request $request)
    {
        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $rows = collect();
        $summary = null;

        $participantId = $request->participant_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($participantId && $startDate && $endDate) {
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
            $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

            $rows = DB::table('partbp')
                ->where('ParticipantId', $participantId)
                ->whereBetween('CalenderDate', [$start, $end])
                ->orderBy('CalenderDate')
                ->get([
                    'CalenderDate',
                    'LowBP',
                    'HighBP',
                    'Pulse',
                    'Weight',
                ]);

            $avg = DB::table('partbp')
                ->where('ParticipantId', $participantId)
                ->whereBetween('CalenderDate', [$start, $end])
                ->selectRaw('
                    ROUND(AVG(LowBP), 0) as avg_lowbp,
                    ROUND(AVG(HighBP), 0) as avg_highbp,
                    ROUND(AVG(Pulse), 0) as avg_pulse,
                    ROUND(AVG(Weight), 1) as avg_weight
                ')
                ->first();

            $member = DB::table('members')
                ->where('ParticipantId', $participantId)
                ->first([
                    'ParticipantId',
                    'LastName',
                    'FirstName'
                ]);

            $summary = [
                'member'         => $member,
                'participant_id' => $participantId,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'avg_lowbp'      => $avg->avg_lowbp ?? null,
                'avg_highbp'     => $avg->avg_highbp ?? null,
                'avg_pulse'      => $avg->avg_pulse ?? null,
                'avg_weight'     => $avg->avg_weight ?? null,
            ];
        }

        return view('nursing.reports.bp_member_report', compact(
            'members',
            'rows',
            'summary',
            'participantId',
            'startDate',
            'endDate'
        ));

    } // End Method


    public function bpReportShift(Request $request)
    {
        $rows = collect();

        $shift = $request->shift;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($shift && $startDate && $endDate) {
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
            $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

            $rows = DB::table('partbp as p')
                ->join('members as m', 'm.ParticipantId', '=', 'p.ParticipantId')
                ->where('m.Trans_flag', $shift)
                ->whereBetween('p.CalenderDate', [$start, $end])
                ->groupBy(
                    'p.ParticipantId',
                    'm.LastName',
                    'm.FirstName'
                )
                ->orderBy('m.LastName')
                ->orderBy('m.FirstName')
                ->selectRaw('
                    p.ParticipantId,
                    m.LastName,
                    m.FirstName,
                    ROUND(AVG(p.LowBP), 0) as avg_lowbp,
                    ROUND(AVG(p.HighBP), 0) as avg_highbp,
                    ROUND(AVG(p.Pulse), 0) as avg_pulse,
                    ROUND(AVG(p.Weight), 1) as avg_weight
                ')
                ->get();
        }

        return view('nursing.reports.bp_shift_report', compact(
            'rows',
            'shift',
            'startDate',
            'endDate'
        ));

    } // End Method


    public function bpReportMemberPdf(Request $request)
    {
        $participantId = $request->participant_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
        $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

        $rows = DB::table('partbp')
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$start, $end])
            ->orderBy('CalenderDate')
            ->get([
                'CalenderDate',
                'LowBP',
                'HighBP',
                'Pulse',
                'Weight',
            ]);

        $avg = DB::table('partbp')
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$start, $end])
            ->selectRaw('
                ROUND(AVG(LowBP), 0) as avg_lowbp,
                ROUND(AVG(HighBP), 0) as avg_highbp,
                ROUND(AVG(Pulse), 0) as avg_pulse,
                ROUND(AVG(Weight), 1) as avg_weight
            ')
            ->first();

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first([
                'ParticipantId',
                'LastName',
                'FirstName'
            ]);

        $html = view('nursing.reports.pdf.bp_member_report_pdf', compact(
            'rows',
            'avg',
            'member',
            'startDate',
            'endDate'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 12,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->SetTitle('Member BP Pulse Weight Report');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('bp_member_report.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf');

    } // End Method


    public function bpReportShiftPdf(Request $request)
    {
        $shift = $request->shift;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
        $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

        $members = DB::table('members')
            ->where('Trans_flag', $shift)
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName'
            ]);

        $averages = DB::table('partbp')
            ->whereBetween('CalenderDate', [$start, $end])
            ->groupBy('ParticipantId')
            ->selectRaw('
                ParticipantId,
                ROUND(AVG(LowBP), 0) as avg_lowbp,
                ROUND(AVG(HighBP), 0) as avg_highbp,
                ROUND(AVG(Pulse), 0) as avg_pulse,
                ROUND(AVG(Weight), 1) as avg_weight
            ')
            ->get()
            ->keyBy('ParticipantId');

        $rows = $members->map(function ($member) use ($averages) {
            if (!isset($averages[$member->ParticipantId])) {
                return null;
            }

            $avg = $averages[$member->ParticipantId];

            return (object) [
                'ParticipantId' => $member->ParticipantId,
                'LastName'      => $member->LastName,
                'FirstName'     => $member->FirstName,
                'avg_lowbp'     => $avg->avg_lowbp,
                'avg_highbp'    => $avg->avg_highbp,
                'avg_pulse'     => $avg->avg_pulse,
                'avg_weight'    => $avg->avg_weight,
            ];
        })->filter()->values();

        $html = view('nursing.reports.pdf.bp_shift_report_pdf', compact(
            'rows',
            'shift',
            'startDate',
            'endDate'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 12,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->SetTitle('Shift BP Pulse Weight Average Report');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('bp_shift_report.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf');

    } // End Method


    public function bpReportMemberExcel(Request $request)
    {
        $company = $this->reportCompany();

        $participantId = $request->participant_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
        $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

        $rows = DB::table('partbp')
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$start, $end])
            ->orderBy('CalenderDate')
            ->get([
                'CalenderDate',
                'LowBP',
                'HighBP',
                'Pulse',
                'Weight',
            ]);

        $avg = DB::table('partbp')
            ->where('ParticipantId', $participantId)
            ->whereBetween('CalenderDate', [$start, $end])
            ->selectRaw('
                ROUND(AVG(LowBP), 0) as avg_lowbp,
                ROUND(AVG(HighBP), 0) as avg_highbp,
                ROUND(AVG(Pulse), 0) as avg_pulse,
                ROUND(AVG(Weight), 1) as avg_weight
            ')
            ->first();

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first([
                'ParticipantId',
                'LastName',
                'FirstName'
            ]);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BpMemberReportExport(
                $company,
                $rows,
                $avg,
                $member,
                $startDate,
                $endDate
            ),
            'bp_member_report.xlsx'
        );

    } // End Method


    public function bpReportShiftExcel(Request $request)
    {
        $company = $this->reportCompany();

        $shift = $request->shift;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
        $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

        $members = DB::table('members')
            ->where('Trans_flag', $shift)
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName'
            ]);

        $averages = DB::table('partbp')
            ->whereBetween('CalenderDate', [$start, $end])
            ->groupBy('ParticipantId')
            ->selectRaw('
                ParticipantId,
                ROUND(AVG(LowBP), 0) as avg_lowbp,
                ROUND(AVG(HighBP), 0) as avg_highbp,
                ROUND(AVG(Pulse), 0) as avg_pulse,
                ROUND(AVG(Weight), 1) as avg_weight
            ')
            ->get()
            ->keyBy('ParticipantId');

        $rows = $members->map(function ($member) use ($averages) {
            if (!isset($averages[$member->ParticipantId])) {
                return null;
            }

            $avg = $averages[$member->ParticipantId];

            return (object) [
                'ParticipantId' => $member->ParticipantId,
                'LastName'      => $member->LastName,
                'FirstName'     => $member->FirstName,
                'avg_lowbp'     => $avg->avg_lowbp,
                'avg_highbp'    => $avg->avg_highbp,
                'avg_pulse'     => $avg->avg_pulse,
                'avg_weight'    => $avg->avg_weight,
            ];
        })->filter()->values();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BpShiftReportExport(
                $company,
                $rows,
                $shift,
                $startDate,
                $endDate
            ),
            'bp_shift_report.xlsx'
        );

    } // End Method


    private function validateBpReportDates(Request $request, $requireMember = false, $requireShift = false)
    {
        $rules = [
            'start_date' => ['required', 'date_format:m/d/Y'],
            'end_date'   => ['required', 'date_format:m/d/Y'],
        ];

        if ($requireMember) {
            $rules['participant_id'] = ['required'];
        }

        if ($requireShift) {
            $rules['shift'] = ['required', 'in:A,N'];
        }

        $request->validate($rules);

        $start = \Carbon\Carbon::createFromFormat('m/d/Y', $request->start_date);
        $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $request->end_date);
        $today = now()->startOfDay();

        if ($start->gt($end)) {
            return back()->withErrors(['start_date' => 'Start date cannot be greater than end date.'])->withInput();
        }

        if ($start->gt($today) || $end->gt($today)) {
            return back()->withErrors(['end_date' => 'Future dates are not allowed.'])->withInput();
        }

        return null;

    } // End Report

    private function formatPhoneFax($value)
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }

        return $value;

    } // End Method

    private function reportCompany()
    {
        return [
            'name'    => config('company.comp_nm', 'Company Name'),
            'address' => config('company.complc_addr1', 'Company Address'),
            'phone'   => $this->formatPhoneFax(config('company.complc_ph', '')),
            'fax'     => $this->formatPhoneFax(config('company.complc_fax', '')),
        ];

    } // End Method


   public function monitoringReport(Request $request)
    {
        $company = $this->reportCompany();
        $shift = $request->shift;
        $rows = collect();

        if (in_array($shift, ['A', 'N'])) {
            $rows = $this->getMonitoringReportRows($request);
        }

        return view('nursing.reports.monitoring_report', compact(
            'company',
            'shift',
            'rows'
        ));

    } // End Method

    public function monitoringReportPdf(Request $request)
    {
        $company = $this->reportCompany();
        $shift = $request->shift;
        $rows = $this->getMonitoringReportRows($request);

        $html = view('nursing.reports.pdf.monitoring_report_pdf', compact(
            'company',
            'shift',
            'rows'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 12,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->SetTitle('Monitoring Report for Members');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('monitoring_report.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf');

    } // End Method

    public function monitoringReportExcel(Request $request)
    {
        $company = $this->reportCompany();
        $shift = $request->shift;
        $rows = $this->getMonitoringReportRows($request);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MonitoringReportExport($company, $shift, $rows),
            'monitoring_report.xlsx'
        );

    } // End Method

    private function getMonitoringReportRows(Request $request)
    {
        $shift = $request->shift;
        $search = trim((string) $request->dt_search);
        $orderCol = $request->dt_order_col;
        $orderDir = strtolower($request->dt_order_dir) === 'desc' ? 'desc' : 'asc';

        $columnMap = [
            0 => 'ParticipantId',
            1 => 'LastName',
            2 => 'FirstName',
            3 => 'bloodsugar',
            4 => 'bloodpressure',
            5 => 'weight',
            6 => 'others',
        ];

        $sortColumn = $columnMap[$orderCol] ?? 'LastName';

        $query = DB::table('members')
            ->where('Trans_flag', $shift);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ParticipantId', 'like', "%{$search}%")
                ->orWhere('LastName', 'like', "%{$search}%")
                ->orWhere('FirstName', 'like', "%{$search}%")
                ->orWhere('bloodsugar', 'like', "%{$search}%")
                ->orWhere('bloodpressure', 'like', "%{$search}%")
                ->orWhere('weight', 'like', "%{$search}%")
                ->orWhere('others', 'like', "%{$search}%");
            });
        }

        return $query->orderBy($sortColumn, $orderDir)
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName',
                'bloodsugar',
                'bloodpressure',
                'weight',
                'others',
            ]);

    } // End Method

    public function vitalsEntry(Request $request, string $type = 'A')
    {
        $type = in_array(strtoupper($type), ['A', 'N', 'G'], true) ? strtoupper($type) : 'A';

        $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
            default => 'Morning Shift',
        };

        $selectedDateInput = $request->input('entry_date');
        $selectedDateYmd = null;

        $members = collect();
        $existingVitals = collect();

        if ($selectedDateInput) {
            try {
                $selectedDate = \Carbon\Carbon::createFromFormat('m/d/Y', $selectedDateInput)->startOfDay();
                $selectedDateYmd = $selectedDate->format('Y-m-d');

                if ($selectedDate->gt(\Carbon\Carbon::today())) {
                    return redirect()
                        ->route('nursing.vitals.entry', ['type' => $type])
                        ->with('error', 'Future date is not allowed.');
                }

                $members = DB::table('members')
                    ->select(
                        DB::raw('TRIM(ParticipantId) as ParticipantId'),
                        DB::raw('TRIM(LastName) as LastName'),
                        DB::raw('TRIM(FirstName) as FirstName')
                    )
                    ->where('Trans_flag', $type)
                    ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
                    ->get();

                $existingVitals = DB::table('partbp')
                    ->whereDate('CalenderDate', $selectedDateYmd)
                    ->get()
                    ->keyBy(function ($row) {
                        return trim($row->ParticipantId);
                    });

            } catch (\Exception $e) {
                return redirect()
                    ->route('nursing.vitals.entry', ['type' => $type])
                    ->with('error', 'Invalid date format. Please use MM/DD/YYYY.');
            }
        }

        return view('nursing.vitals_entry_simple_fix', compact(
            'type',
            'shiftTitle',
            'selectedDateInput',
            'selectedDateYmd',
            'members',
            'existingVitals'
        ));

    } // End Method

    public function saveVitalsEntry(Request $request)
    {
        $request->validate([
            'entry_date' => ['required'],
            'vitals'     => ['required', 'array'],
        ]);

        try {
            $selectedDate = \Carbon\Carbon::createFromFormat('m/d/Y', $request->entry_date)->startOfDay();
            $selectedDateYmd = $selectedDate->format('Y-m-d');
            $selectedDateTime = $selectedDate->format('Y-m-d H:i:s');

            if ($selectedDate->gt(\Carbon\Carbon::today())) {
                return back()
                    ->withInput()
                    ->with('error', 'Future date is not allowed.');
            }

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Invalid date format. Please use MM/DD/YYYY.');
        }

        $vitals = $request->input('vitals', []);

        $type = strtoupper($request->input('type', 'A'));

        if (!in_array($type, ['A', 'N', 'G'], true)) {
            $type = 'A';
        }

        DB::beginTransaction();

        try {
            $now = now();

            $userCd = Auth::user()->user_cd
                ?? Auth::user()->id
                ?? null;

            $companyId = session('company_id') ?? 'DS02';

            /*
            |--------------------------------------------------------------------------
            | Lock SerialNo while saving
            |--------------------------------------------------------------------------
            | This prevents two users from getting same SerialNo when both insert
            | new rows at the same time.
            */
            $lastSerialNo = DB::table('partbp')
                ->orderByDesc('SerialNo')
                ->lockForUpdate()
                ->value('SerialNo');

            $nextSerialNo = ((int) $lastSerialNo) + 1;

            $conflictMembers = [];
            $savedCount = 0;

            foreach ($vitals as $participantId => $row) {
                $participantId = trim($participantId);

                if ($participantId === '') {
                    continue;
                }

                $lowBP       = $this->cleanVitalValue($row['LowBP'] ?? null);
                $highBP      = $this->cleanVitalValue($row['HighBP'] ?? null);
                $sugar       = $this->cleanVitalValue($row['Sugar'] ?? null);
                $pulse       = $this->cleanVitalValue($row['Pulse'] ?? null);
                $temperature = $this->cleanVitalValue($row['Temperature'] ?? null);
                $weight      = $this->cleanVitalValue($row['Weight'] ?? null);

               $hasAnyValue =
                    $lowBP !== null ||
                    $highBP !== null ||
                    $sugar !== null ||
                    $pulse !== null ||
                    $temperature !== null ||
                    $weight !== null;

                /*
                |--------------------------------------------------------------------------
                | Blank row handling
                |--------------------------------------------------------------------------
                | If there is no existing record and all fields are blank, skip it.
                | If there is an existing record and all fields are blank, allow update
                | so user can clear/delete all vitals values for that member.
                */

                $postedUpdateDt = trim((string) ($row['original_update_dt'] ?? ''));

                /*
                |--------------------------------------------------------------------------
                | Get current database row and lock it during save
                |--------------------------------------------------------------------------
                */
                $currentRow = DB::table('partbp')
                    ->where('ParticipantId', $participantId)
                    ->where('CalenderDate', $selectedDateTime)
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | If new blank row, skip it
                |--------------------------------------------------------------------------
                | If existing row is blanked out, allow update to null values.
                */
                if (!$currentRow && !$hasAnyValue) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Concurrency check
                |--------------------------------------------------------------------------
                | If the row exists and update_dt is different from what was loaded
                | on the screen, another user changed this row.
                */
                if ($currentRow) {
                    $dbUpdateDt = !empty($currentRow->update_dt)
                        ? \Carbon\Carbon::parse($currentRow->update_dt)->format('Y-m-d H:i:s')
                        : '';

                    $screenUpdateDt = $postedUpdateDt !== ''
                        ? \Carbon\Carbon::parse($postedUpdateDt)->format('Y-m-d H:i:s')
                        : '';

                    if ($dbUpdateDt !== $screenUpdateDt) {
                        $conflictMembers[] = $participantId;
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Update existing row - Vitals fields only
                    |--------------------------------------------------------------------------
                    | Do not update ADL fields here.
                    */
                    DB::table('partbp')
                        ->where('ParticipantId', $participantId)
                        ->where('CalenderDate', $selectedDateTime)
                        ->update([
                            'trans_flag'    => $type,

                            'LowBP'         => $lowBP,
                            'HighBP'        => $highBP,
                            'Sugar'         => $sugar,
                            'Pulse'         => $pulse,
                            'Temperature'   => $temperature,
                            'Weight'        => $weight,

                            'user_cd'       => $userCd,
                            'update_dt'     => $now,
                            'update_flag'   => 'Y',
                            'company_id'    => $companyId,
                        ]);

                    $savedCount++;
                } else {
                    /*
                    |--------------------------------------------------------------------------
                    | Insert new row
                    |--------------------------------------------------------------------------
                    */
                    DB::table('partbp')->insert([
                        'SerialNo'      => $nextSerialNo++,
                        'ParticipantId' => $participantId,
                        'CalenderDate'  => $selectedDateTime,
                        'trans_flag'    => $type,

                        'LowBP'         => $lowBP,
                        'HighBP'        => $highBP,
                        'Sugar'         => $sugar,
                        'Pulse'         => $pulse,
                        'Temperature'   => $temperature,
                        'Weight'        => $weight,

                        'user_cd'       => $userCd,
                        'update_dt'     => $now,
                        'update_flag'   => 'Y',
                        'company_id'    => $companyId,
                    ]);

                    $savedCount++;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | If conflict found, rollback everything
            |--------------------------------------------------------------------------
            | This avoids partial save.
            */
            if (!empty($conflictMembers)) {
                DB::rollBack();

                return redirect()
                    ->route('nursing.vitals.entry', [
                        'type'       => $type,
                        'entry_date' => $request->entry_date,
                    ])
                    ->with(
                        'error',
                        'Some records were updated by another user. Screen has been reloaded. Please review and save again. Member ID(s): '
                        . implode(', ', $conflictMembers)
                    );
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Reload same screen after save
            |--------------------------------------------------------------------------
            */
            return redirect()
                ->route('nursing.vitals.entry', [
                    'type'       => $type,
                    'entry_date' => $request->entry_date,
                ])
                ->with('success', 'Vitals saved successfully. Screen reloaded with latest records.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                    ->route('nursing.vitals.entry', [
                        'type'       => $type,
                        'entry_date' => $request->entry_date,
                    ])
                    ->withInput()
                    ->with('error', 'Future date is not allowed.');
                        }

    } // End Method

    private function cleanVitalValue($value)
    {

        $value = trim((string) $value);

        return $value === '' ? null : strtoupper($value);

    } //End Method

    public function exportDailyVitalsPdf(Request $request, string $type = 'A')
    {
        /*
        |--------------------------------------------------------------------------
        | Validate selected shift/category
        |--------------------------------------------------------------------------
        */
        $type = strtoupper(trim($type));

        if (!in_array($type, ['A', 'N', 'G'], true)) {
            $type = 'A';
        }

        $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
        };

        /*
        |--------------------------------------------------------------------------
        | Validate report parameters
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'date'       => ['required', 'date_format:m/d/Y'],
            'sort_by'    => ['nullable', 'in:ParticipantId,LastName,FirstName'],
            'sort_order' => ['nullable', 'in:asc,desc'],
        ], [
            'date.required'    => 'Please select a report date.',
            'date.date_format' => 'Please enter the date in MM/DD/YYYY format.',
        ]);

        $dateInput = $validated['date'];
        $sortBy = $validated['sort_by'] ?? 'ParticipantId';
        $sortOrder = strtolower($validated['sort_order'] ?? 'asc');

        try {
            $selectedDate = Carbon::createFromFormat('m/d/Y', $dateInput)
                ->startOfDay();

            if ($selectedDate->format('m/d/Y') !== $dateInput) {
                throw new \Exception('Invalid date.');
            }

            if ($selectedDate->gt(Carbon::today())) {
                return redirect()
                    ->route('nursing.reports.daily-vitals', [
                        'type'       => $type,
                        'date'       => $dateInput,
                        'sort_by'    => $sortBy,
                        'sort_order' => $sortOrder,
                    ])
                    ->with(
                        'warning',
                        'Future dates are not allowed.'
                    );
            }

            $selectedDateYmd = $selectedDate->format('Y-m-d');

            /*
            |--------------------------------------------------------------------------
            | Members for selected shift/category
            |--------------------------------------------------------------------------
            */
            $memberQuery = DB::table('members')
                ->select(
                    DB::raw('TRIM(ParticipantId) as ParticipantId'),
                    DB::raw('TRIM(LastName) as LastName'),
                    DB::raw('TRIM(FirstName) as FirstName')
                )
                ->where('Trans_flag', $type);

            if ($sortBy === 'LastName') {
                $memberQuery
                    ->orderBy('LastName', $sortOrder)
                    ->orderBy('FirstName', 'asc');
            } elseif ($sortBy === 'FirstName') {
                $memberQuery
                    ->orderBy('FirstName', $sortOrder)
                    ->orderBy('LastName', 'asc');
            } else {
                $memberQuery->orderByRaw(
                    'CAST(ParticipantId AS UNSIGNED) ' . $sortOrder
                );
            }

            $members = $memberQuery->get();

            if ($members->isEmpty()) {
                return redirect()
                    ->route('nursing.reports.daily-vitals', [
                        'type'       => $type,
                        'date'       => $dateInput,
                        'sort_by'    => $sortBy,
                        'sort_order' => $sortOrder,
                    ])
                    ->with(
                        'warning',
                        'No members were found for ' . $shiftTitle . '.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Participant IDs belonging to the selected shift/category
            |--------------------------------------------------------------------------
            */
            $participantIds = $members
                ->pluck('ParticipantId')
                ->map(function ($participantId) {
                    return trim((string) $participantId);
                })
                ->filter()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Get actual vitals for selected date and selected member category
            |--------------------------------------------------------------------------
            | We do not rely only on partbp.trans_flag because older records may have
            | a blank trans_flag. Membership is determined from members.Trans_flag.
            |--------------------------------------------------------------------------
            */
            $existingVitals = DB::table('partbp')
                ->whereDate('CalenderDate', $selectedDateYmd)
                ->whereIn(
                    DB::raw('TRIM(ParticipantId)'),
                    $participantIds->all()
                )
                ->where(function ($query) {
                    $query
                        ->whereNotNull('LowBP')
                        ->orWhereNotNull('HighBP')
                        ->orWhereNotNull('Sugar')
                        ->orWhereNotNull('Pulse')
                        ->orWhereNotNull('Temperature')
                        ->orWhereNotNull('Weight');
                })
                ->get()
                ->keyBy(function ($row) {
                    return trim((string) $row->ParticipantId);
                });

            /*
            |--------------------------------------------------------------------------
            | Stop when no actual vital values exist
            |--------------------------------------------------------------------------
            | An ADL-only row in partbp will not count as a Daily Vitals record.
            |--------------------------------------------------------------------------
            */
            $existingVitals = $existingVitals->filter(function ($row) {
                return
                    trim((string) ($row->LowBP ?? '')) !== '' ||
                    trim((string) ($row->HighBP ?? '')) !== '' ||
                    trim((string) ($row->Sugar ?? '')) !== '' ||
                    trim((string) ($row->Pulse ?? '')) !== '' ||
                    trim((string) ($row->Temperature ?? '')) !== '' ||
                    trim((string) ($row->Weight ?? '')) !== '';
            });

            if ($existingVitals->isEmpty()) {
                return redirect()
                    ->route('nursing.reports.daily-vitals', [
                        'type'       => $type,
                        'date'       => $dateInput,
                        'sort_by'    => $sortBy,
                        'sort_order' => $sortOrder,
                    ])
                    ->with(
                        'warning',
                        'There are no daily vitals records for ' .
                        $shiftTitle . ' for ' .
                        $selectedDate->format('m/d/Y') . '.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Nurse name
            |--------------------------------------------------------------------------
            */
            $user = Auth::user();

            $nurseName = trim(
                ($user->lastname ?? '') . ', ' .
                ($user->firstname ?? '')
            );

            if ($nurseName === ',' || $nurseName === '') {
                $nurseName = $user->name
                    ?? $user->user_cd
                    ?? 'Nurse';
            }

            /*
            |--------------------------------------------------------------------------
            | Company information
            |--------------------------------------------------------------------------
            */
            $companyName = config('company.comp_nm')
                ?? 'ABC Healthcare Services';

            $companyAddressParts = array_filter([
                config('company.complc_addr1'),
                config('company.complc_city'),
                config('company.complc_st'),
                config('company.complc_pin'),
            ]);

            $companyAddress = implode(' ', $companyAddressParts);

            $companyPhone = formatPhone(
                config('company.complc_ph') ?? ''
            );

            $companyFax = formatPhone(
                config('company.complc_fax') ?? ''
            );

            $phoneFaxParts = array_filter([
                $companyPhone !== '' ? 'Phone: ' . $companyPhone : null,
                $companyFax !== '' ? 'Fax: ' . $companyFax : null,
            ]);

            $phoneFaxLine = implode(' | ', $phoneFaxParts);

            /*
            |--------------------------------------------------------------------------
            | Build PDF HTML
            |--------------------------------------------------------------------------
            */
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">

                <style>
                    body {
                        font-family: DejaVu Sans, sans-serif;
                        font-size: 9px;
                        color: #000;
                        font-weight: normal;
                    }

                    .header {
                        text-align: center;
                        margin-bottom: 8px;
                        border-bottom: 2px solid #000;
                        padding-bottom: 6px;
                    }

                    .header h1 {
                        font-size: 17px;
                        margin: 0 0 3px 0;
                        color: #000;
                        font-weight: bold;
                    }

                    .header p {
                        font-size: 9px;
                        margin: 1px 0;
                        color: #000;
                    }

                    .report-title {
                        text-align: center;
                        font-size: 14px;
                        font-weight: bold;
                        margin: 7px 0 8px 0;
                        color: #000;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 8.5px;
                        table-layout: fixed;
                        border: 1.2px solid #000;
                    }

                    thead {
                        display: table-header-group;
                    }

                    th {
                        background-color: #e6e6e6;
                        color: #000;
                        border: 1.2px solid #000;
                        padding: 4px 2px;
                        text-align: center;
                        font-weight: bold;
                        word-wrap: break-word;
                    }

                    td {
                        border: 1px solid #000;
                        padding: 4px 2px;
                        word-wrap: break-word;
                        color: #000;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    .text-center {
                        text-align: center;
                    }

                    .name-col {
                        text-align: left;
                    }
                </style>
            </head>

            <body>

                <div class="header">
                    <h1>' . e($companyName) . '</h1>';

            if ($companyAddress !== '') {
                $html .= '<p>' . e($companyAddress) . '</p>';
            }

            if ($phoneFaxLine !== '') {
                $html .= '<p>' . e($phoneFaxLine) . '</p>';
            }

            $html .= '
                </div>

                <div class="report-title">
                    Daily Vitals Report -
                    ' . e($shiftTitle) . ' -
                    ' . e($selectedDate->format('m/d/Y')) . '
                </div>

                <table>
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="14%">Last Name</th>
                            <th width="14%">First Name</th>
                            <th width="10%">BP Max / SYS</th>
                            <th width="10%">BP Min / DIA</th>
                            <th width="10%">Sugar</th>
                            <th width="10%">Pulse</th>
                            <th width="10%">Weight</th>
                            <th width="10%">Temp</th>
                            <th width="7%">Initials</th>
                        </tr>
                    </thead>

                    <tbody>';

            /*
            |--------------------------------------------------------------------------
            | Preserve existing behavior
            |--------------------------------------------------------------------------
            | Show every member in the selected shift. Members with no vitals for the
            | selected date will have blank cells.
            |--------------------------------------------------------------------------
            */
            foreach ($members as $member) {
                $pid = trim((string) $member->ParticipantId);
                $v = $existingVitals->get($pid);

                $formattedParticipantId = ctype_digit($pid)
                    ? str_pad($pid, 3, '0', STR_PAD_LEFT)
                    : $pid;

                $html .= '<tr>';

                $html .= '<td class="text-center">' .
                    e($formattedParticipantId) .
                    '</td>';

                $html .= '<td class="name-col">' .
                    e($member->LastName ?? '') .
                    '</td>';

                $html .= '<td class="name-col">' .
                    e($member->FirstName ?? '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    e($v->HighBP ?? '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    e($v->LowBP ?? '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    e($v->Sugar ?? '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    e($v->Pulse ?? '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    e($v->Weight ?? '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    e($v->Temperature ?? '') .
                    '</td>';

                $html .= '<td class="text-center">&nbsp;</td>';

                $html .= '</tr>';
            }

            $html .= '
                    </tbody>
                </table>

            </body>
            </html>';

            /*
            |--------------------------------------------------------------------------
            | Footer
            |--------------------------------------------------------------------------
            */
            $footerHtml = '
                <table width="100%"
                    style="
                        font-family: DejaVu Sans, sans-serif;
                        font-size: 9px;
                        border-top: 1.2px solid #000;
                        padding-top: 4px;
                        color:#000;
                    ">

                    <tr>
                        <td width="28%" style="border:none; color:#000;">
                            <strong>Entered By:</strong><br>
                            ' . e($nurseName) . '
                        </td>

                        <td width="34%" style="border:none; color:#000;">
                            <strong>Nurse Signature:</strong><br>

                            <div style="
                                border-bottom:1.2px solid #000;
                                height:16px;
                                width:190px;
                            "></div>
                        </td>

                        <td width="25%" style="border:none; color:#000;">
                            <strong>Signed Date:</strong><br>

                            <div style="
                                border-bottom:1.2px solid #000;
                                height:16px;
                                width:120px;
                            "></div>
                        </td>

                        <td width="13%"
                            style="
                                border:none;
                                text-align:right;
                                color:#000;
                            ">
                            Page {PAGENO} of {nbpg}
                        </td>
                    </tr>
                </table>';

            /*
            |--------------------------------------------------------------------------
            | Generate PDF
            |--------------------------------------------------------------------------
            */
            $mpdf = new Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4-L',
                'margin_left'   => 6,
                'margin_right'  => 6,
                'margin_top'    => 6,
                'margin_bottom' => 22,
                'margin_footer' => 5,
                'tempDir'       => storage_path('app/mpdf-temp'),
            ]);

            $mpdf->SetTitle(
                'Daily Vitals Report - ' .
                $shiftTitle . ' - ' .
                $dateInput
            );

            $mpdf->SetHTMLFooter($footerHtml);
            $mpdf->WriteHTML($html);

            $filename = 'daily_vitals_' .
                strtolower($type) . '_' .
                $selectedDate->format('Y_m_d') . '.pdf';

            return response($mpdf->Output($filename, 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header(
                    'Content-Disposition',
                    'inline; filename="' . $filename . '"'
                );

        } catch (\Exception $e) {
            return redirect()
                ->route('nursing.reports.daily-vitals', [
                    'type'       => $type,
                    'date'       => $dateInput,
                    'sort_by'    => $sortBy,
                    'sort_order' => $sortOrder,
                ])
                ->with(
                    'warning',
                    'Unable to generate the Daily Vitals Report: ' .
                    $e->getMessage()
                );
        }
    } // End Method
   public function latestVitalsEntry(Request $request)
    {
        try {
            $request->validate([
                'entry_date' => ['required'],
            ]);

            $type = strtoupper($request->input('type', 'A'));

            if (!in_array($type, ['A', 'N', 'G'], true)) {
                $type = 'A';
            }

            $selectedDate = \Carbon\Carbon::createFromFormat('m/d/Y', $request->entry_date)->startOfDay();
            $selectedDateYmd = $selectedDate->format('Y-m-d');

            $records = DB::table('partbp as p')
                ->join('members as m', function ($join) {
                    $join->on(
                        DB::raw('TRIM(p.ParticipantId)'),
                        '=',
                        DB::raw('TRIM(m.ParticipantId)')
                    );
                })
                ->whereDate('p.CalenderDate', $selectedDateYmd)
                ->where('m.Trans_flag', $type)
                ->select(
                    DB::raw('TRIM(p.ParticipantId) as ParticipantId'),
                    'p.LowBP',
                    'p.HighBP',
                    'p.Sugar',
                    'p.Pulse',
                    'p.Temperature',
                    'p.Weight',
                    'p.BathingYN',
                    'p.ToiletYN',
                    'p.TransferYN',
                    'p.LocomotionYN',
                    'p.EatingYN',
                    'p.user_cd',
                    'p.update_dt'
                )
                ->get()
                ->keyBy(function ($row) {
                    return trim($row->ParticipantId);
                });

            return response()->json([
                'success' => true,
                'records' => $records,
                'server_time' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

    } // End Method

    public function saveAdlEntry(Request $request)
    {
        $request->validate([
            'entry_date' => ['required'],
            'adl'        => ['required', 'array'],
        ]);

        try {
            $selectedDate = \Carbon\Carbon::createFromFormat('m/d/Y', $request->entry_date)->startOfDay();
            $selectedDateYmd = $selectedDate->format('Y-m-d');
            $selectedDateTime = $selectedDate->format('Y-m-d H:i:s');

            if ($selectedDate->gt(\Carbon\Carbon::today())) {
                return back()
                    ->withInput()
                    ->with('error', 'Future date is not allowed.');
            }

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Invalid date format. Please use MM/DD/YYYY.');
        }

        $adlRows = $request->input('adl', []);

        $type = strtoupper($request->input('type', 'A'));

        if (!in_array($type, ['A', 'N', 'G'], true)) {
            $type = 'A';
        }

        DB::beginTransaction();

        try {
            $now = now();

            $userCd = Auth::user()->user_cd
                ?? Auth::user()->id
                ?? null;

            $companyId = session('company_id') ?? 'DS02';

            /*
            |--------------------------------------------------------------------------
            | Lock SerialNo while saving
            |--------------------------------------------------------------------------
            */
            $lastSerialNo = DB::table('partbp')
                ->orderByDesc('SerialNo')
                ->lockForUpdate()
                ->value('SerialNo');

            $nextSerialNo = ((int) $lastSerialNo) + 1;

            $conflictMembers = [];
            $savedCount = 0;

            foreach ($adlRows as $participantId => $row) {
                $participantId = trim($participantId);

                if ($participantId === '') {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Checkbox rule
                |--------------------------------------------------------------------------
                | Checked   = Y
                | Unchecked = N
                |
                | For ADL, we save all rows because all N values may be intentional.
                */
                $bathingYN    = isset($row['BathingYN']) ? 'Y' : 'N';
                $toiletYN     = isset($row['ToiletYN']) ? 'Y' : 'N';
                $transferYN   = isset($row['TransferYN']) ? 'Y' : 'N';
                $locomotionYN = isset($row['LocomotionYN']) ? 'Y' : 'N';
                $eatingYN     = isset($row['EatingYN']) ? 'Y' : 'N';

                $postedUpdateDt = trim((string) ($row['original_update_dt'] ?? ''));

                /*
                |--------------------------------------------------------------------------
                | Get current database row and lock it during save
                |--------------------------------------------------------------------------
                */
                $currentRow = DB::table('partbp')
                    ->where('ParticipantId', $participantId)
                    ->where('CalenderDate', $selectedDateTime)
                    ->lockForUpdate()
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Concurrency check
                |--------------------------------------------------------------------------
                */
                if ($currentRow) {
                    $dbUpdateDt = !empty($currentRow->update_dt)
                        ? \Carbon\Carbon::parse($currentRow->update_dt)->format('Y-m-d H:i:s')
                        : '';

                    $screenUpdateDt = $postedUpdateDt !== ''
                        ? \Carbon\Carbon::parse($postedUpdateDt)->format('Y-m-d H:i:s')
                        : '';

                    if ($dbUpdateDt !== $screenUpdateDt) {
                        $conflictMembers[] = $participantId;
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Update existing row - ADL fields only
                    |--------------------------------------------------------------------------
                    | Do not update Vitals fields here.
                    */
                    DB::table('partbp')
                        ->where('ParticipantId', $participantId)
                        ->where('CalenderDate', $selectedDateTime)
                        ->update([
                            'trans_flag'    => $type,

                            'BathingYN'     => $bathingYN,
                            'ToiletYN'      => $toiletYN,
                            'TransferYN'    => $transferYN,
                            'LocomotionYN'  => $locomotionYN,
                            'EatingYN'      => $eatingYN,

                            'user_cd'       => $userCd,
                            'update_dt'     => $now,
                            'update_flag'   => 'Y',
                            'company_id'    => $companyId,
                        ]);

                    $savedCount++;
                } else {
                    /*
                    |--------------------------------------------------------------------------
                    | Insert new row
                    |--------------------------------------------------------------------------
                    */
                    DB::table('partbp')->insert([
                        'SerialNo'      => $nextSerialNo++,
                        'ParticipantId' => $participantId,
                        'CalenderDate'  => $selectedDateTime,
                        'trans_flag'    => $type,

                        'BathingYN'     => $bathingYN,
                        'ToiletYN'      => $toiletYN,
                        'TransferYN'    => $transferYN,
                        'LocomotionYN'  => $locomotionYN,
                        'EatingYN'      => $eatingYN,

                        'user_cd'       => $userCd,
                        'update_dt'     => $now,
                        'update_flag'   => 'Y',
                        'company_id'    => $companyId,
                    ]);

                    $savedCount++;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | If conflict found, rollback everything
            |--------------------------------------------------------------------------
            */
            if (!empty($conflictMembers)) {
                DB::rollBack();

                return redirect()
                    ->route('nursing.adl.entry', [
                        'type'       => $type,
                        'entry_date' => $request->entry_date,
                    ])
                    ->with(
                        'error',
                        'Some records were updated by another user. Screen has been reloaded. Please review and save again. Member ID(s): '
                        . implode(', ', $conflictMembers)
                    );
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Reload same screen after save
            |--------------------------------------------------------------------------
            */
            return redirect()
                ->route('nursing.adl.entry', [
                    'type'       => $type,
                    'entry_date' => $request->entry_date,
                ])
                ->with('success', 'ADL saved successfully. Screen reloaded with latest records.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('nursing.adl.entry', [
                    'type'       => $type,
                    'entry_date' => $request->entry_date,
                ])
                ->withInput()
                ->with('error', 'Future date is not allowed.');
                    }

    } // End Method

    public function adlEntry(Request $request, string $type = 'A')
    {
        $type = in_array(strtoupper($type), ['A', 'N', 'G'], true) ? strtoupper($type) : 'A';

        $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
            default => 'Morning Shift',
        };

        $selectedDateInput = $request->input('entry_date');
        $selectedDateYmd = null;

        $members = collect();
        $existingVitals = collect();

        if ($selectedDateInput) {
            try {
                $selectedDate = \Carbon\Carbon::createFromFormat('m/d/Y', $selectedDateInput)->startOfDay();
                $selectedDateYmd = $selectedDate->format('Y-m-d');

                if ($selectedDate->gt(\Carbon\Carbon::today())) {
                    return redirect()
                        ->route('nursing.adl.entry', ['type' => $type])
                        ->with('error', 'Future date is not allowed.');
                }

                $members = DB::table('members')
                    ->select(
                        DB::raw('TRIM(ParticipantId) as ParticipantId'),
                        DB::raw('TRIM(LastName) as LastName'),
                        DB::raw('TRIM(FirstName) as FirstName'),

                        // Default ADL needs from members table - read only display
                        'bathing_yn',
                        'toilet_yn',
                        'transfer_yn',
                        'locomotion_yn',
                        'eating_yn'
                    )
                    ->where('Trans_flag', $type)
                    ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
                    ->get();

                $existingVitals = DB::table('partbp')
                    ->whereDate('CalenderDate', $selectedDateYmd)
                    ->get()
                    ->keyBy(function ($row) {
                        return trim($row->ParticipantId);
                    });

            } catch (\Exception $e) {
                return redirect()
                ->route('nursing.adl.entry', ['type' => $type])
                ->with('error', 'Invalid date format. Please use MM/DD/YYYY.');
            }
        }

        $companyRecord = DB::table('gnmscomp')->first();
        $adlBathingLabel = $companyRecord->adl_bathing_label ?? 'Bathing';

        return view('nursing.adl_entry', compact(
            'type',
            'shiftTitle',
            'selectedDateInput',
            'selectedDateYmd',
            'members',
            'existingVitals',
            'adlBathingLabel'
        ));

    } // End Method

    public function exportDailyAdlPdf(Request $request, string $type = 'A')
    {
        /*
        |--------------------------------------------------------------------------
        | Validate selected member category
        |--------------------------------------------------------------------------
        */
        $type = strtoupper(trim($type));

        if (!in_array($type, ['A', 'N', 'G'], true)) {
            $type = 'A';
        }

        $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
        };

        /*
        |--------------------------------------------------------------------------
        | Validate report parameters
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'date'       => ['required', 'date_format:m/d/Y'],
            'sort_by'    => ['nullable', 'in:ParticipantId,LastName,FirstName'],
            'sort_order' => ['nullable', 'in:asc,desc'],
        ], [
            'date.required'    => 'Please select a report date.',
            'date.date_format' => 'Please enter the date in MM/DD/YYYY format.',
        ]);

        $dateInput = $validated['date'];
        $sortBy = $validated['sort_by'] ?? 'ParticipantId';
        $sortOrder = strtolower($validated['sort_order'] ?? 'asc');

        try {
            $selectedDate = Carbon::createFromFormat('m/d/Y', $dateInput)
                ->startOfDay();

            /*
            |--------------------------------------------------------------------------
            | Make sure Carbon did not silently adjust an invalid date
            |--------------------------------------------------------------------------
            */
            if ($selectedDate->format('m/d/Y') !== $dateInput) {
                throw new \Exception('Invalid date.');
            }

            if ($selectedDate->gt(Carbon::today())) {
                return redirect()
                    ->route('nursing.reports.daily-adl', [
                        'type'       => $type,
                        'date'       => $dateInput,
                        'sort_by'    => $sortBy,
                        'sort_order' => $sortOrder,
                    ])
                    ->with(
                        'warning',
                        'Future dates are not allowed.'
                    );
            }

            $selectedDateYmd = $selectedDate->format('Y-m-d');

            /*
            |--------------------------------------------------------------------------
            | Load members for selected category
            |--------------------------------------------------------------------------
            */
            $memberQuery = DB::table('members')
                ->select(
                    DB::raw('TRIM(ParticipantId) as ParticipantId'),
                    DB::raw('TRIM(LastName) as LastName'),
                    DB::raw('TRIM(FirstName) as FirstName')
                )
                ->where('Trans_flag', $type);

            if ($sortBy === 'LastName') {
                $memberQuery
                    ->orderBy('LastName', $sortOrder)
                    ->orderBy('FirstName', 'asc');
            } elseif ($sortBy === 'FirstName') {
                $memberQuery
                    ->orderBy('FirstName', $sortOrder)
                    ->orderBy('LastName', 'asc');
            } else {
                $memberQuery->orderByRaw(
                    'CAST(ParticipantId AS UNSIGNED) ' . $sortOrder
                );
            }

            $members = $memberQuery->get();

            if ($members->isEmpty()) {
                return redirect()
                    ->route('nursing.reports.daily-adl', [
                        'type'       => $type,
                        'date'       => $dateInput,
                        'sort_by'    => $sortBy,
                        'sort_order' => $sortOrder,
                    ])
                    ->with(
                        'warning',
                        'No members were found for ' . $shiftTitle . '.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Participant IDs for selected category
            |--------------------------------------------------------------------------
            */
            $participantIds = $members
                ->pluck('ParticipantId')
                ->map(function ($participantId) {
                    return trim((string) $participantId);
                })
                ->filter()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Load saved ADL records
            |--------------------------------------------------------------------------
            | A saved ADL record with all N values is still a valid record.
            | Therefore, we check for non-null ADL fields rather than requiring Y.
            |--------------------------------------------------------------------------
            */
            $existingAdl = DB::table('partbp')
                ->whereDate('CalenderDate', $selectedDateYmd)
                ->whereIn(
                    DB::raw('TRIM(ParticipantId)'),
                    $participantIds->all()
                )
                ->where(function ($query) {
                    $query
                        ->whereNotNull('BathingYN')
                        ->orWhereNotNull('ToiletYN')
                        ->orWhereNotNull('TransferYN')
                        ->orWhereNotNull('LocomotionYN')
                        ->orWhereNotNull('EatingYN');
                })
                ->get()
                ->keyBy(function ($row) {
                    return trim((string) $row->ParticipantId);
                });

            /*
            |--------------------------------------------------------------------------
            | Stop when no ADL records exist
            |--------------------------------------------------------------------------
            */
            if ($existingAdl->isEmpty()) {
                return redirect()
                    ->route('nursing.reports.daily-adl', [
                        'type'       => $type,
                        'date'       => $dateInput,
                        'sort_by'    => $sortBy,
                        'sort_order' => $sortOrder,
                    ])
                    ->with(
                        'warning',
                        'There are no daily ADL records for ' .
                        $shiftTitle . ' for ' .
                        $selectedDate->format('m/d/Y') . '.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Nurse information
            |--------------------------------------------------------------------------
            */
            $user = Auth::user();

            $nurseName = trim(
                ($user->lastname ?? '') . ', ' .
                ($user->firstname ?? '')
            );

            if ($nurseName === ',' || $nurseName === '') {
                $nurseName = $user->name
                    ?? $user->user_cd
                    ?? 'Nurse';
            }

            $companyRecord = DB::table('gnmscomp')->first();
            $adlBathingLabel = $companyRecord->adl_bathing_label ?? 'Bathing';
            
            /*
            |--------------------------------------------------------------------------
            | Company information
            |--------------------------------------------------------------------------
            */
            $companyName = config('company.comp_nm')
                ?? 'ABC Healthcare Services';

            $companyAddressParts = array_filter([
                config('company.complc_addr1'),
                config('company.complc_city'),
                config('company.complc_st'),
                config('company.complc_pin'),
            ]);

            $companyAddress = implode(' ', $companyAddressParts);

            $companyPhone = formatPhone(
                config('company.complc_ph') ?? ''
            );

            $companyFax = formatPhone(
                config('company.complc_fax') ?? ''
            );

            $phoneFaxParts = array_filter([
                $companyPhone !== ''
                    ? 'Phone: ' . $companyPhone
                    : null,

                $companyFax !== ''
                    ? 'Fax: ' . $companyFax
                    : null,
            ]);

            $phoneFaxLine = implode(' | ', $phoneFaxParts);

            $checkMark = '<span style="font-size:11px; font-weight:bold;">&#10003;</span>';

            /*
            |--------------------------------------------------------------------------
            | Build PDF HTML
            |--------------------------------------------------------------------------
            */
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">

                <style>
                    body {
                        font-family: DejaVu Sans, sans-serif;
                        font-size: 9px;
                        color: #000;
                        font-weight: normal;
                    }

                    .header {
                        text-align: center;
                        margin-bottom: 8px;
                        border-bottom: 2px solid #000;
                        padding-bottom: 6px;
                    }

                    .header h1 {
                        font-size: 17px;
                        margin: 0 0 3px 0;
                        color: #000;
                        font-weight: bold;
                    }

                    .header p {
                        font-size: 9px;
                        margin: 1px 0;
                        color: #000;
                    }

                    .report-title {
                        text-align: center;
                        font-size: 14px;
                        font-weight: bold;
                        margin: 7px 0 8px 0;
                        color: #000;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 8.5px;
                        table-layout: fixed;
                        border: 1.2px solid #000;
                    }

                    thead {
                        display: table-header-group;
                    }

                    th {
                        background-color: #e6e6e6;
                        color: #000;
                        border: 1.2px solid #000;
                        padding: 4px 2px;
                        text-align: center;
                        font-weight: bold;
                        word-wrap: break-word;
                    }

                    td {
                        border: 1px solid #000;
                        padding: 4px 2px;
                        word-wrap: break-word;
                        color: #000;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    .text-center {
                        text-align: center;
                    }

                    .name-col {
                        text-align: left;
                    }
                </style>
            </head>

            <body>

                <div class="header">
                    <h1>' . e($companyName) . '</h1>';

            if ($companyAddress !== '') {
                $html .= '<p>' . e($companyAddress) . '</p>';
            }

            if ($phoneFaxLine !== '') {
                $html .= '<p>' . e($phoneFaxLine) . '</p>';
            }

            $html .= '
                </div>

                <div class="report-title">
                    Daily ADL Report -
                    ' . e($shiftTitle) . ' -
                    ' . e($selectedDate->format('m/d/Y')) . '
                </div>

                <table>
                    <thead>
                        <tr>
                            <th width="6%">ID</th>
                            <th width="18%">Last Name</th>
                            <th width="18%">First Name</th>
                            <th width="12%">Dressing</th>
                            <th width="10%">Toilet</th>
                            <th width="11%">Transfer</th>
                            <th width="12%">Ambulation</th>
                            <th width="10%">Eating</th>
                            <th width="3%">Initials</th>
                        </tr>
                    </thead>

                    <tbody>';

            /*
            |--------------------------------------------------------------------------
            | Show all members in the selected category
            |--------------------------------------------------------------------------
            | Members without saved ADL for the selected date will display blank cells.
            |--------------------------------------------------------------------------
            */
            foreach ($members as $member) {
                $pid = trim((string) $member->ParticipantId);
                $v = $existingAdl->get($pid);

                $formattedParticipantId = ctype_digit($pid)
                    ? str_pad($pid, 3, '0', STR_PAD_LEFT)
                    : $pid;

                $html .= '<tr>';

                $html .= '<td class="text-center">' .
                    e($formattedParticipantId) .
                    '</td>';

                $html .= '<td class="name-col">' .
                    e($member->LastName ?? '') .
                    '</td>';

                $html .= '<td class="name-col">' .
                    e($member->FirstName ?? '') .
                    '</td>';

                /*
                |--------------------------------------------------------------------------
                | Dressing is stored in BathingYN
                |--------------------------------------------------------------------------
                */
                $html .= '<td class="text-center">' .
                    (($v->BathingYN ?? 'N') === 'Y'
                        ? $checkMark
                        : '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    (($v->ToiletYN ?? 'N') === 'Y'
                        ? $checkMark
                        : '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    (($v->TransferYN ?? 'N') === 'Y'
                        ? $checkMark
                        : '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    (($v->LocomotionYN ?? 'N') === 'Y'
                        ? $checkMark
                        : '') .
                    '</td>';

                $html .= '<td class="text-center">' .
                    (($v->EatingYN ?? 'N') === 'Y'
                        ? $checkMark
                        : '') .
                    '</td>';

                $html .= '<td class="text-center">&nbsp;</td>';

                $html .= '</tr>';
            }

            $html .= '
                    </tbody>
                </table>

            </body>
            </html>';

            /*
            |--------------------------------------------------------------------------
            | PDF footer
            |--------------------------------------------------------------------------
            */
            $footerHtml = '
                <table width="100%"
                    style="
                        font-family: DejaVu Sans, sans-serif;
                        font-size: 9px;
                        border-top: 1.2px solid #000;
                        padding-top: 4px;
                        color:#000;
                    ">

                    <tr>
                        <td width="28%" style="border:none; color:#000;">
                            <strong>Entered By:</strong><br>
                            ' . e($nurseName) . '
                        </td>

                        <td width="34%" style="border:none; color:#000;">
                            <strong>Nurse Signature:</strong><br>

                            <div style="
                                border-bottom:1.2px solid #000;
                                height:16px;
                                width:190px;
                            "></div>
                        </td>

                        <td width="25%" style="border:none; color:#000;">
                            <strong>Signed Date:</strong><br>

                            <div style="
                                border-bottom:1.2px solid #000;
                                height:16px;
                                width:120px;
                            "></div>
                        </td>

                        <td width="13%"
                            style="
                                border:none;
                                text-align:right;
                                color:#000;
                            ">
                            Page {PAGENO} of {nbpg}
                        </td>
                    </tr>
                </table>';

            /*
            |--------------------------------------------------------------------------
            | Generate PDF
            |--------------------------------------------------------------------------
            */
            $mpdf = new Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4-L',
                'margin_left'   => 9,
                'margin_right'  => 4,
                'margin_top'    => 6,
                'margin_bottom' => 22,
                'margin_footer' => 5,
                'tempDir'       => storage_path('app/mpdf-temp'),
            ]);

            $mpdf->SetTitle(
                'Daily ADL Report - ' .
                $shiftTitle . ' - ' .
                $dateInput
            );

            $mpdf->SetHTMLFooter($footerHtml);
            $mpdf->WriteHTML($html);

            $filename = 'daily_adl_' .
                strtolower($type) . '_' .
                $selectedDate->format('Y_m_d') . '.pdf';

            return response($mpdf->Output($filename, 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header(
                    'Content-Disposition',
                    'inline; filename="' . $filename . '"'
                );

        } catch (\Exception $e) {
            return redirect()
                ->route('nursing.reports.daily-adl', [
                    'type'       => $type,
                    'date'       => $dateInput,
                    'sort_by'    => $sortBy,
                    'sort_order' => $sortOrder,
                ])
                ->with(
                    'warning',
                    'Unable to generate the Daily ADL Report: ' .
                    $e->getMessage()
                );
        }
    } // End Method
    public function nursingComments()
    {
        return view('nursing.nursing_comments');

    } // End Method

    public function nursingCommentMembers($shift)
    {
        if (!in_array($shift, ['A', 'N'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid shift selected.',
                'members' => [],
            ], 422);
        }

        $members = DB::table('members')
            ->where('Trans_flag', $shift)
            ->orderBy('ParticipantId')
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName',
            ])
            ->map(function ($member) {
                return [
                    'id' => $member->ParticipantId,
                    'text' => str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT)
                        . ' - '
                        . trim($member->LastName . ', ' . $member->FirstName),
                ];
            });

        return response()->json([
            'success' => true,
            'members' => $members,
        ]);

    } // End Method

    public function loadNursingComments(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'shift' => 'required|in:A,N',
        ]);

        $comment = DB::table('nursing_comments')
            ->where('ParticipantId', $request->participant_id)
            ->where('Trans_flag', $request->shift)
            ->first();

        return response()->json([
            'success' => true,
            'exists' => $comment ? true : false,
            'comments' => $comment->comments ?? '',
        ]);

    } // End Method

    public function saveNursingComments(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'shift' => 'required|in:A,N',
            'comments' => 'nullable|string|max:5000',
        ]);

        $participantId = $request->participant_id;
        $shift = $request->shift;
        $comments = $request->comments;
        $userCd = Auth::user()->user_cd
                ?? Auth::user()->id
                ?? null;

        $existing = DB::table('nursing_comments')
            ->where('ParticipantId', $participantId)
            ->where('Trans_flag', $shift)
            ->first();

        if ($existing) {
            DB::table('nursing_comments')
                ->where('id', $existing->id)
                ->update([
                    'comments' => $comments,
                    'updated_by' => $userCd,
                    'updated_at' => now(),
                ]);

            $message = 'Nursing comments updated successfully.';
        } else {
            DB::table('nursing_comments')
                ->insert([
                    'ParticipantId' => $participantId,
                    'Trans_flag' => $shift,
                    'comments' => $comments,
                    'created_by' => $userCd,
                    'updated_by' => $userCd,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $message = 'Nursing comments saved successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);

    } // End Method

    public function exportNursingCommentsPdf(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'shift' => 'required|in:A,N',
        ]);

        $participantId = $request->participant_id;
        $shift = $request->shift;

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first([
                'ParticipantId',
                'LastName',
                'FirstName',
                'Trans_flag',
            ]);

        if (!$member) {
            abort(404, 'Member not found.');
        }

        $comment = DB::table('nursing_comments')
            ->where('ParticipantId', $participantId)
            ->where('Trans_flag', $shift)
            ->first();

        $shiftName = $shift === 'A' ? 'Morning Shift' : 'Noon Shift';

        $html = view('nursing.nursing_comments_pdf', [
            'member' => $member,
            'comment' => $comment,
            'shift' => $shift,
            'shiftName' => $shiftName,
            'printedAt' => now()->format('m/d/Y h:i A'),
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'nursing-comments-'
            . str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT)
            . '-'
            . $shift
            . '.pdf';

        return response($mpdf->Output($fileName, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');

    } // End Method

    public function nursing90DaySummary(Request $request, string $type = 'A')
    {
        $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName',
                'DateOfBirth',
            ]);

            $previousSummaries = collect();

        return view('nursing.nursing_90day_summary', compact('type', 'members', 'previousSummaries'));

    } // End Method

    public function loadNursing90DaySummary(Request $request, string $type = 'A')
    {
        $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

        $request->validate([
            'participant_id'  => 'required',
            'assessment_date' => 'required',
        ]);

        $participantId = $request->participant_id;

        $assessmentDate = $this->parseUsDateOrNull($request->assessment_date);

        if (!$assessmentDate) {
            return redirect()
                ->route('nursing.90day.summary', $type)
                ->with('error', 'Please enter a valid Assessment Date in mm/dd/yyyy format.');
        }

        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName',
                'DateOfBirth',
            ]);

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first([
                'ParticipantId',
                'LastName',
                'FirstName',
                'DateOfBirth',
                'Trans_flag',
            ]);

        if (!$member) {
            return redirect()
                ->route('nursing.90day.summary', $type)
                ->with('error', 'Selected member was not found.');
        }

        $assessment = DB::table('nursing_90day_summaries')
            ->where('ParticipantId', $participantId)
            ->where('assessment_date', $assessmentDate)
            ->where('Trans_flag', $type)
            ->first();

        $previousSummaries = DB::table('nursing_90day_summaries')
            ->where('ParticipantId', $participantId)
            ->where('Trans_flag', $type)
            ->orderByDesc('assessment_date')
            ->get([
                'id',
                'ParticipantId',
                'assessment_date',
                'Trans_flag',
                'patient_name',
                'signature',
                'created_at',
                'updated_at',
            ]);

        return view('nursing.nursing_90day_summary', compact(
            'type',
            'members',
            'member',
            'assessment',
            'assessmentDate',
            'previousSummaries'

        ));

    } // End Method

    private function yn(Request $request, string $field): string
    {
        return $request->has($field) ? 'Y' : 'N';

    } // End Method

    public function saveNursing90DaySummary(Request $request)
    {
        $request->validate([
            'participant_id'  => 'required',
            'assessment_date' => 'required',
            'Trans_flag'      => 'required|in:A,N',
        ]);

        $type = strtoupper($request->Trans_flag);

        $assessmentDate = $this->parseUsDateOrNull($request->assessment_date);

        if (!$assessmentDate) {
            return back()
                ->withInput()
                ->with('error', 'Please enter a valid Assessment Date in mm/dd/yyyy format.');
        }

        $participantId = $request->participant_id;

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return back()->withInput()->with('error', 'Selected member was not found.');
        }

        $patientName = trim(($member->LastName ?? '') . ', ' . ($member->FirstName ?? ''));
        $dob = null;

        if (!empty($member->DateOfBirth)) {
            try {
                $dob = Carbon::parse($member->DateOfBirth)->format('Y-m-d');
            } catch (\Exception $e) {
                $dob = null;
            }
        }

        $data = [
            'ParticipantId'   => $participantId,
            'assessment_date' => $assessmentDate,
            'Trans_flag'      => $type,
            'patient_name'    => $patientName,
            'dob'             => $dob,

            'mobility_ambulatory' => $this->yn($request, 'mobility_ambulatory'),
            'mobility_wheelchair' => $this->yn($request, 'mobility_wheelchair'),
            'mobility_cane'       => $this->yn($request, 'mobility_cane'),
            'mobility_walker'     => $this->yn($request, 'mobility_walker'),
            'mobility_comments'   => $request->mobility_comments,

            'mental_alert'       => $this->yn($request, 'mental_alert'),
            'mental_oriented'    => $this->yn($request, 'mental_oriented'),
            'mental_confused'    => $this->yn($request, 'mental_confused'),
            'mental_disoriented' => $this->yn($request, 'mental_disoriented'),

            'mental_change_same'     => $this->yn($request, 'mental_change_same'),
            'mental_change_increase' => $this->yn($request, 'mental_change_increase'),
            'mental_change_decrease' => $this->yn($request, 'mental_change_decrease'),
            'mental_comments'        => $request->mental_comments,

            'edema'          => $this->yn($request, 'edema'),
            'edema_plus1'    => $this->yn($request, 'edema_plus1'),
            'edema_plus2'    => $this->yn($request, 'edema_plus2'),
            'edema_trace'    => $this->yn($request, 'edema_trace'),
            'edema_location' => $request->edema_location,

            'shortness_breath_yes'      => $this->yn($request, 'shortness_breath_yes'),
            'shortness_breath_no'       => $this->yn($request, 'shortness_breath_no'),
            'shortness_breath_exertion' => $this->yn($request, 'shortness_breath_exertion'),

            'visual_acuity'      => $request->visual_acuity,
            'wears_glasses_yes' => $this->yn($request, 'wears_glasses_yes'),
            'wears_glasses_no'  => $this->yn($request, 'wears_glasses_no'),

            'urinary_incontinence_yes' => $this->yn($request, 'urinary_incontinence_yes'),
            'urinary_incontinence_no'  => $this->yn($request, 'urinary_incontinence_no'),
            'bowel_incontinence_yes'   => $this->yn($request, 'bowel_incontinence_yes'),
            'bowel_incontinence_no'    => $this->yn($request, 'bowel_incontinence_no'),

            'skin'    => $request->skin,
            'bp_from' => $request->bp_from,
            'bp_to'   => $request->bp_to,

            'diabetic_yes' => $this->yn($request, 'diabetic_yes'),
            'diabetic_no'  => $this->yn($request, 'diabetic_no'),

            'hypoglycemic_yes' => $this->yn($request, 'hypoglycemic_yes'),
            'hypoglycemic_no'  => $this->yn($request, 'hypoglycemic_no'),

            'hyperglycemic_yes' => $this->yn($request, 'hyperglycemic_yes'),
            'hyperglycemic_no'  => $this->yn($request, 'hyperglycemic_no'),

            'medication_change_yes' => $this->yn($request, 'medication_change_yes'),
            'medication_change_no'  => $this->yn($request, 'medication_change_no'),

            'medication_reason_not_effective'     => $this->yn($request, 'medication_reason_not_effective'),
            'medication_reason_does_not_tolerate' => $this->yn($request, 'medication_reason_does_not_tolerate'),
            'medication_reason_md_order'          => $this->yn($request, 'medication_reason_md_order'),
            'medication_reason_patient_refused'   => $this->yn($request, 'medication_reason_patient_refused'),
            'medication_reason_other'             => $request->medication_reason_other,

            'meds_delivered_yes' => $this->yn($request, 'meds_delivered_yes'),
            'meds_delivered_no'  => $this->yn($request, 'meds_delivered_no'),

            'antidepressants' => $this->yn($request, 'antidepressants'),
            'antipsychotic'   => $this->yn($request, 'antipsychotic'),

            'side_effects_yes' => $this->yn($request, 'side_effects_yes'),
            'side_effects_no'  => $this->yn($request, 'side_effects_no'),

            'tx_good' => $this->yn($request, 'tx_good'),
            'tx_fair' => $this->yn($request, 'tx_fair'),
            'tx_poor' => $this->yn($request, 'tx_poor'),

            'medication_comments' => $request->medication_comments,

            'diet_standard_yes' => $this->yn($request, 'diet_standard_yes'),
            'diet_standard_no'  => $this->yn($request, 'diet_standard_no'),

            'diet_no_concentrated_sweets_yes' => $this->yn($request, 'diet_no_concentrated_sweets_yes'),
            'diet_no_concentrated_sweets_no'  => $this->yn($request, 'diet_no_concentrated_sweets_no'),

            'diet_other'     => $request->diet_other,
            'diet_other_yes' => $this->yn($request, 'diet_other_yes'),
            'diet_other_no'  => $this->yn($request, 'diet_other_no'),

            'diet_comments' => $request->diet_comments,

            'pain_neck'       => $this->yn($request, 'pain_neck'),
            'pain_upper_back' => $this->yn($request, 'pain_upper_back'),
            'pain_low_back'   => $this->yn($request, 'pain_low_back'),
            'pain_shoulder'   => $this->yn($request, 'pain_shoulder'),
            'pain_elbow'      => $this->yn($request, 'pain_elbow'),
            'pain_wrist'      => $this->yn($request, 'pain_wrist'),
            'pain_hip'        => $this->yn($request, 'pain_hip'),
            'pain_knee'       => $this->yn($request, 'pain_knee'),
            'pain_ankle'      => $this->yn($request, 'pain_ankle'),
            'pain_muscle'     => $this->yn($request, 'pain_muscle'),

            'pain_score'         => $request->pain_score,
            'pain_treatment_yes' => $this->yn($request, 'pain_treatment_yes'),
            'pain_treatment_no'  => $this->yn($request, 'pain_treatment_no'),

            'adl_dressing_level'      => $request->adl_dressing_level,
            'adl_toilet_use_level'    => $request->adl_toilet_use_level,
            'adl_ambulation_level'    => $request->adl_ambulation_level,
            'adl_locomotion_level'    => $request->adl_locomotion_level,
            'adl_transfer_level'      => $request->adl_transfer_level,
            'adl_eating_level'        => $request->adl_eating_level,

            'adl_dressing_provided_yes'   => $this->yn($request, 'adl_dressing_provided_yes'),
            'adl_dressing_provided_no'    => $this->yn($request, 'adl_dressing_provided_no'),
            'adl_toilet_use_provided_yes' => $this->yn($request, 'adl_toilet_use_provided_yes'),
            'adl_toilet_use_provided_no'  => $this->yn($request, 'adl_toilet_use_provided_no'),
            'adl_ambulation_provided_yes' => $this->yn($request, 'adl_ambulation_provided_yes'),
            'adl_ambulation_provided_no'  => $this->yn($request, 'adl_ambulation_provided_no'),
            'adl_locomotion_provided_yes' => $this->yn($request, 'adl_locomotion_provided_yes'),
            'adl_locomotion_provided_no'  => $this->yn($request, 'adl_locomotion_provided_no'),
            'adl_transfer_provided_yes'   => $this->yn($request, 'adl_transfer_provided_yes'),
            'adl_transfer_provided_no'    => $this->yn($request, 'adl_transfer_provided_no'),
            'adl_eating_provided_yes'     => $this->yn($request, 'adl_eating_provided_yes'),
            'adl_eating_provided_no'      => $this->yn($request, 'adl_eating_provided_no'),

            'clinic_podiatry'    => $this->yn($request, 'clinic_podiatry'),
            'clinic_optometry'   => $this->yn($request, 'clinic_optometry'),
            'clinic_pain_clinic' => $this->yn($request, 'clinic_pain_clinic'),
            'clinic_psychiatry'  => $this->yn($request, 'clinic_psychiatry'),
            'clinic_pt'          => $this->yn($request, 'clinic_pt'),
            'clinic_ot'          => $this->yn($request, 'clinic_ot'),
            'clinic_other'       => $request->clinic_other,

            'recent_hospitalization' => $request->recent_hospitalization,
            'body_picture_change'    => $request->body_picture_change,
            'long_note'              => $request->long_note,
            'signature'              => $request->signature,

            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ];

        $existing = DB::table('nursing_90day_summaries')
            ->where('ParticipantId', $participantId)
            ->where('assessment_date', $assessmentDate)
            ->where('Trans_flag', $type)
            ->first();

        if ($existing) {
            DB::table('nursing_90day_summaries')
                ->where('id', $existing->id)
                ->update($data);

            $id = $existing->id;
            $message = '90 Days Nursing Summary updated successfully.';
        } else {
            $data['created_by'] = Auth::id();
            $data['created_at'] = now();

            $id = DB::table('nursing_90day_summaries')->insertGetId($data);
            $message = '90 Days Nursing Summary saved successfully.';
        }

        return redirect()
            ->route('nursing.90day.summary.load', [
                'type'            => $type,
                'participant_id'  => $participantId,
                'assessment_date' => Carbon::parse($assessmentDate)->format('m/d/Y'),
            ])
            ->with('success', $message)
            ->with('saved_id', $id);

    } // End Method

    public function exportNursing90DaySummaryPdf($id)
    {
        $assessment = DB::table('nursing_90day_summaries')
            ->where('id', $id)
            ->first();

        if (!$assessment) {
            return back()->with('error', 'Assessment record not found.');
        }

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->ParticipantId)
            ->first();

        $html = view('nursing.nursing_90day_summary_pdf', compact(
            'assessment',
            'member'
        ))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'orientation' => 'P',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->SetTitle('90 Days Nursing Summary');
        $mpdf->WriteHTML($html);

        $fileName = '90-days-nursing-summary-' .
            $assessment->ParticipantId . '-' .
            Carbon::parse($assessment->assessment_date)->format('Ymd') .
            '.pdf';

        return response($mpdf->Output($fileName, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');

    } // End Method

    private function parseUsDateOrNull($date)
    {
        try {
            $parsed = \Carbon\Carbon::createFromFormat('m/d/Y', $date);

            if ($parsed->format('m/d/Y') !== $date) {
                return null;
            }

            if ($parsed->year < 1900 || $parsed->year > 2100) {
                return null;
            }

            return $parsed->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }

    } // End Method

    private function decodeJsonSection($value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];

    } // End Method

    public function nursingAssessmentSummary(Request $request, string $type = 'A')
    {
        $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName',
                'DateOfBirth',
            ]);

        $previousAssessments = collect();

        return view('nursing.nursing_assessment_summary', compact(
            'type',
            'members',
            'previousAssessments'
        ));

    } // End Method

    public function loadNursingAssessmentSummary(Request $request, string $type = 'A')
    {
        $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

        $request->validate([
            'participant_id'  => 'required',
            'assessment_date' => 'required',
        ]);

        $participantId = $request->participant_id;

        $assessmentDate = $this->parseUsDateOrNull($request->assessment_date);

        if (!$assessmentDate) {
            return redirect()
                ->route('nursing.assessment.summary', $type)
                ->with('error', 'Please enter a valid Assessment Date in mm/dd/yyyy format.');
        }

        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get([
                'ParticipantId',
                'LastName',
                'FirstName',
                'DateOfBirth',
            ]);

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first([
                'ParticipantId',
                'LastName',
                'FirstName',
                'DateOfBirth',
                'Trans_flag',
            ]);

        if (!$member) {
            return redirect()
                ->route('nursing.assessment.summary', $type)
                ->with('error', 'Selected member was not found.');
        }

        $assessment = DB::table('nursing_assessment_summaries')
            ->where('ParticipantId', $participantId)
            ->where('assessment_date', $assessmentDate)
            ->where('Trans_flag', $type)
            ->first();

        $previousAssessments = DB::table('nursing_assessment_summaries')
            ->where('ParticipantId', $participantId)
            ->where('Trans_flag', $type)
            ->orderByDesc('assessment_date')
            ->get([
                'id',
                'ParticipantId',
                'assessment_date',
                'Trans_flag',
                'patient_name',
                'assessment_type',
                'signature',
                'signature_date',
                'created_at',
                'updated_at',
            ]);

        $sections = [
            'past_medical_history'    => $this->decodeJsonSection($assessment->past_medical_history ?? null),
            'vital_hospitalization'   => $this->decodeJsonSection($assessment->vital_hospitalization ?? null),
            'functional_assessment'   => $this->decodeJsonSection($assessment->functional_assessment ?? null),
            'bowel_bladder'           => $this->decodeJsonSection($assessment->bowel_bladder ?? null),
            'mental_behavior_sleep'   => $this->decodeJsonSection($assessment->mental_behavior_sleep ?? null),
            'skilled_needs'           => $this->decodeJsonSection($assessment->skilled_needs ?? null),
            'skin_assessment'         => $this->decodeJsonSection($assessment->skin_assessment ?? null),
            'assistive_devices'       => $this->decodeJsonSection($assessment->assistive_devices ?? null),
            'nutritional_status'      => $this->decodeJsonSection($assessment->nutritional_status ?? null),
            'pain_assessment'         => $this->decodeJsonSection($assessment->pain_assessment ?? null),
        ];

        return view('nursing.nursing_assessment_summary', compact(
            'type',
            'members',
            'member',
            'assessment',
            'assessmentDate',
            'previousAssessments',
            'sections'
        ));

    } // End Method

    public function saveNursingAssessmentSummary(Request $request)
    {
        $request->validate([
            'participant_id'  => 'required',
            'assessment_date' => 'required',
            'Trans_flag'      => 'required|in:A,N',
        ]);

        $type = strtoupper($request->Trans_flag);

        $assessmentDate = $this->parseUsDateOrNull($request->assessment_date);

        if (!$assessmentDate) {
            return back()
                ->withInput()
                ->with('error', 'Please enter a valid Assessment Date in mm/dd/yyyy format.');
        }

        $signatureDate = null;

        if (!empty($request->signature_date)) {
            $signatureDate = $this->parseUsDateOrNull($request->signature_date);

            if (!$signatureDate) {
                return back()
                    ->withInput()
                    ->with('error', 'Please enter a valid Signature Date in mm/dd/yyyy format.');
            }
        }

        $participantId = $request->participant_id;

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return back()->withInput()->with('error', 'Selected member was not found.');
        }

        $patientName = trim(($member->LastName ?? '') . ', ' . ($member->FirstName ?? ''));

        $dob = null;
        if (!empty($member->DateOfBirth)) {
            try {
                $dob = Carbon::parse($member->DateOfBirth)->format('Y-m-d');
            } catch (\Exception $e) {
                $dob = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Page 1 - Past Medical History / Vitals / Hospitalization
        |--------------------------------------------------------------------------
        */
        $pastMedicalHistory = [
            'assessment_type' => $request->assessment_type,

            'source_neuro_participant'      => $this->yn($request, 'source_neuro_participant'),
            'source_neuro_medical_record'   => $this->yn($request, 'source_neuro_medical_record'),
            'source_neuro_family'           => $this->yn($request, 'source_neuro_family'),

            'head_trauma'                   => $this->yn($request, 'head_trauma'),
            'head_trauma_specify'           => $request->head_trauma_specify,
            'migraine_headache'             => $this->yn($request, 'migraine_headache'),
            'impaired_vision_left'          => $this->yn($request, 'impaired_vision_left'),
            'impaired_vision_right'         => $this->yn($request, 'impaired_vision_right'),
            'impaired_hearing_left'         => $this->yn($request, 'impaired_hearing_left'),
            'impaired_hearing_right'        => $this->yn($request, 'impaired_hearing_right'),
            'hemiparesis_left_arm'          => $this->yn($request, 'hemiparesis_left_arm'),
            'hemiparesis_left_leg'          => $this->yn($request, 'hemiparesis_left_leg'),
            'hemiparesis_right_arm'         => $this->yn($request, 'hemiparesis_right_arm'),
            'hemiparesis_right_leg'         => $this->yn($request, 'hemiparesis_right_leg'),
            'paralysis_left_arm'            => $this->yn($request, 'paralysis_left_arm'),
            'paralysis_left_leg'            => $this->yn($request, 'paralysis_left_leg'),
            'paralysis_right_arm'           => $this->yn($request, 'paralysis_right_arm'),
            'paralysis_right_leg'           => $this->yn($request, 'paralysis_right_leg'),
            'aphasia_motor'                 => $this->yn($request, 'aphasia_motor'),
            'aphasia_expressive'            => $this->yn($request, 'aphasia_expressive'),
            'mental_retardation'            => $this->yn($request, 'mental_retardation'),
            'dementia'                      => $this->yn($request, 'dementia'),
            'alzheimers_disease'            => $this->yn($request, 'alzheimers_disease'),
            'cva'                           => $this->yn($request, 'cva'),
            'parkinsons_disease'            => $this->yn($request, 'parkinsons_disease'),
            'seizure_disorder'              => $this->yn($request, 'seizure_disorder'),
            'neuro_comment'                 => $request->neuro_comment,

            'source_cardiac_participant'    => $this->yn($request, 'source_cardiac_participant'),
            'source_cardiac_medical_record' => $this->yn($request, 'source_cardiac_medical_record'),
            'source_cardiac_family'         => $this->yn($request, 'source_cardiac_family'),

            'angina'                        => $this->yn($request, 'angina'),
            'myocardial_infarction'         => $this->yn($request, 'myocardial_infarction'),
            'irregular_heart_rhythm'        => $this->yn($request, 'irregular_heart_rhythm'),
            'permanent_pacemaker'           => $this->yn($request, 'permanent_pacemaker'),
            'permanent_pacemaker_rate'      => $request->permanent_pacemaker_rate,
            'peripheral_edema'              => $this->yn($request, 'peripheral_edema'),
            'peripheral_edema_location'     => $request->peripheral_edema_location,
            'valve_disorder'                => $this->yn($request, 'valve_disorder'),
            'murmur'                        => $this->yn($request, 'murmur'),
            'congestive_heart_failure'      => $this->yn($request, 'congestive_heart_failure'),
            'htn'                           => $this->yn($request, 'htn'),
            'cardiac_comment'               => $request->cardiac_comment,

            'source_respiratory_participant'      => $this->yn($request, 'source_respiratory_participant'),
            'source_respiratory_medical_record'   => $this->yn($request, 'source_respiratory_medical_record'),
            'source_respiratory_family'           => $this->yn($request, 'source_respiratory_family'),

            'smoking'                       => $this->yn($request, 'smoking'),
            'smoking_specify'               => $request->smoking_specify,
            'asthma'                        => $this->yn($request, 'asthma'),
            'bronchitis'                    => $this->yn($request, 'bronchitis'),
            'pneumonia'                     => $this->yn($request, 'pneumonia'),
            'copd'                          => $this->yn($request, 'copd'),
            'cancer'                        => $this->yn($request, 'cancer'),
            'chronic_cough'                 => $this->yn($request, 'chronic_cough'),
            'influenza'                     => $this->yn($request, 'influenza'),
            'tb'                            => $this->yn($request, 'tb'),
            'respiratory_comment'           => $request->respiratory_comment,

            'source_gigu_participant'       => $this->yn($request, 'source_gigu_participant'),
            'source_gigu_medical_record'    => $this->yn($request, 'source_gigu_medical_record'),
            'source_gigu_family'            => $this->yn($request, 'source_gigu_family'),

            'ulcer'                         => $this->yn($request, 'ulcer'),
            'ulcer_specify'                 => $request->ulcer_specify,
            'gerd'                          => $this->yn($request, 'gerd'),
            'hepatitis'                     => $this->yn($request, 'hepatitis'),
            'hepatitis_specify'             => $request->hepatitis_specify,
            'gi_bleeding'                   => $this->yn($request, 'gi_bleeding'),
            'pancreatitis'                  => $this->yn($request, 'pancreatitis'),
            'gigu_aphasia'                  => $this->yn($request, 'gigu_aphasia'),
            'cirrhosis'                     => $this->yn($request, 'cirrhosis'),
            'gallbladder_disease'           => $this->yn($request, 'gallbladder_disease'),
            'gallstones'                    => $this->yn($request, 'gallstones'),
            'kidney_stones'                 => $this->yn($request, 'kidney_stones'),
            'esrd_dialysis'                 => $this->yn($request, 'esrd_dialysis'),
            'gigu_comment'                  => $request->gigu_comment,

            'source_endocrine_participant'     => $this->yn($request, 'source_endocrine_participant'),
            'source_endocrine_medical_record'  => $this->yn($request, 'source_endocrine_medical_record'),
            'source_endocrine_family'          => $this->yn($request, 'source_endocrine_family'),

            'non_insulin_diabetes'          => $this->yn($request, 'non_insulin_diabetes'),
            'insulin_diabetes'              => $this->yn($request, 'insulin_diabetes'),
            'dka'                           => $this->yn($request, 'dka'),
            'hhnk'                          => $this->yn($request, 'hhnk'),
            'hypothyroidism'                => $this->yn($request, 'hypothyroidism'),
            'hyperthyroidism'               => $this->yn($request, 'hyperthyroidism'),
            'allergies'                     => $request->allergies,
            'endocrine_comment'             => $request->endocrine_comment,

            'source_current_complaint_participant'     => $this->yn($request, 'source_current_complaint_participant'),
            'source_current_complaint_medical_record'  => $this->yn($request, 'source_current_complaint_medical_record'),
            'source_current_complaint_family'          => $this->yn($request, 'source_current_complaint_family'),
            'current_complaint'             => $request->current_complaint,
        ];

        $vitalHospitalization = [
            'temp'                         => $request->temp,
            'bp'                           => $request->bp,
            'respiratory_rate'             => $request->respiratory_rate,
            'pulse'                        => $request->pulse,
            'pacemaker'                    => $this->yn($request, 'pacemaker'),
            'pacemaker_last_checked_date'  => $request->pacemaker_last_checked_date,
            'implantable_defibrillator'    => $this->yn($request, 'implantable_defibrillator'),
            'no_hospitalization'           => $this->yn($request, 'no_hospitalization'),
            'hospitalization_dates'        => $request->hospitalization_dates,
            'primary_diagnosis_hospitalization' => $request->primary_diagnosis_hospitalization,
        ];

        /*
        |--------------------------------------------------------------------------
        | Page 2 - Functional Assessment
        |--------------------------------------------------------------------------
        */
        $functionalRows = [
            'bed_recliner_mobility',
            'transfers',
            'locomotion',
            'eating',
            'dressing',
            'toileting',
            'hygiene',
            'bathing',
        ];

        $functionalAssessment = [];

        foreach ($functionalRows as $row) {
            $functionalAssessment[$row] = [
                'level'   => $request->input($row . '_level'),
                'comment' => $request->input($row . '_comment'),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Page 3 - Bowel/Bladder, Mental, Behavior, Sleep, Skilled Needs
        |--------------------------------------------------------------------------
        */
        $bowelBladder = [
            'bowel_level'                         => $request->bowel_level,
            'bladder_level'                       => $request->bladder_level,
            'toileting_assisted_request'          => $this->yn($request, 'toileting_assisted_request'),
            'toileting_scheduled_program'         => $this->yn($request, 'toileting_scheduled_program'),
            'toileting_pads_briefs_pullups'       => $this->yn($request, 'toileting_pads_briefs_pullups'),
            'toileting_change_continent_status'   => $this->yn($request, 'toileting_change_continent_status'),
            'bowel_regular'                       => $this->yn($request, 'bowel_regular'),
            'bowel_loose_stool'                   => $this->yn($request, 'bowel_loose_stool'),
            'bowel_enema'                         => $this->yn($request, 'bowel_enema'),
            'bowel_enema_date'                    => $request->bowel_enema_date,
            'bowel_constipation'                  => $this->yn($request, 'bowel_constipation'),
            'bowel_diarrhea'                      => $this->yn($request, 'bowel_diarrhea'),
            'bowel_laxative'                      => $this->yn($request, 'bowel_laxative'),
            'bowel_laxative_med'                  => $request->bowel_laxative_med,
        ];

        $mentalBehaviorSleep = [
            'alert_oriented'        => $this->yn($request, 'alert_oriented'),
            'oriented_person'       => $this->yn($request, 'oriented_person'),
            'oriented_place'        => $this->yn($request, 'oriented_place'),
            'oriented_time'         => $this->yn($request, 'oriented_time'),
            'confused'              => $this->yn($request, 'confused'),
            'confused_sometimes'    => $this->yn($request, 'confused_sometimes'),
            'confused_always'       => $this->yn($request, 'confused_always'),
            'semi_comatose'         => $this->yn($request, 'semi_comatose'),
            'comatose'              => $this->yn($request, 'comatose'),
            'memory_loss'           => $this->yn($request, 'memory_loss'),
            'memory_short_term'     => $this->yn($request, 'memory_short_term'),
            'memory_long_term'      => $this->yn($request, 'memory_long_term'),
            'no_memory_loss'        => $this->yn($request, 'no_memory_loss'),
            'mmse_score'            => $request->mmse_score,

            'agitation'                         => $this->yn($request, 'agitation'),
            'physically_aggressive'             => $this->yn($request, 'physically_aggressive'),
            'verbally_abusive'                  => $this->yn($request, 'verbally_abusive'),
            'hoards'                            => $this->yn($request, 'hoards'),
            'places_self_at_risk'               => $this->yn($request, 'places_self_at_risk'),
            'resists_care'                      => $this->yn($request, 'resists_care'),
            'wandering'                         => $this->yn($request, 'wandering'),
            'socially_inappropriate_behavior'   => $this->yn($request, 'socially_inappropriate_behavior'),
            'behavior_other'                    => $request->behavior_other,
            'behavior_management_program'       => $request->behavior_management_program,

            'sleeps_through_night'      => $this->yn($request, 'sleeps_through_night'),
            'poor_due_to'               => $request->poor_due_to,
            'awakes_frequently'         => $this->yn($request, 'awakes_frequently'),
            'awakes_reason'             => $request->awakes_reason,
            'naps_during_day'           => $this->yn($request, 'naps_during_day'),
            'naps_time'                 => $request->naps_time,
            'requires_hypnotic'         => $this->yn($request, 'requires_hypnotic'),
            'hypnotic_indication'       => $request->hypnotic_indication,
            'sleep_med'                 => $request->sleep_med,
        ];

        $skilledNeeds = [
            'rehab_pt'               => $this->yn($request, 'rehab_pt'),
            'rehab_pt_days'          => $request->rehab_pt_days,
            'rehab_ot'               => $this->yn($request, 'rehab_ot'),
            'rehab_ot_days'          => $request->rehab_ot_days,
            'rehab_speech'           => $this->yn($request, 'rehab_speech'),
            'rehab_speech_days'      => $request->rehab_speech_days,

            'daily_oxygen'           => $this->yn($request, 'daily_oxygen'),
            'hemodialysis'           => $this->yn($request, 'hemodialysis'),
            'tube_feeding'           => $this->yn($request, 'tube_feeding'),
            'tube_gtt'               => $this->yn($request, 'tube_gtt'),
            'tube_ngt'               => $this->yn($request, 'tube_ngt'),
            'blood_transfusion'      => $this->yn($request, 'blood_transfusion'),
            'tracheostomy_care'      => $this->yn($request, 'tracheostomy_care'),
            'ostomy_care'            => $this->yn($request, 'ostomy_care'),
            'abt_therapy'            => $this->yn($request, 'abt_therapy'),

            'iv_fluids'              => $this->yn($request, 'iv_fluids'),
            'iv_fluids_dates'        => $request->iv_fluids_dates,
            'iv_medication'          => $this->yn($request, 'iv_medication'),
            'iv_medication_dates'    => $request->iv_medication_dates,
            'respiratory_therapy'    => $this->yn($request, 'respiratory_therapy'),
            'internal_bleeding'      => $this->yn($request, 'internal_bleeding'),
            'chemotherapy_radiation' => $this->yn($request, 'chemotherapy_radiation'),
            'hip_fracture'           => $this->yn($request, 'hip_fracture'),

            'suctioning'             => $this->yn($request, 'suctioning'),
            'tpn'                    => $this->yn($request, 'tpn'),
            'wound_care'             => $this->yn($request, 'wound_care'),
            'uti'                    => $this->yn($request, 'uti'),
            'dehydration'            => $this->yn($request, 'dehydration'),
            'other_fracture'         => $request->other_fracture,
            'skilled_other'          => $request->skilled_other,

            'hemo_not_applicable'    => $this->yn($request, 'hemo_not_applicable'),
            'treatment_orders'       => $request->treatment_orders,
            'times_per_week'         => $request->times_per_week,
            'active_shunt_location'  => $request->active_shunt_location,
            'bruit_yes'              => $this->yn($request, 'bruit_yes'),
            'bruit_no'               => $this->yn($request, 'bruit_no'),
            'thrill_yes'             => $this->yn($request, 'thrill_yes'),
            'thrill_no'              => $this->yn($request, 'thrill_no'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Page 4 - Skin, Devices, Nutrition, Pain
        |--------------------------------------------------------------------------
        */
        $skinAssessment = [
            'skin_pressure_ulcer'  => $this->yn($request, 'skin_pressure_ulcer'),
            'skin_stasis_ulcer'    => $this->yn($request, 'skin_stasis_ulcer'),
            'skin_rash'            => $this->yn($request, 'skin_rash'),
            'skin_ecchymosis'      => $this->yn($request, 'skin_ecchymosis'),
            'skin_burn'            => $this->yn($request, 'skin_burn'),
            'skin_abrasion'        => $this->yn($request, 'skin_abrasion'),
            'skin_tear'            => $this->yn($request, 'skin_tear'),
            'skin_cellulitis'      => $this->yn($request, 'skin_cellulitis'),
            'skin_blister'         => $this->yn($request, 'skin_blister'),
            'skin_hematoma'        => $this->yn($request, 'skin_hematoma'),
            'skin_surgical_wound'  => $this->yn($request, 'skin_surgical_wound'),
            'skin_scar'            => $this->yn($request, 'skin_scar'),
            'skin_edema'           => $this->yn($request, 'skin_edema'),
            'skin_body_notes'      => $request->skin_body_notes,
            'skin_significant_findings' => $request->skin_significant_findings,
        ];

        $assistiveDevices = [
            'device_none'                 => $this->yn($request, 'device_none'),
            'wc_cushion'                  => $this->yn($request, 'wc_cushion'),
            'specialty_wc'                => $this->yn($request, 'specialty_wc'),
            'specialty_wc_accessories'    => $this->yn($request, 'specialty_wc_accessories'),
            'knee_separator'              => $this->yn($request, 'knee_separator'),
            'handroll_left'               => $this->yn($request, 'handroll_left'),
            'handroll_right'              => $this->yn($request, 'handroll_right'),
            'hand_splint_left'            => $this->yn($request, 'hand_splint_left'),
            'hand_splint_right'           => $this->yn($request, 'hand_splint_right'),
            'prosthesis_left'             => $this->yn($request, 'prosthesis_left'),
            'prosthesis_right'            => $this->yn($request, 'prosthesis_right'),
            'brace_splint_left'           => $this->yn($request, 'brace_splint_left'),
            'brace_splint_right'          => $this->yn($request, 'brace_splint_right'),
            'device_other'                => $request->device_other,
            'instruction_wear_schedule'   => $request->instruction_wear_schedule,
            'dentures_none'               => $this->yn($request, 'dentures_none'),
            'dentures_full_upper'         => $this->yn($request, 'dentures_full_upper'),
            'dentures_full_lower'         => $this->yn($request, 'dentures_full_lower'),
            'dentures_partial_upper'      => $this->yn($request, 'dentures_partial_upper'),
            'dentures_partial_lower'      => $this->yn($request, 'dentures_partial_lower'),
            'hearing_aid_none'            => $this->yn($request, 'hearing_aid_none'),
            'hearing_aid_left'            => $this->yn($request, 'hearing_aid_left'),
            'hearing_aid_right'           => $this->yn($request, 'hearing_aid_right'),
            'glasses'                     => $this->yn($request, 'glasses'),
        ];

        $nutritionalStatus = [
            'regular_diet'             => $this->yn($request, 'regular_diet'),
            'ncs'                      => $this->yn($request, 'ncs'),
            'nas'                      => $this->yn($request, 'nas'),
            'supplement_type'          => $request->supplement_type,
            'therapeutic_diet'         => $request->therapeutic_diet,
            'appetite_good'            => $this->yn($request, 'appetite_good'),
            'appetite_fair'            => $this->yn($request, 'appetite_fair'),
            'appetite_poor'            => $this->yn($request, 'appetite_poor'),
            'chewing_problem'          => $this->yn($request, 'chewing_problem'),
            'swallowing_problem'       => $this->yn($request, 'swallowing_problem'),
            'mechanical_soft'          => $this->yn($request, 'mechanical_soft'),
            'puree'                    => $this->yn($request, 'puree'),
            'fluid_restriction'        => $request->fluid_restriction,
            'weight_lbs'               => $request->weight_lbs,
            'weight_stable'            => $this->yn($request, 'weight_stable'),
            'weight_change'            => $this->yn($request, 'weight_change'),
            'weight_gain'              => $this->yn($request, 'weight_gain'),
            'weight_loss'              => $this->yn($request, 'weight_loss'),
            'planned'                  => $this->yn($request, 'planned'),
            'unplanned'                => $this->yn($request, 'unplanned'),
            'md_nutritionist_informed' => $this->yn($request, 'md_nutritionist_informed'),
        ];

        $painAssessment = [
            'pain_no'  => $this->yn($request, 'pain_no'),
            'pain_yes' => $this->yn($request, 'pain_yes'),
            'pain_notes' => $request->pain_notes,
        ];

        $data = [
            'ParticipantId'             => $participantId,
            'assessment_date'           => $assessmentDate,
            'Trans_flag'                => $type,
            'patient_name'              => $patientName,
            'dob'                       => $dob,
            'assessment_type'           => $request->assessment_type,

            'past_medical_history'      => json_encode($pastMedicalHistory),
            'vital_hospitalization'     => json_encode($vitalHospitalization),
            'functional_assessment'     => json_encode($functionalAssessment),
            'bowel_bladder'             => json_encode($bowelBladder),
            'mental_behavior_sleep'     => json_encode($mentalBehaviorSleep),
            'skilled_needs'             => json_encode($skilledNeeds),
            'skin_assessment'           => json_encode($skinAssessment),
            'assistive_devices'         => json_encode($assistiveDevices),
            'nutritional_status'        => json_encode($nutritionalStatus),
            'pain_assessment'           => json_encode($painAssessment),

            'signature'                 => $request->signature,
            'signature_date'            => $signatureDate,

            'updated_by'                => Auth::id(),
            'updated_at'                => now(),
        ];

        $existing = DB::table('nursing_assessment_summaries')
            ->where('ParticipantId', $participantId)
            ->where('assessment_date', $assessmentDate)
            ->where('Trans_flag', $type)
            ->first();

        if ($existing) {
            DB::table('nursing_assessment_summaries')
                ->where('id', $existing->id)
                ->update($data);

            $id = $existing->id;
            $message = 'Nursing Assessment Summary updated successfully.';
        } else {
            $data['created_by'] = Auth::id();
            $data['created_at'] = now();

            $id = DB::table('nursing_assessment_summaries')->insertGetId($data);
            $message = 'Nursing Assessment Summary saved successfully.';
        }

        return redirect()
            ->route('nursing.assessment.summary.load', [
                'type'            => $type,
                'participant_id'  => $participantId,
                'assessment_date' => Carbon::parse($assessmentDate)->format('m/d/Y'),
            ])
            ->with('success', $message)
            ->with('saved_id', $id);

    } // End Method

    public function exportNursingAssessmentSummaryPdf($id)
    {
        $assessment = DB::table('nursing_assessment_summaries')
            ->where('id', $id)
            ->first();

        if (!$assessment) {
            return back()->with('error', 'Assessment record not found.');
        }

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->ParticipantId)
            ->first();

        $sections = [
            'past_medical_history'    => $this->decodeJsonSection($assessment->past_medical_history ?? null),
            'vital_hospitalization'   => $this->decodeJsonSection($assessment->vital_hospitalization ?? null),
            'functional_assessment'   => $this->decodeJsonSection($assessment->functional_assessment ?? null),
            'bowel_bladder'           => $this->decodeJsonSection($assessment->bowel_bladder ?? null),
            'mental_behavior_sleep'   => $this->decodeJsonSection($assessment->mental_behavior_sleep ?? null),
            'skilled_needs'           => $this->decodeJsonSection($assessment->skilled_needs ?? null),
            'skin_assessment'         => $this->decodeJsonSection($assessment->skin_assessment ?? null),
            'assistive_devices'       => $this->decodeJsonSection($assessment->assistive_devices ?? null),
            'nutritional_status'      => $this->decodeJsonSection($assessment->nutritional_status ?? null),
            'pain_assessment'         => $this->decodeJsonSection($assessment->pain_assessment ?? null),
        ];

        $html = view('nursing.nursing_assessment_summary_pdf', compact(
            'assessment',
            'member',
            'sections'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'orientation' => 'P',
            'margin_left' => 7,
            'margin_right' => 7,
            'margin_top' => 7,
            'margin_bottom' => 7,
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->SetTitle('Nursing Assessment Summary');
        $mpdf->WriteHTML($html);

        $fileName = 'nursing-assessment-summary-' .
            $assessment->ParticipantId . '-' .
            Carbon::parse($assessment->assessment_date)->format('Ymd') .
            '.pdf';

        return response($mpdf->Output($fileName, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');

    } // End Method

   public function monthlyVitalsReport(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Selected member category
        |--------------------------------------------------------------------------
        | A = Morning
        | N = Noon
        | G = Guest
        */
        $type = strtoupper(trim((string) $request->query('type', 'A')));

        if (!in_array($type, ['A', 'N', 'G'], true)) {
            $type = 'A';
        }

        $typeName = match ($type) {
            'A' => 'Morning',
            'N' => 'Noon',
            'G' => 'Guest',
        };

        /*
        |--------------------------------------------------------------------------
        | Load only members belonging to the selected category
        |--------------------------------------------------------------------------
        */
        $members = Member::query()
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'Trans_flag'
            )
            ->where('Trans_flag', $type)
            ->orderByRaw('CAST(ParticipantId AS UNSIGNED)')
            ->get();

        return view('nursing.reports.monthly_vitals_report', compact(
            'members',
            'type',
            'typeName'
        ));
    } // End Method


    public function dailyVitalsReport(Request $request)
    {
        $type = strtoupper(trim((string) $request->query('type', 'A')));

        if (!in_array($type, ['A', 'N', 'G'], true)) {
            $type = 'A';
        }

        $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
        };

        return view('nursing.reports.daily_vitals_report', compact(
            'type',
            'shiftTitle'
        ));

    } // End Method

    public function dailyAdlReport(Request $request)
    {
        $type = strtoupper(trim((string) $request->query('type', 'A')));

        if (!in_array($type, ['A', 'N', 'G'], true)) {
            $type = 'A';
        }

        $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
        };

        return view('nursing.reports.daily_adl_report', compact(
            'type',
            'shiftTitle'
        ));

    } // End Method

    /**
     * Display quarterly assessment member report.
     */
    public function QuarterlyAssessmentMembersReport(Request $request)
    {
        $shift = strtoupper(trim((string) $request->input('shift', 'A')));
        $month = strtoupper(trim((string) $request->input('month', '')));

        if (!in_array($shift, ['A', 'N'], true)) {
            $shift = 'A';
        }

        $rows = collect();
        $reportData = null;

        /*
        |--------------------------------------------------------------------------
        | Only run the report when the user entered a month
        |--------------------------------------------------------------------------
        */
        if ($month !== '') {
            $validator = Validator::make(
                [
                    'month' => $month,
                    'shift' => $shift,
                ],
                [
                    'month' => [
                        'required',
                        'string',
                        'size:3',
                        'regex:/^[A-Z]{3}$/',
                        Rule::in([
                            'JAN', 'FEB', 'MAR', 'APR',
                            'MAY', 'JUN', 'JUL', 'AUG',
                            'SEP', 'OCT', 'NOV', 'DEC',
                        ]),
                    ],
                    'shift' => [
                        'required',
                        Rule::in(['A', 'N']),
                    ],
                ],
                [
                    'month.required' => 'Please enter the month for which you need the report.',
                    'month.size'     => 'Please enter a three-character month such as JAN, FEB or MAR.',
                    'month.regex'    => 'Numbers and special characters are not allowed. Please enter JAN, FEB, MAR, etc.',
                    'month.in'       => 'Please enter a valid three-character month such as JAN, FEB or MAR.',
                ]
            );

            if ($validator->fails()) {
                return redirect()
                    ->route('nursing.quarterly.assessment.members', [
                        'shift' => $shift,
                    ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $reportData = $this->resolveQuarterlyAssessmentMonth($month);

            $rows = $this->getQuarterlyAssessmentMembers(
                $shift,
                $reportData['month_numbers']
            );
        }

        $shiftLabel = $shift === 'N' ? 'Noon' : 'Morning';

        return view('nursing.reports.quarterly_assessment_members', [
            'title'             => 'Quarterly Assessment Members',
            'module'            => 'nursing',
            'rows'              => $rows,
            'shift'             => $shift,
            'shiftLabel'        => $shiftLabel,
            'selectedMonth'     => $month,
            'selectedMonthName' => $reportData['selected_month_name'] ?? null,
            'quarterMonthNames' => $reportData['month_names'] ?? [],
            'reportGenerated'   => $month !== '' && $reportData !== null,
        ]);

    } // End Method

    /**
     * Export quarterly assessment member report to PDF.
     */
    public function QuarterlyAssessmentMembersPdf(Request $request)
    {
        $filters = $this->validateQuarterlyAssessmentFilters($request);

        $shift = $filters['shift'];
        $month = $filters['month'];

        $sort = strtolower(trim((string) $request->input(
            'sort',
            'last_name'
        )));

        $direction = strtolower(trim((string) $request->input(
            'direction',
            'asc'
        )));

        $reportData = $this->resolveQuarterlyAssessmentMonth($month);

        $rows = $this->getQuarterlyAssessmentMembers(
            $shift,
            $reportData['month_numbers'],
            $sort,
            $direction
        );

        $shiftLabel = $shift === 'N' ? 'Noon' : 'Morning';

        $company = $this->getQuarterlyAssessmentCompany();

        $pdf = Pdf::loadView(
            'nursing.reports.pdf.quarterly_assessment_members_pdf',
            [
                'company'           => $company,
                'rows'              => $rows,
                'shift'             => $shift,
                'shiftLabel'        => $shiftLabel,
                'selectedMonth'     => $month,
                'selectedMonthName' => $reportData['selected_month_name'],
                'quarterMonthNames' => $reportData['month_names'],
            ]
        );

        $pdf->setPaper('letter', 'landscape');

        $fileName = 'quarterly_assessment_members_'
            . strtolower($shiftLabel)
            . '_'
            . strtolower($month)
            . '.pdf';

        return $pdf->stream($fileName);

    } // End Method

    /**
     * Export quarterly assessment member report to Excel.
     */
    public function QuarterlyAssessmentMembersExcel(Request $request)
    {
        $filters = $this->validateQuarterlyAssessmentFilters($request);

        $shift = $filters['shift'];
        $month = $filters['month'];

        $sort = strtolower(trim((string) $request->input(
            'sort',
            'last_name'
        )));

        $direction = strtolower(trim((string) $request->input(
            'direction',
            'asc'
        )));

        $reportData = $this->resolveQuarterlyAssessmentMonth($month);

        $rows = $this->getQuarterlyAssessmentMembers(
            $shift,
            $reportData['month_numbers'],
            $sort,
            $direction
        );

        $shiftLabel = $shift === 'N' ? 'Noon' : 'Morning';

        $company = $this->getQuarterlyAssessmentCompany();

        $fileName = 'quarterly_assessment_members_'
            . strtolower($shiftLabel)
            . '_'
            . strtolower($month)
            . '.xlsx';

        return Excel::download(
            new QuarterlyAssessmentMembersExport(
                $company,
                $rows,
                $shift,
                $shiftLabel,
                $month,
                $reportData['selected_month_name'],
                $reportData['month_names']
            ),
            $fileName
        );

    } // End Method

    /**
     * Retrieve members matching the selected quarterly month group.
     */
    private function getQuarterlyAssessmentMembers(string $shift, array $monthNumbers, string $sort = 'last_name', string $direction = 'asc')
    {
        /*
        |--------------------------------------------------------------------------
        | Only allow known sorting values
        |--------------------------------------------------------------------------
        */
        $allowedSorts = [
            'participant_id' => 'ParticipantId',
            'last_name'      => 'LastName',
            'first_name'     => 'FirstName',
            'middle_name'    => 'MiddleName',
            'admission_date' => 'AdmissionDate',
        ];

        $sortColumn = $allowedSorts[$sort] ?? 'LastName';

        $direction = strtolower($direction) === 'desc'
            ? 'desc'
            : 'asc';

        $query = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'AdmissionDate'
            )
            ->where('Trans_flag', $shift)
            ->whereNotNull('AdmissionDate')
            ->whereIn(DB::raw('MONTH(AdmissionDate)'), $monthNumbers)
            ->orderBy($sortColumn, $direction);

        /*
        |--------------------------------------------------------------------------
        | Secondary sorting makes equal values display consistently
        |--------------------------------------------------------------------------
        */
        if ($sortColumn !== 'LastName') {
            $query->orderBy('LastName', 'asc');
        }

        if ($sortColumn !== 'FirstName') {
            $query->orderBy('FirstName', 'asc');
        }

        if ($sortColumn !== 'ParticipantId') {
            $query->orderBy('ParticipantId', 'asc');
        }

        return $query->get();

    } // End Method


    /**
     * Validate the PDF and Excel report filters.
     */
    private function validateQuarterlyAssessmentFilters(Request $request): array
    {
        $month = strtoupper(trim((string) $request->input('month', '')));
        $shift = strtoupper(trim((string) $request->input('shift', 'A')));

        $validator = Validator::make(
            [
                'month' => $month,
                'shift' => $shift,
            ],
            [
                'month' => [
                    'required',
                    'string',
                    'size:3',
                    'regex:/^[A-Z]{3}$/',
                    Rule::in([
                        'JAN', 'FEB', 'MAR', 'APR',
                        'MAY', 'JUN', 'JUL', 'AUG',
                        'SEP', 'OCT', 'NOV', 'DEC',
                    ]),
                ],
                'shift' => [
                    'required',
                    Rule::in(['A', 'N']),
                ],
            ],
            [
                'month.required' => 'Please enter the month for which you need the report.',
                'month.size'     => 'Please enter a three-character month such as JAN, FEB or MAR.',
                'month.regex'    => 'Numbers and special characters are not allowed.',
                'month.in'       => 'Please enter a valid three-character month such as JAN, FEB or MAR.',
            ]
        );

        if ($validator->fails()) {
            abort(422, $validator->errors()->first());
        }

        return [
            'month' => $month,
            'shift' => $shift,
        ];

    } // End Method


    /**
     * Convert the entered month into the legacy quarterly month group.
     */
    private function resolveQuarterlyAssessmentMonth(string $month): array
    {
        $month = strtoupper(trim($month));

        $monthDetails = [
            'JAN' => [
                'number' => 1,
                'name'   => 'January',
            ],
            'FEB' => [
                'number' => 2,
                'name'   => 'February',
            ],
            'MAR' => [
                'number' => 3,
                'name'   => 'March',
            ],
            'APR' => [
                'number' => 4,
                'name'   => 'April',
            ],
            'MAY' => [
                'number' => 5,
                'name'   => 'May',
            ],
            'JUN' => [
                'number' => 6,
                'name'   => 'June',
            ],
            'JUL' => [
                'number' => 7,
                'name'   => 'July',
            ],
            'AUG' => [
                'number' => 8,
                'name'   => 'August',
            ],
            'SEP' => [
                'number' => 9,
                'name'   => 'September',
            ],
            'OCT' => [
                'number' => 10,
                'name'   => 'October',
            ],
            'NOV' => [
                'number' => 11,
                'name'   => 'November',
            ],
            'DEC' => [
                'number' => 12,
                'name'   => 'December',
            ],
        ];

        if (!isset($monthDetails[$month])) {
            abort(422, 'Please enter a valid three-character month such as JAN, FEB or MAR.');
        }

        $selectedMonthNumber = $monthDetails[$month]['number'];

        /*
        |--------------------------------------------------------------------------
        | Legacy PowerBuilder grouping
        |--------------------------------------------------------------------------
        |
        | Group 1: January, April, July, October
        | Group 2: February, May, August, November
        | Group 3: March, June, September, December
        |
        */
        if (in_array($selectedMonthNumber, [1, 4, 7, 10], true)) {
            $monthNumbers = [1, 4, 7, 10];
            $monthNames = ['January', 'April', 'July', 'October'];
        } elseif (in_array($selectedMonthNumber, [2, 5, 8, 11], true)) {
            $monthNumbers = [2, 5, 8, 11];
            $monthNames = ['February', 'May', 'August', 'November'];
        } else {
            $monthNumbers = [3, 6, 9, 12];
            $monthNames = ['March', 'June', 'September', 'December'];
        }

        return [
            'selected_month'      => $month,
            'selected_month_name' => $monthDetails[$month]['name'],
            'selected_month_no'   => $selectedMonthNumber,
            'month_numbers'       => $monthNumbers,
            'month_names'         => $monthNames,
        ];

    } // End Method


    /**
     * Retrieve company information for the report header.
     *
     * Modify this method to match the company query already used in your
     * other NursingController reports.
     */
    private function getQuarterlyAssessmentCompany()
    {
        return DB::table('gnmscomp')->first();

    } // End Method

    public function NurseLog(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Allowed Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'ParticipantId',
            'LastName',
            'FirstName',
        ];

        $sort = $request->get('sort', 'LastName');
        $dir  = strtolower($request->get('dir', 'asc'));

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'LastName';
        }

        if (!in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }


        /*
        |--------------------------------------------------------------------------
        | Shift
        |--------------------------------------------------------------------------
        |
        | A = Morning
        | N = Noon
        |
        */

        $shift = strtoupper($request->get('shift', 'A'));

        if (!in_array($shift, ['A', 'N'], true)) {
            $shift = 'A';
        }


        /*
        |--------------------------------------------------------------------------
        | Company Information
        |--------------------------------------------------------------------------
        */

        $companyRecord = DB::table('gnmscomp')->first();

        $company = $this->buildNurseLogCompany($companyRecord);


        /*
        |--------------------------------------------------------------------------
        | Members
        |--------------------------------------------------------------------------
        */

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',

                // New monitoring fields
                'bloodsugar',
                'bloodpressure',
                'weight',
                'others'
            )
            ->where('Trans_flag', $shift)
            ->orderBy($sort, $dir)
            ->orderBy('ParticipantId', 'asc')
            ->get();


        return view('nursing.reports.nurse_log', [
            'members' => $members,
            'company' => $company,
            'sort'    => $sort,
            'dir'     => $dir,
            'shift'   => $shift,
            'isPdf'   => false,
        ]);
    } // End Method

    public function NurseLogPdf(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Allowed Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'ParticipantId',
            'LastName',
            'FirstName',
        ];

        $sort = $request->get('sort', 'LastName');
        $dir  = strtolower($request->get('dir', 'asc'));

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'LastName';
        }

        if (!in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }


        /*
        |--------------------------------------------------------------------------
        | Shift
        |--------------------------------------------------------------------------
        */

        $shift = strtoupper($request->get('shift', 'A'));

        if (!in_array($shift, ['A', 'N'], true)) {
            $shift = 'A';
        }


        /*
        |--------------------------------------------------------------------------
        | Company Information
        |--------------------------------------------------------------------------
        */

        $companyRecord = DB::table('gnmscomp')->first();

        $company = $this->buildNurseLogCompany($companyRecord);


        /*
        |--------------------------------------------------------------------------
        | Members
        |--------------------------------------------------------------------------
        */

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',

                // New monitoring fields
                'bloodsugar',
                'bloodpressure',
                'weight',
                'others'
            )
            ->where('Trans_flag', $shift)
            ->orderBy($sort, $dir)
            ->orderBy('ParticipantId', 'asc')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Render PDF Blade
        |--------------------------------------------------------------------------
        */

        $html = view('nursing.reports.nurse_log_pdf', [
            'members' => $members,
            'company' => $company,
            'sort'    => $sort,
            'dir'     => $dir,
            'shift'   => $shift,
            'isPdf'   => true,
        ])->render();


        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'orientation'   => 'L',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 10,
            'margin_footer' => 4,
        ]);


        $shiftLabel = $shift === 'N'
            ? 'Noon Shift'
            : 'Morning Shift';

        $mpdf->SetTitle('Nurse Log - ' . $shiftLabel);
        $mpdf->SetAuthor($company['name'] ?? '');
        $mpdf->SetDisplayMode('fullpage');

        /*
        |--------------------------------------------------------------------------
        | Page Number Footer
        |--------------------------------------------------------------------------
        */

        $mpdf->SetHTMLFooter('
            <div style="
                text-align: right;
                font-family: Arial, sans-serif;
                font-size: 8px;
                color: #000;
            ">
                Page {PAGENO} of {nbpg}
            </div>
        ');


        $mpdf->WriteHTML($html);


        $fileName = $shift === 'N'
            ? 'Nurse_Log_Noon.pdf'
            : 'Nurse_Log_Morning.pdf';


        return response(
            $mpdf->Output($fileName, 'S'),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]
        );
        
    } // End Method

    private function buildNurseLogCompany($record)
    {
        if (!$record) {
            return [
                'name'    => '',
                'address' => '',
                'city'    => '',
                'state'   => '',
                'zip'     => '',
                'phone'   => '',
                'fax'     => '',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Convert DB record to array
        |--------------------------------------------------------------------------
        */

        $row = (array) $record;

        /*
        |--------------------------------------------------------------------------
        | Helper to locate first available column
        |--------------------------------------------------------------------------
        */

        $getValue = function (array $names) use ($row) {

            foreach ($names as $name) {

                if (array_key_exists($name, $row)) {
                    return trim((string) $row[$name]);
                }

                /*
                |--------------------------------------------------------------
                | Case-insensitive lookup
                |--------------------------------------------------------------
                */

                foreach ($row as $key => $value) {
                    if (strtolower($key) === strtolower($name)) {
                        return trim((string) $value);
                    }
                }
            }

            return '';
        };


        return [

            'name' => $getValue([
                'comp_nm',               
            ]),

            'address' => $getValue([
                'complc_addr1',                
            ]),

            'city' => $getValue([
                'complc_city',                
            ]),

            'state' => $getValue([
                'complc_st',                
            ]),

            'zip' => $getValue([
                'complc_pin',                
            ]),

            'phone' => $getValue([
                'complc_ph',                
            ]),

            'fax' => $getValue([
                'complc_fax',                
            ]),
        ];

    } // End Method

}
