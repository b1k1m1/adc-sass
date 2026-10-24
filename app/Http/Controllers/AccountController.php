<?php

namespace App\Http\Controllers;

use App\Models\AccountMaster;
use App\Models\Faglmsacctcategory;
use App\Models\Fagltrjournal;
use App\Models\Fashmsbank;
use App\Models\Fashtran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\TrialBalanceMonthwiseExcelExport;
use App\Exports\TraditionalBankReconciliationExport;
use function Laravel\Prompts\table;
use App\Exports\BalanceSheetExport;
use App\Exports\ProfitLossExport;
use App\Exports\ProfitLossComparisonExport;
use App\Exports\BankReconciliationSimpleExport;
use App\Exports\BankReconciliationHistoryExport;
use Mpdf\Mpdf;
use Maatwebsite\Excel\Facades\Excel;


// --- Then your controller class starts here ---
class AccountController extends Controller
{

    public function AccountsDashboard()
    {

        return view('accounts.accounts_index', ['title' => 'Account Management', 'module' => 'accounts']);
    } // End Method

    public function EnterPayment()
    {

        $fashbanks = Fashmsbank::all();
        $accounts = AccountMaster::orderBy('party_nm')->get();
        $nextId = Fashtran::selectRaw('MAX(CAST(trans_no AS UNSIGNED)) as maxid')->value('maxid') + 1;

        return view('accounts.accounts_bank_payment', compact('fashbanks', 'accounts', 'nextId'), ['title' => 'Account Management', 'module' => 'accounts']);
    } // End Method

    // public function StorePayment(Request $request)
    // {
    //     $user = Auth::user();
    //     $company = DB::table('gnmscomp')->first();

    //     $validated = $request->validate([
    //         'trans_no'   => 'required|numeric',
    //         'trans_dt'   => 'required|date',
    //         'party_cd'   => 'required|string|max:18',
    //         'account_id' => 'required|string|max:18',
    //         'payto_name' => 'nullable|string',
    //         'amount'     => 'required',
    //         'ref_no'     => 'nullable|string',
    //         'remarks'    => 'nullable|string',
    //         'check_no'   => 'nullable|string',
    //         'check_dt'   => 'nullable|date',
    //         'clear_flag' => 'nullable|string',
    //         'post_flag'  => 'nullable|string',
    //     ]);

    //     $amount = (float) str_replace(
    //         ',',
    //         '',
    //         (string) ($validated['amount'] ?? 0)
    //     );

    //     if ($amount <= 0) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'amount' => 'Payment amount must be greater than zero.',
    //             ]);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Normalize account codes
    //     |--------------------------------------------------------------------------
    //     |
    //     | partyCd = Expense, vendor, or other debit account
    //     | bankId  = Bank account being credited
    //     |
    //     */

    //     $partyCd = $this->normalizeAccountId(
    //         $validated['party_cd']
    //     );

    //     $bankId = $this->normalizeAccountId(
    //         $validated['account_id']
    //     );

    //     DB::transaction(function () use (
    //         $validated,
    //         $amount,
    //         $partyCd,
    //         $bankId,
    //         $user,
    //         $company
    //     ) {
    //         $transNo = $validated['trans_no'];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Serial 000: Debit expense/vendor/other account
    //         |--------------------------------------------------------------------------
    //         */

    //         DB::table('fashtrans')->insert([
    //             'trans_no'     => $transNo,
    //             'trans_dt'     => $validated['trans_dt'],
    //             'serial_no'    => '000',
    //             'trans_type'   => 'PAYM',

    //             'party_cd'     => $partyCd,
    //             'partysub_cd'  => null,

    //             'payto_name'   => $validated['payto_name'] ?? null,
    //             'debit_amt'    => $amount,
    //             'credit_amt'   => 0,

    //             'ref_no'       => $validated['ref_no'] ?? null,
    //             'remarks'      => $validated['remarks'] ?? null,
    //             'check_no'     => $validated['check_no'] ?? null,
    //             'check_dt'     => $validated['check_dt'] ?? null,

    //             'clear_flag'   => $validated['clear_flag'] ?? null,
    //             'post_flag'    => $validated['post_flag'] ?? null,
    //             'action_flag'  => 'O',
    //             'trans_flag'   => 'A',

    //             'company_id'   => $company->comp_cd,
    //             'update_flag'  => 'Y',
    //             'user_cd'      => $user?->user_cd,
    //             'update_dt'    => now(),

    //             'account_flag' => 'V',

    //             // Corresponding bank account
    //             'bank_id'      => $bankId,

    //             'comments'     => null,
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Serial 101: Credit bank account
    //         |--------------------------------------------------------------------------
    //         */

    //         DB::table('fashtrans')->insert([
    //             'trans_no'     => $transNo,
    //             'trans_dt'     => $validated['trans_dt'],
    //             'serial_no'    => '101',
    //             'trans_type'   => 'PAYM',

    //             'party_cd'     => $bankId,
    //             'partysub_cd'  => null,

    //             'payto_name'   => $validated['payto_name'] ?? null,
    //             'debit_amt'    => 0,
    //             'credit_amt'   => $amount,

    //             'ref_no'       => $validated['ref_no'] ?? null,
    //             'remarks'      => $validated['remarks'] ?? null,
    //             'check_no'     => $validated['check_no'] ?? null,
    //             'check_dt'     => $validated['check_dt'] ?? null,

    //             'clear_flag'   => $validated['clear_flag'] ?? null,
    //             'post_flag'    => $validated['post_flag'] ?? null,
    //             'action_flag'  => null,
    //             'trans_flag'   => 'A',

    //             'company_id'   => $company->comp_cd,
    //             'update_flag'  => 'Y',
    //             'user_cd'      => $user?->user_cd,
    //             'update_dt'    => now(),

    //             'account_flag' => 'B',

    //             // Corresponding expense/vendor account
    //             'bank_id'      => $partyCd,

    //             'comments'     => null,
    //         ]);
    //     });

    //     $notification = [
    //         'message' => 'Payment Record Inserted Successfully',
    //         'alert-type' => 'success',
    //     ];

    //     return redirect()->back()->with($notification);

    // } // End Method

