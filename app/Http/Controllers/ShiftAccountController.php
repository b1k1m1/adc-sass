<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\HasCommonDbFields;
use Mpdf\Mpdf;

class ShiftAccountController extends Controller
{
    use HasCommonDbFields;

    private string $companyId = 'DS02'; // change if needed
    private string $reportTitle = 'Details of Receipts and Payments Account for SHIFT 2';

    public function ShiftDashboard(){

        return view('shift.shift_index');

    } // End Method

    // -------------------------
    // Helpers
    // -------------------------
    private function parseUsDate(?string $mdy): ?string
    {
        if (!$mdy) return null;
        // Expect mm/dd/yyyy
        return Carbon::createFromFormat('m/d/Y', $mdy)->startOfDay()->toDateTimeString();

    } // End Method

    private function calcNet(float $amount, float $per): float
    {
        // net = amount - (amount * per/100)
        $ded = ($amount * $per) / 100;
        return round($amount - $ded, 2);

    } // End Method

    // -------------------------
    // DEPOSITS (DEPS)
    // -------------------------
    public function depositIndex()
    {
        return view('shift.deposits.index');

    } // End Method

    public function depositData()
    {
        $q = DB::table('shiftaccount')
            ->select([
                'trans_no','trans_dt','payto_name','credit_amt','per','net_credit','remarks'
            ])
            ->where('company_id', $this->companyId)
            ->where('trans_type', 'DEPS')
            ->orderByDesc('trans_no');

        return DataTables::of($q)
            ->editColumn('trans_dt', function ($row) {
                return $row->trans_dt ? Carbon::parse($row->trans_dt)->format('m/d/Y') : '';
            })
            ->addColumn('action', function ($row) {
                $url = route('shift.deposits.edit', $row->trans_no);
                return '<a href="'.$url.'" class="btn btn-sm btn-outline-primary">Edit</a>';
            })
            ->rawColumns(['action'])
            ->make(true);

    } // End Method

    public function depositCreate()
    {
        $fromNames = [
            'INSURANCE CLAIMS',
            'FOOD ASSISTANCE',
        ];

        return view('shift.deposits.form', [
            'mode' => 'create',
            'fromNames' => $fromNames,
            'row' => null,
        ]);

    } // End Method

    public function depositStore(Request $request)
    {
        $data = $request->validate([
            'trans_dt'    => ['required'],
            'payto_name'  => ['required','in:INSURANCE CLAIMS,FOOD ASSISTANCE'],
            'credit_amt'  => ['required','numeric','min:0'],
            'per'         => ['nullable','numeric','min:0','max:100'],
            'net_credit'  => ['nullable','numeric','min:0'],
            'remarks'     => ['nullable','string','max:30'],
        ]);

        $credit = (float)$data['credit_amt'];
        $per    = (float)($data['per'] ?? 0);

        // if user didn't type net_credit, calculate. If typed, keep it.
        $netCredit = array_key_exists('net_credit', $data) && $data['net_credit'] !== null && $data['net_credit'] !== ''
            ? (float)$data['net_credit']
            : $this->calcNet($credit, $per);

        $insert = [
            'trans_dt'     => $this->parseUsDate($data['trans_dt']),
            'trans_type'   => 'DEPS',
            'party_cd'     => null,
            'payto_name'   => $data['payto_name'],
            'debit_amt'    => 0,
            'credit_amt'   => $credit,
            'per'          => $per,
            'net_debit'    => 0,
            'net_credit'   => $netCredit,
            'remarks'      => $data['remarks'] ?? null,
            'check_no'     => null,
            'trans_flag'   => 'Y',
            'company_id'   => $this->companyId,
            'pay_type'     => null,
        ];

        $insert = $this->addCommonFieldsForCreate($insert);

        DB::table('shiftaccount')->insert($insert);

        return redirect()->route('shift.deposits.index')->with('success', 'Deposit saved.');

    } // End Method

    public function depositEdit($trans_no)
    {
        $row = DB::table('shiftaccount')
            ->where('company_id', $this->companyId)
            ->where('trans_type', 'DEPS')
            ->where('trans_no', $trans_no)
            ->first();

        abort_if(!$row, 404);

        $fromNames = ['INSURANCE CLAIMS','FOOD ASSISTANCE'];

        return view('shift.deposits.form', [
            'mode' => 'edit',
            'fromNames' => $fromNames,
            'row' => $row,
        ]);

    } // End Method

