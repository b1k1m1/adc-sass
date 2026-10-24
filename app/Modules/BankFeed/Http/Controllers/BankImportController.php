<?php

namespace App\Modules\BankFeed\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankFeed\Models\BankImport;
use App\Modules\BankFeed\Models\BankRule;
use App\Modules\BankFeed\Services\BankFeedService;
use App\Modules\BankFeed\Services\BankRulesService;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BankImportController extends Controller
{
    public function index(Request $request)
    {
        // Banks for the dropdown
        $banks = DB::table('fashmsbank')
            ->orderBy('account_name')
            ->get();

        // Bank filter from request
        $bankFilter = $request->input('filter_bank_id'); // from GET

        // Vendor / party list (your existing code)
        $accounts = DB::table('accountmasters')   // <-- your table name
            ->orderBy('party_nm')
            ->get();

        // Base query: only unposted imports
        $importsQuery = BankImport::query()
            ->where(function ($q) {
                $q->whereNull('is_posted')
                ->orWhere('is_posted', false);
            })
            ->where('status', '!=', 'ignored')
            ->orderBy('txn_date', 'asc')
            ->orderBy('id', 'asc');

        // Filter by bank, if selected
        if ($bankFilter) {
            $importsQuery->where('bank_id', $bankFilter);
        }

        // GL codes for category dropdown
        $glcodes = DB::table('faglmsacctcategory')
            ->orderBy('name')
            ->get();

        // Paginate and keep query string (filters) on pagination links
        //$imports = $importsQuery->paginate(50)->withQueryString();
        $imports = $importsQuery->get();

        /**
         * Load rules for this bank:
         * - Only active rules
         * - If bank selected: rules with bank_id == that OR bank_id NULL (generic)
         * - If no bank selected: only generic rules (bank_id NULL)
         * - Order by priority then id
         */
        $rules = BankRule::query()
            ->where('is_active', 1)
            ->when($bankFilter, function ($q) use ($bankFilter) {
                $q->where(function ($sub) use ($bankFilter) {
                    $sub->whereNull('bank_id')
                        ->orWhere('bank_id', $bankFilter);
                });
            }, function ($q) {
                $q->whereNull('bank_id');
            })
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        // Apply rule service in memory to each row (no DB writes)
        foreach ($imports as $row) {
            $amountOut = (float)($row->debit  ?? 0);
            $amountIn  = (float)($row->credit ?? 0);
            $amount    = $amountIn - $amountOut;

            // Normalize description (same logic)
            $desc = $this->normalizeDescription($row->description);
            $bankCategory   = $row->bank_category ?? null;

            $rule = $this->findMatchingRule($row->bank_id, $desc, $amount, $bankCategory ?? null);

            $row->ui_suggested_party_cd = $rule?->party_cd;

                if ($rule) {
                    $row->rule_hits_count = 1;
                    $row->rule_hits = [[
                        'label' => "Rule #{$rule->id}: {$rule->pattern_value}",
                    ]];
                } else {
                    $row->rule_hits_count = 0;
                    $row->rule_hits = [];
                }
            }

        return view('bank_feed.bank_imports.index', compact(
            'banks',
            'imports',
            'accounts',
            'glcodes',
            'bankFilter'
        ));

    } // End Method

        private function makeFingerprint(
        string $bankId,
        string $dateStr,
        ?string $description,
        ?float $debit,
        ?float $credit,
        ?string $sourceFile = null,
        ?int $rowNumber = null
    ): string
    {
        $date = Carbon::parse($dateStr)->format('Y-m-d');

        $debit  = number_format((float)($debit  ?? 0), 2, '.', '');
        $credit = number_format((float)($credit ?? 0), 2, '.', '');

        $desc = $this->normalizeDescription($description);

        $raw = implode('|', [
            $bankId,
            $date,
            $desc,
            $debit,
            $credit,
            $sourceFile,
            $rowNumber,
        ]);

        return md5($raw);
    }

    private function normalizeDescription(?string $description): string
    {
        if (!$description) {
            return '';
        }

        // Convert to uppercase for consistency
        $desc = strtoupper($description);

        // Remove substring "ACH WITHDRAWAL"
        $desc = str_ireplace('ACH WITHDRAWAL', '', $desc);

        // Trim and collapse spaces
        $desc = trim(preg_replace('/\s+/', ' ', $desc));

        // Limit to VARCHAR(50)
        return Str::limit($desc, 50, '');  // no "..." suffix
    }

    public function upload(Request $request)
    {
        $request->validate([
            'bank_id'   => ['required', 'string', 'max:18'],
            'file'      => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $bankId = $request->bank_id;
        $bank = DB::table('fashmsbank')->where('account_id', $bankId)->first();
        $importMode = $bank->import_mode ?? 'debit_credit_cols';

        if (!$bank) {
            return back()->with('error', 'Invalid bank selected.');
        }
        $bankName = $bank->account_name;

        $file       = $request->file('file');
        $original   = $file->getClientOriginalName();

        // Store file in storage/app/bank_imports/...
        $path = $file->storeAs('bank_imports', time() . '_' . $original);

        $fullPath = Storage::path($path);

        if (!file_exists($fullPath)) {
            return back()->with('error', 'Uploaded file not found on server.');
        }

        $handle = fopen($fullPath, 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to open uploaded file.');
        }

        // Read header row
        $headerRow = fgetcsv($handle);
        if (!$headerRow) {
            fclose($handle);
            return back()->with('error', 'CSV file appears to be empty.');
        }

        // Clean header names
        $headers = array_map(function ($h) {
            return strtolower(trim($h));
        }, $headerRow);

        // Map columns (you can tweak keywords based on your banks)
        $dateIndex      = $this->findColumnIndex($headers, ['date', 'txn date', 'transaction date', 'posting date']);
        $descIndex      = $this->findColumnIndex($headers, ['description', 'details', 'narration', 'memo']);
        $debitIndex     = $this->findColumnIndex($headers, ['debit', 'withdrawal', 'withdrawls', 'debitamt', 'amount out']);
        $creditIndex    = $this->findColumnIndex($headers, ['credit', 'deposit', 'creditamt', 'amount in']);
        $amountIndex    = $this->findColumnIndex($headers, ['amount', 'amt', 'transaction amount', 'amt.']);
        $categoryIndex  = $this->findColumnIndex($headers, ['category', 'type', 'bank category']);

      // Must have at least date + description
        if ($dateIndex === null || $descIndex === null) {
            fclose($handle);
            return back()->with('error', 'Could not auto-detect date and/or description columns in the CSV.');
        }

        // Must have either (debit/credit) OR amount
        if ($debitIndex === null && $creditIndex === null && $amountIndex === null) {
            fclose($handle);
            return back()->with('error', 'Could not auto-detect debit/credit or amount column in the CSV.');
        }

        $inserted = 0;
        $skipped  = 0;
        $rowNumber = 1; // header row

        // Read each remaining line
        while (($row = fgetcsv($handle)) !== false) {
             $rowNumber++;
            // Skip completely empty rows
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $rawDate     = $row[$dateIndex] ?? null;
            $description = $row[$descIndex] ?? null;

            // Bank-sent category (if present)
            $bankCategory = null;
            if ($categoryIndex !== null) {
                $bankCategory = trim($row[$categoryIndex] ?? '');
            }

            // Parse date flexibly
            $txnDate = $this->parseDate($rawDate);

            // Raw values
            $rawDebit  = null;
            $rawCredit = null;
            $rawAmount = null;

            $debit  = 0.0;   // normalized
            $credit = 0.0;   // normalized

            // Case 1: file has explicit Debit/Credit columns
            if ($debitIndex !== null || $creditIndex !== null) {
                if ($debitIndex !== null) {
                    $rawDebit = $this->parseAmount($row[$debitIndex] ?? null);
                }
                if ($creditIndex !== null) {
                    $rawCredit = $this->parseAmount($row[$creditIndex] ?? null);
                }

                // Normalize: always positive numbers
                $debit  = max(0, (float)$rawDebit);
                $credit = max(0, (float)$rawCredit);
            }
            // Case 2: single Amount column with sign
            elseif ($amountIndex !== null) {
                $rawAmount = $this->parseAmount($row[$amountIndex] ?? null);

                if ($importMode === 'amount_neg_debit') {
                    // Negative = debit (out), positive = credit (in)
                    if ($rawAmount < 0) {
                        $debit  = abs($rawAmount);
                        $credit = 0.0;
                    } elseif ($rawAmount > 0) {
                        $debit  = 0.0;
                        $credit = $rawAmount;
                    }
                } elseif ($importMode === 'amount_neg_credit') {
                    // Negative = credit (in), positive = debit (out)
                    if ($rawAmount < 0) {
                        $debit  = 0.0;
                        $credit = abs($rawAmount);
                    } elseif ($rawAmount > 0) {
                        $debit  = $rawAmount;
                        $credit = 0.0;
                    }
                } else {
                    // Fallback: treat like amount_neg_debit
                    if ($rawAmount < 0) {
                        $debit  = abs($rawAmount);
                        $credit = 0.0;
                    } elseif ($rawAmount > 0) {
                        $debit  = 0.0;
                        $credit = $rawAmount;
                    }
                }
            }

            // Skip if both are zero
            if ($debit == 0 && $credit == 0) {
                continue;
            }

            // 1) normalize description
            $normalizedDesc = $this->normalizeDescription($description);

            // 2) calculate fingerprint using normalized description + normalized amounts
            $fingerprint = $this->makeFingerprint(
                $bankId,
                $txnDate,
                $normalizedDesc,
                $debit,
                $credit,
                $original,
                $rowNumber
            );

            // 3) compute amount and find rule
            $amountOut = (float)($debit  ?? 0);
            $amountIn  = (float)($credit ?? 0);
            $netAmount = $amountIn - $amountOut;   // >0 credit, <0 debit

            $matchedRule      = $this->findMatchingRule($bankId, $normalizedDesc, $netAmount, $bankCategory ?? null);
            $suggestedPartyCd = $matchedRule?->party_cd;

            if ($matchedRule) {
                Log::info('Matched rule in upload', [
                    'rule_id'       => $matchedRule->id,
                    'party_cd'      => $matchedRule->party_cd,
                    'rule_category' => $matchedRule->bank_category,
                    'row_category'  => $bankCategory,
                    'desc'          => $normalizedDesc,
                    'amount'        => $netAmount,
                ]);
            }

            /*
            Log::info('Bank import fingerprint debug', [
                'rowNumber' => $rowNumber,
                'bankId' => $bankId,
                'txnDate' => $txnDate,
                'description' => $normalizedDesc,
                'debit' => $debit,
                'credit' => $credit,
                'fingerprint' => $fingerprint,
            ]);
            */

            // Insert into bank_imports (normalized + raw)
            $result = BankImport::insertOrIgnore([
                'fingerprint'       => $fingerprint,
                'bank_id'           => $bankId,
                'bank_name'         => $bankName,
                'source_file'       => $original,
                'txn_date'          => $txnDate,
                'description'       => $normalizedDesc,

                'debit'             => $debit,
                'credit'            => $credit,

                'raw_debit'         => $rawDebit,
                'raw_credit'        => $rawCredit,
                'raw_amount'        => $rawAmount,
                'bank_category'     => $bankCategory,

                'suggested_party_cd'=> $suggestedPartyCd,
                'status'            => 'new',    // for new imports
                'is_posted'         => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            if ($result) {
                $inserted++;
            } else {
                $skipped++;
            }
        }

        fclose($handle);

        return redirect()
            ->route('bankfeed.import.form')
            ->with('message', "CSV processed. Imported {$inserted} rows, skipped {$skipped} duplicates.");

    } // End Method

    /**
     * Try to find a column index whose header contains any of the given keywords.
     */
    protected function findColumnIndex(array $headers, array $keywords): ?int
    {
        foreach ($headers as $index => $header) {
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($header, strtolower($keyword))) {
                    return $index;
                }
            }
        }
        return null;

    } // End Method

    /**
     * Check if a CSV row is effectively empty.
     */
    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string)$cell) !== '') {
                return false;
            }
        }
        return true;

    } // End Method

    /**
     * Parse various date formats into Y-m-d (for DB date).
     */
    protected function parseDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        // Try a few common formats
        $formats = [
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'm-d-Y',
            'd-m-Y',
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $value);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                // try next format
            }
        }

        // Fallback: let Carbon try to parse
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }

    } // End Method

    /**
     * Clean amount string and return numeric value.
     */
    protected function parseAmount($value): float
    {
        if ($value === null) {
            return 0.0;
        }

        $value = trim((string)$value);

        if ($value === '') {
            return 0.0;
        }

        // Remove commas, currency symbols, etc.
        $value = str_replace([',', '$'], '', $value);

        // Handle negative in parentheses (e.g., (123.45))
        if (preg_match('/^\((.*)\)$/', $value, $m)) {
            $value = '-' . $m[1];
        }

        return (float)$value;

    } // End Method

    public function postSelected(Request $request)
    {
        $selectedIds = $request->input('selected_rows', []);

        if (empty($selectedIds)) {
            return $this->jsonOrRedirect(
                $request,
                false,
                'No rows selected to post.'
            );
        }

        /*
        * Process selected transactions in ascending transaction-date order.
        * ID is used as the secondary sort for rows having the same date.
        */
        $imports = BankImport::whereIn('id', $selectedIds)
            ->where('status', 'new')
            ->orderBy('txn_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($imports->isEmpty()) {
            return $this->jsonOrRedirect(
                $request,
                false,
                'No unposted rows found for selection.'
            );
        }

        $companyId   = 'DS02';
        $defaultUser = '1001';

        /*
        * Arrays received from the Blade form:
        *
        * party_cd[row_id]
        * remarks[row_id]
        */
        $partyMap   = $request->input('party_cd', []);
        $remarksMap = $request->input('remarks', []);

        DB::beginTransaction();

        try {
            $postedCount = 0;

            /*
            * Obtain the first available transaction number only once.
            * It will be incremented after each successfully posted import.
            */
            $nextTransNo = (DB::table('fashtrans')->max('trans_no') ?? 0) + 1;

            foreach ($imports as $import) {

                $partyCd = is_array($partyMap)
                    ? ($partyMap[$import->id] ?? null)
                    : null;

                /*
                * Do not post rows without a selected party code.
                */
                if (!$partyCd) {
                    continue;
                }

                $amountOut = (float) ($import->debit ?? 0);
                $amountIn  = (float) ($import->credit ?? 0);

                /*
                * Do not post zero-value transactions.
                */
                if ($amountOut == 0 && $amountIn == 0) {
                    continue;
                }

                $bankCd = $import->bank_id;

                if (!$bankCd) {
                    continue;
                }

                /*
                * Determine transaction type.
                *
                * Debit amount  > 0 = Payment
                * Credit amount > 0 = Deposit
                */
                if ($amountOut > 0) {
                    $transType = 'PAYM';
                    $amt       = $amountOut;
                } else {
                    $transType = 'DEPS';
                    $amt       = $amountIn;
                }

                $transDt = $import->txn_date
                    ? Carbon::parse($import->txn_date)->format('Y-m-d') . ' 00:00:00'
                    : now();

                $refNo = (string) $import->id;

                $payToName = Str::limit(
                    $import->description ?? '',
                    50,
                    ''
                );

                $remarks = Str::limit(
                    $remarksMap[$import->id]
                        ?? ($import->description ?? 'Bank Feed'),
                    50,
                    ''
                );

                /*
                * PAYMENT
                *
                * Line 000: Debit expense/vendor account
                * Line 101: Credit bank account
                */
                if ($transType === 'PAYM') {

                    DB::table('fashtrans')->insert([
                        'trans_no'     => $nextTransNo,
                        'trans_dt'     => $transDt,
                        'serial_no'    => '000',
                        'trans_type'   => 'PAYM',
                        'party_cd'     => $partyCd,
                        'bank_id'      => $bankCd,
                        'debit_amt'    => $amt,
                        'credit_amt'   => 0,
                        'account_flag' => 'E',
                        'payto_name'   => $payToName,
                        'check_no'     => null,
                        'check_dt'     => null,
                        'clear_flag'   => 'N',
                        'post_flag'    => 'U',
                        'trans_flag'   => 'A',
                        'remarks'      => $remarks,
                        'ref_no'       => $refNo,
                        'company_id'   => $companyId,
                        'user_cd'      => $defaultUser,
                        'update_dt'    => now(),
                    ]);

                    DB::table('fashtrans')->insert([
                        'trans_no'     => $nextTransNo,
                        'trans_dt'     => $transDt,
                        'serial_no'    => '101',
                        'trans_type'   => 'PAYM',
                        'party_cd'     => $bankCd,
                        'bank_id'      => $partyCd,
                        'debit_amt'    => 0,
                        'credit_amt'   => $amt,
                        'account_flag' => 'B',
                        'payto_name'   => $payToName,
                        'check_no'     => null,
                        'check_dt'     => null,
                        'clear_flag'   => 'N',
                        'post_flag'    => 'U',
                        'trans_flag'   => 'A',
                        'remarks'      => $remarks,
                        'ref_no'       => $refNo,
                        'company_id'   => $companyId,
                        'user_cd'      => $defaultUser,
                        'update_dt'    => now(),
                    ]);
                }

                /*
                * DEPOSIT
                *
                * Line 000: Credit customer/income account
                * Line 101: Debit bank account
                */
                if ($transType === 'DEPS') {

                    DB::table('fashtrans')->insert([
                        'trans_no'     => $nextTransNo,
                        'trans_dt'     => $transDt,
                        'serial_no'    => '000',
                        'trans_type'   => 'DEPS',
                        'party_cd'     => $partyCd,
                        'bank_id'      => $bankCd,
                        'debit_amt'    => 0,
                        'credit_amt'   => $amt,
                        'account_flag' => 'C',
                        'payto_name'   => $payToName,
                        'check_no'     => null,
                        'check_dt'     => null,
                        'clear_flag'   => 'N',
                        'post_flag'    => 'U',
                        'trans_flag'   => 'A',
                        'remarks'      => $remarks,
                        'ref_no'       => $refNo,
                        'company_id'   => $companyId,
                        'user_cd'      => $defaultUser,
                        'update_dt'    => now(),
                    ]);

                    DB::table('fashtrans')->insert([
                        'trans_no'     => $nextTransNo,
                        'trans_dt'     => $transDt,
                        'serial_no'    => '101',
                        'trans_type'   => 'DEPS',
                        'party_cd'     => $bankCd,
                        'bank_id'      => $partyCd,
                        'debit_amt'    => $amt,
                        'credit_amt'   => 0,
                        'account_flag' => 'B',
                        'payto_name'   => $payToName,
                        'check_no'     => null,
                        'check_dt'     => null,
                        'clear_flag'   => 'N',
                        'post_flag'    => 'U',
                        'trans_flag'   => 'A',
                        'remarks'      => $remarks,
                        'ref_no'       => $refNo,
                        'company_id'   => $companyId,
                        'user_cd'      => $defaultUser,
                        'update_dt'    => now(),
                    ]);
                }

                /*
                * Mark staging row as posted only after both fashtrans
                * accounting lines have been inserted successfully.
                */
                $import->status    = 'posted';
                $import->posted_at = now();
                $import->posted_by = $defaultUser;
                $import->is_posted = 1;
                $import->save();

                $postedCount++;

                /*
                * Move to the next transaction number only after this
                * complete transaction has been saved.
                */
                $nextTransNo++;
            }

            if ($postedCount === 0) {
                DB::rollBack();

                return $this->jsonOrRedirect(
                    $request,
                    false,
                    'No rows were posted. Please check party codes and transaction amounts.'
                );
            }

            DB::commit();

            return $this->jsonOrRedirect(
                $request,
                true,
                "Posted {$postedCount} transaction(s) successfully."
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Bank feed bulk posting error', [
                'message'      => $e->getMessage(),
                'selected_ids' => $selectedIds,
            ]);

            return $this->jsonOrRedirect(
                $request,
                false,
                'Error while posting: ' . $e->getMessage()
            );
        }

    } // End Method

    // Helper: return JSON for AJAX, redirect for non-AJAX
    protected function jsonOrRedirect(Request $request, bool $success, string $message)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $success ? 200 : 400);
        }

        return back()->with($success ? 'message' : 'error', $message);

    } // End Method

    public function postOne(Request $request, $id)
    {
        $partyCd     = $request->input('party_cd');
        $description = $request->input('description');
        $userRemarks = $request->input('remarks');

        $import = BankImport::where('id', $id)
            ->where('status', 'new')
            ->where(function ($query) {
                $query->whereNull('is_posted')
                    ->orWhere('is_posted', false);
            })
            ->first();

        if (!$import) {
            return $this->jsonOrRedirect(
                $request,
                false,
                'Selected row was not found or has already been posted.'
            );
        }

        $amountOut = (float) ($import->debit ?? 0);
        $amountIn  = (float) ($import->credit ?? 0);

        if ($amountOut == 0 && $amountIn == 0) {
            return $this->jsonOrRedirect(
                $request,
                false,
                'Transaction amount is zero.'
            );
        }

        $bankCd = $import->bank_id;

        if (!$bankCd || !$partyCd) {
            return $this->jsonOrRedirect(
                $request,
                false,
                'Missing bank or party account information.'
            );
        }

        if ($amountOut > 0) {
            $transType = 'PAYM';
            $amt       = $amountOut;
        } else {
            $transType = 'DEPS';
            $amt       = $amountIn;
        }

        $companyId   = 'DS02';
        $defaultUser = '1001';

        DB::beginTransaction();

        try {
            $nextTransNo = (DB::table('fashtrans')->max('trans_no') ?? 0) + 1;

            $transDt = $import->txn_date
                ? Carbon::parse($import->txn_date)->format('Y-m-d') . ' 00:00:00'
                : now();

            /*
            * The current process uses the bank_imports ID as ref_no.
            */
            $refNo = (string) $import->id;

            $payToName = Str::limit(
                $description ?: ($import->description ?? ''),
                50,
                ''
            );

            $remarks = Str::limit(
                $userRemarks ?: ($import->description ?? 'Bank Feed'),
                50,
                ''
            );

            /*
            * PAYMENT
            */
            if ($transType === 'PAYM') {

                DB::table('fashtrans')->insert([
                    'trans_no'     => $nextTransNo,
                    'trans_dt'     => $transDt,
                    'serial_no'    => '000',
                    'trans_type'   => 'PAYM',
                    'party_cd'     => $partyCd,
                    'bank_id'      => $bankCd,
                    'debit_amt'    => $amt,
                    'credit_amt'   => 0,
                    'account_flag' => 'E',
                    'payto_name'   => $payToName,
                    'check_no'     => null,
                    'check_dt'     => null,
                    'clear_flag'   => 'N',
                    'post_flag'    => 'U',
                    'trans_flag'   => 'A',
                    'remarks'      => $remarks,
                    'ref_no'       => $refNo,
                    'company_id'   => $companyId,
                    'user_cd'      => $defaultUser,
                    'update_dt'    => now(),
                ]);

                DB::table('fashtrans')->insert([
                    'trans_no'     => $nextTransNo,
                    'trans_dt'     => $transDt,
                    'serial_no'    => '101',
                    'trans_type'   => 'PAYM',
                    'party_cd'     => $bankCd,
                    'bank_id'      => $partyCd,
                    'debit_amt'    => 0,
                    'credit_amt'   => $amt,
                    'account_flag' => 'B',
                    'payto_name'   => $payToName,
                    'check_no'     => null,
                    'check_dt'     => null,
                    'clear_flag'   => 'N',
                    'post_flag'    => 'U',
                    'trans_flag'   => 'A',
                    'remarks'      => $remarks,
                    'ref_no'       => $refNo,
                    'company_id'   => $companyId,
                    'user_cd'      => $defaultUser,
                    'update_dt'    => now(),
                ]);
            }

            /*
            * DEPOSIT
            */
            if ($transType === 'DEPS') {

                DB::table('fashtrans')->insert([
                    'trans_no'     => $nextTransNo,
                    'trans_dt'     => $transDt,
                    'serial_no'    => '000',
                    'trans_type'   => 'DEPS',
                    'party_cd'     => $partyCd,
                    'bank_id'      => $bankCd,
                    'debit_amt'    => 0,
                    'credit_amt'   => $amt,
                    'account_flag' => 'C',
                    'payto_name'   => $payToName,
                    'check_no'     => null,
                    'check_dt'     => null,
                    'clear_flag'   => 'N',
                    'post_flag'    => 'U',
                    'trans_flag'   => 'A',
                    'remarks'      => $remarks,
                    'ref_no'       => $refNo,
                    'company_id'   => $companyId,
                    'user_cd'      => $defaultUser,
                    'update_dt'    => now(),
                ]);

                DB::table('fashtrans')->insert([
                    'trans_no'     => $nextTransNo,
                    'trans_dt'     => $transDt,
                    'serial_no'    => '101',
                    'trans_type'   => 'DEPS',
                    'party_cd'     => $bankCd,
                    'bank_id'      => $partyCd,
                    'debit_amt'    => $amt,
                    'credit_amt'   => 0,
                    'account_flag' => 'B',
                    'payto_name'   => $payToName,
                    'check_no'     => null,
                    'check_dt'     => null,
                    'clear_flag'   => 'N',
                    'post_flag'    => 'U',
                    'trans_flag'   => 'A',
                    'remarks'      => $remarks,
                    'ref_no'       => $refNo,
                    'company_id'   => $companyId,
                    'user_cd'      => $defaultUser,
                    'update_dt'    => now(),
                ]);
            }

            $import->status    = 'posted';
            $import->posted_at = now();
            $import->posted_by = $defaultUser;
            $import->is_posted = 1;
            $import->save();

            DB::commit();

            return $this->jsonOrRedirect(
                $request,
                true,
                'Transaction posted successfully.'
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Bank feed single posting error', [
                'bank_import_id' => $id,
                'message'        => $e->getMessage(),
            ]);

            return $this->jsonOrRedirect(
                $request,
                false,
                'Error while posting: ' . $e->getMessage()
            );
        }

    } // End Method

    /**
     * Find the best matching bank rule for a given bank + description + amount.
     *
     * @param string|null $bankId
     * @param string      $normalizedDesc  Already normalized description (uppercase, trimmed, etc.)
     * @param float       $amount          credit - debit (>0 = credit, <0 = debit)
     * @return \App\Models\BankRule|null
     */
    private function findMatchingRule(?string $bankId, string $normalizedDesc, float $amount, ?string $importCategory = null  ): ?BankRule
        {
            // Determine direction from the amount
            $direction = $amount < 0 ? 'D' : ($amount > 0 ? 'C' : 'Any');
            $haystack  = strtoupper($normalizedDesc);

            // Load active rules for this bank (bank-specific OR global)
            $rules = BankRule::where('is_active', 1)
                ->where(function ($q) use ($bankId) {
                    $q->whereNull('bank_id');
                    if ($bankId) {
                        $q->orWhere('bank_id', $bankId);
                    }
                })
                ->orderBy('priority')   // lower number = higher priority
                ->orderBy('id')
                ->get();

            foreach ($rules as $rule) {

                // 1️⃣ OPTIONAL CATEGORY FILTER (NEW)
                // If the rule has a bank_category set, only match imports with the same category.
                if (!empty($rule->bank_category)) {
                    // If this row has no category, or it differs, skip the rule
                    if (empty($importCategory) ||
                        strtolower($rule->bank_category) !== strtolower($importCategory)) {
                        continue;
                    }
                }
                // Direction filter
                if ($rule->direction === 'D' && $direction !== 'D') {
                    continue;
                }
                if ($rule->direction === 'C' && $direction !== 'C') {
                    continue;
                }


                // 'Any' or NULL means no direction restriction

                // Pattern and type
                $patternType = $rule->pattern_type ?: 'contains';

                // Prefer pattern_value, fallback to legacy pattern
                $pattern = strtoupper(trim($rule->pattern_value ?? $rule->pattern ?? ''));
                if ($pattern === '') {
                    continue;
                }

                $isMatch = false;

                switch ($patternType) {
                    case 'starts_with':
                        $isMatch = \Illuminate\Support\Str::startsWith($haystack, $pattern);
                        break;

                    case 'ends_with':
                        $isMatch = \Illuminate\Support\Str::endsWith($haystack, $pattern);
                        break;

                    case 'equals':
                        $isMatch = ($haystack === $pattern);
                        break;

                    case 'regex':
                        // use original (non-uppercased) string for regex
                        $regex = $rule->pattern_value ?? $rule->pattern;
                        if ($regex && @preg_match($regex, $normalizedDesc) !== false) {
                            $isMatch = (bool)preg_match($regex, $normalizedDesc);
                        }
                        break;

                    case 'contains':
                    default:
                        $isMatch = \Illuminate\Support\Str::contains($haystack, $pattern);
                        break;
                }

                if ($isMatch) {
                    return $rule;    // first match wins (by priority + id)
                }
            }

            return null;

    } //End Method

    public function quickAdd(Request $request)
    {
        $data = $request->validate([
            'party_cd'     => ['required', 'string', 'max:18'],
            'party_nm'     => ['required', 'string', 'max:100'],
            'gl_id'        => ['required', 'integer'],
        ]);

        // Check if already exists
        $exists = DB::table('accountmasters')
            ->where('party_cd', $data['party_cd'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Account code already exists.',
            ], 422);
        }

        // Minimal required fields
             DB::table('accountmasters')->insert([
            'party_cd'     => $data['party_cd'],
            'party_nm'     => $data['party_nm'],
            'party_type' => $data['account_type'], // I/E/V/C/A/L
            'trans_flag'   => 'A',                   // as per your requirement
            'gl_id'        => $data['gl_id'],        // FK to gl master

        ]);

        return response()->json([
            'success' => true,
            'account' => [
                'party_cd'   => $data['party_cd'],
                'party_nm'   => $data['party_nm'],
                'party_type' => $data['account_type'],
            ],
        ]);

    } //End Method

    public function applyRulesToUnposted()
    {
        $imports = BankImport::whereNull('is_posted')
            ->whereNull('suggested_party_cd')
            ->get();

        foreach ($imports as $import) {
            $desc      = $this->normalizeDescription($import->description);

            $amountOut = (float)($import->debit  ?? 0);
            $amountIn  = (float)($import->credit ?? 0);
            $amount    = $amountIn - $amountOut;

            $rule      = $this->findMatchingRule($import->bank_id, $desc, $amount, $import->bank_category ?? null);
            $suggested = $rule?->party_cd;

            if ($suggested) {
                $import->description        = $desc;
                $import->suggested_party_cd = $suggested;
                $import->save();
            }
        }

        return back()->with('message', 'Rules applied to unposted imports.');

    } // End Method


    public function storeFromTransaction(Request $request)
    {
        // Validate the incoming data from the Learn modal
        $data = $request->validate([
            'bank_id'       => ['nullable', 'integer'],
            'pattern_type'  => ['required', 'in:starts_with,ends_with,contains,regex,equals'],
            'pattern_value' => ['required', 'string'],
            'direction'     => ['required', 'in:D,C,Any'],
            'party_cd'      => ['nullable', 'string', 'max:20'],   // adjust length to your schema
            'priority'      => ['nullable', 'integer'],
        ]);

        // Default priority if not given
        $priority = $data['priority'] ?? 100;

        // Insert into bank_rules table
        // Make sure your bank_rules table has these columns,
        // or adjust names accordingly.
        $ruleId = DB::table('bank_rules')->insertGetId([
            'bank_id'       => $data['bank_id'] ?: null,
            'pattern_type'  => $data['pattern_type'],
            'pattern_value' => $data['pattern_value'],
            'pattern'       => $data['pattern_value'],   // <- add this line for old column
            'direction'     => $data['direction'],
            'party_cd'      => $data['party_cd'] ?: null,   // <- auto-filled Party Code
            'priority'      => $priority,
            'is_active'     => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // JSON response for the JS fetch()
        return response()->json([
            'status'  => 'ok',
            'rule_id' => $ruleId,
            'message' => 'Rule created successfully.',
        ]);

    } //End Method

    public function ignoreSelected(Request $request)
    {
        $ids = $request->input('selected_rows', []);

        if (empty($ids)) {
            return back()->with([
                'message'    => 'No transactions selected to ignore.',
                'alert-type' => 'warning',
            ]);
        }

        $defaultUser = 'BF'; // or auth()->user()->user_cd

        $updated = DB::table('bank_imports')
                    ->whereIn('id', $ids)
                    ->where('status', 'new')   // don't touch posted/ignored
                    ->update([
                        'status'     => 'ignored',
                        'ignored_at' => now(),
                        'ignored_by' => $defaultUser,
                    ]);

            if ($updated === 0) {

            return $this->jsonOrRedirect($request, false, 'No NEW rows were ignored (they may already be posted or ignored).');
            }

            return $this->jsonOrRedirect($request, true, "Ignored {$updated} transaction(s) successfully.");

    } // End Method
}