    public function StorePayment(Request $request)
    {
        $user = Auth::user();
        $company = DB::table('gnmscomp')->first();

        $validated = $request->validate([
            'trans_no'   => 'required|numeric',
            'trans_dt'   => 'required|string',
            'party_cd'   => 'required|string|max:18',
            'account_id' => 'required|string|max:18',
            'payto_name' => 'nullable|string',
            'amount'     => 'required',
            'ref_no'     => 'nullable|string',
            'remarks'    => 'nullable|string',
            'check_no'   => 'nullable|string',
            'check_dt'   => 'nullable|string',
            'clear_flag' => 'nullable|string',
            'post_flag'  => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Convert MM/DD/YYYY dates from Flatpickr to MySQL YYYY-MM-DD
        |--------------------------------------------------------------------------
        */

        try {
            $transDate = \Carbon\Carbon::createFromFormat(
                'm/d/Y',
                $validated['trans_dt']
            )->format('Y-m-d');

            $checkDate = !empty($validated['check_dt'])
                ? \Carbon\Carbon::createFromFormat(
                    'm/d/Y',
                    $validated['check_dt']
                )->format('Y-m-d')
                : null;
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'trans_dt' => 'Please enter a valid transaction date in MM/DD/YYYY format.',
                    'check_dt' => 'Please enter a valid check date in MM/DD/YYYY format.',
                ]);
        }

        $amount = (float) str_replace(
            ',',
            '',
            (string) ($validated['amount'] ?? 0)
        );

        if ($amount <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize account codes
        |--------------------------------------------------------------------------
        |
        | partyCd = Expense, vendor, or other debit account
        | bankId  = Bank account being credited
        |
        */

        $partyCd = $this->normalizeAccountId(
            $validated['party_cd']
        );

        $bankId = $this->normalizeAccountId(
            $validated['account_id']
        );

        DB::transaction(function () use (
            $validated,
            $amount,
            $partyCd,
            $bankId,
            $user,
            $company,
            $transDate,
            $checkDate
        ) {
            $transNo = $validated['trans_no'];

            /*
            |--------------------------------------------------------------------------
            | Serial 000: Debit expense/vendor/other account
            |--------------------------------------------------------------------------
            */

            DB::table('fashtrans')->insert([
                'trans_no'     => $transNo,
                'trans_dt'     => $transDate,
                'serial_no'    => '000',
                'trans_type'   => 'PAYM',

                'party_cd'     => $partyCd,
                'partysub_cd'  => null,

                'payto_name'   => $validated['payto_name'] ?? null,
                'debit_amt'    => $amount,
                'credit_amt'   => 0,

                'ref_no'       => $validated['ref_no'] ?? null,
                'remarks'      => $validated['remarks'] ?? null,
                'check_no'     => $validated['check_no'] ?? null,
                'check_dt'     => $checkDate,

                'clear_flag'   => $validated['clear_flag'] ?? null,
                'post_flag'    => $validated['post_flag'] ?? null,
                'action_flag'  => 'O',
                'trans_flag'   => 'A',

                'company_id'   => $company->comp_cd,
                'update_flag'  => 'Y',
                'user_cd'      => $user?->user_cd,
                'update_dt'    => now(),

                'account_flag' => 'V',

                // Corresponding bank account
                'bank_id'      => $bankId,

                'comments'     => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Serial 101: Credit bank account
            |--------------------------------------------------------------------------
            */

            DB::table('fashtrans')->insert([
                'trans_no'     => $transNo,
                'trans_dt'     => $transDate,
                'serial_no'    => '101',
                'trans_type'   => 'PAYM',

                'party_cd'     => $bankId,
                'partysub_cd'  => null,

                'payto_name'   => $validated['payto_name'] ?? null,
                'debit_amt'    => 0,
                'credit_amt'   => $amount,

                'ref_no'       => $validated['ref_no'] ?? null,
                'remarks'      => $validated['remarks'] ?? null,
                'check_no'     => $validated['check_no'] ?? null,
                'check_dt'     => $checkDate,

                'clear_flag'   => $validated['clear_flag'] ?? null,
                'post_flag'    => $validated['post_flag'] ?? null,
                'action_flag'  => null,
                'trans_flag'   => 'A',

                'company_id'   => $company->comp_cd,
                'update_flag'  => 'Y',
                'user_cd'      => $user?->user_cd,
                'update_dt'    => now(),

                'account_flag' => 'B',

                // Corresponding expense/vendor account
                'bank_id'      => $partyCd,

                'comments'     => null,
            ]);
        });

        $notification = [
            'message' => 'Payment Record Inserted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    } // End Method

    // public function ListPayment(Request $request)
    // {
    //     if ($request->ajax()) {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Do not add orderBy() here
    //         |--------------------------------------------------------------------------
    //         |
    //         | DataTables supplies the initial sorting and allows the user to change
    //         | the sorting by clicking any sortable column.
    //         |
    //         */

    //         $query = DB::table('fashtrans')
    //             ->select(
    //                 'trans_no',
    //                 'trans_dt',
    //                 'payto_name',
    //                 'check_no',
    //                 'debit_amt'
    //             )
    //             ->where('serial_no', '000')
    //             ->where('trans_type', 'PAYM')
    //             ->where('trans_flag', 'A')
    //             ->where('debit_amt', '>=', 0);

    //         return DataTables::of($query)
    //             ->addIndexColumn()

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Display transaction date as MM/DD/YYYY
    //             |--------------------------------------------------------------------------
    //             |
    //             | With server-side processing, sorting still uses the actual
    //             | database trans_dt column.
    //             |
    //             */

    //             ->editColumn('trans_dt', function ($row) {
    //                 return $row->trans_dt
    //                     ? Carbon::parse($row->trans_dt)->format('m/d/Y')
    //                     : '';
    //             })

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Format amount
    //             |--------------------------------------------------------------------------
    //             */

    //             ->editColumn('debit_amt', function ($row) {
    //                 return '<div class="text-end">'
    //                     . number_format((float) $row->debit_amt, 2)
    //                     . '</div>';
    //             })

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Edit button
    //             |--------------------------------------------------------------------------
    //             */

    //             ->addColumn('action', function ($row) {
    //                 $transactionDate = Carbon::parse($row->trans_dt)
    //                     ->format('Y-m-d');

    //                 $url = route('edit.payment', [
    //                     'trans_no' => $row->trans_no,
    //                     'trans_dt' => $transactionDate,
    //                 ]);

    //                 return '<a href="' . $url . '"
    //                         class="btn btn-primary btn-sm">
    //                             Edit
    //                             <i class="mdi mdi-dots-vertical ms-1"></i>
    //                         </a>';
    //             })

    //             ->rawColumns([
    //                 'debit_amt',
    //                 'action',
    //             ])

    //             ->make(true);
    //     }

    //     return view('accounts.accounts_bank_payment_list');

    // } // End Method

    // public function ListPayment(Request $request)
    // {
    //     if ($request->ajax()) {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Determine selected date range
    //         |--------------------------------------------------------------------------
    //         |
    //         | When dates are not supplied, default to the current calendar month.
    //         |
    //         */

    //         $startDate = $request->filled('start_date')
    //             ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
    //             : Carbon::now()->startOfMonth()->startOfDay();

    //         $endDate = $request->filled('end_date')
    //             ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
    //             : Carbon::now()->endOfMonth()->endOfDay();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Payment query
    //         |--------------------------------------------------------------------------
    //         |
    //         | Do not add orderBy() here. DataTables supplies the initial sorting
    //         | and allows the user to sort by any sortable column.
    //         |
    //         */

    //         $query = DB::table('fashtrans')
    //             ->select(
    //                 'trans_no',
    //                 'trans_dt',
    //                 'payto_name',
    //                 'check_no',
    //                 'debit_amt'
    //             )
    //             ->where('serial_no', '000')
    //             ->where('trans_type', 'PAYM')
    //             ->where('trans_flag', 'A')
    //             ->where('debit_amt', '>=', 0);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Apply selected date filter
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($request->date_period !== 'all_dates') {
    //             $query->whereDate('trans_dt', '>=', $startDate->format('Y-m-d'))
    //                 ->whereDate('trans_dt', '<=', $endDate->format('Y-m-d'));
    //         }

    //         return DataTables::of($query)
    //             ->addIndexColumn()

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Display transaction date as MM/DD/YYYY
    //             |--------------------------------------------------------------------------
    //             */

    //             ->editColumn('trans_dt', function ($row) {
    //                 return $row->trans_dt
    //                     ? Carbon::parse($row->trans_dt)->format('m/d/Y')
    //                     : '';
    //             })

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Format amount
    //             |--------------------------------------------------------------------------
    //             */

    //             ->editColumn('debit_amt', function ($row) {
    //                 return '<div class="text-end">'
    //                     . number_format((float) $row->debit_amt, 2)
    //                     . '</div>';
    //             })

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Edit button
    //             |--------------------------------------------------------------------------
    //             */

    //             ->addColumn('action', function ($row) {
    //                 $transactionDate = Carbon::parse($row->trans_dt)
    //                     ->format('Y-m-d');

    //                 $url = route('edit.payment', [
    //                     'trans_no' => $row->trans_no,
    //                     'trans_dt' => $transactionDate,
    //                 ]);

    //                 return '<a href="' . $url . '"
    //                         class="btn btn-primary btn-sm">
    //                             Edit
    //                             <i class="mdi mdi-dots-vertical ms-1"></i>
    //                         </a>';
    //             })

    //             ->rawColumns([
    //                 'debit_amt',
    //                 'action',
    //             ])

    //             ->make(true);
    //     }

    //     return view('accounts.accounts_bank_payment_list');

    // } // End Method

    public function ListPayment(Request $request)
    {
        if ($request->ajax()) {

            /*
            |--------------------------------------------------------------------------
            | Determine selected date range
            |--------------------------------------------------------------------------
            */

            $startDate = $request->filled('start_date')
                ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth()->startOfDay();

            $endDate = $request->filled('end_date')
                ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
                : Carbon::now()->endOfMonth()->endOfDay();


            /*
            |--------------------------------------------------------------------------
            | Load account heads once
            |--------------------------------------------------------------------------
            |
            | No JOIN is used.
            |
            | Result example:
            |
            | [
            |     '011' => 'Office Expense',
            |     '025' => 'Utilities',
            | ]
            |
            */

            $accountHeads = DB::table('accountmasters')
                ->pluck('party_nm', 'party_cd');


            /*
            |--------------------------------------------------------------------------
            | Payment query
            |--------------------------------------------------------------------------
            */

            $query = DB::table('fashtrans')
                ->select(
                    'trans_no',
                    'trans_dt',
                    'party_cd',
                    'payto_name',
                    'check_no',
                    'debit_amt'
                )
                ->where('serial_no', '000')
                ->where('trans_type', 'PAYM')
                ->where('trans_flag', 'A')
                ->where('debit_amt', '>=', 0);


            /*
            |--------------------------------------------------------------------------
            | Apply selected date filter
            |--------------------------------------------------------------------------
            */

            if ($request->date_period !== 'all_dates') {

                $query->whereDate(
                    'trans_dt',
                    '>=',
                    $startDate->format('Y-m-d')
                )
                    ->whereDate(
                        'trans_dt',
                        '<=',
                        $endDate->format('Y-m-d')
                    );
            }


            return DataTables::of($query)

                ->addIndexColumn()


                /*
                |--------------------------------------------------------------------------
                | Transaction Date
                |--------------------------------------------------------------------------
                */

                ->editColumn('trans_dt', function ($row) {

                    return $row->trans_dt
                        ? Carbon::parse($row->trans_dt)->format('m/d/Y')
                        : '';
                })


                /*
                |--------------------------------------------------------------------------
                | Account Head
                |--------------------------------------------------------------------------
                |
                | No extra SQL query is executed here.
                |
                */

                ->addColumn('account_head', function ($row) use ($accountHeads) {

                    $partyCode = trim((string) ($row->party_cd ?? ''));

                    if ($partyCode === '') {
                        return '';
                    }

                    $partyName = trim(
                        (string) ($accountHeads->get($partyCode) ?? '')
                    );

                    if ($partyName !== '') {
                        return $partyCode . ' - ' . $partyName;
                    }

                    return $partyCode;
                })


                /*
                |--------------------------------------------------------------------------
                | Sort Account Head
                |--------------------------------------------------------------------------
                |
                | Since accountmasters is not joined to the SQL query,
                | sorting is performed using fashtrans.party_cd.
                |
                */

                ->orderColumn('account_head', function ($query, $order) {

                    $query->orderBy('party_cd', $order);
                })


                /*
                |--------------------------------------------------------------------------
                | Amount
                |--------------------------------------------------------------------------
                */

                ->editColumn('debit_amt', function ($row) {

                    return '<div class="text-end">'
                        . number_format((float) $row->debit_amt, 2)
                        . '</div>';
                })


                /*
                |--------------------------------------------------------------------------
                | Edit Button
                |--------------------------------------------------------------------------
                */

                ->addColumn('action', function ($row) {

                    $transactionDate = Carbon::parse($row->trans_dt)
                        ->format('Y-m-d');

                    $url = route('edit.payment', [
                        'trans_no' => $row->trans_no,
                        'trans_dt' => $transactionDate,
                    ]);

                    return '<a href="' . $url . '"
                            class="btn btn-primary btn-sm">
                                Edit
                                <i class="mdi mdi-dots-vertical ms-1"></i>
                            </a>';
                })


                ->rawColumns([
                    'debit_amt',
                    'action',
                ])

                ->make(true);
        }


        return view('accounts.accounts_bank_payment_list');
    } // End Method

    public function EditPayment($trans_no, $trans_dt)
    {
        $fashbanks = Fashmsbank::all();

        $accounts = AccountMaster::orderBy('party_nm')->get();

        /*
        |--------------------------------------------------------------------------
        | Normalize the date received from the route
        |--------------------------------------------------------------------------
        */

        try {
            $transactionDate = Carbon::parse($trans_dt)->format('Y-m-d');
        } catch (\Throwable $e) {
            abort(404, 'Invalid payment transaction date.');
        }

        $fashpayment = Fashtran::where('trans_no', $trans_no)
            ->whereDate('trans_dt', $transactionDate)
            ->where('trans_flag', 'A')
            ->where('trans_type', 'PAYM')
            ->where('serial_no', '000')
            ->firstOrFail();

        return view(
            'accounts.accounts_edit_bank_payment',
            compact(
                'fashpayment',
                'fashbanks',
                'accounts'
            ),
            [
                'title' => 'Account Management',
                'module' => 'accounts',
            ]
        );
    } // End Method

    // public function UpdatePayment(Request $request)
    // {
    //     $user = Auth::user();

    //     $validated = $request->validate([
    //         'trans_no'   => 'required|numeric',
    //         'trans_dt'   => 'required|date',
    //         'party_cd'   => 'required|string|max:18',
    //         'bank_id'    => 'required|string|max:18',
    //         'payto_name' => 'nullable|string|max:100',
    //         'amount'     => 'required',
    //         'ref_no'     => 'nullable|string|max:50',
    //         'remarks'    => 'nullable|string|max:255',
    //         'check_no'   => 'nullable|string|max:50',
    //         'check_dt'   => 'nullable|date',
    //         'clear_flag' => 'nullable|string|max:1',
    //         'post_flag'  => 'nullable|string|max:1',
    //     ]);

    //     $transNo = $validated['trans_no'];

    //     $amount = (float) str_replace(
    //         ',',
    //         '',
    //         (string) ($validated['amount'] ?? 0)
    //     );

    //     if ($amount <= 0) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'amount' => 'Payment amount must be greater than zero.',
    //             ]);
    //     }

    //     $transDate = Carbon::parse(
    //         $validated['trans_dt']
    //     )->format('Y-m-d');

    //     $checkDate = !empty($validated['check_dt'])
    //         ? Carbon::parse($validated['check_dt'])->format('Y-m-d')
    //         : null;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Preserve leading-zero account codes
    //     |--------------------------------------------------------------------------
    //     */

    //     $partyCd = $this->normalizeAccountId(
    //         $validated['party_cd']
    //     );

    //     $bankId = $this->normalizeAccountId(
    //         $validated['bank_id']
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Verify that both payment rows exist
    //     |--------------------------------------------------------------------------
    //     */

    //     $debitRowExists = DB::table('fashtrans')
    //         ->where('trans_no', $transNo)
    //         ->where('serial_no', '000')
    //         ->where('trans_type', 'PAYM')
    //         ->exists();

    //     $creditRowExists = DB::table('fashtrans')
    //         ->where('trans_no', $transNo)
    //         ->where('serial_no', '101')
    //         ->where('trans_type', 'PAYM')
    //         ->exists();

    //     if (!$debitRowExists || !$creditRowExists) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'payment' =>
    //                     'Both payment transaction lines could not be found.',
    //             ]);
    //     }

    //     try {
    //         DB::transaction(function () use (
    //             $validated,
    //             $transNo,
    //             $amount,
    //             $transDate,
    //             $checkDate,
    //             $partyCd,
    //             $bankId,
    //             $user
    //         ) {
    //             /*
    //             |--------------------------------------------------------------------------
    //             | Serial 000: Debit expense/vendor account
    //             |--------------------------------------------------------------------------
    //             */

    //             DB::table('fashtrans')
    //                 ->where('trans_no', $transNo)
    //                 ->where('serial_no', '000')
    //                 ->where('trans_type', 'PAYM')
    //                 ->update([
    //                     'trans_dt'     => $transDate,
    //                     'party_cd'     => $partyCd,
    //                     'payto_name'   => $validated['payto_name'] ?? null,

    //                     'debit_amt'    => $amount,
    //                     'credit_amt'   => 0,

    //                     'ref_no'       => $validated['ref_no'] ?? null,
    //                     'remarks'      => $validated['remarks'] ?? null,
    //                     'check_no'     => $validated['check_no'] ?? null,
    //                     'check_dt'     => $checkDate,

    //                     'clear_flag'   => $validated['clear_flag'] ?? null,
    //                     'post_flag'    => $validated['post_flag'] ?? null,

    //                     'bank_id'      => $bankId,

    //                     'user_cd'      => $user?->user_cd,
    //                     'update_flag'  => 'Y',
    //                     'update_dt'    => now(),
    //                 ]);

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Serial 101: Credit bank account
    //             |--------------------------------------------------------------------------
    //             */

    //             DB::table('fashtrans')
    //                 ->where('trans_no', $transNo)
    //                 ->where('serial_no', '101')
    //                 ->where('trans_type', 'PAYM')
    //                 ->update([
    //                     'trans_dt'     => $transDate,
    //                     'party_cd'     => $bankId,
    //                     'payto_name'   => $validated['payto_name'] ?? null,

    //                     'debit_amt'    => 0,
    //                     'credit_amt'   => $amount,

    //                     'ref_no'       => $validated['ref_no'] ?? null,
    //                     'remarks'      => $validated['remarks'] ?? null,
    //                     'check_no'     => $validated['check_no'] ?? null,
    //                     'check_dt'     => $checkDate,

    //                     'clear_flag'   => $validated['clear_flag'] ?? null,
    //                     'post_flag'    => $validated['post_flag'] ?? null,

    //                     'bank_id'      => $partyCd,

    //                     'user_cd'      => $user?->user_cd,
    //                     'update_flag'  => 'Y',
    //                     'update_dt'    => now(),
    //                 ]);
    //         });
    //     } catch (\Throwable $e) {
    //         report($e);

    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'payment' => 'Unable to update payment: ' . $e->getMessage(),
    //             ]);
    //     }

    //     $notification = [
    //         'message' => 'Payment Record Updated Successfully',
    //         'alert-type' => 'success',
    //     ];

    //     return redirect()
    //         ->route('list.payment')
    //         ->with($notification);

    // } // End Method

    public function UpdatePayment(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'trans_no'   => 'required|numeric',
            'trans_dt'   => 'required|string',
            'party_cd'   => 'required|string|max:18',
            'bank_id'    => 'required|string|max:18',
            'payto_name' => 'nullable|string|max:100',
            'amount'     => 'required',
            'ref_no'     => 'nullable|string|max:50',
            'remarks'    => 'nullable|string|max:255',
            'check_no'   => 'nullable|string|max:50',
            'check_dt'   => 'nullable|string',
            'clear_flag' => 'nullable|string|max:1',
            'post_flag'  => 'nullable|string|max:1',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Convert MM/DD/YYYY dates from Flatpickr to MySQL YYYY-MM-DD
        |--------------------------------------------------------------------------
        */

        try {
            $transDate = Carbon::createFromFormat(
                'm/d/Y',
                $validated['trans_dt']
            )->format('Y-m-d');

            $checkDate = !empty($validated['check_dt'])
                ? Carbon::createFromFormat(
                    'm/d/Y',
                    $validated['check_dt']
                )->format('Y-m-d')
                : null;
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'trans_dt' => 'Please enter a valid transaction date in MM/DD/YYYY format.',
                    'check_dt' => 'Please enter a valid check date in MM/DD/YYYY format.',
                ]);
        }

        $transNo = $validated['trans_no'];

        $amount = (float) str_replace(
            ',',
            '',
            (string) ($validated['amount'] ?? 0)
        );

        $amount = (float) str_replace(
            ',',
            '',
            (string) ($validated['amount'] ?? 0)
        );

        if ($amount < 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount' => 'Payment amount cannot be negative.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Preserve leading-zero account codes
        |--------------------------------------------------------------------------
        */

        $partyCd = $this->normalizeAccountId(
            $validated['party_cd']
        );

        $bankId = $this->normalizeAccountId(
            $validated['bank_id']
        );

        /*
        |--------------------------------------------------------------------------
        | Verify that both payment rows exist
        |--------------------------------------------------------------------------
        */

        $debitRowExists = DB::table('fashtrans')
            ->where('trans_no', $transNo)
            ->where('serial_no', '000')
            ->where('trans_type', 'PAYM')
            ->exists();

        $creditRowExists = DB::table('fashtrans')
            ->where('trans_no', $transNo)
            ->where('serial_no', '101')
            ->where('trans_type', 'PAYM')
            ->exists();

        if (!$debitRowExists || !$creditRowExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'payment' => 'Both payment transaction lines could not be found.',
                ]);
        }

        try {
            DB::transaction(function () use (
                $validated,
                $transNo,
                $amount,
                $transDate,
                $checkDate,
                $partyCd,
                $bankId,
                $user
            ) {
                /*
                |--------------------------------------------------------------------------
                | Serial 000: Debit expense/vendor account
                |--------------------------------------------------------------------------
                */

                DB::table('fashtrans')
                    ->where('trans_no', $transNo)
                    ->where('serial_no', '000')
                    ->where('trans_type', 'PAYM')
                    ->update([
                        'trans_dt'     => $transDate,
                        'party_cd'     => $partyCd,
                        'payto_name'   => $validated['payto_name'] ?? null,

                        'debit_amt'    => $amount,
                        'credit_amt'   => 0,

                        'ref_no'       => $validated['ref_no'] ?? null,
                        'remarks'      => $validated['remarks'] ?? null,
                        'check_no'     => $validated['check_no'] ?? null,
                        'check_dt'     => $checkDate,

                        'clear_flag'   => $validated['clear_flag'] ?? null,
                        'post_flag'    => $validated['post_flag'] ?? null,

                        'bank_id'      => $bankId,

                        'user_cd'      => $user?->user_cd,
                        'update_flag'  => 'Y',
                        'update_dt'    => now(),
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Serial 101: Credit bank account
                |--------------------------------------------------------------------------
                */

                DB::table('fashtrans')
                    ->where('trans_no', $transNo)
                    ->where('serial_no', '101')
                    ->where('trans_type', 'PAYM')
                    ->update([
                        'trans_dt'     => $transDate,
                        'party_cd'     => $bankId,
                        'payto_name'   => $validated['payto_name'] ?? null,

                        'debit_amt'    => 0,
                        'credit_amt'   => $amount,

                        'ref_no'       => $validated['ref_no'] ?? null,
                        'remarks'      => $validated['remarks'] ?? null,
                        'check_no'     => $validated['check_no'] ?? null,
                        'check_dt'     => $checkDate,

                        'clear_flag'   => $validated['clear_flag'] ?? null,
                        'post_flag'    => $validated['post_flag'] ?? null,

                        'bank_id'      => $partyCd,

                        'user_cd'      => $user?->user_cd,
                        'update_flag'  => 'Y',
                        'update_dt'    => now(),
                    ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'payment' => 'Unable to update payment: ' . $e->getMessage(),
                ]);
        }

        $notification = [
            'message' => 'Payment Record Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->route('list.payment')
            ->with($notification);
    } // End Method

    public function EnterDeposit()
    {

        $fashbanks = Fashmsbank::all();
        $accounts = AccountMaster::orderBy('party_nm')->get();
        $nextId = Fashtran::selectRaw('MAX(CAST(trans_no AS UNSIGNED)) as maxid')->value('maxid') + 1;

        return view('accounts.accounts_bank_deposit', compact('fashbanks', 'accounts', 'nextId'), ['title' => 'Account Management', 'module' => 'accounts']);
    } // End Method

    public function StoreDeposit(Request $request)
    {
        $user = Auth::user();
        $company = DB::table('gnmscomp')->first();

        $validated = $request->validate([
            'trans_no'   => 'required|numeric',
            'trans_dt'   => 'required|date',
            'party_cd'   => 'required|string|max:18',
            'account_id' => 'required|string|max:18',
            'payto_name' => 'nullable|string',
            'amount'     => 'required',
            'ref_no'     => 'nullable|string',
            'remarks'    => 'nullable|string',
            'check_no'   => 'nullable|string',
            'check_dt'   => 'nullable|date',
            'clear_flag' => 'nullable|string',
            'post_flag'  => 'nullable|string',
        ]);

        $amount = (float) str_replace(
            ',',
            '',
            (string) ($validated['amount'] ?? 0)
        );

        if ($amount <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount' => 'Deposit amount must be greater than zero.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize account codes
        |--------------------------------------------------------------------------
        |
        | partyCd = Income/customer/other credit account
        | bankId  = Bank account receiving the deposit
        |
        */

        $partyCd = $this->normalizeAccountId(
            $validated['party_cd']
        );

        $bankId = $this->normalizeAccountId(
            $validated['account_id']
        );

        DB::transaction(function () use (
            $validated,
            $amount,
            $partyCd,
            $bankId,
            $user,
            $company
        ) {
            $transNo = $validated['trans_no'];

            /*
            |--------------------------------------------------------------------------
            | Serial 000: Credit income/customer/other account
            |--------------------------------------------------------------------------
            */

            DB::table('fashtrans')->insert([
                'trans_no'     => $transNo,
                'trans_dt'     => $validated['trans_dt'],
                'serial_no'    => '000',
                'trans_type'   => 'DEPS',

                'party_cd'     => $partyCd,
                'partysub_cd'  => null,

                'payto_name'   => $validated['payto_name'] ?? null,
                'debit_amt'    => 0,
                'credit_amt'   => $amount,

                'ref_no'       => $validated['ref_no'] ?? null,
                'remarks'      => $validated['remarks'] ?? null,
                'check_no'     => $validated['check_no'] ?? null,
                'check_dt'     => $validated['check_dt'] ?? null,

                'clear_flag'   => $validated['clear_flag'] ?? null,
                'post_flag'    => $validated['post_flag'] ?? null,
                'action_flag'  => 'O',
                'trans_flag'   => 'A',

                'company_id'   => $company->comp_cd,
                'update_flag'  => 'Y',
                'user_cd'      => $user?->user_cd,
                'update_dt'    => now(),

                'account_flag' => 'C',

                // Corresponding bank account
                'bank_id'      => $bankId,

                'comments'     => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Serial 101: Debit bank account
            |--------------------------------------------------------------------------
            */

            DB::table('fashtrans')->insert([
                'trans_no'     => $transNo,
                'trans_dt'     => $validated['trans_dt'],
                'serial_no'    => '101',
                'trans_type'   => 'DEPS',

                'party_cd'     => $bankId,
                'partysub_cd'  => null,

                'payto_name'   => $validated['payto_name'] ?? null,
                'debit_amt'    => $amount,
                'credit_amt'   => 0,

                'ref_no'       => $validated['ref_no'] ?? null,
                'remarks'      => $validated['remarks'] ?? null,
                'check_no'     => $validated['check_no'] ?? null,
                'check_dt'     => $validated['check_dt'] ?? null,

                'clear_flag'   => $validated['clear_flag'] ?? null,
                'post_flag'    => $validated['post_flag'] ?? null,
                'action_flag'  => null,
                'trans_flag'   => 'A',

                'company_id'   => $company->comp_cd,
                'update_flag'  => 'Y',
                'user_cd'      => $user?->user_cd,
                'update_dt'    => now(),

                'account_flag' => 'B',

                // Corresponding income/customer account
                'bank_id'      => $partyCd,

                'comments'     => null,
            ]);
        });

        $notification = [
            'message' => 'Deposit Record Inserted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    } // End Method

    public function ListDeposit(Request $request)
    {
        if ($request->ajax()) {

            /*
            |--------------------------------------------------------------------------
            | Do not place orderBy() here
            |--------------------------------------------------------------------------
            |
            | DataTables will provide the initial ordering and will allow users
            | to sort by any sortable column.
            |
            */

            $query = DB::table('fashtrans')
                ->select(
                    'trans_no',
                    'trans_dt',
                    'payto_name',
                    'check_no',
                    'credit_amt'
                )
                ->where('serial_no', '000')
                ->where('trans_type', 'DEPS')
                ->where('trans_flag', 'A')
                ->where('credit_amt', '>', 0);

            return DataTables::of($query)

                ->addIndexColumn()

                /*
                |--------------------------------------------------------------------------
                | Display date as MM/DD/YYYY
                |--------------------------------------------------------------------------
                |
                | Sorting will still use the database trans_dt field.
                |
                */

                ->editColumn('trans_dt', function ($row) {
                    return $row->trans_dt
                        ? \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y')
                        : '';
                })

                /*
                |--------------------------------------------------------------------------
                | Format deposit amount
                |--------------------------------------------------------------------------
                */

                ->editColumn('credit_amt', function ($row) {
                    return '<div class="text-end">'
                        . number_format((float) $row->credit_amt, 2)
                        . '</div>';
                })

                /*
                |--------------------------------------------------------------------------
                | Edit button
                |--------------------------------------------------------------------------
                */

                ->addColumn('action', function ($row) {

                    $transactionDate = \Carbon\Carbon::parse($row->trans_dt)
                        ->format('Y-m-d');

                    $url = route('edit.deposit', [
                        'trans_no' => $row->trans_no,
                        'trans_dt' => $transactionDate,
                    ]);

                    return '<a href="' . $url . '"
                            class="btn btn-primary btn-sm">
                                Edit
                                <i class="mdi mdi-dots-vertical ms-1"></i>
                            </a>';
                })

                ->rawColumns([
                    'credit_amt',
                    'action',
                ])

                ->make(true);
        }

        return view('accounts.accounts_bank_deposit_list');
    } // End Method

    public function EditDeposit($trans_no, $trans_dt)
    {

        $fashbanks = Fashmsbank::all();
        $accounts = AccountMaster::orderBy('party_nm')->get();
        $fashdeposit = Fashtran::where('trans_no', $trans_no)
            ->where('trans_dt', $trans_dt)
            ->where('trans_flag', 'A')
            ->where('trans_type', 'DEPS')
            ->where('serial_no', '000')
            ->firstOrFail();
        return view('accounts.accounts_edit_bank_deposit', compact('fashdeposit', 'fashbanks', 'accounts'), ['title' => 'Account Management', 'module' => 'accounts']);
    } // End Method

    public function UpdateDeposit(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'trans_no'   => 'required|numeric',
            'trans_dt'   => 'required|date',
            'party_cd'   => 'required|string|max:18',
            'bank_id'    => 'required|string|max:18',
            'payto_name' => 'nullable|string',
            'amount'     => 'required',
            'ref_no'     => 'nullable|string',
            'remarks'    => 'nullable|string',
            'check_no'   => 'nullable|string',
            'check_dt'   => 'nullable|date',
            'clear_flag' => 'nullable|string',
            'post_flag'  => 'nullable|string',
        ]);

        $transNo = $validated['trans_no'];

        $amount = (float) str_replace(
            ',',
            '',
            (string) ($validated['amount'] ?? 0)
        );

        if ($amount <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount' => 'Deposit amount must be greater than zero.',
                ]);
        }

        $transDate = Carbon::parse(
            $validated['trans_dt']
        )->format('Y-m-d');

        $checkDate = !empty($validated['check_dt'])
            ? Carbon::parse($validated['check_dt'])->format('Y-m-d')
            : null;

        /*
        |--------------------------------------------------------------------------
        | Normalize both account codes
        |--------------------------------------------------------------------------
        */

        $partyCd = $this->normalizeAccountId(
            $validated['party_cd']
        );

        $bankId = $this->normalizeAccountId(
            $validated['bank_id']
        );

        DB::transaction(function () use (
            $validated,
            $transNo,
            $amount,
            $transDate,
            $checkDate,
            $partyCd,
            $bankId,
            $user
        ) {
            /*
            |--------------------------------------------------------------------------
            | Serial 000: Credit income/customer/other account
            |--------------------------------------------------------------------------
            */

            $updatedCreditRow = DB::table('fashtrans')
                ->where('trans_no', $transNo)
                ->where('serial_no', '000')
                ->where('trans_type', 'DEPS')
                ->where('trans_flag', 'A')
                ->update([
                    'trans_dt'     => $transDate,

                    'party_cd'     => $partyCd,

                    'payto_name'   => $validated['payto_name'] ?? null,
                    'debit_amt'    => 0,
                    'credit_amt'   => $amount,

                    'ref_no'       => $validated['ref_no'] ?? null,
                    'remarks'      => $validated['remarks'] ?? null,
                    'check_no'     => $validated['check_no'] ?? null,
                    'check_dt'     => $checkDate,

                    'clear_flag'   => $validated['clear_flag'] ?? null,
                    'post_flag'    => $validated['post_flag'] ?? null,

                    'user_cd'      => $user?->user_cd,
                    'update_dt'    => now(),

                    // Corresponding bank account
                    'bank_id'      => $bankId,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Serial 101: Debit bank account
            |--------------------------------------------------------------------------
            */

            $updatedDebitRow = DB::table('fashtrans')
                ->where('trans_no', $transNo)
                ->where('serial_no', '101')
                ->where('trans_type', 'DEPS')
                ->where('trans_flag', 'A')
                ->update([
                    'trans_dt'     => $transDate,

                    'party_cd'     => $bankId,

                    'payto_name'   => $validated['payto_name'] ?? null,
                    'debit_amt'    => $amount,
                    'credit_amt'   => 0,

                    'ref_no'       => $validated['ref_no'] ?? null,
                    'remarks'      => $validated['remarks'] ?? null,
                    'check_no'     => $validated['check_no'] ?? null,
                    'check_dt'     => $checkDate,

                    'clear_flag'   => $validated['clear_flag'] ?? null,
                    'post_flag'    => $validated['post_flag'] ?? null,

                    'user_cd'      => $user?->user_cd,
                    'update_dt'    => now(),

                    // Corresponding income/customer account
                    'bank_id'      => $partyCd,
                ]);

            /*
            * Laravel may return 0 when the submitted values are identical to
            * the existing values. Therefore, do not treat update() = 0 as an
            * error here.
            */
        });

        $notification = [
            'message' => 'Deposit Record Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('list.deposit')->with($notification);
    } // End Method

    // public function BankReport(Request $request){

    //     {
    //     // Get date range from request (or default to current month)

    //     $start_date = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
    //     $end_date = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
    //     $bank_id = $request->input('account_id', '');

    //     // Optionally: $bank_id = $request->input('bank_id');

    //     // 1. Company Info
    //     //$company = DB::table('gnmscomps')->first();
    //     $fashbanks = Fashmsbank::all();

    //     // 2. Opening balance (sum before start date)
    //     $op = DB::table('fashtrans')
    //         ->where('bank_id', $bank_id) // Uncomment if you filter by bank
    //         ->where('trans_dt', '<', $start_date)
    //         ->selectRaw('SUM(debit_amt) as debit, SUM(credit_amt) as credit')
    //         ->first();
    //     //3. Get opening balance from Journal
    //     // to be coded to get balance.

    //     $op_debit_amt = $op->debit ?? 0;
    //     $op_credit_amt = $op->credit ?? 0;

    //     // 3. Detail rows (within range)
    //     $rows = DB::table('fashtrans')
    //         ->join('accountmasters', 'fashtrans.party_cd', '=', 'accountmasters.party_cd')
    //         ->where('fashtrans.bank_id', $bank_id) // Uncomment if needed
    //         ->where('serial_no','000')
    //         ->whereBetween('fashtrans.trans_dt', [$start_date, $end_date])
    //         ->orderBy('fashtrans.trans_dt', 'asc')
    //         ->select(
    //             'fashtrans.*',
    //             'accountmasters.party_nm'
    //         )
    //         ->get();

    //     // 4. Totals for the range
    //     $totdr = $rows->sum('debit_amt');
    //     $totcr = $rows->sum('credit_amt');

    //     // 5. Closing balance (opening + period)
    //     $cl_debit_amt = $op_debit_amt + $totdr;
    //     $cl_credit_amt = $op_credit_amt + $totcr;

    //     return view('accounts.accounts_bank_report', compact(
    //         'rows','fashbanks',
    //         'start_date', 'end_date',
    //         'op_debit_amt', 'op_credit_amt',
    //         'cl_debit_amt', 'cl_credit_amt',
    //         'totdr', 'totcr'
    //     ));
    // }


    // }// End Method

    // public function BankReport(Request $request)
    // {
    //     // ---------------------------------------------------------------
    //     // 1. Get date range from request
    //     // ---------------------------------------------------------------
    //     $start_date = $request->input(
    //         'start_date',
    //         now()->startOfMonth()->format('Y-m-d')
    //     );

    //     $end_date = $request->input(
    //         'end_date',
    //         now()->endOfMonth()->format('Y-m-d')
    //     );

    //     $bank_id = $request->input('account_id', '');

    //     // ---------------------------------------------------------------
    //     // 2. Get Bank List
    //     // ---------------------------------------------------------------
    //     $fashbanks = Fashmsbank::all();

    //     // ---------------------------------------------------------------
    //     // 3. Determine Fiscal Year Start Date
    //     //
    //     // Accounting year is Calendar Year.
    //     //
    //     // Example:
    //     // Start Date       = 09/01/2026
    //     // Fiscal Start     = 01/01/2026
    //     // ---------------------------------------------------------------
    //     $startDate = \Carbon\Carbon::parse($start_date);

    //     $fiscal_start_date = $startDate
    //         ->copy()
    //         ->startOfYear()
    //         ->format('Y-m-d');

    //     // ---------------------------------------------------------------
    //     // 4. Get Beginning Balance from fagltrjournal
    //     //
    //     // Only use:
    //     // trans_type = BBAL
    //     // account_id = Selected Bank Account
    //     // trans_dt   = 01/01/YYYY
    //     //
    //     // Example:
    //     // Account 1101
    //     // Debit  = 69,178.73
    //     // Credit = 0.00
    //     //
    //     // Beginning Balance = Debit - Credit
    //     //
    //     // If there is NO BBAL entry, COALESCE will return 0.00.
    //     // ---------------------------------------------------------------
    //     $journalOpening = DB::table('fagltrjournal')
    //         ->where('account_id', $bank_id)
    //         ->where('trans_type', 'BBAL')
    //         ->whereDate('trans_dt', $fiscal_start_date)
    //         ->selectRaw('
    //             COALESCE(SUM(debit_amt), 0.00) as debit,
    //             COALESCE(SUM(credit_amt), 0.00) as credit
    //         ')
    //         ->first();

    //     $journal_debit = (float) ($journalOpening->debit ?? 0.00);
    //     $journal_credit = (float) ($journalOpening->credit ?? 0.00);

    //     // Beginning balance stored in GL
    //     $beginning_balance =
    //         $journal_debit
    //         - $journal_credit;

    //     // ---------------------------------------------------------------
    //     // 5. Get Prior Bank Activity
    //     //
    //     // From 01/01/YYYY through DAY BEFORE selected start date.
    //     //
    //     // Example:
    //     //
    //     // Report Start Date = 09/01/2026
    //     //
    //     // Transactions used:
    //     // 01/01/2026 through 08/31/2026
    //     //
    //     // If report starts 01/01/2026,
    //     // prior debit/credit remain 0.00.
    //     // ---------------------------------------------------------------
    //     $prior_debit = 0.00;
    //     $prior_credit = 0.00;

    //     if ($startDate->gt(
    //         \Carbon\Carbon::parse($fiscal_start_date)
    //     )) {

    //         $priorActivity = DB::table('fashtrans')
    //             ->where('bank_id', $bank_id)
    //             ->where('serial_no', '000')
    //             ->whereDate(
    //                 'trans_dt',
    //                 '>=',
    //                 $fiscal_start_date
    //             )
    //             ->whereDate(
    //                 'trans_dt',
    //                 '<',
    //                 $start_date
    //             )
    //             ->selectRaw('
    //                 COALESCE(SUM(debit_amt), 0.00) as debit,
    //                 COALESCE(SUM(credit_amt), 0.00) as credit
    //             ')
    //             ->first();

    //         $prior_debit =
    //             (float) ($priorActivity->debit ?? 0.00);

    //         $prior_credit =
    //             (float) ($priorActivity->credit ?? 0.00);
    //     }

    //     // ---------------------------------------------------------------
    //     // 6. Calculate Opening Balance
    //     //
    //     // Opening Balance =
    //     //
    //     // Beginning Balance from BBAL
    //     // + Prior Credits
    //     // - Prior Debits
    //     //
    //     // ---------------------------------------------------------------
    //     $opening_balance =
    //         $beginning_balance
    //         + $prior_credit
    //         - $prior_debit;

    //     // ---------------------------------------------------------------
    //     // Convert calculated opening balance into existing
    //     // Debit / Credit variables used by Blade.
    //     //
    //     // Positive Bank Balance = Debit Balance
    //     // Negative Bank Balance = Credit Balance
    //     // ---------------------------------------------------------------
    //     if ($opening_balance >= 0) {

    //         $op_debit_amt = $opening_balance;
    //         $op_credit_amt = 0.00;
    //     } else {

    //         $op_debit_amt = 0.00;
    //         $op_credit_amt = abs($opening_balance);
    //     }

    //     // ---------------------------------------------------------------
    //     // 7. Get Detail Rows Within Selected Report Date Range
    //     // ---------------------------------------------------------------
    //     $rows = DB::table('fashtrans')
    //         ->join(
    //             'accountmasters',
    //             'fashtrans.party_cd',
    //             '=',
    //             'accountmasters.party_cd'
    //         )
    //         ->where(
    //             'fashtrans.bank_id',
    //             $bank_id
    //         )
    //         ->where(
    //             'fashtrans.serial_no',
    //             '000'
    //         )
    //         ->whereDate(
    //             'fashtrans.trans_dt',
    //             '>=',
    //             $start_date
    //         )
    //         ->whereDate(
    //             'fashtrans.trans_dt',
    //             '<=',
    //             $end_date
    //         )
    //         ->orderBy(
    //             'fashtrans.trans_dt',
    //             'asc'
    //         )
    //         ->select(
    //             'fashtrans.*',
    //             'accountmasters.party_nm'
    //         )
    //         ->get();

    //     // ---------------------------------------------------------------
    //     // 8. Calculate Report Period Totals
    //     // ---------------------------------------------------------------
    //     $totdr = $rows->sum('debit_amt');

    //     $totcr = $rows->sum('credit_amt');

    //     // ---------------------------------------------------------------
    //     // 9. Calculate Closing Balance
    //     //
    //     // Closing Balance =
    //     //
    //     // Opening Balance
    //     // + Current Period Credits
    //     // - Current Period Debits
    //     // ---------------------------------------------------------------
    //     $closing_balance =
    //         $opening_balance
    //         + $totcr
    //         - $totdr;

    //     // ---------------------------------------------------------------
    //     // Convert Closing Balance into existing
    //     // Debit / Credit variables used by Blade.
    //     // ---------------------------------------------------------------
    //     if ($closing_balance >= 0) {

    //         $cl_debit_amt = $closing_balance;
    //         $cl_credit_amt = 0.00;
    //     } else {

    //         $cl_debit_amt = 0.00;
    //         $cl_credit_amt = abs($closing_balance);
    //     }

    //     // ---------------------------------------------------------------
    //     // 10. Return Bank Report
    //     // ---------------------------------------------------------------
    //     return view(
    //         'accounts.accounts_bank_report',
    //         compact(
    //             'rows',
    //             'fashbanks',
    //             'start_date',
    //             'end_date',
    //             'op_debit_amt',
    //             'op_credit_amt',
    //             'cl_debit_amt',
    //             'cl_credit_amt',
    //             'totdr',
    //             'totcr'
    //         )
    //     );
    // }
    // // End Method

    public function BankReport(Request $request)
    {
        // ---------------------------------------------------------------
        // 1. Get date range from request
        // ---------------------------------------------------------------
        $start_date = $request->input(
            'start_date',
            now()->startOfMonth()->format('Y-m-d')
        );

        $end_date = $request->input(
            'end_date',
            now()->endOfMonth()->format('Y-m-d')
        );

        $bank_id = $request->input('account_id', '');

        // ---------------------------------------------------------------
        // 2. Get bank list
        // ---------------------------------------------------------------
        $fashbanks = Fashmsbank::all();

        // ---------------------------------------------------------------
        // 3. Determine beginning of calendar/fiscal year
        //
        // Example:
        // Report Start = 09/01/2026
        // Fiscal Start = 01/01/2026
        // ---------------------------------------------------------------
        $startDate = \Carbon\Carbon::parse($start_date);

        $fiscal_start_date = $startDate
            ->copy()
            ->startOfYear()
            ->format('Y-m-d');

        // ---------------------------------------------------------------
        // 4. Get BBAL from fagltrjournal
        //
        // IMPORTANT:
        //
        // GL Debit BBAL  = Normal Bank Balance
        // GL Credit BBAL = Bank Overdraft
        //
        // But in the BANK REPORT:
        //
        // GL Debit BBAL  -> Credit column
        // GL Credit BBAL -> Debit column
        //
        // If no BBAL exists, both become 0.00.
        // ---------------------------------------------------------------
        $journalOpening = DB::table('fagltrjournal')
            ->where('account_id', $bank_id)
            ->where('trans_type', 'BBAL')
            ->whereDate('trans_dt', $fiscal_start_date)
            ->selectRaw('
                COALESCE(SUM(debit_amt), 0.00) as debit,
                COALESCE(SUM(credit_amt), 0.00) as credit
            ')
            ->first();

        $journal_debit =
            (float) ($journalOpening->debit ?? 0.00);

        $journal_credit =
            (float) ($journalOpening->credit ?? 0.00);

        // ---------------------------------------------------------------
        // 5. Get transactions from Jan 1 through day BEFORE report start
        //
        // Example:
        // Start = 09/01/2026
        //
        // Include:
        // 01/01/2026 through 08/31/2026
        // ---------------------------------------------------------------
        $prior_debit = 0.00;
        $prior_credit = 0.00;

        if ($startDate->gt(
            \Carbon\Carbon::parse($fiscal_start_date)
        )) {

            $priorActivity = DB::table('fashtrans')
                ->where('bank_id', $bank_id)
                ->where('serial_no', '000')
                ->whereDate(
                    'trans_dt',
                    '>=',
                    $fiscal_start_date
                )
                ->whereDate(
                    'trans_dt',
                    '<',
                    $start_date
                )
                ->selectRaw('
                    COALESCE(SUM(debit_amt), 0.00) as debit,
                    COALESCE(SUM(credit_amt), 0.00) as credit
                ')
                ->first();

            $prior_debit =
                (float) ($priorActivity->debit ?? 0.00);

            $prior_credit =
                (float) ($priorActivity->credit ?? 0.00);
        }

        // ---------------------------------------------------------------
        // 6. Calculate NET opening bank balance
        //
        // Normal balance:
        //
        // BBAL Debit
        // + Prior Credits / Income
        // - Prior Debits / Expenses
        //
        // Overdraft gives a negative internal balance.
        // ---------------------------------------------------------------
        $opening_balance =
            ($journal_debit - $journal_credit)
            + $prior_credit
            - $prior_debit;

        // ---------------------------------------------------------------
        // 7. Put OPENING BALANCE on correct BANK REPORT side
        //
        // Normal positive bank balance -> CREDIT column
        // Overdraft                  -> DEBIT column
        //
        // This is intentionally opposite to GL BBAL presentation.
        // ---------------------------------------------------------------
        if ($opening_balance >= 0) {

            $op_debit_amt = 0.00;
            $op_credit_amt = $opening_balance;
        } else {

            $op_debit_amt = abs($opening_balance);
            $op_credit_amt = 0.00;
        }

        // ---------------------------------------------------------------
        // 8. Get detail transactions for selected report period
        //
        // fashtrans:
        //
        // debit_amt  = Expense / Payment
        // credit_amt = Income / Receipt
        // ---------------------------------------------------------------
        $rows = DB::table('fashtrans')
            ->join(
                'accountmasters',
                'fashtrans.party_cd',
                '=',
                'accountmasters.party_cd'
            )
            ->where(
                'fashtrans.bank_id',
                $bank_id
            )
            ->where(
                'fashtrans.serial_no',
                '000'
            )
            ->whereDate(
                'fashtrans.trans_dt',
                '>=',
                $start_date
            )
            ->whereDate(
                'fashtrans.trans_dt',
                '<=',
                $end_date
            )
            ->orderBy(
                'fashtrans.trans_dt',
                'asc'
            )
            ->select(
                'fashtrans.*',
                'accountmasters.party_nm'
            )
            ->get();

        // ---------------------------------------------------------------
        // 9. Period transaction totals
        // ---------------------------------------------------------------
        $totdr = (float) $rows->sum('debit_amt');

        $totcr = (float) $rows->sum('credit_amt');

        // ---------------------------------------------------------------
        // 10. Calculate closing bank balance
        //
        // Opening Balance
        // + Current Credits / Income
        // - Current Debits / Expenses
        // ---------------------------------------------------------------
        $closing_balance =
            $opening_balance
            + $totcr
            - $totdr;

        // ---------------------------------------------------------------
        // 11. BALANCE C/F
        //
        // We put the closing balance on the OPPOSITE side
        // so Debit and Credit totals become equal.
        //
        // Positive bank balance:
        //     Balance C/F goes under DEBIT
        //
        // Overdraft:
        //     Balance C/F goes under CREDIT
        // ---------------------------------------------------------------
        if ($closing_balance >= 0) {

            $cl_debit_amt = $closing_balance;
            $cl_credit_amt = 0.00;
        } else {

            $cl_debit_amt = 0.00;
            $cl_credit_amt = abs($closing_balance);
        }

        // ---------------------------------------------------------------
        // 12. Grand totals for Bank Report
        //
        // Include:
        // Opening Balance
        // + Transactions
        // + Closing Balance C/F
        //
        // Both Debit and Credit sides should match.
        // ---------------------------------------------------------------
        // $grand_debit =
        //     $op_debit_amt
        //     + $totdr
        //     + $cl_debit_amt;

        // $grand_credit =
        //     $op_credit_amt
        //     + $totcr
        //     + $cl_credit_amt;

        $subtotal_debit =
            $op_debit_amt
            + $totdr;

        $subtotal_credit =
            $op_credit_amt
            + $totcr;

        $grand_debit =
            $subtotal_debit
            + $cl_debit_amt;

        $grand_credit =
            $subtotal_credit
            + $cl_credit_amt;

        // ---------------------------------------------------------------
        // 12. Return Bank Report
        // ---------------------------------------------------------------
        return view(
            'accounts.accounts_bank_report',
            compact(
                'rows',
                'fashbanks',
                'start_date',
                'end_date',
                'op_debit_amt',
                'op_credit_amt',
                'cl_debit_amt',
                'cl_credit_amt',
                'totdr',
                'totcr',
                'subtotal_debit',
                'subtotal_credit',
                'grand_debit',
                'grand_credit'
            )
        );
    }
    // End Method

    public function DownloadPdf(Request $request)
    {
        // ---------------------------------------------------------------
        // 1. Get filters
        // ---------------------------------------------------------------
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $account_id = $request->input('account_id');

        // ---------------------------------------------------------------
        // 2. Company information
        // ---------------------------------------------------------------
        $company = DB::table('gnmscomp')->first();

        // ---------------------------------------------------------------
        // 3. Determine beginning of calendar year
        // ---------------------------------------------------------------
        $startDate = \Carbon\Carbon::parse($start_date);

        $fiscal_start_date = $startDate
            ->copy()
            ->startOfYear()
            ->format('Y-m-d');

        // ---------------------------------------------------------------
        // 4. Get Beginning Balance from fagltrjournal
        //
        // Debit BBAL  = normal bank balance
        // Credit BBAL = overdraft
        //
        // If no BBAL exists, treat as 0.00
        // ---------------------------------------------------------------
        $journalOpening = DB::table('fagltrjournal')
            ->where('account_id', $account_id)
            ->where('trans_type', 'BBAL')
            ->whereDate('trans_dt', $fiscal_start_date)
            ->selectRaw('
                COALESCE(SUM(debit_amt), 0.00) as debit,
                COALESCE(SUM(credit_amt), 0.00) as credit
            ')
            ->first();

        $journal_debit =
            (float) ($journalOpening->debit ?? 0.00);

        $journal_credit =
            (float) ($journalOpening->credit ?? 0.00);

        // ---------------------------------------------------------------
        // 5. Get activity from Jan 1 through day before start date
        // ---------------------------------------------------------------
        $prior_debit = 0.00;
        $prior_credit = 0.00;

        if ($startDate->gt(
            \Carbon\Carbon::parse($fiscal_start_date)
        )) {

            $priorActivity = DB::table('fashtrans')
                ->where('bank_id', $account_id)
                ->where('serial_no', '000')
                ->whereDate(
                    'trans_dt',
                    '>=',
                    $fiscal_start_date
                )
                ->whereDate(
                    'trans_dt',
                    '<',
                    $start_date
                )
                ->selectRaw('
                    COALESCE(SUM(debit_amt), 0.00) as debit,
                    COALESCE(SUM(credit_amt), 0.00) as credit
                ')
                ->first();

            $prior_debit =
                (float) ($priorActivity->debit ?? 0.00);

            $prior_credit =
                (float) ($priorActivity->credit ?? 0.00);
        }

        // ---------------------------------------------------------------
        // 6. Calculate Opening Balance
        //
        // BBAL Debit - BBAL Credit
        // + Prior Credits
        // - Prior Debits
        // ---------------------------------------------------------------
        $opening_balance =
            ($journal_debit - $journal_credit)
            + $prior_credit
            - $prior_debit;

        // ---------------------------------------------------------------
        // 7. Opening balance presentation for BANK REPORT
        //
        // Normal positive balance -> CREDIT side
        // Overdraft               -> DEBIT side
        // ---------------------------------------------------------------
        if ($opening_balance >= 0) {

            $op_debit_amt = 0.00;
            $op_credit_amt = $opening_balance;
        } else {

            $op_debit_amt = abs($opening_balance);
            $op_credit_amt = 0.00;
        }

        // ---------------------------------------------------------------
        // 8. Get detail transactions for selected period
        // ---------------------------------------------------------------
        $rows = DB::table('fashtrans')
            ->join(
                'accountmasters',
                'fashtrans.party_cd',
                '=',
                'accountmasters.party_cd'
            )
            ->where(
                'fashtrans.bank_id',
                $account_id
            )
            ->where(
                'fashtrans.serial_no',
                '000'
            )
            ->whereDate(
                'fashtrans.trans_dt',
                '>=',
                $start_date
            )
            ->whereDate(
                'fashtrans.trans_dt',
                '<=',
                $end_date
            )
            ->orderBy(
                'fashtrans.trans_dt',
                'asc'
            )
            ->select(
                'fashtrans.*',
                'accountmasters.party_nm'
            )
            ->get();

        // ---------------------------------------------------------------
        // 9. Transaction totals
        // ---------------------------------------------------------------
        $totdr = (float) $rows->sum('debit_amt');

        $totcr = (float) $rows->sum('credit_amt');

        // ---------------------------------------------------------------
        // 10. Calculate Closing Balance
        // ---------------------------------------------------------------
        $closing_balance =
            $opening_balance
            + $totcr
            - $totdr;

        // ---------------------------------------------------------------
        // 11. Closing Balance C/F
        //
        // Positive balance -> DEBIT side
        // Overdraft        -> CREDIT side
        // ---------------------------------------------------------------
        if ($closing_balance >= 0) {

            $cl_debit_amt = $closing_balance;
            $cl_credit_amt = 0.00;
        } else {

            $cl_debit_amt = 0.00;
            $cl_credit_amt = abs($closing_balance);
        }

        // ---------------------------------------------------------------
        // 12. Subtotals BEFORE Balance C/F
        // ---------------------------------------------------------------
        $subtotal_debit =
            $op_debit_amt
            + $totdr;

        $subtotal_credit =
            $op_credit_amt
            + $totcr;

        // ---------------------------------------------------------------
        // 13. Grand Totals AFTER Balance C/F
        // ---------------------------------------------------------------
        $grand_debit =
            $subtotal_debit
            + $cl_debit_amt;

        $grand_credit =
            $subtotal_credit
            + $cl_credit_amt;

        // ---------------------------------------------------------------
        // 14. Build PDF HTML
        // ---------------------------------------------------------------
        $html = view(
            'accounts.accounts_bank_report_pdf',
            [
                'company' => $company,
                'rows' => $rows,
                'start_date' => $start_date,
                'end_date' => $end_date,

                'op_debit_amt' => $op_debit_amt,
                'op_credit_amt' => $op_credit_amt,

                'cl_debit_amt' => $cl_debit_amt,
                'cl_credit_amt' => $cl_credit_amt,

                'totdr' => $totdr,
                'totcr' => $totcr,

                'subtotal_debit' => $subtotal_debit,
                'subtotal_credit' => $subtotal_credit,

                'grand_debit' => $grand_debit,
                'grand_credit' => $grand_credit,
            ]
        )->render();

        // ---------------------------------------------------------------
        // 15. Create PDF
        // ---------------------------------------------------------------
        $mpdf = new Mpdf([
            'orientation' => 'L',
            'tempDir' => storage_path('app/mpdf'),
        ]);

        // ---------------------------------------------------------------
        // Header
        // ---------------------------------------------------------------
        $mpdf->SetHTMLHeader('
            <div style="text-align:left;">
                <b>' . ($company->comp_nm ?? 'Company Name') . '</b><br>
                Bank Book from '
            . \Carbon\Carbon::parse($start_date)->format('m/d/Y')
            . ' to '
            . \Carbon\Carbon::parse($end_date)->format('m/d/Y')
            . '
            </div>

            <div style="height:24px;"></div>
        ');

        // ---------------------------------------------------------------
        // Footer
        // ---------------------------------------------------------------
        $mpdf->SetHTMLFooter('
            <div style="text-align:right; font-size:0.9em;">
                Page {PAGENO} of {nbpg}
            </div>
        ');

        // ---------------------------------------------------------------
        // Write HTML
        // ---------------------------------------------------------------
        $mpdf->WriteHTML($html);

        // ---------------------------------------------------------------
        // 16. Return PDF INLINE
        //
        // This allows browser to display PDF instead of downloading it.
        // Use target="_blank" on Blade button/link.
        // ---------------------------------------------------------------
        return response(
            $mpdf->Output('bank-book.pdf', 'S')
        )
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="bank-book.pdf"'
            );
    }
    // End Method

    public function PrintCheck()
    {

        return view('accounts.accounts_print_check', ['title' => 'Account Management', 'module' => 'accounts']);
    } // End Method

    public function PrintCheckData(Request $request)
    {
        $query = Fashtran::query()
            ->select(
                'trans_no',
                'trans_dt',
                'payto_name',
                'check_no',
                'debit_amt'
            )
            ->where('trans_flag', 'A')
            ->where('trans_type', 'PAYM')
            ->where('serial_no', '000');

        return DataTables::of($query)

            /*
            |--------------------------------------------------------------------------
            | Manual ordering for server-side DataTables
            |--------------------------------------------------------------------------
            */
            ->order(function ($query) use ($request) {

                $orderColumnIndex = $request->input('order.0.column');
                $orderDirection   = $request->input('order.0.dir', 'desc');

                $orderDirection = strtolower($orderDirection) === 'asc' ? 'asc' : 'desc';

                $columns = [
                    1 => 'trans_no',
                    2 => 'trans_dt',
                    3 => 'payto_name',
                    4 => 'check_no',
                    5 => 'debit_amt',
                ];

                if (isset($columns[$orderColumnIndex])) {
                    $column = $columns[$orderColumnIndex];

                    /*
                    |--------------------------------------------------------------------------
                    | trans_no sometimes sorts wrong if stored as varchar
                    |--------------------------------------------------------------------------
                    */
                    if ($column === 'trans_no') {
                        $query->orderByRaw('CAST(trans_no AS UNSIGNED) ' . $orderDirection);
                    } else {
                        $query->orderBy($column, $orderDirection);
                    }
                } else {
                    $query->orderByRaw('CAST(trans_no AS UNSIGNED) DESC');
                }
            })

            ->addIndexColumn()

            ->editColumn('trans_dt', function ($row) {
                return $row->trans_dt
                    ? \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y')
                    : '';
            })

            ->editColumn('debit_amt', function ($row) {
                return '<div class="text-end">' . number_format((float) $row->debit_amt, 2) . '</div>';
            })

            ->addColumn('action', function ($row) {


                $directPrintRoute = route('print.check', [
                    'trans_no' => $row->trans_no,
                    'direct'   => 1,
                ]);

                $pdfRoute = route('print.check', [
                    'trans_no' => $row->trans_no,
                ]);

                return '
                        <a href="' . $directPrintRoute . '"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-success waves-light waves-effect">
                            Print Check
                            <i class="mdi mdi-printer-check ms-2"></i>
                        </a>

                        <a href="' . $pdfRoute . '"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-primary waves-light waves-effect">
                            View Check
                            <i class="mdi mdi-printer ms-2"></i>
                        </a>
                    ';
            })


            ->rawColumns(['action', 'debit_amt'])
            ->make(true);
    } // End Method

    public function CheckPrinting(Request $request, $trans_no)
    {
        $check = Fashtran::where('trans_no', $trans_no)
            ->where('trans_flag', 'A')
            ->where('trans_type', 'PAYM')
            ->where('serial_no', '000')
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Direct printing
        |--------------------------------------------------------------------------
        */
        if ($request->boolean('direct')) {
            return view(
                'accounts.accounts_check_direct_print',
                compact('check')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Existing PDF preview
        |--------------------------------------------------------------------------
        */
        $pdf = Pdf::loadView(
            'accounts.accounts_check_pdf',
            compact('check')
        );

        return $pdf->stream(
            'check_' . $check->trans_no . '.pdf'
        );
    } // End Method

    public function JournalEntry()
    {

        $accounts = AccountMaster::orderBy('party_nm')->get();
        $nextid = Fagltrjournal::selectRaw('MAX(CAST(trans_no AS UNSIGNED)) as maxid')->value('maxid') + 1;

        return view('accounts.accounts_journal_entry', compact('accounts', 'nextid'), ['title' => 'Account Management', 'module' => 'accounts']);
    } // End Method


    public function StoreJournalEntry(Request $request)
    {
        $user = Auth::user();
        $company = DB::table('gnmscomp')->first();

        $validated = $request->validate([
            'trans_no' => 'required|numeric',
            'trans_dt' => 'required|date',
            'trans_period' => 'nullable|string',
            'trans_type' => 'nullable|string',
            'trans_flag' => 'nullable|string',
            'post_flag' => 'nullable|string',

            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|string|max:18',
            'lines.*.subaccount_id' => 'nullable|string|max:18',
            'lines.*.debit_amt' => 'nullable|numeric',
            'lines.*.credit_amt' => 'nullable|numeric',
            'lines.*.description' => 'nullable|string|max:40',
        ]);

        DB::transaction(function () use (
            $validated,
            $user,
            $company
        ) {
            $serial = 101;

            foreach ($validated['lines'] as $line) {

                /*
                |--------------------------------------------------------------------------
                | Retrieve the actual account code from accountmasters
                |--------------------------------------------------------------------------
                |
                | Examples:
                | 12  becomes 012
                | 55  becomes 055
                | 164 remains 164
                |
                */
                $accountId = $this->normalizeAccountId(
                    $line['account_id']
                );

                DB::table('fagltrjournal')->insert([
                    'trans_no' => $validated['trans_no'],

                    'serial_no' => str_pad(
                        $serial,
                        3,
                        '0',
                        STR_PAD_LEFT
                    ),

                    'trans_dt' => $validated['trans_dt'],
                    'trans_period' => $validated['trans_period'] ?? null,
                    'trans_type' => $validated['trans_type'] ?? null,
                    'trans_flag' => $validated['trans_flag'] ?? null,
                    'post_flag' => $validated['post_flag'] ?? null,

                    'account_id' => $accountId,

                    'subaccount_id' => $line['subaccount_id'] ?? null,
                    'debit_amt' => $line['debit_amt'] ?? 0,
                    'credit_amt' => $line['credit_amt'] ?? 0,
                    'description' => $line['description'] ?? null,

                    'update_flag' => 'Y',
                    'company_id' => $company->comp_cd,
                    'update_dt' => now(),
                    'user_cd' => $user?->user_cd,
                ]);

                $serial++;
            }
        });

        $notification = [
            'message' => 'Journal Record Inserted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    } // End Method

    public function ListJournalEntry()
    {

        //$journalList = Fagltrjournal::distinct()->select('trans_no', 'trans_dt', 'trans_type')->get();
        $journalLists = DB::table('fagltrjournal')->select('trans_no', 'trans_dt', 'trans_type')
            ->distinct()
            ->orderBy('trans_dt', 'desc')
            ->orderBy('trans_no', 'desc')
            ->get();

        return view('accounts.accounts_journal_entry_all', compact('journalLists'));
    } // End Method


    public function EditJournalEntry($trans_no, $trans_dt)
    {

        // Get all lines for this journal entry
        $journalLines = DB::table('fagltrjournal')
            ->where('trans_no', $trans_no)
            ->where('trans_dt', $trans_dt)
            ->orderBy('serial_no')
            ->get();
        // You may also want the main journal "header" fields if you have them.
        // For a flat structure, just use the first line for trans_type, period, etc.
        $header = $journalLines->first();
        $accounts = AccountMaster::orderBy('party_nm')->get();

        // $entries is a collection of all lines for this journal entry
        // Pass to Blade to allow editing
        return view('accounts.accounts_journal_edit', compact('journalLines', 'header', 'accounts'));
    } // End Method

    public function UpdateJournalEntry(Request $request, $trans_no, $trans_dt)
    {
        $user = Auth::user();
        $company = DB::table('gnmscomp')->first();

        $validated = $request->validate([
            'trans_no' => 'required|numeric',
            'trans_dt' => 'required|date',
            'trans_period' => 'nullable|string',
            'trans_type' => 'nullable|string',
            'trans_flag' => 'nullable|string',
            'post_flag' => 'nullable|string',

            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|string|max:18',
            'lines.*.subaccount_id' => 'nullable|string|max:18',
            'lines.*.debit_amt' => 'nullable',
            'lines.*.credit_amt' => 'nullable',
            'lines.*.description' => 'nullable|string|max:40',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Server-side debit and credit balance check
    |--------------------------------------------------------------------------
    */

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($validated['lines'] as $line) {
            $debitValue = $line['debit_amt'] ?? 0;
            $creditValue = $line['credit_amt'] ?? 0;

            $totalDebit += (float) str_replace(
                ',',
                '',
                (string) $debitValue
            );

            $totalCredit += (float) str_replace(
                ',',
                '',
                (string) $creditValue
            );
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()
                ->withInput()
                ->withErrors([
                    'Total Debit and Credit must be equal.',
                ]);
        }

        try {
            DB::transaction(function () use (
                $trans_no,
                $trans_dt,
                $validated,
                $user,
                $company
            ) {
                /*
            |--------------------------------------------------------------------------
            | Delete the existing journal lines
            |--------------------------------------------------------------------------
            */

                DB::table('fagltrjournal')
                    ->where('trans_no', $trans_no)
                    ->where('trans_dt', $trans_dt)
                    ->delete();

                /*
            |--------------------------------------------------------------------------
            | Insert the revised journal lines
            |--------------------------------------------------------------------------
            */

                $serial = 101;

                foreach ($validated['lines'] as $line) {

                    $accountId = $this->normalizeAccountId(
                        $line['account_id']
                    );

                    $debitValue = $line['debit_amt'] ?? 0;
                    $creditValue = $line['credit_amt'] ?? 0;

                    $debit = (float) str_replace(
                        ',',
                        '',
                        (string) $debitValue
                    );

                    $credit = (float) str_replace(
                        ',',
                        '',
                        (string) $creditValue
                    );

                    DB::table('fagltrjournal')->insert([
                        'trans_no' => $trans_no,
                        'trans_dt' => $trans_dt,

                        'serial_no' => str_pad($serial, 3, '0', STR_PAD_LEFT),

                        'trans_period' =>
                        $validated['trans_period'] ?? null,

                        'trans_type' =>
                        $validated['trans_type'] ?? null,

                        'account_id' => $accountId,

                        'subaccount_id' =>
                        $line['subaccount_id'] ?? null,

                        'debit_amt' => $debit,
                        'credit_amt' => $credit,

                        'description' =>
                        $line['description'] ?? null,

                        'trans_flag' => 'A',
                        'post_flag' => 'N',
                        'update_flag' => 'Y',
                        'company_id' => $company->comp_cd,
                        'update_dt' => now(),
                        'user_cd' => $user?->user_cd,
                    ]);

                    $serial++;
                }
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'journal' =>
                    'Unable to update the journal entry: '
                        . $e->getMessage(),
                ]);
        }

        $notification = [
            'message' => 'Journal Record Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    } // End Method

    public function CopyJournalEntry($trans_no, $trans_dt)
    {
        $user = Auth::user();
        $company = DB::table('gnmscomp')->first();

        try {
            $newTransNo = DB::transaction(function () use (
                $trans_no,
                $trans_dt,
                $user,
                $company
            ) {
                /*
                |--------------------------------------------------------------------------
                | Convert the route date to database format
                |--------------------------------------------------------------------------
                */

                $originalTransDate = \Carbon\Carbon::parse($trans_dt)
                    ->format('Y-m-d');

                /*
                |--------------------------------------------------------------------------
                | Retrieve all lines from the original journal
                |--------------------------------------------------------------------------
                */

                $originalLines = DB::table('fagltrjournal')
                    ->where('trans_no', $trans_no)
                    ->whereDate('trans_dt', $originalTransDate)
                    ->orderBy('serial_no')
                    ->get();

                if ($originalLines->isEmpty()) {
                    throw new \RuntimeException(
                        'The original journal entry could not be found.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Generate the next transaction number
                |--------------------------------------------------------------------------
                */

                $lastTransaction = DB::table('fagltrjournal')
                    ->select('trans_no')
                    ->orderByRaw('CAST(trans_no AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $newTransNo = $lastTransaction
                    ? ((int) $lastTransaction->trans_no + 1)
                    : 1;

                /*
                |--------------------------------------------------------------------------
                | Copy all journal lines to the new transaction
                |--------------------------------------------------------------------------
                */

                $serial = 101;

                foreach ($originalLines as $originalLine) {

                    DB::table('fagltrjournal')->insert([
                        'trans_no' => $newTransNo,

                        'serial_no' => str_pad(
                            $serial,
                            3,
                            '0',
                            STR_PAD_LEFT
                        ),

                        /*
                        * Initially keep the same transaction date.
                        * The user can change it on the new Edit Journal screen.
                        */
                        'trans_dt' => $originalTransDate,

                        'trans_period' => $originalLine->trans_period,
                        'trans_type' => $originalLine->trans_type,

                        'account_id' => $originalLine->account_id,
                        'subaccount_id' => $originalLine->subaccount_id,

                        'debit_amt' => $originalLine->debit_amt,
                        'credit_amt' => $originalLine->credit_amt,

                        'description' => $originalLine->description,

                        /*
                        * A copied entry should start as active and unposted.
                        */
                        'trans_flag' => 'A',
                        'post_flag' => 'U',
                        'update_flag' => 'Y',

                        'company_id' => $company?->comp_cd,
                        'update_dt' => now(),
                        'user_cd' => $user?->user_cd,
                    ]);

                    $serial++;
                }

                return $newTransNo;
            });

            /*
            |--------------------------------------------------------------------------
            | Open the newly created journal in the same browser window
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('journal.edit', [
                    'trans_no' => $newTransNo,
                    'trans_dt' => \Carbon\Carbon::parse($trans_dt)
                        ->format('Y-m-d'),
                ])
                ->with([
                    'message' =>
                    'Journal copied successfully. You are now editing the new journal.',
                    'alert-type' => 'success',
                ]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with([
                    'message' =>
                    'Unable to copy the journal entry: '
                        . $e->getMessage(),
                    'alert-type' => 'error',
                ]);
        }
    } // End Method

    public function GeneralLedger(Request $request)
    {

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // 1. Clear the temp table
        DB::table('fagltrans_temp')->truncate();

        // 2. Load all account names for fast lookup
        $accountNames = DB::table('accountmasters')->pluck('party_nm', 'party_cd')->toArray();

        // 3. Load all journal rows for the period
        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        // 4. Group all journal rows by trans_no for quick cross-account lookups
        $journalByTransNo = $journalRows->groupBy('trans_no');

        // 5. Prepare an array to collect temp table rows
        $tempRows = [];

        foreach ($journalRows as $row) {
            $trno     = $row->trans_no;
            $trtype1  = $row->trans_type;
            $partycd1 = $row->account_id;
            $debit1   = $row->debit_amt;
            $credit1  = $row->credit_amt;
            $desc1    = $row->description;

            $ptonm = '';
            $rema = '';
            $refno = ''; // can add more logic if you want to use ref_no later

            if ($trtype1 == "BBAL") {
                $ptonm = ($debit1 > 0) ? "TO BALANCE B/F" : "BY BALANCE B/F";
                $rema = '';
            } else {
                $otherRows = $journalByTransNo[$trno] ?? collect();
                if ($credit1 > 0) {
                    // Find debit account_id for this trans_no
                    $partycd2 = optional($otherRows->firstWhere('debit_amt', '>', 0))->account_id;
                    $rema = $partycd2 ? ($accountNames[$partycd2] ?? '') : '';
                    $ptonm = "TRNS TO";
                    if (!empty($desc1)) {
                        $rema = $desc1;
                    }
                } else {
                    // Find credit account_id for this trans_no
                    $partycd3 = optional($otherRows->firstWhere('credit_amt', '>', 0))->account_id;
                    $rema = $partycd3 ? ($accountNames[$partycd3] ?? '') : '';
                    $ptonm = "TRNS FROM JOURNAL";
                    if (!empty($desc1)) {
                        $rema = $desc1;
                    }
                }
            }

            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $trtype1,
                'party_cd'   => $partycd1,
                'payto_name' => $ptonm,
                'remarks'    => $rema,
                'debit_amt'  => $debit1,
                'credit_amt' => $credit1,
                'ref_no'     => $refno, // add this if you want to store ref_no
            ];
        }

        // 6. Process Bank Posting (fashtrans)
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($bankRows as $row) {
            $partycd  = $row->party_cd;
            $ptonm    = $row->payto_name;
            $refno    = $row->ref_no;
            $debit    = $row->debit_amt;
            $credit   = $row->credit_amt;
            $rema = '';

            if ($debit > 0) {
                if ($partycd == '1101' || $partycd == '103') {
                    $rema = $ptonm;
                    $ptonm = "TO CHECK ";
                } else {
                    $rema = $ptonm;
                    $ptonm = "TO CHECK FROM TD BANK";
                }
            } else {
                if ($partycd == '1101' || $partycd == '103') {
                    $rema = $ptonm;
                    $ptonm = "BY CHECK ";
                } else {
                    $rema = $ptonm;
                    $ptonm = "BY CHECK TO TD BANK";
                }
            }

            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $partycd,
                'payto_name' => $ptonm,
                'remarks'    => $rema,
                'ref_no'     => $refno,
                'debit_amt'  => $debit,
                'credit_amt' => $credit,
            ];
        }

        // 7. Bulk insert all rows at once (efficient for 6000 rows)
        if (count($tempRows) > 0) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // 8. Prepare data for reporting
        $report = DB::table('fagltrans_temp')
            ->orderBy('party_cd')
            ->orderBy('trans_dt')
            ->get();

        return view('accounts.accounts_general_ledger', [
            'report' => $report,
            'accountNames' => $accountNames,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    } // End Method

    public function DownloadGenLedgerPdf(Request $request)
    {

        // Get filters from request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get all account names as an associative array: ['103' => 'SALARY ACCOUNT', ...]
        $accountNames = DB::table('accountmasters')->pluck('party_nm', 'party_cd')->toArray();

        // Fetch company info
        $company = DB::table('gnmscomp')->first();

        $report = DB::table('fagltrans_temp')
            ->orderBy('party_cd')
            ->orderBy('trans_dt')
            ->get();

        $html = view('accounts.accounts_general_ledger_pdf', compact(
            'report',
            'startDate',
            'endDate',
            'company',
            'accountNames'
        ))->render();

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf'),
            'margin_top' => 22,
            'margin_bottom' => 18,
        ]);

        $mpdf->SetHTMLHeader('
        <div style="font-size:10pt;text-align:left;font-weight:bold;">
            <b>' . ($company->comp_nm ?? 'Company Name') . '</b><br>
            Ledger for the period : ' . e(\Carbon\Carbon::parse($startDate)->format('m/d/Y')) . ' To ' . e(\Carbon\Carbon::parse($endDate)->format('m/d/Y')) . '
        </div>
        <hr style="margin:2px 0 0 0;">
        ');

        $mpdf->SetHTMLFooter('
        <div style="text-align:right;font-size:9pt;font-style:italic;">
            Page {PAGENO}
        </div>
        ');

        ini_set('pcre.backtrack_limit', 10000000);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('GeneralLedger.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="GeneralLedger.pdf"'
        ]);
    } // End Method

    public function TrialBalance(Request $request)
    {

        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        DB::table('fagltrans_temp')->truncate();
        DB::table('fagltpbalance')->truncate();

        // Collect all temp rows
        $tempRows = [];
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($bankRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->party_cd,
                'payto_name' => $row->payto_name,
                'ref_no'     => $row->ref_no,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($journalRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->account_id,
                'payto_name' => null,
                'ref_no' => null,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        if (count($tempRows)) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // Grouped per-party_cd sums
        $balances = DB::table('fagltrans_temp')
            ->selectRaw('party_cd,
            SUM(CASE WHEN trans_type = "BBAL" THEN debit_amt ELSE 0 END) as op_dr,
            SUM(CASE WHEN trans_type = "BBAL" THEN credit_amt ELSE 0 END) as op_cr,
            SUM(CASE WHEN trans_type <> "BBAL" THEN debit_amt ELSE 0 END) as tr_dr,
            SUM(CASE WHEN trans_type <> "BBAL" THEN credit_amt ELSE 0 END) as tr_cr
        ')
            ->groupBy('party_cd')
            ->get();

        $balanceRows = [];
        foreach ($balances as $b) {
            $op_dr = $b->op_dr ?? 0;
            $op_cr = $b->op_cr ?? 0;
            $tr_dr = $b->tr_dr ?? 0;
            $tr_cr = $b->tr_cr ?? 0;
            $totaldr = $op_dr + $tr_dr;
            $totalcr = $op_cr + $tr_cr;
            $balamt = $totaldr - $totalcr;
            $bl_dr = $balamt > 0 ? $balamt : 0;
            $bl_cr = $balamt < 0 ? abs($balamt) : 0;
            $serial_no = 1;

            if ($op_dr > 0 || $op_cr > 0 || $tr_dr > 0 || $tr_cr > 0 || $bl_dr > 0 || $bl_cr > 0) {
                $balanceRows[] = [
                    'serial_no' => $serial_no,
                    'party_cd' => $b->party_cd,
                    'bb_debit_amt' => $op_dr,
                    'bb_credit_amt' => $op_cr,
                    'tr_debit_amt' => $tr_dr,
                    'tr_credit_amt' => $tr_cr,
                    'eb_debit_amt' => $bl_dr,
                    'eb_credit_amt' => $bl_cr,
                ];
            }
        }

        if (count($balanceRows)) {
            DB::table('fagltpbalance')->insert($balanceRows);
        }

        // Now join the table to get required data
        $rows = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->join('fagltpbalance as b', 'b.party_cd', '=', 'a.party_cd')
            ->select(
                'a.party_cd',
                'a.party_nm',
                'a.gl_id',
                'c.name as category_name',
                'c.account_type',
                'b.serial_no',
                'b.bb_debit_amt',
                'b.bb_credit_amt',
                'b.tr_debit_amt',
                'b.tr_credit_amt',
                'b.eb_debit_amt',
                'b.eb_credit_amt'
            )
            ->orderBy('c.account_type')
            ->orderBy('a.gl_id')
            ->orderBy('a.party_cd')
            ->get();

        $groupedByType = $rows->groupBy('account_type');

        return view('accounts.accounts_trialbalance', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'groupedByType' => $groupedByType,
            'rows' => $rows,
        ]);
    } // End Method

    public function DownloadTrialBalancePdf(Request $request)
    {

        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        // Fetch company info
        $company = DB::table('gnmscomp')->first();

        $rows = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->join('fagltpbalance as b', 'b.party_cd', '=', 'a.party_cd')
            ->select(
                'a.party_cd',
                'a.party_nm',
                'a.gl_id',
                'c.name as category_name',
                'c.account_type',
                'b.serial_no',
                'b.bb_debit_amt',
                'b.bb_credit_amt',
                'b.tr_debit_amt',
                'b.tr_credit_amt',
                'b.eb_debit_amt',
                'b.eb_credit_amt'
            )
            ->orderBy('c.account_type')
            ->orderBy('a.gl_id')
            ->orderBy('a.party_cd')
            ->get();

        $groupedByType = $rows->groupBy('account_type');

        $html = view('accounts.accounts_trialbalance_pdf', compact(
            'rows',
            'startDate',
            'endDate',
            'groupedByType'
        ))->render();

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf'),
            'margin_top' => 30,
            'margin_bottom' => 18,
        ]);

        $mpdf->SetHTMLHeader('
        <div style="font-size:10pt;text-align:center;font-weight:bold;">
        <b>' . ($company->comp_nm ?? 'Company Name') . '</b><br>
            Closing Trial Balance as of ' . date("m/d/Y", strtotime($endDate)) . '
        </div>
        ');

        $mpdf->SetHTMLFooter('
        <hr style="margin:2px 0 0 0;">
        <div style="text-align:right;font-size:9pt;font-style:italic;">
            Page {PAGENO}
        </div>
        ');

        ini_set('pcre.backtrack_limit', 10000000);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('TrialBalance.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="TrialBalance.pdf"'
        ]);
    }

    public function TrialBalanceMonthwise(Request $request)
    {

        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // --- 1. Build months array
        $months = [];
        $period = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();
        while ($period <= $end) {
            $months[] = $period->format('Y-m');
            $period->addMonth();
        }
        // 2. Fill temp table as usual
        DB::table('fagltrans_temp')->truncate();
        DB::table('fagltpbalance')->truncate();

        // Collect all temp rows
        $tempRows = [];
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($bankRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->party_cd,
                'payto_name' => $row->payto_name,
                'ref_no'     => $row->ref_no,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($journalRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->account_id,
                'payto_name' => null,
                'ref_no' => null,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        if (count($tempRows)) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // 3. Get monthly debit/credit per account
        $monthly = DB::table('fagltrans_temp')
            ->selectRaw("
                        party_cd,
                        DATE_FORMAT(trans_dt, '%Y-%m') as ym,
                        SUM(debit_amt) as debit,
                        SUM(credit_amt) as credit
                    ")
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->groupBy('party_cd', 'ym')
            ->get();

        // 4. Get account info
        $accounts = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->select('a.party_cd', 'a.party_nm', 'a.gl_id', 'c.name as category_name', 'c.account_type')
            ->orderBy('c.account_type')->orderBy('a.gl_id')->orderBy('a.party_cd')
            ->get();

        // 5. Build mapping: party_cd => info + months array
        $data = [];
        foreach ($accounts as $acc) {
            $data[$acc->party_cd] = [
                'info' => $acc,
                'months' => array_fill_keys($months, ['debit' => 0, 'credit' => 0]),
            ];
        }
        foreach ($monthly as $row) {
            if (isset($data[$row->party_cd]) && isset($data[$row->party_cd]['months'][$row->ym])) {
                $data[$row->party_cd]['months'][$row->ym]['debit'] = $row->debit;
                $data[$row->party_cd]['months'][$row->ym]['credit'] = $row->credit;
            }
        }

        // Filter: Remove accounts with all zero months
        $data = array_filter($data, function ($acc) {
            foreach ($acc['months'] as $month) {
                if ($month['debit'] != 0 || $month['credit'] != 0) {
                    return true; // Show this account
                }
            }
            return false; // Hide this account
        });

        //  6. Group by account_type for sectioned display (like before)
        $groupedByType = collect($data)->groupBy(function ($d) {
            return $d['info']->account_type;
        });

        // 7. Company name
        //$compname = 'Company Name'; // (Change as needed)
        $compname = config('company.comp_nm');

        return view('accounts.accounts_trialbalance_monthwise', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'months' => $months,
            'groupedByType' => $groupedByType,
            'data' => $data,
            'compname' => $compname,
        ]);
    } // End Method

    public function TrialBalanceMonthwisePDF(Request $request)
    {

        // 1. Read dates from query (coming from request()->all())
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // --- 1. Build months array
        $months = [];
        $period = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();
        while ($period <= $end) {
            $months[] = $period->format('Y-m');
            $period->addMonth();
        }
        // 2. Fill temp table as usual
        DB::table('fagltrans_temp')->truncate();
        DB::table('fagltpbalance')->truncate();

        // Collect all temp rows
        $tempRows = [];
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($bankRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->party_cd,
                'payto_name' => $row->payto_name,
                'ref_no'     => $row->ref_no,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($journalRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->account_id,
                'payto_name' => null,
                'ref_no' => null,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        if (count($tempRows)) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // 3. Get monthly debit/credit per account
        $monthly = DB::table('fagltrans_temp')
            ->selectRaw("
                        party_cd,
                        DATE_FORMAT(trans_dt, '%Y-%m') as ym,
                        SUM(debit_amt) as debit,
                        SUM(credit_amt) as credit
                    ")
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->groupBy('party_cd', 'ym')
            ->get();

        // 4. Get account info
        $accounts = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->select('a.party_cd', 'a.party_nm', 'a.gl_id', 'c.name as category_name', 'c.account_type')
            ->orderBy('c.account_type')->orderBy('a.gl_id')->orderBy('a.party_cd')
            ->get();

        // 5. Build mapping: party_cd => info + months array
        $data = [];
        foreach ($accounts as $acc) {
            $data[$acc->party_cd] = [
                'info' => $acc,
                'months' => array_fill_keys($months, ['debit' => 0, 'credit' => 0]),
            ];
        }
        foreach ($monthly as $row) {
            if (isset($data[$row->party_cd]) && isset($data[$row->party_cd]['months'][$row->ym])) {
                $data[$row->party_cd]['months'][$row->ym]['debit'] = $row->debit;
                $data[$row->party_cd]['months'][$row->ym]['credit'] = $row->credit;
            }
        }

        // Filter: Remove accounts with all zero months
        $data = array_filter($data, function ($acc) {
            foreach ($acc['months'] as $month) {
                if ($month['debit'] != 0 || $month['credit'] != 0) {
                    return true; // Show this account
                }
            }
            return false; // Hide this account
        });

        //  6. Group by account_type for sectioned display (like before)
        $groupedByType = collect($data)->groupBy(function ($d) {
            return $d['info']->account_type;
        });

        // 7. Company name
        //$compname = 'Company Name'; // (Change as needed)
        $compname = config('company.comp_nm');

        // 4. Render Blade to HTML
        $html = view('accounts.accounts_trialbalance_monthwise_pdf', [
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'months'         => $months,
            'groupedByType'  => $groupedByType,
            'data'           => $data,
            'company'        => $compname,
        ])->render();

        // 5. Create mPDF instance (A4 Landscape is usually better for wide tables)
        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf'),
            'format'      => 'A4-L',
            'margin_top'  => 10,
            'margin_right' => 8,
            'margin_bottom' => 10,
            'margin_left' => 8,
        ]);

        $mpdf->SetTitle('Trial Balance Monthwise');

        // Optional: simple footer with page number
        //$mpdf->SetFooter('Trial Balance Monthwise|Page {PAGENO} of {nbpg}');
        $mpdf->SetFooter('| |Page {PAGENO} of {nbpg}');

        // 6. Write HTML and output
        $mpdf->WriteHTML($html);

        $pdfContent = $mpdf->Output('', 'S'); // capture as string

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="trial_balance_monthwise.pdf"');
    } // End Method

    private function buildMonthwiseTrialBalance(Request $request)
    {

        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // --- 1. Build months array
        $months = [];
        $period = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $end = \Carbon\Carbon::parse($endDate)->endOfMonth();
        while ($period <= $end) {
            $months[] = $period->format('Y-m');
            $period->addMonth();
        }

        // --- 2. Fill temp table as usual
        DB::table('fagltrans_temp')->truncate();
        DB::table('fagltpbalance')->truncate();

        // Collect all temp rows
        $tempRows = [];
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($bankRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->party_cd,
                'payto_name' => $row->payto_name,
                'ref_no'     => $row->ref_no,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();

        foreach ($journalRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->account_id,
                'payto_name' => null,
                'ref_no' => null,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }

        if (count($tempRows)) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // --- Opening balance per account (BBAL only, up to startDate) ---
        $openings = DB::table('fagltrans_temp')
            ->selectRaw('party_cd,
            SUM(CASE WHEN trans_type = "BBAL" THEN debit_amt ELSE 0 END) as op_dr,
            SUM(CASE WHEN trans_type = "BBAL" THEN credit_amt ELSE 0 END) as op_cr
        ')
            ->groupBy('party_cd')
            ->get()
            ->keyBy('party_cd');

        // --- Monthly transactions per account, excluding BBAL ---
        $monthwise = DB::table('fagltrans_temp')
            ->selectRaw('party_cd, DATE_FORMAT(trans_dt, "%Y-%m") as ym,
            SUM(CASE WHEN trans_type <> "BBAL" THEN debit_amt ELSE 0 END) as tr_dr,
            SUM(CASE WHEN trans_type <> "BBAL" THEN credit_amt ELSE 0 END) as tr_cr
        ')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->groupBy('party_cd', 'ym')
            ->get();

        // --- Total transactions in period for ending balance ---
        $periodTotals = DB::table('fagltrans_temp')
            ->selectRaw('party_cd,
            SUM(CASE WHEN trans_type <> "BBAL" THEN debit_amt ELSE 0 END) as tr_dr,
            SUM(CASE WHEN trans_type <> "BBAL" THEN credit_amt ELSE 0 END) as tr_cr
        ')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->groupBy('party_cd')
            ->get()
            ->keyBy('party_cd');

        // --- Build report array per account ---
        $accounts = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->select('a.party_cd', 'a.party_nm', 'a.gl_id', 'c.name as category_name', 'c.account_type')
            ->orderBy('c.account_type')->orderBy('a.gl_id')->orderBy('a.party_cd')
            ->get();

        $data = [];
        foreach ($accounts as $acc) {
            $party_cd = $acc->party_cd;

            $op_dr = isset($openings[$party_cd]) ? $openings[$party_cd]->op_dr : 0;
            $op_cr = isset($openings[$party_cd]) ? $openings[$party_cd]->op_cr : 0;

            // Fill monthly transactions (tr_dr, tr_cr)
            $monthsData = [];
            foreach ($months as $m) {
                $found = $monthwise->first(function ($row) use ($party_cd, $m) {
                    return $row->party_cd == $party_cd && $row->ym == $m;
                });
                $monthsData[$m] = [
                    'tr_dr' => $found ? $found->tr_dr : 0,
                    'tr_cr' => $found ? $found->tr_cr : 0,
                ];
            }

            // Calculate period totals for ending balance
            $tr_dr = isset($periodTotals[$party_cd]) ? $periodTotals[$party_cd]->tr_dr : 0;
            $tr_cr = isset($periodTotals[$party_cd]) ? $periodTotals[$party_cd]->tr_cr : 0;
            $totaldr = $op_dr + $tr_dr;
            $totalcr = $op_cr + $tr_cr;
            $balamt = $totaldr - $totalcr;
            $bl_dr = $balamt > 0 ? $balamt : 0;
            $bl_cr = $balamt < 0 ? abs($balamt) : 0;

            $data[$party_cd] = [
                'info' => $acc,
                'op_dr' => $op_dr,
                'op_cr' => $op_cr,
                'months' => $monthsData,
                'bl_dr' => $bl_dr,
                'bl_cr' => $bl_cr,
            ];
        }

        // --- Filter: Show only accounts with any opening, month, or ending activity ---
        $data = array_filter($data, function ($acc) {
            if ($acc['op_dr'] != 0 || $acc['op_cr'] != 0 || $acc['bl_dr'] != 0 || $acc['bl_cr'] != 0) {
                return true;
            }
            foreach ($acc['months'] as $month) {
                if ($month['tr_dr'] != 0 || $month['tr_cr'] != 0) {
                    return true;
                }
            }
            return false;
        });

        $groupedByType = collect($data)->groupBy(function ($d) {
            return $d['info']->account_type;
        });

        //$compname = 'Company Name'; // update as needed
        $compname = config('company.comp_nm');

        // Remove the dd([...]) line once you confirm data is present!
        // dd([
        //     'accounts'   => $accounts->pluck('party_cd')->all(),
        //     'monthwise'  => $monthwise->pluck('party_cd')->unique()->all(),
        //     'openings'   => $openings->keys()->all(),
        // ]);

        return compact('startDate', 'endDate', 'months', 'groupedByType', 'data', 'compname');
    }

    public function TrialBalanceWithBalanceMonthwise(Request $request)
    {
        $vars = $this->buildMonthwiseTrialBalance($request);
        return view('accounts.accounts_trialbalance_balance_monthwise', $vars);
    } // End Method

    public function TrialBalanceWithBalanceMonthwisePdf(Request $request)

    {
        // 1. Reuse existing logic (same as HTML/Excel)
        $vars = $this->buildMonthwiseTrialBalance($request);
        // $vars = [
        //   'startDate', 'endDate', 'months', 'groupedByType',
        //   'data', 'compname'
        // ];

        // 2. Render Blade -> HTML
        $html = view('accounts.accounts_trialbalance_balance_monthwise_pdf', [
            'startDate'      => $vars['startDate'],
            'endDate'        => $vars['endDate'],
            'months'         => $vars['months'],
            'groupedByType'  => $vars['groupedByType'],
            'data'           => $vars['data'],
            'companyName'    => $vars['compname'] ?? 'Company Name',
        ])->render();

        // 3. Create mPDF instance (A4 Landscape)
        $mpdf = new Mpdf([
            //'format'        => 'A4-L',
            'tempDir' => storage_path('app/mpdf'),
            'format'        => 'Legal-L',   // US Legal paper size, Landscape,
            'margin_top'    => 10,
            'margin_right'  => 8,
            'margin_bottom' => 10,
            'margin_left'   => 8,
        ]);

        $mpdf->SetTitle('Trial Balance Monthwise (With Opening Balance)');

        // Only page number in footer (centered)
        $mpdf->SetFooter('Page {PAGENO}');

        // 4. Output PDF
        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="trial_balance_monthwise_with_opening_balance.pdf"'
            );
    } // End Method

    public function TrialBalanceWithBalanceMonthwiseExcel(Request $request)
    {

        $vars = $this->buildMonthwiseTrialBalance($request); // same as for view

        return Excel::download(
            new TrialBalanceMonthwiseExcelExport(
                $vars['months'],
                $vars['data'],
                $vars['compname'],
                $vars['startDate'],
                $vars['endDate']
            ),
            'trial_balance_monthwise.xlsx'
        );
    } // End Method

    // Profit & Loss Report (Simple: Not Monthwise)
    public function ProfitLoss(Request $request)
    {
        // 1. Set period (can add form for date range in Blade)
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // 2. Prepare temp as in your trial balance code
        DB::table('fagltrans_temp')->truncate();

        $tempRows = [];
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();
        foreach ($bankRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->party_cd,
                'payto_name' => $row->payto_name,
                'ref_no'     => $row->ref_no,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }
        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();
        foreach ($journalRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->account_id,
                'payto_name' => null,
                'ref_no' => null,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }
        if (count($tempRows)) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // 3. Get accountmasters & categories
        $accounts = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->select('a.party_cd', 'a.party_nm', 'a.gl_id', 'c.name as category_name', 'c.account_type')
            ->orderBy('c.account_type')->orderBy('a.gl_id')->orderBy('a.party_cd')
            ->get();

        // 4. Get Income and Expense data
        $incomeRows = [];
        $expenseRows = [];
        $totalIncome = $totalExpense = 0;

        foreach ($accounts as $acc) {
            $sum = DB::table('fagltrans_temp')
                ->where('party_cd', $acc->party_cd)
                ->where('trans_type', '<>', 'BBAL')
                ->selectRaw('SUM(debit_amt) as dr, SUM(credit_amt) as cr')
                ->first();

            $amount = 0;
            if ($acc->account_type == 'II') {
                // Income (usually Credit - Debit)
                $amount = ($sum->cr ?? 0) - ($sum->dr ?? 0);
                if (abs($amount) > 0.001) {
                    $incomeRows[] = [
                        'gl_id' => $acc->gl_id,
                        'category' => $acc->category_name,
                        'code' => $acc->party_cd,
                        'name' => $acc->party_nm,
                        'amount' => $amount,
                    ];
                    $totalIncome += $amount;
                }
            } elseif ($acc->account_type == 'IE') {
                // Expense (usually Debit - Credit)
                $amount = ($sum->dr ?? 0) - ($sum->cr ?? 0);
                if (abs($amount) > 0.001) {
                    $expenseRows[] = [
                        'gl_id' => $acc->gl_id,
                        'category' => $acc->category_name,
                        'code' => $acc->party_cd,
                        'name' => $acc->party_nm,
                        'amount' => $amount,
                    ];
                    $totalExpense += $amount;
                }
            }
        }

        $netProfit = $totalIncome - $totalExpense;

        //$compname = 'Company Name'; // Use your actual company variable
        $compname = config('company.comp_nm');

        return view('accounts.accounts_profitloss', compact(
            'incomeRows',
            'expenseRows',
            'totalIncome',
            'totalExpense',
            'netProfit',
            'startDate',
            'endDate',
            'compname'
        ));
    } // End Method

    // PDF Export
    public function ProfitLossPDF(Request $request)
    {
        $vars = $this->ProfitLoss($request)->getData();
        $pdf = Pdf::loadView('accounts.accounts_profitloss_pdf', $vars)
            ->setPaper('a4', 'portrait');
        return $pdf->download('profit_loss.pdf');
    } // End Method

    // Excel Export
    public function ProfitLossExcel(Request $request)
    {
        $vars = $this->ProfitLoss($request)->getData();
        return Excel::download(new \App\Exports\ProfitLossExport($vars), 'profit_loss.xlsx');
    } // End Method

    public function ProfitLossDetails(Request $request, $party_cd)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Fetch transactions for the selected account and date range
        $rows = DB::table('fagltrans_temp')
            ->where('party_cd', $party_cd)
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->orderBy('trans_dt')
            ->get();

        $account = DB::table('accountmasters')->where('party_cd', $party_cd)->first();

        $totalDebit = $rows->sum('debit_amt');
        $totalCredit = $rows->sum('credit_amt');

        return view('accounts.accounts_profitloss_details', compact(
            'rows',
            'account',
            'startDate',
            'endDate',
            'totalDebit',
            'totalCredit'
        ));
    } //End Method

    public function BalanceSheet(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $asOfDate = $endDate;

        // 1. Get closing balances for BA (Assets) and BL (Liabilities)
        $balances = DB::table('fagltrans_temp')
            ->join('accountmasters', 'fagltrans_temp.party_cd', '=', 'accountmasters.party_cd')
            ->join('faglmsacctcategory as c', 'accountmasters.gl_id', '=', 'c.id')
            ->select(
                'accountmasters.gl_id',
                'accountmasters.party_cd',
                'accountmasters.party_nm',
                'c.name as category_name',
                'c.account_type',
                //DB::raw('SUM(debit_amt - credit_amt) as balance')
                DB::raw('SUM(
                CASE WHEN c.account_type = "BA" THEN debit_amt - credit_amt
                     WHEN c.account_type = "BL" THEN credit_amt - debit_amt
                END ) as balance')
            )
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->whereIn('c.account_type', ['BA', 'BL'])
            ->groupBy('accountmasters.gl_id', 'accountmasters.party_cd', 'accountmasters.party_nm', 'c.name', 'c.account_type')
            ->orderBy('c.account_type')->orderBy('accountmasters.gl_id')
            ->get();

        // 2. Separate Assets and Liabilities
        $assets = $balances->where('account_type', 'BA');
        $liabilities = $balances->where('account_type', 'BL');

        // 3. Calculate Net Profit/(Loss) from P&L
        $profitLoss = $this->calculateNetProfitLoss($startDate, $endDate);

        // 4. Total Assets/Liabilities
        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance') + $profitLoss; // Add profit/loss to Liabilities

        //$compname = 'Company Name'; // Or fetch from settings
        $compname = config('company.comp_nm');
        return view('accounts.accounts_balancesheet', compact(
            'asOfDate',
            'assets',
            'liabilities',
            'profitLoss',
            'totalAssets',
            'totalLiabilities',
            'compname'
        ));
    } // End Method

    // Example helper
    private function calculateNetProfitLoss($startDate, $endDate)
    {
        $result = DB::table('fagltrans_temp')
            ->join('accountmasters', 'fagltrans_temp.party_cd', '=', 'accountmasters.party_cd')
            ->join('faglmsacctcategory as c', 'accountmasters.gl_id', '=', 'c.id')
            ->select(
                DB::raw('SUM(CASE WHEN c.account_type = "II" THEN credit_amt - debit_amt ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN c.account_type = "IE" THEN debit_amt - credit_amt ELSE 0 END) as expense')
            )
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->first();

        return ($result->income ?? 0) - ($result->expense ?? 0);
    } // End Method

    public function exportBalanceSheetPDF(Request $request)
    {
        // 1. Get the data for the report (reuse your display code!)
        $vars = $this->BalanceSheet($request)->getData();

        // 2. Render Blade to HTML
        $html = view('accounts.accounts_balancesheet_pdf', (array)$vars)->render();

        // 3. Create mPDF instance (A4 landscape)
        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf'),
            'format' => 'A4-L',
            'margin_footer' => 12,
        ]);

        // 4. Add footer with page numbers
        $mpdf->SetHTMLFooter('<div style="text-align:center;font-size:8pt;">Page {PAGENO} of {nbpg}</div>');

        // 5. Write the HTML
        $mpdf->WriteHTML($html);

        // 6. Output as download
        return response($mpdf->Output('balancesheet.pdf', \Mpdf\Output\Destination::STRING_RETURN))
            ->header('Content-Type', 'application/pdf');
    } // End Method

    public function exportBalanceSheetExcel(Request $request)
    {
        // Prepare the data using your existing BalanceSheet() method
        $vars = $this->BalanceSheet($request)->getData();

        return Excel::download(
            new BalanceSheetExport(
                $vars['assets'],
                $vars['liabilities'],
                $vars['profitLoss'],
                $vars['totalAssets'],
                $vars['totalLiabilities'],
                $vars['compname'],
                $vars['endDate'] ?? $vars['asOfDate'] ?? null
            ),
            'balancesheet.xlsx'
        );
    } // End Method


    public function ProfitLossNew(Request $request)
    {

        // 1. Set period (can add form for date range in Blade)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 2. Prepare temp as in your trial balance code
        DB::table('fagltrans_temp')->truncate();

        $tempRows = [];
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();
        foreach ($bankRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->party_cd,
                'payto_name' => $row->payto_name,
                'ref_no'     => $row->ref_no,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }
        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();
        foreach ($journalRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->account_id,
                'payto_name' => null,
                'ref_no' => null,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }
        if (count($tempRows)) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // 3. Get accountmasters & categories
        $accounts = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->select('a.party_cd', 'a.party_nm', 'a.gl_id', 'c.name as category_name', 'c.account_type')
            ->orderBy('c.account_type')->orderBy('a.gl_id')->orderBy('a.party_cd')
            ->get();

        // 4. Get Income and Expense data
        $incomeRows = [];
        $expenseRows = [];
        $totalIncome = $totalExpense = 0;

        foreach ($accounts as $acc) {
            $sum = DB::table('fagltrans_temp')
                ->where('party_cd', $acc->party_cd)
                ->where('trans_type', '<>', 'BBAL')
                ->selectRaw('SUM(debit_amt) as dr, SUM(credit_amt) as cr')
                ->first();

            $amount = 0;
            if ($acc->account_type == 'II') {
                // Income (usually Credit - Debit)
                $amount = ($sum->cr ?? 0) - ($sum->dr ?? 0);
                if (abs($amount) > 0.001) {
                    $incomeRows[] = [
                        'gl_id' => $acc->gl_id,
                        'category' => $acc->category_name,
                        'code' => $acc->party_cd,
                        'name' => $acc->party_nm,
                        'amount' => $amount,
                    ];
                    $totalIncome += $amount;
                }
            } elseif ($acc->account_type == 'IE') {
                // Expense (usually Debit - Credit)
                $amount = ($sum->dr ?? 0) - ($sum->cr ?? 0);
                if (abs($amount) > 0.001) {
                    $expenseRows[] = [
                        'gl_id' => $acc->gl_id,
                        'category' => $acc->category_name,
                        'code' => $acc->party_cd,
                        'name' => $acc->party_nm,
                        'amount' => $amount,
                    ];
                    $totalExpense += $amount;
                }
            }
        }

        $netProfit = $totalIncome - $totalExpense;
        //$compname = 'Company Name'; // Use your actual company variable
        $compname = config('company.comp_nm');

        return view('accounts.accounts_profitloss_new', compact(
            'incomeRows',
            'expenseRows',
            'totalIncome',
            'totalExpense',
            'netProfit',
            'startDate',
            'endDate',
            'compname'
        ));
    } // End Method

    public function ProfitLossMonthwise(Request $request)
    {

        // 1. Set period (can add form for date range in Blade)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 2. Prepare temp as in your trial balance code
        DB::table('fagltrans_temp')->truncate();

        // 3. Generate temp table fagltrans_temp from fashtrans and fagltrjournal table
        $tempRows = [];
        $bankRows = DB::table('fashtrans')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();
        foreach ($bankRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->party_cd,
                'payto_name' => $row->payto_name,
                'ref_no'     => $row->ref_no,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }
        $journalRows = DB::table('fagltrjournal')
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->get();
        foreach ($journalRows as $row) {
            $tempRows[] = [
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $row->account_id,
                'payto_name' => null,
                'ref_no' => null,
                'debit_amt'  => $row->debit_amt,
                'credit_amt' => $row->credit_amt,
            ];
        }
        if (count($tempRows)) {
            DB::table('fagltrans_temp')->insert($tempRows);
        }

        // 4. Fetch accounts & categories
        $accounts = DB::table('accountmasters as a')
            ->join('faglmsacctcategory as c', 'a.gl_id', '=', 'c.id')
            ->select('a.party_cd', 'a.party_nm', 'a.gl_id', 'c.name as category_name', 'c.account_type')
            ->orderBy('c.account_type')->orderBy('a.gl_id')->orderBy('a.party_cd')
            ->get();

        // 5. Build array of months (e.g., ['2024-01', '2024-02', ...])
        $months = [];
        $period = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $end = \Carbon\Carbon::parse($endDate)->endOfMonth();
        while ($period <= $end) {
            $months[] = $period->format('Y-m');
            $period->addMonth();
        }

        // 6. Group temp data by account and month
        $monthlySums = DB::table('fagltrans_temp')
            ->selectRaw('party_cd, DATE_FORMAT(trans_dt, "%Y-%m") as ym,
                SUM(CASE WHEN trans_type <> "BBAL" THEN debit_amt ELSE 0 END) as dr,
                SUM(CASE WHEN trans_type <> "BBAL" THEN credit_amt ELSE 0 END) as cr')
            ->groupBy('party_cd', 'ym')
            ->get();

        // 7. Build pivot table: each row = account, each col = month
        $incomeRows = [];
        $expenseRows = [];
        $totalIncomeByMonth = [];
        $totalExpenseByMonth = [];

        foreach ($accounts as $acc) {
            $row = [
                'gl_id' => $acc->gl_id,
                'category' => $acc->category_name,
                'code' => $acc->party_cd,
                'name' => $acc->party_nm,
                'amounts' => [],
            ];
            foreach ($months as $m) {
                $found = $monthlySums->first(function ($sum) use ($acc, $m) {
                    return $sum->party_cd == $acc->party_cd && $sum->ym == $m;
                });
                if ($acc->account_type == 'II') {
                    $amt = ($found->cr ?? 0) - ($found->dr ?? 0);
                    $row['amounts'][$m] = $amt;
                    $totalIncomeByMonth[$m] = ($totalIncomeByMonth[$m] ?? 0) + $amt;
                } elseif ($acc->account_type == 'IE') {
                    $amt = ($found->dr ?? 0) - ($found->cr ?? 0);
                    $row['amounts'][$m] = $amt;
                    $totalExpenseByMonth[$m] = ($totalExpenseByMonth[$m] ?? 0) + $amt;
                }
            }
            $hasNonzero = collect($row['amounts'])->filter(function ($v) {
                return abs($v) > 0.001;
            })->isNotEmpty();

            if ($hasNonzero) {
                if ($acc->account_type == 'II') {
                    $incomeRows[] = $row;
                } elseif ($acc->account_type == 'IE') {
                    $expenseRows[] = $row;
                }
            }
        }

        // Net Profit/Loss per month
        $netProfitByMonth = [];
        foreach ($months as $m) {
            $netProfitByMonth[$m] = ($totalIncomeByMonth[$m] ?? 0) - ($totalExpenseByMonth[$m] ?? 0);
        }

        //$compname = 'Company Name'; // Replace as needed
        $compname = config('company.comp_nm');

        return view('accounts.accounts_profitloss_comparison', compact(
            'incomeRows',
            'expenseRows',
            'totalIncomeByMonth',
            'totalExpenseByMonth',
            'netProfitByMonth',
            'months',
            'startDate',
            'endDate',
            'compname'
        ));
    } // End Method

    public function ProfitLossComparisonPDF(Request $request)
    {
        // Generate the same data as in the on-screen report
        $vars = $this->ProfitLossMonthwise($request)->getData();

        $pdf = Pdf::loadView('accounts.accounts_profitloss_comparison_pdf', (array)$vars)
            ->setPaper('A4', 'landscape'); // Landscape is usually better for wide tables

        return $pdf->download('profit_loss_comparison.pdf');
    } // End Method

    public function ProfitLossComparisonExcel(Request $request)
    {

        $vars = $this->ProfitLossMonthwise($request)->getData();
        return Excel::download(
            new ProfitLossComparisonExport(
                months: $vars['months'],
                incomeRows: $vars['incomeRows'],
                expenseRows: $vars['expenseRows'],
                totalIncomeByMonth: $vars['totalIncomeByMonth'],
                totalExpenseByMonth: $vars['totalExpenseByMonth'],
                netProfitByMonth: $vars['netProfitByMonth'],
                compname: $vars['compname'],
                startDate: $vars['startDate'],
                endDate: $vars['endDate'],
            ),
            'profit_loss_comparison.xlsx'
        );
    } // End Method

    //  public function Bankrecon()
    // {
    //     $banks = DB::table('fashmsbank')
    //         ->select('account_id', 'account_name')
    //         ->where('company_id', config('company.company_id', 'DS02')) // adjust your company source
    //         ->orderBy('account_name')
    //         ->get();

    //     return view('accounts.accounts_bankrecon', compact('banks'));

    // } // End method

    //     /**
    //  * Load existing recon for bank+month or create new header.
    //  * Rule: Jan => user must enter opening balance (manual).
    //  * Other months => opening balance auto from prev month closing.
    //  */
    // public function BankreconLoadOrCreate(Request $request)
    // {
    //     $request->validate([
    //         'bank_id' => 'required|string',
    //         'period'  => 'required|date_format:Y-m',

    //         // optional values from screen (recommended)
    //         'opening_balance' => 'nullable|numeric',
    //         'opening_dt'      => 'nullable|date',
    //         'closing_balance' => 'nullable|numeric',
    //         'closing_dt'      => 'nullable|date',
    //     ]);

    //     $companyId = config('company.company_id', 'DS02');
    //     $bankId    = $request->bank_id;

    //     [$year, $month] = array_map('intval', explode('-', $request->period));

    //     $recon = DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('bank_id', $bankId)
    //         ->where('period_year', $year)
    //         ->where('period_month', $month)
    //         ->first();

    //     // default dates for the period
    //     $defaultOpeningDt = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
    //     $defaultClosingDt = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

    //     // user can override from UI
    //     $uiOpeningDt = $request->opening_dt ? \Carbon\Carbon::parse($request->opening_dt)->startOfDay() : null;
    //     $uiClosingDt = $request->closing_dt ? \Carbon\Carbon::parse($request->closing_dt)->endOfDay() : null;

    //     if (!$recon) {
    //         // compute opening balance
    //         $openingBalance = 0;

    //         if ($month === 1) {
    //             // January: if user entered, use it; else keep 0
    //             $openingBalance = $request->opening_balance !== null ? (float)$request->opening_balance : 0;
    //         } else {
    //             $prevYear  = ($month === 1) ? $year - 1 : $year;
    //             $prevMonth = ($month === 1) ? 12 : $month - 1;

    //             $prev = DB::table('fashbankrecon')
    //                 ->where('company_id', $companyId)
    //                 ->where('bank_id', $bankId)
    //                 ->where('period_year', $prevYear)
    //                 ->where('period_month', $prevMonth)
    //                 ->first();

    //             $openingBalance = $prev ? (float)$prev->closing_balance : 0;

    //             // if UI entered opening balance, respect it
    //             if ($request->opening_balance !== null) {
    //                 $openingBalance = (float)$request->opening_balance;
    //             }
    //         }

    //         $openingDt = $uiOpeningDt ?: $defaultOpeningDt;
    //         $closingDt = $uiClosingDt ?: $defaultClosingDt;

    //         $closingBalance = $request->closing_balance !== null ? (float)$request->closing_balance : 0;

    //         $reconId = DB::table('fashbankrecon')->insertGetId([
    //             'company_id'      => $companyId,
    //             'bank_id'         => $bankId,
    //             'period_year'     => $year,
    //             'period_month'    => $month,

    //             'opening_balance' => $openingBalance,
    //             'opening_dt'      => $openingDt,

    //             'closing_balance' => $closingBalance,
    //             'closing_dt'      => $closingDt,

    //             'status'          => 'OPEN',
    //             'user_cd'         => '1001',
    //             'update_dt'       => now(),
    //             'update_flag'     => 'Y',
    //         ]);

    //         $recon = DB::table('fashbankrecon')->where('recon_id', $reconId)->first();

    //     } else {
    //         // Optional: if recon exists and user typed new dates/balances, update it
    //         $update = [];

    //         if ($request->opening_balance !== null) $update['opening_balance'] = (float)$request->opening_balance;
    //         if ($uiOpeningDt) $update['opening_dt'] = $uiOpeningDt;

    //         if ($request->closing_balance !== null) $update['closing_balance'] = (float)$request->closing_balance;
    //         if ($uiClosingDt) $update['closing_dt'] = $uiClosingDt;

    //         if (!empty($update)) {
    //             $update['update_dt'] = now();
    //             $update['update_flag'] = 'Y';

    //             DB::table('fashbankrecon')->where('recon_id', $recon->recon_id)->update($update);
    //             $recon = DB::table('fashbankrecon')->where('recon_id', $recon->recon_id)->first();
    //         }
    //     }

    //     return response()->json([
    //         'ok'    => true,
    //         'recon' => $recon,
    //     ]);
    // }

    //     /**
    //  * Return transactions for the selected bank within opening_dt..closing_dt range.
    //  * One grid with PAYM and DEPS; checkbox is "cleared for this recon".
    //  */

    // public function BankreconTransactions(Request $request)
    // {
    //     $request->validate([
    //         'recon_id' => 'required|integer',
    //     ]);

    //     $reconId = (int) $request->recon_id;

    //     $recon = DB::table('fashbankrecon')
    //         ->where('recon_id', $reconId)
    //         ->first();

    //     if (!$recon) {
    //         return response()->json(['ok' => false, 'message' => 'Recon not found'], 404);
    //     }

    //     // 1) Load cleared items (NO JOIN) and build a lookup set
    //     $clearedRows = DB::table('fashbankrecon_items')
    //         ->where('recon_id', $reconId)
    //         ->select([
    //             'trans_no',
    //             DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') as trans_dt_str"),
    //             'serial_no',
    //         ])
    //         ->get();

    //     $clearedSet = [];
    //     foreach ($clearedRows as $c) {
    //         $key = $c->trans_no . '|' . $c->trans_dt_str . '|' . $c->serial_no;
    //         $clearedSet[$key] = true;
    //     }

    //     // 2) Load transactions from fashtrans (NO JOIN)
    //     $rows = DB::table('fashtrans')
    //         ->where('company_id', $recon->company_id)
    //         ->whereRaw('TRIM(bank_id) = ?', [trim($recon->bank_id)])   // safe (no join collation compare)
    //         ->whereIn('trans_type', ['PAYM','DEPS'])
    //         ->whereBetween('trans_dt', [$recon->opening_dt, $recon->closing_dt])
    //         ->orderBy('trans_dt')
    //         ->orderBy('trans_no')
    //         ->orderBy('serial_no')
    //         ->select([
    //             'trans_no',
    //             DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') as trans_dt"),
    //             'serial_no',
    //             'trans_type',
    //             'payto_name',
    //             'remarks',
    //             'ref_no',
    //             'check_no',
    //             'debit_amt',
    //             'credit_amt',
    //         ])
    //         ->get();

    //     // 3) Mark cleared in PHP
    //     $clearedPaym = 0.0;
    //     $clearedDeps = 0.0;

    //     $out = [];
    //     foreach ($rows as $r) {
    //         $key = $r->trans_no . '|' . $r->trans_dt . '|' . $r->serial_no;
    //         $isCleared = isset($clearedSet[$key]) ? 1 : 0;

    //         if ($isCleared) {
    //             if ($r->trans_type === 'PAYM') $clearedPaym += (float)$r->debit_amt;
    //             if ($r->trans_type === 'DEPS') $clearedDeps += (float)$r->credit_amt;
    //         }

    //         $r->is_cleared = $isCleared;
    //         $out[] = $r;
    //     }

    //     $clearedBalance = (float)$recon->opening_balance + $clearedDeps - $clearedPaym;
    //     $difference     = (float)$recon->closing_balance - $clearedBalance;

    //     return response()->json([
    //         'ok' => true,
    //         'recon' => $recon,
    //         'summary' => [
    //             'cleared_deps'    => $clearedDeps,
    //             'cleared_paym'    => $clearedPaym,
    //             'cleared_balance' => $clearedBalance,
    //             'difference'      => $difference,
    //         ],
    //         'rows' => $out,
    //     ]);

    // } // End Method

    // public function BankreconStartStore(Request $request)
    // {
    //     $request->validate([
    //         'bank_id'         => 'required|string',
    //         'opening_balance' => 'required|numeric',
    //         'opening_dt'      => 'required|date',
    //         'closing_balance' => 'required|numeric',
    //         'closing_dt'      => 'required|date|after_or_equal:opening_dt',
    //     ]);

    //     $companyId = config('company.company_id', 'DS02');

    //     $openingDt = \Carbon\Carbon::parse($request->opening_dt)->startOfDay();
    //     $closingDt = \Carbon\Carbon::parse($request->closing_dt)->endOfDay();

    //     // Period MUST come from closing date (statement ending date)
    //     $year  = (int)$closingDt->format('Y');
    //     $month = (int)$closingDt->format('m');

    //     // If recon exists for bank+month, update it; else insert
    //     $existing = DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('bank_id', $request->bank_id)
    //         ->where('period_year', $year)
    //         ->where('period_month', $month)
    //         ->first();

    //     if ($existing) {
    //         DB::table('fashbankrecon')->where('recon_id', $existing->recon_id)->update([
    //             'opening_balance' => $request->opening_balance,
    //             'opening_dt'      => $openingDt,
    //             'closing_balance' => $request->closing_balance,
    //             'closing_dt'      => $closingDt,
    //             'status'          => 'OPEN',
    //             'user_cd'         => '1001',
    //             'update_dt'       => now(),
    //             'update_flag'     => 'Y',
    //         ]);
    //         $reconId = $existing->recon_id;
    //     } else {
    //         $reconId = DB::table('fashbankrecon')->insertGetId([
    //             'company_id'      => $companyId,
    //             'bank_id'         => $request->bank_id,
    //             'period_year'     => $year,
    //             'period_month'    => $month,
    //             'opening_balance' => $request->opening_balance,
    //             'opening_dt'      => $openingDt,
    //             'closing_balance' => $request->closing_balance,
    //             'closing_dt'      => $closingDt,
    //             'status'          => 'OPEN',
    //             'user_cd'         => '1001',
    //             'update_dt'       => now(),
    //             'update_flag'     => 'Y',
    //         ]);
    //     }

    //     // go to transactions screen
    //     return redirect()->route('bankrecon.reconcile', $reconId);

    // } //End Method

    // public function BankreconToggleCleared(Request $request)
    // {
    //     $request->validate([
    //         'recon_id'   => 'required|integer',
    //         'trans_no'   => 'required|integer',
    //         'trans_dt'   => 'required|date',
    //         'serial_no'  => 'required|string',
    //         'checked'    => 'required|in:0,1',
    //     ]);

    //     $reconId  = (int)$request->recon_id;

    //     // normalize trans_dt to the exact format used in DB
    //     $transDt  = \Carbon\Carbon::parse($request->trans_dt)->format('Y-m-d H:i:s');
    //     $transNo  = (int)$request->trans_no;
    //     $serialNo = (string)$request->serial_no;

    //     if ((int)$request->checked === 1) {
    //         // insert if not exists
    //         $exists = DB::table('fashbankrecon_items')
    //             ->where('recon_id', $reconId)
    //             ->where('trans_no', $transNo)
    //             ->where('trans_dt', $transDt)
    //             ->where('serial_no', $serialNo)
    //             ->exists();

    //         if (!$exists) {
    //             DB::table('fashbankrecon_items')->insert([
    //                 'recon_id'     => $reconId,
    //                 'trans_no'     => $transNo,
    //                 'trans_dt'     => $transDt,
    //                 'serial_no'    => $serialNo,
    //                 'cleared_flag' => 'Y',
    //                 'cleared_dt'   => now(),
    //             ]);
    //         }
    //     } else {
    //         // delete
    //         DB::table('fashbankrecon_items')
    //             ->where('recon_id', $reconId)
    //             ->where('trans_no', $transNo)
    //             ->where('trans_dt', $transDt)
    //             ->where('serial_no', $serialNo)
    //             ->delete();
    //     }

    //     return response()->json(['ok' => true]);

    // } // End Method

    // public function BankreconSaveHeader(Request $request)
    // {
    //     $request->validate([
    //         'recon_id'         => 'required|integer',
    //         'opening_balance'  => 'required|numeric',
    //         'opening_dt'       => 'required|date',
    //         'closing_balance'  => 'required|numeric',
    //         'closing_dt'       => 'required|date|after_or_equal:opening_dt',
    //     ]);

    //     $openingDt = Carbon::parse($request->opening_dt)->startOfDay();
    //     $closingDt = Carbon::parse($request->closing_dt)->endOfDay();

    //     $companyId = config('company.company_id', 'DS02');

    //     $recon = DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('recon_id', $request->recon_id)
    //         ->first();

    //     if (!$recon) return response()->json(['ok' => false, 'message' => 'Recon not found'], 404);
    //     if ($recon->status === 'CLOSED') return response()->json(['ok' => false, 'message' => 'Recon is closed'], 422);

    //     DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('recon_id', $request->recon_id)
    //         ->update([
    //             'opening_balance' => $request->opening_balance,
    //             'opening_dt'      => $openingDt,
    //             'closing_balance' => $request->closing_balance,
    //             'closing_dt'      => $closingDt,
    //             'user_cd'         => '1001',
    //             'update_dt'       => now(),
    //             'update_flag'     => 'Y',
    //         ]);

    //     return response()->json(['ok' => true]);

    // } // End Method

    // public function BankreconBulkToggle(Request $request)
    // {
    //     $request->validate([
    //         'recon_id' => 'required|integer',
    //         'checked'  => 'required|in:0,1',
    //         'items'    => 'required|array',
    //         'items.*.trans_no'  => 'required|integer',
    //         'items.*.trans_dt'  => 'required|string',
    //         'items.*.serial_no' => 'required|string',
    //     ]);

    //     $reconId = (int)$request->recon_id;
    //     $checked = (int)$request->checked;

    //     // If recon is closed, block updates
    //     $recon = DB::table('fashbankrecon')->where('recon_id', $reconId)->first();
    //     if (!$recon) return response()->json(['ok' => false, 'message' => 'Recon not found'], 404);
    //     if ($recon->status === 'CLOSED') return response()->json(['ok' => false, 'message' => 'Recon is closed'], 422);

    //     // Normalize items to DB datetime string
    //     $items = [];
    //     foreach ($request->items as $it) {
    //         $items[] = [
    //             'recon_id'  => $reconId,
    //             'trans_no'  => (int)$it['trans_no'],
    //             'trans_dt'  => \Carbon\Carbon::parse($it['trans_dt'])->format('Y-m-d H:i:s'),
    //             'serial_no' => (string)$it['serial_no'],
    //         ];
    //     }

    //     if ($checked === 1) {
    //         // Insert missing rows (ignore duplicates) in chunks
    //         DB::beginTransaction();
    //         try {
    //             foreach (array_chunk($items, 500) as $chunk) {
    //                 $values = [];
    //                 $bindings = [];

    //                 foreach ($chunk as $r) {
    //                     $values[] = "(?, ?, ?, ?, 'Y', NOW())";
    //                     $bindings[] = $r['recon_id'];
    //                     $bindings[] = $r['trans_no'];
    //                     $bindings[] = $r['trans_dt'];
    //                     $bindings[] = $r['serial_no'];
    //                 }

    //                 $sql = "INSERT IGNORE INTO fashbankrecon_items
    //                         (recon_id, trans_no, trans_dt, serial_no, cleared_flag, cleared_dt)
    //                         VALUES " . implode(',', $values);

    //                 DB::statement($sql, $bindings);
    //             }
    //             DB::commit();
    //         } catch (\Throwable $e) {
    //             DB::rollBack();
    //             throw $e;
    //         }

    //     } else {
    //         // Bulk delete (unclear all) — chunked to avoid huge queries
    //         DB::beginTransaction();
    //         try {
    //             foreach (array_chunk($items, 300) as $chunk) {
    //                 foreach ($chunk as $r) {
    //                     DB::table('fashbankrecon_items')
    //                         ->where('recon_id', $reconId)
    //                         ->where('trans_no', $r['trans_no'])
    //                         ->where('trans_dt', $r['trans_dt'])
    //                         ->where('serial_no', $r['serial_no'])
    //                         ->delete();
    //                 }
    //             }
    //             DB::commit();
    //         } catch (\Throwable $e) {
    //             DB::rollBack();
    //             throw $e;
    //         }
    //     }

    //     return response()->json(['ok' => true]);

    // } // End Method


    // public function BankreconClose(Request $request)
    // {
    //     $request->validate([
    //         'recon_id' => 'required|integer',
    //     ]);

    //     $companyId = config('company.company_id', 'DS02');

    //     DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('recon_id', $request->recon_id)
    //         ->update([
    //             'status'     => 'CLOSED',
    //             'user_cd'    => '1001',
    //             'update_dt'  => now(),
    //             'update_flag'=> 'Y',
    //         ]);

    //     return response()->json(['ok' => true]);

    // } // End Method

    // public function BankreconReport($recon_id)
    // {
    //     $companyId = config('company.company_id', 'DS02');

    //     $recon = DB::table('fashbankrecon')->where('company_id', $companyId)->where('recon_id', $recon_id)->first();
    //     abort_if(!$recon, 404);

    //     $bank = DB::table('fashmsbank')
    //         ->where('company_id', $companyId)
    //         ->where('account_id', $recon->bank_id)
    //         ->first();

    //     $items = DB::table('fashtrans as t')
    //         ->join('fashbankrecon_items as i', function ($j) use ($recon_id) {
    //             $j->on('i.trans_no', '=', 't.trans_no')
    //               ->on('i.trans_dt', '=', 't.trans_dt')
    //               ->on('i.serial_no', '=', 't.serial_no')
    //               ->where('i.recon_id', '=', $recon_id);
    //         })
    //         ->where('t.company_id', $companyId)
    //         ->orderBy('t.trans_dt')
    //         ->orderBy('t.trans_no')
    //         ->orderBy('t.serial_no')
    //         ->get();

    //     return view('bankrecon.report', compact('recon','bank','items'));

    // } // End Method

    /**
     * Display the Bank Reconciliation screen.
     */
    public function Bankrecon()
    {
        $companyId = config('company.company_id', 'DS02');

        $banks = DB::table('fashmsbank')
            ->select('account_id', 'account_name')
            ->where('company_id', $companyId)
            ->orderBy('account_name')
            ->get();

        return view('accounts.accounts_bankrecon', compact('banks'));
    }


    /**
     * Load an existing reconciliation or create a new monthly header.
     *
     * Important:
     * - Loading an existing month does NOT overwrite its balances.
     * - A new month automatically receives the previous month's
     *   statement ending balance as its opening balance.
     * - If there is no previous reconciliation, opening balance starts at 0.00
     *   and the user can enter the conversion/opening balance and save it.
     */
    public function BankreconLoadOrCreate(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|string',
            'period'  => 'required|date_format:Y-m',
        ]);

        $companyId = config('company.company_id', 'DS02');
        $bankId    = trim((string) $request->bank_id);

        [$year, $month] = array_map('intval', explode('-', $request->period));

        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->whereRaw('TRIM(bank_id) = ?', [$bankId])
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->first();

        $isNew = false;

        if (!$recon) {
            $periodStart = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
            $periodEnd   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

            $previousPeriod = $periodStart->copy()->subMonthNoOverflow();

            $previousRecon = DB::table('fashbankrecon')
                ->where('company_id', $companyId)
                ->whereRaw('TRIM(bank_id) = ?', [$bankId])
                ->where('period_year', (int) $previousPeriod->format('Y'))
                ->where('period_month', (int) $previousPeriod->format('m'))
                ->first();

            $openingBalance = $previousRecon
                ? (float) $previousRecon->closing_balance
                : 0.00;

            $reconId = DB::table('fashbankrecon')->insertGetId([
                'company_id'      => $companyId,
                'bank_id'         => $bankId,
                'period_year'     => $year,
                'period_month'    => $month,
                'opening_balance' => $openingBalance,
                'opening_dt'      => $periodStart,
                'closing_balance' => 0.00,
                'closing_dt'      => $periodEnd,
                'status'          => 'OPEN',
                'user_cd'         => '1001',
                'update_dt'       => now(),
                'update_flag'     => 'Y',
            ]);

            $recon = DB::table('fashbankrecon')
                ->where('recon_id', $reconId)
                ->first();

            $isNew = true;
        }

        return response()->json([
            'ok'     => true,
            'is_new' => $isNew,
            'recon'  => $recon,
        ]);
    } // End Method



    /**
     * Return all transactions available for this reconciliation.
     *
     * The grid includes:
     * - Transactions through the current statement ending date.
     * - Older transactions that have never cleared.
     * - Transactions already checked in the current reconciliation.
     *
     * The grid excludes:
     * - Transactions cleared in an earlier reconciliation for this bank.
     *
     * IMPORTANT:
     * Reconciliation matching uses:
     *
     *      trans_no + DATE(trans_dt) + serial_no
     *
     * We intentionally ignore the TIME portion of trans_dt because accounting
     * transactions may later be normalized from a timestamp such as
     * 2026-02-02 08:33:10 to 2026-02-02 00:00:00.
     */
    public function BankreconTransactions(Request $request)
    {
        $request->validate([
            'recon_id' => 'required|integer',
        ]);

        $reconId = (int) $request->recon_id;

        /*
        |--------------------------------------------------------------------------
        | Load reconciliation header
        |--------------------------------------------------------------------------
        */
        $recon = DB::table('fashbankrecon')
            ->where('recon_id', $reconId)
            ->first();

        if (!$recon) {
            return response()->json([
                'ok'      => false,
                'message' => 'Reconciliation not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Current reconciliation items
        |--------------------------------------------------------------------------
        |
        | Use DATE only when building the matching key.
        |
        | Example:
        |
        | fashbankrecon_items:
        | 25957 | 2026-02-02 08:33:10 | 000
        |
        | fashtrans:
        | 25957 | 2026-02-02 00:00:00 | 000
        |
        | Both become:
        |
        | 25957|2026-02-02|000
        |
        */
        $currentRows = DB::table('fashbankrecon_items')
            ->where('recon_id', $reconId)
            ->select([
                'trans_no',
                DB::raw(
                    "DATE_FORMAT(trans_dt, '%Y-%m-%d') AS trans_date"
                ),
                'serial_no',
            ])
            ->get();

        $currentSet = [];

        foreach ($currentRows as $item) {

            $key = (string) $item->trans_no
                . '|'
                . $item->trans_date
                . '|'
                . trim((string) $item->serial_no);

            $currentSet[$key] = true;
        }


        /*
        |--------------------------------------------------------------------------
        | Transactions cleared in an EARLIER reconciliation
        |--------------------------------------------------------------------------
        |
        | These transactions must never reappear in a later reconciliation.
        |
        | Again, match by transaction number + transaction DATE + serial number.
        | Do not use the time portion of trans_dt.
        |
        */
        $previousClearedRows = DB::table('fashbankrecon_items as i')
            ->join(
                'fashbankrecon as h',
                'h.recon_id',
                '=',
                'i.recon_id'
            )
            ->where(
                'h.company_id',
                $recon->company_id
            )
            ->whereRaw(
                'TRIM(h.bank_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->where(
                'h.recon_id',
                '<>',
                $reconId
            )
            ->where(
                'i.cleared_flag',
                'Y'
            )
            ->where(function ($query) use ($recon) {

                $query
                    ->where(
                        'h.period_year',
                        '<',
                        $recon->period_year
                    )
                    ->orWhere(function ($query) use ($recon) {

                        $query
                            ->where(
                                'h.period_year',
                                $recon->period_year
                            )
                            ->where(
                                'h.period_month',
                                '<',
                                $recon->period_month
                            );
                    });
            })
            ->select([
                'i.trans_no',
                DB::raw(
                    "DATE_FORMAT(i.trans_dt, '%Y-%m-%d') AS trans_date"
                ),
                'i.serial_no',
            ])
            ->get();

        $previousClearedSet = [];

        foreach ($previousClearedRows as $item) {

            $key = (string) $item->trans_no
                . '|'
                . $item->trans_date
                . '|'
                . trim((string) $item->serial_no);

            $previousClearedSet[$key] = true;
        }


        /*
        |--------------------------------------------------------------------------
        | Determine conversion / reconciliation starting date
        |--------------------------------------------------------------------------
        |
        | The earliest reconciliation header is treated as the conversion cutoff.
        |
        | Example:
        |
        | First reconciliation: December 2025
        |
        | January 2026 should include:
        | - December 2025 outstanding transactions
        | - January 2026 transactions
        |
        | But it should not include old legacy transactions before conversion.
        |
        */
        $firstRecon = DB::table('fashbankrecon')
            ->where(
                'company_id',
                $recon->company_id
            )
            ->whereRaw(
                'TRIM(bank_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->first();

        $conversionStartDt =
            $firstRecon && $firstRecon->opening_dt
            ? \Carbon\Carbon::parse(
                $firstRecon->opening_dt
            )->startOfDay()
            : \Carbon\Carbon::create(
                (int) $recon->period_year,
                (int) $recon->period_month,
                1
            )->startOfDay();


        /*
        |--------------------------------------------------------------------------
        | Load eligible bank transactions
        |--------------------------------------------------------------------------
        |
        | NOTE:
        | We retain the transaction's full datetime in the returned data because
        | the Blade/DataTable may need it. We only remove the TIME portion when
        | constructing the reconciliation matching key.
        |
        */
        $transactions = DB::table('fashtrans')
            ->where(
                'company_id',
                $recon->company_id
            )
            ->whereRaw(
                'TRIM(bank_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->whereIn(
                'trans_type',
                ['PAYM', 'DEPS']
            )
            ->whereBetween(
                'trans_dt',
                [
                    $conversionStartDt,
                    $recon->closing_dt,
                ]
            )
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->select([
                'trans_no',

                DB::raw(
                    "DATE_FORMAT(
                        trans_dt,
                        '%Y-%m-%d %H:%i:%s'
                    ) AS trans_dt"
                ),

                'serial_no',
                'trans_type',
                'payto_name',
                'remarks',
                'ref_no',
                'check_no',
                'debit_amt',
                'credit_amt',
            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Build reconciliation grid
        |--------------------------------------------------------------------------
        */
        $clearedPaym = 0.00;
        $clearedDeps = 0.00;

        $rows = [];

        foreach ($transactions as $transaction) {

            /*
            |--------------------------------------------------------------------------
            | Normalize transaction DATE
            |--------------------------------------------------------------------------
            */
            $transDate = \Carbon\Carbon::parse(
                $transaction->trans_dt
            )->format('Y-m-d');


            /*
            |--------------------------------------------------------------------------
            | Reconciliation matching key
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | Do NOT include the TIME portion.
            |
            */
            $key = (string) $transaction->trans_no
                . '|'
                . $transDate
                . '|'
                . trim((string) $transaction->serial_no);


            /*
            |--------------------------------------------------------------------------
            | Is this transaction checked in CURRENT reconciliation?
            |--------------------------------------------------------------------------
            */
            $isCurrentCleared =
                isset($currentSet[$key]);


            /*
            |--------------------------------------------------------------------------
            | Was this transaction already cleared in an EARLIER period?
            |--------------------------------------------------------------------------
            |
            | If yes, do not allow it to reappear.
            |
            | Exception:
            | If the same item is already stored in the current reconciliation,
            | keep displaying it so the current reconciliation can still be viewed
            | and corrected if necessary.
            |
            */
            if (
                isset($previousClearedSet[$key])
                && !$isCurrentCleared
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Mark row as cleared / uncleared
            |--------------------------------------------------------------------------
            */
            $transaction->is_cleared =
                $isCurrentCleared ? 1 : 0;


            /*
            |--------------------------------------------------------------------------
            | Calculate currently cleared totals
            |--------------------------------------------------------------------------
            */
            if ($isCurrentCleared) {

                if (
                    strtoupper(
                        trim((string) $transaction->trans_type)
                    ) === 'PAYM'
                ) {

                    $clearedPaym +=
                        (float) $transaction->debit_amt;
                }

                if (
                    strtoupper(
                        trim((string) $transaction->trans_type)
                    ) === 'DEPS'
                ) {

                    $clearedDeps +=
                        (float) $transaction->credit_amt;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Add transaction to grid
            |--------------------------------------------------------------------------
            */
            $rows[] = $transaction;
        }


        /*
        |--------------------------------------------------------------------------
        | Build reconciliation summary
        |--------------------------------------------------------------------------
        */
        $summary = $this->bankreconBuildSummary(
            $recon,
            $clearedPaym,
            $clearedDeps
        );


        /*
        |--------------------------------------------------------------------------
        | Return JSON
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'ok'      => true,
            'recon'   => $recon,
            'summary' => $summary,
            'rows'    => $rows,
        ]);
    } // End Method

    /**
     * Legacy start/store method retained in case an existing route uses it.
     */
    public function BankreconStartStore(Request $request)
    {
        $request->validate([
            'bank_id'         => 'required|string',
            'opening_balance' => 'required|numeric',
            'opening_dt'      => 'required|date',
            'closing_balance' => 'required|numeric',
            'closing_dt'      => 'required|date|after_or_equal:opening_dt',
        ]);

        $companyId = config('company.company_id', 'DS02');
        $bankId    = trim((string) $request->bank_id);
        $openingDt = \Carbon\Carbon::parse($request->opening_dt)->startOfDay();
        $closingDt = \Carbon\Carbon::parse($request->closing_dt)->endOfDay();
        $year      = (int) $closingDt->format('Y');
        $month     = (int) $closingDt->format('m');

        $existing = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->whereRaw('TRIM(bank_id) = ?', [$bankId])
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->first();

        $values = [
            'opening_balance' => (float) $request->opening_balance,
            'opening_dt'      => $openingDt,
            'closing_balance' => (float) $request->closing_balance,
            'closing_dt'      => $closingDt,
            'status'          => 'OPEN',
            'user_cd'         => '1001',
            'update_dt'       => now(),
            'update_flag'     => 'Y',
        ];

        if ($existing) {
            DB::table('fashbankrecon')
                ->where('recon_id', $existing->recon_id)
                ->update($values);

            $reconId = $existing->recon_id;
        } else {
            $reconId = DB::table('fashbankrecon')->insertGetId(array_merge(
                $values,
                [
                    'company_id'  => $companyId,
                    'bank_id'     => $bankId,
                    'period_year' => $year,
                    'period_month' => $month,
                ]
            ));
        }

        return redirect()->route('bankrecon.reconcile', $reconId);
    }


    /**
     * Check or uncheck one transaction.
     *
     * Returns the recalculated summary so the Blade does not have to reload
     * the DataTable. This prevents the screen from jumping back to the top.
     */
    public function BankreconToggleCleared(Request $request)
    {
        $request->validate([
            'recon_id'         => 'required|integer',
            'trans_no'         => 'required|integer',
            'trans_dt'         => 'required|date',
            'serial_no'        => 'required|string',
            'checked'          => 'required|in:0,1',
            'opening_balance'  => 'required|numeric',
            'closing_balance'  => 'required|numeric',
            'closing_dt'       => 'required|date',
        ]);

        $reconId = (int) $request->recon_id;

        $recon = DB::table('fashbankrecon')
            ->where('recon_id', $reconId)
            ->first();

        if (!$recon) {
            return response()->json([
                'ok'      => false,
                'message' => 'Reconciliation not found.',
            ], 404);
        }

        if ($recon->status === 'CLOSED') {
            return response()->json([
                'ok'      => false,
                'message' => 'This reconciliation is closed.',
            ], 422);
        }

        /*
         * Save the balances currently visible on the screen before
         * recalculating the reconciliation summary. This prevents the
         * summary from using old 0.00 values when the user checks a row
         * before pressing Save Balances.
         */
        $periodStart = \Carbon\Carbon::create(
            (int) $recon->period_year,
            (int) $recon->period_month,
            1
        )->startOfDay();

        $closingDt = \Carbon\Carbon::parse($request->closing_dt)->endOfDay();

        DB::table('fashbankrecon')
            ->where('recon_id', $reconId)
            ->update([
                'opening_balance' => (float) $request->opening_balance,
                'opening_dt'      => $periodStart,
                'closing_balance' => (float) $request->closing_balance,
                'closing_dt'      => $closingDt,
                'user_cd'         => '1001',
                'update_dt'       => now(),
                'update_flag'     => 'Y',
            ]);

        $recon = DB::table('fashbankrecon')
            ->where('recon_id', $reconId)
            ->first();

        $transDt  = \Carbon\Carbon::parse($request->trans_dt)->format('Y-m-d H:i:s');
        $transNo  = (int) $request->trans_no;
        $serialNo = (string) $request->serial_no;

        if ((int) $request->checked === 1) {
            $exists = DB::table('fashbankrecon_items')
                ->where('recon_id', $reconId)
                ->where('trans_no', $transNo)
                ->where('trans_dt', $transDt)
                ->where('serial_no', $serialNo)
                ->exists();

            if (!$exists) {
                DB::table('fashbankrecon_items')->insert([
                    'recon_id'     => $reconId,
                    'trans_no'     => $transNo,
                    'trans_dt'     => $transDt,
                    'serial_no'    => $serialNo,
                    'cleared_flag' => 'Y',
                    'cleared_dt'   => now(),
                ]);
            }
        } else {
            DB::table('fashbankrecon_items')
                ->where('recon_id', $reconId)
                ->where('trans_no', $transNo)
                ->where('trans_dt', $transDt)
                ->where('serial_no', $serialNo)
                ->delete();
        }

        $summary = $this->bankreconSummaryFromDatabase($recon);

        return response()->json([
            'ok'      => true,
            'summary' => $summary,
        ]);
    }


    /**
     * Save reconciliation balances and statement ending date.
     */
    public function BankreconSaveHeader(Request $request)
    {
        $request->validate([
            'recon_id'         => 'required|integer',
            'opening_balance'  => 'required|numeric',
            'closing_balance'  => 'required|numeric',
            'closing_dt'       => 'required|date',
        ]);

        $companyId = config('company.company_id', 'DS02');

        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('recon_id', $request->recon_id)
            ->first();

        if (!$recon) {
            return response()->json([
                'ok'      => false,
                'message' => 'Reconciliation not found.',
            ], 404);
        }

        if ($recon->status === 'CLOSED') {
            return response()->json([
                'ok'      => false,
                'message' => 'This reconciliation is closed.',
            ], 422);
        }

        $periodStart = \Carbon\Carbon::create(
            (int) $recon->period_year,
            (int) $recon->period_month,
            1
        )->startOfDay();

        $closingDt = \Carbon\Carbon::parse($request->closing_dt)->endOfDay();

        if ($closingDt->lt($periodStart)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Statement ending date cannot be before the reconciliation month.',
            ], 422);
        }

        DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('recon_id', $recon->recon_id)
            ->update([
                'opening_balance' => (float) $request->opening_balance,
                'opening_dt'      => $periodStart,
                'closing_balance' => (float) $request->closing_balance,
                'closing_dt'      => $closingDt,
                'user_cd'         => '1001',
                'update_dt'       => now(),
                'update_flag'     => 'Y',
            ]);

        $recon = DB::table('fashbankrecon')
            ->where('recon_id', $recon->recon_id)
            ->first();

        $summary = $this->bankreconSummaryFromDatabase($recon);

        return response()->json([
            'ok'      => true,
            'recon'   => $recon,
            'summary' => $summary,
        ]);
    }


    /**
     * Check or uncheck every transaction currently loaded in the DataTable.
     */
    public function BankreconBulkToggle(Request $request)
    {
        $request->validate([
            'recon_id'              => 'required|integer',
            'checked'               => 'required|in:0,1',
            'items'                 => 'required|array',
            'items.*.trans_no'      => 'required|integer',
            'items.*.trans_dt'      => 'required|string',
            'items.*.serial_no'     => 'required|string',
            'opening_balance'       => 'required|numeric',
            'closing_balance'       => 'required|numeric',
            'closing_dt'            => 'required|date',
        ]);

        $reconId = (int) $request->recon_id;
        $checked = (int) $request->checked;

        $recon = DB::table('fashbankrecon')
            ->where('recon_id', $reconId)
            ->first();

        if (!$recon) {
            return response()->json([
                'ok'      => false,
                'message' => 'Reconciliation not found.',
            ], 404);
        }

        if ($recon->status === 'CLOSED') {
            return response()->json([
                'ok'      => false,
                'message' => 'This reconciliation is closed.',
            ], 422);
        }

        $periodStart = \Carbon\Carbon::create(
            (int) $recon->period_year,
            (int) $recon->period_month,
            1
        )->startOfDay();

        $closingDt = \Carbon\Carbon::parse($request->closing_dt)->endOfDay();

        DB::table('fashbankrecon')
            ->where('recon_id', $reconId)
            ->update([
                'opening_balance' => (float) $request->opening_balance,
                'opening_dt'      => $periodStart,
                'closing_balance' => (float) $request->closing_balance,
                'closing_dt'      => $closingDt,
                'user_cd'         => '1001',
                'update_dt'       => now(),
                'update_flag'     => 'Y',
            ]);

        $recon = DB::table('fashbankrecon')
            ->where('recon_id', $reconId)
            ->first();

        $items = [];

        foreach ($request->items as $item) {
            $items[] = [
                'recon_id'  => $reconId,
                'trans_no'  => (int) $item['trans_no'],
                'trans_dt'  => \Carbon\Carbon::parse($item['trans_dt'])->format('Y-m-d H:i:s'),
                'serial_no' => (string) $item['serial_no'],
            ];
        }

        DB::beginTransaction();

        try {
            if ($checked === 1) {
                foreach (array_chunk($items, 500) as $chunk) {
                    $values   = [];
                    $bindings = [];

                    foreach ($chunk as $row) {
                        $values[] = "(?, ?, ?, ?, 'Y', NOW())";
                        $bindings[] = $row['recon_id'];
                        $bindings[] = $row['trans_no'];
                        $bindings[] = $row['trans_dt'];
                        $bindings[] = $row['serial_no'];
                    }

                    if (!empty($values)) {
                        DB::statement(
                            "INSERT IGNORE INTO fashbankrecon_items
                             (recon_id, trans_no, trans_dt, serial_no, cleared_flag, cleared_dt)
                             VALUES " . implode(',', $values),
                            $bindings
                        );
                    }
                }
            } else {
                foreach (array_chunk($items, 300) as $chunk) {
                    foreach ($chunk as $row) {
                        DB::table('fashbankrecon_items')
                            ->where('recon_id', $reconId)
                            ->where('trans_no', $row['trans_no'])
                            ->where('trans_dt', $row['trans_dt'])
                            ->where('serial_no', $row['serial_no'])
                            ->delete();
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $summary = $this->bankreconSummaryFromDatabase($recon);

        return response()->json([
            'ok'      => true,
            'summary' => $summary,
        ]);
    }


    /**
     * Close the reconciliation only when it balances.
     */
    // public function BankreconClose(Request $request)
    // {
    //     $request->validate([
    //         'recon_id' => 'required|integer',
    //     ]);

    //     $companyId = config('company.company_id', 'DS02');

    //     $recon = DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('recon_id', $request->recon_id)
    //         ->first();

    //     if (!$recon) {
    //         return response()->json([
    //             'ok'      => false,
    //             'message' => 'Reconciliation not found.',
    //         ], 404);
    //     }

    //     if ($recon->status === 'CLOSED') {
    //         return response()->json([
    //             'ok'      => false,
    //             'message' => 'This reconciliation is already closed.',
    //         ], 422);
    //     }

    //     $summary = $this->bankreconSummaryFromDatabase($recon);

    //     if (abs((float) $summary['difference']) >= 0.005) {
    //         return response()->json([
    //             'ok'      => false,
    //             'message' => 'The reconciliation cannot be closed until the difference is 0.00.',
    //             'summary' => $summary,
    //         ], 422);
    //     }

    //     DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('recon_id', $recon->recon_id)
    //         ->update([
    //             'status'      => 'CLOSED',
    //             'user_cd'     => '1001',
    //             'update_dt'   => now(),
    //             'update_flag' => 'Y',
    //         ]);

    //     return response()->json([
    //         'ok'      => true,
    //         'status'  => 'CLOSED',
    //         'summary' => $summary,
    //     ]);
    // }

    public function BankreconClose(Request $request)
    {
        $request->validate([
            'recon_id' => 'required|integer',
        ]);

        $companyId = config('company.company_id', 'DS02');

        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('recon_id', $request->recon_id)
            ->first();

        if (!$recon) {
            return response()->json([
                'ok'      => false,
                'message' => 'Reconciliation not found.',
            ], 404);
        }

        if ($recon->status === 'CLOSED') {
            return response()->json([
                'ok'      => false,
                'message' => 'This reconciliation is already closed.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify reconciliation balances
        |--------------------------------------------------------------------------
        */
        $summary = $this->bankreconSummaryFromDatabase($recon);

        if (abs((float) $summary['difference']) >= 0.005) {
            return response()->json([
                'ok'      => false,
                'message' => 'The reconciliation cannot be closed until the difference is 0.00.',
                'summary' => $summary,
            ], 422);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Build historical snapshot BEFORE closing
            |--------------------------------------------------------------------------
            */
            $this->bankreconCreateSnapshot($recon);

            /*
            |--------------------------------------------------------------------------
            | Close reconciliation
            |--------------------------------------------------------------------------
            */
            DB::table('fashbankrecon')
                ->where('company_id', $companyId)
                ->where('recon_id', $recon->recon_id)
                ->update([
                    'status'      => 'CLOSED',
                    'user_cd'     => '1001',
                    'update_dt'   => now(),
                    'update_flag' => 'Y',
                ]);

            DB::commit();
        } catch (\Throwable $exception) {

            DB::rollBack();

            throw $exception;
        }

        return response()->json([
            'ok'      => true,
            'status'  => 'CLOSED',
            'summary' => $summary,
        ]);
    } // End Method

    /**
     * Create a permanent historical snapshot for a CLOSED reconciliation.
     *
     * IMPORTANT:
     * Reconciliation matching uses:
     *
     *      trans_no + DATE(trans_dt) + serial_no
     *
     * The TIME portion of trans_dt is intentionally ignored because
     * transaction timestamps may later be normalized to midnight.
     */
    private function bankreconCreateSnapshot($recon): void
    {
        /*
        |--------------------------------------------------------------------------
        | Current reconciliation cleared items
        |--------------------------------------------------------------------------
        */
        $currentItems = DB::table('fashbankrecon_items')
            ->where('recon_id', $recon->recon_id)
            ->where('cleared_flag', 'Y')
            ->select([
                'trans_no',
                DB::raw(
                    "DATE_FORMAT(trans_dt, '%Y-%m-%d') AS trans_date"
                ),
                'serial_no',
            ])
            ->get();

        $currentSet = [];

        foreach ($currentItems as $item) {
            $key = (string) $item->trans_no
                . '|'
                . $item->trans_date
                . '|'
                . trim((string) $item->serial_no);

            $currentSet[$key] = true;
        }


        /*
        |--------------------------------------------------------------------------
        | Transactions cleared in PREVIOUS reconciliations
        |--------------------------------------------------------------------------
        |
        | These transactions must not become outstanding items in this
        | reconciliation.
        |
        */
        $previousClearedItems = DB::table('fashbankrecon_items as i')
            ->join(
                'fashbankrecon as h',
                'h.recon_id',
                '=',
                'i.recon_id'
            )
            ->where(
                'h.company_id',
                $recon->company_id
            )
            ->whereRaw(
                'TRIM(h.bank_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->where(
                'h.recon_id',
                '<>',
                $recon->recon_id
            )
            ->where(
                'i.cleared_flag',
                'Y'
            )
            ->where(function ($query) use ($recon) {
                $query
                    ->where(
                        'h.period_year',
                        '<',
                        $recon->period_year
                    )
                    ->orWhere(function ($query) use ($recon) {
                        $query
                            ->where(
                                'h.period_year',
                                $recon->period_year
                            )
                            ->where(
                                'h.period_month',
                                '<',
                                $recon->period_month
                            );
                    });
            })
            ->select([
                'i.trans_no',
                DB::raw(
                    "DATE_FORMAT(i.trans_dt, '%Y-%m-%d') AS trans_date"
                ),
                'i.serial_no',
            ])
            ->get();

        $previousClearedSet = [];

        foreach ($previousClearedItems as $item) {
            $key = (string) $item->trans_no
                . '|'
                . $item->trans_date
                . '|'
                . trim((string) $item->serial_no);

            $previousClearedSet[$key] = true;
        }


        /*
        |--------------------------------------------------------------------------
        | Determine conversion/reconciliation starting date
        |--------------------------------------------------------------------------
        */
        $firstRecon = DB::table('fashbankrecon')
            ->where(
                'company_id',
                $recon->company_id
            )
            ->whereRaw(
                'TRIM(bank_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->first();

        $conversionStartDt =
            $firstRecon && $firstRecon->opening_dt
            ? \Carbon\Carbon::parse(
                $firstRecon->opening_dt
            )->startOfDay()
            : \Carbon\Carbon::create(
                (int) $recon->period_year,
                (int) $recon->period_month,
                1
            )->startOfDay();


        /*
        |--------------------------------------------------------------------------
        | Load current bank transaction data
        |--------------------------------------------------------------------------
        |
        | This snapshot is created at reconciliation close time.
        |
        */
        $transactions = DB::table('fashtrans')
            ->where(
                'company_id',
                $recon->company_id
            )
            ->whereRaw(
                'TRIM(bank_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->whereIn(
                'trans_type',
                ['PAYM', 'DEPS']
            )
            ->whereBetween(
                'trans_dt',
                [
                    $conversionStartDt,
                    $recon->closing_dt,
                ]
            )
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->select([
                'trans_no',
                'trans_dt',
                'serial_no',
                'trans_type',
                'payto_name',
                'remarks',
                'ref_no',
                'check_no',
                'debit_amt',
                'credit_amt',
            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Remove existing snapshot for this reconciliation
        |--------------------------------------------------------------------------
        |
        | Normally a CLOSED reconciliation should only be snapshotted once.
        | This delete allows controlled rebuilding/backfill when required.
        |
        */
        DB::table('fashbankrecon_snapshot_items')
            ->where(
                'recon_id',
                $recon->recon_id
            )
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Build snapshot rows
        |--------------------------------------------------------------------------
        */
        $insertRows = [];

        foreach ($transactions as $transaction) {

            /*
            |--------------------------------------------------------------------------
            | Normalize DATE only for reconciliation matching
            |--------------------------------------------------------------------------
            */
            $transDate = \Carbon\Carbon::parse(
                $transaction->trans_dt
            )->format('Y-m-d');

            /*
            |--------------------------------------------------------------------------
            | Matching key
            |--------------------------------------------------------------------------
            */
            $key = (string) $transaction->trans_no
                . '|'
                . $transDate
                . '|'
                . trim((string) $transaction->serial_no);


            /*
            |--------------------------------------------------------------------------
            | Was this transaction cleared in THIS reconciliation?
            |--------------------------------------------------------------------------
            */
            $isCurrentCleared =
                isset($currentSet[$key]);


            /*
            |--------------------------------------------------------------------------
            | Ignore transactions cleared in an earlier reconciliation
            |--------------------------------------------------------------------------
            |
            | Unless they are explicitly present in this current reconciliation.
            |
            */
            if (
                isset($previousClearedSet[$key])
                && !$isCurrentCleared
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Preserve current full transaction datetime in snapshot
            |--------------------------------------------------------------------------
            |
            | We only ignore TIME for matching. We still store the transaction's
            | current full datetime in the snapshot.
            |
            */
            $snapshotTransDt = \Carbon\Carbon::parse(
                $transaction->trans_dt
            )->format('Y-m-d H:i:s');


            $insertRows[] = [
                'recon_id'     => $recon->recon_id,

                'trans_no'     => (int) $transaction->trans_no,
                'trans_dt'     => $snapshotTransDt,
                'serial_no'    => trim(
                    (string) $transaction->serial_no
                ),

                'trans_type'   => trim(
                    (string) $transaction->trans_type
                ),

                'payto_name'   => $transaction->payto_name,
                'remarks'      => $transaction->remarks,
                'ref_no'       => $transaction->ref_no,
                'check_no'     => $transaction->check_no,

                'debit_amt'    => (float) (
                    $transaction->debit_amt ?? 0
                ),

                'credit_amt'   => (float) (
                    $transaction->credit_amt ?? 0
                ),

                'cleared_flag' => $isCurrentCleared
                    ? 'Y'
                    : 'N',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Bulk insert snapshot
        |--------------------------------------------------------------------------
        */
        foreach (
            array_chunk(
                $insertRows,
                500
            ) as $chunk
        ) {
            if (!empty($chunk)) {
                DB::table(
                    'fashbankrecon_snapshot_items'
                )->insert($chunk);
            }
        }
    } // End Method

    public function BankreconBackfillSnapshots()
    {
        $companyId = config('company.company_id', 'DS02');

        $closedRecons = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('status', 'CLOSED')
            ->orderBy('bank_id')
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->get();

        $created = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($closedRecons as $recon) {

            /*
            |--------------------------------------------------------------------------
            | Do not rebuild an existing snapshot
            |--------------------------------------------------------------------------
            */
            $snapshotExists = DB::table('fashbankrecon_snapshot_items')
                ->where('recon_id', $recon->recon_id)
                ->exists();

            if ($snapshotExists) {
                $skipped++;
                continue;
            }

            try {

                DB::beginTransaction();

                /*
                |--------------------------------------------------------------------------
                | Create snapshot using the method we already added
                |--------------------------------------------------------------------------
                */
                $this->bankreconCreateSnapshot($recon);

                /*
                |--------------------------------------------------------------------------
                | Populate closed_dt for older reconciliations
                |--------------------------------------------------------------------------
                |
                | Since these reconciliations were closed before closed_dt existed,
                | update_dt is the best available historical approximation.
                |
                */
                if (empty($recon->closed_dt)) {

                    DB::table('fashbankrecon')
                        ->where('recon_id', $recon->recon_id)
                        ->update([
                            'closed_dt' => $recon->update_dt ?: now(),
                        ]);
                }

                DB::commit();

                $created++;
            } catch (\Throwable $exception) {

                DB::rollBack();

                $errors[] = [
                    'recon_id' => $recon->recon_id,
                    'bank_id'  => trim((string) $recon->bank_id),
                    'period'   => $recon->period_year
                        . '-'
                        . str_pad(
                            (string) $recon->period_month,
                            2,
                            '0',
                            STR_PAD_LEFT
                        ),
                    'message'   => $exception->getMessage(),
                ];
            }
        }

        return response()->json([
            'ok'      => empty($errors),
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    } // End Method

    /**
     * Bank Reconciliation History Report.
     *
     * Allows the user to select:
     * - Bank
     * - Year
     * - Month
     *
     * CLOSED reconciliations are always displayed from the historical
     * snapshot table and never reconstructed from live fashtrans data.
     */
    public function BankreconHistory(Request $request)
    {
        $companyId = config('company.company_id', 'DS02');

        /*
        |--------------------------------------------------------------------------
        | Banks
        |--------------------------------------------------------------------------
        */
        $banks = DB::table('fashmsbank')
            ->select(
                'account_id',
                'account_name'
            )
            ->where('company_id', $companyId)
            ->orderBy('account_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Available reconciliation years
        |--------------------------------------------------------------------------
        |
        | Only show years where a CLOSED reconciliation exists.
        |
        */
        $years = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('status', 'CLOSED')
            ->select('period_year')
            ->distinct()
            ->orderBy('period_year', 'desc')
            ->pluck('period_year');

        /*
        |--------------------------------------------------------------------------
        | Selected filter values
        |--------------------------------------------------------------------------
        */
        $selectedBank  = trim((string) $request->get('bank_id', ''));
        $selectedYear  = $request->filled('year')
            ? (int) $request->year
            : null;

        $selectedMonth = $request->filled('month')
            ? (int) $request->month
            : null;

        $reportData = null;
        $message    = null;

        /*
        |--------------------------------------------------------------------------
        | Load report only after Bank + Year + Month are selected
        |--------------------------------------------------------------------------
        */
        if (
            $selectedBank !== ''
            && $selectedYear
            && $selectedMonth
        ) {

            $recon = DB::table('fashbankrecon')
                ->where('company_id', $companyId)
                ->whereRaw(
                    'TRIM(bank_id) = ?',
                    [$selectedBank]
                )
                ->where('period_year', $selectedYear)
                ->where('period_month', $selectedMonth)
                ->where('status', 'CLOSED')
                ->first();

            if (!$recon) {

                $message =
                    'No closed bank reconciliation was found for the selected bank and period.';
            } else {

                $reportData =
                    $this->bankreconHistoryReportData(
                        $recon
                    );
            }
        }

        return view(
            'accounts.accounts_bankrecon_history',
            compact(
                'banks',
                'years',
                'selectedBank',
                'selectedYear',
                'selectedMonth',
                'reportData',
                'message'
            )
        );
    } // End Method


    /**
     * Build Bank Reconciliation History Report data.
     *
     * IMPORTANT:
     * This method reads CLOSED reconciliation transactions only from
     * fashbankrecon_snapshot_items.
     *
     * It does NOT read transaction amounts from live fashtrans.
     */
    private function bankreconHistoryReportData($recon): array
    {
        $companyId = $recon->company_id;

        /*
        |--------------------------------------------------------------------------
        | Bank information
        |--------------------------------------------------------------------------
        */
        $bank = DB::table('fashmsbank')
            ->where(
                'company_id',
                $companyId
            )
            ->whereRaw(
                'TRIM(account_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Cleared Deposits
        |--------------------------------------------------------------------------
        */
        $clearedDeposits =
            DB::table(
                'fashbankrecon_snapshot_items'
            )
            ->where(
                'recon_id',
                $recon->recon_id
            )
            ->where(
                'trans_type',
                'DEPS'
            )
            ->where(
                'cleared_flag',
                'Y'
            )
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Uncleared Deposits
        |--------------------------------------------------------------------------
        */
        $unclearedDeposits =
            DB::table(
                'fashbankrecon_snapshot_items'
            )
            ->where(
                'recon_id',
                $recon->recon_id
            )
            ->where(
                'trans_type',
                'DEPS'
            )
            ->where(
                'cleared_flag',
                'N'
            )
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Cleared Payments
        |--------------------------------------------------------------------------
        */
        $clearedPayments =
            DB::table(
                'fashbankrecon_snapshot_items'
            )
            ->where(
                'recon_id',
                $recon->recon_id
            )
            ->where(
                'trans_type',
                'PAYM'
            )
            ->where(
                'cleared_flag',
                'Y'
            )
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Uncleared Payments
        |--------------------------------------------------------------------------
        */
        $unclearedPayments =
            DB::table(
                'fashbankrecon_snapshot_items'
            )
            ->where(
                'recon_id',
                $recon->recon_id
            )
            ->where(
                'trans_type',
                'PAYM'
            )
            ->where(
                'cleared_flag',
                'N'
            )
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */
        $totals = [

            'cleared_deposits' =>
            round(
                $clearedDeposits->sum(
                    fn($row) =>
                    (float) $row->credit_amt
                ),
                2
            ),

            'uncleared_deposits' =>
            round(
                $unclearedDeposits->sum(
                    fn($row) =>
                    (float) $row->credit_amt
                ),
                2
            ),

            'cleared_payments' =>
            round(
                $clearedPayments->sum(
                    fn($row) =>
                    (float) $row->debit_amt
                ),
                2
            ),

            'uncleared_payments' =>
            round(
                $unclearedPayments->sum(
                    fn($row) =>
                    (float) $row->debit_amt
                ),
                2
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Cleared Balance
        |--------------------------------------------------------------------------
        |
        | Beginning Statement Balance
        | + Cleared Deposits
        | - Cleared Payments
        |
        */
        $totals['cleared_balance'] =
            round(
                (float) $recon->opening_balance
                    + $totals['cleared_deposits']
                    - $totals['cleared_payments'],
                2
            );

        /*
        |--------------------------------------------------------------------------
        | Reconciliation Difference
        |--------------------------------------------------------------------------
        */
        $totals['difference'] =
            round(
                (float) $recon->closing_balance
                    - $totals['cleared_balance'],
                2
            );

        /*
        |--------------------------------------------------------------------------
        | Outstanding transaction count
        |--------------------------------------------------------------------------
        */
        $totals['cleared_deposit_count'] =
            $clearedDeposits->count();

        $totals['uncleared_deposit_count'] =
            $unclearedDeposits->count();

        $totals['cleared_payment_count'] =
            $clearedPayments->count();

        $totals['uncleared_payment_count'] =
            $unclearedPayments->count();

        /*
        |--------------------------------------------------------------------------
        | Adjusted Book Balance
        |--------------------------------------------------------------------------
        |
        | Statement Ending Balance
        | + Outstanding Payments
        | - Outstanding Deposits
        |
        | This is useful for displaying the relationship between the statement
        | balance and the outstanding transactions.
        |
        */
        $totals['adjusted_book_balance'] =
            round(
                (float) $recon->closing_balance
                    + $totals['uncleared_payments']
                    - $totals['uncleared_deposits'],
                2
            );

        return [
            'recon'               => $recon,
            'bank'                => $bank,

            'clearedDeposits'     => $clearedDeposits,
            'unclearedDeposits'   => $unclearedDeposits,

            'clearedPayments'     => $clearedPayments,
            'unclearedPayments'   => $unclearedPayments,

            'totals'              => $totals,
        ];
    } // End Method

    /**
     * Export historical Bank Reconciliation Report to PDF.
     */
    public function BankreconHistoryPdf(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|string',
            'year'    => 'required|integer',
            'month'   => 'required|integer|min:1|max:12',
        ]);

        //$companyId = config('company.comp_nm', 'DS02');
        $companyId = 'DS02';

        $bankId = trim((string) $request->bank_id);
        $year   = (int) $request->year;
        $month  = (int) $request->month;

        /*
        |--------------------------------------------------------------------------
        | Find CLOSED reconciliation
        |--------------------------------------------------------------------------
        */
        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->whereRaw(
                'TRIM(bank_id) = ?',
                [$bankId]
            )
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->where('status', 'CLOSED')
            ->first();

        abort_if(
            !$recon,
            404,
            'Closed reconciliation not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Verify snapshot exists
        |--------------------------------------------------------------------------
        |
        | A historical CLOSED report must come from the frozen snapshot.
        |
        */
        $snapshotExists = DB::table('fashbankrecon_snapshot_items')
            ->where('recon_id', $recon->recon_id)
            ->exists();

        abort_if(
            !$snapshotExists,
            404,
            'Historical snapshot was not found for this reconciliation.'
        );

        /*
        |--------------------------------------------------------------------------
        | Load frozen historical data
        |--------------------------------------------------------------------------
        */
        $reportData = $this->bankreconHistoryReportData(
            $recon
        );

        /*
        |--------------------------------------------------------------------------
        | Render PDF Blade
        |--------------------------------------------------------------------------
        */
        $html = view(
            'accounts.accounts_bankrecon_history_pdf',
            $reportData
        )->render();

        /*
        |--------------------------------------------------------------------------
        | mPDF Temp Folder
        |--------------------------------------------------------------------------
        */
        $tempDir = storage_path('app/mpdf-temp');

        if (!is_dir($tempDir)) {
            mkdir(
                $tempDir,
                0775,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create PDF
        |--------------------------------------------------------------------------
        */
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'orientation'   => 'L',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 12,
            'tempDir'       => $tempDir,
            'simpleTables'  => true,
        ]);

        $mpdf->SetTitle(
            'Bank Reconciliation History'
        );

        $mpdf->SetAuthor(
            config('app.name')
        );

        $mpdf->SetDisplayMode(
            'fullpage'
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent oversized tables from causing abnormal pagination
        |--------------------------------------------------------------------------
        */
        $mpdf->shrink_tables_to_fit = 1;

        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-size:7pt;color:#666;">'
                . '<tr>'
                . '<td width="50%">'
                . 'Printed: {DATE m/d/Y h:i A}'
                . '</td>'
                . '<td width="50%" style="text-align:right;">'
                . 'Page {PAGENO} of {nbpg}'
                . '</td>'
                . '</tr>'
                . '</table>'
        );

        $mpdf->WriteHTML($html);

        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */
        $fileName =
            'Bank_Reconciliation_History_'
            . preg_replace(
                '/[^A-Za-z0-9_-]/',
                '_',
                $bankId
            )
            . '_'
            . $year
            . '_'
            . str_pad(
                (string) $month,
                2,
                '0',
                STR_PAD_LEFT
            )
            . '.pdf';

        /*
        |--------------------------------------------------------------------------
        | Display PDF in browser
        |--------------------------------------------------------------------------
        */
        return response(
            $mpdf->Output(
                $fileName,
                \Mpdf\Output\Destination::STRING_RETURN
            ),
            200,
            [
                'Content-Type' =>
                'application/pdf',

                'Content-Disposition' =>
                'inline; filename="'
                    . $fileName
                    . '"',
            ]
        );
    } // End Method


    /**
     * Export historical Bank Reconciliation Report to Excel.
     */
    public function BankreconHistoryExcel(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|string',
            'year'    => 'required|integer',
            'month'   => 'required|integer|min:1|max:12',
        ]);

        //$companyId = config('company.comp_nm', 'DS02');
        $companyId = 'DS02';

        $bankId = trim((string) $request->bank_id);
        $year   = (int) $request->year;
        $month  = (int) $request->month;

        /*
        |--------------------------------------------------------------------------
        | Find CLOSED reconciliation
        |--------------------------------------------------------------------------
        */
        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->whereRaw(
                'TRIM(bank_id) = ?',
                [$bankId]
            )
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->where('status', 'CLOSED')
            ->first();

        abort_if(
            !$recon,
            404,
            'Closed reconciliation not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Verify historical snapshot exists
        |--------------------------------------------------------------------------
        */
        $snapshotExists = DB::table('fashbankrecon_snapshot_items')
            ->where('recon_id', $recon->recon_id)
            ->exists();

        abort_if(
            !$snapshotExists,
            404,
            'Historical snapshot was not found for this reconciliation.'
        );

        /*
        |--------------------------------------------------------------------------
        | Get exactly the same frozen data used by screen and PDF
        |--------------------------------------------------------------------------
        */
        $reportData = $this->bankreconHistoryReportData(
            $recon
        );

        /*
        |--------------------------------------------------------------------------
        | Excel Filename
        |--------------------------------------------------------------------------
        */
        $fileName =
            'Bank_Reconciliation_History_'
            . preg_replace(
                '/[^A-Za-z0-9_-]/',
                '_',
                $bankId
            )
            . '_'
            . $year
            . '_'
            . str_pad(
                (string) $month,
                2,
                '0',
                STR_PAD_LEFT
            )
            . '.xlsx';

        return Excel::download(
            new BankReconciliationHistoryExport(
                $reportData
            ),
            $fileName
        );
    } // End Method

    // /**
    //  * Existing reconciliation report.
    //  */
    // /**
    //  * Bank Reconciliation report.
    //  *
    //  * Shows four sections:
    //  * 1. Cleared deposits
    //  * 2. Uncleared deposits
    //  * 3. Cleared payments
    //  * 4. Uncleared payments
    //  */
    // public function BankreconReport($recon_id)
    // {
    //     $companyId = config('company.company_id', 'DS02');

    //     $recon = DB::table('fashbankrecon')
    //         ->where('company_id', $companyId)
    //         ->where('recon_id', $recon_id)
    //         ->first();

    //     abort_if(!$recon, 404);

    //     $bank = DB::table('fashmsbank')
    //         ->where('company_id', $companyId)
    //         ->whereRaw('TRIM(account_id) = ?', [trim((string) $recon->bank_id)])
    //         ->first();

    //     /*
    //      * Current reconciliation items.
    //      */
    //     $currentItems = DB::table('fashbankrecon_items')
    //         ->where('recon_id', $recon->recon_id)
    //         ->select([
    //             'trans_no',
    //             DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt_str"),
    //             'serial_no',
    //         ])
    //         ->get();

    //     $currentSet = [];

    //     foreach ($currentItems as $item) {
    //         $key = $item->trans_no
    //             . '|' . $item->trans_dt_str
    //             . '|' . $item->serial_no;

    //         $currentSet[$key] = true;
    //     }

    //     /*
    //      * Transactions already cleared in earlier reconciliations.
    //      * These must not appear as outstanding in this report.
    //      */
    //     $previousClearedItems = DB::table('fashbankrecon_items as i')
    //         ->join('fashbankrecon as h', 'h.recon_id', '=', 'i.recon_id')
    //         ->where('h.company_id', $recon->company_id)
    //         ->whereRaw('TRIM(h.bank_id) = ?', [trim((string) $recon->bank_id)])
    //         ->where('h.recon_id', '<>', $recon->recon_id)
    //         ->where(function ($query) use ($recon) {
    //             $query->where('h.period_year', '<', $recon->period_year)
    //                 ->orWhere(function ($query) use ($recon) {
    //                     $query->where('h.period_year', $recon->period_year)
    //                         ->where('h.period_month', '<', $recon->period_month);
    //                 });
    //         })
    //         ->select([
    //             'i.trans_no',
    //             DB::raw("DATE_FORMAT(i.trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt_str"),
    //             'i.serial_no',
    //         ])
    //         ->get();

    //     $previousClearedSet = [];

    //     foreach ($previousClearedItems as $item) {
    //         $key = $item->trans_no
    //             . '|' . $item->trans_dt_str
    //             . '|' . $item->serial_no;

    //         $previousClearedSet[$key] = true;
    //     }

    //     /*
    //      * Earliest reconciliation date is the conversion cutoff.
    //      */
    //     $firstRecon = DB::table('fashbankrecon')
    //         ->where('company_id', $recon->company_id)
    //         ->whereRaw('TRIM(bank_id) = ?', [trim((string) $recon->bank_id)])
    //         ->orderBy('period_year')
    //         ->orderBy('period_month')
    //         ->first();

    //     $conversionStartDt = $firstRecon && $firstRecon->opening_dt
    //         ? \Carbon\Carbon::parse($firstRecon->opening_dt)->startOfDay()
    //         : \Carbon\Carbon::create(
    //             (int) $recon->period_year,
    //             (int) $recon->period_month,
    //             1
    //         )->startOfDay();

    //     /*
    //      * Load all eligible transactions for this reconciliation period.
    //      */
    //     $transactions = DB::table('fashtrans')
    //         ->where('company_id', $recon->company_id)
    //         ->whereRaw('TRIM(bank_id) = ?', [trim((string) $recon->bank_id)])
    //         ->whereIn('trans_type', ['PAYM', 'DEPS'])
    //         ->whereBetween('trans_dt', [
    //             $conversionStartDt,
    //             $recon->closing_dt,
    //         ])
    //         ->orderBy('trans_dt')
    //         ->orderBy('trans_no')
    //         ->orderBy('serial_no')
    //         ->select([
    //             'trans_no',
    //             DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt"),
    //             'serial_no',
    //             'trans_type',
    //             'payto_name',
    //             'remarks',
    //             'ref_no',
    //             'check_no',
    //             'debit_amt',
    //             'credit_amt',
    //         ])
    //         ->get();

    //     $clearedDeposits   = collect();
    //     $unclearedDeposits = collect();
    //     $clearedPayments   = collect();
    //     $unclearedPayments = collect();

    //     foreach ($transactions as $transaction) {
    //         $key = $transaction->trans_no
    //             . '|' . $transaction->trans_dt
    //             . '|' . $transaction->serial_no;

    //         $isClearedCurrent = isset($currentSet[$key]);

    //         /*
    //          * Do not show transactions cleared in a prior month.
    //          */
    //         if (isset($previousClearedSet[$key]) && !$isClearedCurrent) {
    //             continue;
    //         }

    //         if ($transaction->trans_type === 'DEPS') {
    //             if ($isClearedCurrent) {
    //                 $clearedDeposits->push($transaction);
    //             } else {
    //                 $unclearedDeposits->push($transaction);
    //             }
    //         }

    //         if ($transaction->trans_type === 'PAYM') {
    //             if ($isClearedCurrent) {
    //                 $clearedPayments->push($transaction);
    //             } else {
    //                 $unclearedPayments->push($transaction);
    //             }
    //         }
    //     }

    //     $totals = [
    //         'cleared_deposits' => $clearedDeposits->sum(
    //             fn ($row) => (float) $row->credit_amt
    //         ),
    //         'uncleared_deposits' => $unclearedDeposits->sum(
    //             fn ($row) => (float) $row->credit_amt
    //         ),
    //         'cleared_payments' => $clearedPayments->sum(
    //             fn ($row) => (float) $row->debit_amt
    //         ),
    //         'uncleared_payments' => $unclearedPayments->sum(
    //             fn ($row) => (float) $row->debit_amt
    //         ),
    //     ];

    //     $totals['cleared_balance'] =
    //         (float) $recon->opening_balance
    //         + $totals['cleared_deposits']
    //         - $totals['cleared_payments'];

    //     $totals['difference'] =
    //         (float) $recon->closing_balance
    //         - $totals['cleared_balance'];

    //     return view('accounts.accounts_bankrecon_report', compact(
    //         'recon',
    //         'bank',
    //         'clearedDeposits',
    //         'unclearedDeposits',
    //         'clearedPayments',
    //         'unclearedPayments',
    //         'totals'
    //     ));
    // }

    /**
     * Export Bank Reconciliation to PDF using mPDF.
     *
     * The report contains:
     * - Cleared deposits
     * - Uncleared deposits
     * - Cleared payments
     * - Uncleared payments
     * - Totals and reconciliation summary
     */
    public function BankreconReport($recon_id)
    {
        $companyId = config('company.company_id', 'DS02');

        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('recon_id', $recon_id)
            ->first();

        abort_if(!$recon, 404);

        $bank = DB::table('fashmsbank')
            ->where('company_id', $companyId)
            ->whereRaw('TRIM(account_id) = ?', [trim((string) $recon->bank_id)])
            ->first();

        /*
         * Current reconciliation items.
         */
        $currentItems = DB::table('fashbankrecon_items')
            ->where('recon_id', $recon->recon_id)
            ->select([
                'trans_no',
                DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt_str"),
                'serial_no',
            ])
            ->get();

        $currentSet = [];

        foreach ($currentItems as $item) {
            $key = $item->trans_no
                . '|' . $item->trans_dt_str
                . '|' . $item->serial_no;

            $currentSet[$key] = true;
        }

        /*
         * Transactions cleared in prior reconciliations.
         */
        $previousClearedItems = DB::table('fashbankrecon_items as i')
            ->join('fashbankrecon as h', 'h.recon_id', '=', 'i.recon_id')
            ->where('h.company_id', $recon->company_id)
            ->whereRaw('TRIM(h.bank_id) = ?', [trim((string) $recon->bank_id)])
            ->where('h.recon_id', '<>', $recon->recon_id)
            ->where(function ($query) use ($recon) {
                $query->where('h.period_year', '<', $recon->period_year)
                    ->orWhere(function ($query) use ($recon) {
                        $query->where('h.period_year', $recon->period_year)
                            ->where('h.period_month', '<', $recon->period_month);
                    });
            })
            ->select([
                'i.trans_no',
                DB::raw("DATE_FORMAT(i.trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt_str"),
                'i.serial_no',
            ])
            ->get();

        $previousClearedSet = [];

        foreach ($previousClearedItems as $item) {
            $key = $item->trans_no
                . '|' . $item->trans_dt_str
                . '|' . $item->serial_no;

            $previousClearedSet[$key] = true;
        }

        /*
         * Earliest reconciliation date is the conversion cutoff.
         */
        $firstRecon = DB::table('fashbankrecon')
            ->where('company_id', $recon->company_id)
            ->whereRaw('TRIM(bank_id) = ?', [trim((string) $recon->bank_id)])
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->first();

        $conversionStartDt = $firstRecon && $firstRecon->opening_dt
            ? \Carbon\Carbon::parse($firstRecon->opening_dt)->startOfDay()
            : \Carbon\Carbon::create(
                (int) $recon->period_year,
                (int) $recon->period_month,
                1
            )->startOfDay();

        /*
         * Load all eligible transactions through the statement ending date.
         */
        $transactions = DB::table('fashtrans')
            ->where('company_id', $recon->company_id)
            ->whereRaw('TRIM(bank_id) = ?', [trim((string) $recon->bank_id)])
            ->whereIn('trans_type', ['PAYM', 'DEPS'])
            ->whereBetween('trans_dt', [
                $conversionStartDt,
                $recon->closing_dt,
            ])
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->select([
                'trans_no',
                DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt"),
                'serial_no',
                'trans_type',
                'payto_name',
                'remarks',
                'ref_no',
                'check_no',
                'debit_amt',
                'credit_amt',
            ])
            ->get();

        $clearedDeposits   = collect();
        $unclearedDeposits = collect();
        $clearedPayments   = collect();
        $unclearedPayments = collect();

        foreach ($transactions as $transaction) {
            $key = $transaction->trans_no
                . '|' . $transaction->trans_dt
                . '|' . $transaction->serial_no;

            $isClearedCurrent = isset($currentSet[$key]);

            /*
             * Exclude transactions cleared in a previous month.
             */
            if (isset($previousClearedSet[$key]) && !$isClearedCurrent) {
                continue;
            }

            if ($transaction->trans_type === 'DEPS') {
                if ($isClearedCurrent) {
                    $clearedDeposits->push($transaction);
                } else {
                    $unclearedDeposits->push($transaction);
                }
            }

            if ($transaction->trans_type === 'PAYM') {
                if ($isClearedCurrent) {
                    $clearedPayments->push($transaction);
                } else {
                    $unclearedPayments->push($transaction);
                }
            }
        }

        $totals = [
            'cleared_deposits' => $clearedDeposits->sum(
                fn($row) => (float) $row->credit_amt
            ),

            'uncleared_deposits' => $unclearedDeposits->sum(
                fn($row) => (float) $row->credit_amt
            ),

            'cleared_payments' => $clearedPayments->sum(
                fn($row) => (float) $row->debit_amt
            ),

            'uncleared_payments' => $unclearedPayments->sum(
                fn($row) => (float) $row->debit_amt
            ),
        ];

        $totals['cleared_balance'] =
            (float) $recon->opening_balance
            + $totals['cleared_deposits']
            - $totals['cleared_payments'];

        $totals['difference'] =
            (float) $recon->closing_balance
            - $totals['cleared_balance'];

        /*
         * Render the Blade into HTML.
         */
        /*
         * Render the report Blade.
         */
        $html = view('accounts.accounts_bankrecon_report', compact(
            'recon',
            'bank',
            'clearedDeposits',
            'unclearedDeposits',
            'clearedPayments',
            'unclearedPayments',
            'totals'
        ))->render();

        $tempDir = storage_path('app/mpdf-temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        /*
         * simpleTables is important here. It avoids mPDF's complex table
         * pagination behavior that can create hundreds of blank pages.
         */
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'orientation'   => 'L',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 10,
            'tempDir'       => $tempDir,
            'simpleTables'  => true,
        ]);

        $mpdf->SetTitle('Bank Reconciliation');
        $mpdf->SetAuthor(config('app.name'));
        $mpdf->SetDisplayMode('fullpage');

        /*
         * Prevent oversized tables from forcing abnormal page creation.
         */
        $mpdf->shrink_tables_to_fit = 1;

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-size:7pt;color:#666;">'
                . '<tr>'
                . '<td width="50%">Printed: {DATE m/d/Y h:i A}</td>'
                . '<td width="50%" style="text-align:right;">Page {PAGENO} of {nbpg}</td>'
                . '</tr>'
                . '</table>'
        );

        $mpdf->WriteHTML($html);

        $fileName = 'Bank_Reconciliation_'
            . preg_replace('/[^A-Za-z0-9_-]/', '_', trim((string) $recon->bank_id))
            . '_'
            . $recon->period_year
            . '_'
            . str_pad((string) $recon->period_month, 2, '0', STR_PAD_LEFT)
            . '.pdf';

        return response(
            $mpdf->Output($fileName, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]
        );
    } // End Method

    /**
     * Build the reconciliation summary.
     */

    /**
     * Calculate reconciliation totals from current reconciliation items.
     *
     * IMPORTANT:
     * Matching uses:
     *
     *      trans_no + DATE(trans_dt) + serial_no
     *
     * instead of matching the full DATETIME.
     */
    private function bankreconSummaryFromDatabase($recon): array
    {
        /*
        |--------------------------------------------------------------------------
        | Load cleared reconciliation items
        |--------------------------------------------------------------------------
        */
        $items = DB::table('fashbankrecon_items')
            ->where(
                'recon_id',
                $recon->recon_id
            )
            ->where(
                'cleared_flag',
                'Y'
            )
            ->select([
                'trans_no',

                DB::raw(
                    "DATE_FORMAT(trans_dt, '%Y-%m-%d') AS trans_date"
                ),

                'serial_no',
            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | No cleared transactions
        |--------------------------------------------------------------------------
        */
        if ($items->isEmpty()) {
            return $this->bankreconBuildSummary(
                $recon,
                0.00,
                0.00
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Build reconciliation-item matching set
        |--------------------------------------------------------------------------
        */
        $itemSet = [];

        $transNos = [];

        foreach ($items as $item) {

            $key = (string) $item->trans_no
                . '|'
                . $item->trans_date
                . '|'
                . trim((string) $item->serial_no);

            $itemSet[$key] = true;

            $transNos[] = (int) $item->trans_no;
        }

        $transNos = array_values(
            array_unique($transNos)
        );


        /*
        |--------------------------------------------------------------------------
        | Load matching bank transactions
        |--------------------------------------------------------------------------
        |
        | Limit by trans_no first for performance, then perform the final
        | DATE-based matching in PHP.
        |
        */
        $transactions = DB::table('fashtrans')
            ->where(
                'company_id',
                $recon->company_id
            )
            ->whereRaw(
                'TRIM(bank_id) = ?',
                [trim((string) $recon->bank_id)]
            )
            ->whereIn(
                'trans_no',
                $transNos
            )
            ->whereIn(
                'trans_type',
                ['PAYM', 'DEPS']
            )
            ->whereDate(
                'trans_dt',
                '<=',
                \Carbon\Carbon::parse(
                    $recon->closing_dt
                )->format('Y-m-d')
            )
            ->select([
                'trans_no',
                'trans_dt',
                'serial_no',
                'trans_type',
                'debit_amt',
                'credit_amt',
            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Calculate totals
        |--------------------------------------------------------------------------
        */
        $clearedPaym = 0.00;
        $clearedDeps = 0.00;

        foreach ($transactions as $transaction) {

            $transDate = \Carbon\Carbon::parse(
                $transaction->trans_dt
            )->format('Y-m-d');

            $key = (string) $transaction->trans_no
                . '|'
                . $transDate
                . '|'
                . trim((string) $transaction->serial_no);


            /*
            |--------------------------------------------------------------------------
            | Skip if not part of this reconciliation
            |--------------------------------------------------------------------------
            */
            if (!isset($itemSet[$key])) {
                continue;
            }


            $transType = strtoupper(
                trim(
                    (string) $transaction->trans_type
                )
            );


            if ($transType === 'PAYM') {
                $clearedPaym +=
                    (float) (
                        $transaction->debit_amt ?? 0
                    );
            }


            if ($transType === 'DEPS') {
                $clearedDeps +=
                    (float) (
                        $transaction->credit_amt ?? 0
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Build summary
        |--------------------------------------------------------------------------
        */
        return $this->bankreconBuildSummary(
            $recon,
            $clearedPaym,
            $clearedDeps
        );
    } // End Method

    private function bankreconBuildSummary(
        $recon,
        float $clearedPaym,
        float $clearedDeps
    ): array {
        $clearedBalance = (float) $recon->opening_balance
            + $clearedDeps
            - $clearedPaym;

        $difference = (float) $recon->closing_balance - $clearedBalance;

        return [
            'cleared_deps'    => round($clearedDeps, 2),
            'cleared_paym'    => round($clearedPaym, 2),
            'cleared_balance' => round($clearedBalance, 2),
            'difference'      => round($difference, 2),
        ];
    }


    // If you already have mPDF pattern in your project, use same style (preview/download).
    public function BankreconReportPdf($recon_id)
    {
        // implement using your existing mPDF helper pattern
        // return PDF response
        abort(501);
    } // End Method

    public function bankDepositRegister(Request $request)
    {
        $banks = DB::table('fashmsbank')
            ->select('account_id', 'account_name')
            ->orderBy('account_name')
            ->get();

        $rows = collect();

        $bankId = $request->bank_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($request->filled('bank_id') && $request->filled('start_date') && $request->filled('end_date')) {
            $rows = $this->getBankDepositRegisterRows($bankId, $startDate, $endDate);
        }

        return view('accounts.bank_deposit_register', compact(
            'banks',
            'rows',
            'bankId',
            'startDate',
            'endDate'
        ));
    } // End Method

    private function getBankDepositRegisterRows($bankId, $startDate, $endDate)
    {
        $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
        $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

        return DB::table('fashtrans as t')
            ->leftJoin('accountmasters as a', 't.party_cd', '=', 'a.party_cd')
            ->where('t.bank_id', $bankId)
            ->whereDate('t.trans_dt', '>=', $start)
            ->whereDate('t.trans_dt', '<=', $end)
            ->where('t.trans_type', 'DEPS')
            ->where('t.trans_flag', 'A')
            ->where('t.serial_no', '000')
            ->orderBy('t.trans_dt', 'asc')
            ->orderBy('t.trans_no', 'asc')
            ->select([
                't.trans_no',
                't.trans_dt',
                't.party_cd',
                'a.party_nm',
                't.payto_name',
                't.check_no',
                't.check_dt',
                't.ref_no',
                't.remarks',
                't.credit_amt',
            ])
            ->get();
    } // End method

    public function bankDepositRegisterPdf(Request $request)
    {
        if (!$request->filled('bank_id') || !$request->filled('start_date') || !$request->filled('end_date')) {
            return redirect()->route('bank.deposit.register')
                ->with('error', 'Please select bank, start date and end date first.');
        }

        $bankId = $request->bank_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $bank = DB::table('fashmsbank')
            ->where('account_id', $bankId)
            ->first();

        $rows = $this->getBankDepositRegisterRows($bankId, $startDate, $endDate);

        $html = view('accounts.bank_deposit_register_pdf', compact(
            'bank',
            'rows',
            'bankId',
            'startDate',
            'endDate'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('bank_deposit_register.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="bank_deposit_register.pdf"');
    } // End Method

    private function getBankPaymentRegisterRows($bankId, $startDate, $endDate)
    {
        $start = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
        $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');

        return DB::table('fashtrans as t')
            ->leftJoin('accountmasters as a', 't.party_cd', '=', 'a.party_cd')
            ->where('t.bank_id', $bankId)
            ->whereDate('t.trans_dt', '>=', $start)
            ->whereDate('t.trans_dt', '<=', $end)
            ->where('t.trans_type', 'PAYM')
            ->where('t.trans_flag', 'A')
            ->where('t.serial_no', '000')
            ->orderBy('t.trans_dt', 'asc')
            ->orderBy('t.trans_no', 'asc')
            ->select([
                't.trans_no',
                't.trans_dt',
                't.party_cd',
                'a.party_nm',
                't.payto_name',
                't.check_no',
                't.check_dt',
                't.ref_no',
                't.remarks',
                't.debit_amt',
            ])
            ->get();
    } // End Method

    public function bankPaymentRegister(Request $request)
    {
        $banks = DB::table('fashmsbank')
            ->select('account_id', 'account_name')
            ->orderBy('account_name')
            ->get();

        $rows = collect();

        $bankId = $request->bank_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($request->filled('bank_id') && $request->filled('start_date') && $request->filled('end_date')) {
            $rows = $this->getBankPaymentRegisterRows($bankId, $startDate, $endDate);
        }

        return view('accounts.bank_payment_register', compact(
            'banks',
            'rows',
            'bankId',
            'startDate',
            'endDate'
        ));
    } // End Method

    public function bankPaymentRegisterPdf(Request $request)
    {
        if (!$request->filled('bank_id') || !$request->filled('start_date') || !$request->filled('end_date')) {
            return redirect()->route('bank.payment.register')
                ->with('error', 'Please select bank, start date and end date first.');
        }

        $bankId = $request->bank_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $bank = DB::table('fashmsbank')
            ->where('account_id', $bankId)
            ->first();

        $rows = $this->getBankPaymentRegisterRows($bankId, $startDate, $endDate);

        $html = view('accounts.bank_payment_register_pdf', compact(
            'bank',
            'rows',
            'bankId',
            'startDate',
            'endDate'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('bank_payment_register.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="bank_payment_register.pdf"');
    } // End Method


    //     public function AccountGeneralLedger(Request $request)
    //     {
    //         /*
    //         |--------------------------------------------------------------------------
    //         | 1. Account dropdown
    //         |--------------------------------------------------------------------------
    //         */
    //         $accounts = DB::table('accountmasters')
    //             ->select('party_cd', 'party_nm')
    //             ->whereNotNull('party_cd')
    //             ->orderBy('party_nm')
    //             ->get();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 2. Default criteria
    //         |--------------------------------------------------------------------------
    //         */
    //         $startDate = $request->input(
    //             'start_date',
    //             now()->startOfMonth()->format('Y-m-d')
    //         );

    //         $endDate = $request->input(
    //             'end_date',
    //             now()->endOfMonth()->format('Y-m-d')
    //         );

    //         $selectedPartyCd = $request->input('party_cd');

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 3. Default empty report
    //         |--------------------------------------------------------------------------
    //         */
    //         $report = collect();

    //         $selectedAccount = null;

    //         $totalDebit = 0;
    //         $totalCredit = 0;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 4. Generate report only when an account is selected
    //         |--------------------------------------------------------------------------
    //         */
    //         if (!empty($selectedPartyCd)) {

    //             $request->validate([
    //                 'party_cd' => [
    //                     'required',
    //                     'string',
    //                     'max:8',
    //                     'exists:accountmasters,party_cd',
    //                 ],
    //                 'start_date' => [
    //                     'required',
    //                     'date',
    //                 ],
    //                 'end_date' => [
    //                     'required',
    //                     'date',
    //                     'after_or_equal:start_date',
    //                 ],
    //             ]);

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Selected account
    //             |--------------------------------------------------------------------------
    //             */
    //             $selectedAccount = DB::table('accountmasters')
    //                 ->select('party_cd', 'party_nm')
    //                 ->where('party_cd', $selectedPartyCd)
    //                 ->first();

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Account names used for journal opposite-account descriptions
    //             |--------------------------------------------------------------------------
    //             */
    //             $accountNames = DB::table('accountmasters')
    //                 ->pluck('party_nm', 'party_cd')
    //                 ->toArray();

    //             $reportRows = [];

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 5. Journal transactions for selected account
    //             |--------------------------------------------------------------------------
    //             */
    //             $selectedJournalRows = DB::table('fagltrjournal')
    //                 ->where('account_id', $selectedPartyCd)
    //                 ->whereBetween('trans_dt', [$startDate, $endDate])
    //                 ->orderBy('trans_dt')
    //                 ->orderBy('trans_no')
    //                 ->get();

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Load all related journal rows
    //             |--------------------------------------------------------------------------
    //             | These rows are needed to identify the account on the opposite side
    //             | of each journal transaction.
    //             */
    //             $journalTransactionNumbers = $selectedJournalRows
    //                 ->pluck('trans_no')
    //                 ->filter()
    //                 ->unique()
    //                 ->values();

    //             $journalByTransNo = collect();

    //             if ($journalTransactionNumbers->isNotEmpty()) {

    //                 $relatedJournalRows = DB::table('fagltrjournal')
    //                     ->whereIn('trans_no', $journalTransactionNumbers)
    //                     ->get();

    //                 $journalByTransNo = $relatedJournalRows->groupBy('trans_no');
    //             }

    //             foreach ($selectedJournalRows as $row) {

    //                 $transactionNumber = $row->trans_no;
    //                 $transactionType = $row->trans_type;

    //                 $debitAmount = (float) ($row->debit_amt ?? 0);
    //                 $creditAmount = (float) ($row->credit_amt ?? 0);

    //                 $description = trim((string) ($row->description ?? ''));

    //                 $payToName = '';
    //                 $remarks = '';
    //                 $referenceNumber = $row->ref_no ?? '';

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Beginning balance
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 if ($transactionType === 'BBAL') {

    //                     $payToName = $debitAmount > 0
    //                         ? 'TO BALANCE B/F'
    //                         : 'BY BALANCE B/F';

    //                 } else {

    //                     $otherRows = $journalByTransNo->get(
    //                         $transactionNumber,
    //                         collect()
    //                     );

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Selected account is credited
    //                     |--------------------------------------------------------------------------
    //                     | Locate the debit account.
    //                     */
    //                     if ($creditAmount > 0) {

    //                         $oppositeRow = $otherRows->first(function ($item) use (
    //                             $selectedPartyCd
    //                         ) {
    //                             return (float) ($item->debit_amt ?? 0) > 0
    //                                 && (string) $item->account_id
    //                                     !== (string) $selectedPartyCd;
    //                         });

    //                         $oppositePartyCd = $oppositeRow->account_id ?? null;

    //                         $payToName = 'TRNS TO';

    //                         if ($description !== '') {
    //                             $remarks = $description;
    //                         } elseif (!empty($oppositePartyCd)) {
    //                             $remarks = $accountNames[$oppositePartyCd] ?? '';
    //                         }

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Selected account is debited
    //                     |--------------------------------------------------------------------------
    //                     | Locate the credit account.
    //                     */
    //                     } else {

    //                         $oppositeRow = $otherRows->first(function ($item) use (
    //                             $selectedPartyCd
    //                         ) {
    //                             return (float) ($item->credit_amt ?? 0) > 0
    //                                 && (string) $item->account_id
    //                                     !== (string) $selectedPartyCd;
    //                         });

    //                         $oppositePartyCd = $oppositeRow->account_id ?? null;

    //                         $payToName = 'TRNS FROM JOURNAL';

    //                         if ($description !== '') {
    //                             $remarks = $description;
    //                         } elseif (!empty($oppositePartyCd)) {
    //                             $remarks = $accountNames[$oppositePartyCd] ?? '';
    //                         }
    //                     }
    //                 }

    //                 $reportRows[] = (object) [
    //                     'trans_no'   => $transactionNumber,
    //                     'trans_dt'   => $row->trans_dt,
    //                     'trans_type' => $transactionType,
    //                     'party_cd'   => $selectedPartyCd,
    //                     'payto_name' => $payToName,
    //                     'remarks'    => $remarks,
    //                     'ref_no'     => $referenceNumber,
    //                     'debit_amt'  => $debitAmount,
    //                     'credit_amt' => $creditAmount,
    //                     'source'     => 'JOURNAL',
    //                 ];
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 6. Bank transactions for selected account
    //             |--------------------------------------------------------------------------
    //             */
    //             $bankRows = DB::table('fashtrans')
    //                 ->where('party_cd', $selectedPartyCd)
    //                 ->whereBetween('trans_dt', [$startDate, $endDate])
    //                 ->orderBy('trans_dt')
    //                 ->orderBy('trans_no')
    //                 ->orderBy('serial_no')
    //                 ->get();

    //             foreach ($bankRows as $row) {

    //                 $partyCd = $row->party_cd;

    //                 $originalPayToName = trim(
    //                     (string) ($row->payto_name ?? '')
    //                 );

    //                 $debitAmount = (float) ($row->debit_amt ?? 0);
    //                 $creditAmount = (float) ($row->credit_amt ?? 0);

    //                 $remarks = $originalPayToName;

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Preserve existing bank transaction description logic
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 if ($debitAmount > 0) {

    //                     if ($partyCd === '1101' || $partyCd === '103') {
    //                         $payToName = 'TO CHECK';
    //                     } else {
    //                         $payToName = 'TO CHECK FROM TD BANK';
    //                     }

    //                 } else {

    //                     if ($partyCd === '1101' || $partyCd === '103') {
    //                         $payToName = 'BY CHECK';
    //                     } else {
    //                         $payToName = 'BY CHECK TO TD BANK';
    //                     }
    //                 }

    //                 $reportRows[] = (object) [
    //                     'trans_no'   => $row->trans_no ?? '',
    //                     'trans_dt'   => $row->trans_dt,
    //                     'trans_type' => $row->trans_type,
    //                     'party_cd'   => $partyCd,
    //                     'payto_name' => $payToName,
    //                     'remarks'    => $remarks,
    //                     'ref_no'     => $row->ref_no ?? '',
    //                     'debit_amt'  => $debitAmount,
    //                     'credit_amt' => $creditAmount,
    //                     'source'     => 'BANK',
    //                 ];
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 7. Sort combined journal and bank transactions
    //             |--------------------------------------------------------------------------
    //             */
    //             $report = collect($reportRows)
    //                 ->sortBy(function ($row) {
    //                     return sprintf(
    //                         '%s-%012s-%s',
    //                         $row->trans_dt,
    //                         $row->trans_no ?? '',
    //                         $row->source ?? ''
    //                     );
    //                 })
    //                 ->values();

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 8. Totals
    //             |--------------------------------------------------------------------------
    //             */
    //             $totalDebit = $report->sum('debit_amt');
    //             $totalCredit = $report->sum('credit_amt');
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 9. New report blade
    //         |--------------------------------------------------------------------------
    //         */
    //         return view('accounts.accounts_general_ledger_partywise', [
    //             'accounts'            => $accounts,
    //             'report'              => $report,
    //             'selectedAccount'     => $selectedAccount,
    //             'selectedPartyCd'     => $selectedPartyCd,
    //             'startDate'           => $startDate,
    //             'endDate'             => $endDate,
    //             'totalDebit'          => $totalDebit,
    //             'totalCredit'         => $totalCredit,
    //         ]);

    //     } // End Method


    //     public function AccountGeneralLedgerPdf(Request $request)
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | 1. Validate report criteria
    //     |--------------------------------------------------------------------------
    //     */
    //     $request->validate([
    //         'party_cd' => [
    //             'required',
    //             'string',
    //             'max:8',
    //             'exists:accountmasters,party_cd',
    //         ],
    //         'start_date' => [
    //             'required',
    //             'date',
    //         ],
    //         'end_date' => [
    //             'required',
    //             'date',
    //             'after_or_equal:start_date',
    //         ],
    //     ]);

    //     $selectedPartyCd = $request->input('party_cd');
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 2. Selected account information
    //     |--------------------------------------------------------------------------
    //     */
    //     $selectedAccount = DB::table('accountmasters')
    //         ->select('party_cd', 'party_nm')
    //         ->where('party_cd', $selectedPartyCd)
    //         ->first();

    //     if (!$selectedAccount) {
    //         return redirect()
    //             ->back()
    //             ->with('error', 'The selected account could not be found.');
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 3. Account names for opposite journal accounts
    //     |--------------------------------------------------------------------------
    //     */
    //     $accountNames = DB::table('accountmasters')
    //         ->pluck('party_nm', 'party_cd')
    //         ->toArray();

    //     $reportRows = [];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 4. Journal transactions for selected account
    //     |--------------------------------------------------------------------------
    //     */
    //     $selectedJournalRows = DB::table('fagltrjournal')
    //         ->where('account_id', $selectedPartyCd)
    //         ->whereBetween('trans_dt', [$startDate, $endDate])
    //         ->orderBy('trans_dt')
    //         ->orderBy('trans_no')
    //         ->get();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Load all related journal rows
    //     |--------------------------------------------------------------------------
    //     */
    //     $journalTransactionNumbers = $selectedJournalRows
    //         ->pluck('trans_no')
    //         ->filter()
    //         ->unique()
    //         ->values();

    //     $journalByTransNo = collect();

    //     if ($journalTransactionNumbers->isNotEmpty()) {
    //         $relatedJournalRows = DB::table('fagltrjournal')
    //             ->whereIn('trans_no', $journalTransactionNumbers)
    //             ->get();

    //         $journalByTransNo = $relatedJournalRows->groupBy('trans_no');
    //     }

    //     foreach ($selectedJournalRows as $row) {
    //         $transactionNumber = $row->trans_no;
    //         $transactionType = $row->trans_type;

    //         $debitAmount = (float) ($row->debit_amt ?? 0);
    //         $creditAmount = (float) ($row->credit_amt ?? 0);

    //         $description = trim((string) ($row->description ?? ''));

    //         $payToName = '';
    //         $remarks = '';
    //         $referenceNumber = $row->ref_no ?? '';

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Beginning balance
    //         |--------------------------------------------------------------------------
    //         */
    //         if ($transactionType === 'BBAL') {
    //             $payToName = $debitAmount > 0
    //                 ? 'TO BALANCE B/F'
    //                 : 'BY BALANCE B/F';
    //         } else {
    //             $otherRows = $journalByTransNo->get(
    //                 $transactionNumber,
    //                 collect()
    //             );

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Selected account is credited
    //             |--------------------------------------------------------------------------
    //             */
    //             if ($creditAmount > 0) {
    //                 $oppositeRow = $otherRows->first(
    //                     function ($item) use ($selectedPartyCd) {
    //                         return (float) ($item->debit_amt ?? 0) > 0
    //                             && (string) $item->account_id
    //                                 !== (string) $selectedPartyCd;
    //                     }
    //                 );

    //                 $oppositePartyCd = $oppositeRow->account_id ?? null;

    //                 $payToName = 'TRNS TO';

    //                 if ($description !== '') {
    //                     $remarks = $description;
    //                 } elseif (!empty($oppositePartyCd)) {
    //                     $remarks = $accountNames[$oppositePartyCd] ?? '';
    //                 }
    //             } else {
    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Selected account is debited
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 $oppositeRow = $otherRows->first(
    //                     function ($item) use ($selectedPartyCd) {
    //                         return (float) ($item->credit_amt ?? 0) > 0
    //                             && (string) $item->account_id
    //                                 !== (string) $selectedPartyCd;
    //                     }
    //                 );

    //                 $oppositePartyCd = $oppositeRow->account_id ?? null;

    //                 $payToName = 'TRNS FROM JOURNAL';

    //                 if ($description !== '') {
    //                     $remarks = $description;
    //                 } elseif (!empty($oppositePartyCd)) {
    //                     $remarks = $accountNames[$oppositePartyCd] ?? '';
    //                 }
    //             }
    //         }

    //         $reportRows[] = (object) [
    //             'trans_no'   => $transactionNumber,
    //             'trans_dt'   => $row->trans_dt,
    //             'trans_type' => $transactionType,
    //             'party_cd'   => $selectedPartyCd,
    //             'payto_name' => $payToName,
    //             'remarks'    => $remarks,
    //             'ref_no'     => $referenceNumber,
    //             'debit_amt'  => $debitAmount,
    //             'credit_amt' => $creditAmount,
    //             'source'     => 'JOURNAL',
    //         ];
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 5. Bank transactions for selected account
    //     |--------------------------------------------------------------------------
    //     */
    //     $bankRows = DB::table('fashtrans')
    //         ->where('party_cd', $selectedPartyCd)
    //         ->whereBetween('trans_dt', [$startDate, $endDate])
    //         ->orderBy('trans_dt')
    //         ->orderBy('trans_no')
    //         ->orderBy('serial_no')
    //         ->get();

    //     foreach ($bankRows as $row) {
    //         $partyCd = $row->party_cd;

    //         $originalPayToName = trim(
    //             (string) ($row->payto_name ?? '')
    //         );

    //         $debitAmount = (float) ($row->debit_amt ?? 0);
    //         $creditAmount = (float) ($row->credit_amt ?? 0);

    //         $remarks = $originalPayToName;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Preserve existing bank description logic
    //         |--------------------------------------------------------------------------
    //         */
    //         if ($debitAmount > 0) {
    //             if ($partyCd === '1101' || $partyCd === '103') {
    //                 $payToName = 'TO CHECK';
    //             } else {
    //                 $payToName = 'TO CHECK FROM TD BANK';
    //             }
    //         } else {
    //             if ($partyCd === '1101' || $partyCd === '103') {
    //                 $payToName = 'BY CHECK';
    //             } else {
    //                 $payToName = 'BY CHECK TO TD BANK';
    //             }
    //         }

    //         $reportRows[] = (object) [
    //             'trans_no'   => $row->trans_no ?? '',
    //             'trans_dt'   => $row->trans_dt,
    //             'trans_type' => $row->trans_type,
    //             'party_cd'   => $partyCd,
    //             'payto_name' => $payToName,
    //             'remarks'    => $remarks,
    //             'ref_no'     => $row->ref_no ?? '',
    //             'debit_amt'  => $debitAmount,
    //             'credit_amt' => $creditAmount,
    //             'source'     => 'BANK',
    //         ];
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 6. Sort combined transactions
    //     |--------------------------------------------------------------------------
    //     */
    //     $report = collect($reportRows)
    //         ->sortBy(function ($row) {
    //             return sprintf(
    //                 '%s-%012s-%s',
    //                 $row->trans_dt,
    //                 $row->trans_no ?? '',
    //                 $row->source ?? ''
    //             );
    //         })
    //         ->values();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 7. Totals
    //     |--------------------------------------------------------------------------
    //     */
    //     $totalDebit = (float) $report->sum('debit_amt');
    //     $totalCredit = (float) $report->sum('credit_amt');

    //     $balance = $totalDebit - $totalCredit;
    //     $grandTotal = max($totalDebit, $totalCredit);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 8. Company information
    //     |--------------------------------------------------------------------------
    //     | Change the table name below only if your company table has a different
    //     | name. Remove this query if $company is already shared globally.
    //     */
    //     $company = DB::table('gnmscomp')->first();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 9. Render PDF blade
    //     |--------------------------------------------------------------------------
    //     */
    //     $html = view('accounts.accounts_general_ledger_partywise_pdf', [
    //         'report'          => $report,
    //         'selectedAccount' => $selectedAccount,
    //         'selectedPartyCd' => $selectedPartyCd,
    //         'startDate'       => $startDate,
    //         'endDate'         => $endDate,
    //         'totalDebit'      => $totalDebit,
    //         'totalCredit'     => $totalCredit,
    //         'balance'         => $balance,
    //         'grandTotal'      => $grandTotal,
    //         'company'         => $company,
    //     ])->render();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 10. Create PDF using mPDF
    //     |--------------------------------------------------------------------------
    //     */
    //     $mpdf = new \Mpdf\Mpdf([
    //         'tempDir'=>storage_path('app/mpdf'),
    //         'mode' => 'utf-8',
    //         'format' => 'A4',
    //         'orientation' => 'P',
    //         'margin_left' => 10,
    //         'margin_right' => 10,
    //         'margin_top' => 22,
    //         'margin_bottom' => 18,
    //     ]);


    //     $mpdf->SetHTMLHeader('
    //         <div style="font-size:10pt;text-align:left;font-weight:bold;">
    //             <b>' . ($company->comp_nm ?? 'Company Name') . '</b><br>
    //             Ledger for the period : ' . e(\Carbon\Carbon::parse($startDate)->format('m/d/Y')) . ' To ' . e(\Carbon\Carbon::parse($endDate)->format('m/d/Y')) . '
    //         </div>
    //         <hr style="margin:2px 0 0 0;">
    //         ');

    //     $mpdf->SetTitle(
    //         'General Ledger - ' . $selectedAccount->party_nm
    //     );

    //     $mpdf->WriteHTML($html);

    //     $safeAccountCode = preg_replace(
    //         '/[^A-Za-z0-9_-]/',
    //         '_',
    //         $selectedPartyCd
    //     );

    //     $fileName = 'general-ledger-'
    //         . $safeAccountCode
    //         . '-'
    //         . $startDate
    //         . '-to-'
    //         . $endDate
    //         . '.pdf';

    //     return response(
    //         $mpdf->Output($fileName, 'S'),
    //         200,
    //         [
    //             'Content-Type' => 'application/pdf',
    //             'Content-Disposition' => 'inline; filename="' . $fileName . '"',
    //         ]
    //     );
    // }

    public function AccountGeneralLedger(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | 1. Account dropdown
    |--------------------------------------------------------------------------
    */
        $accounts = DB::table('accountmasters')
            ->select('party_cd', 'party_nm')
            ->whereNotNull('party_cd')
            ->orderBy('party_nm')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | 2. Default criteria
    |--------------------------------------------------------------------------
    */
        $startDate = $request->input(
            'start_date',
            now()->startOfMonth()->format('Y-m-d')
        );

        $endDate = $request->input(
            'end_date',
            now()->endOfMonth()->format('Y-m-d')
        );

        $selectedPartyCd = $request->input('party_cd');

        /*
    |--------------------------------------------------------------------------
    | 3. Default empty report
    |--------------------------------------------------------------------------
    */
        $report = collect();
        $selectedAccount = null;

        $totalDebit = 0;
        $totalCredit = 0;

        /*
    |--------------------------------------------------------------------------
    | 4. Generate report only when an account is selected
    |--------------------------------------------------------------------------
    */
        if (!empty($selectedPartyCd)) {

            $request->validate([
                'party_cd' => [
                    'required',
                    'string',
                    'max:8',
                    'exists:accountmasters,party_cd',
                ],
                'start_date' => [
                    'required',
                    'date',
                ],
                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                ],
            ]);

            /*
        |--------------------------------------------------------------------------
        | Selected account
        |--------------------------------------------------------------------------
        */
            $selectedAccount = DB::table('accountmasters')
                ->select('party_cd', 'party_nm')
                ->where('party_cd', $selectedPartyCd)
                ->first();

            /*
        |--------------------------------------------------------------------------
        | Account names for opposite journal descriptions
        |--------------------------------------------------------------------------
        */
            $accountNames = DB::table('accountmasters')
                ->pluck('party_nm', 'party_cd')
                ->toArray();

            $reportRows = [];

            /*
        |--------------------------------------------------------------------------
        | 5. Calculate opening balance
        |--------------------------------------------------------------------------
        | The January 1 BBAL entry represents the prior year's balance.
        |
        | When the selected report starts after January 1, calculate:
        |
        | January 1 BBAL
        | + journal activity before the report start date
        | + bank activity before the report start date
        |--------------------------------------------------------------------------
        */
            $reportStartDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $yearStartDate = $reportStartDate->copy()->startOfYear();
            $openingBalanceEndDate = $reportStartDate->copy()->subDay();

            if ($reportStartDate->gt($yearStartDate)) {

                /*
            |--------------------------------------------------------------------------
            | Journal balance before selected start date
            |--------------------------------------------------------------------------
            */
                $journalOpening = DB::table('fagltrjournal')
                    ->where('account_id', $selectedPartyCd)
                    ->whereBetween('trans_dt', [
                        $yearStartDate->format('Y-m-d'),
                        $openingBalanceEndDate->format('Y-m-d'),
                    ])
                    ->selectRaw('
                    COALESCE(SUM(debit_amt), 0) AS total_debit,
                    COALESCE(SUM(credit_amt), 0) AS total_credit
                ')
                    ->first();

                /*
            |--------------------------------------------------------------------------
            | Bank balance before selected start date
            |--------------------------------------------------------------------------
            */
                $bankOpening = DB::table('fashtrans')
                    ->where('party_cd', $selectedPartyCd)
                    ->whereBetween('trans_dt', [
                        $yearStartDate->format('Y-m-d'),
                        $openingBalanceEndDate->format('Y-m-d'),
                    ])
                    ->selectRaw('
                    COALESCE(SUM(debit_amt), 0) AS total_debit,
                    COALESCE(SUM(credit_amt), 0) AS total_credit
                ')
                    ->first();

                $openingDebit =
                    (float) ($journalOpening->total_debit ?? 0)
                    + (float) ($bankOpening->total_debit ?? 0);

                $openingCredit =
                    (float) ($journalOpening->total_credit ?? 0)
                    + (float) ($bankOpening->total_credit ?? 0);

                /*
            |--------------------------------------------------------------------------
            | Positive balance = debit
            | Negative balance = credit
            |--------------------------------------------------------------------------
            */
                $openingBalance = $openingDebit - $openingCredit;

                if (abs($openingBalance) > 0.00001) {

                    $reportRows[] = (object) [
                        'trans_no'   => '',
                        'trans_dt'   => $startDate,
                        'trans_type' => 'BBAL',
                        'party_cd'   => $selectedPartyCd,

                        'payto_name' => $openingBalance > 0
                            ? 'TO BALANCE B/F'
                            : 'BY BALANCE B/F',

                        'remarks' => 'Opening balance as of '
                            . $openingBalanceEndDate->format('m/d/Y'),

                        'ref_no' => '',

                        'debit_amt' => $openingBalance > 0
                            ? abs($openingBalance)
                            : 0,

                        'credit_amt' => $openingBalance < 0
                            ? abs($openingBalance)
                            : 0,

                        'source' => 'OPENING',
                    ];
                }
            }

            /*
        |--------------------------------------------------------------------------
        | 6. Journal transactions within selected date range
        |--------------------------------------------------------------------------
        */
            $selectedJournalRows = DB::table('fagltrjournal')
                ->where('account_id', $selectedPartyCd)
                ->whereBetween('trans_dt', [$startDate, $endDate])
                ->orderBy('trans_dt')
                ->orderBy('trans_no')
                ->get();

            /*
        |--------------------------------------------------------------------------
        | Load all related journal rows
        |--------------------------------------------------------------------------
        */
            $journalTransactionNumbers = $selectedJournalRows
                ->pluck('trans_no')
                ->filter()
                ->unique()
                ->values();

            $journalByTransNo = collect();

            if ($journalTransactionNumbers->isNotEmpty()) {

                $relatedJournalRows = DB::table('fagltrjournal')
                    ->whereIn('trans_no', $journalTransactionNumbers)
                    ->get();

                $journalByTransNo = $relatedJournalRows->groupBy('trans_no');
            }

            foreach ($selectedJournalRows as $row) {

                $transactionNumber = $row->trans_no;
                $transactionType = $row->trans_type;

                $debitAmount = (float) ($row->debit_amt ?? 0);
                $creditAmount = (float) ($row->credit_amt ?? 0);

                $description = trim(
                    (string) ($row->description ?? '')
                );

                $payToName = '';
                $remarks = '';
                $referenceNumber = $row->ref_no ?? '';

                /*
            |--------------------------------------------------------------------------
            | Actual January 1 opening balance row
            |--------------------------------------------------------------------------
            */
                if ($transactionType === 'BBAL') {

                    $payToName = $debitAmount > 0
                        ? 'TO BALANCE B/F'
                        : 'BY BALANCE B/F';
                } else {

                    $otherRows = $journalByTransNo->get(
                        $transactionNumber,
                        collect()
                    );

                    /*
                |--------------------------------------------------------------------------
                | Selected account is credited
                |--------------------------------------------------------------------------
                */
                    if ($creditAmount > 0) {

                        $oppositeRow = $otherRows->first(
                            function ($item) use ($selectedPartyCd) {
                                return (float) ($item->debit_amt ?? 0) > 0
                                    && (string) $item->account_id
                                    !== (string) $selectedPartyCd;
                            }
                        );

                        $oppositePartyCd =
                            $oppositeRow->account_id ?? null;

                        $payToName = 'TRNS TO';

                        if ($description !== '') {
                            $remarks = $description;
                        } elseif (!empty($oppositePartyCd)) {
                            $remarks =
                                $accountNames[$oppositePartyCd] ?? '';
                        }
                    } else {

                        /*
                    |--------------------------------------------------------------------------
                    | Selected account is debited
                    |--------------------------------------------------------------------------
                    */
                        $oppositeRow = $otherRows->first(
                            function ($item) use ($selectedPartyCd) {
                                return (float) ($item->credit_amt ?? 0) > 0
                                    && (string) $item->account_id
                                    !== (string) $selectedPartyCd;
                            }
                        );

                        $oppositePartyCd =
                            $oppositeRow->account_id ?? null;

                        $payToName = 'TRNS FROM JOURNAL';

                        if ($description !== '') {
                            $remarks = $description;
                        } elseif (!empty($oppositePartyCd)) {
                            $remarks =
                                $accountNames[$oppositePartyCd] ?? '';
                        }
                    }
                }

                $reportRows[] = (object) [
                    'trans_no'   => $transactionNumber,
                    'trans_dt'   => $row->trans_dt,
                    'trans_type' => $transactionType,
                    'party_cd'   => $selectedPartyCd,
                    'payto_name' => $payToName,
                    'remarks'    => $remarks,
                    'ref_no'     => $referenceNumber,
                    'debit_amt'  => $debitAmount,
                    'credit_amt' => $creditAmount,
                    'source'     => 'JOURNAL',
                ];
            }

            /*
        |--------------------------------------------------------------------------
        | 7. Bank transactions within selected date range
        |--------------------------------------------------------------------------
        */
            $bankRows = DB::table('fashtrans')
                ->where('party_cd', $selectedPartyCd)
                ->whereBetween('trans_dt', [$startDate, $endDate])
                ->orderBy('trans_dt')
                ->orderBy('trans_no')
                ->orderBy('serial_no')
                ->get();

            foreach ($bankRows as $row) {

                $partyCd = $row->party_cd;

                $originalPayToName = trim(
                    (string) ($row->payto_name ?? '')
                );

                $debitAmount = (float) ($row->debit_amt ?? 0);
                $creditAmount = (float) ($row->credit_amt ?? 0);

                $remarks = $originalPayToName;

                /*
            |--------------------------------------------------------------------------
            | Preserve existing bank description logic
            |--------------------------------------------------------------------------
            */
                if ($debitAmount > 0) {

                    if ($partyCd === '1101' || $partyCd === '103') {
                        $payToName = 'TO CHECK';
                    } else {
                        $payToName = 'TO CHECK FROM TD BANK';
                    }
                } else {

                    if ($partyCd === '1101' || $partyCd === '103') {
                        $payToName = 'BY CHECK';
                    } else {
                        $payToName = 'BY CHECK TO TD BANK';
                    }
                }

                $reportRows[] = (object) [
                    'trans_no'   => $row->trans_no ?? '',
                    'trans_dt'   => $row->trans_dt,
                    'trans_type' => $row->trans_type,
                    'party_cd'   => $partyCd,
                    'payto_name' => $payToName,
                    'remarks'    => $remarks,
                    'ref_no'     => $row->ref_no ?? '',
                    'debit_amt'  => $debitAmount,
                    'credit_amt' => $creditAmount,
                    'source'     => 'BANK',
                ];
            }

            /*
        |--------------------------------------------------------------------------
        | 8. Sort combined transactions
        |--------------------------------------------------------------------------
        | OPENING must appear before transactions entered on the selected
        | report start date.
        |--------------------------------------------------------------------------
        */
            $report = collect($reportRows)
                ->sortBy(function ($row) {

                    $sourceOrder = match ($row->source ?? '') {
                        'OPENING' => 0,
                        'JOURNAL' => 1,
                        'BANK'    => 2,
                        default   => 9,
                    };

                    return sprintf(
                        '%s-%d-%012s',
                        $row->trans_dt,
                        $sourceOrder,
                        $row->trans_no ?? ''
                    );
                })
                ->values();

            /*
        |--------------------------------------------------------------------------
        | 9. Totals
        |--------------------------------------------------------------------------
        */
            $totalDebit = (float) $report->sum('debit_amt');
            $totalCredit = (float) $report->sum('credit_amt');
        }

        /*
    |--------------------------------------------------------------------------
    | 10. Return report blade
    |--------------------------------------------------------------------------
    */
        return view('accounts.accounts_general_ledger_partywise', [
            'accounts'        => $accounts,
            'report'          => $report,
            'selectedAccount' => $selectedAccount,
            'selectedPartyCd' => $selectedPartyCd,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'totalDebit'      => $totalDebit,
            'totalCredit'     => $totalCredit,
        ]);
    } // End Method


    public function AccountGeneralLedgerPdf(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | 1. Validate report criteria
    |--------------------------------------------------------------------------
    */
        $request->validate([
            'party_cd' => [
                'required',
                'string',
                'max:8',
                'exists:accountmasters,party_cd',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        $selectedPartyCd = $request->input('party_cd');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        /*
    |--------------------------------------------------------------------------
    | 2. Selected account
    |--------------------------------------------------------------------------
    */
        $selectedAccount = DB::table('accountmasters')
            ->select('party_cd', 'party_nm')
            ->where('party_cd', $selectedPartyCd)
            ->first();

        if (!$selectedAccount) {
            return redirect()
                ->back()
                ->with('error', 'The selected account could not be found.');
        }

        /*
    |--------------------------------------------------------------------------
    | 3. Account names for opposite journal descriptions
    |--------------------------------------------------------------------------
    */
        $accountNames = DB::table('accountmasters')
            ->pluck('party_nm', 'party_cd')
            ->toArray();

        $reportRows = [];

        /*
    |--------------------------------------------------------------------------
    | 4. Calculate opening balance
    |--------------------------------------------------------------------------
    */
        $reportStartDate = \Carbon\Carbon::parse($startDate)->startOfDay();
        $yearStartDate = $reportStartDate->copy()->startOfYear();
        $openingBalanceEndDate = $reportStartDate->copy()->subDay();

        if ($reportStartDate->gt($yearStartDate)) {

            /*
        |--------------------------------------------------------------------------
        | Journal balance before selected start date
        |--------------------------------------------------------------------------
        */
            $journalOpening = DB::table('fagltrjournal')
                ->where('account_id', $selectedPartyCd)
                ->whereBetween('trans_dt', [
                    $yearStartDate->format('Y-m-d'),
                    $openingBalanceEndDate->format('Y-m-d'),
                ])
                ->selectRaw('
                COALESCE(SUM(debit_amt), 0) AS total_debit,
                COALESCE(SUM(credit_amt), 0) AS total_credit
            ')
                ->first();

            /*
        |--------------------------------------------------------------------------
        | Bank balance before selected start date
        |--------------------------------------------------------------------------
        */
            $bankOpening = DB::table('fashtrans')
                ->where('party_cd', $selectedPartyCd)
                ->whereBetween('trans_dt', [
                    $yearStartDate->format('Y-m-d'),
                    $openingBalanceEndDate->format('Y-m-d'),
                ])
                ->selectRaw('
                COALESCE(SUM(debit_amt), 0) AS total_debit,
                COALESCE(SUM(credit_amt), 0) AS total_credit
            ')
                ->first();

            $openingDebit =
                (float) ($journalOpening->total_debit ?? 0)
                + (float) ($bankOpening->total_debit ?? 0);

            $openingCredit =
                (float) ($journalOpening->total_credit ?? 0)
                + (float) ($bankOpening->total_credit ?? 0);

            $openingBalance = $openingDebit - $openingCredit;

            if (abs($openingBalance) > 0.00001) {

                $reportRows[] = (object) [
                    'trans_no'   => '',
                    'trans_dt'   => $startDate,
                    'trans_type' => 'BBAL',
                    'party_cd'   => $selectedPartyCd,

                    'payto_name' => $openingBalance > 0
                        ? 'TO BALANCE B/F'
                        : 'BY BALANCE B/F',

                    'remarks' => 'Opening balance as of '
                        . $openingBalanceEndDate->format('m/d/Y'),

                    'ref_no' => '',

                    'debit_amt' => $openingBalance > 0
                        ? abs($openingBalance)
                        : 0,

                    'credit_amt' => $openingBalance < 0
                        ? abs($openingBalance)
                        : 0,

                    'source' => 'OPENING',
                ];
            }
        }

        /*
    |--------------------------------------------------------------------------
    | 5. Journal transactions within selected date range
    |--------------------------------------------------------------------------
    */
        $selectedJournalRows = DB::table('fagltrjournal')
            ->where('account_id', $selectedPartyCd)
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Load related journal rows
    |--------------------------------------------------------------------------
    */
        $journalTransactionNumbers = $selectedJournalRows
            ->pluck('trans_no')
            ->filter()
            ->unique()
            ->values();

        $journalByTransNo = collect();

        if ($journalTransactionNumbers->isNotEmpty()) {

            $relatedJournalRows = DB::table('fagltrjournal')
                ->whereIn('trans_no', $journalTransactionNumbers)
                ->get();

            $journalByTransNo = $relatedJournalRows->groupBy('trans_no');
        }

        foreach ($selectedJournalRows as $row) {

            $transactionNumber = $row->trans_no;
            $transactionType = $row->trans_type;

            $debitAmount = (float) ($row->debit_amt ?? 0);
            $creditAmount = (float) ($row->credit_amt ?? 0);

            $description = trim(
                (string) ($row->description ?? '')
            );

            $payToName = '';
            $remarks = '';
            $referenceNumber = $row->ref_no ?? '';

            /*
        |--------------------------------------------------------------------------
        | Actual January 1 opening balance
        |--------------------------------------------------------------------------
        */
            if ($transactionType === 'BBAL') {

                $payToName = $debitAmount > 0
                    ? 'TO BALANCE B/F'
                    : 'BY BALANCE B/F';
            } else {

                $otherRows = $journalByTransNo->get(
                    $transactionNumber,
                    collect()
                );

                /*
            |--------------------------------------------------------------------------
            | Selected account is credited
            |--------------------------------------------------------------------------
            */
                if ($creditAmount > 0) {

                    $oppositeRow = $otherRows->first(
                        function ($item) use ($selectedPartyCd) {
                            return (float) ($item->debit_amt ?? 0) > 0
                                && (string) $item->account_id
                                !== (string) $selectedPartyCd;
                        }
                    );

                    $oppositePartyCd =
                        $oppositeRow->account_id ?? null;

                    $payToName = 'TRNS TO';

                    if ($description !== '') {
                        $remarks = $description;
                    } elseif (!empty($oppositePartyCd)) {
                        $remarks =
                            $accountNames[$oppositePartyCd] ?? '';
                    }
                } else {

                    /*
                |--------------------------------------------------------------------------
                | Selected account is debited
                |--------------------------------------------------------------------------
                */
                    $oppositeRow = $otherRows->first(
                        function ($item) use ($selectedPartyCd) {
                            return (float) ($item->credit_amt ?? 0) > 0
                                && (string) $item->account_id
                                !== (string) $selectedPartyCd;
                        }
                    );

                    $oppositePartyCd =
                        $oppositeRow->account_id ?? null;

                    $payToName = 'TRNS FROM JOURNAL';

                    if ($description !== '') {
                        $remarks = $description;
                    } elseif (!empty($oppositePartyCd)) {
                        $remarks =
                            $accountNames[$oppositePartyCd] ?? '';
                    }
                }
            }

            $reportRows[] = (object) [
                'trans_no'   => $transactionNumber,
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $transactionType,
                'party_cd'   => $selectedPartyCd,
                'payto_name' => $payToName,
                'remarks'    => $remarks,
                'ref_no'     => $referenceNumber,
                'debit_amt'  => $debitAmount,
                'credit_amt' => $creditAmount,
                'source'     => 'JOURNAL',
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | 6. Bank transactions within selected date range
    |--------------------------------------------------------------------------
    */
        $bankRows = DB::table('fashtrans')
            ->where('party_cd', $selectedPartyCd)
            ->whereBetween('trans_dt', [$startDate, $endDate])
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->get();

        foreach ($bankRows as $row) {

            $partyCd = $row->party_cd;

            $originalPayToName = trim(
                (string) ($row->payto_name ?? '')
            );

            $debitAmount = (float) ($row->debit_amt ?? 0);
            $creditAmount = (float) ($row->credit_amt ?? 0);

            $remarks = $originalPayToName;

            /*
        |--------------------------------------------------------------------------
        | Preserve existing bank description logic
        |--------------------------------------------------------------------------
        */
            if ($debitAmount > 0) {

                if ($partyCd === '1101' || $partyCd === '103') {
                    $payToName = 'TO CHECK';
                } else {
                    $payToName = 'TO CHECK FROM TD BANK';
                }
            } else {

                if ($partyCd === '1101' || $partyCd === '103') {
                    $payToName = 'BY CHECK';
                } else {
                    $payToName = 'BY CHECK TO TD BANK';
                }
            }

            $reportRows[] = (object) [
                'trans_no'   => $row->trans_no ?? '',
                'trans_dt'   => $row->trans_dt,
                'trans_type' => $row->trans_type,
                'party_cd'   => $partyCd,
                'payto_name' => $payToName,
                'remarks'    => $remarks,
                'ref_no'     => $row->ref_no ?? '',
                'debit_amt'  => $debitAmount,
                'credit_amt' => $creditAmount,
                'source'     => 'BANK',
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | 7. Sort combined transactions
    |--------------------------------------------------------------------------
    */
        $report = collect($reportRows)
            ->sortBy(function ($row) {

                $sourceOrder = match ($row->source ?? '') {
                    'OPENING' => 0,
                    'JOURNAL' => 1,
                    'BANK'    => 2,
                    default   => 9,
                };

                return sprintf(
                    '%s-%d-%012s',
                    $row->trans_dt,
                    $sourceOrder,
                    $row->trans_no ?? ''
                );
            })
            ->values();

        /*
    |--------------------------------------------------------------------------
    | 8. Totals
    |--------------------------------------------------------------------------
    */
        $totalDebit = (float) $report->sum('debit_amt');
        $totalCredit = (float) $report->sum('credit_amt');

        $balance = $totalDebit - $totalCredit;
        $grandTotal = max($totalDebit, $totalCredit);

        /*
    |--------------------------------------------------------------------------
    | 9. Company information
    |--------------------------------------------------------------------------
    */
        $company = DB::table('gnmscomp')->first();

        /*
    |--------------------------------------------------------------------------
    | 10. Render PDF blade
    |--------------------------------------------------------------------------
    */
        $html = view(
            'accounts.accounts_general_ledger_partywise_pdf',
            [
                'report'          => $report,
                'selectedAccount' => $selectedAccount,
                'selectedPartyCd' => $selectedPartyCd,
                'startDate'       => $startDate,
                'endDate'         => $endDate,
                'totalDebit'      => $totalDebit,
                'totalCredit'     => $totalCredit,
                'balance'         => $balance,
                'grandTotal'      => $grandTotal,
                'company'         => $company,
            ]
        )->render();

        /*
    |--------------------------------------------------------------------------
    | 11. Create PDF using mPDF
    |--------------------------------------------------------------------------
    */
        $mpdf = new \Mpdf\Mpdf([
            'tempDir'       => storage_path('app/mpdf'),
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'orientation'   => 'P',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 22,
            'margin_bottom' => 18,
        ]);

        $mpdf->SetHTMLHeader('
        <div style="font-size:10pt;text-align:left;font-weight:bold;">
            <b>'
            . e($company->comp_nm ?? 'Company Name')
            . '</b><br>
            Ledger for the period : '
            . e(
                \Carbon\Carbon::parse($startDate)
                    ->format('m/d/Y')
            )
            . ' To '
            . e(
                \Carbon\Carbon::parse($endDate)
                    ->format('m/d/Y')
            )
            . '
        </div>

        <hr style="margin:2px 0 0 0;">
    ');

        $mpdf->SetTitle(
            'General Ledger - ' . $selectedAccount->party_nm
        );

        $mpdf->WriteHTML($html);

        $safeAccountCode = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $selectedPartyCd
        );

        $fileName = 'general-ledger-'
            . $safeAccountCode
            . '-'
            . $startDate
            . '-to-'
            . $endDate
            . '.pdf';

        return response(
            $mpdf->Output($fileName, 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                'inline; filename="' . $fileName . '"',
            ]
        );
    }

    public function fiscalYearClosingPreview(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Select fiscal year
        |--------------------------------------------------------------------------
        */

        $fiscalYear = (int) $request->input(
            'fiscal_year',
            now()->subYear()->year
        );

        if ($fiscalYear < 2000 || $fiscalYear > 2100) {
            return back()->withErrors([
                'fiscal_year' => 'Please select a valid fiscal year.',
            ]);
        }

        $startDate = $fiscalYear . '-01-01';
        $closingDate = $fiscalYear . '-12-31';
        $openingDate = ($fiscalYear + 1) . '-01-01';

        /*
        |--------------------------------------------------------------------------
        | 2. Check whether this fiscal year is already closed
        |--------------------------------------------------------------------------
        */

        $existingClosing = DB::table('fiscal_year_closings')
            ->where('fiscal_year', $fiscalYear)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | 3. Combine bank transactions and journal transactions
        |--------------------------------------------------------------------------
        |
        | We calculate directly from the permanent transaction tables.
        | We do not use fagltrans_temp for the closing process.
        |
        */

        $makeBankQuery = function () use ($startDate, $closingDate) {
            return DB::table('fashtrans')
                ->selectRaw("
                        CASE
                            WHEN TRIM(party_cd) REGEXP '^[0-9]+$'
                                AND CHAR_LENGTH(TRIM(party_cd)) < 3
                            THEN LPAD(TRIM(party_cd), 3, '0')
                            ELSE TRIM(party_cd)
                        END AS account_id,
                        trans_dt,
                        trans_type,
                        debit_amt,
                        credit_amt
                    ")
                ->whereDate('trans_dt', '>=', $startDate)
                ->whereDate('trans_dt', '<=', $closingDate);
        };

        $makeJournalQuery = function () use ($startDate, $closingDate) {
            return DB::table('fagltrjournal')
                ->selectRaw("
                    CASE
                        WHEN TRIM(account_id) REGEXP '^[0-9]+$'
                            AND CHAR_LENGTH(TRIM(account_id)) < 3
                        THEN LPAD(TRIM(account_id), 3, '0')
                        ELSE TRIM(account_id)
                    END AS account_id,
                    trans_dt,
                    trans_type,
                    debit_amt,
                    credit_amt
                ")
                ->whereDate('trans_dt', '>=', $startDate)
                ->whereDate('trans_dt', '<=', $closingDate);
        };

        $allTransactions = $makeBankQuery()
            ->unionAll($makeJournalQuery());
        /*
        |--------------------------------------------------------------------------
        | 4. Calculate balances by account
        |--------------------------------------------------------------------------
        */

        $accountBalances = DB::query()
            ->fromSub($allTransactions, 't')
            ->join(
                'accountmasters as a',
                't.account_id',
                '=',
                'a.party_cd'
            )
            ->join(
                'faglmsacctcategory as c',
                'a.gl_id',
                '=',
                'c.id'
            )
            ->select(
                'a.party_cd',
                'a.party_nm',
                'a.gl_id',
                'c.name as category_name',
                'c.account_type'
            )
            ->selectRaw('SUM(COALESCE(t.debit_amt, 0)) AS total_debit')
            ->selectRaw('SUM(COALESCE(t.credit_amt, 0)) AS total_credit')
            ->groupBy(
                'a.party_cd',
                'a.party_nm',
                'a.gl_id',
                'c.name',
                'c.account_type'
            )
            ->orderBy('c.account_type')
            ->orderBy('a.gl_id')
            ->orderBy('a.party_cd')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 5. Calculate income and expenses
        |--------------------------------------------------------------------------
        |
        | BBAL is excluded from P&L, matching your existing P&L report.
        |
        */
        $profitLossTransactions = DB::query()
            ->fromSub(
                $makeBankQuery()->unionAll($makeJournalQuery()),
                't'
            )
            ->join(
                'accountmasters as a',
                't.account_id',
                '=',
                'a.party_cd'
            )
            ->join(
                'faglmsacctcategory as c',
                'a.gl_id',
                '=',
                'c.id'
            )
            ->whereIn('c.account_type', ['II', 'IE'])
            ->where(function ($query) {
                $query->whereNull('t.trans_type')
                    ->orWhere('t.trans_type', '<>', 'BBAL');
            })
            ->selectRaw('
                SUM(
                    CASE
                        WHEN c.account_type = "II"
                        THEN COALESCE(t.credit_amt, 0)
                        - COALESCE(t.debit_amt, 0)
                        ELSE 0
                    END
                ) AS total_income
            ')
            ->selectRaw('
                SUM(
                    CASE
                        WHEN c.account_type = "IE"
                        THEN COALESCE(t.debit_amt, 0)
                        - COALESCE(t.credit_amt, 0)
                        ELSE 0
                    END
                ) AS total_expense
            ')
            ->first();

        $totalIncome = (float) ($profitLossTransactions->total_income ?? 0);
        $totalExpense = (float) ($profitLossTransactions->total_expense ?? 0);
        $netProfitLoss = $totalIncome - $totalExpense;

        /*
        |--------------------------------------------------------------------------
        | 6. Prepare Balance Sheet carry-forward rows
        |--------------------------------------------------------------------------
        */

        $assetRows = [];
        $liabilityRows = [];

        foreach ($accountBalances as $account) {
            $totalDebit = (float) $account->total_debit;
            $totalCredit = (float) $account->total_credit;

            if ($account->account_type === 'BA') {
                $balance = $totalDebit - $totalCredit;

                if (abs($balance) >= 0.005) {
                    $assetRows[] = [
                        'gl_id' => $account->gl_id,
                        'category' => $account->category_name,
                        'account_id' => $account->party_cd,
                        'account_name' => $account->party_nm,
                        'balance' => $balance,
                        'opening_debit' => $balance > 0 ? $balance : 0,
                        'opening_credit' => $balance < 0 ? abs($balance) : 0,
                    ];
                }
            }

            if ($account->account_type === 'BL') {
                $balance = $totalCredit - $totalDebit;

                /*
                * Add the current-year profit or loss to retained earnings.
                */
                if ((string) $account->party_cd === '164') {
                    $balance += $netProfitLoss;
                }

                if (abs($balance) >= 0.005) {
                    $liabilityRows[] = [
                        'gl_id' => $account->gl_id,
                        'category' => $account->category_name,
                        'account_id' => $account->party_cd,
                        'account_name' => $account->party_nm,
                        'balance' => $balance,
                        'opening_debit' => $balance < 0 ? abs($balance) : 0,
                        'opening_credit' => $balance > 0 ? $balance : 0,
                    ];
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Make sure retained earnings exists in the preview
        |--------------------------------------------------------------------------
        |
        | If account 164 had no previous activity, it may not appear in the
        | grouped account balances. In that case, add it manually.
        |
        */

        $retainedEarningsExists = collect($liabilityRows)
            ->contains(function ($row) {
                return (string) $row['account_id'] === '164';
            });

        if (!$retainedEarningsExists && abs($netProfitLoss) >= 0.005) {
            $retainedEarningsAccount = DB::table('accountmasters as a')
                ->join(
                    'faglmsacctcategory as c',
                    'a.gl_id',
                    '=',
                    'c.id'
                )
                ->where('a.party_cd', '164')
                ->select(
                    'a.party_cd',
                    'a.party_nm',
                    'a.gl_id',
                    'c.name as category_name',
                    'c.account_type'
                )
                ->first();

            if (!$retainedEarningsAccount) {
                return back()->withErrors([
                    'retained_earnings' =>
                    'Retained Earnings account 164 was not found.',
                ]);
            }

            if ($retainedEarningsAccount->account_type !== 'BL') {
                return back()->withErrors([
                    'retained_earnings' =>
                    'Retained Earnings account 164 must belong to a BL category.',
                ]);
            }

            $liabilityRows[] = [
                'gl_id' => $retainedEarningsAccount->gl_id,
                'category' => $retainedEarningsAccount->category_name,
                'account_id' => $retainedEarningsAccount->party_cd,
                'account_name' => $retainedEarningsAccount->party_nm,
                'balance' => $netProfitLoss,
                'opening_debit' =>
                $netProfitLoss < 0 ? abs($netProfitLoss) : 0,
                'opening_credit' =>
                $netProfitLoss > 0 ? $netProfitLoss : 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Preview totals
        |--------------------------------------------------------------------------
        */

        $totalAssets = collect($assetRows)->sum('balance');
        $totalLiabilities = collect($liabilityRows)->sum('balance');

        $difference = round($totalAssets - $totalLiabilities, 2);

        $openingDebitTotal =
            collect($assetRows)->sum('opening_debit')
            + collect($liabilityRows)->sum('opening_debit');

        $openingCreditTotal =
            collect($assetRows)->sum('opening_credit')
            + collect($liabilityRows)->sum('opening_credit');

        $openingDifference = round(
            $openingDebitTotal - $openingCreditTotal,
            2
        );

        $numberOfOpeningRows =
            count($assetRows) + count($liabilityRows);

        $canClose =
            !$existingClosing
            && abs($difference) < 0.01
            && abs($openingDifference) < 0.01
            && $numberOfOpeningRows > 0;

        $compname = config('company.comp_nm');

        return view(
            'accounts.accounts_fiscal_year_closing_preview',
            compact(
                'fiscalYear',
                'startDate',
                'closingDate',
                'openingDate',
                'existingClosing',
                'assetRows',
                'liabilityRows',
                'totalIncome',
                'totalExpense',
                'netProfitLoss',
                'totalAssets',
                'totalLiabilities',
                'difference',
                'openingDebitTotal',
                'openingCreditTotal',
                'openingDifference',
                'numberOfOpeningRows',
                'canClose',
                'compname'
            )
        );
    }  //End Method

    private function normalizeAccountId($accountId): string
    {
        $accountId = trim((string) $accountId);

        /*
        |--------------------------------------------------------------------------
        | First try the value exactly as received
        |--------------------------------------------------------------------------
        */
        $masterAccountId = DB::table('accountmasters')
            ->where('party_cd', $accountId)
            ->value('party_cd');

        if ($masterAccountId !== null) {
            return (string) $masterAccountId;
        }

        /*
        |--------------------------------------------------------------------------
        | If it is numeric and shorter than 3 digits, try zero-padding
        |--------------------------------------------------------------------------
        */
        if (ctype_digit($accountId) && strlen($accountId) < 3) {
            $paddedAccountId = str_pad(
                $accountId,
                3,
                '0',
                STR_PAD_LEFT
            );

            $masterAccountId = DB::table('accountmasters')
                ->where('party_cd', $paddedAccountId)
                ->value('party_cd');

            if ($masterAccountId !== null) {
                return (string) $masterAccountId;
            }
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            'lines' => [
                "Account code {$accountId} was not found in Account Master.",
            ],
        ]);
    } // End Method


    /**
     * Display Account Master listing.
     */
    /**
     * Display Account Master listing.
     */
    public function accountMasterIndex(Request $request)
    {
        $status = $request->input('status', 'A');

        $query = DB::table('accountmasters as am')
            ->leftJoin('faglmsacctcategory as gl', function ($join) {
                $join->on('gl.id', '=', 'am.gl_id')
                    ->on('gl.company_id', '=', 'am.company_id');
            })
            ->select(
                'am.party_cd',
                'am.party_nm',
                'am.party_type',
                'am.party_contact',
                'am.party_phone1',
                'am.party_email',
                'am.gl_id',
                'am.trans_flag',
                'gl.name as gl_name'
            );

        if ($status !== 'ALL') {
            $query->where('am.trans_flag', $status);
        }

        $accounts = $query
            ->orderByRaw('CAST(am.party_cd AS UNSIGNED)')
            ->orderBy('am.party_cd')
            ->get();

        return view('accounts.accountmasters.index', compact(
            'accounts',
            'status'
        ));
    } // End Method

    /**
     * Show Account Master create form.
     */
    public function accountMasterCreate()
    {
        $companyId = $this->getAccountMasterCompanyId();

        $partyCd = $this->getNextPartyCode();

        $glAccounts = DB::table('faglmsacctcategory')
            ->select('id', 'name')
            ->where('company_id', $companyId)
            ->where(function ($query) {
                $query->where('trans_flag', 'A')
                    ->orWhereNull('trans_flag');
            })
            ->orderBy('id')
            ->get();

        return view('accounts.accountmasters.create', compact(
            'partyCd',
            'glAccounts'
        ));
    }


    /**
     * Save new Account Master.
     */
    public function accountMasterStore(Request $request)
    {
        $companyId = $this->getAccountMasterCompanyId();

        $validated = $request->validate([
            'party_nm'      => ['required', 'string', 'max:45'],
            'party_add1'    => ['nullable', 'string', 'max:35'],
            'party_add2'    => ['nullable', 'string', 'max:35'],
            'party_city'    => ['nullable', 'string', 'max:25'],
            'party_state'   => ['nullable', 'string', 'max:2'],
            'party_zip'     => ['nullable', 'string', 'max:10'],
            'party_contact' => ['nullable', 'string', 'max:35'],
            'party_phone1'  => ['nullable', 'string', 'max:10'],
            'party_phone2'  => ['nullable', 'string', 'max:10'],
            'party_fax'     => ['nullable', 'string', 'max:10'],
            'party_cell'    => ['nullable', 'string', 'max:10'],
            'party_email'   => ['nullable', 'email', 'max:35'],
            'party_type'    => ['required', 'string', 'max:1'],
            'trans_flag'    => ['required', Rule::in(['A', 'H', 'D'])],
            'account_no'    => ['nullable', 'string', 'max:25'],
            'gl_id'         => [
                'required',
                'string',
                'max:18',
                Rule::exists('faglmsacctcategory', 'id')
                    ->where(function ($query) use ($companyId) {
                        $query->where('company_id', $companyId);
                    }),
            ],
            'yn_1099'       => ['nullable', Rule::in(['Y', 'N'])],
            'ssn_1099'      => ['nullable', 'string', 'max:10'],
        ], [
            'party_nm.required'   => 'Account name is required.',
            'party_type.required' => 'Account type is required.',
            'trans_flag.required' => 'Status is required.',
            'gl_id.required'      => 'G/L Account is required.',
        ]);

        DB::transaction(function () use ($validated, $companyId) {

            // Recalculate on the server so the browser value cannot be changed.
            $partyCd = $this->getNextPartyCode();

            DB::table('accountmasters')->insert([
                'party_cd'      => $partyCd,
                'party_nm'      => trim($validated['party_nm']),
                'party_add1'    => $this->blankToNull($validated['party_add1'] ?? null),
                'party_add2'    => $this->blankToNull($validated['party_add2'] ?? null),
                'party_city'    => $this->blankToNull($validated['party_city'] ?? null),
                'party_state'   => strtoupper($this->blankToNull($validated['party_state'] ?? null) ?? ''),
                'party_zip'     => $this->blankToNull($validated['party_zip'] ?? null),
                'party_contact' => $this->blankToNull($validated['party_contact'] ?? null),
                'party_phone1'  => $this->cleanPhone($validated['party_phone1'] ?? null),
                'party_phone2'  => $this->cleanPhone($validated['party_phone2'] ?? null),
                'party_fax'     => $this->cleanPhone($validated['party_fax'] ?? null),
                'party_cell'    => $this->cleanPhone($validated['party_cell'] ?? null),
                'party_email'   => $this->blankToNull($validated['party_email'] ?? null),
                'party_type'    => $validated['party_type'],
                'trans_flag'    => $validated['trans_flag'],
                'update_flag'   => 'A',
                'user_cd'       => $this->getAccountMasterUserCode(),
                'update_dt'     => now(),
                'company_id'    => $companyId,
                'account_no'    => $this->blankToNull($validated['account_no'] ?? null),
                'gl_id'         => $validated['gl_id'],
                'yn_1099'       => $validated['yn_1099'] ?? 'N',
                'ssn_1099'      => $this->blankToNull($validated['ssn_1099'] ?? null),
            ]);
        });

        return redirect()
            ->route('accountmasters.index')
            ->with('success', 'Account Master record created successfully.');
    }


    /**
     * Show Account Master edit form.
     */
    public function accountMasterEdit(string $party_cd)
    {
        $account = DB::table('accountmasters')
            ->where('party_cd', $party_cd)
            ->first();

        abort_if(!$account, 404, 'Account Master record not found.');

        $companyId = $account->company_id ?: $this->getAccountMasterCompanyId();

        $glAccounts = DB::table('faglmsacctcategory')
            ->select('id', 'name')
            ->where('company_id', $companyId)
            ->where(function ($query) use ($account) {
                $query->where('trans_flag', 'A')
                    ->orWhereNull('trans_flag')
                    ->orWhere('id', $account->gl_id);
            })
            ->orderBy('id')
            ->get();

        return view('accounts.accountmasters.edit', compact(
            'account',
            'glAccounts'
        ));
    }


    /**
     * Update Account Master.
     */
    public function accountMasterUpdate(Request $request, string $party_cd)
    {
        $account = DB::table('accountmasters')
            ->where('party_cd', $party_cd)
            ->first();

        abort_if(!$account, 404, 'Account Master record not found.');

        $companyId = $account->company_id ?: $this->getAccountMasterCompanyId();

        $validated = $request->validate([
            'party_nm'      => ['required', 'string', 'max:45'],
            'party_add1'    => ['nullable', 'string', 'max:35'],
            'party_add2'    => ['nullable', 'string', 'max:35'],
            'party_city'    => ['nullable', 'string', 'max:25'],
            'party_state'   => ['nullable', 'string', 'max:2'],
            'party_zip'     => ['nullable', 'string', 'max:10'],
            'party_contact' => ['nullable', 'string', 'max:35'],
            'party_phone1'  => ['nullable', 'string', 'max:10'],
            'party_phone2'  => ['nullable', 'string', 'max:10'],
            'party_fax'     => ['nullable', 'string', 'max:10'],
            'party_cell'    => ['nullable', 'string', 'max:10'],
            'party_email'   => ['nullable', 'email', 'max:35'],
            'party_type'    => ['required', 'string', 'max:1'],
            'trans_flag'    => ['required', Rule::in(['A', 'H', 'D'])],
            'account_no'    => ['nullable', 'string', 'max:25'],
            'gl_id'         => [
                'required',
                'string',
                'max:18',
                Rule::exists('faglmsacctcategory', 'id')
                    ->where(function ($query) use ($companyId) {
                        $query->where('company_id', $companyId);
                    }),
            ],
            'yn_1099'       => ['nullable', Rule::in(['Y', 'N'])],
            'ssn_1099'      => ['nullable', 'string', 'max:10'],
        ], [
            'party_nm.required'   => 'Account name is required.',
            'party_type.required' => 'Account type is required.',
            'trans_flag.required' => 'Status is required.',
            'gl_id.required'      => 'G/L Account is required.',
        ]);

        DB::table('accountmasters')
            ->where('party_cd', $party_cd)
            ->update([
                'party_nm'      => trim($validated['party_nm']),
                'party_add1'    => $this->blankToNull($validated['party_add1'] ?? null),
                'party_add2'    => $this->blankToNull($validated['party_add2'] ?? null),
                'party_city'    => $this->blankToNull($validated['party_city'] ?? null),
                'party_state'   => strtoupper($this->blankToNull($validated['party_state'] ?? null) ?? ''),
                'party_zip'     => $this->blankToNull($validated['party_zip'] ?? null),
                'party_contact' => $this->blankToNull($validated['party_contact'] ?? null),
                'party_phone1'  => $this->cleanPhone($validated['party_phone1'] ?? null),
                'party_phone2'  => $this->cleanPhone($validated['party_phone2'] ?? null),
                'party_fax'     => $this->cleanPhone($validated['party_fax'] ?? null),
                'party_cell'    => $this->cleanPhone($validated['party_cell'] ?? null),
                'party_email'   => $this->blankToNull($validated['party_email'] ?? null),
                'party_type'    => $validated['party_type'],
                'trans_flag'    => $validated['trans_flag'],
                'update_flag'   => 'U',
                'user_cd'       => $this->getAccountMasterUserCode(),
                'update_dt'     => now(),
                'company_id'    => $companyId,
                'account_no'    => $this->blankToNull($validated['account_no'] ?? null),
                'gl_id'         => $validated['gl_id'],
                'yn_1099'       => $validated['yn_1099'] ?? 'N',
                'ssn_1099'      => $this->blankToNull($validated['ssn_1099'] ?? null),
            ]);

        return redirect()
            ->route('accountmasters.index')
            ->with('success', 'Account Master record updated successfully.');
    }


    /**
     * Mark Account Master as deleted.
     */
    public function accountMasterDestroy(string $party_cd)
    {
        $account = DB::table('accountmasters')
            ->where('party_cd', $party_cd)
            ->first();

        abort_if(!$account, 404, 'Account Master record not found.');

        DB::table('accountmasters')
            ->where('party_cd', $party_cd)
            ->update([
                'trans_flag'  => 'D',
                'update_flag' => 'D',
                'user_cd'     => $this->getAccountMasterUserCode(),
                'update_dt'   => now(),
            ]);

        return redirect()
            ->route('accountmasters.index')
            ->with('success', 'Account Master record marked as deleted.');
    }


    /**
     * Generate next numeric party code.
     */
    private function getNextPartyCode(): string
    {
        $maximumPartyCode = DB::table('accountmasters')
            ->max('party_cd');

        return (string) ((int) $maximumPartyCode + 1);
    }


    /**
     * Get current company ID.
     */
    private function getAccountMasterCompanyId(): string
    {
        if (session()->has('company_id')) {
            return (string) session('company_id');
        }

        $company = DB::table('gnmscomp')->first();

        if ($company && isset($company->company_id)) {
            return (string) $company->company_id;
        }

        return '0001';
    }


    /**
     * Get current user's code.
     */
    private function getAccountMasterUserCode(): ?string
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if (isset($user->user_cd)) {
            return substr((string) $user->user_cd, 0, 4);
        }

        if (isset($user->id)) {
            return substr((string) $user->id, 0, 4);
        }

        return null;
    }


    /**
     * Convert empty text to null.
     */
    private function blankToNull($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }


    /**
     * Store telephone numbers as maximum 10 numeric digits.
     */
    private function cleanPhone($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return substr($digits, 0, 10);
    } // End Method

    private function buildBankReconReportData($recon_id): array
    {
        $companyId = config('company.company_id', 'DS02');

        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('recon_id', $recon_id)
            ->first();

        abort_if(!$recon, 404);

        $bank = DB::table('fashmsbank')
            ->where('company_id', $companyId)
            ->whereRaw('TRIM(account_id) = ?', [trim((string) $recon->bank_id)])
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Current reconciliation items
        |--------------------------------------------------------------------------
        */
        $currentItems = DB::table('fashbankrecon_items')
            ->where('recon_id', $recon->recon_id)
            ->select([
                'trans_no',
                DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt_str"),
                'serial_no',
            ])
            ->get();

        $currentSet = [];

        foreach ($currentItems as $item) {
            $key = $item->trans_no
                . '|' . $item->trans_dt_str
                . '|' . $item->serial_no;

            $currentSet[$key] = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Transactions cleared in prior reconciliations
        |--------------------------------------------------------------------------
        */
        $previousClearedItems = DB::table('fashbankrecon_items as i')
            ->join('fashbankrecon as h', 'h.recon_id', '=', 'i.recon_id')
            ->where('h.company_id', $recon->company_id)
            ->whereRaw('TRIM(h.bank_id) = ?', [trim((string) $recon->bank_id)])
            ->where('h.recon_id', '<>', $recon->recon_id)
            ->where(function ($query) use ($recon) {
                $query->where('h.period_year', '<', $recon->period_year)
                    ->orWhere(function ($query) use ($recon) {
                        $query->where('h.period_year', $recon->period_year)
                            ->where('h.period_month', '<', $recon->period_month);
                    });
            })
            ->select([
                'i.trans_no',
                DB::raw("DATE_FORMAT(i.trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt_str"),
                'i.serial_no',
            ])
            ->get();

        $previousClearedSet = [];

        foreach ($previousClearedItems as $item) {
            $key = $item->trans_no
                . '|' . $item->trans_dt_str
                . '|' . $item->serial_no;

            $previousClearedSet[$key] = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Earliest reconciliation date is the conversion cutoff
        |--------------------------------------------------------------------------
        */
        $firstRecon = DB::table('fashbankrecon')
            ->where('company_id', $recon->company_id)
            ->whereRaw('TRIM(bank_id) = ?', [trim((string) $recon->bank_id)])
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->first();

        $conversionStartDt = $firstRecon && $firstRecon->opening_dt
            ? \Carbon\Carbon::parse($firstRecon->opening_dt)->startOfDay()
            : \Carbon\Carbon::create(
                (int) $recon->period_year,
                (int) $recon->period_month,
                1
            )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Load all eligible bank transactions through statement ending date
        |--------------------------------------------------------------------------
        */
        $transactions = DB::table('fashtrans')
            ->where('company_id', $recon->company_id)
            ->whereRaw('TRIM(bank_id) = ?', [trim((string) $recon->bank_id)])
            ->whereIn('trans_type', ['PAYM', 'DEPS'])
            ->whereBetween('trans_dt', [
                $conversionStartDt,
                $recon->closing_dt,
            ])
            ->orderBy('trans_dt')
            ->orderBy('trans_no')
            ->orderBy('serial_no')
            ->select([
                'trans_no',
                DB::raw("DATE_FORMAT(trans_dt, '%Y-%m-%d %H:%i:%s') AS trans_dt"),
                'serial_no',
                'trans_type',
                'payto_name',
                'remarks',
                'ref_no',
                'check_no',
                'debit_amt',
                'credit_amt',
            ])
            ->get();

        $clearedDeposits   = collect();
        $unclearedDeposits = collect();
        $clearedPayments   = collect();
        $unclearedPayments = collect();

        foreach ($transactions as $transaction) {
            $key = $transaction->trans_no
                . '|' . $transaction->trans_dt
                . '|' . $transaction->serial_no;

            $isClearedCurrent = isset($currentSet[$key]);

            /*
            |--------------------------------------------------------------------------
            | Exclude transactions cleared in a previous month
            |--------------------------------------------------------------------------
            */
            if (isset($previousClearedSet[$key]) && !$isClearedCurrent) {
                continue;
            }

            if ($transaction->trans_type === 'DEPS') {
                if ($isClearedCurrent) {
                    $clearedDeposits->push($transaction);
                } else {
                    $unclearedDeposits->push($transaction);
                }
            }

            if ($transaction->trans_type === 'PAYM') {
                if ($isClearedCurrent) {
                    $clearedPayments->push($transaction);
                } else {
                    $unclearedPayments->push($transaction);
                }
            }
        }

        $totals = [
            'cleared_deposits' => $clearedDeposits->sum(
                fn($row) => (float) $row->credit_amt
            ),

            'uncleared_deposits' => $unclearedDeposits->sum(
                fn($row) => (float) $row->credit_amt
            ),

            'cleared_payments' => $clearedPayments->sum(
                fn($row) => (float) $row->debit_amt
            ),

            'uncleared_payments' => $unclearedPayments->sum(
                fn($row) => (float) $row->debit_amt
            ),
        ];

        $totals['cleared_balance'] =
            (float) $recon->opening_balance
            + $totals['cleared_deposits']
            - $totals['cleared_payments'];

        $totals['difference'] =
            (float) $recon->closing_balance
            - $totals['cleared_balance'];

        return [
            'recon'              => $recon,
            'bank'               => $bank,
            'clearedDeposits'    => $clearedDeposits,
            'unclearedDeposits'  => $unclearedDeposits,
            'clearedPayments'    => $clearedPayments,
            'unclearedPayments'  => $unclearedPayments,
            'totals'             => $totals,
        ];
    } // End Method

    public function BankreconSimplePdf($recon_id)
    {
        $companyId = config('company.company_id', 'DS02');

        /*
        |--------------------------------------------------------------------------
        | Load reconciliation
        |--------------------------------------------------------------------------
        */
        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('recon_id', $recon_id)
            ->first();

        abort_if(
            !$recon,
            404,
            'Bank reconciliation not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | CLOSED reconciliation must use historical snapshot data
        |--------------------------------------------------------------------------
        */
        if ($recon->status === 'CLOSED') {

            $data = $this->bankreconHistoryReportData($recon);
        } else {

            /*
            |--------------------------------------------------------------------------
            | OPEN reconciliation can still use the live report calculation
            |--------------------------------------------------------------------------
            |
            | This keeps the current working reconciliation behavior intact.
            |
            */
            $data = $this->buildBankReconReportData($recon_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Render simplified PDF Blade
        |--------------------------------------------------------------------------
        */
        $html = view(
            'accounts.accounts_bankrecon_simple_report',
            $data
        )->render();

        $tempDir = storage_path('app/mpdf-temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'orientation'   => 'L',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 10,
            'tempDir'       => $tempDir,
            'simpleTables'  => true,
        ]);

        $mpdf->SetTitle(
            'Bank Reconciliation - Simplified'
        );

        $mpdf->SetAuthor(
            config('app.name')
        );

        $mpdf->SetDisplayMode('fullpage');

        $mpdf->shrink_tables_to_fit = 1;

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-size:7pt;color:#666;">'
                . '<tr>'
                . '<td width="50%">Printed: {DATE m/d/Y h:i A}</td>'
                . '<td width="50%" style="text-align:right;">Page {PAGENO} of {nbpg}</td>'
                . '</tr>'
                . '</table>'
        );

        $mpdf->WriteHTML($html);

        $fileName =
            'Bank_Reconciliation_Simplified_'
            . preg_replace(
                '/[^A-Za-z0-9_-]/',
                '_',
                trim((string) $recon->bank_id)
            )
            . '_'
            . $recon->period_year
            . '_'
            . str_pad(
                (string) $recon->period_month,
                2,
                '0',
                STR_PAD_LEFT
            )
            . '.pdf';

        return response(
            $mpdf->Output(
                $fileName,
                \Mpdf\Output\Destination::STRING_RETURN
            ),
            200,
            [
                'Content-Type' =>
                'application/pdf',

                'Content-Disposition' =>
                'inline; filename="' . $fileName . '"',
            ]
        );
    } // End Method

    public function BankreconSimpleExcel($recon_id)
    {
        $companyId = config('company.company_id', 'DS02');

        /*
        |--------------------------------------------------------------------------
        | Load reconciliation
        |--------------------------------------------------------------------------
        */
        $recon = DB::table('fashbankrecon')
            ->where('company_id', $companyId)
            ->where('recon_id', $recon_id)
            ->first();

        abort_if(
            !$recon,
            404,
            'Bank reconciliation not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | CLOSED reconciliation uses historical snapshot data
        |--------------------------------------------------------------------------
        */
        if ($recon->status === 'CLOSED') {

            $data = $this->bankreconHistoryReportData($recon);
        } else {

            /*
            |--------------------------------------------------------------------------
            | OPEN reconciliation can still use live reconciliation data
            |--------------------------------------------------------------------------
            */
            $data = $this->buildBankReconReportData($recon_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */
        $fileName =
            'Bank_Reconciliation_Simplified_'
            . preg_replace(
                '/[^A-Za-z0-9_-]/',
                '_',
                trim((string) $recon->bank_id)
            )
            . '_'
            . $recon->period_year
            . '_'
            . str_pad(
                (string) $recon->period_month,
                2,
                '0',
                STR_PAD_LEFT
            )
            . '.xlsx';

        return Excel::download(
            new BankReconciliationSimpleExport($data),
            $fileName
        );
    } // End Method

    public function BankCategorization()
    {
        $banks = DB::table('fashmsbank')
            ->orderBy('account_name')
            ->get();

        return view('accounts.accounts_bank_categorization', compact('banks'), [
            'title' => 'Bank Categorization',
            'module' => 'account',
        ]);
    } // End Method


    public function BankCategorizationData(Request $request)
    {
        $bankId    = $request->input('bank_id');
        $transType = $request->input('trans_type', 'PAYM');
        $fromDate  = $request->input('from_date');
        $toDate    = $request->input('to_date');
        $status    = $request->input('status', 'all');

        $query = DB::table('fashtrans as f')
            ->leftJoin('accountmasters as a', 'f.party_cd', '=', 'a.party_cd')
            ->select(
                'f.trans_no',
                'f.trans_dt',
                'f.trans_type',
                'f.serial_no',
                'f.bank_id',
                'f.party_cd',
                'a.party_nm',
                'f.payto_name',
                'f.check_no',
                'f.debit_amt',
                'f.credit_amt',
                'f.remarks'
            )
            ->where('f.serial_no', '000')
            ->where('f.trans_flag', 'A');

        if (!empty($bankId)) {
            $query->where('f.bank_id', $bankId);
        }

        if ($transType === 'PAYM') {
            $query->where('f.trans_type', 'PAYM')
                ->where('f.debit_amt', '>', 0);
        } elseif ($transType === 'DEPS') {
            $query->where('f.trans_type', 'DEPS')
                ->where('f.credit_amt', '>', 0);
        }

        if (!empty($fromDate)) {
            $query->whereDate('f.trans_dt', '>=', Carbon::parse($fromDate)->format('Y-m-d'));
        }

        if (!empty($toDate)) {
            $query->whereDate('f.trans_dt', '<=', Carbon::parse($toDate)->format('Y-m-d'));
        }

        if ($status === 'categorized') {
            $query->whereNotNull('f.party_cd')
                ->where('f.party_cd', '<>', '');
        }

        if ($status === 'uncategorized') {
            $query->where(function ($q) {
                $q->whereNull('f.party_cd')
                    ->orWhere('f.party_cd', '');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Do not force fixed ordering after this if DataTables sorting is used.
        | This default order will show latest records first.
        |--------------------------------------------------------------------------
        */
        $query->orderBy('f.trans_dt', 'desc')
            ->orderBy('f.trans_no', 'desc');

        //$accounts = DB::table('accountmasters')
        //    ->select('party_cd', 'party_nm')
        //    ->orderBy('party_nm')
        //    ->get();

        return DataTables::of($query)
            ->addColumn('select_box', function ($row) {
                return '<input type="checkbox" class="row-check" value="' . e($row->trans_no) . '">';
            })
            ->editColumn('trans_dt', function ($row) {
                return $row->trans_dt ? Carbon::parse($row->trans_dt)->format('m/d/Y') : '';
            })
            ->addColumn('description', function ($row) {
                return e($row->payto_name ?? '');
            })
            ->editColumn('debit_amt', function ($row) {
                return $row->debit_amt > 0
                    ? '<div class="text-end">' . number_format($row->debit_amt, 2) . '</div>'
                    : '';
            })
            ->editColumn('credit_amt', function ($row) {
                return $row->credit_amt > 0
                    ? '<div class="text-end">' . number_format($row->credit_amt, 2) . '</div>'
                    : '';
            })
            ->addColumn('party_select', function ($row) {
                $selectedText = '';

                if (!empty($row->party_cd)) {
                    $selectedText = trim(($row->party_cd ?? '') . ' - ' . ($row->party_nm ?? ''));
                }

                $html = '<select class="form-select form-select-sm party-cd select2-party"
                            data-trans-no="' . e($row->trans_no) . '"
                            style="width: 100%;">';

                if (!empty($row->party_cd)) {
                    $html .= '<option value="' . e($row->party_cd) . '" selected>';
                    $html .= e($selectedText);
                    $html .= '</option>';
                }

                $html .= '</select>';

                return $html;
            })
            ->addColumn('account_name', function ($row) {
                return e($row->party_nm ?? '');
            })
            ->addColumn('remarks_input', function ($row) {
                return '<input type="text"
                        class="form-control form-control-sm remarks-input"
                        data-trans-no="' . e($row->trans_no) . '"
                        value="' . e($row->remarks ?? '') . '">';
            })
            ->addColumn('rule_status', function ($row) {
                if (!empty($row->party_cd)) {
                    return '<span class="badge bg-success">Categorized</span>';
                }

                return '<span class="badge bg-warning text-dark">Uncategorized</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button"
                            class="btn btn-sm btn-primary save-one"
                            data-trans-no="' . e($row->trans_no) . '">
                        Save
                    </button>';
            })
            ->rawColumns([
                'select_box',
                'debit_amt',
                'credit_amt',
                'party_select',
                'remarks_input',
                'rule_status',
                'action',
            ])
            ->make(true);
    } // End Method


    public function SaveBankCategorization(Request $request)
    {
        $rows = $request->input('rows', []);

        if (empty($rows)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No rows selected.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $transNo = $row['trans_no'] ?? null;

                if (empty($transNo)) {
                    continue;
                }

                DB::table('fashtrans')
                    ->where('trans_no', $transNo)
                    ->where('serial_no', '000')
                    ->update([
                        'party_cd' => $row['party_cd'] ?? null,
                        'remarks'  => $row['remarks'] ?? null,
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Selected transactions saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    } // End Method


    public function ApplyBankCategorizationRules(Request $request)
    {
        $transNos  = $request->input('trans_nos', []);
        $bankId    = $request->input('bank_id');
        $transType = $request->input('trans_type', 'PAYM');

        if (empty($transNos)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please select at least one row.',
            ], 422);
        }

        $direction = $transType === 'DEPS' ? 'C' : 'D';

        $rules = DB::table('bank_rules')
            ->where('is_active', 1)
            ->where(function ($q) use ($bankId) {
                $q->whereNull('bank_id')
                    ->orWhere('bank_id', '')
                    ->orWhere('bank_id', $bankId);
            })
            ->where(function ($q) use ($direction) {
                $q->where('direction', 'Any')
                    ->orWhere('direction', $direction);
            })
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $transactions = DB::table('fashtrans')
            ->whereIn('trans_no', $transNos)
            ->where('serial_no', '000')
            ->where('trans_flag', 'A')
            ->get();

        $updated = 0;

        DB::beginTransaction();

        try {
            foreach ($transactions as $transaction) {

                $searchText = strtoupper(trim(
                    ($transaction->payto_name ?? '') . ' ' .
                        ($transaction->remarks ?? '') . ' ' .
                        ($transaction->check_no ?? '')
                ));

                foreach ($rules as $rule) {
                    if ($this->BankRuleMatched($searchText, $rule)) {

                        DB::table('fashtrans')
                            ->where('trans_no', $transaction->trans_no)
                            ->where('serial_no', '000')
                            ->update([
                                'party_cd' => $rule->party_cd,
                            ]);

                        $updated++;
                        break;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $updated . ' transaction(s) categorized by rules.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    } // End Method


    private function BankRuleMatched(string $searchText, $rule): bool
    {
        $matchType = $rule->match_type ?? $rule->pattern_type ?? 'contains';
        $pattern   = $rule->pattern ?? $rule->pattern_value ?? '';

        $pattern = strtoupper(trim($pattern));

        if ($pattern === '') {
            return false;
        }

        return match ($matchType) {
            'contains'    => str_contains($searchText, $pattern),
            'starts_with' => str_starts_with($searchText, $pattern),
            'ends_with'   => str_ends_with($searchText, $pattern),
            'exact'       => $searchText === $pattern,
            'equals'      => $searchText === $pattern,
            default       => false,
        };
    } // End Method


    public function BankCategorizationAccountSearch(Request $request)
    {
        $search = trim((string) $request->input('q'));

        $query = DB::table('accountmasters')
            ->select(
                'party_cd',
                'party_nm'
            );

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('party_cd', 'like', '%' . $search . '%')
                    ->orWhere('party_nm', 'like', '%' . $search . '%');
            });
        }

        $accounts = $query
            ->orderBy('party_nm')
            ->limit(30)
            ->get();

        $results = $accounts->map(function ($account) {
            return [
                'id'   => $account->party_cd,
                'text' => $account->party_cd . ' - ' . $account->party_nm,
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    } // End Method


    public function TraditionalBankReconciliationDates(Request $request)
    {
        $bank_id = $request->input('bank_id');

        if (!$bank_id) {
            return response()->json([]);
        }

        $reconciliations = DB::table('fashbankrecon')
            ->where('bank_id', $bank_id)
            ->whereNotNull('closing_dt')
            ->orderBy('closing_dt', 'desc')
            ->select(
                'recon_id',
                'closing_dt',
                'closing_balance',
                'status'
            )
            ->get();

        $data = $reconciliations->map(function ($row) {
            return [
                'recon_id' => $row->recon_id,

                'closing_dt' => \Carbon\Carbon::parse(
                    $row->closing_dt
                )->format('Y-m-d'),

                'display_date' => \Carbon\Carbon::parse(
                    $row->closing_dt
                )->format('m/d/Y'),

                'closing_balance' => (float) $row->closing_balance,

                'status' => $row->status,
            ];
        });

        return response()->json($data);
    } // End Method

    public function TraditionalBankReconciliation(Request $request)
    {
        // ---------------------------------------------------------------
        // 1. Bank List
        // ---------------------------------------------------------------
        $fashbanks = DB::table('fashmsbank')
            ->orderBy('account_name')
            ->get();

        // ---------------------------------------------------------------
        // 2. User Selection
        // ---------------------------------------------------------------
        $bank_id = $request->input('account_id');

        $recon_id = $request->input('recon_id');

        // ---------------------------------------------------------------
        // 3. Initial Screen
        // ---------------------------------------------------------------
        if (!$bank_id || !$recon_id) {

            return view(
                'accounts.accounts_traditional_bank_reconciliation',
                [
                    'fashbanks' => $fashbanks,

                    'bank_id' => $bank_id,

                    'recon_id' => $recon_id,

                    'bank' => null,

                    'recon' => null,

                    'recon_date' => null,

                    'statement_closing_balance' => 0.00,

                    'book_balance' => 0.00,

                    'unclearedDeposits' => collect(),

                    'unclearedPayments' => collect(),

                    'totalDepositsInTransit' => 0.00,

                    'totalOutstandingPayments' => 0.00,

                    'calculatedStatementBalance' => 0.00,

                    'difference' => 0.00,
                ]
            );
        }

        // ---------------------------------------------------------------
        // 4. Get Reconciliation Data
        // ---------------------------------------------------------------
        try {

            $data =
                $this->getTraditionalBankReconciliationData(
                    $bank_id,
                    $recon_id
                );
        } catch (\Exception $e) {

            return redirect()
                ->route(
                    'accounts.traditional.bank.reconciliation'
                )
                ->with(
                    'error',
                    $e->getMessage()
                );
        }

        // ---------------------------------------------------------------
        // 5. Add Screen-Specific Variables
        // ---------------------------------------------------------------
        $data['fashbanks'] = $fashbanks;

        $data['bank_id'] = $bank_id;

        $data['recon_id'] = $recon_id;

        // ---------------------------------------------------------------
        // 6. Return View
        // ---------------------------------------------------------------
        return view(
            'accounts.accounts_traditional_bank_reconciliation',
            $data
        );
    } // End Method

    private function getTraditionalBankReconciliationData($bank_id, $recon_id): array
    {

        // ---------------------------------------------------------------
        // 1. Get selected bank
        // ---------------------------------------------------------------
        $bank = DB::table('fashmsbank')
            ->where('account_id', $bank_id)
            ->first();

        if (!$bank) {
            throw new \Exception(
                'Selected bank account was not found.'
            );
        }

        // ---------------------------------------------------------------
        // 2. Get reconciliation header
        //
        // Make sure recon belongs to selected bank
        // ---------------------------------------------------------------
        $recon = DB::table('fashbankrecon')
            ->where('recon_id', $recon_id)
            ->where('bank_id', $bank_id)
            ->first();

        if (!$recon) {
            throw new \Exception(
                'Selected reconciliation was not found.'
            );
        }

        // ---------------------------------------------------------------
        // 3. Reconciliation Date
        // ---------------------------------------------------------------
        $reconDate = \Carbon\Carbon::parse(
            $recon->closing_dt
        );

        $recon_date = $reconDate->format('Y-m-d');

        // ---------------------------------------------------------------
        // 4. Actual Bank Statement Closing Balance
        // ---------------------------------------------------------------
        $statement_closing_balance =
            (float) ($recon->closing_balance ?? 0.00);

        // ---------------------------------------------------------------
        // 5. Fiscal / Calendar Year Start
        //
        // Same logic as BankReport()
        // ---------------------------------------------------------------
        $fiscal_start_date = $reconDate
            ->copy()
            ->startOfYear()
            ->format('Y-m-d');

        // ---------------------------------------------------------------
        // 6. Get BBAL from fagltrjournal
        //
        // GL Debit BBAL  = Normal Bank Balance
        // GL Credit BBAL = Bank Overdraft
        // ---------------------------------------------------------------
        $journalOpening = DB::table('fagltrjournal')
            ->where('account_id', $bank_id)
            ->where('trans_type', 'BBAL')
            ->whereDate(
                'trans_dt',
                $fiscal_start_date
            )
            ->selectRaw('
                COALESCE(SUM(debit_amt), 0.00) as debit,
                COALESCE(SUM(credit_amt), 0.00) as credit
            ')
            ->first();

        $journal_debit =
            (float) ($journalOpening->debit ?? 0.00);

        $journal_credit =
            (float) ($journalOpening->credit ?? 0.00);

        // ---------------------------------------------------------------
        // 7. Get Bank Activity
        //
        // From fiscal start through reconciliation date
        //
        // fashtrans:
        //
        // debit_amt  = Payment / Expense
        // credit_amt = Deposit / Receipt
        // ---------------------------------------------------------------
        $activity = DB::table('fashtrans')
            ->where('bank_id', $bank_id)
            ->where('serial_no', '000')
            ->whereDate(
                'trans_dt',
                '>=',
                $fiscal_start_date
            )
            ->whereDate(
                'trans_dt',
                '<=',
                $recon_date
            )
            ->selectRaw('
                COALESCE(SUM(debit_amt), 0.00) as debit,
                COALESCE(SUM(credit_amt), 0.00) as credit
            ')
            ->first();

        $total_debit =
            (float) ($activity->debit ?? 0.00);

        $total_credit =
            (float) ($activity->credit ?? 0.00);

        // ---------------------------------------------------------------
        // 8. Calculate Bank Book Balance
        //
        // Same logic as BankReport()
        //
        // BBAL Debit
        // - BBAL Credit
        // + Deposits / Credits
        // - Payments / Debits
        // ---------------------------------------------------------------
        $book_balance =
            ($journal_debit - $journal_credit)
            + $total_credit
            - $total_debit;

        // ---------------------------------------------------------------
        // 9. Deposits in Transit
        //
        // DEPS
        // cleared_flag = N
        // Amount = credit_amt
        // ---------------------------------------------------------------
        $unclearedDeposits = DB::table(
            'fashbankrecon_snapshot_items'
        )
            ->where('recon_id', $recon_id)
            ->where('cleared_flag', 'N')
            ->where('trans_type', 'DEPS')
            ->whereDate(
                'trans_dt',
                '<=',
                $recon_date
            )
            ->orderBy('trans_dt', 'asc')
            ->orderBy('trans_no', 'asc')
            ->get();

        $totalDepositsInTransit =
            (float) $unclearedDeposits
                ->sum('credit_amt');

        // ---------------------------------------------------------------
        // 10. Outstanding Payments / Checks
        //
        // PAYM
        // cleared_flag = N
        // Amount = debit_amt
        // ---------------------------------------------------------------
        $unclearedPayments = DB::table(
            'fashbankrecon_snapshot_items'
        )
            ->where('recon_id', $recon_id)
            ->where('cleared_flag', 'N')
            ->where('trans_type', 'PAYM')
            ->whereDate(
                'trans_dt',
                '<=',
                $recon_date
            )
            ->orderBy('trans_dt', 'asc')
            ->orderBy('trans_no', 'asc')
            ->get();

        $totalOutstandingPayments =
            (float) $unclearedPayments
                ->sum('debit_amt');

        // ---------------------------------------------------------------
        // 11. Traditional Reconciliation Calculation
        //
        // Bank Book Balance
        // + Outstanding Payments
        // - Deposits in Transit
        //
        // = Calculated Bank Statement Balance
        // ---------------------------------------------------------------
        $calculatedStatementBalance =
            $book_balance
            + $totalOutstandingPayments
            - $totalDepositsInTransit;

        // ---------------------------------------------------------------
        // 12. Difference
        // ---------------------------------------------------------------
        $difference =
            $calculatedStatementBalance
            - $statement_closing_balance;

        // Prevent -0.00
        if (abs($difference) < 0.005) {
            $difference = 0.00;
        }

        // ---------------------------------------------------------------
        // 13. Return All Data
        // ---------------------------------------------------------------
        return [

            'bank' =>
            $bank,

            'recon' =>
            $recon,

            'recon_date' =>
            $recon_date,

            'statement_closing_balance' =>
            $statement_closing_balance,

            'book_balance' =>
            $book_balance,

            'unclearedDeposits' =>
            $unclearedDeposits,

            'unclearedPayments' =>
            $unclearedPayments,

            'totalDepositsInTransit' =>
            $totalDepositsInTransit,

            'totalOutstandingPayments' =>
            $totalOutstandingPayments,

            'calculatedStatementBalance' =>
            $calculatedStatementBalance,

            'difference' =>
            $difference,

        ];
    } // End Method

    public function TraditionalBankReconciliationPdf(Request $request)
    {

        $bank_id = $request->input('account_id');

        $recon_id = $request->input('recon_id');

        // ---------------------------------------------------------------
        // Validation
        // ---------------------------------------------------------------
        if (!$bank_id || !$recon_id) {

            return redirect()
                ->route(
                    'accounts.traditional.bank.reconciliation'
                )
                ->with(
                    'error',
                    'Please select bank and reconciliation date.'
                );
        }

        // ---------------------------------------------------------------
        // Get Common Reconciliation Data
        // ---------------------------------------------------------------
        try {

            $data =
                $this->getTraditionalBankReconciliationData(
                    $bank_id,
                    $recon_id
                );
        } catch (\Exception $e) {

            return redirect()
                ->route(
                    'accounts.traditional.bank.reconciliation'
                )
                ->with(
                    'error',
                    $e->getMessage()
                );
        }

        // ---------------------------------------------------------------
        // Generate PDF
        // ---------------------------------------------------------------
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'accounts.accounts_traditional_bank_reconciliation_pdf',
            $data
        );

        $pdf->setPaper(
            'letter',
            'portrait'
        );

        $fileName =
            'Traditional_Bank_Reconciliation_' .
            \Carbon\Carbon::parse(
                $data['recon_date']
            )->format('Ymd') .
            '.pdf';

        return $pdf->stream($fileName);
    } // End Method


    public function TraditionalBankReconciliationExcel(Request $request)
    {
        $bank_id = $request->input('account_id');
        $recon_id = $request->input('recon_id');

        // ---------------------------------------------------------------
        // Validate selection
        // ---------------------------------------------------------------
        if (!$bank_id || !$recon_id) {

            return redirect()
                ->route('accounts.traditional.bank.reconciliation')
                ->with(
                    'error',
                    'Please select bank and reconciliation date.'
                );
        }

        // ---------------------------------------------------------------
        // Get common reconciliation data
        // ---------------------------------------------------------------
        try {

            $data =
                $this->getTraditionalBankReconciliationData(
                    $bank_id,
                    $recon_id
                );
        } catch (\Exception $e) {

            return redirect()
                ->route('accounts.traditional.bank.reconciliation')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }

        // ---------------------------------------------------------------
        // Excel filename
        // ---------------------------------------------------------------
        $fileName =
            'Traditional_Bank_Reconciliation_' .
            \Carbon\Carbon::parse(
                $data['recon_date']
            )->format('Ymd') .
            '.xlsx';

        // ---------------------------------------------------------------
        // Export Excel
        // ---------------------------------------------------------------
        return Excel::download(
            new TraditionalBankReconciliationExport($data),
            $fileName
        );
    }
}