    public function depositUpdate(Request $request, $trans_no)
    {
        $data = $request->validate([
            'trans_dt'    => ['required'],
            'payto_name'  => ['required','in:INSURANCE CLAIMS,FOOD ASSISTANCE'],
            'credit_amt'  => ['required','numeric','min:0'],
            'per'         => ['nullable','numeric','min:0','max:100'],
            'net_credit'  => ['nullable','numeric','min:0'],
            'remarks'     => ['nullable','string','max:30'],
        ]);

        $credit = (float)$data['credit_amt'];
        $per    = (float)($data['per'] ?? 0);

        $netCredit = array_key_exists('net_credit', $data) && $data['net_credit'] !== null && $data['net_credit'] !== ''
            ? (float)$data['net_credit']
            : $this->calcNet($credit, $per);

        $update = [
            'trans_dt'   => $this->parseUsDate($data['trans_dt']),
            'payto_name' => $data['payto_name'],
            'credit_amt' => $credit,
            'per'        => $per,
            'net_credit' => $netCredit,
            'remarks'    => $data['remarks'] ?? null,
        ];

        $update = $this->addCommonFieldsForUpdate($update);

        DB::table('shiftaccount')
            ->where('company_id', $this->companyId)
            ->where('trans_type', 'DEPS')
            ->where('trans_no', $trans_no)
            ->update($update);

        return redirect()->route('shift.deposits.index')->with('success', 'Deposit updated.');

    } // End Method

    // -------------------------
    // PAYMENTS (PAYM)
    // -------------------------
    public function paymentIndex()
    {
        return view('shift.payments.index');

    } // End Method

    public function paymentData()
    {
        $q = DB::table('shiftaccount')
            ->select([
                'trans_no','trans_dt','party_cd','payto_name','debit_amt','per','net_debit','pay_type','check_no','remarks'
            ])
            ->where('company_id', $this->companyId)
            ->where('trans_type', 'PAYM')
            ->orderByDesc('trans_no');

        return DataTables::of($q)
            ->editColumn('trans_dt', fn($row) => $row->trans_dt ? Carbon::parse($row->trans_dt)->format('m/d/Y') : '')
            ->addColumn('action', function ($row) {
                $url = route('shift.payments.edit', $row->trans_no);
                return '<a href="'.$url.'" class="btn btn-sm btn-outline-primary">Edit</a>';
            })
            ->rawColumns(['action'])
            ->make(true);

    } // End Method

    public function paymentCreate()
    {

        $payTypes = [
            'CK' => 'CHECK',
            'CC' => 'Credit Card',
            'CS' => 'CASH',
        ];

        return view('shift.payments.form', [
            'mode' => 'create',
            'payTypes' => $payTypes,
            'row' => null,
        ]);

    } // End Method

    public function paymentStore(Request $request)
    {
        $data = $request->validate([
            'trans_dt'    => ['required'],
            'party_cd'    => ['required','string','max:10'],
            'debit_amt'   => ['required','numeric','min:0'],
            'pay_type'    => ['required','in:CK,CC,CS'],
            'check_no'    => ['nullable','numeric'],
            'per'         => ['nullable','numeric','min:0','max:100'],
            'net_debit'   => ['nullable','numeric','min:0'],
            'remarks'     => ['nullable','string','max:30'],
        ]);

        $party = DB::table('accountmasters')
            ->select(['party_cd','party_nm'])
            ->where('party_cd', $data['party_cd'])
            ->first();

        abort_if(!$party, 422, 'Invalid Party.');

        $debit = (float)$data['debit_amt'];
        $per   = (float)($data['per'] ?? 0);

        $netDebit = array_key_exists('net_debit', $data) && $data['net_debit'] !== null && $data['net_debit'] !== ''
            ? (float)$data['net_debit']
            : $this->calcNet($debit, $per);

        $insert = [
            'trans_dt'     => $this->parseUsDate($data['trans_dt']),
            'trans_type'   => 'PAYM',
            'party_cd'     => $party->party_cd,
            'payto_name'   => $party->party_nm,     // store party_nm here
            'debit_amt'    => $debit,
            'credit_amt'   => 0,
            'per'          => $per,
            'net_debit'    => $netDebit,
            'net_credit'   => 0,
            'remarks'      => $data['remarks'] ?? null,
            'check_no'     => $data['check_no'] ?? null,
            'trans_flag'   => 'Y',
            'company_id'   => $this->companyId,
            'pay_type'     => $data['pay_type'],
        ];

        $insert = $this->addCommonFieldsForCreate($insert);

        DB::table('shiftaccount')->insert($insert);

        return redirect()->route('shift.payments.index')->with('success', 'Payment saved.');

    } // End Method

    public function paymentEdit($trans_no)
    {
        $row = DB::table('shiftaccount')
            ->where('company_id', $this->companyId)
            ->where('trans_type', 'PAYM')
            ->where('trans_no', $trans_no)
            ->first();

        abort_if(!$row, 404);

        $payTypes = [
            'CK' => 'CHECK',
            'CC' => 'Credit Card',
            'CS' => 'CASH',
        ];

        return view('shift.payments.form', [
            'mode' => 'edit',
            'payTypes' => $payTypes,
            'row' => $row,
        ]);

    } // End Method

    public function paymentUpdate(Request $request, $trans_no)
    {
        $data = $request->validate([
            'trans_dt'    => ['required'],
            'party_cd'    => ['required','string','max:10'],
            'debit_amt'   => ['required','numeric','min:0'],
            'pay_type'    => ['required','in:CK,CC,CS'],
            'check_no'    => ['nullable','numeric'],
            'per'         => ['nullable','numeric','min:0','max:100'],
            'net_debit'   => ['nullable','numeric','min:0'],
            'remarks'     => ['nullable','string','max:30'],
        ]);

        $party = DB::table('accountmasters')
            ->select(['party_cd','party_nm'])
            ->where('party_cd', $data['party_cd'])
            ->first();

        abort_if(!$party, 422, 'Invalid Party.');

        $debit = (float)$data['debit_amt'];
        $per   = (float)($data['per'] ?? 0);

        $netDebit = array_key_exists('net_debit', $data) && $data['net_debit'] !== null && $data['net_debit'] !== ''
            ? (float)$data['net_debit']
            : $this->calcNet($debit, $per);

        $update = [
            'trans_dt'   => $this->parseUsDate($data['trans_dt']),
            'party_cd'   => $party->party_cd,
            'payto_name' => $party->party_nm,
            'debit_amt'  => $debit,
            'pay_type'   => $data['pay_type'],
            'check_no'   => $data['check_no'] ?? null,
            'per'        => $per,
            'net_debit'  => $netDebit,
            'remarks'    => $data['remarks'] ?? null,
        ];

        $update = $this->addCommonFieldsForUpdate($update);

        DB::table('shiftaccount')
            ->where('company_id', $this->companyId)
            ->where('trans_type', 'PAYM')
            ->where('trans_no', $trans_no)
            ->update($update);

        return redirect()->route('shift.payments.index')->with('success', 'Payment updated.');

    } // End Method

    private function startDate(string $ymd): string
    {
        return Carbon::createFromFormat('Y-m-d', $ymd)->startOfDay()->toDateTimeString();

    } // End Method

    private function endDate(string $ymd): string
    {
        return Carbon::createFromFormat('Y-m-d', $ymd)->endOfDay()->toDateTimeString();

    } // End Method


    public function IndexReport(){

        return view('shift.reports.index');


    } // End Method

    public function RunReport(Request $request){

        $data = $request->validate([
            'adt_start_date' => ['required','date_format:Y-m-d'],
            'adt_end_date'   => ['required','date_format:Y-m-d','after_or_equal:adt_start_date'],
        ]);

        $viewData = $this->buildReportData($data['adt_start_date'], $data['adt_end_date']);

        return view('shift.reports.result', $viewData);

    } // End Method

    private function buildReportData(string $adtStart, string $adtEnd): array
    {
        // INPUT: Y-m-d from <input type="date">
        $start = Carbon::createFromFormat('Y-m-d', $adtStart)->startOfDay();
        $end   = Carbon::createFromFormat('Y-m-d', $adtEnd)->endOfDay();

        // DISPLAY: m/d/Y for header
        $startLabel = $start->format('m/d/Y');
        $endLabel   = $end->format('m/d/Y');

        $base = DB::table('shiftaccount')->where('company_id', $this->companyId);

        // Opening (prior to start date)
        $open = (clone $base)
            ->where('trans_dt', '<', $start)  // ✅ use Carbon / datetime
            ->selectRaw('COALESCE(SUM(COALESCE(net_debit,0)),0) AS sum_net_debit')
            ->selectRaw('COALESCE(SUM(COALESCE(net_credit,0)),0) AS sum_net_credit')
            ->first();

        $openingBalance = round(((float)$open->sum_net_debit - (float)$open->sum_net_credit), 2);

        $op_credit_a = $openingBalance < 0 ? round(abs($openingBalance), 2) : 0.00;
        $op_debit_a  = $openingBalance > 0 ? round($openingBalance, 2) : 0.00;

        // Detail rows (between dates)
        $rows = (clone $base)
            ->whereBetween('trans_dt', [$start, $end])   // ✅ use Carbon / datetime
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->select([
                'trans_no','trans_dt','payto_name','pay_type','check_no',
                'credit_amt','per','net_credit','net_debit','remarks','trans_type'
            ])
            ->get()
            ->map(function ($r) {
                $r->trans_dt_fmt = $r->trans_dt ? Carbon::parse($r->trans_dt)->format('m/d/Y') : '';
                $r->my_home_amt  = round(((float)($r->credit_amt ?? 0) - (float)($r->net_credit ?? 0)), 2);
                return $r;
            });

        // Period totals
        $period = (clone $base)
            ->whereBetween('trans_dt', [$start, $end])   // ✅ use Carbon / datetime
            ->selectRaw('COALESCE(SUM(COALESCE(net_credit,0)),0) AS sum_net_credit')
            ->selectRaw('COALESCE(SUM(COALESCE(net_debit,0)),0) AS sum_net_debit')
            ->selectRaw('COALESCE(SUM(COALESCE(credit_amt,0) - COALESCE(net_credit,0)),0) AS sum_myhome_amt')
            ->first();

        $sum_net_credit = round((float)$period->sum_net_credit, 2);
        $sum_net_debit  = round((float)$period->sum_net_debit, 2);
        $sum_myhome_amt = round((float)$period->sum_myhome_amt, 2);

        // Legacy balancing closing so totals match
        $receiptsSide = round($op_credit_a + $sum_net_credit, 2);
        $paymentsSide = round($op_debit_a  + $sum_net_debit, 2);
        $diff = round($receiptsSide - $paymentsSide, 2);

        $cl_credit_amt = $diff < 0 ? round(abs($diff), 2) : 0.00;
        $cl_debit_amt  = $diff > 0 ? round(abs($diff), 2) : 0.00;

        $totcr = round($receiptsSide + $cl_credit_amt, 2);
        $totdr = round($paymentsSide + $cl_debit_amt, 2);

        return [
            'reportTitle' => $this->reportTitle,
            'adt_start_date' => $startLabel,
            'adt_end_date'   => $endLabel,

            'raw_start_date' => $adtStart, // Y-m-d
            'raw_end_date'   => $adtEnd,   // Y-m-d

            'todayFmt' => Carbon::now()->format('m/d/Y'),

            'op_credit_a' => $op_credit_a,
            'op_debit_a'  => $op_debit_a,

            'rows' => $rows,

            'sum_myhome_amt' => $sum_myhome_amt,
            'sum_net_credit' => $sum_net_credit,
            'sum_net_debit'  => $sum_net_debit,

            'cl_credit_amt' => $cl_credit_amt,
            'cl_debit_amt'  => $cl_debit_amt,

            'totcr' => $totcr,
            'totdr' => $totdr,
        ];

    } // End Method


    public function RunPdfReport(Request $request){

         $data = $request->validate([
            'adt_start_date' => ['required','date_format:Y-m-d'],
            'adt_end_date'   => ['required','date_format:Y-m-d','after_or_equal:adt_start_date'],
            'pdf_action'     => ['nullable','in:preview,download'],
        ]);

        $viewData = $this->buildReportData($data['adt_start_date'], $data['adt_end_date']);

        // Render a dedicated PDF view (recommended)
        $html = view('shift.reports.pdf', $viewData)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 28,
            'margin_bottom' => 18,
            'tempDir' => storage_path('app/mpdf'), // you already use this pattern
        ]);

        // Header / Footer (legacy-ish)
        $mpdf->SetHTMLHeader(view('shift.reports.pdf_header', $viewData)->render());
        $mpdf->SetHTMLFooter(view('shift.reports.pdf_footer', $viewData)->render());

        $mpdf->WriteHTML($html);

        $fileName = 'SHIFT2_Receipts_Payments_'.$viewData['adt_start_date'].'_to_'.$viewData['adt_end_date'].'.pdf';
        $fileName = str_replace(['/', ' '], ['-', '_'], $fileName);

        $pdfBinary = $mpdf->Output($fileName, 'S');
        $action = $data['pdf_action'] ?? 'preview';
        $disposition = $action === 'download' ? 'attachment' : 'inline';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="'.$fileName.'"',
        ]);

    } // End Method

}
