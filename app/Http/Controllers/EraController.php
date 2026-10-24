<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class EraController extends Controller
{
    public function showUploadForm()
    {
        return view('era.era_upload');

    } // End Method

    // public function processUpload(Request $request)
    // {
    //     $request->validate([
    //         'era_file' => ['required', 'file'],
    //     ]);

    //     $file     = $request->file('era_file');
    //     $fileName = $file->getClientOriginalName();
    //     $raw      = file_get_contents($file->getRealPath());

    //     // Normalize line endings (835 uses ~ as segment terminator)
    //     $raw = str_replace(["\r\n", "\n\r", "\n"], "\r", $raw);

    //     DB::beginTransaction();

    //     try {
    //         // 1) Save ERA header record
    //         $eraFileId = DB::table('era_files')->insertGetId([
    //             'file_name'   => $fileName,
    //             'raw_content' => $raw,
    //             'created_at'  => now(),
    //             'updated_at'  => now(),
    //         ]);

    //         $segments = preg_split('/~\s*/', $raw);

    //         $payerName    = null;
    //         $paymentDate  = null;
    //         $paymentAmt   = null;
    //         $traceNumber  = null;

    //         $insertedClaims = 0;
    //         $updatedLines   = 0;

    //         foreach ($segments as $segRaw) {
    //             $segRaw = trim($segRaw);
    //             if ($segRaw === '') {
    //                 continue;
    //             }

    //             $parts = explode('*', $segRaw);
    //             $tag   = strtoupper($parts[0] ?? '');

    //             /* ---------- HEADER-LEVEL ---------- */

    //             if ($tag === 'BPR') {
    //                 // BPR*I*473.30*C*CHK************20251130
    //                 $paymentAmt = isset($parts[2]) ? (float) $parts[2] : null;
    //                  // PAYMENT DATE fallback: last element of BPR if numeric YYYYMMDD
    //                 $last = end($parts);
    //                 if ($last && preg_match('/^\d{8}$/', $last)) {
    //                     $paymentDate = substr($last, 0, 4) . '-' .
    //                                 substr($last, 4, 2) . '-' .
    //                                 substr($last, 6, 2);
    //                 }
    //             }

    //             if ($tag === 'TRN') {
    //                 // TRN*1*TRACE_NUMBER*...
    //                 $traceNumber = $parts[2] ?? null;
    //             }

    //             if ($tag === 'N1' && isset($parts[1]) && strtoupper($parts[1]) === 'PR') {
    //                 // N1*PR*PAYER NAME
    //                 $payerName = $parts[2] ?? null;
    //             }

    //             if ($tag === 'DTM' && isset($parts[1]) && $parts[1] === '405') {
    //                 // DTM*405*YYYYMMDD
    //                 $dateStr = $parts[2] ?? null;
    //                 if ($dateStr && strlen($dateStr) === 8) {
    //                     $paymentDate = substr($dateStr, 0, 4) . '-' .
    //                                    substr($dateStr, 4, 2) . '-' .
    //                                    substr($dateStr, 6, 2);
    //                 }
    //             }

    //             /* ---------- CLAIM-LEVEL: CLP ---------- */

    //             if ($tag === 'CLP') {
    //                 // CLP*<patient control>*<status>*<total charge>*<total paid>*<pat resp>*...*<payer claim #>
    //                 $patientControl = $parts[1] ?? null;           // CLP01 = your claim_no
    //                 $claimStatus    = $parts[2] ?? null;           // CLP02
    //                 $totalCharge    = isset($parts[3]) ? (float) $parts[3] : null; // CLP03
    //                 $totalPaid      = isset($parts[4]) ? (float) $parts[4] : null; // CLP04
    //                 $patResp        = isset($parts[5]) ? (float) $parts[5] : null; // CLP05
    //                 $payerClaimNum  = $parts[7] ?? null;           // CLP07

    //                 if (!$patientControl) {
    //                     continue;
    //                 }

    //                 // If your ERA sends unpadded "721" but DB has "000721", uncomment:
    //                 // $patientControl = str_pad($patientControl, 6, '0', STR_PAD_LEFT);

    //                 $participant = DB::table('claims')
    //                     ->where('claim_no', $patientControl)
    //                     ->value('ParticipantId');

    //                 // Save CLP summary in era_claims
    //                 DB::table('era_claims')->insert([
    //                     'era_file_id'            => $eraFileId,
    //                     'patient_control_number' => $patientControl,
    //                     'payer_claim_number'     => $payerClaimNum,
    //                     'claim_status_code'      => $claimStatus,
    //                     'total_charge'           => $totalCharge,
    //                     'total_paid'             => $totalPaid,
    //                     'patient_responsibility' => $patResp,
    //                     'member_id'              => $participant,
    //                     'created_at'             => now(),
    //                     'updated_at'             => now(),
    //                 ]);

    //                 $insertedClaims++;

    //                 // Update ALL claims rows with this claim_no
    //                 if ($totalPaid !== null) {
    //                     $affected = DB::table('claims')
    //                         ->where('claim_no', $patientControl)
    //                         ->update([
    //                             'ins1_paid' => $totalPaid,
    //                         ]);

    //                     $updatedLines += $affected;
    //                 }

    //                 continue;
    //             }

    //             // (We ignore SVC/CAS/MOA for now to keep it simple for users.)
    //         }

    //         // 3) Update ERA file header with summary info
    //         DB::table('era_files')
    //             ->where('id', $eraFileId)
    //             ->update([
    //                 'payer_name'     => $payerName,
    //                 'payment_date'   => $paymentDate,
    //                 'payment_amount' => $paymentAmt,
    //                 'trace_number'   => $traceNumber,
    //                 'updated_at'     => now(),
    //             ]);

    //         DB::commit();

    //         return redirect()
    //             ->route('era.upload.form')
    //             ->with(
    //                 'success',
    //                 "ERA imported. {$insertedClaims} CLP claim(s) parsed, {$updatedLines} claim row(s) updated."
    //             );

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         report($e);
    //         return back()->withErrors([
    //             'era_file' => 'Error parsing ERA: ' . $e->getMessage()
    //         ]);
    //     }

    // } // End Method

    public function index()
    {
        $files = DB::table('era_files')
            ->orderByDesc('id')
            ->get();

        // count claims per ERA file (no JOIN)
        $claimsCounts = DB::table('era_claims')
            ->select('era_file_id', DB::raw('COUNT(*) AS cnt'))
            ->groupBy('era_file_id')
            ->pluck('cnt', 'era_file_id');

        return view('era.era_index', compact('files', 'claimsCounts'));
    } // End Method


    // public function processUploadNew(Request $request)
    // {
    // $request->validate([
    //     'era_file' => ['required', 'file'],
    // ]);

    // $file = $request->file('era_file');

    // $fileName = $file->getClientOriginalName();

    // $raw = file_get_contents(
    //     $file->getRealPath()
    // );

    // /*
    // |--------------------------------------------------------------------------
    // | Normalize Line Endings
    // |--------------------------------------------------------------------------
    // */

    // $raw = str_replace(
    //     ["\r\n", "\n\r", "\n"],
    //     "\r",
    //     $raw
    // );

    public function processUploadNew(Request $request,  ?string $rawOverride = null, ?string $fileNameOverride = null)
    {
        /*
    |--------------------------------------------------------------------------
    | Determine ERA Source
    |--------------------------------------------------------------------------
    */

        if ($rawOverride !== null) {

            /*
        |--------------------------------------------------------------------------
        | Claim.MD ERA
        |--------------------------------------------------------------------------
        |
        | Physical file was already saved by claimMdEraProcess().
        | DO NOT save it again here.
        |
        */

            $raw =
                $rawOverride;

            $fileName =
                $fileNameOverride
                ?: 'ClaimMD_ERA.835';
        } else {

            /*
        |--------------------------------------------------------------------------
        | Manual ERA Upload
        |--------------------------------------------------------------------------
        */

            $request->validate([
                'era_file' => [
                    'required',
                    'file',
                ],
            ]);


            $file =
                $request->file(
                    'era_file'
                );


            $fileName =
                $file->getClientOriginalName();


            $raw =
                file_get_contents(
                    $file->getRealPath()
                );


            /*
        |--------------------------------------------------------------------------
        | Save Original Manual ERA File
        |--------------------------------------------------------------------------
        */

            $safeFileName =
                preg_replace(
                    '/[^A-Za-z0-9._-]/',
                    '_',
                    basename($fileName)
                );


            $storagePath =
                'era/' . $safeFileName;


            /*
        |--------------------------------------------------------------------------
        | If Same Filename Exists
        |--------------------------------------------------------------------------
        */

            if (
                Storage::disk('adc_private')
                ->exists($storagePath)
            ) {

                $existingContent =
                    Storage::disk('adc_private')
                    ->get($storagePath);


                /*
            |--------------------------------------------------------------------------
            | Exact Same Physical File
            |--------------------------------------------------------------------------
            */

                if (
                    hash('sha256', $existingContent)
                    !==
                    hash('sha256', $raw)
                ) {

                    /*
                |--------------------------------------------------------------------------
                | Same Filename But Different ERA
                |--------------------------------------------------------------------------
                */

                    $baseName =
                        pathinfo(
                            $safeFileName,
                            PATHINFO_FILENAME
                        );


                    $extension =
                        pathinfo(
                            $safeFileName,
                            PATHINFO_EXTENSION
                        );


                    $safeFileName =
                        $baseName
                        . '_'
                        . now()->format('Ymd_His')
                        . (
                            $extension !== ''
                            ? '.' . $extension
                            : ''
                        );


                    $storagePath =
                        'era/' . $safeFileName;


                    Storage::disk('adc_private')
                        ->put(
                            $storagePath,
                            $raw
                        );
                }
            } else {

                /*
            |--------------------------------------------------------------------------
            | New Physical File
            |--------------------------------------------------------------------------
            */

                Storage::disk('adc_private')
                    ->put(
                        $storagePath,
                        $raw
                    );
            }


            /*
        |--------------------------------------------------------------------------
        | Verify Manual ERA Was Stored
        |--------------------------------------------------------------------------
        */

            if (
                !Storage::disk('adc_private')
                    ->exists($storagePath)
            ) {

                throw new \RuntimeException(
                    'ERA file could not be saved to private storage.'
                );
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Normalize Line Endings
    |--------------------------------------------------------------------------
    */

        $raw = str_replace(
            [
                "\r\n",
                "\n\r",
                "\n",
            ],
            "\r",
            $raw
        );

        DB::beginTransaction();

        try {

            $segments = preg_split(
                '/~\s*/',
                $raw
            );

            $payerName   = null;
            $paymentDate = null;
            $paymentAmt  = null;
            $traceNumber = null;


            /*
            |--------------------------------------------------------------------------
            | FIRST PASS - ERA Header
            |--------------------------------------------------------------------------
            */

            foreach ($segments as $segRaw) {

                $segRaw = trim($segRaw);

                if ($segRaw === '') {
                    continue;
                }

                $parts = explode(
                    '*',
                    $segRaw
                );

                $tag = strtoupper(
                    trim(
                        (string) (
                            $parts[0]
                            ?? ''
                        )
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | BPR
                |--------------------------------------------------------------------------
                */

                if ($tag === 'BPR') {

                    $paymentAmt =
                        isset($parts[2])
                        && $parts[2] !== ''
                        ? (float) $parts[2]
                        : null;

                    $last = end($parts);

                    if (
                        $last
                        &&
                        preg_match(
                            '/^\d{8}$/',
                            $last
                        )
                    ) {

                        $paymentDate =
                            substr($last, 0, 4)
                            .
                            '-'
                            .
                            substr($last, 4, 2)
                            .
                            '-'
                            .
                            substr($last, 6, 2);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | TRN
                |--------------------------------------------------------------------------
                */

                if ($tag === 'TRN') {

                    $traceNumber = trim(
                        (string) (
                            $parts[2]
                            ?? ''
                        )
                    );

                    if ($traceNumber === '') {
                        $traceNumber = null;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Payer
                |--------------------------------------------------------------------------
                */

                if (
                    $tag === 'N1'
                    &&
                    strtoupper(
                        trim(
                            (string) (
                                $parts[1]
                                ?? ''
                            )
                        )
                    ) === 'PR'
                ) {

                    $payerName = trim(
                        (string) (
                            $parts[2]
                            ?? ''
                        )
                    );

                    if ($payerName === '') {
                        $payerName = null;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | DTM 405
                |--------------------------------------------------------------------------
                */

                if (
                    $tag === 'DTM'
                    &&
                    trim(
                        (string) (
                            $parts[1]
                            ?? ''
                        )
                    ) === '405'
                ) {

                    $dateRaw = trim(
                        (string) (
                            $parts[2]
                            ?? ''
                        )
                    );

                    if (
                        preg_match(
                            '/^\d{8}$/',
                            $dateRaw
                        )
                    ) {

                        $paymentDate =
                            substr($dateRaw, 0, 4)
                            .
                            '-'
                            .
                            substr($dateRaw, 4, 2)
                            .
                            '-'
                            .
                            substr($dateRaw, 6, 2);
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Duplicate Check
            |--------------------------------------------------------------------------
            */

            $duplicate = false;

            if ($traceNumber) {

                $duplicate = DB::table('era_files')
                    ->where(
                        'trace_number',
                        $traceNumber
                    )
                    ->exists();
            }


            /*
            |--------------------------------------------------------------------------
            | Save ERA File
            |--------------------------------------------------------------------------
            */

            $eraFileId = DB::table('era_files')
                ->insertGetId([

                    'file_name' =>
                    $fileName,

                    'raw_content' =>
                    $raw,

                    'status' =>
                    'ready',

                    'trace_number' =>
                    $traceNumber,

                    'payer_name' =>
                    $payerName,

                    'payment_date' =>
                    $paymentDate,

                    'payment_amount' =>
                    $paymentAmt,

                    'possible_duplicate' =>
                    $duplicate,

                    'status_message' =>
                    $duplicate
                        ? 'Possible duplicate – same TRN as another ERA'
                        : null,

                    'created_at' =>
                    now(),

                    'updated_at' =>
                    now(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | SECOND PASS
            |--------------------------------------------------------------------------
            */

            $insertedClaims    = 0;

            $currentEraClaimId = null;

            $currentSvcSeq     = 0;

            $inServiceLoop     = false;


            foreach ($segments as $segRaw) {

                $segRaw = trim($segRaw);

                if ($segRaw === '') {
                    continue;
                }

                $parts = explode(
                    '*',
                    $segRaw
                );

                $tag = strtoupper(
                    trim(
                        (string) (
                            $parts[0]
                            ?? ''
                        )
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | CLP - Claim
                |--------------------------------------------------------------------------
                */

                if ($tag === 'CLP') {

                    $currentSvcSeq =
                        0;

                    $inServiceLoop =
                        false;


                    $patientControl = trim(
                        (string) (
                            $parts[1]
                            ?? ''
                        )
                    );


                    $claimStatus = trim(
                        (string) (
                            $parts[2]
                            ?? ''
                        )
                    );


                    $totalCharge =
                        isset($parts[3])
                        && $parts[3] !== ''
                        ? (float) $parts[3]
                        : null;


                    $totalPaid =
                        isset($parts[4])
                        && $parts[4] !== ''
                        ? (float) $parts[4]
                        : null;


                    $patResp =
                        isset($parts[5])
                        && $parts[5] !== ''
                        ? (float) $parts[5]
                        : null;


                    $payerClaimNum = trim(
                        (string) (
                            $parts[7]
                            ?? ''
                        )
                    );


                    if ($patientControl === '') {

                        $currentEraClaimId =
                            null;

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Normalize Claim Number For ADC Lookup
                    |--------------------------------------------------------------------------
                    |
                    | ERA: 17237
                    | ADC: 017237
                    |--------------------------------------------------------------------------
                    */

                    $claimNoForLookup =
                        $patientControl;


                    if (
                        ctype_digit(
                            $claimNoForLookup
                        )
                    ) {

                        $claimNoForLookup =
                            str_pad(
                                $claimNoForLookup,
                                6,
                                '0',
                                STR_PAD_LEFT
                            );
                    }


                    $participant =
                        DB::table('claims')
                        ->where(
                            'claim_no',
                            $claimNoForLookup
                        )
                        ->value(
                            'ParticipantId'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Save ERA Claim
                    |--------------------------------------------------------------------------
                    */

                    $currentEraClaimId =
                        DB::table('era_claims')
                        ->insertGetId([

                            /*
                                | Keep original value from ERA
                                */

                            'patient_control_number' =>
                            $patientControl,

                            'era_file_id' =>
                            $eraFileId,

                            'payer_claim_number' =>
                            $payerClaimNum !== ''
                                ? $payerClaimNum
                                : null,

                            'claim_status_code' =>
                            $claimStatus !== ''
                                ? $claimStatus
                                : null,

                            'total_charge' =>
                            $totalCharge,

                            'total_paid' =>
                            $totalPaid,

                            'patient_responsibility' =>
                            $patResp,

                            'member_id' =>
                            $participant,

                            'service_from' =>
                            null,

                            'service_to' =>
                            null,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);


                    $insertedClaims++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Remaining Segments Require A CLP
                |--------------------------------------------------------------------------
                */

                if (!$currentEraClaimId) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | SVC - Service Line
                |--------------------------------------------------------------------------
                */

                if ($tag === 'SVC') {

                    $currentSvcSeq++;

                    $inServiceLoop =
                        true;


                    $svc01 = trim(
                        (string) (
                            $parts[1]
                            ?? ''
                        )
                    );


                    $chargeAmount =
                        isset($parts[2])
                        && $parts[2] !== ''
                        ? (float) $parts[2]
                        : null;


                    $paidAmount =
                        isset($parts[3])
                        && $parts[3] !== ''
                        ? (float) $parts[3]
                        : null;


                    $procedureCode = null;

                    $mod1 = null;
                    $mod2 = null;
                    $mod3 = null;
                    $mod4 = null;


                    if ($svc01 !== '') {

                        $svcParts =
                            explode(
                                ':',
                                $svc01
                            );


                        $procedureCode =
                            $svcParts[1]
                            ?? null;

                        $mod1 =
                            $svcParts[2]
                            ?? null;

                        $mod2 =
                            $svcParts[3]
                            ?? null;

                        $mod3 =
                            $svcParts[4]
                            ?? null;

                        $mod4 =
                            $svcParts[5]
                            ?? null;
                    }


                    DB::table('era_service_lines')
                        ->insert([

                            'era_claim_id' =>
                            $currentEraClaimId,

                            'svc_sequence' =>
                            $currentSvcSeq,

                            'procedure_code' =>
                            $procedureCode,

                            'modifier1' =>
                            $mod1,

                            'modifier2' =>
                            $mod2,

                            'modifier3' =>
                            $mod3,

                            'modifier4' =>
                            $mod4,

                            'service_from' =>
                            null,

                            'service_to' =>
                            null,

                            'charge_amount' =>
                            $chargeAmount,

                            'paid_amount' =>
                            $paidAmount,

                            'patient_responsibility' =>
                            null,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);


                    continue;
                }

                // NEW BLOCK
                // DTM - Claim Level Service Dates
                /*
                |--------------------------------------------------------------------------
                | DTM - Claim Level Service Dates
                |--------------------------------------------------------------------------
                |
                | 232 = Claim / Statement Period Start
                | 233 = Claim / Statement Period End
                |--------------------------------------------------------------------------
                */

                if (
                    $tag === 'DTM'
                    &&
                    !$inServiceLoop
                ) {

                    $qualifier = trim(
                        (string) (
                            $parts[1]
                            ?? ''
                        )
                    );

                    $dateRaw = trim(
                        (string) (
                            $parts[2]
                            ?? ''
                        )
                    );

                    $serviceDate = null;

                    if (
                        preg_match(
                            '/^\d{8}$/',
                            $dateRaw
                        )
                    ) {

                        $serviceDate =
                            substr($dateRaw, 0, 4)
                            . '-'
                            . substr($dateRaw, 4, 2)
                            . '-'
                            . substr($dateRaw, 6, 2);
                    }

                    if ($serviceDate) {

                        if ($qualifier === '232') {

                            DB::table('era_claims')
                                ->where(
                                    'id',
                                    $currentEraClaimId
                                )
                                ->update([

                                    'service_from' =>
                                    $serviceDate,

                                    'updated_at' =>
                                    now(),

                                ]);
                        } elseif ($qualifier === '233') {

                            DB::table('era_claims')
                                ->where(
                                    'id',
                                    $currentEraClaimId
                                )
                                ->update([

                                    'service_to' =>
                                    $serviceDate,

                                    'updated_at' =>
                                    now(),

                                ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Only Skip Further Processing For 232 / 233
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $qualifier === '232'
                        ||
                        $qualifier === '233'
                    ) {
                        continue;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | DTM - Service Date
                |--------------------------------------------------------------------------
                |
                | 472 = Service Date
                | 150 = Service Period Start
                | 151 = Service Period End
                |--------------------------------------------------------------------------
                */

                if (
                    $tag === 'DTM'
                    &&
                    $inServiceLoop
                    &&
                    $currentSvcSeq > 0
                ) {

                    $qualifier = trim(
                        (string) (
                            $parts[1]
                            ?? ''
                        )
                    );


                    $dateRaw = trim(
                        (string) (
                            $parts[2]
                            ?? ''
                        )
                    );


                    $serviceDate =
                        null;


                    if (
                        preg_match(
                            '/^\d{8}$/',
                            $dateRaw
                        )
                    ) {

                        $serviceDate =
                            substr($dateRaw, 0, 4)
                            .
                            '-'
                            .
                            substr($dateRaw, 4, 2)
                            .
                            '-'
                            .
                            substr($dateRaw, 6, 2);
                    }


                    if ($serviceDate) {

                        if ($qualifier === '472') {

                            DB::table('era_service_lines')
                                ->where(
                                    'era_claim_id',
                                    $currentEraClaimId
                                )
                                ->where(
                                    'svc_sequence',
                                    $currentSvcSeq
                                )
                                ->update([

                                    'service_from' =>
                                    $serviceDate,

                                    'service_to' =>
                                    $serviceDate,

                                    'updated_at' =>
                                    now(),

                                ]);
                        } elseif (
                            $qualifier === '150'
                        ) {

                            DB::table('era_service_lines')
                                ->where(
                                    'era_claim_id',
                                    $currentEraClaimId
                                )
                                ->where(
                                    'svc_sequence',
                                    $currentSvcSeq
                                )
                                ->update([

                                    'service_from' =>
                                    $serviceDate,

                                    'updated_at' =>
                                    now(),

                                ]);
                        } elseif (
                            $qualifier === '151'
                        ) {

                            DB::table('era_service_lines')
                                ->where(
                                    'era_claim_id',
                                    $currentEraClaimId
                                )
                                ->where(
                                    'svc_sequence',
                                    $currentSvcSeq
                                )
                                ->update([

                                    'service_to' =>
                                    $serviceDate,

                                    'updated_at' =>
                                    now(),

                                ]);
                        }
                    }


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | CAS - Adjustment
                |--------------------------------------------------------------------------
                */

                if ($tag === 'CAS') {

                    $groupCode = strtoupper(
                        trim(
                            (string) (
                                $parts[1]
                                ?? ''
                            )
                        )
                    );


                    $level =
                        $inServiceLoop
                        ? 'line'
                        : 'claim';


                    $svcSeqToUse =
                        $inServiceLoop
                        ? $currentSvcSeq
                        : null;


                    /*
                    |--------------------------------------------------------------------------
                    | CAS Repeating Triplets
                    |--------------------------------------------------------------------------
                    |
                    | Reason / Amount / Quantity
                    |
                    | Quantity may be blank, but its X12 position is retained.
                    |--------------------------------------------------------------------------
                    */

                    $n =
                        count(
                            $parts
                        );


                    for (
                        $i = 2;
                        $i + 1 < $n;
                        $i += 3
                    ) {

                        $reasonCode = trim(
                            (string) (
                                $parts[$i]
                                ?? ''
                            )
                        );


                        if ($reasonCode === '') {
                            continue;
                        }


                        $amountRaw =
                            $parts[$i + 1]
                            ?? null;


                        if (
                            $amountRaw === null
                            ||
                            $amountRaw === ''
                            ||
                            !is_numeric($amountRaw)
                        ) {

                            continue;
                        }


                        $amount =
                            (float) $amountRaw;


                        $quantity =
                            null;


                        if (
                            isset(
                                $parts[$i + 2]
                            )
                            &&
                            $parts[$i + 2] !== ''
                            &&
                            is_numeric(
                                $parts[$i + 2]
                            )
                        ) {

                            $quantity =
                                (float) $parts[$i + 2];
                        }


                        DB::table('era_adjustments')
                            ->insert([

                                'era_claim_id' =>
                                $currentEraClaimId,

                                'svc_sequence' =>
                                $svcSeqToUse,

                                'level' =>
                                $level,

                                'group_code' =>
                                $groupCode !== ''
                                    ? $groupCode
                                    : null,

                                'reason_code' =>
                                $reasonCode,

                                'amount' =>
                                $amount,

                                'quantity' =>
                                $quantity,

                                'created_at' =>
                                now(),

                                'updated_at' =>
                                now(),

                            ]);
                    }


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | MOA - Claim Remark
                |--------------------------------------------------------------------------
                */

                if ($tag === 'MOA') {

                    $n =
                        count(
                            $parts
                        );


                    for (
                        $i = 3;
                        $i < $n;
                        $i++
                    ) {

                        $code = strtoupper(
                            trim(
                                (string) (
                                    $parts[$i]
                                    ?? ''
                                )
                            )
                        );


                        if ($code === '') {
                            continue;
                        }


                        if (
                            !preg_match(
                                '/^[A-Z][0-9A-Z]{1,4}$/',
                                $code
                            )
                        ) {

                            continue;
                        }


                        DB::table('era_remarks')
                            ->insert([

                                'era_claim_id' =>
                                $currentEraClaimId,

                                'svc_sequence' =>
                                null,

                                'level' =>
                                'claim',

                                'source' =>
                                'MOA',

                                'code' =>
                                $code,

                                'created_at' =>
                                now(),

                                'updated_at' =>
                                now(),

                            ]);
                    }


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | LQ - Remark
                |--------------------------------------------------------------------------
                */

                if ($tag === 'LQ') {

                    $code = strtoupper(
                        trim(
                            (string) (
                                $parts[2]
                                ?? ''
                            )
                        )
                    );


                    if ($code !== '') {

                        $level =
                            $inServiceLoop
                            ? 'line'
                            : 'claim';


                        $svcSeqToUse =
                            $inServiceLoop
                            ? $currentSvcSeq
                            : null;


                        DB::table('era_remarks')
                            ->insert([

                                'era_claim_id' =>
                                $currentEraClaimId,

                                'svc_sequence' =>
                                $svcSeqToUse,

                                'level' =>
                                $level,

                                'source' =>
                                'LQ',

                                'code' =>
                                $code,

                                'created_at' =>
                                now(),

                                'updated_at' =>
                                now(),

                            ]);
                    }


                    continue;
                }
            } // End segment loop


            /*
            |--------------------------------------------------------------------------
            | Final Header Update
            |--------------------------------------------------------------------------
            */

            DB::table('era_files')
                ->where(
                    'id',
                    $eraFileId
                )
                ->update([

                    'payer_name' =>
                    $payerName,

                    'payment_date' =>
                    $paymentDate,

                    'payment_amount' =>
                    $paymentAmt,

                    'trace_number' =>
                    $traceNumber,

                    'updated_at' =>
                    now(),

                ]);


            DB::commit();


            $msgExtra =
                $duplicate
                ? ' (Possible duplicate – same TRN as another ERA)'
                : '';


            return redirect()
                ->route('era.index')
                ->with([

                    'message' =>
                    "ERA imported. {$insertedClaims} CLP claim(s) parsed{$msgExtra}.",

                    'alert-type' =>
                    $duplicate
                        ? 'warning'
                        : 'success',

                ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);


            return redirect()
                ->route('era.index')
                ->with([

                    'message' =>
                    'Error parsing ERA: '
                        .
                        $e->getMessage(),

                    'alert-type' =>
                    'danger',

                ]);
        }
    } // End Method

    public function downloadPdf($id)
    {
        $file = DB::table('era_files')->where('id', $id)->first();
        if (!$file) {
            abort(404, 'ERA file not found');
        }

        // All claims for this ERA
        $claims = DB::table('era_claims')
            ->where('era_file_id', $id)
            ->orderBy('id')
            ->get();

        // Collect unique member IDs (ParticipantIds)
        $memberIds = $claims->pluck('member_id')->filter()->unique()->values();

        // Load members; adjust column names if you actually use lowercase
        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $memberIds)
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->get();

        // Helper to normalize IDs
        $canonical = function ($value) {
            $v = (string) $value;
            $v = trim($v);
            // If you don't want to strip leading zeros, comment this out:
            $v = ltrim($v, '0');
            return $v;
        };

        // Build ParticipantId => [last, first] map
        $memberMap = [];
        foreach ($members as $m) {
            $key = $canonical($m->ParticipantId);
            $memberMap[$key] = [
                'last'  => $m->LastName,
                'first' => $m->FirstName,
            ];
        }

        $pdf = PDF::loadView('era.era_pdf', [
            'file'      => $file,
            'claims'    => $claims,
            'memberMap' => $memberMap,
            'canonical' => $canonical, // pass helper so view can use same logic
        ])->setPaper('letter', 'portrait');

        return $pdf->download("ERA_{$file->id}.pdf");
    }


    public function downloadCsv($id)
    {
        $file = DB::table('era_files')->where('id', $id)->first();
        if (!$file) {
            abort(404, 'ERA file not found');
        }

        // 1) All ERA claims for this file
        $claims = DB::table('era_claims')
            ->where('era_file_id', $id)
            ->orderBy('id')
            ->get();

        // 2) Unique member IDs from era_claims (these are ParticipantIds)
        $memberIds = $claims->pluck('member_id')->filter()->unique()->values();

        // 3) Load matching members
        //    Adjust column names if your actual columns are lowercase.
        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $memberIds)
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->get();

        // Helper to normalize keys (handles spaces / leading zeros)
        $canonical = function ($value) {
            $v = (string) $value;
            $v = trim($v);
            // If you don't want to strip leading zeros, remove this next line:
            $v = ltrim($v, '0');
            return $v;
        };

        // 4) Build ParticipantId => [last, first] map
        $memberMap = [];
        foreach ($members as $m) {
            $key = $canonical($m->ParticipantId);
            $memberMap[$key] = [
                'last'  => $m->LastName,
                'first' => $m->FirstName,
            ];
        }

        // dd($memberMap); // you already saw this giving 730 => TAILOR/ANSUYA

        $fileName = "ERA_{$file->id}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $columns = [
            'Member ID',
            'Last Name',
            'First Name',
            'Patient Control #',
            'Payer Claim #',
            'Claim Status',
            'Total Charge',
            'Total Paid',
            'Patient Responsibility',
            'Payment Date',
            'Payer Name',
            'Trace Number',
        ];

        return Response::streamDownload(function () use ($columns, $claims, $file, $memberMap, $canonical) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, $columns);

            foreach ($claims as $c) {
                // member_id in era_claims = ParticipantId
                $memberIdRaw   = $c->member_id ?? '';
                $memberIdCanon = $canonical($memberIdRaw);

                $nameInfo = $memberMap[$memberIdCanon] ?? ['last' => '', 'first' => ''];

                fputcsv($handle, [
                    trim((string) $memberIdRaw),          // Member ID as stored
                    $nameInfo['last'],                    // LastName from members
                    $nameInfo['first'],                   // FirstName from members
                    $c->patient_control_number,
                    $c->payer_claim_number,
                    $c->claim_status_code,
                    $c->total_charge,
                    $c->total_paid,
                    $c->patient_responsibility,
                    $file->payment_date
                        ? \Carbon\Carbon::parse($file->payment_date)->format('m/d/Y')
                        : '',
                    $file->payer_name,
                    $file->trace_number,
                ]);
            }

            fclose($handle);
        }, $fileName, $headers);

    } //End Method

    public function processEra($id)
    {
        /*
        |--------------------------------------------------------------------------
        | ERA File
        |--------------------------------------------------------------------------
        */

        $file = DB::table('era_files')
            ->where(
                'id',
                $id
            )
            ->first();


        if (!$file) {

            abort(
                404,
                'ERA file not found'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Already Processed
        |--------------------------------------------------------------------------
        */

        if (
            $file->status === 'processed'
        ) {

            return redirect()
                ->route('era.index')
                ->with([

                    'message' =>
                    'This ERA file is already processed.',

                    'alert-type' =>
                    'info',

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        $userCd =
            null;


        if (
            Auth::check()
            &&
            Auth::user()
        ) {

            $userCd =
                Auth::user()->user_cd
                ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Claims
        |--------------------------------------------------------------------------
        */

        $eraClaims = DB::table('era_claims')
            ->where(
                'era_file_id',
                $id
            )
            ->orderBy('id')
            ->get();


        if (
            $eraClaims->isEmpty()
        ) {

            return redirect()
                ->route('era.index')
                ->with([

                    'message' =>
                    'This ERA file does not contain any claim records.',

                    'alert-type' =>
                    'warning',

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        $moneyMatches =
            function (
                $a,
                $b,
                $tolerance = 0.02
            ) {

                return
                    abs(
                        round(
                            (float) $a,
                            2
                        )
                            -
                            round(
                                (float) $b,
                                2
                            )
                    )
                    <=
                    $tolerance;
            };


        /*
        |--------------------------------------------------------------------------
        | Allocate Claim-Level Amount Across Claim Lines
        |--------------------------------------------------------------------------
        */

        $allocateAmount =
            function (
                $amount,
                $rows
            ) {

                $allocation =
                    [];


                $amount =
                    round(
                        (float) $amount,
                        2
                    );


                if (
                    $rows->isEmpty()
                ) {

                    return $allocation;
                }


                if (
                    abs($amount)
                    <
                    0.005
                ) {

                    foreach ($rows as $row) {

                        $allocation[$row->id] = 0.00;
                    }


                    return $allocation;
                }


                $weightTotal =
                    $rows->sum(
                        function ($row) {

                            return
                                max(
                                    (float) (
                                        $row->total_amount
                                        ?? 0
                                    ),
                                    0
                                );
                        }
                    );


                if (
                    $weightTotal <= 0
                ) {

                    $weightTotal =
                        $rows->count();


                    foreach ($rows as $row) {

                        $row->_era_weight =
                            1;
                    }
                } else {

                    foreach ($rows as $row) {

                        $row->_era_weight =
                            max(
                                (float) (
                                    $row->total_amount
                                    ?? 0
                                ),
                                0
                            );
                    }
                }


                $allocated =
                    0.00;


                $rows =
                    $rows->values();


                $lastIndex =
                    $rows->count()
                    -
                    1;


                foreach (
                    $rows as $index => $row
                ) {

                    if (
                        $index
                        ===
                        $lastIndex
                    ) {

                        $lineAmount =
                            round(
                                $amount
                                    -
                                    $allocated,
                                2
                            );
                    } else {

                        $lineAmount =
                            round(
                                $amount
                                    *
                                    (
                                        $row->_era_weight
                                        /
                                        $weightTotal
                                    ),
                                2
                            );


                        $allocated +=
                            $lineAmount;
                    }


                    $allocation[$row->id] =
                        $lineAmount;
                }


                return $allocation;
            };


        /*
        |--------------------------------------------------------------------------
        | Build Posting Plan
        |--------------------------------------------------------------------------
        |
        | Nothing is posted until ALL claims can be safely matched.
        |--------------------------------------------------------------------------
        */

        $postingPlans =
            [];


        $validationErrors =
            [];

        $unmatchedClaims = [];


        foreach (
            $eraClaims as $eraClaim
        ) {

            $eraClaimNo = trim(
                (string) (
                    $eraClaim
                    ->patient_control_number
                    ?? ''
                )
            );


            if ($eraClaimNo === '') {

                $validationErrors[] =
                    "ERA Claim ID {$eraClaim->id}: Claim number is blank.";

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Normalize Claim Number
            |--------------------------------------------------------------------------
            */

            $claimNoForLookup =
                $eraClaimNo;


            if (
                ctype_digit(
                    $claimNoForLookup
                )
            ) {

                $claimNoForLookup =
                    str_pad(
                        $claimNoForLookup,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | ADC Claim Rows
            |--------------------------------------------------------------------------
            */

            $claimRows = DB::table('claims')
                ->where(
                    'claim_no',
                    $claimNoForLookup
                )
                ->orderBy(
                    'line_no'
                )
                ->get();


                        /*
            |--------------------------------------------------------------------------
            | Legacy 837 Fallback
            |--------------------------------------------------------------------------
            |
            | Normal claim_no matching always runs first.
            |
            | Only when the normal claim number cannot be found do we check
            | legacy_837_claim_map for the historical bad CLM01 values.
            |
            */

            if ($claimRows->isEmpty()) {

                $legacyMaps = DB::table('legacy_837_claim_map')
                    ->where(
                        'clm01',
                        $eraClaimNo
                    )
                    ->get();


                /*
                |--------------------------------------------------------------------------
                | Handle Possible Zero-Padding Difference
                |--------------------------------------------------------------------------
                */

                if (
                    $legacyMaps->isEmpty()
                    &&
                    ctype_digit($eraClaimNo)
                ) {

                    $legacyNumeric =
                        (int) $eraClaimNo;

                    $legacyMaps = DB::table('legacy_837_claim_map')
                        ->get()
                        ->filter(function ($row) use ($legacyNumeric) {

                            $mapValue =
                                trim(
                                    (string) (
                                        $row->clm01
                                        ?? ''
                                    )
                                );

                            return
                                ctype_digit($mapValue)
                                &&
                                (int) $mapValue
                                ===
                                $legacyNumeric;
                        })
                        ->values();
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Legacy Mapping Against ERA
                |--------------------------------------------------------------------------
                */

                $safeLegacyMaps =
                    $legacyMaps
                    ->filter(function ($map) use ($eraClaim) {

                        /*
                        |--------------------------------------------------------------------------
                        | ParticipantId Must Exist
                        |--------------------------------------------------------------------------
                        */

                        if (
                            empty($map->ParticipantId)
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Service From
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($eraClaim->service_from)
                            &&
                            !empty($map->service_from)
                            &&
                            (string) $eraClaim->service_from
                            !==
                            (string) $map->service_from
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Service To
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($eraClaim->service_to)
                            &&
                            !empty($map->service_to)
                            &&
                            (string) $eraClaim->service_to
                            !==
                            (string) $map->service_to
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Charge Amount
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $eraClaim->total_charge !== null
                            &&
                            $map->charge_amount !== null
                            &&
                            abs(
                                round(
                                    (float) $eraClaim->total_charge,
                                    2
                                )
                                -
                                round(
                                    (float) $map->charge_amount,
                                    2
                                )
                            )
                            >
                            0.02
                        ) {
                            return false;
                        }


                        return true;
                    })
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | Exactly One Legacy Mapping Required
                |--------------------------------------------------------------------------
                */

                if (
                    $safeLegacyMaps->count()
                    ===
                    1
                ) {

                    $legacyMap =
                        $safeLegacyMaps->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Locate Actual ADC Claim
                    |--------------------------------------------------------------------------
                    */

                    $legacyClaimRows =
                        DB::table('claims')
                        ->where(
                            'ParticipantId',
                            trim(
                                (string) $legacyMap->ParticipantId
                            )
                        );


                    if (
                        !empty($legacyMap->service_from)
                    ) {

                        $legacyClaimRows
                            ->whereDate(
                                'service_from',
                                $legacyMap->service_from
                            );
                    }


                    if (
                        !empty($legacyMap->service_to)
                    ) {

                        $legacyClaimRows
                            ->whereDate(
                                'service_to',
                                $legacyMap->service_to
                            );
                    }


                    $legacyClaimRows =
                        $legacyClaimRows
                        ->orderBy('line_no')
                        ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | Charge Validation
                    |--------------------------------------------------------------------------
                    |
                    | Do not blindly use ParticipantId + date.
                    |
                    */

                    $legacyClaimRows =
                        $legacyClaimRows
                        ->filter(function ($row) use ($legacyMap) {

                            if (
                                $legacyMap->charge_amount === null
                            ) {
                                return true;
                            }

                            return
                                abs(
                                    round(
                                        (float) (
                                            $row->total_amount
                                            ?? 0
                                        ),
                                        2
                                    )
                                    -
                                    round(
                                        (float) $legacyMap->charge_amount,
                                        2
                                    )
                                )
                                <=
                                0.02;
                        })
                        ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | Use Legacy Claim Only When Safe
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $legacyClaimRows->count()
                        ===
                        1
                    ) {

                        $claimRows =
                            $legacyClaimRows;


                        /*
                        |--------------------------------------------------------------------------
                        | Fill ERA Member ID
                        |--------------------------------------------------------------------------
                        */

                        DB::table('era_claims')
                            ->where(
                                'id',
                                $eraClaim->id
                            )
                            ->update([

                                'member_id' =>
                                $legacyMap->ParticipantId,

                                'updated_at' =>
                                now(),

                            ]);


                        $eraClaim->member_id =
                            $legacyMap->ParticipantId;
                    }
                }
            }


            // if (
            //     $claimRows->isEmpty()
            // ) {

            //     $validationErrors[] =
            //         "ERA Claim # {$eraClaimNo}: ADC Claim # {$claimNoForLookup} was not found.";

            //     continue;
            // }

            if (
                $claimRows->isEmpty()
            ) {

                $unmatchedClaims[] = [

                    'era_file_id' =>
                    $id,

                    'era_claim_id' =>
                    $eraClaim->id,

                    'patient_control_number' =>
                    $eraClaimNo,

                    'normalized_claim_no' =>
                    $claimNoForLookup,

                    'payer_claim_number' =>
                    $eraClaim->payer_claim_number
                        ?? null,

                    'member_id' =>
                    $eraClaim->member_id
                        ?? null,

                    'claim_status_code' =>
                    $eraClaim->claim_status_code
                        ?? null,

                    'service_from' =>
                    $eraClaim->service_from
                        ?? null,

                    'service_to' =>
                    $eraClaim->service_to
                        ?? null,

                    'total_charge' =>
                    $eraClaim->total_charge
                        ?? null,

                    'total_paid' =>
                    $eraClaim->total_paid
                        ?? null,

                    'patient_responsibility' =>
                    $eraClaim->patient_responsibility
                        ?? null,

                    'reason' =>
                    'ADC claim not found',

                    'created_at' =>
                    now(),

                    'updated_at' =>
                    now(),

                ];

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ERA Service Lines
            |--------------------------------------------------------------------------
            */

            $serviceLines =
                DB::table('era_service_lines')
                ->where(
                    'era_claim_id',
                    $eraClaim->id
                )
                ->orderBy(
                    'svc_sequence'
                )
                ->get();


            /*
            |--------------------------------------------------------------------------
            | ERA CAS Adjustments
            |--------------------------------------------------------------------------
            */

            $adjustments =
                DB::table('era_adjustments')
                ->where(
                    'era_claim_id',
                    $eraClaim->id
                )
                ->orderBy('id')
                ->get();


            /*
            |--------------------------------------------------------------------------
            | No SVC Lines
            |--------------------------------------------------------------------------
            */

            if (
                $serviceLines->isEmpty()
            ) {

                if (
                    $claimRows->count()
                    !==
                    1
                ) {

                    $validationErrors[] =
                        "ERA Claim # {$eraClaimNo}: ERA has no SVC lines but ADC Claim # {$claimNoForLookup} contains "
                        .
                        $claimRows->count()
                        .
                        ' lines.';

                    continue;
                }


                $claim =
                    $claimRows->first();


                $paidAmount =
                    round(
                        (float) (
                            $eraClaim->total_paid
                            ?? 0
                        ),
                        2
                    );


                $patientResp =
                    round(
                        (float) (
                            $eraClaim
                            ->patient_responsibility
                            ?? 0
                        ),
                        2
                    );


                $contractualAdj =
                    0.00;


                $otherAdj =
                    0.00;


                foreach (
                    $adjustments as $adj
                ) {

                    $amount =
                        round(
                            (float) (
                                $adj->amount
                                ?? 0
                            ),
                            2
                        );


                    $group = strtoupper(
                        trim(
                            (string) (
                                $adj->group_code
                                ?? ''
                            )
                        )
                    );


                    if ($group === 'CO') {

                        $contractualAdj +=
                            $amount;
                    } elseif (
                        $group !== 'PR'
                    ) {

                        $otherAdj +=
                            $amount;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Fallback Contractual Adjustment
                |--------------------------------------------------------------------------
                */

                if (
                    abs(
                        $contractualAdj
                    )
                    <
                    0.005
                ) {

                    $contractualAdj =
                        max(
                            round(
                                (float) $claim->total_amount
                                    -
                                    $paidAmount
                                    -
                                    $patientResp
                                    -
                                    $otherAdj,
                                2
                            ),
                            0
                        );
                }


                $postingPlans[] = [

                    'era_claim' =>
                    $eraClaim,

                    'claim' =>
                    $claim,

                    'paid_amount' =>
                    $paidAmount,

                    'patient_responsibility' =>
                    $patientResp,

                    'contractual_adjustment' =>
                    round(
                        $contractualAdj,
                        2
                    ),

                    'other_adjustment' =>
                    round(
                        $otherAdj,
                        2
                    ),

                    'line_adjustments' =>
                    $adjustments,

                    'allocated_claim_adjustments' =>
                    [],

                ];


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Separate Claim-Level Adjustments
            |--------------------------------------------------------------------------
            */

            $claimLevelAdjustments =
                $adjustments
                ->filter(
                    function ($adj) {

                        return
                            strtolower(
                                trim(
                                    (string) (
                                        $adj->level
                                        ?? ''
                                    )
                                )
                            )
                            ===
                            'claim';
                    }
                )
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Match SVC To Claims
            |--------------------------------------------------------------------------
            */

            $usedClaimIds =
                [];


            $matches =
                [];


            $claimHasError =
                false;


            foreach (
                $serviceLines as $svc
            ) {

                $svcSequence =
                    (int) (
                        $svc->svc_sequence
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Candidate Validation Function
                |--------------------------------------------------------------------------
                */

                $candidateMatches =
                    function ($row)
                    use (
                        $svc,
                        $moneyMatches,
                        $eraClaim
                    ) {

                        if (!$row) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Procedure
                        |--------------------------------------------------------------------------
                        */

                        $svcProc = strtoupper(
                            trim(
                                (string) (
                                    $svc
                                    ->procedure_code
                                    ?? ''
                                )
                            )
                        );


                        $claimProc = strtoupper(
                            trim(
                                (string) (
                                    $row->proc_code
                                    ?? ''
                                )
                            )
                        );


                        if (
                            $svcProc !== ''
                            &&
                            $claimProc !== ''
                            &&
                            $svcProc !== $claimProc
                        ) {

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Modifier
                        |--------------------------------------------------------------------------
                        */

                        $svcMod = strtoupper(
                            trim(
                                (string) (
                                    $svc->modifier1
                                    ?? ''
                                )
                            )
                        );


                        $claimMod = strtoupper(
                            trim(
                                (string) (
                                    $row->m1
                                    ?? ''
                                )
                            )
                        );


                        if (
                            $svcMod !== ''
                            &&
                            $claimMod !== ''
                            &&
                            $svcMod !== $claimMod
                        ) {

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Service From
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($svc->service_from)
                            &&
                            !empty($row->service_from)
                            &&
                            (string) $svc->service_from
                            !==
                            (string) $row->service_from
                        ) {

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Service To
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($svc->service_to)
                            &&
                            !empty($row->service_to)
                            &&
                            (string) $svc->service_to
                            !==
                            (string) $row->service_to
                        ) {

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Charge Amount
                        |--------------------------------------------------------------------------
                        |
                        | CLP status 22 = reversal.
                        | Reversal ERA amounts may be negative while the original ADC
                        | claim amount remains positive.
                        |--------------------------------------------------------------------------
                        */

                        if ($svc->charge_amount !== null) {

                            $svcCharge =
                                (float) $svc->charge_amount;


                            /*
                            |--------------------------------------------------------------------------
                            | Reversal
                            |--------------------------------------------------------------------------
                            */

                            if (
                                (string) (
                                    $eraClaim->claim_status_code
                                    ?? ''
                                )
                                ===
                                '22'
                            ) {

                                $svcCharge =
                                    abs($svcCharge);
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Compare Against ADC Claim
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !$moneyMatches(
                                    $row->total_amount,
                                    $svcCharge
                                )
                            ) {

                                return false;
                            }
                        }


                        return true;
                    };


                /*
                |--------------------------------------------------------------------------
                | First Attempt:
                | claim_no + line_no = svc_sequence
                |--------------------------------------------------------------------------
                */

                $candidate =
                    $claimRows
                    ->first(
                        function ($row)
                        use (
                            $svcSequence,
                            $usedClaimIds
                        ) {

                            return
                                (int) $row->line_no
                                ===
                                $svcSequence
                                &&
                                !isset(
                                    $usedClaimIds[$row->id]
                                );
                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | Fallback Search
                |--------------------------------------------------------------------------
                */

                if (
                    !$candidate
                    ||
                    !$candidateMatches(
                        $candidate
                    )
                ) {

                    $possible =
                        $claimRows
                        ->filter(
                            function ($row)
                            use (
                                $usedClaimIds,
                                $candidateMatches
                            ) {

                                if (
                                    isset(
                                        $usedClaimIds[$row->id]
                                    )
                                ) {

                                    return false;
                                }


                                return
                                    $candidateMatches(
                                        $row
                                    );
                            }
                        )
                        ->values();


                    if (
                        $possible->count()
                        ===
                        1
                    ) {

                        $candidate =
                            $possible->first();
                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | ERA Exception - SVC Could Not Safely Match
                        |--------------------------------------------------------------------------
                        |
                        | Do not stop the entire ERA. Quarantine this ERA claim so the
                        | remaining claims can continue processing safely.
                        |--------------------------------------------------------------------------
                        */

                        $svcProcForReason =
                            trim(
                                (string) (
                                    $svc->procedure_code
                                    ?? ''
                                )
                            );

                        $svcModForReason =
                            trim(
                                (string) (
                                    $svc->modifier1
                                    ?? ''
                                )
                            );

                        $svcFromForReason =
                            (string) (
                                $svc->service_from
                                ?? ''
                            );

                        $svcToForReason =
                            (string) (
                                $svc->service_to
                                ?? ''
                            );

                        $svcChargeForReason =
                            $svc->charge_amount !== null
                                ? number_format(
                                    (float) $svc->charge_amount,
                                    2,
                                    '.',
                                    ''
                                )
                                : '';

                        $adcSummary =
                            $claimRows
                                ->map(
                                    function ($row) {

                                        return
                                            'L'
                                            . (
                                                $row->line_no
                                                ?? '?'
                                            )
                                            . ' '
                                            . trim(
                                                (string) (
                                                    $row->proc_code
                                                    ?? ''
                                                )
                                            )
                                            . '/'
                                            . trim(
                                                (string) (
                                                    $row->m1
                                                    ?? ''
                                                )
                                            )
                                            . ' '
                                            . (
                                                $row->service_from
                                                ?? ''
                                            )
                                            . '-'
                                            . (
                                                $row->service_to
                                                ?? ''
                                            )
                                            . ' $'
                                            . number_format(
                                                (float) (
                                                    $row->total_amount
                                                    ?? 0
                                                ),
                                                2,
                                                '.',
                                                ''
                                            );
                                    }
                                )
                                ->implode('; ');

                        $reason =
                            "SVC #{$svcSequence} could not safely match ADC claim {$claimNoForLookup}. "
                            . "ERA {$svcProcForReason}/{$svcModForReason} "
                            . "{$svcFromForReason}-{$svcToForReason} "
                            . "\${$svcChargeForReason}; ADC {$adcSummary}";

                        $unmatchedClaims[] = [

                            'era_file_id' =>
                            $id,

                            'era_claim_id' =>
                            $eraClaim->id,

                            'patient_control_number' =>
                            $eraClaimNo,

                            'normalized_claim_no' =>
                            $claimNoForLookup,

                            'payer_claim_number' =>
                            $eraClaim->payer_claim_number
                                ?? null,

                            'member_id' =>
                            $eraClaim->member_id
                                ?? null,

                            'claim_status_code' =>
                            $eraClaim->claim_status_code
                                ?? null,

                            'service_from' =>
                            $eraClaim->service_from
                                ?? null,

                            'service_to' =>
                            $eraClaim->service_to
                                ?? null,

                            'total_charge' =>
                            $eraClaim->total_charge
                                ?? null,

                            'total_paid' =>
                            $eraClaim->total_paid
                                ?? null,

                            'patient_responsibility' =>
                            $eraClaim->patient_responsibility
                                ?? null,

                            'reason' =>
                            substr(
                                $reason,
                                0,
                                255
                            ),

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ];

                        $claimHasError =
                            true;

                        break;
                    }
                }


                $usedClaimIds[$candidate->id] =
                    true;


                $matches[] = [

                    'svc' =>
                    $svc,

                    'claim' =>
                    $candidate,

                ];
            }


            if ($claimHasError) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Every ADC Claim Line Must Match
            |--------------------------------------------------------------------------
            */

            if (
                count(
                    $usedClaimIds
                )
                !==
                $claimRows->count()
            ) {

                /*
                |--------------------------------------------------------------------------
                | ERA Exception - Claim Line Count Did Not Match
                |--------------------------------------------------------------------------
                |
                | The ADC claim exists, but the ERA did not safely account for every
                | ADC claim line. Quarantine this claim and continue with the ERA.
                |--------------------------------------------------------------------------
                */

                $reason =
                    "ERA matched "
                    .
                    count($usedClaimIds)
                    .
                    " service line(s), but ADC claim {$claimNoForLookup} contains "
                    .
                    $claimRows->count()
                    .
                    ' line(s).';

                $unmatchedClaims[] = [

                    'era_file_id' =>
                    $id,

                    'era_claim_id' =>
                    $eraClaim->id,

                    'patient_control_number' =>
                    $eraClaimNo,

                    'normalized_claim_no' =>
                    $claimNoForLookup,

                    'payer_claim_number' =>
                    $eraClaim->payer_claim_number
                        ?? null,

                    'member_id' =>
                    $eraClaim->member_id
                        ?? null,

                    'claim_status_code' =>
                    $eraClaim->claim_status_code
                        ?? null,

                    'service_from' =>
                    $eraClaim->service_from
                        ?? null,

                    'service_to' =>
                    $eraClaim->service_to
                        ?? null,

                    'total_charge' =>
                    $eraClaim->total_charge
                        ?? null,

                    'total_paid' =>
                    $eraClaim->total_paid
                        ?? null,

                    'patient_responsibility' =>
                    $eraClaim->patient_responsibility
                        ?? null,

                    'reason' =>
                    substr(
                        $reason,
                        0,
                        255
                    ),

                    'created_at' =>
                    now(),

                    'updated_at' =>
                    now(),

                ];

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Claim-Level CAS Totals
            |--------------------------------------------------------------------------
            */

            $claimCO =
                0.00;


            $claimPR =
                0.00;


            $claimOther =
                0.00;


            foreach (
                $claimLevelAdjustments as $adj
            ) {

                $amount =
                    round(
                        (float) (
                            $adj->amount
                            ?? 0
                        ),
                        2
                    );


                $group = strtoupper(
                    trim(
                        (string) (
                            $adj->group_code
                            ?? ''
                        )
                    )
                );


                if ($group === 'CO') {

                    $claimCO +=
                        $amount;
                } elseif (
                    $group === 'PR'
                ) {

                    $claimPR +=
                        $amount;
                } else {

                    $claimOther +=
                        $amount;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Allocate Claim-Level Amounts
            |--------------------------------------------------------------------------
            */

            $allocationRows =
                collect(
                    array_map(
                        function ($match) {

                            return
                                $match['claim'];
                        },
                        $matches
                    )
                );


            $allocatedCO =
                $allocateAmount(
                    $claimCO,
                    $allocationRows
                );


            $allocatedOther =
                $allocateAmount(
                    $claimOther,
                    $allocationRows
                );


            /*
            |--------------------------------------------------------------------------
            | Calculate Existing Line-Level PR
            |--------------------------------------------------------------------------
            */

            $linePrTotal =
                0.00;


            foreach ($matches as $match) {

                $svc =
                    $match['svc'];


                $lineAdjustments =
                    $adjustments
                    ->filter(
                        function ($adj)
                        use ($svc) {

                            return
                                strtolower(
                                    trim(
                                        (string) (
                                            $adj->level
                                            ?? ''
                                        )
                                    )
                                )
                                ===
                                'line'
                                &&
                                (int) (
                                    $adj->svc_sequence
                                    ?? 0
                                )
                                ===
                                (int) (
                                    $svc->svc_sequence
                                    ?? 0
                                );
                        }
                    );


                foreach (
                    $lineAdjustments as $adj
                ) {

                    if (
                        strtoupper(
                            trim(
                                (string) (
                                    $adj->group_code
                                    ?? ''
                                )
                            )
                        )
                        ===
                        'PR'
                    ) {

                        $linePrTotal +=
                            round(
                                (float) (
                                    $adj->amount
                                    ?? 0
                                ),
                                2
                            );
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Remaining PR To Allocate
            |--------------------------------------------------------------------------
            */

            if (
                abs(
                    $claimPR
                )
                >=
                0.005
            ) {

                $remainingPR =
                    $claimPR;
            } else {

                $remainingPR =
                    round(
                        (float) (
                            $eraClaim
                            ->patient_responsibility
                            ?? 0
                        )
                            -
                            $linePrTotal,
                        2
                    );
            }


            $allocatedPR =
                $allocateAmount(
                    $remainingPR,
                    $allocationRows
                );


            /*
            |--------------------------------------------------------------------------
            | Build One Posting Plan Per SVC Line
            |--------------------------------------------------------------------------
            */

            foreach ($matches as $match) {

                $svc =
                    $match['svc'];


                $claim =
                    $match['claim'];


                $svcSequence =
                    (int) (
                        $svc->svc_sequence
                        ?? 0
                    );


                $lineAdjustments =
                    $adjustments
                    ->filter(
                        function ($adj)
                        use ($svcSequence) {

                            return
                                strtolower(
                                    trim(
                                        (string) (
                                            $adj->level
                                            ?? ''
                                        )
                                    )
                                )
                                ===
                                'line'
                                &&
                                (int) (
                                    $adj->svc_sequence
                                    ?? 0
                                )
                                ===
                                $svcSequence;
                        }
                    )
                    ->values();


                $lineCO =
                    0.00;


                $linePR =
                    0.00;


                $lineOther =
                    0.00;


                foreach (
                    $lineAdjustments as $adj
                ) {

                    $amount =
                        round(
                            (float) (
                                $adj->amount
                                ?? 0
                            ),
                            2
                        );


                    $group = strtoupper(
                        trim(
                            (string) (
                                $adj->group_code
                                ?? ''
                            )
                        )
                    );


                    if ($group === 'CO') {

                        $lineCO +=
                            $amount;
                    } elseif (
                        $group === 'PR'
                    ) {

                        $linePR +=
                            $amount;
                    } else {

                        $lineOther +=
                            $amount;
                    }
                }


                $paidAmount =
                    round(
                        (float) (
                            $svc->paid_amount
                            ?? 0
                        ),
                        2
                    );
                /*
                |--------------------------------------------------------------------------
                | Reversal Flag
                |--------------------------------------------------------------------------
                */

                $isReversal =
                    (string) (
                        $eraClaim->claim_status_code
                        ?? ''
                    )
                    ===
                    '22';


                $contractualAdj =
                    round(
                        $lineCO
                            +
                            (
                                $allocatedCO[$claim->id]
                                ?? 0
                            ),
                        2
                    );


                $patientResp =
                    round(
                        $linePR
                            +
                            (
                                $allocatedPR[$claim->id]
                                ?? 0
                            ),
                        2
                    );


                $otherAdj =
                    round(
                        $lineOther
                            +
                            (
                                $allocatedOther[$claim->id]
                                ?? 0
                            ),
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Fallback CO
                |--------------------------------------------------------------------------
                */

                if (
                    !$isReversal
                    &&
                    abs(
                        $contractualAdj
                    )
                    <
                    0.005
                    &&
                    abs(
                        $claimCO
                    )
                    <
                    0.005
                ) {

                    $fallback =
                        round(
                            (float) $claim->total_amount
                                -
                                $paidAmount
                                -
                                $patientResp
                                -
                                $otherAdj,
                            2
                        );


                    if ($fallback > 0) {

                        $contractualAdj =
                            $fallback;
                    }
                }

                $postingPlans[] = [

                    'era_claim' =>
                    $eraClaim,

                    'claim' =>
                    $claim,

                    'paid_amount' =>
                    $paidAmount,

                    'patient_responsibility' =>
                    $patientResp,

                    'contractual_adjustment' =>
                    $contractualAdj,

                    'other_adjustment' =>
                    $otherAdj,

                    'line_adjustments' =>
                    $lineAdjustments,

                    'allocated_claim_adjustments' => [

                        'CO' =>
                        $allocatedCO[$claim->id]
                            ?? 0,

                        'PR' =>
                        $allocatedPR[$claim->id]
                            ?? 0,

                        'OTHER' =>
                        $allocatedOther[$claim->id]
                            ?? 0,

                    ],

                ];
            }
        } // End ERA claim loop


        /*
        |--------------------------------------------------------------------------
        | Stop Before Posting If Anything Failed Validation
        |--------------------------------------------------------------------------
        */

        if (
            !empty($validationErrors)
        ) {

            $message =
                implode(
                    ' | ',
                    $validationErrors
                );


            DB::table('era_files')
                ->where(
                    'id',
                    $id
                )
                ->update([

                    'status' =>
                    'ready',

                    'status_message' =>
                    substr(
                        $message,
                        0,
                        1000
                    ),

                    'updated_at' =>
                    now(),

                ]);


            return redirect()
                ->route('era.index')
                ->with([

                    'message' =>
                    'ERA was NOT posted. '
                        .
                        $message,

                    'alert-type' =>
                    'warning',

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | POST ERA
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | Remove Previous Transactions For This ERA
            |--------------------------------------------------------------------------
            */

            DB::table('claim_transactions')
                ->where(
                    'era_file_id',
                    $id
                )
                ->delete();


            /*
            |--------------------------------------------------------------------------
            | Refresh ERA Exceptions For This File
            |--------------------------------------------------------------------------
            |
            | Any previous unresolved entries for this ERA are first marked resolved.
            | Claims still unmatched in the current run are then reopened/upserted below.
            |--------------------------------------------------------------------------
            */

            DB::table('era_unmatched_claims')
                ->where(
                    'era_file_id',
                    $id
                )
                ->where(
                    'resolved_yn',
                    'N'
                )
                ->update([

                    'resolved_yn' =>
                    'Y',

                    'resolved_at' =>
                    now(),

                    'updated_at' =>
                    now(),

                ]);


            foreach ($unmatchedClaims as $unmatched) {

                DB::table('era_unmatched_claims')
                    ->updateOrInsert(

                        [
                            'era_file_id' =>
                            $unmatched['era_file_id'],

                            'era_claim_id' =>
                            $unmatched['era_claim_id'],
                        ],

                        [
                            'patient_control_number' =>
                            $unmatched['patient_control_number'],

                            'normalized_claim_no' =>
                            $unmatched['normalized_claim_no'],

                            'payer_claim_number' =>
                            $unmatched['payer_claim_number'],

                            'member_id' =>
                            $unmatched['member_id'],

                            'claim_status_code' =>
                            $unmatched['claim_status_code'],

                            'service_from' =>
                            $unmatched['service_from'],

                            'service_to' =>
                            $unmatched['service_to'],

                            'total_charge' =>
                            $unmatched['total_charge'],

                            'total_paid' =>
                            $unmatched['total_paid'],

                            'patient_responsibility' =>
                            $unmatched['patient_responsibility'],

                            'reason' =>
                            $unmatched['reason'],

                            'resolved_yn' =>
                            'N',

                            'resolved_at' =>
                            null,

                            'created_at' =>
                            $unmatched['created_at'],

                            'updated_at' =>
                            now(),
                        ]
                    );
            }


            $postedLines =
                0;


            $postedClaims =
                [];

            /*
            |--------------------------------------------------------------------------
            | Final Claim State Candidates
            |--------------------------------------------------------------------------
            */

            $claimFinalCandidates =
                [];

            $totalPaidPosted =
                0.00;


            $totalAdjPosted =
                0.00;


            $totalBalance =
                0.00;


            foreach (
                $postingPlans as $plan
            ) {

                $eraClaim =
                    $plan['era_claim'];


                $claim =
                    $plan['claim'];


                $paidAmount =
                    round(
                        (float) $plan['paid_amount'],
                        2
                    );


                $patientResp =
                    round(
                        (float) $plan['patient_responsibility'],
                        2
                    );


                $contractualAdj =
                    round(
                        (float) $plan['contractual_adjustment'],
                        2
                    );


                $otherAdj =
                    round(
                        (float) $plan['other_adjustment'],
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Balance
                |--------------------------------------------------------------------------
                |
                | Patient responsibility intentionally remains in balance.
                |--------------------------------------------------------------------------
                */

                $balanceAmount =
                    round(
                        max(
                            (float) (
                                $claim->total_amount
                                ?? 0
                            )
                                -
                                $paidAmount
                                -
                                $contractualAdj
                                -
                                $otherAdj
                                -
                                (float) (
                                    $claim->writeoff_amount
                                    ?? 0
                                ),
                            0
                        ),
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | Zero-Payment Denial - Keep Claim Balance Open
                |--------------------------------------------------------------------------
                |
                | CLP status 4 = denied.
                | We do not know whether another payer may adjudicate the same claim later.
                | Therefore, a $0.00 denial must not financially close the ADC claim.
                |
                */

                $isZeroPaymentDenial =
                    (string) (
                        $eraClaim->claim_status_code
                        ?? ''
                    )
                    ===
                    '4'
                    &&
                    abs($paidAmount) < 0.005;


                if ($isZeroPaymentDenial) {

                    /*
                    |--------------------------------------------------------------------------
                    | Do Not Financially Post Denial Adjustments
                    |--------------------------------------------------------------------------
                    |
                    | The original CAS / PR information remains safely stored in the ERA
                    | tables. These values are only zeroed for the ADC claim financial state.
                    |
                    */

                    $contractualAdj = 0.00;
                    $otherAdj       = 0.00;
                    $patientResp    = 0.00;

                    $balanceAmount =
                        round(
                            max(
                                (float) (
                                    $claim->total_amount
                                    ?? 0
                                )
                                -
                                (float) (
                                    $claim->ins1_paid
                                    ?? 0
                                )
                                -
                                (float) (
                                    $claim->writeoff_amount
                                    ?? 0
                                ),
                                0
                            ),
                            2
                        );
                }
                /*
                |--------------------------------------------------------------------------
                | Allowed
                |--------------------------------------------------------------------------
                */

                $allowedAmount =
                    round(
                        max(
                            $paidAmount
                                +
                                $patientResp,
                            0
                        ),
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Determine Whether This Service Line Contains CO-16
                |--------------------------------------------------------------------------
                |
                | CO-16 indicates that the claim/service lacks information or contains
                | a submission/billing issue that normally requires correction.
                |
                | In our ADC workflow, a zero-payment CO-16 line is marked rejected so
                | office staff can identify it for correction/resubmission rather than
                | treating it as a final paid or denied claim.
                |--------------------------------------------------------------------------
                */

                $hasCO16 = false;

                foreach (
                    $plan['line_adjustments']
                    as $adj
                ) {

                    $groupCode = strtoupper(
                        trim(
                            (string) (
                                $adj->group_code
                                ?? ''
                            )
                        )
                    );

                    $reasonCode = trim(
                        (string) (
                            $adj->reason_code
                            ?? ''
                        )
                    );

                    if (
                        $groupCode === 'CO'
                        &&
                        $reasonCode === '16'
                    ) {

                        $hasCO16 = true;

                        break;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Claim Status
                |--------------------------------------------------------------------------
                |
                | Rules:
                |
                | 1. CLP status 4 = denied.
                |
                | 2. CO-16 = rejected so it can be corrected/resubmitted.
                |
                | 3. If payer paid $0.00 and the billed amount was completely removed by
                |    other payer adjustments, do NOT call the claim "paid".
                |
                | 4. Fully paid claims with an actual insurance payment = paid.
                |
                | 5. Remaining open balance = accepted.
                |--------------------------------------------------------------------------
                */

                if (
                    (string) (
                        $eraClaim->claim_status_code
                        ?? ''
                    )
                    ===
                    '4'
                ) {

                    $status =
                        'denied';
                } elseif (
                    $hasCO16
                ) {

                    $status =
                        'rejected';
                } elseif (
                    $paidAmount <= 0.005
                    &&
                    $balanceAmount <= 0.005
                    &&
                    (
                        $contractualAdj > 0.005
                        ||
                        $otherAdj > 0.005
                    )
                ) {

                    $status =
                        'denied';
                } elseif (
                    $paidAmount > 0.005
                    &&
                    $balanceAmount <= 0.005
                ) {

                    $status =
                        'paid';
                } else {

                    $status =
                        'accepted';
                }

                /*
                |--------------------------------------------------------------------------
                | Update Exact Claim Line
                |--------------------------------------------------------------------------
                */

                /*
                |--------------------------------------------------------------------------
                | Save Final Claim Candidate
                |--------------------------------------------------------------------------
                |
                | Do not update the claims table yet.
                | The same ADC claim can appear multiple times in one ERA
                | as payment, reversal, denial, etc.
                |--------------------------------------------------------------------------
                */

                $claimFinalCandidates[$claim->id][] = [

                    'claim' =>
                    $claim,

                    'era_claim' =>
                    $eraClaim,

                    'paid_amount' =>
                    $paidAmount,

                    'patient_responsibility' =>
                    $patientResp,

                    'contractual_adjustment' =>
                    $contractualAdj,

                    'other_adjustment' =>
                    $otherAdj,

                    'balance_amount' =>
                    $balanceAmount,

                    'status' =>
                    $status,

                ];
                /*
                |--------------------------------------------------------------------------
                | Payment Transaction
                |--------------------------------------------------------------------------
                */

                if (
                    abs(
                        $paidAmount
                    )
                    >=
                    0.005
                ) {

                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                            $claim->id,

                            'era_file_id' =>
                            $id,

                            'era_claim_id' =>
                            $eraClaim->id,

                            'claim_no' =>
                            $claim->claim_no,

                            'ParticipantId' =>
                            $claim->ParticipantId,

                            'payer_claim_number' =>
                            $eraClaim
                                ->payer_claim_number,

                            'transaction_date' =>
                            $file->payment_date
                                ??
                                now()
                                ->toDateString(),

                            //'transaction_type' =>
                            //'payment',

                            'transaction_type' => (
                                (string) (
                                    $eraClaim->claim_status_code
                                    ?? ''
                                )
                                ===
                                '22'
                            )
                                ? 'reversal'
                                : 'payment',

                            'amount' =>
                            $paidAmount,

                            'description' =>
                            'ERA insurance payment - line '
                                .
                                $claim->line_no,

                            'created_by' =>
                            $userCd,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Financial Adjustment Transactions
                |--------------------------------------------------------------------------
                |
                | For a zero-payment CLP status 4 denial, keep all CAS/PR detail
                | in the ERA tables but do not financially post those adjustments
                | to claim_transactions or reduce the ADC claim balance.
                |--------------------------------------------------------------------------
                */

                if (!$isZeroPaymentDenial) {

                    /*
                    |--------------------------------------------------------------------------
                    | Actual Line-Level CAS Transactions
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $plan['line_adjustments']
                        as $adj
                    ) {

                        $group = strtoupper(
                            trim(
                                (string) (
                                    $adj->group_code
                                    ?? ''
                                )
                            )
                        );


                    $transactionType =
                        'deduction';


                    if ($group === 'CO') {

                        $transactionType =
                            'contractual_adjustment';
                    } elseif (
                        $group === 'PR'
                    ) {

                        $transactionType =
                            'patient_responsibility';
                    }


                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                            $claim->id,

                            'era_file_id' =>
                            $id,

                            'era_claim_id' =>
                            $eraClaim->id,

                            'claim_no' =>
                            $claim->claim_no,

                            'ParticipantId' =>
                            $claim->ParticipantId,

                            'payer_claim_number' =>
                            $eraClaim
                                ->payer_claim_number,

                            'transaction_date' =>
                            $file->payment_date
                                ??
                                now()
                                ->toDateString(),

                            'transaction_type' =>
                            $transactionType,

                            'amount' =>
                            round(
                                (float) (
                                    $adj->amount
                                    ?? 0
                                ),
                                2
                            ),

                            'group_code' =>
                            $adj->group_code,

                            'reason_code' =>
                            $adj->reason_code,

                            'description' =>
                            'ERA CAS adjustment - line '
                                .
                                $claim->line_no,

                            'created_by' =>
                            $userCd,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Allocated Claim-Level CO
                |--------------------------------------------------------------------------
                */

                $allocatedCO =
                    round(
                        (float) (
                            $plan['allocated_claim_adjustments']['CO']
                            ?? 0
                        ),
                        2
                    );


                if (
                    abs(
                        $allocatedCO
                    )
                    >=
                    0.005
                ) {

                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                            $claim->id,

                            'era_file_id' =>
                            $id,

                            'era_claim_id' =>
                            $eraClaim->id,

                            'claim_no' =>
                            $claim->claim_no,

                            'ParticipantId' =>
                            $claim->ParticipantId,

                            'payer_claim_number' =>
                            $eraClaim
                                ->payer_claim_number,

                            'transaction_date' =>
                            $file->payment_date
                                ??
                                now()
                                ->toDateString(),

                            'transaction_type' =>
                            'contractual_adjustment',

                            'amount' =>
                            $allocatedCO,

                            'group_code' =>
                            'CO',

                            'description' =>
                            'ERA allocated claim-level adjustment - line '
                                .
                                $claim->line_no,

                            'created_by' =>
                            $userCd,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Allocated PR
                |--------------------------------------------------------------------------
                */

                $allocatedPR =
                    round(
                        (float) (
                            $plan['allocated_claim_adjustments']['PR']
                            ?? 0
                        ),
                        2
                    );


                if (
                    abs(
                        $allocatedPR
                    )
                    >=
                    0.005
                ) {

                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                            $claim->id,

                            'era_file_id' =>
                            $id,

                            'era_claim_id' =>
                            $eraClaim->id,

                            'claim_no' =>
                            $claim->claim_no,

                            'ParticipantId' =>
                            $claim->ParticipantId,

                            'payer_claim_number' =>
                            $eraClaim
                                ->payer_claim_number,

                            'transaction_date' =>
                            $file->payment_date
                                ??
                                now()
                                ->toDateString(),

                            'transaction_type' =>
                            'patient_responsibility',

                            'amount' =>
                            $allocatedPR,

                            'group_code' =>
                            'PR',

                            'description' =>
                            'ERA allocated patient responsibility - line '
                                .
                                $claim->line_no,

                            'created_by' =>
                            $userCd,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Allocated Other Adjustment
                |--------------------------------------------------------------------------
                */

                $allocatedOther =
                    round(
                        (float) (
                            $plan['allocated_claim_adjustments']['OTHER']
                            ?? 0
                        ),
                        2
                    );


                if (
                    abs(
                        $allocatedOther
                    )
                    >=
                    0.005
                ) {

                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                            $claim->id,

                            'era_file_id' =>
                            $id,

                            'era_claim_id' =>
                            $eraClaim->id,

                            'claim_no' =>
                            $claim->claim_no,

                            'ParticipantId' =>
                            $claim->ParticipantId,

                            'payer_claim_number' =>
                            $eraClaim
                                ->payer_claim_number,

                            'transaction_date' =>
                            $file->payment_date
                                ??
                                now()
                                ->toDateString(),

                            'transaction_type' =>
                            'deduction',

                            'amount' =>
                            $allocatedOther,

                            'description' =>
                            'ERA allocated claim-level adjustment - line '
                                .
                                $claim->line_no,

                            'created_by' =>
                            $userCd,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Fallback CO Transaction
                |--------------------------------------------------------------------------
                |
                | If there was no actual/allocated CO but we calculated one.
                |--------------------------------------------------------------------------
                */

                $actualLineCO =
                    $plan['line_adjustments']
                    ->filter(
                        function ($adj) {

                            return
                                strtoupper(
                                    trim(
                                        (string) (
                                            $adj->group_code
                                            ?? ''
                                        )
                                    )
                                )
                                ===
                                'CO';
                        }
                    )
                    ->sum('amount');


                $recordedCO =
                    round(
                        (float) $actualLineCO
                            +
                            $allocatedCO,
                        2
                    );


                $fallbackCO =
                    round(
                        $contractualAdj
                            -
                            $recordedCO,
                        2
                    );


                if (
                    abs(
                        $fallbackCO
                    )
                    >=
                    0.005
                ) {

                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                            $claim->id,

                            'era_file_id' =>
                            $id,

                            'era_claim_id' =>
                            $eraClaim->id,

                            'claim_no' =>
                            $claim->claim_no,

                            'ParticipantId' =>
                            $claim->ParticipantId,

                            'payer_claim_number' =>
                            $eraClaim
                                ->payer_claim_number,

                            'transaction_date' =>
                            $file->payment_date
                                ??
                                now()
                                ->toDateString(),

                            'transaction_type' =>
                            'contractual_adjustment',

                            'amount' =>
                            $fallbackCO,

                            'group_code' =>
                            'CO',

                            'description' =>
                            'ERA calculated contractual adjustment - line '
                                .
                                $claim->line_no,

                            'created_by' =>
                            $userCd,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),

                        ]);
                }

                } // End non-denial financial adjustment posting


                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                */

                $postedLines++;


                $postedClaims[$eraClaim->id] =
                    true;


                $totalPaidPosted +=
                    $paidAmount;


                $totalAdjPosted +=
                    $contractualAdj
                    +
                    $otherAdj;


                $totalBalance +=
                    $balanceAmount;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Final Claim State Once Per ADC Claim Line
            |--------------------------------------------------------------------------
            */

            foreach ($claimFinalCandidates as $claimId => $candidates) {

                $candidates =
                    collect($candidates);


                /*
                |--------------------------------------------------------------------------
                | Separate Reversals From Normal Adjudications
                |--------------------------------------------------------------------------
                */

                $reversals =
                    $candidates
                    ->filter(function ($candidate) {

                        return
                            (string) (
                                $candidate['era_claim']
                                ->claim_status_code
                                ?? ''
                            )
                            ===
                            '22';
                    })
                    ->values();


                $normalCandidates =
                    $candidates
                    ->filter(function ($candidate) {

                        return
                            (string) (
                                $candidate['era_claim']
                                ->claim_status_code
                                ?? ''
                            )
                            !==
                            '22';
                    })
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | Cancel Matching Payment Against Reversal
                |--------------------------------------------------------------------------
                */

                $cancelledIndexes =
                    [];


                foreach ($reversals as $reversal) {

                    $reversalAmount =
                        abs(
                            round(
                                (float) (
                                    $reversal['paid_amount']
                                    ?? 0
                                ),
                                2
                            )
                        );


                    if ($reversalAmount < 0.005) {
                        continue;
                    }


                    foreach ($normalCandidates as $index => $candidate) {

                        if (
                            isset(
                                $cancelledIndexes[$index]
                            )
                        ) {
                            continue;
                        }


                        $candidatePaid =
                            round(
                                (float) (
                                    $candidate['paid_amount']
                                    ?? 0
                                ),
                                2
                            );


                        if (
                            $candidatePaid > 0
                            &&
                            abs(
                                $candidatePaid
                                    -
                                    $reversalAmount
                            )
                            <=
                            0.02
                        ) {

                            $cancelledIndexes[$index] =
                                true;

                            break;
                        }
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Remaining Effective Adjudications
                |--------------------------------------------------------------------------
                */

                $effectiveCandidates =
                    $normalCandidates
                    ->filter(function ($candidate, $index)
                    use ($cancelledIndexes) {

                        return
                            !isset(
                                $cancelledIndexes[$index]
                            );
                    })
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | Determine Final Candidate
                |--------------------------------------------------------------------------
                */

                $finalCandidate =
                    null;


                /*
                |--------------------------------------------------------------------------
                | Prefer Remaining Positive Payment
                |--------------------------------------------------------------------------
                */

                $paidCandidates =
                    $effectiveCandidates
                    ->filter(function ($candidate) {

                        return
                            (float) (
                                $candidate['paid_amount']
                                ?? 0
                            )
                            >
                            0.005;
                    })
                    ->values();


                if ($paidCandidates->isNotEmpty()) {

                    $finalCandidate =
                        $paidCandidates
                        ->sortByDesc(function ($candidate) {

                            return
                                (float) (
                                    $candidate['paid_amount']
                                    ?? 0
                                );
                        })
                        ->first();
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | If No Payment Remains, Prefer Denial
                    |--------------------------------------------------------------------------
                    */

                    $deniedCandidate =
                        $effectiveCandidates
                        ->first(function ($candidate) {

                            return
                                (string) (
                                    $candidate['era_claim']
                                    ->claim_status_code
                                    ?? ''
                                )
                                ===
                                '4';
                        });


                    if ($deniedCandidate) {

                        $finalCandidate =
                            $deniedCandidate;
                    } elseif ($effectiveCandidates->isNotEmpty()) {

                        $finalCandidate =
                            $effectiveCandidates
                            ->first();
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Pure Reversal / No Safe Final Adjudication
                |--------------------------------------------------------------------------
                */

                if (!$finalCandidate) {
                    continue;
                }


                $claim =
                    $finalCandidate['claim'];

                $eraClaim =
                    $finalCandidate['era_claim'];


                /*
                |--------------------------------------------------------------------------
                | Update Claim Once
                |--------------------------------------------------------------------------
                */

                DB::table('claims')
                    ->where(
                        'id',
                        $claimId
                    )
                    ->update([

                        'payer_claim_number' =>
                        $eraClaim
                            ->payer_claim_number,

                        'allowed' =>
                        round(
                            max(
                                (float) (
                                    $finalCandidate['paid_amount']
                                    ?? 0
                                )
                                    +
                                    (float) (
                                        $finalCandidate['patient_responsibility']
                                        ?? 0
                                    ),
                                0
                            ),
                            2
                        ),

                        'ins1_paid' =>
                        round(
                            (float) (
                                $finalCandidate['paid_amount']
                                ?? 0
                            ),
                            2
                        ),

                        'adjustment_amount' =>
                        round(
                            (float) (
                                $finalCandidate['contractual_adjustment']
                                ?? 0
                            ),
                            2
                        ),

                        'patient_responsibility' =>
                        round(
                            (float) (
                                $finalCandidate['patient_responsibility']
                                ?? 0
                            ),
                            2
                        ),

                        'deduction_amount' =>
                        round(
                            (float) (
                                $finalCandidate['other_adjustment']
                                ?? 0
                            ),
                            2
                        ),

                        'balance_amount' =>
                        round(
                            (float) (
                                $finalCandidate['balance_amount']
                                ?? 0
                            ),
                            2
                        ),

                        'paid_date' =>
                        $file->payment_date,

                        'era_posted_at' =>
                        now(),

                        'status' =>
                        $finalCandidate['status'],

                        'updated_at' =>
                        now(),

                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Auto Resolve / Archive Prior Denial Exceptions
            |--------------------------------------------------------------------------
            |
            | At this point all ADC claim rows have already received their final
            | financial state for this ERA.
            |
            | If this ERA contains a positive payment for a claim that was previously
            | denied / paid $0.00, and the current ADC balance is now zero, automatically
            | resolve and archive that denial exception.
            |--------------------------------------------------------------------------
            */

            $positiveEraClaims =
                $eraClaims

                ->filter(
                    function ($eraClaim) {

                        return
                            (float) (
                                $eraClaim->total_paid
                                ?? 0
                            )
                            >
                            0.005

                            &&

                            (string) (
                                $eraClaim->claim_status_code
                                ?? ''
                            )
                            !==
                            '22';
                    }
                )

                ->values();


            foreach ($positiveEraClaims as $paidEraClaim) {

                $this->autoArchiveResolvedDenialException(
                    $paidEraClaim,
                    $userCd
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Mark ERA Processed
            |--------------------------------------------------------------------------
            */

            $postedClaimCount =
                count(
                    $postedClaims
                );


            $unmatchedCount =
                count(
                    $unmatchedClaims
                );


            $statusMessage =
                "Posted {$postedClaimCount} claim(s), {$postedLines} service line(s).";


            if ($unmatchedCount > 0) {

                $statusMessage .=
                    " {$unmatchedCount} ERA claim(s) could not be posted safely and were moved to ERA Exceptions.";
            }


            DB::table('era_files')
                ->where(
                    'id',
                    $id
                )
                ->update([

                    'status' =>
                    'processed',

                    'status_message' =>
                    $statusMessage,

                    'processed_at' =>
                    now(),

                    'updated_at' =>
                    now(),

                ]);


            DB::commit();


            return redirect()
                ->route('era.index')
                ->with([

                    'message' =>
                    'ERA file processed. '
                        .
                        "Claims: {$postedClaimCount}, "
                        .
                        "Lines: {$postedLines}, "
                        .
                        "Exceptions: {$unmatchedCount}, "
                        .
                        'Paid: $'
                        .
                        number_format(
                            $totalPaidPosted,
                            2
                        )
                        .
                        ', Adjustments: $'
                        .
                        number_format(
                            $totalAdjPosted,
                            2
                        )
                        .
                        ', Balance: $'
                        .
                        number_format(
                            $totalBalance,
                            2
                        )
                        .
                        '.',

                    'alert-type' =>
                    $unmatchedCount > 0
                        ? 'warning'
                        : 'success',

                ]);
        } catch (\Throwable $e) {

            DB::rollBack();


            DB::table('era_files')
                ->where(
                    'id',
                    $id
                )
                ->update([

                    'status' =>
                    'error',

                    'status_message' =>
                    substr(
                        $e->getMessage(),
                        0,
                        1000
                    ),

                    'updated_at' =>
                    now(),

                ]);


            report($e);


            return redirect()
                ->route('era.index')
                ->with([

                    'message' =>
                    'Error processing ERA: '
                        .
                        $e->getMessage(),

                    'alert-type' =>
                    'danger',

                ]);
        }
    } // End Method


    // public function processEra($id)
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | ERA File
    //     |--------------------------------------------------------------------------
    //     */

    //     $file = DB::table('era_files')
    //         ->where(
    //             'id',
    //             $id
    //         )
    //         ->first();


    //     if (!$file) {

    //         abort(
    //             404,
    //             'ERA file not found'
    //         );
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Already Processed
    //     |--------------------------------------------------------------------------
    //     */

    //     if (
    //         $file->status === 'processed'
    //     ) {

    //         return redirect()
    //             ->route('era.index')
    //             ->with([

    //                 'message' =>
    //                 'This ERA file is already processed.',

    //                 'alert-type' =>
    //                 'info',

    //             ]);
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | User
    //     |--------------------------------------------------------------------------
    //     */

    //     $userCd =
    //         null;


    //     if (
    //         Auth::check()
    //         &&
    //         Auth::user()
    //     ) {

    //         $userCd =
    //             Auth::user()->user_cd
    //             ?? null;
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | ERA Claims
    //     |--------------------------------------------------------------------------
    //     */

    //     $eraClaims = DB::table('era_claims')
    //         ->where(
    //             'era_file_id',
    //             $id
    //         )
    //         ->orderBy('id')
    //         ->get();


    //     if (
    //         $eraClaims->isEmpty()
    //     ) {

    //         return redirect()
    //             ->route('era.index')
    //             ->with([

    //                 'message' =>
    //                 'This ERA file does not contain any claim records.',

    //                 'alert-type' =>
    //                 'warning',

    //             ]);
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Helpers
    //     |--------------------------------------------------------------------------
    //     */

    //     $moneyMatches =
    //         function (
    //             $a,
    //             $b,
    //             $tolerance = 0.02
    //         ) {

    //             return
    //                 abs(
    //                     round(
    //                         (float) $a,
    //                         2
    //                     )
    //                         -
    //                         round(
    //                             (float) $b,
    //                             2
    //                         )
    //                 )
    //                 <=
    //                 $tolerance;
    //         };


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Allocate Claim-Level Amount Across Claim Lines
    //     |--------------------------------------------------------------------------
    //     */

    //     $allocateAmount =
    //         function (
    //             $amount,
    //             $rows
    //         ) {

    //             $allocation =
    //                 [];


    //             $amount =
    //                 round(
    //                     (float) $amount,
    //                     2
    //                 );


    //             if (
    //                 $rows->isEmpty()
    //             ) {

    //                 return $allocation;
    //             }


    //             if (
    //                 abs($amount)
    //                 <
    //                 0.005
    //             ) {

    //                 foreach ($rows as $row) {

    //                     $allocation[$row->id] = 0.00;
    //                 }


    //                 return $allocation;
    //             }


    //             $weightTotal =
    //                 $rows->sum(
    //                     function ($row) {

    //                         return
    //                             max(
    //                                 (float) (
    //                                     $row->total_amount
    //                                     ?? 0
    //                                 ),
    //                                 0
    //                             );
    //                     }
    //                 );


    //             if (
    //                 $weightTotal <= 0
    //             ) {

    //                 $weightTotal =
    //                     $rows->count();


    //                 foreach ($rows as $row) {

    //                     $row->_era_weight =
    //                         1;
    //                 }
    //             } else {

    //                 foreach ($rows as $row) {

    //                     $row->_era_weight =
    //                         max(
    //                             (float) (
    //                                 $row->total_amount
    //                                 ?? 0
    //                             ),
    //                             0
    //                         );
    //                 }
    //             }


    //             $allocated =
    //                 0.00;


    //             $rows =
    //                 $rows->values();


    //             $lastIndex =
    //                 $rows->count()
    //                 -
    //                 1;


    //             foreach (
    //                 $rows as $index => $row
    //             ) {

    //                 if (
    //                     $index
    //                     ===
    //                     $lastIndex
    //                 ) {

    //                     $lineAmount =
    //                         round(
    //                             $amount
    //                                 -
    //                                 $allocated,
    //                             2
    //                         );
    //                 } else {

    //                     $lineAmount =
    //                         round(
    //                             $amount
    //                                 *
    //                                 (
    //                                     $row->_era_weight
    //                                     /
    //                                     $weightTotal
    //                                 ),
    //                             2
    //                         );


    //                     $allocated +=
    //                         $lineAmount;
    //                 }


    //                 $allocation[$row->id] =
    //                     $lineAmount;
    //             }


    //             return $allocation;
    //         };


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Build Posting Plan
    //     |--------------------------------------------------------------------------
    //     |
    //     | Nothing is posted until ALL claims can be safely matched.
    //     |--------------------------------------------------------------------------
    //     */

    //     $postingPlans =
    //         [];


    //     $validationErrors =
    //         [];

    //     $unmatchedClaims = [];


    //     foreach (
    //         $eraClaims as $eraClaim
    //     ) {

    //         $eraClaimNo = trim(
    //             (string) (
    //                 $eraClaim
    //                 ->patient_control_number
    //                 ?? ''
    //             )
    //         );


    //         if ($eraClaimNo === '') {

    //             $validationErrors[] =
    //                 "ERA Claim ID {$eraClaim->id}: Claim number is blank.";

    //             continue;
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Normalize Claim Number
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimNoForLookup =
    //             $eraClaimNo;


    //         if (
    //             ctype_digit(
    //                 $claimNoForLookup
    //             )
    //         ) {

    //             $claimNoForLookup =
    //                 str_pad(
    //                     $claimNoForLookup,
    //                     6,
    //                     '0',
    //                     STR_PAD_LEFT
    //                 );
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | ADC Claim Rows
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimRows = DB::table('claims')
    //             ->where(
    //                 'claim_no',
    //                 $claimNoForLookup
    //             )
    //             ->orderBy(
    //                 'line_no'
    //             )
    //             ->get();


    //         // if (
    //         //     $claimRows->isEmpty()
    //         // ) {

    //         //     $validationErrors[] =
    //         //         "ERA Claim # {$eraClaimNo}: ADC Claim # {$claimNoForLookup} was not found.";

    //         //     continue;
    //         // }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | ERA Service Lines
    //         |--------------------------------------------------------------------------
    //         */

    //         $serviceLines =
    //             DB::table('era_service_lines')
    //             ->where(
    //                 'era_claim_id',
    //                 $eraClaim->id
    //             )
    //             ->orderBy(
    //                 'svc_sequence'
    //             )
    //             ->get();


    //         /*
    //         |--------------------------------------------------------------------------
    //         | ERA CAS Adjustments
    //         |--------------------------------------------------------------------------
    //         */

    //         $adjustments =
    //             DB::table('era_adjustments')
    //             ->where(
    //                 'era_claim_id',
    //                 $eraClaim->id
    //             )
    //             ->orderBy('id')
    //             ->get();


    //         /*
    //         |--------------------------------------------------------------------------
    //         | No SVC Lines
    //         |--------------------------------------------------------------------------
    //         */

    //         if (
    //             $serviceLines->isEmpty()
    //         ) {

    //             if (
    //                 $claimRows->count()
    //                 !==
    //                 1
    //             ) {

    //                 $validationErrors[] =
    //                     "ERA Claim # {$eraClaimNo}: ERA has no SVC lines but ADC Claim # {$claimNoForLookup} contains "
    //                     .
    //                     $claimRows->count()
    //                     .
    //                     ' lines.';

    //                 continue;
    //             }


    //             $claim =
    //                 $claimRows->first();


    //             $paidAmount =
    //                 round(
    //                     (float) (
    //                         $eraClaim->total_paid
    //                         ?? 0
    //                     ),
    //                     2
    //                 );


    //             $patientResp =
    //                 round(
    //                     (float) (
    //                         $eraClaim
    //                         ->patient_responsibility
    //                         ?? 0
    //                     ),
    //                     2
    //                 );


    //             $contractualAdj =
    //                 0.00;


    //             $otherAdj =
    //                 0.00;


    //             foreach (
    //                 $adjustments as $adj
    //             ) {

    //                 $amount =
    //                     round(
    //                         (float) (
    //                             $adj->amount
    //                             ?? 0
    //                         ),
    //                         2
    //                     );


    //                 $group = strtoupper(
    //                     trim(
    //                         (string) (
    //                             $adj->group_code
    //                             ?? ''
    //                         )
    //                     )
    //                 );


    //                 if ($group === 'CO') {

    //                     $contractualAdj +=
    //                         $amount;
    //                 } elseif (
    //                     $group !== 'PR'
    //                 ) {

    //                     $otherAdj +=
    //                         $amount;
    //                 }
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Fallback Contractual Adjustment
    //             |--------------------------------------------------------------------------
    //             */

    //             if (
    //                 abs(
    //                     $contractualAdj
    //                 )
    //                 <
    //                 0.005
    //             ) {

    //                 $contractualAdj =
    //                     max(
    //                         round(
    //                             (float) $claim->total_amount
    //                                 -
    //                                 $paidAmount
    //                                 -
    //                                 $patientResp
    //                                 -
    //                                 $otherAdj,
    //                             2
    //                         ),
    //                         0
    //                     );
    //             }


    //             $postingPlans[] = [

    //                 'era_claim' =>
    //                 $eraClaim,

    //                 'claim' =>
    //                 $claim,

    //                 'paid_amount' =>
    //                 $paidAmount,

    //                 'patient_responsibility' =>
    //                 $patientResp,

    //                 'contractual_adjustment' =>
    //                 round(
    //                     $contractualAdj,
    //                     2
    //                 ),

    //                 'other_adjustment' =>
    //                 round(
    //                     $otherAdj,
    //                     2
    //                 ),

    //                 'line_adjustments' =>
    //                 $adjustments,

    //                 'allocated_claim_adjustments' =>
    //                 [],

    //             ];


    //             continue;
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Separate Claim-Level Adjustments
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimLevelAdjustments =
    //             $adjustments
    //             ->filter(
    //                 function ($adj) {

    //                     return
    //                         strtolower(
    //                             trim(
    //                                 (string) (
    //                                     $adj->level
    //                                     ?? ''
    //                                 )
    //                             )
    //                         )
    //                         ===
    //                         'claim';
    //                 }
    //             )
    //             ->values();


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Match SVC To Claims
    //         |--------------------------------------------------------------------------
    //         */

    //         $usedClaimIds =
    //             [];


    //         $matches =
    //             [];


    //         $claimHasError =
    //             false;


    //         foreach (
    //             $serviceLines as $svc
    //         ) {

    //             $svcSequence =
    //                 (int) (
    //                     $svc->svc_sequence
    //                     ?? 0
    //                 );


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Candidate Validation Function
    //             |--------------------------------------------------------------------------
    //             */

    //             $candidateMatches =
    //                 function ($row)
    //                 use (
    //                     $svc,
    //                     $moneyMatches,
    //                     $eraClaim
    //                 ) {

    //                     if (!$row) {
    //                         return false;
    //                     }


    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Procedure
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     $svcProc = strtoupper(
    //                         trim(
    //                             (string) (
    //                                 $svc
    //                                 ->procedure_code
    //                                 ?? ''
    //                             )
    //                         )
    //                     );


    //                     $claimProc = strtoupper(
    //                         trim(
    //                             (string) (
    //                                 $row->proc_code
    //                                 ?? ''
    //                             )
    //                         )
    //                     );


    //                     if (
    //                         $svcProc !== ''
    //                         &&
    //                         $claimProc !== ''
    //                         &&
    //                         $svcProc !== $claimProc
    //                     ) {

    //                         return false;
    //                     }


    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Modifier
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     $svcMod = strtoupper(
    //                         trim(
    //                             (string) (
    //                                 $svc->modifier1
    //                                 ?? ''
    //                             )
    //                         )
    //                     );


    //                     $claimMod = strtoupper(
    //                         trim(
    //                             (string) (
    //                                 $row->m1
    //                                 ?? ''
    //                             )
    //                         )
    //                     );


    //                     if (
    //                         $svcMod !== ''
    //                         &&
    //                         $claimMod !== ''
    //                         &&
    //                         $svcMod !== $claimMod
    //                     ) {

    //                         return false;
    //                     }


    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Service From
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     if (
    //                         !empty($svc->service_from)
    //                         &&
    //                         !empty($row->service_from)
    //                         &&
    //                         (string) $svc->service_from
    //                         !==
    //                         (string) $row->service_from
    //                     ) {

    //                         return false;
    //                     }


    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Service To
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     if (
    //                         !empty($svc->service_to)
    //                         &&
    //                         !empty($row->service_to)
    //                         &&
    //                         (string) $svc->service_to
    //                         !==
    //                         (string) $row->service_to
    //                     ) {

    //                         return false;
    //                     }


    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Charge Amount
    //                     |--------------------------------------------------------------------------
    //                     |
    //                     | CLP status 22 = reversal.
    //                     | Reversal ERA amounts may be negative while the original ADC
    //                     | claim amount remains positive.
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     if ($svc->charge_amount !== null) {

    //                         $svcCharge =
    //                             (float) $svc->charge_amount;


    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Reversal
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         if (
    //                             (string) (
    //                                 $eraClaim->claim_status_code
    //                                 ?? ''
    //                             )
    //                             ===
    //                             '22'
    //                         ) {

    //                             $svcCharge =
    //                                 abs($svcCharge);
    //                         }


    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Compare Against ADC Claim
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         if (
    //                             !$moneyMatches(
    //                                 $row->total_amount,
    //                                 $svcCharge
    //                             )
    //                         ) {

    //                             return false;
    //                         }
    //                     }


    //                     return true;
    //                 };


    //             /*
    //             |--------------------------------------------------------------------------
    //             | First Attempt:
    //             | claim_no + line_no = svc_sequence
    //             |--------------------------------------------------------------------------
    //             */

    //             $candidate =
    //                 $claimRows
    //                 ->first(
    //                     function ($row)
    //                     use (
    //                         $svcSequence,
    //                         $usedClaimIds
    //                     ) {

    //                         return
    //                             (int) $row->line_no
    //                             ===
    //                             $svcSequence
    //                             &&
    //                             !isset(
    //                                 $usedClaimIds[$row->id]
    //                             );
    //                     }
    //                 );


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Fallback Search
    //             |--------------------------------------------------------------------------
    //             */

    //             if (
    //                 !$candidate
    //                 ||
    //                 !$candidateMatches(
    //                     $candidate
    //                 )
    //             ) {

    //                 $possible =
    //                     $claimRows
    //                     ->filter(
    //                         function ($row)
    //                         use (
    //                             $usedClaimIds,
    //                             $candidateMatches
    //                         ) {

    //                             if (
    //                                 isset(
    //                                     $usedClaimIds[$row->id]
    //                                 )
    //                             ) {

    //                                 return false;
    //                             }


    //                             return
    //                                 $candidateMatches(
    //                                     $row
    //                                 );
    //                         }
    //                     )
    //                     ->values();


    //                 if (
    //                     $possible->count()
    //                     ===
    //                     1
    //                 ) {

    //                     $candidate =
    //                         $possible->first();
    //                 } else {

    //                     $validationErrors[] =
    //                         "ERA Claim # {$eraClaimNo}, SVC # {$svcSequence}: Could not safely match to ADC Claim # {$claimNoForLookup}.";

    //                     $claimHasError =
    //                         true;

    //                     continue;
    //                 }
    //             }


    //             $usedClaimIds[$candidate->id] =
    //                 true;


    //             $matches[] = [

    //                 'svc' =>
    //                 $svc,

    //                 'claim' =>
    //                 $candidate,

    //             ];
    //         }


    //         if ($claimHasError) {
    //             continue;
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Every ADC Claim Line Must Match
    //         |--------------------------------------------------------------------------
    //         */

    //         if (
    //             count(
    //                 $usedClaimIds
    //             )
    //             !==
    //             $claimRows->count()
    //         ) {

    //             $validationErrors[] =
    //                 "ERA Claim # {$eraClaimNo}: ERA matched "
    //                 .
    //                 count($usedClaimIds)
    //                 .
    //                 " service line(s), but ADC Claim # {$claimNoForLookup} contains "
    //                 .
    //                 $claimRows->count()
    //                 .
    //                 ' line(s).';

    //             continue;
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Claim-Level CAS Totals
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimCO =
    //             0.00;


    //         $claimPR =
    //             0.00;


    //         $claimOther =
    //             0.00;


    //         foreach (
    //             $claimLevelAdjustments as $adj
    //         ) {

    //             $amount =
    //                 round(
    //                     (float) (
    //                         $adj->amount
    //                         ?? 0
    //                     ),
    //                     2
    //                 );


    //             $group = strtoupper(
    //                 trim(
    //                     (string) (
    //                         $adj->group_code
    //                         ?? ''
    //                     )
    //                 )
    //             );


    //             if ($group === 'CO') {

    //                 $claimCO +=
    //                     $amount;
    //             } elseif (
    //                 $group === 'PR'
    //             ) {

    //                 $claimPR +=
    //                     $amount;
    //             } else {

    //                 $claimOther +=
    //                     $amount;
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Allocate Claim-Level Amounts
    //         |--------------------------------------------------------------------------
    //         */

    //         $allocationRows =
    //             collect(
    //                 array_map(
    //                     function ($match) {

    //                         return
    //                             $match['claim'];
    //                     },
    //                     $matches
    //                 )
    //             );


    //         $allocatedCO =
    //             $allocateAmount(
    //                 $claimCO,
    //                 $allocationRows
    //             );


    //         $allocatedOther =
    //             $allocateAmount(
    //                 $claimOther,
    //                 $allocationRows
    //             );


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Calculate Existing Line-Level PR
    //         |--------------------------------------------------------------------------
    //         */

    //         $linePrTotal =
    //             0.00;


    //         foreach ($matches as $match) {

    //             $svc =
    //                 $match['svc'];


    //             $lineAdjustments =
    //                 $adjustments
    //                 ->filter(
    //                     function ($adj)
    //                     use ($svc) {

    //                         return
    //                             strtolower(
    //                                 trim(
    //                                     (string) (
    //                                         $adj->level
    //                                         ?? ''
    //                                     )
    //                                 )
    //                             )
    //                             ===
    //                             'line'
    //                             &&
    //                             (int) (
    //                                 $adj->svc_sequence
    //                                 ?? 0
    //                             )
    //                             ===
    //                             (int) (
    //                                 $svc->svc_sequence
    //                                 ?? 0
    //                             );
    //                     }
    //                 );


    //             foreach (
    //                 $lineAdjustments as $adj
    //             ) {

    //                 if (
    //                     strtoupper(
    //                         trim(
    //                             (string) (
    //                                 $adj->group_code
    //                                 ?? ''
    //                             )
    //                         )
    //                     )
    //                     ===
    //                     'PR'
    //                 ) {

    //                     $linePrTotal +=
    //                         round(
    //                             (float) (
    //                                 $adj->amount
    //                                 ?? 0
    //                             ),
    //                             2
    //                         );
    //                 }
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Remaining PR To Allocate
    //         |--------------------------------------------------------------------------
    //         */

    //         if (
    //             abs(
    //                 $claimPR
    //             )
    //             >=
    //             0.005
    //         ) {

    //             $remainingPR =
    //                 $claimPR;
    //         } else {

    //             $remainingPR =
    //                 round(
    //                     (float) (
    //                         $eraClaim
    //                         ->patient_responsibility
    //                         ?? 0
    //                     )
    //                         -
    //                         $linePrTotal,
    //                     2
    //                 );
    //         }


    //         $allocatedPR =
    //             $allocateAmount(
    //                 $remainingPR,
    //                 $allocationRows
    //             );


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Build One Posting Plan Per SVC Line
    //         |--------------------------------------------------------------------------
    //         */

    //         foreach ($matches as $match) {

    //             $svc =
    //                 $match['svc'];


    //             $claim =
    //                 $match['claim'];


    //             $svcSequence =
    //                 (int) (
    //                     $svc->svc_sequence
    //                     ?? 0
    //                 );


    //             $lineAdjustments =
    //                 $adjustments
    //                 ->filter(
    //                     function ($adj)
    //                     use ($svcSequence) {

    //                         return
    //                             strtolower(
    //                                 trim(
    //                                     (string) (
    //                                         $adj->level
    //                                         ?? ''
    //                                     )
    //                                 )
    //                             )
    //                             ===
    //                             'line'
    //                             &&
    //                             (int) (
    //                                 $adj->svc_sequence
    //                                 ?? 0
    //                             )
    //                             ===
    //                             $svcSequence;
    //                     }
    //                 )
    //                 ->values();


    //             $lineCO =
    //                 0.00;


    //             $linePR =
    //                 0.00;


    //             $lineOther =
    //                 0.00;


    //             foreach (
    //                 $lineAdjustments as $adj
    //             ) {

    //                 $amount =
    //                     round(
    //                         (float) (
    //                             $adj->amount
    //                             ?? 0
    //                         ),
    //                         2
    //                     );


    //                 $group = strtoupper(
    //                     trim(
    //                         (string) (
    //                             $adj->group_code
    //                             ?? ''
    //                         )
    //                     )
    //                 );


    //                 if ($group === 'CO') {

    //                     $lineCO +=
    //                         $amount;
    //                 } elseif (
    //                     $group === 'PR'
    //                 ) {

    //                     $linePR +=
    //                         $amount;
    //                 } else {

    //                     $lineOther +=
    //                         $amount;
    //                 }
    //             }


    //             $paidAmount =
    //                 round(
    //                     (float) (
    //                         $svc->paid_amount
    //                         ?? 0
    //                     ),
    //                     2
    //                 );
    //             /*
    //             |--------------------------------------------------------------------------
    //             | Reversal Flag
    //             |--------------------------------------------------------------------------
    //             */

    //             $isReversal =
    //                 (string) (
    //                     $eraClaim->claim_status_code
    //                     ?? ''
    //                 )
    //                 ===
    //                 '22';


    //             $contractualAdj =
    //                 round(
    //                     $lineCO
    //                         +
    //                         (
    //                             $allocatedCO[$claim->id]
    //                             ?? 0
    //                         ),
    //                     2
    //                 );


    //             $patientResp =
    //                 round(
    //                     $linePR
    //                         +
    //                         (
    //                             $allocatedPR[$claim->id]
    //                             ?? 0
    //                         ),
    //                     2
    //                 );


    //             $otherAdj =
    //                 round(
    //                     $lineOther
    //                         +
    //                         (
    //                             $allocatedOther[$claim->id]
    //                             ?? 0
    //                         ),
    //                     2
    //                 );


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Fallback CO
    //             |--------------------------------------------------------------------------
    //             */

    //             if (
    //                 !$isReversal
    //                 &&
    //                 abs(
    //                     $contractualAdj
    //                 )
    //                 <
    //                 0.005
    //                 &&
    //                 abs(
    //                     $claimCO
    //                 )
    //                 <
    //                 0.005
    //             ) {

    //                 $fallback =
    //                     round(
    //                         (float) $claim->total_amount
    //                             -
    //                             $paidAmount
    //                             -
    //                             $patientResp
    //                             -
    //                             $otherAdj,
    //                         2
    //                     );


    //                 if ($fallback > 0) {

    //                     $contractualAdj =
    //                         $fallback;
    //                 }
    //             }

    //             $postingPlans[] = [

    //                 'era_claim' =>
    //                 $eraClaim,

    //                 'claim' =>
    //                 $claim,

    //                 'paid_amount' =>
    //                 $paidAmount,

    //                 'patient_responsibility' =>
    //                 $patientResp,

    //                 'contractual_adjustment' =>
    //                 $contractualAdj,

    //                 'other_adjustment' =>
    //                 $otherAdj,

    //                 'line_adjustments' =>
    //                 $lineAdjustments,

    //                 'allocated_claim_adjustments' => [

    //                     'CO' =>
    //                     $allocatedCO[$claim->id]
    //                         ?? 0,

    //                     'PR' =>
    //                     $allocatedPR[$claim->id]
    //                         ?? 0,

    //                     'OTHER' =>
    //                     $allocatedOther[$claim->id]
    //                         ?? 0,

    //                 ],

    //             ];
    //         }
    //     } // End ERA claim loop


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Stop Before Posting If Anything Failed Validation
    //     |--------------------------------------------------------------------------
    //     */

    //     if (
    //         !empty($validationErrors)
    //     ) {

    //         $message =
    //             implode(
    //                 ' | ',
    //                 $validationErrors
    //             );


    //         DB::table('era_files')
    //             ->where(
    //                 'id',
    //                 $id
    //             )
    //             ->update([

    //                 'status' =>
    //                 'ready',

    //                 'status_message' =>
    //                 substr(
    //                     $message,
    //                     0,
    //                     1000
    //                 ),

    //                 'updated_at' =>
    //                 now(),

    //             ]);


    //         return redirect()
    //             ->route('era.index')
    //             ->with([

    //                 'message' =>
    //                 'ERA was NOT posted. '
    //                     .
    //                     $message,

    //                 'alert-type' =>
    //                 'warning',

    //             ]);
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | POST ERA
    //     |--------------------------------------------------------------------------
    //     */

    //     DB::beginTransaction();


    //     try {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Remove Previous Transactions For This ERA
    //         |--------------------------------------------------------------------------
    //         */

    //         DB::table('claim_transactions')
    //             ->where(
    //                 'era_file_id',
    //                 $id
    //             )
    //             ->delete();


    //         $postedLines =
    //             0;


    //         $postedClaims =
    //             [];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Final Claim State Candidates
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimFinalCandidates =
    //             [];

    //         $totalPaidPosted =
    //             0.00;


    //         $totalAdjPosted =
    //             0.00;


    //         $totalBalance =
    //             0.00;


    //         foreach (
    //             $postingPlans as $plan
    //         ) {

    //             $eraClaim =
    //                 $plan['era_claim'];


    //             $claim =
    //                 $plan['claim'];


    //             $paidAmount =
    //                 round(
    //                     (float) $plan['paid_amount'],
    //                     2
    //                 );


    //             $patientResp =
    //                 round(
    //                     (float) $plan['patient_responsibility'],
    //                     2
    //                 );


    //             $contractualAdj =
    //                 round(
    //                     (float) $plan['contractual_adjustment'],
    //                     2
    //                 );


    //             $otherAdj =
    //                 round(
    //                     (float) $plan['other_adjustment'],
    //                     2
    //                 );


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Balance
    //             |--------------------------------------------------------------------------
    //             |
    //             | Patient responsibility intentionally remains in balance.
    //             |--------------------------------------------------------------------------
    //             */

    //             $balanceAmount =
    //                 round(
    //                     max(
    //                         (float) (
    //                             $claim->total_amount
    //                             ?? 0
    //                         )
    //                             -
    //                             $paidAmount
    //                             -
    //                             $contractualAdj
    //                             -
    //                             $otherAdj
    //                             -
    //                             (float) (
    //                                 $claim->writeoff_amount
    //                                 ?? 0
    //                             ),
    //                         0
    //                     ),
    //                     2
    //                 );


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Allowed
    //             |--------------------------------------------------------------------------
    //             */

    //             $allowedAmount =
    //                 round(
    //                     max(
    //                         $paidAmount
    //                             +
    //                             $patientResp,
    //                         0
    //                     ),
    //                     2
    //                 );


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Determine Whether This Service Line Contains CO-16
    //             |--------------------------------------------------------------------------
    //             |
    //             | CO-16 indicates that the claim/service lacks information or contains
    //             | a submission/billing issue that normally requires correction.
    //             |
    //             | In our ADC workflow, a zero-payment CO-16 line is marked rejected so
    //             | office staff can identify it for correction/resubmission rather than
    //             | treating it as a final paid or denied claim.
    //             |--------------------------------------------------------------------------
    //             */

    //             $hasCO16 = false;

    //             foreach (
    //                 $plan['line_adjustments']
    //                 as $adj
    //             ) {

    //                 $groupCode = strtoupper(
    //                     trim(
    //                         (string) (
    //                             $adj->group_code
    //                             ?? ''
    //                         )
    //                     )
    //                 );

    //                 $reasonCode = trim(
    //                     (string) (
    //                         $adj->reason_code
    //                         ?? ''
    //                     )
    //                 );

    //                 if (
    //                     $groupCode === 'CO'
    //                     &&
    //                     $reasonCode === '16'
    //                 ) {

    //                     $hasCO16 = true;

    //                     break;
    //                 }
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Claim Status
    //             |--------------------------------------------------------------------------
    //             |
    //             | Rules:
    //             |
    //             | 1. CLP status 4 = denied.
    //             |
    //             | 2. CO-16 = rejected so it can be corrected/resubmitted.
    //             |
    //             | 3. If payer paid $0.00 and the billed amount was completely removed by
    //             |    other payer adjustments, do NOT call the claim "paid".
    //             |
    //             | 4. Fully paid claims with an actual insurance payment = paid.
    //             |
    //             | 5. Remaining open balance = accepted.
    //             |--------------------------------------------------------------------------
    //             */

    //             if (
    //                 (string) (
    //                     $eraClaim->claim_status_code
    //                     ?? ''
    //                 )
    //                 ===
    //                 '4'
    //             ) {

    //                 $status =
    //                     'denied';
    //             } elseif (
    //                 $hasCO16
    //             ) {

    //                 $status =
    //                     'rejected';
    //             } elseif (
    //                 $paidAmount <= 0.005
    //                 &&
    //                 $balanceAmount <= 0.005
    //                 &&
    //                 (
    //                     $contractualAdj > 0.005
    //                     ||
    //                     $otherAdj > 0.005
    //                 )
    //             ) {

    //                 $status =
    //                     'denied';
    //             } elseif (
    //                 $paidAmount > 0.005
    //                 &&
    //                 $balanceAmount <= 0.005
    //             ) {

    //                 $status =
    //                     'paid';
    //             } else {

    //                 $status =
    //                     'accepted';
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Update Exact Claim Line
    //             |--------------------------------------------------------------------------
    //             */

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Save Final Claim Candidate
    //             |--------------------------------------------------------------------------
    //             |
    //             | Do not update the claims table yet.
    //             | The same ADC claim can appear multiple times in one ERA
    //             | as payment, reversal, denial, etc.
    //             |--------------------------------------------------------------------------
    //             */

    //             $claimFinalCandidates[$claim->id][] = [

    //                 'claim' =>
    //                 $claim,

    //                 'era_claim' =>
    //                 $eraClaim,

    //                 'paid_amount' =>
    //                 $paidAmount,

    //                 'patient_responsibility' =>
    //                 $patientResp,

    //                 'contractual_adjustment' =>
    //                 $contractualAdj,

    //                 'other_adjustment' =>
    //                 $otherAdj,

    //                 'balance_amount' =>
    //                 $balanceAmount,

    //                 'status' =>
    //                 $status,

    //             ];
    //             /*
    //             |--------------------------------------------------------------------------
    //             | Payment Transaction
    //             |--------------------------------------------------------------------------
    //             */

    //             if (
    //                 abs(
    //                     $paidAmount
    //                 )
    //                 >=
    //                 0.005
    //             ) {

    //                 DB::table('claim_transactions')
    //                     ->insert([

    //                         'claim_id' =>
    //                         $claim->id,

    //                         'era_file_id' =>
    //                         $id,

    //                         'era_claim_id' =>
    //                         $eraClaim->id,

    //                         'claim_no' =>
    //                         $claim->claim_no,

    //                         'ParticipantId' =>
    //                         $claim->ParticipantId,

    //                         'payer_claim_number' =>
    //                         $eraClaim
    //                             ->payer_claim_number,

    //                         'transaction_date' =>
    //                         $file->payment_date
    //                             ??
    //                             now()
    //                             ->toDateString(),

    //                         //'transaction_type' =>
    //                         //'payment',

    //                         'transaction_type' => (
    //                             (string) (
    //                                 $eraClaim->claim_status_code
    //                                 ?? ''
    //                             )
    //                             ===
    //                             '22'
    //                         )
    //                             ? 'reversal'
    //                             : 'payment',

    //                         'amount' =>
    //                         $paidAmount,

    //                         'description' =>
    //                         'ERA insurance payment - line '
    //                             .
    //                             $claim->line_no,

    //                         'created_by' =>
    //                         $userCd,

    //                         'created_at' =>
    //                         now(),

    //                         'updated_at' =>
    //                         now(),

    //                     ]);
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Actual Line-Level CAS Transactions
    //             |--------------------------------------------------------------------------
    //             */

    //             foreach (
    //                 $plan['line_adjustments']
    //                 as $adj
    //             ) {

    //                 $group = strtoupper(
    //                     trim(
    //                         (string) (
    //                             $adj->group_code
    //                             ?? ''
    //                         )
    //                     )
    //                 );


    //                 $transactionType =
    //                     'deduction';


    //                 if ($group === 'CO') {

    //                     $transactionType =
    //                         'contractual_adjustment';
    //                 } elseif (
    //                     $group === 'PR'
    //                 ) {

    //                     $transactionType =
    //                         'patient_responsibility';
    //                 }


    //                 DB::table('claim_transactions')
    //                     ->insert([

    //                         'claim_id' =>
    //                         $claim->id,

    //                         'era_file_id' =>
    //                         $id,

    //                         'era_claim_id' =>
    //                         $eraClaim->id,

    //                         'claim_no' =>
    //                         $claim->claim_no,

    //                         'ParticipantId' =>
    //                         $claim->ParticipantId,

    //                         'payer_claim_number' =>
    //                         $eraClaim
    //                             ->payer_claim_number,

    //                         'transaction_date' =>
    //                         $file->payment_date
    //                             ??
    //                             now()
    //                             ->toDateString(),

    //                         'transaction_type' =>
    //                         $transactionType,

    //                         'amount' =>
    //                         round(
    //                             (float) (
    //                                 $adj->amount
    //                                 ?? 0
    //                             ),
    //                             2
    //                         ),

    //                         'group_code' =>
    //                         $adj->group_code,

    //                         'reason_code' =>
    //                         $adj->reason_code,

    //                         'description' =>
    //                         'ERA CAS adjustment - line '
    //                             .
    //                             $claim->line_no,

    //                         'created_by' =>
    //                         $userCd,

    //                         'created_at' =>
    //                         now(),

    //                         'updated_at' =>
    //                         now(),

    //                     ]);
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Allocated Claim-Level CO
    //             |--------------------------------------------------------------------------
    //             */

    //             $allocatedCO =
    //                 round(
    //                     (float) (
    //                         $plan['allocated_claim_adjustments']['CO']
    //                         ?? 0
    //                     ),
    //                     2
    //                 );


    //             if (
    //                 abs(
    //                     $allocatedCO
    //                 )
    //                 >=
    //                 0.005
    //             ) {

    //                 DB::table('claim_transactions')
    //                     ->insert([

    //                         'claim_id' =>
    //                         $claim->id,

    //                         'era_file_id' =>
    //                         $id,

    //                         'era_claim_id' =>
    //                         $eraClaim->id,

    //                         'claim_no' =>
    //                         $claim->claim_no,

    //                         'ParticipantId' =>
    //                         $claim->ParticipantId,

    //                         'payer_claim_number' =>
    //                         $eraClaim
    //                             ->payer_claim_number,

    //                         'transaction_date' =>
    //                         $file->payment_date
    //                             ??
    //                             now()
    //                             ->toDateString(),

    //                         'transaction_type' =>
    //                         'contractual_adjustment',

    //                         'amount' =>
    //                         $allocatedCO,

    //                         'group_code' =>
    //                         'CO',

    //                         'description' =>
    //                         'ERA allocated claim-level adjustment - line '
    //                             .
    //                             $claim->line_no,

    //                         'created_by' =>
    //                         $userCd,

    //                         'created_at' =>
    //                         now(),

    //                         'updated_at' =>
    //                         now(),

    //                     ]);
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Allocated PR
    //             |--------------------------------------------------------------------------
    //             */

    //             $allocatedPR =
    //                 round(
    //                     (float) (
    //                         $plan['allocated_claim_adjustments']['PR']
    //                         ?? 0
    //                     ),
    //                     2
    //                 );


    //             if (
    //                 abs(
    //                     $allocatedPR
    //                 )
    //                 >=
    //                 0.005
    //             ) {

    //                 DB::table('claim_transactions')
    //                     ->insert([

    //                         'claim_id' =>
    //                         $claim->id,

    //                         'era_file_id' =>
    //                         $id,

    //                         'era_claim_id' =>
    //                         $eraClaim->id,

    //                         'claim_no' =>
    //                         $claim->claim_no,

    //                         'ParticipantId' =>
    //                         $claim->ParticipantId,

    //                         'payer_claim_number' =>
    //                         $eraClaim
    //                             ->payer_claim_number,

    //                         'transaction_date' =>
    //                         $file->payment_date
    //                             ??
    //                             now()
    //                             ->toDateString(),

    //                         'transaction_type' =>
    //                         'patient_responsibility',

    //                         'amount' =>
    //                         $allocatedPR,

    //                         'group_code' =>
    //                         'PR',

    //                         'description' =>
    //                         'ERA allocated patient responsibility - line '
    //                             .
    //                             $claim->line_no,

    //                         'created_by' =>
    //                         $userCd,

    //                         'created_at' =>
    //                         now(),

    //                         'updated_at' =>
    //                         now(),

    //                     ]);
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Allocated Other Adjustment
    //             |--------------------------------------------------------------------------
    //             */

    //             $allocatedOther =
    //                 round(
    //                     (float) (
    //                         $plan['allocated_claim_adjustments']['OTHER']
    //                         ?? 0
    //                     ),
    //                     2
    //                 );


    //             if (
    //                 abs(
    //                     $allocatedOther
    //                 )
    //                 >=
    //                 0.005
    //             ) {

    //                 DB::table('claim_transactions')
    //                     ->insert([

    //                         'claim_id' =>
    //                         $claim->id,

    //                         'era_file_id' =>
    //                         $id,

    //                         'era_claim_id' =>
    //                         $eraClaim->id,

    //                         'claim_no' =>
    //                         $claim->claim_no,

    //                         'ParticipantId' =>
    //                         $claim->ParticipantId,

    //                         'payer_claim_number' =>
    //                         $eraClaim
    //                             ->payer_claim_number,

    //                         'transaction_date' =>
    //                         $file->payment_date
    //                             ??
    //                             now()
    //                             ->toDateString(),

    //                         'transaction_type' =>
    //                         'deduction',

    //                         'amount' =>
    //                         $allocatedOther,

    //                         'description' =>
    //                         'ERA allocated claim-level adjustment - line '
    //                             .
    //                             $claim->line_no,

    //                         'created_by' =>
    //                         $userCd,

    //                         'created_at' =>
    //                         now(),

    //                         'updated_at' =>
    //                         now(),

    //                     ]);
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Fallback CO Transaction
    //             |--------------------------------------------------------------------------
    //             |
    //             | If there was no actual/allocated CO but we calculated one.
    //             |--------------------------------------------------------------------------
    //             */

    //             $actualLineCO =
    //                 $plan['line_adjustments']
    //                 ->filter(
    //                     function ($adj) {

    //                         return
    //                             strtoupper(
    //                                 trim(
    //                                     (string) (
    //                                         $adj->group_code
    //                                         ?? ''
    //                                     )
    //                                 )
    //                             )
    //                             ===
    //                             'CO';
    //                     }
    //                 )
    //                 ->sum('amount');


    //             $recordedCO =
    //                 round(
    //                     (float) $actualLineCO
    //                         +
    //                         $allocatedCO,
    //                     2
    //                 );


    //             $fallbackCO =
    //                 round(
    //                     $contractualAdj
    //                         -
    //                         $recordedCO,
    //                     2
    //                 );


    //             if (
    //                 abs(
    //                     $fallbackCO
    //                 )
    //                 >=
    //                 0.005
    //             ) {

    //                 DB::table('claim_transactions')
    //                     ->insert([

    //                         'claim_id' =>
    //                         $claim->id,

    //                         'era_file_id' =>
    //                         $id,

    //                         'era_claim_id' =>
    //                         $eraClaim->id,

    //                         'claim_no' =>
    //                         $claim->claim_no,

    //                         'ParticipantId' =>
    //                         $claim->ParticipantId,

    //                         'payer_claim_number' =>
    //                         $eraClaim
    //                             ->payer_claim_number,

    //                         'transaction_date' =>
    //                         $file->payment_date
    //                             ??
    //                             now()
    //                             ->toDateString(),

    //                         'transaction_type' =>
    //                         'contractual_adjustment',

    //                         'amount' =>
    //                         $fallbackCO,

    //                         'group_code' =>
    //                         'CO',

    //                         'description' =>
    //                         'ERA calculated contractual adjustment - line '
    //                             .
    //                             $claim->line_no,

    //                         'created_by' =>
    //                         $userCd,

    //                         'created_at' =>
    //                         now(),

    //                         'updated_at' =>
    //                         now(),

    //                     ]);
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Totals
    //             |--------------------------------------------------------------------------
    //             */

    //             $postedLines++;


    //             $postedClaims[$eraClaim->id] =
    //                 true;


    //             $totalPaidPosted +=
    //                 $paidAmount;


    //             $totalAdjPosted +=
    //                 $contractualAdj
    //                 +
    //                 $otherAdj;


    //             $totalBalance +=
    //                 $balanceAmount;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Update Final Claim State Once Per ADC Claim Line
    //         |--------------------------------------------------------------------------
    //         */

    //         foreach ($claimFinalCandidates as $claimId => $candidates) {

    //             $candidates =
    //                 collect($candidates);


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Separate Reversals From Normal Adjudications
    //             |--------------------------------------------------------------------------
    //             */

    //             $reversals =
    //                 $candidates
    //                 ->filter(function ($candidate) {

    //                     return
    //                         (string) (
    //                             $candidate['era_claim']
    //                             ->claim_status_code
    //                             ?? ''
    //                         )
    //                         ===
    //                         '22';
    //                 })
    //                 ->values();


    //             $normalCandidates =
    //                 $candidates
    //                 ->filter(function ($candidate) {

    //                     return
    //                         (string) (
    //                             $candidate['era_claim']
    //                             ->claim_status_code
    //                             ?? ''
    //                         )
    //                         !==
    //                         '22';
    //                 })
    //                 ->values();


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Cancel Matching Payment Against Reversal
    //             |--------------------------------------------------------------------------
    //             */

    //             $cancelledIndexes =
    //                 [];


    //             foreach ($reversals as $reversal) {

    //                 $reversalAmount =
    //                     abs(
    //                         round(
    //                             (float) (
    //                                 $reversal['paid_amount']
    //                                 ?? 0
    //                             ),
    //                             2
    //                         )
    //                     );


    //                 if ($reversalAmount < 0.005) {
    //                     continue;
    //                 }


    //                 foreach ($normalCandidates as $index => $candidate) {

    //                     if (
    //                         isset(
    //                             $cancelledIndexes[$index]
    //                         )
    //                     ) {
    //                         continue;
    //                     }


    //                     $candidatePaid =
    //                         round(
    //                             (float) (
    //                                 $candidate['paid_amount']
    //                                 ?? 0
    //                             ),
    //                             2
    //                         );


    //                     if (
    //                         $candidatePaid > 0
    //                         &&
    //                         abs(
    //                             $candidatePaid
    //                                 -
    //                                 $reversalAmount
    //                         )
    //                         <=
    //                         0.02
    //                     ) {

    //                         $cancelledIndexes[$index] =
    //                             true;

    //                         break;
    //                     }
    //                 }
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Remaining Effective Adjudications
    //             |--------------------------------------------------------------------------
    //             */

    //             $effectiveCandidates =
    //                 $normalCandidates
    //                 ->filter(function ($candidate, $index)
    //                 use ($cancelledIndexes) {

    //                     return
    //                         !isset(
    //                             $cancelledIndexes[$index]
    //                         );
    //                 })
    //                 ->values();


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Determine Final Candidate
    //             |--------------------------------------------------------------------------
    //             */

    //             $finalCandidate =
    //                 null;


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Prefer Remaining Positive Payment
    //             |--------------------------------------------------------------------------
    //             */

    //             $paidCandidates =
    //                 $effectiveCandidates
    //                 ->filter(function ($candidate) {

    //                     return
    //                         (float) (
    //                             $candidate['paid_amount']
    //                             ?? 0
    //                         )
    //                         >
    //                         0.005;
    //                 })
    //                 ->values();


    //             if ($paidCandidates->isNotEmpty()) {

    //                 $finalCandidate =
    //                     $paidCandidates
    //                     ->sortByDesc(function ($candidate) {

    //                         return
    //                             (float) (
    //                                 $candidate['paid_amount']
    //                                 ?? 0
    //                             );
    //                     })
    //                     ->first();
    //             } else {

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | If No Payment Remains, Prefer Denial
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $deniedCandidate =
    //                     $effectiveCandidates
    //                     ->first(function ($candidate) {

    //                         return
    //                             (string) (
    //                                 $candidate['era_claim']
    //                                 ->claim_status_code
    //                                 ?? ''
    //                             )
    //                             ===
    //                             '4';
    //                     });


    //                 if ($deniedCandidate) {

    //                     $finalCandidate =
    //                         $deniedCandidate;
    //                 } elseif ($effectiveCandidates->isNotEmpty()) {

    //                     $finalCandidate =
    //                         $effectiveCandidates
    //                         ->first();
    //                 }
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Pure Reversal / No Safe Final Adjudication
    //             |--------------------------------------------------------------------------
    //             */

    //             if (!$finalCandidate) {
    //                 continue;
    //             }


    //             $claim =
    //                 $finalCandidate['claim'];

    //             $eraClaim =
    //                 $finalCandidate['era_claim'];


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Update Claim Once
    //             |--------------------------------------------------------------------------
    //             */

    //             DB::table('claims')
    //                 ->where(
    //                     'id',
    //                     $claimId
    //                 )
    //                 ->update([

    //                     'payer_claim_number' =>
    //                     $eraClaim
    //                         ->payer_claim_number,

    //                     'allowed' =>
    //                     round(
    //                         max(
    //                             (float) (
    //                                 $finalCandidate['paid_amount']
    //                                 ?? 0
    //                             )
    //                                 +
    //                                 (float) (
    //                                     $finalCandidate['patient_responsibility']
    //                                     ?? 0
    //                                 ),
    //                             0
    //                         ),
    //                         2
    //                     ),

    //                     'ins1_paid' =>
    //                     round(
    //                         (float) (
    //                             $finalCandidate['paid_amount']
    //                             ?? 0
    //                         ),
    //                         2
    //                     ),

    //                     'adjustment_amount' =>
    //                     round(
    //                         (float) (
    //                             $finalCandidate['contractual_adjustment']
    //                             ?? 0
    //                         ),
    //                         2
    //                     ),

    //                     'patient_responsibility' =>
    //                     round(
    //                         (float) (
    //                             $finalCandidate['patient_responsibility']
    //                             ?? 0
    //                         ),
    //                         2
    //                     ),

    //                     'deduction_amount' =>
    //                     round(
    //                         (float) (
    //                             $finalCandidate['other_adjustment']
    //                             ?? 0
    //                         ),
    //                         2
    //                     ),

    //                     'balance_amount' =>
    //                     round(
    //                         (float) (
    //                             $finalCandidate['balance_amount']
    //                             ?? 0
    //                         ),
    //                         2
    //                     ),

    //                     'paid_date' =>
    //                     $file->payment_date,

    //                     'era_posted_at' =>
    //                     now(),

    //                     'status' =>
    //                     $finalCandidate['status'],

    //                     'updated_at' =>
    //                     now(),

    //                 ]);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Mark ERA Processed
    //         |--------------------------------------------------------------------------
    //         */

    //         $postedClaimCount =
    //             count(
    //                 $postedClaims
    //             );


    //         DB::table('era_files')
    //             ->where(
    //                 'id',
    //                 $id
    //             )
    //             ->update([

    //                 'status' =>
    //                 'processed',

    //                 'status_message' =>
    //                 "Posted {$postedClaimCount} claim(s), {$postedLines} service line(s).",

    //                 'processed_at' =>
    //                 now(),

    //                 'updated_at' =>
    //                 now(),

    //             ]);


    //         DB::commit();


    //         return redirect()
    //             ->route('era.index')
    //             ->with([

    //                 'message' =>
    //                 'ERA file processed. '
    //                     .
    //                     "Claims: {$postedClaimCount}, "
    //                     .
    //                     "Lines: {$postedLines}, "
    //                     .
    //                     'Paid: $'
    //                     .
    //                     number_format(
    //                         $totalPaidPosted,
    //                         2
    //                     )
    //                     .
    //                     ', Adjustments: $'
    //                     .
    //                     number_format(
    //                         $totalAdjPosted,
    //                         2
    //                     )
    //                     .
    //                     ', Balance: $'
    //                     .
    //                     number_format(
    //                         $totalBalance,
    //                         2
    //                     )
    //                     .
    //                     '.',

    //                 'alert-type' =>
    //                 'success',

    //             ]);
    //     } catch (\Throwable $e) {

    //         DB::rollBack();


    //         DB::table('era_files')
    //             ->where(
    //                 'id',
    //                 $id
    //             )
    //             ->update([

    //                 'status' =>
    //                 'error',

    //                 'status_message' =>
    //                 substr(
    //                     $e->getMessage(),
    //                     0,
    //                     1000
    //                 ),

    //                 'updated_at' =>
    //                 now(),

    //             ]);


    //         report($e);


    //         return redirect()
    //             ->route('era.index')
    //             ->with([

    //                 'message' =>
    //                 'Error processing ERA: '
    //                     .
    //                     $e->getMessage(),

    //                 'alert-type' =>
    //                 'danger',

    //             ]);
    //     }
    // } // End Method

    public function showClaim($id)
    {
        $clpStatusDescriptions = [
            '1'  => 'Processed as Primary',
            '2'  => 'Processed as Secondary',
            '3'  => 'Processed as Tertiary',
            '4'  => 'Denied',
            '19' => 'Processed as Predetermination',
            '20' => 'Pended / Suspended',
            '21' => 'Rejected',
            '22' => 'Reversal / Takeback',
            '23' => 'Supplemental Payment',
        ];
        $claim = DB::table('era_claims')->where('id', $id)->first();
        if (!$claim) {
            abort(404, 'ERA claim not found');
        }

        $file = DB::table('era_files')->where('id', $claim->era_file_id)->first();

        $member = DB::table('billing_members')->where('ParticipantId', $claim->member_id)->first();


        $serviceLines = DB::table('era_service_lines')
            ->where('era_claim_id', $id)
            ->orderBy('svc_sequence')
            ->get();

        $adjustments = DB::table('era_adjustments')
            ->where('era_claim_id', $id)
            ->orderBy('level')
            ->orderBy('svc_sequence')
            ->get();

        // 1) Load all remarks for this claim
        $remarks = DB::table('era_remarks')
            ->where('era_claim_id', $id)
            ->orderBy('level')
            ->orderBy('svc_sequence')
            ->get();

        // 2) Load remark code descriptions from era_remark_codes (one small lookup)
        $remarkCodes = DB::table('era_remark_codes')
            ->where('active', 1)
            ->get()
            ->keyBy('code');

        // 3) Attach descriptions and group by svc_sequence (like adjustments)
        $remarksBySvc = [];

        foreach ($remarks as $r) {
            $info = $remarkCodes[$r->code] ?? null;
            $r->short_desc = $info->short_desc ?? '';
            $r->long_desc  = $info->long_desc ?? '';

            $key = $r->svc_sequence ?? 'claim';
            $remarksBySvc[$key][] = $r;
        }

        // Fetch reason descriptions in one go
        $reasonCodes = DB::table('era_reason_codes')
            ->where('active', 1)
            ->get()
            ->groupBy(function ($row) {
                return $row->group_code . ':' . $row->reason_code;
            });

        $adjBySvc = [];

        foreach ($adjustments as $adj) {

            // Attach reason descriptions
            $lookupKey = $adj->group_code . ':' . $adj->reason_code;
            $adj->short_desc = $reasonCodes[$lookupKey][0]->short_desc ?? '';
            $adj->long_desc  = $reasonCodes[$lookupKey][0]->long_desc ?? '';

            // Group by SVC sequence
            $svcKey = $adj->svc_sequence ?? 'claim';
            $adjBySvc[$svcKey][] = $adj;
        }

        $statusDesc = $clpStatusDescriptions[$claim->claim_status_code] ?? 'Unknown';

        return view('era.era_claim_show', compact('claim', 'file', 'serviceLines', 'adjBySvc', 'remarksBySvc', 'statusDesc', 'member'));
    } // End Method

    public function showFile($id)
    {
        $file = DB::table('era_files')
            ->where('id', $id)
            ->first();

        if (!$file) {
            abort(404, 'ERA file not found');
        }

        $claims = DB::table('era_claims')
            ->where('era_file_id', $id)
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load Member Names (No JOIN)
        |--------------------------------------------------------------------------
        */

        $memberIds = $claims
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();

        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $memberIds)
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName'
            )
            ->get()
            ->keyBy('ParticipantId');

        /*
        |--------------------------------------------------------------------------
        | Claim Status Mapping - CLP02
        |--------------------------------------------------------------------------
        */

        $statusMap = [

            '1' => [
                'text'  => 'Processed as Primary',
                'class' => 'success',
            ],

            '2' => [
                'text'  => 'Processed as Secondary',
                'class' => 'info',
            ],

            '3' => [
                'text'  => 'Processed as Tertiary',
                'class' => 'info',
            ],

            '4' => [
                'text'  => 'Denied',
                'class' => 'danger',
            ],

            '19' => [
                'text'  => 'Processed as Primary - Forwarding Expected',
                'class' => 'warning',
            ],

            '20' => [
                'text'  => 'Processed as Secondary - Forwarding Expected',
                'class' => 'warning',
            ],

            '21' => [
                'text'  => 'Processed as Tertiary - Forwarding Expected',
                'class' => 'warning',
            ],

            '22' => [
                'text'  => 'Reversal of Previous Payment',
                'class' => 'dark',
            ],

            '23' => [
                'text'  => 'Not Our Claim - Forwarded to Another Payer',
                'class' => 'secondary',
            ],

            '25' => [
                'text'  => 'Predetermination Pricing Only',
                'class' => 'info',
            ],

        ];

        /*
        |--------------------------------------------------------------------------
        | Attach Member Names + Friendly Status
        |--------------------------------------------------------------------------
        */

        foreach ($claims as $claim) {

            $claim->FirstName = '';
            $claim->LastName  = '';

            if (
                !empty($claim->member_id) &&
                isset($members[$claim->member_id])
            ) {

                $claim->FirstName =
                    $members[$claim->member_id]->FirstName ?? '';

                $claim->LastName =
                    $members[$claim->member_id]->LastName ?? '';
            }

            $status = $statusMap[$claim->claim_status_code]
                ?? [
                    'text'  => 'Unknown',
                    'class' => 'secondary',
                ];

            $claim->status_text  = $status['text'];
            $claim->status_class = $status['class'];
        }

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totCharge = $claims->sum('total_charge');
        $totPaid   = $claims->sum('total_paid');
        $totPR     = $claims->sum('patient_responsibility');

        return view(
            'era.era_file_show',
            compact(
                'file',
                'claims',
                'totCharge',
                'totPaid',
                'totPR'
            )
        );
    } // End Method

    public function medisoftPaymentImportForm()
    {
        return view(
            'era.era_medisoft_payment_import'
        );
    } // End Method


    public function medisoftPaymentImportPreview(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Upload
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:20480',
            ],
        ]);


        $file = $request->file('csv_file');


        if (!$file || !$file->isValid()) {

            return redirect()
                ->route('era.medisoft.payments.form')
                ->with(
                    'error',
                    'Invalid CSV file.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Open CSV
        |--------------------------------------------------------------------------
        */

        $handle = fopen(
            $file->getRealPath(),
            'r'
        );


        if ($handle === false) {

            return redirect()
                ->route('era.medisoft.payments.form')
                ->with(
                    'error',
                    'Unable to open CSV file.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Read Header
        |--------------------------------------------------------------------------
        */

        $header = fgetcsv($handle);


        if (!$header) {

            fclose($handle);

            return redirect()
                ->route('era.medisoft.payments.form')
                ->with(
                    'error',
                    'CSV file does not contain a header row.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Header Names
        |--------------------------------------------------------------------------
        |
        | Examples:
        |
        | Item               -> item
        | Date From          -> date_from
        | Procedure Code     -> procedure_code
        | Check Number       -> check_number
        | Unapplied Amount   -> unapplied_amount
        |--------------------------------------------------------------------------
        */

        $header = array_map(
            function ($column) {

                $column =
                    strtolower(
                        trim(
                            (string) $column
                        )
                    );


                $column = str_replace(
                    [
                        ' ',
                        '-',
                        '/',
                        '\\',
                    ],
                    '_',
                    $column
                );


                $column = preg_replace(
                    '/[^a-z0-9_]/',
                    '',
                    $column
                );


                $column = preg_replace(
                    '/_+/',
                    '_',
                    $column
                );


                return trim(
                    $column,
                    '_'
                );
            },
            $header
        );


        /*
        |--------------------------------------------------------------------------
        | Required Columns
        |--------------------------------------------------------------------------
        */

        $requiredColumns = [
            'item',
            'id',
            'date_from',
            'procedure_code',
            'amount',
        ];


        $missingColumns = [];


        foreach ($requiredColumns as $column) {

            if (
                !in_array(
                    $column,
                    $header,
                    true
                )
            ) {

                $missingColumns[] =
                    $column;
            }
        }


        if (!empty($missingColumns)) {

            fclose($handle);

            return redirect()
                ->route('era.medisoft.payments.form')
                ->with(
                    'error',
                    'Missing required CSV column(s): '
                        .
                        implode(
                            ', ',
                            $missingColumns
                        )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Preload Billing Members
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Historical payments may belong to inactive/former members.
        | Therefore DO NOT filter active=1 here.
        |--------------------------------------------------------------------------
        */

        $members = DB::table(
            'billing_members'
        )
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName'
            )
            ->get()
            ->keyBy(
                function ($member) {

                    $id =
                        trim(
                            (string)
                            $member->ParticipantId
                        );


                    if ($id === '') {

                        return '';
                    }


                    if (is_numeric($id)) {

                        return (string) (
                            (int) $id
                        );
                    }


                    $id =
                        ltrim(
                            $id,
                            '0'
                        );


                    return $id === ''
                        ? '0'
                        : $id;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Existing Medisoft Item Numbers
        |--------------------------------------------------------------------------
        */

        $existingItems = [];


        DB::table(
            'medisoft_payment_history'
        )
            ->select(
                'id',
                'medisoft_item_no'
            )
            ->orderBy('id')
            ->chunkById(
                2000,
                function ($records) use (&$existingItems) {

                    foreach ($records as $record) {

                        $existingItems[(string)
                        $record->medisoft_item_no] = true;
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Preview Rows
        |--------------------------------------------------------------------------
        */

        $previewRows = [];

        $csvItemNumbers = [];

        $csvRowNumber = 1;


        while (
            ($data = fgetcsv($handle))
            !== false
        ) {

            $csvRowNumber++;


            /*
            |--------------------------------------------------------------------------
            | Skip Completely Blank Row
            |--------------------------------------------------------------------------
            */

            $hasValue = false;


            foreach ($data as $value) {

                if (
                    trim(
                        (string) $value
                    ) !== ''
                ) {

                    $hasValue = true;

                    break;
                }
            }


            if (!$hasValue) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Match Row Length
            |--------------------------------------------------------------------------
            */

            if (
                count($data)
                <
                count($header)
            ) {

                $data = array_pad(
                    $data,
                    count($header),
                    null
                );
            } elseif (
                count($data)
                >
                count($header)
            ) {

                $data = array_slice(
                    $data,
                    0,
                    count($header)
                );
            }


            $row = array_combine(
                $header,
                $data
            );


            if (!$row) {

                continue;
            }


            $errors   = [];
            $warnings = [];


            /*
            |--------------------------------------------------------------------------
            | Medisoft Item
            |--------------------------------------------------------------------------
            */

            $itemRaw =
                trim(
                    (string) (
                        $row['item']
                        ?? ''
                    )
                );


            $itemNo = null;


            if (
                $itemRaw === ''
                ||
                !ctype_digit($itemRaw)
            ) {

                $errors[] =
                    'Invalid or missing Medisoft Item number';
            } else {

                $itemNo =
                    (int) $itemRaw;


                if ($itemNo <= 0) {

                    $errors[] =
                        'Invalid Medisoft Item number';
                } elseif (
                    isset(
                        $existingItems[(string) $itemNo]
                    )
                ) {

                    $errors[] =
                        'Medisoft payment Item already exists';
                } elseif (
                    isset(
                        $csvItemNumbers[(string) $itemNo]
                    )
                ) {

                    $errors[] =
                        'Duplicate Medisoft Item appears more than once in CSV';
                } else {

                    $csvItemNumbers[(string) $itemNo] = true;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Participant
            |--------------------------------------------------------------------------
            */

            $rawParticipantId =
                trim(
                    (string) (
                        $row['id']
                        ?? ''
                    )
                );


            $canonicalParticipantId =
                '';


            if ($rawParticipantId === '') {

                $errors[] =
                    'Participant ID missing';
            } elseif (
                is_numeric(
                    $rawParticipantId
                )
            ) {

                $canonicalParticipantId =
                    (string) (
                        (int) $rawParticipantId
                    );
            } else {

                /*
                |--------------------------------------------------------------------------
                | You indicated these IDs will be corrected manually before import.
                |--------------------------------------------------------------------------
                */

                $errors[] =
                    'Participant ID must be replaced with ADC ParticipantId before import';
            }


            $member = null;

            $participantId =
                $rawParticipantId;


            if (
                $canonicalParticipantId
                !== ''
            ) {

                $member =
                    $members->get(
                        $canonicalParticipantId
                    );


                if (!$member) {

                    $errors[] =
                        'Participant not found in billing member master';
                } else {

                    $participantId =
                        trim(
                            (string)
                            $member->ParticipantId
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Member Name
            |--------------------------------------------------------------------------
            */

            $memberName = '';


            if ($member) {

                $memberName =
                    trim(
                        (
                            $member->LastName
                            ?? ''
                        )
                            .
                            ', '
                            .
                            (
                                $member->FirstName
                                ?? ''
                            ),
                        ', '
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Payment Date
            |--------------------------------------------------------------------------
            */

            $paymentDate = null;


            $paymentDateRaw =
                trim(
                    (string) (
                        $row['date_from']
                        ?? ''
                    )
                );


            if ($paymentDateRaw === '') {

                $errors[] =
                    'Payment date missing';
            } else {

                try {

                    $paymentDate =
                        Carbon::createFromFormat(
                            'm/d/Y',
                            $paymentDateRaw
                        )->format(
                            'Y-m-d'
                        );
                } catch (\Throwable $e) {

                    try {

                        $paymentDate =
                            Carbon::parse(
                                $paymentDateRaw
                            )->format(
                                'Y-m-d'
                            );
                    } catch (\Throwable $e) {

                        $errors[] =
                            'Invalid payment date';
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Transaction Type
            |--------------------------------------------------------------------------
            */

            $transactionType =
                strtoupper(
                    trim(
                        (string) (
                            $row['procedure_code']
                            ?? ''
                        )
                    )
                );


            if ($transactionType === '') {

                $errors[] =
                    'Transaction type missing';
            } elseif (
                !in_array(
                    $transactionType,
                    [
                        'IP',
                        'IA',
                        'IW',
                        'DD',
                        'IT',
                    ],
                    true
                )
            ) {

                $warnings[] =
                    'Unknown Medisoft transaction type: '
                    .
                    $transactionType;
            }


            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            $description =
                trim(
                    (string) (
                        $row['description']
                        ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Attending Provider
            |--------------------------------------------------------------------------
            */

            $attendingProvider =
                trim(
                    (string) (
                        $row['attending_provider']
                        ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Original Signed Amount
            |--------------------------------------------------------------------------
            */

            $originalAmount =
                $this->medisoftPaymentCsvNumber(
                    $row['amount']
                        ?? 0
                );
            /*
            |--------------------------------------------------------------------------
            | Normalized Payment Amount
            |--------------------------------------------------------------------------
            |
            | Medisoft Payment / Adjustment Codes:
            |
            | IP = Insurance Payment
            |      Medisoft amount is normally negative.
            |      ADC payment effect is stored as positive.
            |
            | IT = Insurance Take Back
            |      Medisoft amount is normally positive.
            |      ADC payment effect is stored as negative.
            |
            | IA = Insurance Adjustment
            | IW = Insurance Withhold
            | DD = Insurance Deductible
            |
            | IA / IW / DD are retained as historical transactions but are not
            | treated as cash receipts in payment_amount.
            |--------------------------------------------------------------------------
            */

            $paymentAmount = 0.00;


            switch ($transactionType) {

                case 'IP':

                    /*
                    |--------------------------------------------------------------------------
                    | Insurance Payment
                    |--------------------------------------------------------------------------
                    |
                    | Medisoft exports payment as negative.
                    | ADC stores received payment as positive.
                    |--------------------------------------------------------------------------
                    */

                    $paymentAmount =
                        abs(
                            $originalAmount
                        );

                    break;


                case 'IT':

                    /*
                    |--------------------------------------------------------------------------
                    | Insurance Take Back
                    |--------------------------------------------------------------------------
                    |
                    | Medisoft exports take-back as positive.
                    | ADC stores its payment effect as negative.
                    |--------------------------------------------------------------------------
                    */

                    $paymentAmount =
                        -abs(
                            $originalAmount
                        );

                    break;


                case 'IA':

                    /*
                    |--------------------------------------------------------------------------
                    | Insurance Adjustment
                    |--------------------------------------------------------------------------
                    |
                    | Preserve transaction for now.
                    | Do not treat it as cash payment.
                    |--------------------------------------------------------------------------
                    */

                    $paymentAmount = 0.00;

                    break;


                case 'IW':

                    /*
                    |--------------------------------------------------------------------------
                    | Insurance Withhold
                    |--------------------------------------------------------------------------
                    */

                    $paymentAmount = 0.00;

                    break;


                case 'DD':

                    /*
                    |--------------------------------------------------------------------------
                    | Insurance Deductible
                    |--------------------------------------------------------------------------
                    */

                    $paymentAmount = 0.00;

                    break;
            }



            /*
            |--------------------------------------------------------------------------
            | Check Number
            |--------------------------------------------------------------------------
            */

            $checkNumber =
                trim(
                    (string) (
                        $row['check_number']
                        ?? ''
                    )
                );


            if ($checkNumber === '') {

                $warnings[] =
                    'Check / reference number is blank';
            }


            /*
            |--------------------------------------------------------------------------
            | Unapplied Amount
            |--------------------------------------------------------------------------
            */

            $unappliedAmount =
                $this->medisoftPaymentCsvNumber(
                    $row['unapplied_amount']
                        ?? 0
                );


            if (
                abs(
                    $unappliedAmount
                ) > 0.005
            ) {

                $warnings[] =
                    'Transaction contains an unapplied amount';
            }


            /*
            |--------------------------------------------------------------------------
            | Preview Row
            |--------------------------------------------------------------------------
            */

            $previewRows[] = [

                'key' =>
                (string)
                count($previewRows),

                'csv_row' =>
                $csvRowNumber,

                'medisoft_item_no' =>
                $itemNo,

                'ParticipantId' =>
                $participantId,

                'member_name' =>
                $memberName,

                'payment_date' =>
                $paymentDate,

                'transaction_type' =>
                $transactionType,

                'description' =>
                $description,

                'attending_provider' =>
                $attendingProvider,

                'original_amount' =>
                round(
                    $originalAmount,
                    2
                ),

                'payment_amount' =>
                round(
                    $paymentAmount,
                    2
                ),

                'check_number' =>
                $checkNumber,

                'unapplied_amount' =>
                round(
                    $unappliedAmount,
                    2
                ),

                'valid' =>
                empty($errors),

                'warning' =>
                !empty($warnings),

                'message' =>
                !empty($errors)
                    ? implode(
                        '; ',
                        array_unique(
                            $errors
                        )
                    )
                    : (
                        !empty($warnings)
                        ? implode(
                            '; ',
                            array_unique(
                                $warnings
                            )
                        )
                        : 'OK'
                    ),
            ];
        }


        fclose($handle);


        /*
        |--------------------------------------------------------------------------
        | Store Preview
        |--------------------------------------------------------------------------
        */

        $token =
            (string)
            Str::uuid();


        $imports =
            session(
                'medisoft_payment_imports',
                []
            );


        $imports[$token] = [

            'rows' =>
            $previewRows,

            'created_at' =>
            now()->toDateTimeString(),
        ];


        session([
            'medisoft_payment_imports'
            =>
            $imports
        ]);


        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */

        $readyCount =
            collect(
                $previewRows
            )
            ->where(
                'valid',
                true
            )
            ->count();


        $invalidCount =
            count($previewRows)
            -
            $readyCount;


        $warningCount =
            collect(
                $previewRows
            )
            ->filter(
                function ($row) {

                    return
                        !empty($row['valid'])
                        &&
                        !empty($row['warning']);
                }
            )
            ->count();


        return view(
            'era.era_medisoft_payment_preview',
            compact(
                'token',
                'previewRows',
                'readyCount',
                'invalidCount',
                'warningCount'
            )
        );
    } // End Method

    private function medisoftPaymentCsvNumber($value): float
    {
        if ($value === null) {

            return 0.00;
        }


        $value =
            trim(
                (string) $value
            );


        if ($value === '') {

            return 0.00;
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Formatting
        |--------------------------------------------------------------------------
        */

        $value = str_replace(
            [
                ',',
                '$',
                ' ',
            ],
            '',
            $value
        );


        /*
        |--------------------------------------------------------------------------
        | Accounting Format
        |
        | (479.80) -> -479.80
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $value,
                '('
            )
            &&
            str_ends_with(
                $value,
                ')'
            )
        ) {

            $value =
                '-'
                .
                trim(
                    $value,
                    '()'
                );
        }


        return is_numeric(
            $value
        )
            ? round(
                (float) $value,
                2
            )
            : 0.00;
    } // End Method

    public function medisoftPaymentImportProcess(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'token' => [
                'required',
                'string',
            ],
        ]);


        $token =
            trim(
                (string)
                $request->input(
                    'token'
                )
            );


        $imports =
            session(
                'medisoft_payment_imports',
                []
            );


        if (
            !isset(
                $imports[$token]
            )
        ) {

            return redirect()
                ->route(
                    'era.medisoft.payments.form'
                )
                ->with(
                    'error',
                    'The Medisoft payment preview has expired. Please upload the CSV again.'
                );
        }


        $previewRows =
            $imports[$token]['rows']
            ?? [];


        /*
        |--------------------------------------------------------------------------
        | Import All Valid?
        |--------------------------------------------------------------------------
        */

        $importAllValid =
            $request->boolean(
                'import_all_valid'
            );


        /*
        |--------------------------------------------------------------------------
        | Select Rows
        |--------------------------------------------------------------------------
        */

        if ($importAllValid) {

            $rowsToImport =
                collect(
                    $previewRows
                )
                ->filter(
                    function ($row) {

                        return !empty($row['valid']);
                    }
                )
                ->values();
        } else {

            $selectedKeys =
                collect(
                    $request->input(
                        'selected_rows',
                        []
                    )
                )
                ->map(
                    fn($value) =>
                    (string) $value
                )
                ->unique()
                ->values();


            $rowsToImport =
                collect(
                    $previewRows
                )
                ->filter(
                    function ($row) use (
                        $selectedKeys
                    ) {

                        return
                            !empty($row['valid'])
                            &&
                            $selectedKeys->contains(
                                (string)
                                $row['key']
                            );
                    }
                )
                ->values();
        }


        if (
            $rowsToImport->isEmpty()
        ) {

            return redirect()
                ->route(
                    'era.medisoft.payments.form'
                )
                ->with(
                    'error',
                    'No valid Medisoft payment rows were selected.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Preload Existing Item Numbers
        |--------------------------------------------------------------------------
        */

        $existingItems = [];


        DB::table(
            'medisoft_payment_history'
        )
            ->select(
                'id',
                'medisoft_item_no'
            )
            ->orderBy('id')
            ->chunkById(
                2000,
                function ($records) use (
                    &$existingItems
                ) {

                    foreach (
                        $records
                        as $record
                    ) {

                        $existingItems[(string)
                        $record->medisoft_item_no] = true;
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $importedCount = 0;
        $skippedCount  = 0;
        $failedCount   = 0;


        /*
        |--------------------------------------------------------------------------
        | Result Exceptions Only
        |--------------------------------------------------------------------------
        |
        | We do not put all 20,000 imported rows into the result session.
        | Only skipped/failed rows are retained.
        |--------------------------------------------------------------------------
        */

        $problemRows = [];


        /*
        |--------------------------------------------------------------------------
        | Batch Insert
        |--------------------------------------------------------------------------
        */

        $insertBatch = [];

        $batchSize = 500;


        DB::beginTransaction();


        try {

            foreach (
                $rowsToImport
                as $row
            ) {

                try {

                    $itemNo =
                        (int) (
                            $row['medisoft_item_no']
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Duplicate Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $itemNo <= 0
                    ) {

                        $failedCount++;

                        $problemRows[] = [

                            'csv_row' =>
                            $row['csv_row']
                                ?? '',

                            'item' =>
                            $itemNo,

                            'participant_id' =>
                            $row['ParticipantId']
                                ?? '',

                            'payment_date' =>
                            $row['payment_date']
                                ?? null,

                            'amount' =>
                            $row['original_amount']
                                ?? 0,

                            'result' =>
                            'Failed',

                            'message' =>
                            'Invalid Medisoft Item number.',
                        ];

                        continue;
                    }


                    if (
                        isset(
                            $existingItems[(string)
                            $itemNo]
                        )
                    ) {

                        $skippedCount++;

                        $problemRows[] = [

                            'csv_row' =>
                            $row['csv_row']
                                ?? '',

                            'item' =>
                            $itemNo,

                            'participant_id' =>
                            $row['ParticipantId']
                                ?? '',

                            'payment_date' =>
                            $row['payment_date']
                                ?? null,

                            'amount' =>
                            $row['original_amount']
                                ?? 0,

                            'result' =>
                            'Skipped',

                            'message' =>
                            'Medisoft payment Item already exists.',
                        ];

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Build Insert
                    |--------------------------------------------------------------------------
                    */

                    $importMessage =
                        $row['message']
                        ?? 'Imported from Medisoft payment history';


                    if (
                        $importMessage === 'OK'
                    ) {

                        $importMessage =
                            'Imported from Medisoft payment history';
                    }


                    $insertBatch[] = [

                        'medisoft_item_no' =>
                        $itemNo,

                        'ParticipantId' =>
                        trim(
                            (string) (
                                $row['ParticipantId']
                                ?? ''
                            )
                        ),

                        'payment_date' =>
                        $row['payment_date'],

                        'transaction_type' =>
                        trim(
                            (string) (
                                $row['transaction_type']
                                ?? ''
                            )
                        ),

                        'description' =>
                        !empty($row['description'])
                            ? substr(
                                (string)
                                $row['description'],
                                0,
                                255
                            )
                            : null,

                        'attending_provider' =>
                        !empty($row['attending_provider'])
                            ? substr(
                                (string)
                                $row['attending_provider'],
                                0,
                                20
                            )
                            : null,

                        'original_amount' =>
                        round(
                            (float) (
                                $row['original_amount']
                                ?? 0
                            ),
                            2
                        ),

                        'payment_amount' =>
                        round(
                            (float) (
                                $row['payment_amount']
                                ?? 0
                            ),
                            2
                        ),

                        'check_number' =>
                        !empty($row['check_number'])
                            ? substr(
                                (string)
                                $row['check_number'],
                                0,
                                50
                            )
                            : null,

                        'unapplied_amount' =>
                        round(
                            (float) (
                                $row['unapplied_amount']
                                ?? 0
                            ),
                            2
                        ),

                        'source_system' =>
                        'MEDISOFT',

                        'import_status' =>
                        'imported',

                        'import_message' =>
                        substr(
                            $importMessage,
                            0,
                            255
                        ),

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),
                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | Mark Item As Used In Current Run
                    |--------------------------------------------------------------------------
                    */

                    $existingItems[(string)
                    $itemNo] = true;


                    /*
                    |--------------------------------------------------------------------------
                    | Flush Batch
                    |--------------------------------------------------------------------------
                    */

                    if (
                        count(
                            $insertBatch
                        )
                        >=
                        $batchSize
                    ) {

                        DB::table(
                            'medisoft_payment_history'
                        )
                            ->insert(
                                $insertBatch
                            );


                        $importedCount +=
                            count(
                                $insertBatch
                            );


                        $insertBatch = [];
                    }
                } catch (
                    \Throwable
                    $rowException
                ) {

                    $failedCount++;


                    $problemRows[] = [

                        'csv_row' =>
                        $row['csv_row']
                            ?? '',

                        'item' =>
                        $row['medisoft_item_no']
                            ?? '',

                        'participant_id' =>
                        $row['ParticipantId']
                            ?? '',

                        'payment_date' =>
                        $row['payment_date']
                            ?? null,

                        'amount' =>
                        $row['original_amount']
                            ?? 0,

                        'result' =>
                        'Failed',

                        'message' =>
                        $rowException
                            ->getMessage(),
                    ];
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Final Batch
            |--------------------------------------------------------------------------
            */

            if (
                !empty($insertBatch)
            ) {

                DB::table(
                    'medisoft_payment_history'
                )
                    ->insert(
                        $insertBatch
                    );


                $importedCount +=
                    count(
                        $insertBatch
                    );
            }


            DB::commit();
        } catch (\Throwable $e) {

            DB::rollBack();


            return redirect()
                ->route(
                    'era.medisoft.payments.form'
                )
                ->with(
                    'error',
                    'Medisoft payment import failed: '
                        .
                        $e->getMessage()
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Used Preview
        |--------------------------------------------------------------------------
        */

        unset(
            $imports[$token]
        );


        session([
            'medisoft_payment_imports'
            =>
            $imports
        ]);


        /*
        |--------------------------------------------------------------------------
        | Save Result
        |--------------------------------------------------------------------------
        */

        session([
            'medisoft_payment_import_result'
            =>
            [
                'imported' =>
                $importedCount,

                'skipped' =>
                $skippedCount,

                'failed' =>
                $failedCount,

                'total' =>
                $rowsToImport
                    ->count(),

                'problems' =>
                $problemRows,

                'completed_at' =>
                now()
                    ->format(
                        'm/d/Y h:i A'
                    ),
            ]
        ]);


        return redirect()
            ->route(
                'era.medisoft.payments.result'
            );
    } // End Method

    public function medisoftPaymentImportResult()
    {
        $result =
            session(
                'medisoft_payment_import_result'
            );


        if (!$result) {

            return redirect()
                ->route(
                    'era.medisoft.payments.form'
                )
                ->with(
                    'error',
                    'No Medisoft payment import result is available.'
                );
        }


        return view(
            'era.era_medisoft_payment_result',
            compact(
                'result'
            )
        );
    } // End Method

    public function medisoftPaymentDownloadInvalid($token)
    {
        $token =
            trim(
                (string) $token
            );


        $imports =
            session(
                'medisoft_payment_imports',
                []
            );


        if (
            $token === ''
            ||
            !isset(
                $imports[$token]
            )
        ) {

            return redirect()
                ->route(
                    'era.medisoft.payments.form'
                )
                ->with(
                    'error',
                    'The Medisoft payment preview has expired.'
                );
        }


        $invalidRows =
            collect(
                $imports[$token]['rows']
                    ?? []
            )
            ->filter(
                function ($row) {

                    return empty($row['valid']);
                }
            )
            ->values();


        if (
            $invalidRows->isEmpty()
        ) {

            return back()->with(
                'success',
                'There are no invalid Medisoft payment rows.'
            );
        }


        $fileName =
            'Medisoft_Payment_Invalid_Rows_'
            .
            now()->format(
                'Ymd_His'
            )
            .
            '.csv';


        return response()->streamDownload(

            function () use (
                $invalidRows
            ) {

                $handle =
                    fopen(
                        'php://output',
                        'w'
                    );


                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );


                fputcsv(
                    $handle,
                    [
                        'CSV Row',
                        'Item',
                        'Participant ID',
                        'Member',
                        'Payment Date',
                        'Type',
                        'Description',
                        'Original Amount',
                        'Payment Amount',
                        'Check Number',
                        'Unapplied Amount',
                        'Error Message',
                    ]
                );


                foreach (
                    $invalidRows
                    as $row
                ) {

                    fputcsv(
                        $handle,
                        [
                            $row['csv_row']
                                ?? '',

                            $row['medisoft_item_no']
                                ?? '',

                            $row['ParticipantId']
                                ?? '',

                            $row['member_name']
                                ?? '',

                            $row['payment_date']
                                ?? '',

                            $row['transaction_type']
                                ?? '',

                            $row['description']
                                ?? '',

                            number_format(
                                (float) (
                                    $row['original_amount']
                                    ?? 0
                                ),
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                (float) (
                                    $row['payment_amount']
                                    ?? 0
                                ),
                                2,
                                '.',
                                ''
                            ),

                            $row['check_number']
                                ?? '',

                            number_format(
                                (float) (
                                    $row['unapplied_amount']
                                    ?? 0
                                ),
                                2,
                                '.',
                                ''
                            ),

                            $row['message']
                                ?? '',
                        ]
                    );
                }


                fclose(
                    $handle
                );
            },

            $fileName,

            [
                'Content-Type'
                =>
                'text/csv; charset=UTF-8',
            ]

        );
    } // End Method

    public function claimMdEraList()
    {
        try {

            $allRecords = collect();

            $page = 1;

            do {

                $response = Http::asForm()
                    ->acceptJson()
                    ->timeout(60)
                    ->post(
                        rtrim(config('services.claimmd.url'), '/')
                            . '/services/eralist/',
                        [
                            'AccountKey' => config(
                                'services.claimmd.account_key'
                            ),

                            'NewOnly' => '1',

                            'Page' => $page,
                        ]
                    );

                if (!$response->successful()) {

                    return view(
                        'admin.era.claimmd_era_list',
                        [
                            'eras' => collect(),

                            'errorMessage' =>
                            'Unable to connect to Claim.MD. HTTP Status: '
                                . $response->status(),
                        ]
                    );
                }

                $data = $response->json();

                if (isset($data['error'])) {

                    /*
            |--------------------------------------------------------------------------
            | If Page > 1 and there are no additional results,
            | just stop pagination.
            |--------------------------------------------------------------------------
            */

                    if ($page > 1) {
                        break;
                    }

                    $message =
                        $data['error']['error_mesg']
                        ?? 'Unknown Claim.MD API error.';

                    return view(
                        'admin.era.claimmd_era_list',
                        [
                            'eras' => collect(),
                            'errorMessage' => $message,
                        ]
                    );
                }

                $records = $data['eras']
                    ?? $data['era']
                    ?? $data['records']
                    ?? $data['data']
                    ?? [];

                $records = collect($records);

                $allRecords = $allRecords->concat($records);

                /*
        |--------------------------------------------------------------------------
        | Fewer than 100 means this was the last page.
        |--------------------------------------------------------------------------
        */

                if ($records->count() < 100) {
                    break;
                }

                $page++;

                /*
        |--------------------------------------------------------------------------
        | Safety limit
        |--------------------------------------------------------------------------
        */

                if ($page > 50) {
                    break;
                }
            } while (true);

            /*
            |--------------------------------------------------------------------------
            | HTTP Error
            |--------------------------------------------------------------------------
            */

            if (!$response->successful()) {

                return view('admin.era.claimmd_era_list', [
                    'eras' => collect(),
                    'errorMessage' =>
                    'Unable to connect to Claim.MD. HTTP Status: '
                        . $response->status(),
                ]);
            }

            $data = $response->json();

            /*
            |--------------------------------------------------------------------------
            | Claim.MD API Error
            |--------------------------------------------------------------------------
            */

            if (isset($data['error'])) {

                $message =
                    $data['error']['error_mesg']
                    ?? 'Unknown Claim.MD API error.';

                return view('admin.era.claimmd_era_list', [
                    'eras' => collect(),
                    'errorMessage' => $message,
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | ERA Records
        |--------------------------------------------------------------------------
        */

            $records = $data['eras']
                ?? $data['era']
                ?? $data['records']
                ?? $data['data']
                ?? [];

            /*
        |--------------------------------------------------------------------------
        | Clean ERA List
        |--------------------------------------------------------------------------
        */

            $eras = $allRecords->map(function ($era) {

                return [

                    'eraid' => $era['eraid'] ?? null,

                    'payerid' => $era['payerid'] ?? '',

                    'payer_name' => $era['payer_name'] ?? '',

                    'received_time' => $era['received_time'] ?? '',

                    'paid_date' => $era['paid_date'] ?? '',

                    'paid_amount' => $era['paid_amount'] ?? '0.00',

                    'check_type' => $era['check_type'] ?? '',

                    'check_number' => $era['check_number'] ?? '',

                    'provider_name' => $era['prov_name'] ?? '',

                    'claimmd_provider' =>
                    $era['claimmd_prov_name'] ?? '',

                    'provider_npi' => $era['prov_npi'] ?? '',

                    'download_time' =>
                    $era['download_time'] ?? '',
                ];
            });

            return view('era.era_claimmd_era_list', [
                'eras' => $eras,
                'errorMessage' => null,
            ]);
        } catch (\Throwable $e) {

            Log::error('Claim.MD ERA List Error', [
                'message' => $e->getMessage(),
            ]);

            return view('admin.era.claimmd_era_list', [
                'eras' => collect(),
                'errorMessage' => $e->getMessage(),
            ]);
        }
    } // End Method

    public function claimMdEraProcess(Request $request, $eraid)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Validate ERA ID
            |--------------------------------------------------------------------------
            */

            if (!is_numeric($eraid)) {

                return redirect()
                    ->route('era.claimmd.index')
                    ->with([
                        'message' => 'Invalid Claim.MD ERA ID.',
                        'alert-type' => 'danger',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | File Name
            |--------------------------------------------------------------------------
            */

            $filename = 'ClaimMD_ERA_' . $eraid . '.835';


            /*
            |--------------------------------------------------------------------------
            | Prevent Same Claim.MD ERA From Being Imported Twice
            |--------------------------------------------------------------------------
            |
            | Your regular ERA duplicate logic still checks TRN.
            | This additional check prevents accidentally clicking Download twice
            | for the exact same Claim.MD ERA ID.
            |
            */

            $alreadyImported = DB::table('era_files')
                ->where('file_name', $filename)
                ->exists();

            if ($alreadyImported) {

                return redirect()
                    ->route('era.index')
                    ->with([
                        'message' =>
                        "Claim.MD ERA {$eraid} has already been downloaded/imported.",
                        'alert-type' => 'info',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Download 835 From Claim.MD
            |--------------------------------------------------------------------------
            */

            $response = Http::asForm()
                ->withHeaders([
                    'Accept' => 'application/edi-x12',
                ])
                ->timeout(60)
                ->post(
                    rtrim(
                        config('services.claimmd.url'),
                        '/'
                    )
                        . '/services/era835/',
                    [
                        'AccountKey' =>
                        config('services.claimmd.account_key'),

                        'eraid' =>
                        $eraid,
                    ]
                );


            /*
            |--------------------------------------------------------------------------
            | HTTP Error
            |--------------------------------------------------------------------------
            */

            if (!$response->successful()) {

                Log::error(
                    'Claim.MD ERA Download Failed',
                    [
                        'eraid' => $eraid,
                        'http_status' => $response->status(),
                        'response' => substr(
                            $response->body(),
                            0,
                            1000
                        ),
                    ]
                );

                return redirect()
                    ->route('era.claimmd.index')
                    ->with([
                        'message' =>
                        "Unable to download ERA {$eraid} from Claim.MD.",
                        'alert-type' => 'danger',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Raw 835
            |--------------------------------------------------------------------------
            */

            $content = $response->body();

            if (trim($content) === '') {

                return redirect()
                    ->route('era.claimmd.index')
                    ->with([
                        'message' =>
                        "Claim.MD returned an empty ERA file for ERA {$eraid}.",
                        'alert-type' => 'danger',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Basic X12 Validation
            |--------------------------------------------------------------------------
            */

            if (!str_contains($content, 'ISA*')) {

                Log::warning(
                    'Claim.MD response does not appear to be X12',
                    [
                        'eraid' => $eraid,
                        'response' => substr(
                            $content,
                            0,
                            1000
                        ),
                    ]
                );

                return redirect()
                    ->route('era.claimmd.index')
                    ->with([
                        'message' =>
                        "Claim.MD ERA {$eraid} does not appear to contain a valid X12 835 file.",
                        'alert-type' => 'danger',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Save Original Claim.MD 835 File
            |--------------------------------------------------------------------------
            */
            $filename =
                'ClaimMD_ERA_'
                . $eraid
                . '.835';
            $storagePath =
                'era/' . $filename;

            Storage::disk('adc_private')->put(
                $storagePath,
                $content
            );

            /*
            |--------------------------------------------------------------------------
            | Confirm Saved File
            |--------------------------------------------------------------------------
            */

            if (
                !Storage::disk('adc_private')
                    ->exists($storagePath)
            ) {

                throw new \RuntimeException(
                    'Claim.MD ERA file could not be saved to private storage.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Log Download
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Claim.MD ERA downloaded',
                [
                    'eraid' =>
                    $eraid,

                    'filename' =>
                    $filename,

                    'storage_path' =>
                    $storagePath,
                ]
            );

            return $this->processUploadNew(
                $request,
                $content,
                $filename
            );
        } catch (\Throwable $e) {

            Log::error(
                'Claim.MD ERA Import Error',
                [
                    'eraid' => $eraid,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return redirect()
                ->route('era.claimmd.index')
                ->with([
                    'message' =>
                    'Claim.MD ERA download/import error: '
                        . $e->getMessage(),

                    'alert-type' =>
                    'danger',
                ]);
        }
    } // End Method

    public function exceptions()
    {
        $exceptions = DB::table('era_unmatched_claims as e')
            ->leftJoin(
                'era_files as f',
                'f.id',
                '=',
                'e.era_file_id'
            )
            ->select(
                'e.*',
                'f.file_name',
                'f.payer_name',
                'f.payment_date',
                'f.trace_number'
            )
            ->orderByRaw("
                CASE
                    WHEN e.resolved_yn = 'N' THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc('e.id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Member Names
        |--------------------------------------------------------------------------
        */

        $memberIds = $exceptions
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();


        $members = DB::table('billing_members')
            ->whereIn(
                'ParticipantId',
                $memberIds
            )
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName'
            )
            ->get()
            ->keyBy('ParticipantId');


        foreach ($exceptions as $row) {

            $member =
                $members[
                    $row->member_id
                ]
                ?? null;

            $row->LastName =
                $member->LastName
                ?? '';

            $row->FirstName =
                $member->FirstName
                ?? '';
        }


        return view(
            'era.era_exceptions',
            compact('exceptions')
        );

    } // End Method

    // public function exceptionShow($id)
    // {
    //     $exception = DB::table('era_unmatched_claims as e')
    //         ->leftJoin(
    //             'era_files as f',
    //             'f.id',
    //             '=',
    //             'e.era_file_id'
    //         )
    //         ->leftJoin(
    //             'era_claims as ec',
    //             'ec.id',
    //             '=',
    //             'e.era_claim_id'
    //         )
    //         ->select(
    //             'e.*',
    //             'f.file_name',
    //             'f.payer_name',
    //             'f.payment_date',
    //             'f.trace_number',
    //             'ec.total_charge as era_total_charge',
    //             'ec.total_paid as era_total_paid'
    //         )
    //         ->where(
    //             'e.id',
    //             $id
    //         )
    //         ->first();


    //     if (!$exception) {
    //         abort(404);
    //     }


    //     $serviceLines = DB::table('era_service_lines')
    //         ->where(
    //             'era_claim_id',
    //             $exception->era_claim_id
    //         )
    //         ->orderBy('svc_sequence')
    //         ->get();


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Existing ADC Claim Candidates
    //     |--------------------------------------------------------------------------
    //     */

    //     $claimRows = DB::table('claims')
    //         ->where(
    //             'claim_no',
    //             $exception->normalized_claim_no
    //         )
    //         ->orderBy('line_no')
    //         ->get();


    //     return view(
    //         'era.era_exception_show',
    //         compact(
    //             'exception',
    //             'serviceLines',
    //             'claimRows'
    //         )
    //     );

    // } // End Method

    public function exceptionShow($id)
    {
        /*
        |--------------------------------------------------------------------------
        | ERA Exception
        |--------------------------------------------------------------------------
        */

        $exception = DB::table('era_unmatched_claims')
            ->where('id', $id)
            ->first();

        if (!$exception) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA File
        |--------------------------------------------------------------------------
        */

        $eraFile = DB::table('era_files')
            ->where('id', $exception->era_file_id)
            ->first();

        if ($eraFile) {

            $exception->file_name =
                $eraFile->file_name ?? null;

            $exception->payer_name =
                $eraFile->payer_name ?? null;

            $exception->payment_date =
                $eraFile->payment_date ?? null;

            $exception->trace_number =
                $eraFile->trace_number ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Claim
        |--------------------------------------------------------------------------
        */

        $eraClaim = DB::table('era_claims')
            ->where('id', $exception->era_claim_id)
            ->first();

        if ($eraClaim) {

            $exception->era_total_charge =
                $eraClaim->total_charge ?? null;

            $exception->era_total_paid =
                $eraClaim->total_paid ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Service Lines
        |--------------------------------------------------------------------------
        */

        $serviceLines = DB::table('era_service_lines')
            ->where(
                'era_claim_id',
                $exception->era_claim_id
            )
            ->orderBy('svc_sequence')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Existing Normal ADC Claim Lookup
        |--------------------------------------------------------------------------
        */

        $claimRows = DB::table('claims')
            ->where(
                'claim_no',
                $exception->normalized_claim_no
            )
            ->orderBy('line_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Legacy 837 Recovery Validation
        |--------------------------------------------------------------------------
        |
        | This section DOES NOT post anything.
        |
        | It only checks whether the ERA patient control number can be safely
        | resolved through legacy_837_claim_map.
        |
        */

        $legacyMap = null;

        $legacyMember = null;

        $legacyClaimRows = collect();

        $legacyValidation = [
            'available' => false,
            'safe'      => false,
            'message'   => 'No legacy 837 mapping found.',
        ];


        $eraControlNo = trim(
            (string) (
                $exception->patient_control_number
                ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Find Exact Legacy CLM01 First
        |--------------------------------------------------------------------------
        */

        if ($eraControlNo !== '') {

            $legacyMaps = DB::table('legacy_837_claim_map')
                ->where(
                    'clm01',
                    $eraControlNo
                )
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Numeric Fallback For Zero Padding
            |--------------------------------------------------------------------------
            */

            if (
                $legacyMaps->isEmpty()
                &&
                ctype_digit($eraControlNo)
            ) {

                $eraNumeric =
                    (int) $eraControlNo;


                $legacyMaps = DB::table('legacy_837_claim_map')
                    ->get()
                    ->filter(function ($row) use ($eraNumeric) {

                        $value = trim(
                            (string) (
                                $row->clm01
                                ?? ''
                            )
                        );

                        return
                            $value !== ''
                            &&
                            ctype_digit($value)
                            &&
                            (int) $value
                            ===
                            $eraNumeric;
                    })
                    ->values();
            }


            /*
            |--------------------------------------------------------------------------
            | Legacy Map Found
            |--------------------------------------------------------------------------
            */

            if ($legacyMaps->count() === 1) {

                $legacyMap =
                    $legacyMaps->first();


                $legacyValidation['available'] =
                    true;


                /*
                |--------------------------------------------------------------------------
                | Member
                |--------------------------------------------------------------------------
                */

                if (!empty($legacyMap->ParticipantId)) {

                    $legacyMember = DB::table('billing_members')
                        ->where(
                            'ParticipantId',
                            $legacyMap->ParticipantId
                        )
                        ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | Locate ADC Claim By Participant + Legacy Service Dates
                |--------------------------------------------------------------------------
                */

                if (!empty($legacyMap->ParticipantId)) {

                    $query = DB::table('claims')
                        ->where(
                            'ParticipantId',
                            $legacyMap->ParticipantId
                        );


                    if (!empty($legacyMap->service_from)) {

                        $query->whereDate(
                            'service_from',
                            $legacyMap->service_from
                        );
                    }


                    if (!empty($legacyMap->service_to)) {

                        $query->whereDate(
                            'service_to',
                            $legacyMap->service_to
                        );
                    }


                    $legacyClaimRows =
                        $query
                        ->orderBy('line_no')
                        ->get();
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Legacy Map Against ERA
                |--------------------------------------------------------------------------
                */

                $errors = [];


                if (empty($legacyMap->ParticipantId)) {

                    $errors[] =
                        'Legacy mapping does not have a ParticipantId.';
                }


                /*
                |--------------------------------------------------------------------------
                | ERA Service From
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($exception->service_from)
                    &&
                    !empty($legacyMap->service_from)
                    &&
                    date(
                        'Y-m-d',
                        strtotime($exception->service_from)
                    )
                    !==
                    date(
                        'Y-m-d',
                        strtotime($legacyMap->service_from)
                    )
                ) {

                    $errors[] =
                        'ERA service-from date does not match the legacy 837.';
                }


                /*
                |--------------------------------------------------------------------------
                | ERA Service To
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($exception->service_to)
                    &&
                    !empty($legacyMap->service_to)
                    &&
                    date(
                        'Y-m-d',
                        strtotime($exception->service_to)
                    )
                    !==
                    date(
                        'Y-m-d',
                        strtotime($legacyMap->service_to)
                    )
                ) {

                    $errors[] =
                        'ERA service-to date does not match the legacy 837.';
                }


                /*
                |--------------------------------------------------------------------------
                | Charge
                |--------------------------------------------------------------------------
                */

                if (
                    $exception->total_charge !== null
                    &&
                    $legacyMap->charge_amount !== null
                    &&
                    abs(
                        round(
                            (float) $exception->total_charge,
                            2
                        )
                        -
                        round(
                            (float) $legacyMap->charge_amount,
                            2
                        )
                    )
                    >
                    0.02
                ) {

                    $errors[] =
                        'ERA charge does not match the legacy 837 charge.';
                }


                /*
                |--------------------------------------------------------------------------
                | ADC Claim Candidate Required
                |--------------------------------------------------------------------------
                */

                if ($legacyClaimRows->isEmpty()) {

                    $errors[] =
                        'No ADC claim was found for the mapped ParticipantId and service dates.';
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Candidate Charge
                |--------------------------------------------------------------------------
                */

                if ($legacyClaimRows->isNotEmpty()) {

                    $matchingAmountRows =
                        $legacyClaimRows
                        ->filter(function ($row) use ($legacyMap) {

                            return
                                abs(
                                    round(
                                        (float) (
                                            $row->total_amount
                                            ?? 0
                                        ),
                                        2
                                    )
                                    -
                                    round(
                                        (float) (
                                            $legacyMap->charge_amount
                                            ?? 0
                                        ),
                                        2
                                    )
                                )
                                <=
                                0.02;
                        })
                        ->values();


                    if ($matchingAmountRows->count() !== 1) {

                        $errors[] =
                            'ADC claim could not be uniquely identified by charge amount.';
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Final Validation Result
                |--------------------------------------------------------------------------
                */

                if (empty($errors)) {

                    $legacyValidation['safe'] =
                        true;

                    $legacyValidation['message'] =
                        'SAFE MATCH - Legacy mapping and ADC claim candidate agree.';
                } else {

                    $legacyValidation['safe'] =
                        false;

                    $legacyValidation['message'] =
                        implode(' ', $errors);
                }

            } elseif ($legacyMaps->count() > 1) {

                $legacyValidation['available'] =
                    true;

                $legacyValidation['safe'] =
                    false;

                $legacyValidation['message'] =
                    'Multiple legacy mappings were found for this ERA claim number.';
            }
        }


        return view(
            'era.era_exception_show',
            compact(
                'exception',
                'serviceLines',
                'claimRows',
                'legacyMap',
                'legacyMember',
                'legacyClaimRows',
                'legacyValidation'
            )
        );

    } // End Method

    public function exceptionLegacyRecover($id)
    {
        /*
        |--------------------------------------------------------------------------
        | ERA Exception
        |--------------------------------------------------------------------------
        */

        $exception = DB::table('era_unmatched_claims')
            ->where('id', $id)
            ->first();


        if (!$exception) {

            return redirect()
                ->route('era.exceptions')
                ->with([
                    'message' =>
                        'ERA exception was not found.',

                    'alert-type' =>
                        'danger',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Already Resolved
        |--------------------------------------------------------------------------
        */

        if (
            ($exception->resolved_yn ?? 'N')
            ===
            'Y'
        ) {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'This ERA exception is already resolved.',

                    'alert-type' =>
                        'info',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA File
        |--------------------------------------------------------------------------
        */

        $file = DB::table('era_files')
            ->where(
                'id',
                $exception->era_file_id
            )
            ->first();


        if (!$file) {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'ERA file could not be found.',

                    'alert-type' =>
                        'danger',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Claim
        |--------------------------------------------------------------------------
        */

        $eraClaim = DB::table('era_claims')
            ->where(
                'id',
                $exception->era_claim_id
            )
            ->first();


        if (!$eraClaim) {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'ERA claim could not be found.',

                    'alert-type' =>
                        'danger',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Duplicate Posting Protection
        |--------------------------------------------------------------------------
        |
        | An exception should never already have claim transactions.
        | If it does, do NOT attempt recovery automatically.
        |
        */

        $alreadyPosted = DB::table('claim_transactions')
            ->where(
                'era_file_id',
                $exception->era_file_id
            )
            ->where(
                'era_claim_id',
                $exception->era_claim_id
            )
            ->exists();


        if ($alreadyPosted) {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'Recovery stopped. Transactions already exist for this ERA claim.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Control Number
        |--------------------------------------------------------------------------
        */

        $eraControlNo = trim(
            (string) (
                $exception->patient_control_number
                ?? ''
            )
        );


        if ($eraControlNo === '') {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'Recovery stopped. ERA patient control number is blank.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Legacy Mapping
        |--------------------------------------------------------------------------
        */

        $legacyMaps = DB::table('legacy_837_claim_map')
            ->where(
                'clm01',
                $eraControlNo
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Zero-Padding Fallback
        |--------------------------------------------------------------------------
        */

        if (
            $legacyMaps->isEmpty()
            &&
            ctype_digit($eraControlNo)
        ) {

            $legacyMaps = DB::table('legacy_837_claim_map')
                ->whereRaw(
                    'CAST(clm01 AS UNSIGNED) = ?',
                    [(int) $eraControlNo]
                )
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Mapping Against ERA
        |--------------------------------------------------------------------------
        */

        $safeMaps = $legacyMaps
            ->filter(
                function ($map) use ($eraClaim) {

                    if (
                        empty($map->ParticipantId)
                    ) {
                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Service From
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !empty($eraClaim->service_from)
                        &&
                        !empty($map->service_from)
                        &&
                        date(
                            'Y-m-d',
                            strtotime($eraClaim->service_from)
                        )
                        !==
                        date(
                            'Y-m-d',
                            strtotime($map->service_from)
                        )
                    ) {
                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Service To
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !empty($eraClaim->service_to)
                        &&
                        !empty($map->service_to)
                        &&
                        date(
                            'Y-m-d',
                            strtotime($eraClaim->service_to)
                        )
                        !==
                        date(
                            'Y-m-d',
                            strtotime($map->service_to)
                        )
                    ) {
                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Charge
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $eraClaim->total_charge !== null
                        &&
                        $map->charge_amount !== null
                        &&
                        abs(
                            round(
                                (float) $eraClaim->total_charge,
                                2
                            )
                            -
                            round(
                                (float) $map->charge_amount,
                                2
                            )
                        )
                        >
                        0.02
                    ) {
                        return false;
                    }


                    return true;
                }
            )
            ->values();


        if (
            $safeMaps->count()
            !==
            1
        ) {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        $safeMaps->isEmpty()
                            ? 'Legacy recovery stopped. No safe legacy mapping matched the ERA.'
                            : 'Legacy recovery stopped. More than one possible legacy mapping was found.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        $legacyMap =
            $safeMaps->first();


        /*
        |--------------------------------------------------------------------------
        | Locate ADC Claim
        |--------------------------------------------------------------------------
        */

        $claimQuery = DB::table('claims')
            ->where(
                'ParticipantId',
                trim(
                    (string) $legacyMap->ParticipantId
                )
            );


        if (
            !empty($legacyMap->service_from)
        ) {

            $claimQuery->whereDate(
                'service_from',
                $legacyMap->service_from
            );
        }


        if (
            !empty($legacyMap->service_to)
        ) {

            $claimQuery->whereDate(
                'service_to',
                $legacyMap->service_to
            );
        }


        $candidateClaims =
            $claimQuery
            ->orderBy('line_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Charge Match
        |--------------------------------------------------------------------------
        */

        $candidateClaims =
            $candidateClaims
            ->filter(
                function ($claim) use ($legacyMap) {

                    if (
                        $legacyMap->charge_amount === null
                    ) {
                        return true;
                    }


                    return
                        abs(
                            round(
                                (float) (
                                    $claim->total_amount
                                    ?? 0
                                ),
                                2
                            )
                            -
                            round(
                                (float) $legacyMap->charge_amount,
                                2
                            )
                        )
                        <=
                        0.02;
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Exactly One ADC Claim Required
        |--------------------------------------------------------------------------
        */

        if (
            $candidateClaims->count()
            !==
            1
        ) {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        $candidateClaims->isEmpty()
                            ? 'Legacy recovery stopped. No ADC claim matched ParticipantId, dates and charge.'
                            : 'Legacy recovery stopped. More than one ADC claim matched ParticipantId, dates and charge.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        $claim =
            $candidateClaims->first();


        /*
        |--------------------------------------------------------------------------
        | ERA Service Lines
        |--------------------------------------------------------------------------
        */

        $serviceLines = DB::table('era_service_lines')
            ->where(
                'era_claim_id',
                $eraClaim->id
            )
            ->orderBy('svc_sequence')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Safety Rule
        |--------------------------------------------------------------------------
        |
        | For legacy recovery we currently auto-post only zero or one SVC line.
        | Multi-line exceptions remain open for manual review.
        |
        */

        if (
            $serviceLines->count()
            >
            1
        ) {

            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'Legacy recovery stopped. This ERA claim contains multiple service lines and requires manual review.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Money Helper
        |--------------------------------------------------------------------------
        */

        $moneyMatches =
            function (
                $a,
                $b,
                $tolerance = 0.02
            ) {

                return
                    abs(
                        round(
                            (float) $a,
                            2
                        )
                        -
                        round(
                            (float) $b,
                            2
                        )
                    )
                    <=
                    $tolerance;
            };


        /*
        |--------------------------------------------------------------------------
        | Validate SVC Against ADC Claim
        |--------------------------------------------------------------------------
        */

        $svc =
            $serviceLines->first();


        if ($svc) {

            /*
            |--------------------------------------------------------------------------
            | Procedure
            |--------------------------------------------------------------------------
            */

            $svcProc = strtoupper(
                trim(
                    (string) (
                        $svc->procedure_code
                        ?? ''
                    )
                )
            );


            $claimProc = strtoupper(
                trim(
                    (string) (
                        $claim->proc_code
                        ?? ''
                    )
                )
            );


            if (
                $svcProc !== ''
                &&
                $claimProc !== ''
                &&
                $svcProc !== $claimProc
            ) {

                return redirect()
                    ->route(
                        'era.exception.show',
                        $id
                    )
                    ->with([
                        'message' =>
                            'Legacy recovery stopped. Procedure code does not match.',

                        'alert-type' =>
                            'warning',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Modifier
            |--------------------------------------------------------------------------
            */

            $svcModifier = strtoupper(
                trim(
                    (string) (
                        $svc->modifier1
                        ?? ''
                    )
                )
            );


            $claimModifier = strtoupper(
                trim(
                    (string) (
                        $claim->m1
                        ?? ''
                    )
                )
            );


            if (
                $svcModifier !== ''
                &&
                $claimModifier !== ''
                &&
                $svcModifier !== $claimModifier
            ) {

                return redirect()
                    ->route(
                        'era.exception.show',
                        $id
                    )
                    ->with([
                        'message' =>
                            'Legacy recovery stopped. Modifier does not match.',

                        'alert-type' =>
                            'warning',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Service Dates
            |--------------------------------------------------------------------------
            */

            if (
                !empty($svc->service_from)
                &&
                !empty($claim->service_from)
                &&
                (string) $svc->service_from
                !==
                (string) $claim->service_from
            ) {

                return redirect()
                    ->route(
                        'era.exception.show',
                        $id
                    )
                    ->with([
                        'message' =>
                            'Legacy recovery stopped. ERA service-from date does not match the ADC claim.',

                        'alert-type' =>
                            'warning',
                    ]);
            }


            if (
                !empty($svc->service_to)
                &&
                !empty($claim->service_to)
                &&
                (string) $svc->service_to
                !==
                (string) $claim->service_to
            ) {

                return redirect()
                    ->route(
                        'era.exception.show',
                        $id
                    )
                    ->with([
                        'message' =>
                            'Legacy recovery stopped. ERA service-to date does not match the ADC claim.',

                        'alert-type' =>
                            'warning',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | SVC Charge
            |--------------------------------------------------------------------------
            */

            if (
                $svc->charge_amount !== null
            ) {

                $svcCharge =
                    (float) $svc->charge_amount;


                if (
                    (string) (
                        $eraClaim->claim_status_code
                        ?? ''
                    )
                    ===
                    '22'
                ) {

                    $svcCharge =
                        abs($svcCharge);
                }


                if (
                    !$moneyMatches(
                        $claim->total_amount,
                        $svcCharge
                    )
                ) {

                    return redirect()
                        ->route(
                            'era.exception.show',
                            $id
                        )
                        ->with([
                            'message' =>
                                'Legacy recovery stopped. ERA service-line charge does not match the ADC claim.',

                            'alert-type' =>
                                'warning',
                        ]);
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Adjustments
        |--------------------------------------------------------------------------
        */

        $adjustments = DB::table('era_adjustments')
            ->where(
                'era_claim_id',
                $eraClaim->id
            )
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Financial Values
        |--------------------------------------------------------------------------
        */

        $paidAmount =
            round(
                (float) (
                    $svc
                        ? ($svc->paid_amount ?? 0)
                        : ($eraClaim->total_paid ?? 0)
                ),
                2
            );


        $isReversal =
            (string) (
                $eraClaim->claim_status_code
                ?? ''
            )
            ===
            '22';


        $lineAdjustments =
            collect();


        $claimAdjustments =
            collect();


        if ($svc) {

            $lineAdjustments =
                $adjustments
                ->filter(
                    function ($adj) use ($svc) {

                        return
                            strtolower(
                                trim(
                                    (string) (
                                        $adj->level
                                        ?? ''
                                    )
                                )
                            )
                            ===
                            'line'
                            &&
                            (int) (
                                $adj->svc_sequence
                                ?? 0
                            )
                            ===
                            (int) (
                                $svc->svc_sequence
                                ?? 0
                            );
                    }
                )
                ->values();


            $claimAdjustments =
                $adjustments
                ->filter(
                    function ($adj) {

                        return
                            strtolower(
                                trim(
                                    (string) (
                                        $adj->level
                                        ?? ''
                                    )
                                )
                            )
                            ===
                            'claim';
                    }
                )
                ->values();
        } else {

            /*
            |--------------------------------------------------------------------------
            | No SVC Line
            |--------------------------------------------------------------------------
            */

            $lineAdjustments =
                $adjustments;
        }


        /*
        |--------------------------------------------------------------------------
        | Adjustment Totals
        |--------------------------------------------------------------------------
        */

        $sumGroup =
            function ($rows, $groupCode) {

                return
                    round(
                        (float) $rows
                            ->filter(
                                function ($adj) use ($groupCode) {

                                    return
                                        strtoupper(
                                            trim(
                                                (string) (
                                                    $adj->group_code
                                                    ?? ''
                                                )
                                            )
                                        )
                                        ===
                                        $groupCode;
                                }
                            )
                            ->sum('amount'),
                        2
                    );
            };


        $sumOther =
            function ($rows) {

                return
                    round(
                        (float) $rows
                            ->filter(
                                function ($adj) {

                                    $group =
                                        strtoupper(
                                            trim(
                                                (string) (
                                                    $adj->group_code
                                                    ?? ''
                                                )
                                            )
                                        );


                                    return
                                        $group !== 'CO'
                                        &&
                                        $group !== 'PR';
                                }
                            )
                            ->sum('amount'),
                        2
                    );
            };


        $lineCO =
            $sumGroup(
                $lineAdjustments,
                'CO'
            );


        $linePR =
            $sumGroup(
                $lineAdjustments,
                'PR'
            );


        $lineOther =
            $sumOther(
                $lineAdjustments
            );


        $claimCO =
            $sumGroup(
                $claimAdjustments,
                'CO'
            );


        $claimPR =
            $sumGroup(
                $claimAdjustments,
                'PR'
            );


        $claimOther =
            $sumOther(
                $claimAdjustments
            );


        /*
        |--------------------------------------------------------------------------
        | Patient Responsibility
        |--------------------------------------------------------------------------
        */

        if ($svc) {

            if (
                abs($claimPR)
                >=
                0.005
            ) {

                $allocatedPR =
                    $claimPR;
            } else {

                $allocatedPR =
                    round(
                        (float) (
                            $eraClaim->patient_responsibility
                            ?? 0
                        )
                        -
                        $linePR,
                        2
                    );
            }


            $patientResp =
                round(
                    $linePR
                    +
                    $allocatedPR,
                    2
                );


            $allocatedCO =
                $claimCO;


            $allocatedOther =
                $claimOther;

        } else {

            $patientResp =
                round(
                    (float) (
                        $eraClaim->patient_responsibility
                        ?? 0
                    ),
                    2
                );


            $allocatedPR =
                0.00;


            $allocatedCO =
                0.00;


            $allocatedOther =
                0.00;
        }


        /*
        |--------------------------------------------------------------------------
        | Contractual / Other Adjustments
        |--------------------------------------------------------------------------
        */

        $contractualAdj =
            round(
                $lineCO
                +
                $claimCO,
                2
            );


        $otherAdj =
            round(
                $lineOther
                +
                $claimOther,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Fallback Contractual Adjustment
        |--------------------------------------------------------------------------
        */

        if (
            !$isReversal
            &&
            abs($contractualAdj)
            <
            0.005
            &&
            abs($claimCO)
            <
            0.005
        ) {

            $fallback =
                round(
                    (float) $claim->total_amount
                    -
                    $paidAmount
                    -
                    $patientResp
                    -
                    $otherAdj,
                    2
                );


            if ($fallback > 0) {

                $contractualAdj =
                    $fallback;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Balance
        |--------------------------------------------------------------------------
        */

        $balanceAmount =
            round(
                max(
                    (float) (
                        $claim->total_amount
                        ?? 0
                    )
                    -
                    $paidAmount
                    -
                    $contractualAdj
                    -
                    $otherAdj
                    -
                    (float) (
                        $claim->writeoff_amount
                        ?? 0
                    ),
                    0
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | CO-16
        |--------------------------------------------------------------------------
        */

        $hasCO16 =
            $adjustments
            ->contains(
                function ($adj) {

                    return
                        strtoupper(
                            trim(
                                (string) (
                                    $adj->group_code
                                    ?? ''
                                )
                            )
                        )
                        ===
                        'CO'
                        &&
                        trim(
                            (string) (
                                $adj->reason_code
                                ?? ''
                            )
                        )
                        ===
                        '16';
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Claim Status
        |--------------------------------------------------------------------------
        */

        if (
            (string) (
                $eraClaim->claim_status_code
                ?? ''
            )
            ===
            '4'
        ) {

            $status =
                'denied';

        } elseif ($hasCO16) {

            $status =
                'rejected';

        } elseif (
            $paidAmount <= 0.005
            &&
            $balanceAmount <= 0.005
            &&
            (
                $contractualAdj > 0.005
                ||
                $otherAdj > 0.005
            )
        ) {

            $status =
                'denied';

        } elseif (
            $paidAmount > 0.005
            &&
            $balanceAmount <= 0.005
        ) {

            $status =
                'paid';

        } else {

            $status =
                'accepted';
        }


        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        $userCd =
            Auth::check()
                ? (
                    Auth::user()->user_cd
                    ?? null
                )
                : null;


        /*
        |--------------------------------------------------------------------------
        | POST RECOVERY
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | Lock Exception
            |--------------------------------------------------------------------------
            */

            $lockedException =
                DB::table('era_unmatched_claims')
                ->where(
                    'id',
                    $id
                )
                ->lockForUpdate()
                ->first();


            if (
                !$lockedException
                ||
                ($lockedException->resolved_yn ?? 'N')
                ===
                'Y'
            ) {

                throw new \RuntimeException(
                    'ERA exception is already resolved.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Duplicate Protection Again Inside Transaction
            |--------------------------------------------------------------------------
            */

            $duplicateTransaction =
                DB::table('claim_transactions')
                ->where(
                    'era_file_id',
                    $exception->era_file_id
                )
                ->where(
                    'era_claim_id',
                    $exception->era_claim_id
                )
                ->exists();


            if ($duplicateTransaction) {

                throw new \RuntimeException(
                    'ERA transactions already exist for this claim.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            if (
                abs($paidAmount)
                >=
                0.005
            ) {

                DB::table('claim_transactions')
                    ->insert([

                        'claim_id' =>
                        $claim->id,

                        'era_file_id' =>
                        $exception->era_file_id,

                        'era_claim_id' =>
                        $eraClaim->id,

                        'claim_no' =>
                        $claim->claim_no,

                        'ParticipantId' =>
                        $claim->ParticipantId,

                        'payer_claim_number' =>
                        $eraClaim->payer_claim_number,

                        'transaction_date' =>
                        $file->payment_date
                        ??
                        now()->toDateString(),

                        'transaction_type' =>
                        $isReversal
                            ? 'reversal'
                            : 'payment',

                        'amount' =>
                        $paidAmount,

                        'description' =>
                        'ERA legacy recovery payment - line '
                        .
                        $claim->line_no,

                        'created_by' =>
                        $userCd,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Actual Line-Level Adjustments
            |--------------------------------------------------------------------------
            */

            foreach (
                $lineAdjustments as $adj
            ) {

                $group = strtoupper(
                    trim(
                        (string) (
                            $adj->group_code
                            ?? ''
                        )
                    )
                );


                $transactionType =
                    'deduction';


                if ($group === 'CO') {

                    $transactionType =
                        'contractual_adjustment';

                } elseif ($group === 'PR') {

                    $transactionType =
                        'patient_responsibility';
                }


                DB::table('claim_transactions')
                    ->insert([

                        'claim_id' =>
                        $claim->id,

                        'era_file_id' =>
                        $exception->era_file_id,

                        'era_claim_id' =>
                        $eraClaim->id,

                        'claim_no' =>
                        $claim->claim_no,

                        'ParticipantId' =>
                        $claim->ParticipantId,

                        'payer_claim_number' =>
                        $eraClaim->payer_claim_number,

                        'transaction_date' =>
                        $file->payment_date
                        ??
                        now()->toDateString(),

                        'transaction_type' =>
                        $transactionType,

                        'amount' =>
                        round(
                            (float) (
                                $adj->amount
                                ?? 0
                            ),
                            2
                        ),

                        'group_code' =>
                        $adj->group_code,

                        'reason_code' =>
                        $adj->reason_code,

                        'description' =>
                        'ERA legacy recovery adjustment - line '
                        .
                        $claim->line_no,

                        'created_by' =>
                        $userCd,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Allocated Claim-Level CO
            |--------------------------------------------------------------------------
            */

            if (
                abs($allocatedCO)
                >=
                0.005
            ) {

                DB::table('claim_transactions')
                    ->insert([

                        'claim_id' =>
                        $claim->id,

                        'era_file_id' =>
                        $exception->era_file_id,

                        'era_claim_id' =>
                        $eraClaim->id,

                        'claim_no' =>
                        $claim->claim_no,

                        'ParticipantId' =>
                        $claim->ParticipantId,

                        'payer_claim_number' =>
                        $eraClaim->payer_claim_number,

                        'transaction_date' =>
                        $file->payment_date
                        ??
                        now()->toDateString(),

                        'transaction_type' =>
                        'contractual_adjustment',

                        'amount' =>
                        $allocatedCO,

                        'group_code' =>
                        'CO',

                        'description' =>
                        'ERA legacy recovery claim-level adjustment',

                        'created_by' =>
                        $userCd,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Allocated Patient Responsibility
            |--------------------------------------------------------------------------
            */

            if (
                abs($allocatedPR)
                >=
                0.005
            ) {

                DB::table('claim_transactions')
                    ->insert([

                        'claim_id' =>
                        $claim->id,

                        'era_file_id' =>
                        $exception->era_file_id,

                        'era_claim_id' =>
                        $eraClaim->id,

                        'claim_no' =>
                        $claim->claim_no,

                        'ParticipantId' =>
                        $claim->ParticipantId,

                        'payer_claim_number' =>
                        $eraClaim->payer_claim_number,

                        'transaction_date' =>
                        $file->payment_date
                        ??
                        now()->toDateString(),

                        'transaction_type' =>
                        'patient_responsibility',

                        'amount' =>
                        $allocatedPR,

                        'group_code' =>
                        'PR',

                        'description' =>
                        'ERA legacy recovery patient responsibility',

                        'created_by' =>
                        $userCd,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Allocated Other
            |--------------------------------------------------------------------------
            */

            if (
                abs($allocatedOther)
                >=
                0.005
            ) {

                DB::table('claim_transactions')
                    ->insert([

                        'claim_id' =>
                        $claim->id,

                        'era_file_id' =>
                        $exception->era_file_id,

                        'era_claim_id' =>
                        $eraClaim->id,

                        'claim_no' =>
                        $claim->claim_no,

                        'ParticipantId' =>
                        $claim->ParticipantId,

                        'payer_claim_number' =>
                        $eraClaim->payer_claim_number,

                        'transaction_date' =>
                        $file->payment_date
                        ??
                        now()->toDateString(),

                        'transaction_type' =>
                        'deduction',

                        'amount' =>
                        $allocatedOther,

                        'description' =>
                        'ERA legacy recovery claim-level deduction',

                        'created_by' =>
                        $userCd,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Fallback CO Transaction
            |--------------------------------------------------------------------------
            */

            $recordedCO =
                round(
                    $lineCO
                    +
                    $allocatedCO,
                    2
                );


            $fallbackCO =
                round(
                    $contractualAdj
                    -
                    $recordedCO,
                    2
                );


            if (
                abs($fallbackCO)
                >=
                0.005
            ) {

                DB::table('claim_transactions')
                    ->insert([

                        'claim_id' =>
                        $claim->id,

                        'era_file_id' =>
                        $exception->era_file_id,

                        'era_claim_id' =>
                        $eraClaim->id,

                        'claim_no' =>
                        $claim->claim_no,

                        'ParticipantId' =>
                        $claim->ParticipantId,

                        'payer_claim_number' =>
                        $eraClaim->payer_claim_number,

                        'transaction_date' =>
                        $file->payment_date
                        ??
                        now()->toDateString(),

                        'transaction_type' =>
                        'contractual_adjustment',

                        'amount' =>
                        $fallbackCO,

                        'group_code' =>
                        'CO',

                        'description' =>
                        'ERA legacy recovery calculated contractual adjustment',

                        'created_by' =>
                        $userCd,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Update ADC Claim
            |--------------------------------------------------------------------------
            */

            DB::table('claims')
                ->where(
                    'id',
                    $claim->id
                )
                ->update([

                    'payer_claim_number' =>
                    $eraClaim->payer_claim_number,

                    'allowed' =>
                    round(
                        max(
                            $paidAmount
                            +
                            $patientResp,
                            0
                        ),
                        2
                    ),

                    'ins1_paid' =>
                    $paidAmount,

                    'adjustment_amount' =>
                    $contractualAdj,

                    'patient_responsibility' =>
                    $patientResp,

                    'deduction_amount' =>
                    $otherAdj,

                    'balance_amount' =>
                    $balanceAmount,

                    'paid_date' =>
                    $file->payment_date,

                    'era_posted_at' =>
                    now(),

                    'status' =>
                    $status,

                    'updated_at' =>
                    now(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Update ERA Claim Member
            |--------------------------------------------------------------------------
            */

            DB::table('era_claims')
                ->where(
                    'id',
                    $eraClaim->id
                )
                ->update([

                    'member_id' =>
                    $legacyMap->ParticipantId,

                    'updated_at' =>
                    now(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Resolve Exception
            |--------------------------------------------------------------------------
            */

            DB::table('era_unmatched_claims')
                ->where(
                    'id',
                    $id
                )
                ->update([

                    'member_id' =>
                    $legacyMap->ParticipantId,

                    'resolved_yn' =>
                    'Y',

                    'resolved_at' =>
                    now(),

                    'updated_at' =>
                    now(),

                ]);


            DB::commit();


            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'Legacy ERA claim recovered and posted successfully. ADC Claim # '
                        .
                        $claim->claim_no
                        .
                        ', Participant '
                        .
                        $claim->ParticipantId
                        .
                        ', Paid $'
                        .
                        number_format(
                            $paidAmount,
                            2
                        )
                        .
                        '.',

                    'alert-type' =>
                        'success',
                ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);


            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'Legacy ERA recovery failed: '
                        .
                        $e->getMessage(),

                    'alert-type' =>
                        'danger',
                ]);
        }

    } // End Method

    public function exceptionResolve($id)
    {
        DB::table('era_unmatched_claims')
            ->where(
                'id',
                $id
            )
            ->update([

                'resolved_yn' =>
                    'Y',

                'resolved_at' =>
                    now(),

                'updated_at' =>
                    now(),

            ]);


        return redirect()
            ->route('era.exceptions')
            ->with([

                'message' =>
                    'ERA exception marked as resolved.',

                'alert-type' =>
                    'success',

            ]);

    } // End Method

    public function legacyPaymentImportIndex()
    {
        $rows = DB::table('legacy_era_payments')
            ->orderByDesc('id')
            ->get();


        $total = $rows->count();


        $matched = $rows
            ->whereIn(
                'match_status',
                [
                    'MATCHED',
                    'MULTI_MATCHED',
                ]
            )
            ->count();

        $unmatched = $rows
            ->where(
                'match_status',
                'UNMATCHED'
            )
            ->count();


        return view(
            'era.era_legacy_payment_import',
            compact(
                'rows',
                'total',
                'matched',
                'unmatched'
            )
        );

    } // End Method

    public function legacyPaymentImportProcess(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate File
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'csv_file' =>
                'required|file|mimes:csv,txt|max:5120',
        ]);


        $file =
            $request->file(
                'csv_file'
            );


        $sourceFileName =
            $file->getClientOriginalName();


        $handle =
            fopen(
                $file->getRealPath(),
                'r'
            );


        if (!$handle) {

            return back()
                ->with([
                    'message' =>
                        'Unable to open CSV file.',

                    'alert-type' =>
                        'danger',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $header =
            fgetcsv(
                $handle
            );


        if (!$header) {

            fclose(
                $handle
            );


            return back()
                ->with([
                    'message' =>
                        'CSV file does not contain a header row.',

                    'alert-type' =>
                        'danger',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Header
        |--------------------------------------------------------------------------
        */

        $header =
            array_map(
                function ($value) {

                    return strtolower(
                        trim(
                            preg_replace(
                                '/^\xEF\xBB\xBF/',
                                '',
                                (string) $value
                            )
                        )
                    );

                },
                $header
            );


        /*
        |--------------------------------------------------------------------------
        | Required Columns
        |--------------------------------------------------------------------------
        */

        $requiredColumns = [

            'pcn',
            'pat_name_l',
            'pat_name_f',
            'ins_number',
            'fdos',
            'ldos',
            'proc_code_1',
            'total_charge',
            'amount_paid',
            'payer_icn',

        ];


        foreach (
            $requiredColumns as $column
        ) {

            if (
                !in_array(
                    $column,
                    $header,
                    true
                )
            ) {

                fclose(
                    $handle
                );


                return back()
                    ->with([
                        'message' =>
                            "Required CSV column '{$column}' was not found.",

                        'alert-type' =>
                            'danger',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $imported =
            0;


        $matched =
            0;


        $unmatched =
            0;


        /*
        |--------------------------------------------------------------------------
        | Read Rows
        |--------------------------------------------------------------------------
        */

        while (
            (
                $data =
                    fgetcsv(
                        $handle
                    )
            )
            !==
            false
        ) {

            /*
            |--------------------------------------------------------------------------
            | Skip Blank Rows
            |--------------------------------------------------------------------------
            */

            if (
                count(
                    array_filter(
                        $data,
                        function ($value) {

                            return
                                trim(
                                    (string) $value
                                )
                                !== '';

                        }
                    )
                )
                ===
                0
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Column Count Protection
            |--------------------------------------------------------------------------
            */

            if (
                count($data)
                !==
                count($header)
            ) {

                continue;
            }


            $row =
                array_combine(
                    $header,
                    $data
                );


            /*
            |--------------------------------------------------------------------------
            | CSV Values
            |--------------------------------------------------------------------------
            */

            $pcn =
                trim(
                    (string) (
                        $row['pcn']
                        ?? ''
                    )
                );


            $lastName =
                strtoupper(
                    trim(
                        (string) (
                            $row['pat_name_l']
                            ?? ''
                        )
                    )
                );


            $firstName =
                strtoupper(
                    trim(
                        (string) (
                            $row['pat_name_f']
                            ?? ''
                        )
                    )
                );


            $insuranceNo =
                trim(
                    (string) (
                        $row['ins_number']
                        ?? ''
                    )
                );


            $procCode =
                strtoupper(
                    trim(
                        (string) (
                            $row['proc_code_1']
                            ?? ''
                        )
                    )
                );


            $totalCharge =
                round(
                    (float) (
                        $row['total_charge']
                        ?? 0
                    ),
                    2
                );


            $amountPaid =
                round(
                    (float) (
                        $row['amount_paid']
                        ?? 0
                    ),
                    2
                );


            $payerIcn =
                trim(
                    (string) (
                        $row['payer_icn']
                        ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            try {

                $serviceFrom =
                    Carbon::parse(
                        $row['fdos']
                    )
                    ->format(
                        'Y-m-d'
                    );


                $serviceTo =
                    Carbon::parse(
                        $row['ldos']
                    )
                    ->format(
                        'Y-m-d'
                    );

            } catch (\Throwable $e) {

                $serviceFrom =
                    null;


                $serviceTo =
                    null;
            }


            /*
            |--------------------------------------------------------------------------
            | Defaults
            |--------------------------------------------------------------------------
            */

            $participantId =
                null;


            $claimNo =
                null;


            $claimId =
                null;


            $matchStatus =
                'UNMATCHED';


            $matchMessage =
                'No safe match found.';


            /*
|--------------------------------------------------------------------------
| Find Possible Billing Members
|--------------------------------------------------------------------------
|
| Primary identification:
|
| 1. Insurance number
| 2. Last name
|
| If more than one billing member matches, we do NOT guess.
| We use the ERA claim information to determine which ParticipantId
| actually owns the matching ADC claim.
|--------------------------------------------------------------------------
*/

$members =
    DB::table('billing_members')
    ->whereRaw(
        'TRIM(medicaid_HMO_id) = ?',
        [
            $insuranceNo
        ]
    )
    ->whereRaw(
        'UPPER(TRIM(LastName)) = ?',
        [
            $lastName
        ]
    )
    ->get();


/*
|--------------------------------------------------------------------------
| Optional First Name Refinement
|--------------------------------------------------------------------------
|
| Only use first name when it reduces the candidates.
|--------------------------------------------------------------------------
*/

if (
    $members->count()
    >
    1
    &&
    $firstName !== ''
) {

    $firstNameNormalized =
        preg_replace(
            '/\s+/',
            '',
            strtoupper(
                $firstName
            )
        );


    $firstNameMatches =
        $members
        ->filter(
            function ($member)
            use ($firstNameNormalized) {

                $dbFirstName =
                    preg_replace(
                        '/\s+/',
                        '',
                        strtoupper(
                            trim(
                                (string) (
                                    $member->FirstName
                                    ?? ''
                                )
                            )
                        )
                    );


                return
                    $dbFirstName
                    ===
                    $firstNameNormalized;
            }
        )
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Only Replace Candidate List If First Name Helped
    |--------------------------------------------------------------------------
    */

    if (
        $firstNameMatches->isNotEmpty()
    ) {

        $members =
            $firstNameMatches;
    }
}


/*
|--------------------------------------------------------------------------
| No Billing Member
|--------------------------------------------------------------------------
*/

if (
    $members->isEmpty()
) {

    $matchMessage =
        'Billing member not found by insurance number and name.';

    $unmatched++;

} else {

    /*
    |--------------------------------------------------------------------------
    | Search Claims For Every Possible Participant
    |--------------------------------------------------------------------------
    |
    | This also resolves duplicate billing members such as CHOI, JUNG.
    |--------------------------------------------------------------------------
    */

    $memberClaimMatches =
        collect();


    foreach (
        $members as $possibleMember
    ) {

        $possibleParticipantId =
            trim(
                (string) (
                    $possibleMember->ParticipantId
                    ?? ''
                )
            );


        if (
            $possibleParticipantId
            ===
            ''
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | First Attempt:
        | Exact Claim Date Range
        |--------------------------------------------------------------------------
        */

        $exactClaims =
            DB::table('claims')
            ->where(
                'ParticipantId',
                $possibleParticipantId
            )
            ->whereDate(
                'service_from',
                $serviceFrom
            )
            ->whereDate(
                'service_to',
                $serviceTo
            )
            ->whereRaw(
                'UPPER(TRIM(proc_code)) = ?',
                [
                    $procCode
                ]
            )
            ->orderBy('claim_no')
            ->orderBy('line_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Group Exact Rows By Claim Number
        |--------------------------------------------------------------------------
        */

        $exactGroups =
            $exactClaims
            ->groupBy(
                function ($claim) {

                    return
                        trim(
                            (string) (
                                $claim->claim_no
                                ?? ''
                            )
                        );
                }
            )
            ->filter(
                function ($group)
                use ($totalCharge) {

                    $groupTotal =
                        round(
                            (float) $group
                                ->sum(
                                    function ($claim) {

                                        return
                                            (float) (
                                                $claim->total_amount
                                                ?? 0
                                            );
                                    }
                                ),
                            2
                        );


                    return
                        abs(
                            $groupTotal
                            -
                            $totalCharge
                        )
                        <=
                        0.02;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Exact Single Claim Match
        |--------------------------------------------------------------------------
        */

        if (
            $exactGroups->count()
            ===
            1
        ) {

            $group =
                $exactGroups->first();


            $firstClaim =
                $group->first();


            $memberClaimMatches->push([

                'type' =>
                'single',

                'ParticipantId' =>
                $possibleParticipantId,

                'claim_no' =>
                trim(
                    (string) $firstClaim->claim_no
                ),

                'claim_id' =>
                $firstClaim->id,

                'claims' =>
                $group,

            ]);


            /*
            |--------------------------------------------------------------------------
            | Exact Match Found - No Need Split Fallback For This Member
            |--------------------------------------------------------------------------
            */

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Second Attempt:
        | Multiple Claims Inside ERA Date Range
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | ERA:
        | 09/14 - 09/18 = $383.84
        |
        | ADC:
        | Claim A 09/14 - 09/15 = $191.92
        | Claim B 09/17 - 09/18 = $191.92
        |
        | Combined = $383.84
        |--------------------------------------------------------------------------
        */

        $rangeClaims =
            DB::table('claims')
            ->where(
                'ParticipantId',
                $possibleParticipantId
            )
            ->whereRaw(
                'UPPER(TRIM(proc_code)) = ?',
                [
                    $procCode
                ]
            )
            ->whereDate(
                'service_from',
                '>=',
                $serviceFrom
            )
            ->whereDate(
                'service_to',
                '<=',
                $serviceTo
            )
            ->whereNotNull('claim_no')
            ->orderBy('service_from')
            ->orderBy('claim_no')
            ->orderBy('line_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Group By Claim Number
        |--------------------------------------------------------------------------
        */

        $rangeGroups =
            $rangeClaims
            ->groupBy(
                function ($claim) {

                    return
                        trim(
                            (string) (
                                $claim->claim_no
                                ?? ''
                            )
                        );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Claim-Level Totals
        |--------------------------------------------------------------------------
        */

        $validRangeGroups =
            $rangeGroups
            ->filter(
                function ($group) {

                    return
                        $group->isNotEmpty();
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Need More Than One Claim For Split Fallback
        |--------------------------------------------------------------------------
        */

        if (
            $validRangeGroups->count()
            >
            1
        ) {

            $combinedTotal =
                round(
                    (float) $validRangeGroups
                        ->sum(
                            function ($group) {

                                return
                                    (float) $group
                                    ->sum(
                                        function ($claim) {

                                            return
                                                (float) (
                                                    $claim->total_amount
                                                    ?? 0
                                                );
                                        }
                                    );
                            }
                        ),
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Combined Charge Must Equal ERA Charge
            |--------------------------------------------------------------------------
            */

            if (
                abs(
                    $combinedTotal
                    -
                    $totalCharge
                )
                <=
                0.02
            ) {

                /*
                |--------------------------------------------------------------------------
                | Verify Combined Date Span
                |--------------------------------------------------------------------------
                */

                $allRangeRows =
                    $validRangeGroups
                    ->flatten(1)
                    ->values();


                $earliestFrom =
                    $allRangeRows
                    ->min(
                        'service_from'
                    );


                $latestTo =
                    $allRangeRows
                    ->max(
                        'service_to'
                    );


                if (
                    (string) $earliestFrom
                    ===
                    (string) $serviceFrom
                    &&
                    (string) $latestTo
                    ===
                    (string) $serviceTo
                ) {

                    $claimNumbers =
                        $validRangeGroups
                        ->keys()
                        ->filter()
                        ->values();


                    $memberClaimMatches->push([

                        'type' =>
                        'multiple',

                        'ParticipantId' =>
                        $possibleParticipantId,

                        'claim_no' =>
                        $claimNumbers
                        ->implode(','),

                        'claim_id' =>
                        null,

                        'claims' =>
                        $allRangeRows,

                    ]);
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Evaluate Member / Claim Results
    |--------------------------------------------------------------------------
    */

    if (
        $memberClaimMatches->count()
        ===
        1
    ) {

        $resolvedMatch =
            $memberClaimMatches
            ->first();


        $participantId =
            $resolvedMatch[
                'ParticipantId'
            ];


        $claimNo =
            $resolvedMatch[
                'claim_no'
            ];


        $claimId =
            $resolvedMatch[
                'claim_id'
            ];


        /*
        |--------------------------------------------------------------------------
        | Single Claim
        |--------------------------------------------------------------------------
        */

        if (
            $resolvedMatch['type']
            ===
            'single'
        ) {

            $matchStatus =
                'MATCHED';


            $matchMessage =
                'Matched by insurance number, member, service dates, procedure and charge.';


            $matched++;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Multiple Claims Combined By Payer
            |--------------------------------------------------------------------------
            */

            $matchStatus =
                'MULTI_MATCHED';


            $matchMessage =
                'ERA payment matched multiple ADC claims whose combined dates and charges equal the ERA payment.';


            $matched++;
        }

    } elseif (
        $memberClaimMatches->count()
        >
        1
    ) {

        /*
        |--------------------------------------------------------------------------
        | More Than One Possible Safe Member/Claim Combination
        |--------------------------------------------------------------------------
        */

        $matchStatus =
            'UNMATCHED';


        $matchMessage =
            'Multiple billing member / claim combinations matched the ERA payment.';


        $unmatched++;

    } else {

        /*
        |--------------------------------------------------------------------------
        | Member Exists But No Claim Match
        |--------------------------------------------------------------------------
        */

        $matchStatus =
            'UNMATCHED';


        if (
            $members->count()
            >
            1
        ) {

            $matchMessage =
                'Multiple billing members found, but none had an ADC claim matching the ERA dates, procedure and charge.';

        } else {

            $participantId =
                trim(
                    (string) (
                        $members
                        ->first()
                        ->ParticipantId
                        ?? ''
                    )
                );


            $matchMessage =
                'Member found, but no ADC claim matched the ERA dates, procedure and charge.';
        }


        $unmatched++;
    }
}

            /*
            |--------------------------------------------------------------------------
            | Save Staging Row
            |--------------------------------------------------------------------------
            */

            DB::table(
                'legacy_era_payments'
            )
            ->insert([

                'source_file' =>
                    $sourceFileName,

                'pcn' =>
                    $pcn,

                'last_name' =>
                    $lastName,

                'first_name' =>
                    $firstName,

                'insurance_no' =>
                    $insuranceNo,

                'service_from' =>
                    $serviceFrom,

                'service_to' =>
                    $serviceTo,

                'proc_code' =>
                    $procCode,

                'total_charge' =>
                    $totalCharge,

                'amount_paid' =>
                    $amountPaid,

                'payer_icn' =>
                    $payerIcn,

                'ParticipantId' =>
                    $participantId,

                'claim_no' =>
                    $claimNo,

                'claim_id' =>
                    $claimId,

                'match_status' =>
                    $matchStatus,

                'match_message' =>
                    $matchMessage,

                'processed_yn' =>
                    'N',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),

            ]);


            $imported++;
        }


        fclose(
            $handle
        );


        return redirect()
            ->route(
                'era.legacy.payment.import.index'
            )
            ->with([
                'message' =>
                    "CSV imported. Total: {$imported}, Matched: {$matched}, Unmatched: {$unmatched}.",

                'alert-type' =>
                    $unmatched > 0
                        ? 'warning'
                        : 'success',
            ]);

    } // End Method

    public function legacyPaymentProcessSelected(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'payment_date' =>
                'required|date|before_or_equal:today',

            'payment_ids' =>
                'required|array|min:1',

            'payment_ids.*' =>
                'integer',

        ]);


        $paymentDate =
            Carbon::parse(
                $request->payment_date
            )
            ->format('Y-m-d');


        $paymentIds =
            collect(
                $request->input(
                    'payment_ids',
                    []
                )
            )
            ->map(
                fn($id) => (int) $id
            )
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $processedCount =
            0;


        $skippedCount =
            0;


        $totalPosted =
            0.00;


        $errors =
            [];


        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        $userCd =
            Auth::check()
                ? (
                    Auth::user()->user_cd
                    ?? null
                )
                : null;


        /*
        |--------------------------------------------------------------------------
        | Process Each Selected ERA Payment
        |--------------------------------------------------------------------------
        |
        | Each staging row gets its own transaction.
        |
        | Therefore:
        |
        | - one bad row does not roll back all other rows
        | - claims + transactions + processed flag stay atomic
        |--------------------------------------------------------------------------
        */

        foreach (
            $paymentIds as $paymentId
        ) {

            DB::beginTransaction();


            try {

                /*
                |--------------------------------------------------------------------------
                | Lock Staging Record
                |--------------------------------------------------------------------------
                */

                $payment =
                    DB::table('legacy_era_payments')
                    ->where(
                        'id',
                        $paymentId
                    )
                    ->lockForUpdate()
                    ->first();


                if (!$payment) {

                    throw new \RuntimeException(
                        "Legacy payment ID {$paymentId} was not found."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Already Processed
                |--------------------------------------------------------------------------
                */

                if (
                    ($payment->processed_yn ?? 'N')
                    ===
                    'Y'
                ) {

                    throw new \RuntimeException(
                        "Legacy payment ID {$paymentId} is already processed."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Only Matched Rows May Post
                |--------------------------------------------------------------------------
                */

                if (
                    !in_array(
                        $payment->match_status,
                        [
                            'MATCHED',
                            'MULTI_MATCHED',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        "Legacy payment ID {$paymentId} is not safely matched."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Payment Amount
                |--------------------------------------------------------------------------
                */

                $amountPaid =
                    round(
                        (float) (
                            $payment->amount_paid
                            ?? 0
                        ),
                        2
                    );


                if (
                    $amountPaid
                    <=
                    0
                ) {

                    throw new \RuntimeException(
                        "Legacy payment ID {$paymentId} does not contain a positive payment amount."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Claim Numbers
                |--------------------------------------------------------------------------
                |
                | MATCHED:
                |
                |     017874
                |
                | MULTI_MATCHED:
                |
                |     017755,017913
                |--------------------------------------------------------------------------
                */

                $claimNumbers =
                    collect(
                        explode(
                            ',',
                            (string) (
                                $payment->claim_no
                                ?? ''
                            )
                        )
                    )
                    ->map(
                        function ($claimNo) {

                            return trim(
                                (string) $claimNo
                            );
                        }
                    )
                    ->filter()
                    ->unique()
                    ->values();


                if (
                    $claimNumbers->isEmpty()
                ) {

                    throw new \RuntimeException(
                        "Legacy payment ID {$paymentId} does not contain a resolved claim number."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | MATCHED Must Have One Claim Number
                |--------------------------------------------------------------------------
                */

                if (
                    $payment->match_status
                    ===
                    'MATCHED'
                    &&
                    $claimNumbers->count()
                    !==
                    1
                ) {

                    throw new \RuntimeException(
                        "Legacy payment ID {$paymentId} expected one claim number."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | MULTI_MATCHED Must Have Multiple Claim Numbers
                |--------------------------------------------------------------------------
                */

                if (
                    $payment->match_status
                    ===
                    'MULTI_MATCHED'
                    &&
                    $claimNumbers->count()
                    <
                    2
                ) {

                    throw new \RuntimeException(
                        "Legacy payment ID {$paymentId} expected multiple claim numbers."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Reload Actual ADC Claim Rows
                |--------------------------------------------------------------------------
                |
                | No JOIN.
                |--------------------------------------------------------------------------
                */

                $claimRows =
                    DB::table('claims')
                    ->whereRaw(
                        'TRIM(ParticipantId) = ?',
                        [
                            trim(
                                (string) $payment->ParticipantId
                            )
                        ]
                    )
                    ->whereIn(
                        'claim_no',
                        $claimNumbers->all()
                    )
                    ->orderBy('service_from')
                    ->orderBy('claim_no')
                    ->orderBy('line_no')
                    ->lockForUpdate()
                    ->get();


                if (
                    $claimRows->isEmpty()
                ) {

                    throw new \RuntimeException(
                        "No ADC claim rows were found for legacy payment ID {$paymentId}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Confirm Every Claim Number Still Exists
                |--------------------------------------------------------------------------
                */

                $foundClaimNumbers =
                    $claimRows
                    ->pluck('claim_no')
                    ->map(
                        fn($value) =>
                        trim(
                            (string) $value
                        )
                    )
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();


                $expectedClaimNumbers =
                    $claimNumbers
                    ->sort()
                    ->values();


                if (
                    $foundClaimNumbers->all()
                    !==
                    $expectedClaimNumbers->all()
                ) {

                    throw new \RuntimeException(
                        "One or more ADC claim numbers could not be revalidated for legacy payment ID {$paymentId}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Revalidate Procedure
                |--------------------------------------------------------------------------
                */

                $expectedProcedure =
                    strtoupper(
                        trim(
                            (string) (
                                $payment->proc_code
                                ?? ''
                            )
                        )
                    );


                foreach (
                    $claimRows as $claim
                ) {

                    $claimProcedure =
                        strtoupper(
                            trim(
                                (string) (
                                    $claim->proc_code
                                    ?? ''
                                )
                            )
                        );


                    if (
                        $expectedProcedure !== ''
                        &&
                        $claimProcedure !==
                        $expectedProcedure
                    ) {

                        throw new \RuntimeException(
                            "Procedure code changed for claim {$claim->claim_no}."
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Revalidate Service Range
                |--------------------------------------------------------------------------
                */

                $serviceFrom =
                    (string) (
                        $payment->service_from
                        ?? ''
                    );


                $serviceTo =
                    (string) (
                        $payment->service_to
                        ?? ''
                    );


                foreach (
                    $claimRows as $claim
                ) {

                    if (
                        !empty($claim->service_from)
                        &&
                        !empty($serviceFrom)
                        &&
                        (string) $claim->service_from
                        <
                        $serviceFrom
                    ) {

                        throw new \RuntimeException(
                            "Claim {$claim->claim_no} begins outside the ERA service range."
                        );
                    }


                    if (
                        !empty($claim->service_to)
                        &&
                        !empty($serviceTo)
                        &&
                        (string) $claim->service_to
                        >
                        $serviceTo
                    ) {

                        throw new \RuntimeException(
                            "Claim {$claim->claim_no} ends outside the ERA service range."
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Combined Date Span Must Match ERA
                |--------------------------------------------------------------------------
                */

                $earliestFrom =
                    $claimRows
                    ->min(
                        'service_from'
                    );


                $latestTo =
                    $claimRows
                    ->max(
                        'service_to'
                    );


                if (
                    !empty($serviceFrom)
                    &&
                    (string) $earliestFrom
                    !==
                    $serviceFrom
                ) {

                    throw new \RuntimeException(
                        "ADC service-from span does not match legacy payment ID {$paymentId}."
                    );
                }


                if (
                    !empty($serviceTo)
                    &&
                    (string) $latestTo
                    !==
                    $serviceTo
                ) {

                    throw new \RuntimeException(
                        "ADC service-to span does not match legacy payment ID {$paymentId}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Revalidate Total Charge
                |--------------------------------------------------------------------------
                */

                $adcTotalCharge =
                    round(
                        (float) $claimRows
                        ->sum(
                            function ($claim) {

                                return
                                    (float) (
                                        $claim->total_amount
                                        ?? 0
                                    );
                            }
                        ),
                        2
                    );


                $eraTotalCharge =
                    round(
                        (float) (
                            $payment->total_charge
                            ?? 0
                        ),
                        2
                    );


                if (
                    abs(
                        $adcTotalCharge
                        -
                        $eraTotalCharge
                    )
                    >
                    0.02
                ) {

                    throw new \RuntimeException(
                        "ADC charge {$adcTotalCharge} does not match ERA charge {$eraTotalCharge} for legacy payment ID {$paymentId}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Duplicate Payer ICN Protection
                |--------------------------------------------------------------------------
                |
                | This protects us even if somebody deletes/truncates the staging
                | table later and imports the same clearinghouse CSV again.
                |--------------------------------------------------------------------------
                */

                $payerIcn =
                    trim(
                        (string) (
                            $payment->payer_icn
                            ?? ''
                        )
                    );


                foreach (
                    $claimRows as $claim
                ) {

                    if (
                        $payerIcn
                        ===
                        ''
                    ) {

                        continue;
                    }


                    $duplicate =
                        DB::table('claim_transactions')
                        ->where(
                            'claim_id',
                            $claim->id
                        )
                        ->where(
                            'transaction_type',
                            'payment'
                        )
                        ->where(
                            'payer_claim_number',
                            $payerIcn
                        )
                        ->exists();


                    if ($duplicate) {

                        throw new \RuntimeException(
                            "Payment with payer ICN {$payerIcn} is already posted to claim {$claim->claim_no}."
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Allocate Payment Across ADC Claim Rows
                |--------------------------------------------------------------------------
                |
                | Allocation is proportional to billed amount.
                |
                | Example:
                |
                | Claim A = $191.92
                | Claim B = $191.92
                | ERA Paid = $383.84
                |
                | Result:
                |
                | Claim A = $191.92
                | Claim B = $191.92
                |
                | For partial payment, the same proportional approach applies.
                |--------------------------------------------------------------------------
                */

                $remainingPayment =
                    $amountPaid;


                $allocationRows =
                    [];


                $claimRowCount =
                    $claimRows->count();


                foreach (
                    $claimRows as $index => $claim
                ) {

                    $claimCharge =
                        round(
                            (float) (
                                $claim->total_amount
                                ?? 0
                            ),
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Last Row Gets Rounding Remainder
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $index
                        ===
                        $claimRowCount - 1
                    ) {

                        $allocatedPayment =
                            round(
                                $remainingPayment,
                                2
                            );

                    } else {

                        if (
                            $adcTotalCharge
                            >
                            0
                        ) {

                            $allocatedPayment =
                                round(
                                    $amountPaid
                                    *
                                    (
                                        $claimCharge
                                        /
                                        $adcTotalCharge
                                    ),
                                    2
                                );

                        } else {

                            $allocatedPayment =
                                0.00;
                        }


                        $remainingPayment =
                            round(
                                $remainingPayment
                                -
                                $allocatedPayment,
                                2
                            );
                    }


                    $allocationRows[] = [

                        'claim' =>
                        $claim,

                        'payment' =>
                        $allocatedPayment,

                    ];
                }


                /*
                |--------------------------------------------------------------------------
                | Verify Allocated Payment
                |--------------------------------------------------------------------------
                */

                $allocatedTotal =
                    round(
                        (float) collect(
                            $allocationRows
                        )
                        ->sum(
                            'payment'
                        ),
                        2
                    );


                if (
                    abs(
                        $allocatedTotal
                        -
                        $amountPaid
                    )
                    >
                    0.02
                ) {

                    throw new \RuntimeException(
                        "Payment allocation failed for legacy payment ID {$paymentId}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Post Each Claim Line
                |--------------------------------------------------------------------------
                */

                foreach (
                    $allocationRows as $allocation
                ) {

                    $claim =
                        $allocation['claim'];


                    $linePayment =
                        round(
                            (float) $allocation['payment'],
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Existing Financial Values
                    |--------------------------------------------------------------------------
                    */

                    $existingPaid =
                        round(
                            (float) (
                                $claim->ins1_paid
                                ?? 0
                            ),
                            2
                        );


                    $adjustmentAmount =
                        round(
                            (float) (
                                $claim->adjustment_amount
                                ?? 0
                            ),
                            2
                        );


                    $deductionAmount =
                        round(
                            (float) (
                                $claim->deduction_amount
                                ?? 0
                            ),
                            2
                        );


                    $writeoffAmount =
                        round(
                            (float) (
                                $claim->writeoff_amount
                                ?? 0
                            ),
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | New Insurance Paid
                    |--------------------------------------------------------------------------
                    |
                    | Add instead of overwrite.
                    |--------------------------------------------------------------------------
                    */

                    $newInsurancePaid =
                        round(
                            $existingPaid
                            +
                            $linePayment,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Prevent Obvious Overpayment
                    |--------------------------------------------------------------------------
                    */

                    $maximumInsuranceAmount =
                        round(
                            max(
                                (float) (
                                    $claim->total_amount
                                    ?? 0
                                )
                                -
                                $adjustmentAmount
                                -
                                $deductionAmount
                                -
                                $writeoffAmount,
                                0
                            ),
                            2
                        );


                    if (
                        $newInsurancePaid
                        >
                        $maximumInsuranceAmount
                        +
                        0.02
                    ) {

                        throw new \RuntimeException(
                            "Posting would overpay ADC claim {$claim->claim_no}."
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | New Balance
                    |--------------------------------------------------------------------------
                    |
                    | Patient responsibility remains in the balance.
                    |--------------------------------------------------------------------------
                    */

                    $newBalance =
                        round(
                            max(
                                (float) (
                                    $claim->total_amount
                                    ?? 0
                                )
                                -
                                $newInsurancePaid
                                -
                                $adjustmentAmount
                                -
                                $deductionAmount
                                -
                                $writeoffAmount,
                                0
                            ),
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Claim Status
                    |--------------------------------------------------------------------------
                    */

                    $newStatus =
                        $newBalance
                        <=
                        0.005

                        ? 'paid'

                        : 'accepted';


                    /*
                    |--------------------------------------------------------------------------
                    | Insert Payment Transaction
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $linePayment
                        >
                        0.005
                    ) {

                        DB::table('claim_transactions')
                            ->insert([

                                'claim_id' =>
                                $claim->id,

                                'claim_no' =>
                                $claim->claim_no,

                                'ParticipantId' =>
                                $claim->ParticipantId,

                                'payer_claim_number' =>
                                $payerIcn !== ''
                                    ? $payerIcn
                                    : null,

                                'transaction_date' =>
                                $paymentDate,

                                'transaction_type' =>
                                'payment',

                                'amount' =>
                                $linePayment,

                                'description' =>
                                'Legacy ERA CSV payment'
                                .
                                ' - PCN '
                                .
                                (
                                    $payment->pcn
                                    ?? ''
                                )
                                .
                                ' - Import ID '
                                .
                                $payment->id,

                                'created_by' =>
                                $userCd,

                                'created_at' =>
                                now(),

                                'updated_at' =>
                                now(),

                            ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update ADC Claim
                    |--------------------------------------------------------------------------
                    */

                    DB::table('claims')
                        ->where(
                            'id',
                            $claim->id
                        )
                        ->update([

                            'payer_claim_number' =>
                            $payerIcn !== ''
                                ? $payerIcn
                                : (
                                    $claim->payer_claim_number
                                    ?? null
                                ),

                            'ins1_paid' =>
                            $newInsurancePaid,

                            'balance_amount' =>
                            $newBalance,

                            'paid_date' =>
                            $paymentDate,

                            'era_posted_at' =>
                            now(),

                            'status' =>
                            $newStatus,

                            'updated_at' =>
                            now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Mark Staging Payment Processed
                |--------------------------------------------------------------------------
                */

                DB::table('legacy_era_payments')
                    ->where(
                        'id',
                        $payment->id
                    )
                    ->update([

                        'processed_yn' =>
                        'Y',

                        'processed_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);


                DB::commit();


                $processedCount++;


                $totalPosted =
                    round(
                        $totalPosted
                        +
                        $amountPaid,
                        2
                    );

            } catch (\Throwable $e) {

                DB::rollBack();


                $skippedCount++;


                $errors[] =
                    "ID {$paymentId}: "
                    .
                    $e->getMessage();


                Log::warning(
                    'Legacy ERA CSV payment not posted',
                    [

                        'legacy_payment_id' =>
                        $paymentId,

                        'message' =>
                        $e->getMessage(),

                    ]
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Result Message
        |--------------------------------------------------------------------------
        */

        $message =
            "Legacy ERA posting completed. "
            .
            "Processed: {$processedCount}, "
            .
            "Skipped: {$skippedCount}, "
            .
            'Posted: $'
            .
            number_format(
                $totalPosted,
                2
            )
            .
            '.';


        if (
            !empty($errors)
        ) {

            $message .=
                ' '
                .
                implode(
                    ' | ',
                    array_slice(
                        $errors,
                        0,
                        5
                    )
                );


            if (
                count($errors)
                >
                5
            ) {

                $message .=
                    ' Additional errors were written to the Laravel log.';
            }
        }


        return redirect()
            ->route(
                'era.legacy.payment.import.index'
            )
            ->with([

                'message' =>
                $message,

                'alert-type' =>
                $skippedCount > 0
                    ? 'warning'
                    : 'success',

            ]);

    } // End Method


    public function exceptionCrossMatch($id)
    {
        /*
        |--------------------------------------------------------------------------
        | ERA Exception
        |--------------------------------------------------------------------------
        */

        $exception = DB::table('era_unmatched_claims')
            ->where('id', $id)
            ->first();

        if (!$exception) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Service Lines
        |--------------------------------------------------------------------------
        */

        $serviceLines = DB::table('era_service_lines')
            ->where(
                'era_claim_id',
                $exception->era_claim_id
            )
            ->orderBy('svc_sequence')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Current ADC Claim Rows
        |--------------------------------------------------------------------------
        */

        $currentClaimRows = DB::table('claims')
            ->where(
                'claim_no',
                $exception->normalized_claim_no
            )
            ->orderBy('line_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Cross Match Results
        |--------------------------------------------------------------------------
        */

        $results = [];


        foreach ($serviceLines as $svc) {

            /*
            |--------------------------------------------------------------------------
            | Step 1 - Check Current Claim First
            |--------------------------------------------------------------------------
            */

            $existingMatches = $currentClaimRows
                ->filter(function ($claim) use ($svc) {

                    /*
                    |--------------------------------------------------------------------------
                    | Date Match
                    |--------------------------------------------------------------------------
                    */

                    $claimFrom = !empty($claim->service_from)
                        ? date('Y-m-d', strtotime($claim->service_from))
                        : null;

                    $claimTo = !empty($claim->service_to)
                        ? date('Y-m-d', strtotime($claim->service_to))
                        : null;


                    $svcFrom = !empty($svc->service_from)
                        ? date('Y-m-d', strtotime($svc->service_from))
                        : null;

                    $svcTo = !empty($svc->service_to)
                        ? date('Y-m-d', strtotime($svc->service_to))
                        : null;


                    $dateMatch =
                        $claimFrom === $svcFrom
                        &&
                        $claimTo === $svcTo;


                    /*
                    |--------------------------------------------------------------------------
                    | Amount Match
                    |--------------------------------------------------------------------------
                    */

                    $amountMatch =
                        abs(
                            round(
                                (float) ($claim->total_amount ?? 0),
                                2
                            )
                            -
                            round(
                                (float) ($svc->charge_amount ?? 0),
                                2
                            )
                        )
                        <= 0.02;


                    /*
                    |--------------------------------------------------------------------------
                    | Procedure Match
                    |--------------------------------------------------------------------------
                    */

                    $procedureMatch =
                        trim(
                            strtoupper(
                                (string) ($claim->proc_code ?? '')
                            )
                        )
                        ===
                        trim(
                            strtoupper(
                                (string) ($svc->procedure_code ?? '')
                            )
                        );


                    $modifierMatch =
                        trim(
                            strtoupper(
                                (string) ($claim->m1 ?? '')
                            )
                        )
                        ===
                        trim(
                            strtoupper(
                                (string) ($svc->modifier1 ?? '')
                            )
                        );

                    return
                        $dateMatch
                        &&
                        $amountMatch
                        &&
                        $procedureMatch
                        &&
                        $modifierMatch;
                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Already Matched To Original ADC Claim
            |--------------------------------------------------------------------------
            */

            if ($existingMatches->count() === 1) {

                $match = $existingMatches->first();

                $results[] = [

                    'svc_sequence' => $svc->svc_sequence,

                    'service_from' => $svc->service_from,

                    'service_to' => $svc->service_to,

                    'procedure_code' => $svc->procedure_code,

                    'modifier1' => $svc->modifier1,

                    'charge_amount' => $svc->charge_amount,

                    'status' => 'CURRENT',

                    'message' =>
                        'ERA service line already matches the original ADC claim.',

                    'claim_no' => $match->claim_no,

                    'claim_id' => $match->id,
                ];


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Step 2 - Search Other ADC Claims
            |--------------------------------------------------------------------------
            */

            $candidateRows = DB::table('claims')

                ->where(
                    'ParticipantId',
                    $exception->member_id
                )

                ->whereDate(
                    'service_from',
                    $svc->service_from
                )

                ->whereDate(
                    'service_to',
                    $svc->service_to
                )

                ->where(
                    'claim_no',
                    '<>',
                    $exception->normalized_claim_no
                )

                 ->whereNotIn(
                    'status',
                    ['void', 'draft']
                )

                ->get();


            /*
            |--------------------------------------------------------------------------
            | Strict Candidate Validation
            |--------------------------------------------------------------------------
            */

            $candidateRows = $candidateRows
                ->filter(function ($claim) use ($svc) {

                    /*
                    |--------------------------------------------------------------------------
                    | Charge
                    |--------------------------------------------------------------------------
                    */

                    $amountMatch =
                        abs(
                            round(
                                (float) ($claim->total_amount ?? 0),
                                2
                            )
                            -
                            round(
                                (float) ($svc->charge_amount ?? 0),
                                2
                            )
                        )
                        <= 0.02;


                    /*
                    |--------------------------------------------------------------------------
                    | Procedure
                    |--------------------------------------------------------------------------
                    */

                    $procedureMatch =
                        trim(
                            strtoupper(
                                (string) ($claim->proc_code ?? '')
                            )
                        )
                        ===
                        trim(
                            strtoupper(
                                (string) ($svc->procedure_code ?? '')
                            )
                        );


                    $modifierMatch =
                        trim(
                            strtoupper(
                                (string) ($claim->m1 ?? '')
                            )
                        )
                        ===
                        trim(
                            strtoupper(
                                (string) ($svc->modifier1 ?? '')
                            )
                        );

                    return
                        $amountMatch
                        &&
                        $procedureMatch
                        &&
                        $modifierMatch;
                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Exactly One Candidate
            |--------------------------------------------------------------------------
            */

            if ($candidateRows->count() === 1) {

                $candidate = $candidateRows->first();

                $results[] = [

                    'svc_sequence' => $svc->svc_sequence,

                    'service_from' => $svc->service_from,

                    'service_to' => $svc->service_to,

                    'procedure_code' => $svc->procedure_code,

                    'modifier1' => $svc->modifier1,

                    'charge_amount' => $svc->charge_amount,

                    'status' => 'CROSS_MATCH',

                    'message' =>
                        'Unique cross-claim ADC match found.',

                    'claim_no' => $candidate->claim_no,

                    'claim_id' => $candidate->id,
                ];


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Multiple Candidates
            |--------------------------------------------------------------------------
            */

            if ($candidateRows->count() > 1) {

                $results[] = [

                    'svc_sequence' => $svc->svc_sequence,

                    'service_from' => $svc->service_from,

                    'service_to' => $svc->service_to,

                    'procedure_code' => $svc->procedure_code,

                    'modifier1' => $svc->modifier1,

                    'charge_amount' => $svc->charge_amount,

                    'status' => 'MULTIPLE',

                    'message' =>
                        'Multiple ADC claim candidates found. No action taken.',

                    'claim_no' => null,

                    'claim_id' => null,
                ];


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | No Candidate
            |--------------------------------------------------------------------------
            */

            $results[] = [

                'svc_sequence' => $svc->svc_sequence,

                'service_from' => $svc->service_from,

                'service_to' => $svc->service_to,

                'procedure_code' => $svc->procedure_code,

                'modifier1' => $svc->modifier1,

                'charge_amount' => $svc->charge_amount,

                'status' => 'NONE',

                'message' =>
                    'No safe cross-claim ADC match found.',

                'claim_no' => null,

                'claim_id' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Return To Exception Detail Screen
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Nothing is updated.
        | Nothing is posted.
        | Exception is NOT marked resolved.
        |
        */

        return redirect()
            ->route(
                'era.exception.show',
                $id
            )
            ->with(
                'cross_match_results',
                $results
            );

    } // End Method

    public function exceptionCrossMatchPost($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Exception
        |--------------------------------------------------------------------------
        */

        $exception = DB::table('era_unmatched_claims')
            ->where('id', $id)
            ->first();

        if (!$exception) {
            abort(404);
        }


        if ($exception->resolved_yn === 'Y') {

            return redirect()
                ->route('era.exception.show', $id)
                ->with([
                    'message' =>
                        'This ERA exception is already resolved.',

                    'alert-type' =>
                        'info',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Claim
        |--------------------------------------------------------------------------
        */

        $eraClaim = DB::table('era_claims')
            ->where('id', $exception->era_claim_id)
            ->first();

        if (!$eraClaim) {

            return redirect()
                ->route('era.exception.show', $id)
                ->with([
                    'message' =>
                        'ERA claim could not be found.',

                    'alert-type' =>
                        'danger',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA File
        |--------------------------------------------------------------------------
        */

        $file = DB::table('era_files')
            ->where('id', $exception->era_file_id)
            ->first();

        if (!$file) {

            return redirect()
                ->route('era.exception.show', $id)
                ->with([
                    'message' =>
                        'ERA file could not be found.',

                    'alert-type' =>
                        'danger',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Posting
        |--------------------------------------------------------------------------
        |
        | We do NOT delete anything here.
        |
        | If this ERA claim already produced transactions, stop and investigate.
        |--------------------------------------------------------------------------
        */

        $alreadyPosted = DB::table('claim_transactions')
            ->where(
                'era_claim_id',
                $eraClaim->id
            )
            ->exists();


        if ($alreadyPosted) {

            return redirect()
                ->route('era.exception.show', $id)
                ->with([
                    'message' =>
                        'This ERA claim already has claim transactions. '
                        . 'Cross-match posting was stopped to prevent duplicate posting.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Service Lines
        |--------------------------------------------------------------------------
        */

        $serviceLines = DB::table('era_service_lines')
            ->where(
                'era_claim_id',
                $eraClaim->id
            )
            ->orderBy('svc_sequence')
            ->get();


        if ($serviceLines->isEmpty()) {

            return redirect()
                ->route('era.exception.show', $id)
                ->with([
                    'message' =>
                        'No ERA service lines were found.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Adjustments
        |--------------------------------------------------------------------------
        */

        $adjustments = DB::table('era_adjustments')
            ->where(
                'era_claim_id',
                $eraClaim->id
            )
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Member
        |--------------------------------------------------------------------------
        */

        $participantId = trim(
            (string) (
                $exception->member_id
                ?? $eraClaim->member_id
                ?? ''
            )
        );


        if ($participantId === '') {

            return redirect()
                ->route('era.exception.show', $id)
                ->with([
                    'message' =>
                        'ERA exception does not contain a member ID.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Money Match Helper
        |--------------------------------------------------------------------------
        */

        $moneyMatches = function (
            $a,
            $b,
            $tolerance = 0.02
        ) {

            return abs(
                round((float) $a, 2)
                -
                round((float) $b, 2)
            ) <= $tolerance;
        };


        /*
        |--------------------------------------------------------------------------
        | Match Every ERA SVC To Exactly One ADC Claim
        |--------------------------------------------------------------------------
        */

        $matches = [];

        $usedClaimIds = [];


        foreach ($serviceLines as $svc) {

            /*
            |--------------------------------------------------------------------------
            | Candidate ADC Rows
            |--------------------------------------------------------------------------
            */

            $candidates = DB::table('claims')

                ->where(
                    'ParticipantId',
                    $participantId
                )

                ->whereDate(
                    'service_from',
                    $svc->service_from
                )

                ->whereDate(
                    'service_to',
                    $svc->service_to
                )

                ->whereNotIn(
                    'status',
                    ['void', 'draft']
                )

                ->get()

                ->filter(
                    function ($claim) use (
                        $svc,
                        $moneyMatches,
                        $usedClaimIds
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Claim Already Used
                        |--------------------------------------------------------------------------
                        */

                        if (
                            isset(
                                $usedClaimIds[$claim->id]
                            )
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Procedure
                        |--------------------------------------------------------------------------
                        */

                        $claimProc = strtoupper(
                            trim(
                                (string) (
                                    $claim->proc_code
                                    ?? ''
                                )
                            )
                        );


                        $svcProc = strtoupper(
                            trim(
                                (string) (
                                    $svc->procedure_code
                                    ?? ''
                                )
                            )
                        );


                        if ($claimProc !== $svcProc) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Modifier
                        |--------------------------------------------------------------------------
                        */

                        $claimMod = strtoupper(
                            trim(
                                (string) (
                                    $claim->m1
                                    ?? ''
                                )
                            )
                        );


                        $svcMod = strtoupper(
                            trim(
                                (string) (
                                    $svc->modifier1
                                    ?? ''
                                )
                            )
                        );


                        if ($claimMod !== $svcMod) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Charge
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$moneyMatches(
                                $claim->total_amount,
                                $svc->charge_amount
                            )
                        ) {
                            return false;
                        }


                        return true;
                    }
                )
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Must Be Exactly One
            |--------------------------------------------------------------------------
            */

            if ($candidates->count() !== 1) {

                return redirect()
                    ->route(
                        'era.exception.show',
                        $id
                    )
                    ->with([
                        'message' =>
                            'Cross-match posting stopped. '
                            . 'ERA SVC #'
                            . $svc->svc_sequence
                            . ' found '
                            . $candidates->count()
                            . ' ADC candidate(s). '
                            . 'Exactly one safe match is required.',

                        'alert-type' =>
                            'warning',
                    ]);
            }


            $claim =
                $candidates->first();


            $usedClaimIds[$claim->id] =
                true;


            $matches[] = [
                'svc'   => $svc,
                'claim' => $claim,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Claim-Level Adjustments
        |--------------------------------------------------------------------------
        */

        $claimLevelAdjustments =
            $adjustments
            ->filter(function ($adj) {

                return strtolower(
                    trim(
                        (string) (
                            $adj->level
                            ?? ''
                        )
                    )
                ) === 'claim';
            })
            ->values();


        $claimCO =
            0.00;

        $claimPR =
            0.00;

        $claimOther =
            0.00;


        foreach ($claimLevelAdjustments as $adj) {

            $amount =
                round(
                    (float) (
                        $adj->amount
                        ?? 0
                    ),
                    2
                );


            $group = strtoupper(
                trim(
                    (string) (
                        $adj->group_code
                        ?? ''
                    )
                )
            );


            if ($group === 'CO') {

                $claimCO +=
                    $amount;

            } elseif ($group === 'PR') {

                $claimPR +=
                    $amount;

            } else {

                $claimOther +=
                    $amount;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Allocation Helper
        |--------------------------------------------------------------------------
        */

        $allocateAmount =
            function ($amount, $rows) {

                $allocation =
                    [];


                $amount =
                    round(
                        (float) $amount,
                        2
                    );


                if ($rows->isEmpty()) {
                    return $allocation;
                }


                if (abs($amount) < 0.005) {

                    foreach ($rows as $row) {

                        $allocation[$row->id] =
                            0.00;
                    }

                    return $allocation;
                }


                $weightTotal =
                    $rows->sum(
                        function ($row) {

                            return max(
                                (float) (
                                    $row->total_amount
                                    ?? 0
                                ),
                                0
                            );
                        }
                    );


                if ($weightTotal <= 0) {

                    $weightTotal =
                        $rows->count();

                    foreach ($rows as $row) {

                        $row->_era_weight =
                            1;
                    }

                } else {

                    foreach ($rows as $row) {

                        $row->_era_weight =
                            max(
                                (float) (
                                    $row->total_amount
                                    ?? 0
                                ),
                                0
                            );
                    }
                }


                $allocated =
                    0.00;


                $rows =
                    $rows->values();


                $lastIndex =
                    $rows->count() - 1;


                foreach (
                    $rows as $index => $row
                ) {

                    if ($index === $lastIndex) {

                        $lineAmount =
                            round(
                                $amount
                                -
                                $allocated,
                                2
                            );

                    } else {

                        $lineAmount =
                            round(
                                $amount
                                *
                                (
                                    $row->_era_weight
                                    /
                                    $weightTotal
                                ),
                                2
                            );


                        $allocated +=
                            $lineAmount;
                    }


                    $allocation[$row->id] =
                        $lineAmount;
                }


                return $allocation;
            };


        /*
        |--------------------------------------------------------------------------
        | ADC Rows Used By This ERA Claim
        |--------------------------------------------------------------------------
        */

        $allocationRows =
            collect(
                array_map(
                    function ($match) {

                        return $match['claim'];
                    },
                    $matches
                )
            );


        $allocatedCO =
            $allocateAmount(
                $claimCO,
                $allocationRows
            );


        $allocatedOther =
            $allocateAmount(
                $claimOther,
                $allocationRows
            );


        /*
        |--------------------------------------------------------------------------
        | Existing Line-Level PR
        |--------------------------------------------------------------------------
        */

        $linePrTotal =
            0.00;


        foreach ($matches as $match) {

            $svc =
                $match['svc'];


            $lineAdjustments =
                $adjustments
                ->filter(
                    function ($adj) use ($svc) {

                        return
                            strtolower(
                                trim(
                                    (string) (
                                        $adj->level
                                        ?? ''
                                    )
                                )
                            )
                            ===
                            'line'

                            &&

                            (int) (
                                $adj->svc_sequence
                                ?? 0
                            )
                            ===
                            (int) (
                                $svc->svc_sequence
                                ?? 0
                            );
                    }
                );


            foreach ($lineAdjustments as $adj) {

                if (
                    strtoupper(
                        trim(
                            (string) (
                                $adj->group_code
                                ?? ''
                            )
                        )
                    )
                    ===
                    'PR'
                ) {

                    $linePrTotal +=
                        round(
                            (float) (
                                $adj->amount
                                ?? 0
                            ),
                            2
                        );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Remaining PR
        |--------------------------------------------------------------------------
        */

        if (abs($claimPR) >= 0.005) {

            $remainingPR =
                $claimPR;

        } else {

            $remainingPR =
                round(
                    (float) (
                        $eraClaim
                        ->patient_responsibility
                        ?? 0
                    )
                    -
                    $linePrTotal,
                    2
                );
        }


        $allocatedPR =
            $allocateAmount(
                $remainingPR,
                $allocationRows
            );


        /*
        |--------------------------------------------------------------------------
        | Build Posting Plans
        |--------------------------------------------------------------------------
        */

        $postingPlans =
            [];


        foreach ($matches as $match) {

            $svc =
                $match['svc'];


            $claim =
                $match['claim'];


            $svcSequence =
                (int) (
                    $svc->svc_sequence
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | Line Adjustments
            |--------------------------------------------------------------------------
            */

            $lineAdjustments =
                $adjustments
                ->filter(
                    function ($adj)
                    use ($svcSequence) {

                        return
                            strtolower(
                                trim(
                                    (string) (
                                        $adj->level
                                        ?? ''
                                    )
                                )
                            )
                            ===
                            'line'

                            &&

                            (int) (
                                $adj->svc_sequence
                                ?? 0
                            )
                            ===
                            $svcSequence;
                    }
                )
                ->values();


            $lineCO =
                0.00;

            $linePR =
                0.00;

            $lineOther =
                0.00;


            foreach ($lineAdjustments as $adj) {

                $amount =
                    round(
                        (float) (
                            $adj->amount
                            ?? 0
                        ),
                        2
                    );


                $group =
                    strtoupper(
                        trim(
                            (string) (
                                $adj->group_code
                                ?? ''
                            )
                        )
                    );


                if ($group === 'CO') {

                    $lineCO +=
                        $amount;

                } elseif ($group === 'PR') {

                    $linePR +=
                        $amount;

                } else {

                    $lineOther +=
                        $amount;
                }
            }


            $paidAmount =
                round(
                    (float) (
                        $svc->paid_amount
                        ?? 0
                    ),
                    2
                );


            $contractualAdj =
                round(
                    $lineCO
                    +
                    (
                        $allocatedCO[$claim->id]
                        ?? 0
                    ),
                    2
                );


            $patientResp =
                round(
                    $linePR
                    +
                    (
                        $allocatedPR[$claim->id]
                        ?? 0
                    ),
                    2
                );


            $otherAdj =
                round(
                    $lineOther
                    +
                    (
                        $allocatedOther[$claim->id]
                        ?? 0
                    ),
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Fallback CO
            |--------------------------------------------------------------------------
            */

            if (
                abs($contractualAdj) < 0.005
                &&
                abs($claimCO) < 0.005
            ) {

                $fallback =
                    round(
                        (float) $claim->total_amount
                        -
                        $paidAmount
                        -
                        $patientResp
                        -
                        $otherAdj,
                        2
                    );


                if ($fallback > 0) {

                    $contractualAdj =
                        $fallback;
                }
            }


            $postingPlans[] = [

                'svc' =>
                    $svc,

                'claim' =>
                    $claim,

                'paid_amount' =>
                    $paidAmount,

                'patient_responsibility' =>
                    $patientResp,

                'contractual_adjustment' =>
                    $contractualAdj,

                'other_adjustment' =>
                    $otherAdj,

                'line_adjustments' =>
                    $lineAdjustments,

                'allocated_claim_adjustments' => [

                    'CO' =>
                        $allocatedCO[$claim->id]
                        ?? 0,

                    'PR' =>
                        $allocatedPR[$claim->id]
                        ?? 0,

                    'OTHER' =>
                        $allocatedOther[$claim->id]
                        ?? 0,
                ],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        $userCd =
            null;


        if (
            Auth::check()
            &&
            Auth::user()
        ) {

            $userCd =
                Auth::user()->user_cd
                ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | Post
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            foreach ($postingPlans as $plan) {

                $svc =
                    $plan['svc'];


                $claim =
                    $plan['claim'];


                $paidAmount =
                    round(
                        (float) $plan['paid_amount'],
                        2
                    );


                $patientResp =
                    round(
                        (float) $plan['patient_responsibility'],
                        2
                    );


                $contractualAdj =
                    round(
                        (float) $plan['contractual_adjustment'],
                        2
                    );


                $otherAdj =
                    round(
                        (float) $plan['other_adjustment'],
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Balance
                |--------------------------------------------------------------------------
                */

                $balanceAmount =
                    round(
                        max(
                            (float) $claim->total_amount
                            -
                            $paidAmount
                            -
                            $contractualAdj
                            -
                            $otherAdj
                            -
                            (float) (
                                $claim->writeoff_amount
                                ?? 0
                            ),
                            0
                        ),
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                $hasCO16 =
                    $plan['line_adjustments']
                    ->contains(
                        function ($adj) {

                            return
                                strtoupper(
                                    trim(
                                        (string) (
                                            $adj->group_code
                                            ?? ''
                                        )
                                    )
                                )
                                ===
                                'CO'

                                &&

                                trim(
                                    (string) (
                                        $adj->reason_code
                                        ?? ''
                                    )
                                )
                                ===
                                '16';
                        }
                    );


                if (
                    (string) (
                        $eraClaim->claim_status_code
                        ?? ''
                    )
                    ===
                    '4'
                ) {

                    $status =
                        'denied';

                } elseif ($hasCO16) {

                    $status =
                        'rejected';

                } elseif (
                    $paidAmount <= 0.005
                    &&
                    $balanceAmount <= 0.005
                    &&
                    (
                        $contractualAdj > 0.005
                        ||
                        $otherAdj > 0.005
                    )
                ) {

                    $status =
                        'denied';

                } elseif (
                    $paidAmount > 0.005
                    &&
                    $balanceAmount <= 0.005
                ) {

                    $status =
                        'paid';

                } else {

                    $status =
                        'accepted';
                }


                /*
                |--------------------------------------------------------------------------
                | Payment Transaction
                |--------------------------------------------------------------------------
                */

                if (
                    abs($paidAmount)
                    >=
                    0.005
                ) {

                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                                $claim->id,

                            'era_file_id' =>
                                $file->id,

                            'era_claim_id' =>
                                $eraClaim->id,

                            'claim_no' =>
                                $claim->claim_no,

                            'ParticipantId' =>
                                $claim->ParticipantId,

                            'payer_claim_number' =>
                                $eraClaim->payer_claim_number,

                            'transaction_date' =>
                                $file->payment_date
                                ??
                                now()->toDateString(),

                            'transaction_type' =>
                                'payment',

                            'amount' =>
                                $paidAmount,

                            'description' =>
                                'ERA cross-claim insurance payment - SVC '
                                .
                                $svc->svc_sequence,

                            'created_by' =>
                                $userCd,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Actual Line-Level CAS
                |--------------------------------------------------------------------------
                */

                foreach (
                    $plan['line_adjustments']
                    as $adj
                ) {

                    $group =
                        strtoupper(
                            trim(
                                (string) (
                                    $adj->group_code
                                    ?? ''
                                )
                            )
                        );


                    $transactionType =
                        'deduction';


                    if ($group === 'CO') {

                        $transactionType =
                            'contractual_adjustment';

                    } elseif ($group === 'PR') {

                        $transactionType =
                            'patient_responsibility';
                    }


                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                                $claim->id,

                            'era_file_id' =>
                                $file->id,

                            'era_claim_id' =>
                                $eraClaim->id,

                            'claim_no' =>
                                $claim->claim_no,

                            'ParticipantId' =>
                                $claim->ParticipantId,

                            'payer_claim_number' =>
                                $eraClaim->payer_claim_number,

                            'transaction_date' =>
                                $file->payment_date
                                ??
                                now()->toDateString(),

                            'transaction_type' =>
                                $transactionType,

                            'amount' =>
                                round(
                                    (float) (
                                        $adj->amount
                                        ?? 0
                                    ),
                                    2
                                ),

                            'group_code' =>
                                $adj->group_code,

                            'reason_code' =>
                                $adj->reason_code,

                            'description' =>
                                'ERA cross-claim CAS - SVC '
                                .
                                $svc->svc_sequence,

                            'created_by' =>
                                $userCd,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Fallback / Claim-Level Adjustment Transactions
                |--------------------------------------------------------------------------
                */

                $actualLineCO =
                    $plan['line_adjustments']
                    ->filter(
                        function ($adj) {

                            return
                                strtoupper(
                                    trim(
                                        (string) (
                                            $adj->group_code
                                            ?? ''
                                        )
                                    )
                                )
                                ===
                                'CO';
                        }
                    )
                    ->sum('amount');


                $recordedCO =
                    round(
                        (float) $actualLineCO,
                        2
                    );


                $fallbackCO =
                    round(
                        $contractualAdj
                        -
                        $recordedCO,
                        2
                    );


                if (abs($fallbackCO) >= 0.005) {

                    DB::table('claim_transactions')
                        ->insert([

                            'claim_id' =>
                                $claim->id,

                            'era_file_id' =>
                                $file->id,

                            'era_claim_id' =>
                                $eraClaim->id,

                            'claim_no' =>
                                $claim->claim_no,

                            'ParticipantId' =>
                                $claim->ParticipantId,

                            'payer_claim_number' =>
                                $eraClaim->payer_claim_number,

                            'transaction_date' =>
                                $file->payment_date
                                ??
                                now()->toDateString(),

                            'transaction_type' =>
                                'contractual_adjustment',

                            'amount' =>
                                $fallbackCO,

                            'group_code' =>
                                'CO',

                            'description' =>
                                'ERA cross-claim allocated/calculated CO - SVC '
                                .
                                $svc->svc_sequence,

                            'created_by' =>
                                $userCd,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Update ADC Claim
                |--------------------------------------------------------------------------
                */

                DB::table('claims')
                    ->where(
                        'id',
                        $claim->id
                    )
                    ->update([

                        'payer_claim_number' =>
                            $eraClaim->payer_claim_number,

                        'allowed' =>
                            round(
                                max(
                                    $paidAmount
                                    +
                                    $patientResp,
                                    0
                                ),
                                2
                            ),

                        'ins1_paid' =>
                            $paidAmount,

                        'adjustment_amount' =>
                            $contractualAdj,

                        'patient_responsibility' =>
                            $patientResp,

                        'deduction_amount' =>
                            $otherAdj,

                        'balance_amount' =>
                            $balanceAmount,

                        'paid_date' =>
                            $file->payment_date,

                        'era_posted_at' =>
                            now(),

                        'status' =>
                            $status,

                        'updated_at' =>
                            now(),

                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Mark Exception Resolved
            |--------------------------------------------------------------------------
            */

            DB::table('era_unmatched_claims')
                ->where(
                    'id',
                    $exception->id
                )
                ->update([

                    'resolved_yn' =>
                        'Y',

                    'resolved_at' =>
                        now(),

                    'updated_at' =>
                        now(),

                ]);


            DB::commit();


            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'Cross-claim ERA posted successfully. '
                        .
                        count($postingPlans)
                        .
                        ' service line(s) were posted.',

                    'alert-type' =>
                        'success',
                ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);


            return redirect()
                ->route(
                    'era.exception.show',
                    $id
                )
                ->with([
                    'message' =>
                        'Cross-claim ERA was NOT posted. '
                        .
                        $e->getMessage(),

                    'alert-type' =>
                        'danger',
                ]);
        }

    } // End Method

    /*
|--------------------------------------------------------------------------
| Normalize ERA Claim Number For ADC Lookup
|--------------------------------------------------------------------------
|
| Historical ERA files may contain:
|
|     17694
|
| while the ADC claims table contains:
|
|     017694
|
| Numeric claim numbers shorter than 6 digits are padded for lookup only.
| Nothing in era_claims is changed.
|--------------------------------------------------------------------------
*/

private function normalizeEraClaimNoForLookup($claimNo): string
{
    $claimNo = trim(
        (string) $claimNo
    );

    if (
        $claimNo !== ''
        &&
        ctype_digit($claimNo)
        &&
        strlen($claimNo) < 6
    ) {
        $claimNo = str_pad(
            $claimNo,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    return $claimNo;
}


/*
|--------------------------------------------------------------------------
| ERA Denial / Resolution Exceptions
|--------------------------------------------------------------------------
|
| Shows ERA claims that were:
|
|     - CLP status 4
|       OR
|     - paid $0.00
|
| and determines whether a later ERA for the same claim ultimately paid it.
|
| This report does NOT modify claim financial data.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| ERA Denial / Resolution Exceptions
|--------------------------------------------------------------------------
|
| ONE work-queue record per claim number.
|
| If the same claim has multiple denial / zero-payment ERA adjudications,
| only the MOST RECENT denial becomes the current exception.
|
| Earlier ERA adjudications remain untouched in era_claims and can still
| be viewed in ERA history.
|--------------------------------------------------------------------------
*/

public function denialExceptions(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | 1. Screen Filter
    |--------------------------------------------------------------------------
    */

    $filter = strtolower(
        trim(
            (string) $request->get(
                'filter',
                'active'
            )
        )
    );


    if (
        !in_array(
            $filter,
            [
                'active',
                'resolved',
                'archived',
                'all',
            ],
            true
        )
    ) {
        $filter = 'active';
    }


    /*
    |--------------------------------------------------------------------------
    | 2. Get All Denial / Zero-Payment ERA Candidates
    |--------------------------------------------------------------------------
    |
    | CLP status 4 = denied
    |
    | OR
    |
    | total_paid = 0
    |
    | Status 22 reversals are excluded.
    |--------------------------------------------------------------------------
    */

    $denialCandidates = DB::table(
        'era_claims'
    )

        ->whereNotNull(
            'patient_control_number'
        )

        ->where(
            'patient_control_number',
            '<>',
            ''
        )

        ->where(function ($query) {

            $query
                ->where(
                    'claim_status_code',
                    '4'
                )

                ->orWhere(function ($query2) {

                    $query2->whereRaw(
                        'ABS(COALESCE(total_paid, 0)) < 0.005'
                    );
                });
        })

        ->where(function ($query) {

            $query
                ->whereNull(
                    'claim_status_code'
                )

                ->orWhere(
                    'claim_status_code',
                    '<>',
                    '22'
                );
        })

        ->orderByDesc(
            'id'
        )

        ->get();


    /*
    |--------------------------------------------------------------------------
    | Nothing Found
    |--------------------------------------------------------------------------
    */

    if ($denialCandidates->isEmpty()) {

        $exceptions = collect();

        $counts = [
            'active'   => 0,
            'open'     => 0,
            'partial'  => 0,
            'resolved' => 0,
            'archived' => 0,
        ];


        return view(
            'era.era_denial_exceptions',
            compact(
                'exceptions',
                'filter',
                'counts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 3. ONE Current Denial Exception Per Claim Number
    |--------------------------------------------------------------------------
    |
    | Normalize first:
    |
    |     17694
    |     017694
    |
    | both become:
    |
    |     017694
    |
    | Because records are already ordered newest first, first() gives us
    | the newest denial / zero-payment adjudication for that claim.
    |--------------------------------------------------------------------------
    */

    $denials = $denialCandidates

        ->groupBy(
            function ($row) {

                return $this->normalizeEraClaimNoForLookup(
                    $row->patient_control_number
                    ?? ''
                );
            }
        )

        ->map(
            function ($rows) {

                return $rows
                    ->sortByDesc('id')
                    ->first();
            }
        )

        ->filter()

        ->values();


    /*
    |--------------------------------------------------------------------------
    | 4. Normalized ADC Claim Numbers
    |--------------------------------------------------------------------------
    */

    $claimNos = $denials

        ->pluck(
            'patient_control_number'
        )

        ->map(
            fn($value) =>
            $this->normalizeEraClaimNoForLookup(
                $value
            )
        )

        ->filter()

        ->unique()

        ->values();


    /*
    |--------------------------------------------------------------------------
    | 5. ERA Lookup Variants
    |--------------------------------------------------------------------------
    |
    | We support historical ERA claim values such as:
    |
    |     17694
    |
    | and current:
    |
    |     017694
    |--------------------------------------------------------------------------
    */

    $eraLookupValues = $claimNos

        ->flatMap(
            function ($claimNo) {

                $values = [
                    $claimNo,
                ];


                if (
                    ctype_digit(
                        (string) $claimNo
                    )
                ) {

                    $unpadded = ltrim(
                        (string) $claimNo,
                        '0'
                    );


                    if ($unpadded !== '') {
                        $values[] =
                            $unpadded;
                    }
                }


                return $values;
            }
        )

        ->filter()

        ->unique()

        ->values();


    /*
    |--------------------------------------------------------------------------
    | 6. Current Denial ERA Claim IDs
    |--------------------------------------------------------------------------
    */

    $denialEraClaimIds = $denials

        ->pluck(
            'id'
        )

        ->map(
            fn($id) =>
            (int) $id
        )

        ->values();


    /*
    |--------------------------------------------------------------------------
    | 7. Complete ERA History For These Claims
    |--------------------------------------------------------------------------
    */

    $allEraClaims = DB::table(
        'era_claims'
    )

        ->whereIn(
            'patient_control_number',
            $eraLookupValues
        )

        ->orderBy(
            'id'
        )

        ->get();


    /*
    |--------------------------------------------------------------------------
    | Group ERA History By Normalized Claim Number
    |--------------------------------------------------------------------------
    */

    $eraClaimsByClaimNo =
        $allEraClaims->groupBy(
            function ($row) {

                return $this->normalizeEraClaimNoForLookup(
                    $row->patient_control_number
                    ?? ''
                );
            }
        );


    /*
    |--------------------------------------------------------------------------
    | 8. ERA Files
    |--------------------------------------------------------------------------
    */

    $eraFileIds = $allEraClaims

        ->pluck(
            'era_file_id'
        )

        ->merge(
            $denials->pluck(
                'era_file_id'
            )
        )

        ->filter()

        ->unique()

        ->values();


    $eraFiles = DB::table(
        'era_files'
    )

        ->whereIn(
            'id',
            $eraFileIds
        )

        ->get()

        ->keyBy(
            'id'
        );


    /*
    |--------------------------------------------------------------------------
    | 9. Current ADC Claim Rows
    |--------------------------------------------------------------------------
    */

    $claimRows = DB::table(
        'claims'
    )

        ->whereIn(
            'claim_no',
            $claimNos
        )

        ->select(
            'id',
            'claim_no',
            'ParticipantId',
            'service_from',
            'service_to',
            'total_amount',
            'ins1_paid',
            'balance_amount',
            'status',
            'line_no'
        )

        ->orderBy(
            'claim_no'
        )

        ->orderBy(
            'line_no'
        )

        ->get();


    $claimsByClaimNo =
        $claimRows->groupBy(
            function ($row) {

                return $this->normalizeEraClaimNoForLookup(
                    $row->claim_no
                    ?? ''
                );
            }
        );


    /*
    |--------------------------------------------------------------------------
    | 10. Members
    |--------------------------------------------------------------------------
    */

    $participantIds = $claimRows

        ->pluck(
            'ParticipantId'
        )

        ->filter()

        ->map(
            fn($id) =>
            trim(
                (string) $id
            )
        )

        ->unique()

        ->values();


    $members = DB::table(
        'billing_members'
    )

        ->whereIn(
            'ParticipantId',
            $participantIds
        )

        ->select(
            'ParticipantId',
            'FirstName',
            'LastName'
        )

        ->get();


    $membersById =
        $members->keyBy(
            function ($member) {

                return trim(
                    (string) (
                        $member->ParticipantId
                        ?? ''
                    )
                );
            }
        );


    /*
    |--------------------------------------------------------------------------
    | 11. Denial Reasons For Current Exception Only
    |--------------------------------------------------------------------------
    */

    $adjustments = DB::table(
        'era_adjustments'
    )

        ->whereIn(
            'era_claim_id',
            $denialEraClaimIds
        )

        ->select(
            'era_claim_id',
            'level',
            'group_code',
            'reason_code',
            'amount'
        )

        ->orderBy(
            'id'
        )

        ->get();


    $adjustmentsByEraClaim =
        $adjustments->groupBy(
            'era_claim_id'
        );


    /*
    |--------------------------------------------------------------------------
    | 12. Manual Resolution / Archive Actions
    |--------------------------------------------------------------------------
    */

    $actions = DB::table(
        'era_denial_exception_actions'
    )

        ->whereIn(
            'denial_era_claim_id',
            $denialEraClaimIds
        )

        ->get()

        ->keyBy(
            'denial_era_claim_id'
        );


    /*
    |--------------------------------------------------------------------------
    | 13. Build Work Queue
    |--------------------------------------------------------------------------
    */

    $exceptions = collect();


    foreach ($denials as $denial) {

        /*
        |--------------------------------------------------------------------------
        | Original + Normalized Claim Number
        |--------------------------------------------------------------------------
        */

        $eraClaimNo = trim(
            (string) (
                $denial->patient_control_number
                ?? ''
            )
        );


        $claimNo =
            $this->normalizeEraClaimNoForLookup(
                $eraClaimNo
            );


        if ($claimNo === '') {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Denial ERA File
        |--------------------------------------------------------------------------
        */

        $denialFile =
            $eraFiles->get(
                $denial->era_file_id
            );


        /*
        |--------------------------------------------------------------------------
        | ADC Claim Rows
        |--------------------------------------------------------------------------
        */

        $relatedClaimRows =
            $claimsByClaimNo->get(
                $claimNo,
                collect()
            );


        $firstClaim =
            $relatedClaimRows->first();


        /*
        |--------------------------------------------------------------------------
        | Participant
        |--------------------------------------------------------------------------
        */

        $participantId = trim(
            (string) (
                $firstClaim->ParticipantId
                ?? $denial->member_id
                ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Member
        |--------------------------------------------------------------------------
        */

        $member =
            $membersById->get(
                $participantId
            );


        $memberName = trim(
            (
                $member->LastName
                ?? ''
            )
            .
            (
                !empty(
                    $member->LastName
                )
                &&
                !empty(
                    $member->FirstName
                )
                    ? ', '
                    : ''
            )
            .
            (
                $member->FirstName
                ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Current Claim Financial Totals
        |--------------------------------------------------------------------------
        */

        $currentBalance =
            round(
                (float)
                $relatedClaimRows->sum(
                    'balance_amount'
                ),
                2
            );


        $currentPaid =
            round(
                (float)
                $relatedClaimRows->sum(
                    'ins1_paid'
                ),
                2
            );


        $claimTotal =
            round(
                (float)
                $relatedClaimRows->sum(
                    'total_amount'
                ),
                2
            );


        if (
            $claimTotal <= 0.005
        ) {

            $claimTotal =
                round(
                    (float) (
                        $denial->total_charge
                        ?? 0
                    ),
                    2
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Service Dates
        |--------------------------------------------------------------------------
        */

        $serviceFrom =
            $relatedClaimRows
            ->pluck(
                'service_from'
            )
            ->filter()
            ->min();


        $serviceTo =
            $relatedClaimRows
            ->pluck(
                'service_to'
            )
            ->filter()
            ->max();


        if (!$serviceFrom) {

            $serviceFrom =
                $denial->service_from
                ?? null;
        }


        if (!$serviceTo) {

            $serviceTo =
                $denial->service_to
                ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | Complete ERA History
        |--------------------------------------------------------------------------
        */

        $claimEraHistory =
            $eraClaimsByClaimNo->get(
                $claimNo,
                collect()
            );


        /*
        |--------------------------------------------------------------------------
        | Only ERA Events AFTER Current Denial
        |--------------------------------------------------------------------------
        */

        $laterEraClaims =
            $claimEraHistory

            ->filter(
                function ($row) use ($denial) {

                    return
                        (int) $row->id
                        >
                        (int) $denial->id

                        &&

                        (
                            (string) (
                                $row->claim_status_code
                                ?? ''
                            )
                            !==
                            '22'
                        );
                }
            )

            ->values();


        /*
        |--------------------------------------------------------------------------
        | Later Positive ERA Payments
        |--------------------------------------------------------------------------
        */

        $laterPaidClaims =
            $laterEraClaims

            ->filter(
                function ($row) {

                    return
                        (float) (
                            $row->total_paid
                            ?? 0
                        )
                        >
                        0.005;
                }
            )

            ->values();


        $latestPaidEra =
            $laterPaidClaims
            ->sortByDesc(
                'id'
            )
            ->first();


        $laterPaymentFound =
            $latestPaidEra !== null;


        $latestPaidAmount =
            $latestPaidEra

                ? round(
                    (float) (
                        $latestPaidEra->total_paid
                        ?? 0
                    ),
                    2
                )

                : 0.00;


        $latestPaidFile =
            $latestPaidEra

                ? $eraFiles->get(
                    $latestPaidEra->era_file_id
                )

                : null;


        /*
        |--------------------------------------------------------------------------
        | Manual / Archive Action
        |--------------------------------------------------------------------------
        */

        $action =
            $actions->get(
                $denial->id
            );


        $manualResolved =
            $action
            &&
            !empty(
                $action->resolved_manually_at
            );


        $archived =
            $action
            &&
            !empty(
                $action->archived_at
            );


        /*
        |--------------------------------------------------------------------------
        | Automatic Resolution
        |--------------------------------------------------------------------------
        */

        $autoResolved =
            $laterPaymentFound
            &&
            $currentBalance <= 0.005;


        /*
        |--------------------------------------------------------------------------
        | Current Exception Status
        |--------------------------------------------------------------------------
        */

        if ($archived) {

            $reportStatus =
                'ARCHIVED';

        } elseif ($manualResolved) {

            $reportStatus =
                'RESOLVED';

        } elseif ($autoResolved) {

            $reportStatus =
                'RESOLVED';

        } elseif ($laterPaymentFound) {

            $reportStatus =
                'PARTIAL';

        } else {

            $reportStatus =
                'OPEN';
        }


        /*
        |--------------------------------------------------------------------------
        | Resolution Source
        |--------------------------------------------------------------------------
        */

        $resolutionSource =
            null;


        if ($manualResolved) {

            $resolutionSource =
                'Manual';

        } elseif ($autoResolved) {

            $resolutionSource =
                'Insurance / ERA';

        } elseif ($laterPaymentFound) {

            $resolutionSource =
                'Partial ERA Payment';
        }


        /*
        |--------------------------------------------------------------------------
        | Denial Reasons
        |--------------------------------------------------------------------------
        */

        $denialAdjustments =
            $adjustmentsByEraClaim->get(
                $denial->id,
                collect()
            );


        $reasonParts =
            $denialAdjustments

            ->map(
                function ($adj) {

                    $group = trim(
                        (string) (
                            $adj->group_code
                            ?? ''
                        )
                    );


                    $reason = trim(
                        (string) (
                            $adj->reason_code
                            ?? ''
                        )
                    );


                    if (
                        $group === ''
                        &&
                        $reason === ''
                    ) {
                        return null;
                    }


                    $text = trim(
                        $group
                        .
                        (
                            $group !== ''
                            &&
                            $reason !== ''
                                ? '-'
                                : ''
                        )
                        .
                        $reason
                    );


                    $amount =
                        round(
                            (float) (
                                $adj->amount
                                ?? 0
                            ),
                            2
                        );


                    if (
                        abs($amount)
                        >=
                        0.005
                    ) {

                        $text .=
                            ' ($'
                            .
                            number_format(
                                $amount,
                                2
                            )
                            .
                            ')';
                    }


                    return $text;
                }
            )

            ->filter()

            ->unique()

            ->values();


        /*
        |--------------------------------------------------------------------------
        | Fallback Reason
        |--------------------------------------------------------------------------
        */

        $denialReason =
            $reasonParts->isNotEmpty()

                ? $reasonParts->implode(
                    ', '
                )

                : (
                    (string) (
                        $denial->claim_status_code
                        ?? ''
                    )
                    ===
                    '4'

                    ? 'CLP Status 4 - Denied'

                    : 'Zero Payment'
                );


        /*
        |--------------------------------------------------------------------------
        | Add ONE Work Queue Row
        |--------------------------------------------------------------------------
        */

        $exceptions->push(
            (object) [

                'era_claim_no' =>
                    $eraClaimNo,

                'claim_no' =>
                    $claimNo,

                'denial_era_claim_id' =>
                    $denial->id,

                'denial_era_file_id' =>
                    $denial->era_file_id,

                'ParticipantId' =>
                    $participantId,

                'member_name' =>
                    $memberName,

                'service_from' =>
                    $serviceFrom,

                'service_to' =>
                    $serviceTo,

                'claim_total' =>
                    $claimTotal,

                'original_paid' =>
                    round(
                        (float) (
                            $denial->total_paid
                            ?? 0
                        ),
                        2
                    ),

                'current_paid' =>
                    $currentPaid,

                'current_balance' =>
                    $currentBalance,

                'claim_status_code' =>
                    $denial->claim_status_code,

                'denial_reason' =>
                    $denialReason,

                'denial_date' =>
                    $denialFile->payment_date
                    ?? null,

                'denial_payer' =>
                    $denialFile->payer_name
                    ?? null,

                'denial_trace_number' =>
                    $denialFile->trace_number
                    ?? null,

                'latest_paid_era_claim_id' =>
                    $latestPaidEra->id
                    ?? null,

                'latest_paid_era_file_id' =>
                    $latestPaidEra->era_file_id
                    ?? null,

                'latest_paid_amount' =>
                    $latestPaidAmount,

                'latest_paid_date' =>
                    $latestPaidFile->payment_date
                    ?? null,

                'latest_paid_payer' =>
                    $latestPaidFile->payer_name
                    ?? null,

                'report_status' =>
                    $reportStatus,

                'resolution_source' =>
                    $resolutionSource,

                'resolution_type' =>
                    $action->resolution_type
                    ?? null,

                'resolution_note' =>
                    $action->resolution_note
                    ?? null,

                'resolved_manually_at' =>
                    $action->resolved_manually_at
                    ?? null,

                'resolved_manually_by' =>
                    $action->resolved_manually_by
                    ?? null,

                'archived_at' =>
                    $action->archived_at
                    ?? null,

                'archived_by' =>
                    $action->archived_by
                    ?? null,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 14. Summary Counts
    |--------------------------------------------------------------------------
    */

    $counts = [

        'active' =>
            $exceptions
                ->filter(
                    fn($row) =>
                    empty(
                        $row->archived_at
                    )
                )
                ->count(),

        'open' =>
            $exceptions
                ->where(
                    'report_status',
                    'OPEN'
                )
                ->count(),

        'partial' =>
            $exceptions
                ->where(
                    'report_status',
                    'PARTIAL'
                )
                ->count(),

        'resolved' =>
            $exceptions
                ->where(
                    'report_status',
                    'RESOLVED'
                )
                ->count(),

        'archived' =>
            $exceptions
                ->where(
                    'report_status',
                    'ARCHIVED'
                )
                ->count(),
    ];


    /*
    |--------------------------------------------------------------------------
    | 15. Apply Filter
    |--------------------------------------------------------------------------
    */

    if ($filter === 'active') {

        $exceptions =
            $exceptions->filter(
                fn($row) =>
                empty(
                    $row->archived_at
                )
            );

    } elseif (
        $filter === 'resolved'
    ) {

        $exceptions =
            $exceptions->filter(
                function ($row) {

                    return
                        $row->report_status
                        ===
                        'RESOLVED'

                        &&

                        empty(
                            $row->archived_at
                        );
                }
            );

    } elseif (
        $filter === 'archived'
    ) {

        $exceptions =
            $exceptions->filter(
                fn($row) =>
                !empty(
                    $row->archived_at
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | 16. Sort Work Queue
    |--------------------------------------------------------------------------
    */

    $exceptions =
        $exceptions

        ->sortBy(
            function ($row) {

                $priority = [
                    'OPEN'     => 1,
                    'PARTIAL'  => 2,
                    'RESOLVED' => 3,
                    'ARCHIVED' => 4,
                ];


                return sprintf(
                    '%02d-%010d',
                    $priority[
                        $row->report_status
                    ]
                        ?? 9,
                    9999999999
                        -
                        (int)
                        $row->denial_era_claim_id
                );
            }
        )

        ->values();


    /*
    |--------------------------------------------------------------------------
    | 17. View
    |--------------------------------------------------------------------------
    */

    return view(
        'era.era_denial_exceptions',
        compact(
            'exceptions',
            'filter',
            'counts'
        )
    );

} // End Method


/*
|--------------------------------------------------------------------------
| Manually Resolve ERA Denial Exception
|--------------------------------------------------------------------------
|
| Administrative resolution only.
| Does NOT modify claims financial data.
|--------------------------------------------------------------------------
*/

public function denialExceptionManualResolve(
    Request $request,
    $eraClaimId
) {

    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $request->validate([
        'resolution_note' =>
        'required|string|max:500',
    ]);


    /*
    |--------------------------------------------------------------------------
    | ERA Claim
    |--------------------------------------------------------------------------
    */

    $denial = DB::table(
        'era_claims'
    )

        ->where(
            'id',
            $eraClaimId
        )

        ->first();


    if (!$denial) {

        return back()->with([
            'message' =>
            'ERA denial claim was not found.',

            'alert-type' =>
            'error',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Claim Number
    |--------------------------------------------------------------------------
    */

    $eraClaimNo = trim(
        (string) (
            $denial->patient_control_number
            ?? ''
        )
    );


    $claimNo =
        $this->normalizeEraClaimNoForLookup(
            $eraClaimNo
        );


    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    $userCd =
        Auth::check()

        ? (
            Auth::user()->user_cd
            ?? (string) Auth::id()
        )

        : null;


    /*
    |--------------------------------------------------------------------------
    | Existing Action
    |--------------------------------------------------------------------------
    */

    $existingAction =
        DB::table(
            'era_denial_exception_actions'
        )

        ->where(
            'denial_era_claim_id',
            $denial->id
        )

        ->first();


    /*
    |--------------------------------------------------------------------------
    | Save Manual Resolution
    |--------------------------------------------------------------------------
    */

    DB::table(
        'era_denial_exception_actions'
    )

        ->updateOrInsert(

            [
                'denial_era_claim_id' =>
                $denial->id,
            ],

            [
                'claim_no' =>
                $claimNo,

                'resolution_type' =>
                'manual',

                'resolution_note' =>
                trim(
                    (string)
                    $request->resolution_note
                ),

                'resolved_manually_at' =>
                now(),

                'resolved_manually_by' =>
                $userCd,

                'updated_at' =>
                now(),

                'created_at' =>
                $existingAction->created_at
                ?? now(),
            ]
        );


    return back()->with([
        'message' =>
        "Claim {$claimNo} marked resolved manually.",

        'alert-type' =>
        'success',
    ]);

} // End Method


/*
|--------------------------------------------------------------------------
| Archive Resolved ERA Denial Exception
|--------------------------------------------------------------------------
|
| Only resolved exceptions can be archived.
|
| Resolved means:
|
|     - manually resolved
|
| OR
|
|     - later positive ERA payment exists
|       AND current ADC claim balance = 0
|--------------------------------------------------------------------------
*/

public function denialExceptionArchive(
    $eraClaimId
) {

    /*
    |--------------------------------------------------------------------------
    | ERA Denial
    |--------------------------------------------------------------------------
    */

    $denial = DB::table(
        'era_claims'
    )

        ->where(
            'id',
            $eraClaimId
        )

        ->first();


    if (!$denial) {

        return back()->with([
            'message' =>
            'ERA denial claim was not found.',

            'alert-type' =>
            'error',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Claim Number
    |--------------------------------------------------------------------------
    */

    $eraClaimNo = trim(
        (string) (
            $denial->patient_control_number
            ?? ''
        )
    );


    $claimNo =
        $this->normalizeEraClaimNoForLookup(
            $eraClaimNo
        );


    /*
    |--------------------------------------------------------------------------
    | ERA Claim Number Variants
    |--------------------------------------------------------------------------
    */

    $eraClaimVariants = [
        $claimNo,
    ];


    if (
        ctype_digit(
            $claimNo
        )
    ) {

        $unpadded = ltrim(
            $claimNo,
            '0'
        );


        if ($unpadded !== '') {

            $eraClaimVariants[] =
                $unpadded;
        }
    }


    $eraClaimVariants =
        array_values(
            array_unique(
                $eraClaimVariants
            )
        );


    /*
    |--------------------------------------------------------------------------
    | Existing Action
    |--------------------------------------------------------------------------
    */

    $existingAction =
        DB::table(
            'era_denial_exception_actions'
        )

        ->where(
            'denial_era_claim_id',
            $denial->id
        )

        ->first();


    $manualResolved =
        $existingAction
        &&
        !empty(
            $existingAction
                ->resolved_manually_at
        );


    /*
    |--------------------------------------------------------------------------
    | Later Insurance Payment
    |--------------------------------------------------------------------------
    |
    | Match both:
    |
    |     17694
    |     017694
    |--------------------------------------------------------------------------
    */

    $hasLaterPayment =
        DB::table(
            'era_claims'
        )

        ->whereIn(
            'patient_control_number',
            $eraClaimVariants
        )

        ->where(
            'id',
            '>',
            $denial->id
        )

        ->whereRaw(
            'COALESCE(total_paid, 0) > 0.005'
        )

        ->where(function ($query) {

            $query
                ->whereNull(
                    'claim_status_code'
                )

                ->orWhere(
                    'claim_status_code',
                    '<>',
                    '22'
                );
        })

        ->exists();


    /*
    |--------------------------------------------------------------------------
    | Current ADC Claim Balance
    |--------------------------------------------------------------------------
    |
    | All service lines sharing claim_no are included.
    |--------------------------------------------------------------------------
    */

    $currentBalance =
        round(
            (float)
            DB::table(
                'claims'
            )

                ->where(
                    'claim_no',
                    $claimNo
                )

                ->sum(
                    'balance_amount'
                ),
            2
        );


    /*
    |--------------------------------------------------------------------------
    | Automatic Resolution
    |--------------------------------------------------------------------------
    */

    $autoResolved =
        $hasLaterPayment
        &&
        $currentBalance <= 0.005;


    /*
    |--------------------------------------------------------------------------
    | Do Not Archive Open / Partial Claim
    |--------------------------------------------------------------------------
    */

    if (
        !$manualResolved
        &&
        !$autoResolved
    ) {

        return back()->with([
            'message' =>
            "Claim {$claimNo} is still unresolved and cannot be archived.",

            'alert-type' =>
            'warning',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    $userCd =
        Auth::check()

        ? (
            Auth::user()->user_cd
            ?? (string) Auth::id()
        )

        : null;


    /*
    |--------------------------------------------------------------------------
    | Resolution Type
    |--------------------------------------------------------------------------
    */

    $resolutionType =
        $manualResolved

        ? (
            $existingAction
                ->resolution_type
            ?? 'manual'
        )

        : 'auto_paid';


    /*
    |--------------------------------------------------------------------------
    | Archive
    |--------------------------------------------------------------------------
    */

    DB::table(
        'era_denial_exception_actions'
    )

        ->updateOrInsert(

            [
                'denial_era_claim_id' =>
                    $denial->id,
            ],

            [
                'claim_no' =>
                    $claimNo,

                'resolution_type' =>
                    $resolutionType,

                'archived_at' =>
                    now(),

                'archived_by' =>
                    $userCd,

                'updated_at' =>
                    now(),

                'created_at' =>
                    $existingAction->created_at
                    ?? now(),
            ]
        );


    return back()->with([
        'message' =>
            "Claim {$claimNo} archived successfully.",

        'alert-type' =>
            'success',
    ]);

} // End Method


/*
|--------------------------------------------------------------------------
| Restore Archived ERA Exception
|--------------------------------------------------------------------------
|
| Restoring removes only the archive flag.
|
| Manual resolution information, if any, remains intact.
|--------------------------------------------------------------------------
*/

public function denialExceptionRestore(
    $eraClaimId
) {

    /*
    |--------------------------------------------------------------------------
    | Existing Action
    |--------------------------------------------------------------------------
    */

    $action =
        DB::table(
            'era_denial_exception_actions'
        )

        ->where(
            'denial_era_claim_id',
            $eraClaimId
        )

        ->first();


    if (!$action) {

        return back()->with([
            'message' =>
                'Archived ERA exception was not found.',

            'alert-type' =>
                'warning',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Restore
    |--------------------------------------------------------------------------
    */

    DB::table(
        'era_denial_exception_actions'
    )

        ->where(
            'denial_era_claim_id',
            $eraClaimId
        )

        ->update([

            'archived_at' =>
                null,

            'archived_by' =>
                null,

            'updated_at' =>
                now(),
        ]);


    return back()->with([
        'message' =>
            "Claim {$action->claim_no} restored to the exception list.",

        'alert-type' =>
            'success',
    ]);

} // End Method

public function denialExceptionHistory($eraClaimId)
{
    $sourceClaim = DB::table('era_claims')
        ->where('id', $eraClaimId)
        ->first();

    if (!$sourceClaim) {
        return response()->json([
            'success' => false,
            'message' => 'ERA claim was not found.',
        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Claim Number
    |--------------------------------------------------------------------------
    */

    $claimNo = $this->normalizeEraClaimNoForLookup(
        $sourceClaim->patient_control_number
        ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Support Both Historical Formats
    |--------------------------------------------------------------------------
    |
    | 17694
    | 017694
    |--------------------------------------------------------------------------
    */

    $claimVariants = [
        $claimNo,
    ];


    if (
        $claimNo !== ''
        &&
        ctype_digit($claimNo)
    ) {

        $unpadded = ltrim(
            $claimNo,
            '0'
        );


        if ($unpadded !== '') {
            $claimVariants[] = $unpadded;
        }
    }


    $claimVariants = array_values(
        array_unique(
            $claimVariants
        )
    );


    /*
    |--------------------------------------------------------------------------
    | All ERA Adjudications
    |--------------------------------------------------------------------------
    */

    $eraClaims = DB::table('era_claims')
        ->whereIn(
            'patient_control_number',
            $claimVariants
        )
        ->orderBy('id')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | ERA Files
    |--------------------------------------------------------------------------
    */

    $eraFileIds = $eraClaims
        ->pluck('era_file_id')
        ->filter()
        ->unique()
        ->values();


    $eraFiles = DB::table('era_files')
        ->whereIn(
            'id',
            $eraFileIds
        )
        ->get()
        ->keyBy('id');


    /*
    |--------------------------------------------------------------------------
    | Adjustments
    |--------------------------------------------------------------------------
    */

    $eraClaimIds = $eraClaims
        ->pluck('id')
        ->values();


    $adjustments = DB::table('era_adjustments')
        ->whereIn(
            'era_claim_id',
            $eraClaimIds
        )
        ->select(
            'era_claim_id',
            'group_code',
            'reason_code',
            'amount'
        )
        ->orderBy('id')
        ->get()
        ->groupBy('era_claim_id');


    /*
    |--------------------------------------------------------------------------
    | Build History
    |--------------------------------------------------------------------------
    */

    $history = $eraClaims->map(
        function ($eraClaim) use (
            $eraFiles,
            $adjustments
        ) {

            $file = $eraFiles->get(
                $eraClaim->era_file_id
            );


            $reasonRows = $adjustments->get(
                $eraClaim->id,
                collect()
            );


            $reasonText = $reasonRows
                ->map(
                    function ($adj) {

                        $group = trim(
                            (string) (
                                $adj->group_code
                                ?? ''
                            )
                        );

                        $reason = trim(
                            (string) (
                                $adj->reason_code
                                ?? ''
                            )
                        );


                        if (
                            $group === ''
                            &&
                            $reason === ''
                        ) {
                            return null;
                        }


                        $text = trim(
                            $group
                            .
                            (
                                $group !== ''
                                &&
                                $reason !== ''
                                    ? '-'
                                    : ''
                            )
                            .
                            $reason
                        );


                        $amount = round(
                            (float) (
                                $adj->amount
                                ?? 0
                            ),
                            2
                        );


                        if (
                            abs($amount)
                            >=
                            0.005
                        ) {
                            $text .=
                                ' ($'
                                .
                                number_format(
                                    $amount,
                                    2
                                )
                                .
                                ')';
                        }


                        return $text;
                    }
                )
                ->filter()
                ->unique()
                ->implode(', ');


            /*
            |--------------------------------------------------------------------------
            | Friendly Status
            |--------------------------------------------------------------------------
            */

            $statusCode = (string) (
                $eraClaim->claim_status_code
                ?? ''
            );


            $statusText = match($statusCode) {

                '1' =>
                    'Processed as Primary',

                '2' =>
                    'Processed as Secondary',

                '3' =>
                    'Processed as Tertiary',

                '4' =>
                    'Denied',

                '19' =>
                    'Processed as Primary / Forwarded',

                '20' =>
                    'Processed as Secondary / Forwarded',

                '21' =>
                    'Processed as Tertiary / Forwarded',

                '22' =>
                    'Reversal',

                default =>
                    $statusCode !== ''
                        ? 'Status ' . $statusCode
                        : '',
            };


            return [

                'era_claim_id' =>
                    $eraClaim->id,

                'era_file_id' =>
                    $eraClaim->era_file_id,

                'date' =>
                    $file->payment_date
                    ?? null,

                'payer' =>
                    $file->payer_name
                    ?? '',

                'trace_number' =>
                    $file->trace_number
                    ?? '',

                'patient_control_number' =>
                    $eraClaim->patient_control_number,

                'payer_claim_number' =>
                    $eraClaim->payer_claim_number
                    ?? '',

                'status_code' =>
                    $statusCode,

                'status_text' =>
                    $statusText,

                'charge' =>
                    round(
                        (float) (
                            $eraClaim->total_charge
                            ?? 0
                        ),
                        2
                    ),

                'paid' =>
                    round(
                        (float) (
                            $eraClaim->total_paid
                            ?? 0
                        ),
                        2
                    ),

                'patient_responsibility' =>
                    round(
                        (float) (
                            $eraClaim->patient_responsibility
                            ?? 0
                        ),
                        2
                    ),

                'reason' =>
                    $reasonText,
            ];
        }
    );


    return response()->json([
        'success' =>
            true,

        'claim_no' =>
            $claimNo,

        'history' =>
            $history->values(),
    ]);

} // End Method


    public function denialExceptionsView(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Reuse Existing Exception Logic
        |--------------------------------------------------------------------------
        |
        | We call the same internal report method logic by temporarily using
        | denialExceptions() data generation would require refactoring.
        |
        | For now, simplest and safest approach:
        | duplicate the existing denialExceptions() method and change ONLY
        | the final view name.
        |--------------------------------------------------------------------------
        */

        $filter = strtolower(
            trim(
                (string) $request->get(
                    'filter',
                    'active'
                )
            )
        );

        if (
            !in_array(
                $filter,
                [
                    'active',
                    'resolved',
                    'archived',
                    'all',
                ],
                true
            )
        ) {
            $filter = 'active';
        }


        /*
        |--------------------------------------------------------------------------
        | All Denial / Zero-Payment ERA Candidates
        |--------------------------------------------------------------------------
        */

        $denialCandidates = DB::table(
            'era_claims'
        )

            ->whereNotNull(
                'patient_control_number'
            )

            ->where(
                'patient_control_number',
                '<>',
                ''
            )

            ->where(function ($query) {

                $query
                    ->where(
                        'claim_status_code',
                        '4'
                    )

                    ->orWhere(function ($query2) {

                        $query2->whereRaw(
                            'ABS(COALESCE(total_paid, 0)) < 0.005'
                        );
                    });
            })

            ->where(function ($query) {

                $query
                    ->whereNull(
                        'claim_status_code'
                    )

                    ->orWhere(
                        'claim_status_code',
                        '<>',
                        '22'
                    );
            })

            ->orderByDesc(
                'id'
            )

            ->get();


        if ($denialCandidates->isEmpty()) {

            $exceptions = collect();

            $counts = [
                'active'   => 0,
                'open'     => 0,
                'partial'  => 0,
                'resolved' => 0,
                'archived' => 0,
            ];

            return view(
                'era.era_denial_exceptions_view',
                compact(
                    'exceptions',
                    'filter',
                    'counts'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | One Current Exception Per Claim
        |--------------------------------------------------------------------------
        */

        $denials = $denialCandidates

            ->groupBy(
                function ($row) {

                    return $this->normalizeEraClaimNoForLookup(
                        $row->patient_control_number
                        ?? ''
                    );
                }
            )

            ->map(
                function ($rows) {

                    return $rows
                        ->sortByDesc('id')
                        ->first();
                }
            )

            ->filter()

            ->values();


        $claimNos = $denials

            ->pluck(
                'patient_control_number'
            )

            ->map(
                fn($value) =>
                $this->normalizeEraClaimNoForLookup(
                    $value
                )
            )

            ->filter()

            ->unique()

            ->values();


        $eraLookupValues = $claimNos

            ->flatMap(
                function ($claimNo) {

                    $values = [
                        $claimNo,
                    ];

                    if (
                        ctype_digit(
                            (string) $claimNo
                        )
                    ) {

                        $unpadded = ltrim(
                            (string) $claimNo,
                            '0'
                        );

                        if ($unpadded !== '') {
                            $values[] =
                                $unpadded;
                        }
                    }

                    return $values;
                }
            )

            ->filter()

            ->unique()

            ->values();


        $denialEraClaimIds = $denials

            ->pluck('id')

            ->map(
                fn($id) =>
                (int) $id
            )

            ->values();


        /*
        |--------------------------------------------------------------------------
        | ERA History
        |--------------------------------------------------------------------------
        */

        $allEraClaims = DB::table(
            'era_claims'
        )

            ->whereIn(
                'patient_control_number',
                $eraLookupValues
            )

            ->orderBy(
                'id'
            )

            ->get();


        $eraClaimsByClaimNo =
            $allEraClaims->groupBy(
                function ($row) {

                    return $this->normalizeEraClaimNoForLookup(
                        $row->patient_control_number
                        ?? ''
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | ERA Files
        |--------------------------------------------------------------------------
        */

        $eraFileIds = $allEraClaims

            ->pluck(
                'era_file_id'
            )

            ->merge(
                $denials->pluck(
                    'era_file_id'
                )
            )

            ->filter()

            ->unique()

            ->values();


        $eraFiles = DB::table(
            'era_files'
        )

            ->whereIn(
                'id',
                $eraFileIds
            )

            ->get()

            ->keyBy(
                'id'
            );


        /*
        |--------------------------------------------------------------------------
        | ADC Claims
        |--------------------------------------------------------------------------
        */

        $claimRows = DB::table(
            'claims'
        )

            ->whereIn(
                'claim_no',
                $claimNos
            )

            ->select(
                'id',
                'claim_no',
                'ParticipantId',
                'service_from',
                'service_to',
                'total_amount',
                'ins1_paid',
                'balance_amount',
                'status',
                'line_no'
            )

            ->orderBy(
                'claim_no'
            )

            ->orderBy(
                'line_no'
            )

            ->get();


        $claimsByClaimNo =
            $claimRows->groupBy(
                function ($row) {

                    return $this->normalizeEraClaimNoForLookup(
                        $row->claim_no
                        ?? ''
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Members
        |--------------------------------------------------------------------------
        */

        $participantIds = $claimRows

            ->pluck(
                'ParticipantId'
            )

            ->filter()

            ->map(
                fn($id) =>
                trim(
                    (string) $id
                )
            )

            ->unique()

            ->values();


        $members = DB::table(
            'billing_members'
        )

            ->whereIn(
                'ParticipantId',
                $participantIds
            )

            ->select(
                'ParticipantId',
                'FirstName',
                'LastName'
            )

            ->get();


        $membersById =
            $members->keyBy(
                function ($member) {

                    return trim(
                        (string) (
                            $member->ParticipantId
                            ?? ''
                        )
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Adjustments
        |--------------------------------------------------------------------------
        */

        $adjustments = DB::table(
            'era_adjustments'
        )

            ->whereIn(
                'era_claim_id',
                $denialEraClaimIds
            )

            ->select(
                'era_claim_id',
                'level',
                'group_code',
                'reason_code',
                'amount'
            )

            ->orderBy(
                'id'
            )

            ->get();


        $adjustmentsByEraClaim =
            $adjustments->groupBy(
                'era_claim_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Actions
        |--------------------------------------------------------------------------
        |
        | Read only - we only read status/history.
        |--------------------------------------------------------------------------
        */

        $actions = DB::table(
            'era_denial_exception_actions'
        )

            ->whereIn(
                'denial_era_claim_id',
                $denialEraClaimIds
            )

            ->get()

            ->keyBy(
                'denial_era_claim_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Build Rows
        |--------------------------------------------------------------------------
        */

        $exceptions = collect();


        foreach ($denials as $denial) {

            $eraClaimNo = trim(
                (string) (
                    $denial->patient_control_number
                    ?? ''
                )
            );


            $claimNo =
                $this->normalizeEraClaimNoForLookup(
                    $eraClaimNo
                );


            if ($claimNo === '') {
                continue;
            }


            $denialFile =
                $eraFiles->get(
                    $denial->era_file_id
                );


            $relatedClaimRows =
                $claimsByClaimNo->get(
                    $claimNo,
                    collect()
                );


            $firstClaim =
                $relatedClaimRows->first();


            $participantId = trim(
                (string) (
                    $firstClaim->ParticipantId
                    ?? $denial->member_id
                    ?? ''
                )
            );


            $member =
                $membersById->get(
                    $participantId
                );


            $memberName = trim(
                (
                    $member->LastName
                    ?? ''
                )
                .
                (
                    !empty(
                        $member->LastName
                    )
                    &&
                    !empty(
                        $member->FirstName
                    )
                        ? ', '
                        : ''
                )
                .
                (
                    $member->FirstName
                    ?? ''
                )
            );


            $currentBalance =
                round(
                    (float)
                    $relatedClaimRows->sum(
                        'balance_amount'
                    ),
                    2
                );


            $currentPaid =
                round(
                    (float)
                    $relatedClaimRows->sum(
                        'ins1_paid'
                    ),
                    2
                );


            $claimTotal =
                round(
                    (float)
                    $relatedClaimRows->sum(
                        'total_amount'
                    ),
                    2
                );


            if (
                $claimTotal <= 0.005
            ) {

                $claimTotal =
                    round(
                        (float) (
                            $denial->total_charge
                            ?? 0
                        ),
                        2
                    );
            }


            $serviceFrom =
                $relatedClaimRows
                ->pluck(
                    'service_from'
                )
                ->filter()
                ->min();


            $serviceTo =
                $relatedClaimRows
                ->pluck(
                    'service_to'
                )
                ->filter()
                ->max();


            if (!$serviceFrom) {
                $serviceFrom =
                    $denial->service_from
                    ?? null;
            }


            if (!$serviceTo) {
                $serviceTo =
                    $denial->service_to
                    ?? null;
            }


            $claimEraHistory =
                $eraClaimsByClaimNo->get(
                    $claimNo,
                    collect()
                );


            $laterPaidClaims =
                $claimEraHistory

                ->filter(
                    function ($row) use ($denial) {

                        return
                            (int) $row->id
                            >
                            (int) $denial->id

                            &&

                            (string) (
                                $row->claim_status_code
                                ?? ''
                            )
                            !==
                            '22'

                            &&

                            (float) (
                                $row->total_paid
                                ?? 0
                            )
                            >
                            0.005;
                    }
                )

                ->values();


            $latestPaidEra =
                $laterPaidClaims
                ->sortByDesc(
                    'id'
                )
                ->first();


            $laterPaymentFound =
                $latestPaidEra !== null;


            $latestPaidAmount =
                $latestPaidEra
                    ? round(
                        (float) (
                            $latestPaidEra->total_paid
                            ?? 0
                        ),
                        2
                    )
                    : 0.00;


            $latestPaidFile =
                $latestPaidEra
                    ? $eraFiles->get(
                        $latestPaidEra->era_file_id
                    )
                    : null;


            $action =
                $actions->get(
                    $denial->id
                );


            $manualResolved =
                $action
                &&
                !empty(
                    $action->resolved_manually_at
                );


            $archived =
                $action
                &&
                !empty(
                    $action->archived_at
                );


            $autoResolved =
                $laterPaymentFound
                &&
                $currentBalance <= 0.005;


            if ($archived) {

                $reportStatus =
                    'ARCHIVED';

            } elseif ($manualResolved) {

                $reportStatus =
                    'RESOLVED';

            } elseif ($autoResolved) {

                $reportStatus =
                    'RESOLVED';

            } elseif ($laterPaymentFound) {

                $reportStatus =
                    'PARTIAL';

            } else {

                $reportStatus =
                    'OPEN';
            }


            $resolutionSource =
                null;


            if ($manualResolved) {

                $resolutionSource =
                    'Manual';

            } elseif ($autoResolved) {

                $resolutionSource =
                    'Insurance / ERA';

            } elseif ($laterPaymentFound) {

                $resolutionSource =
                    'Partial ERA Payment';
            }


            $denialAdjustments =
                $adjustmentsByEraClaim->get(
                    $denial->id,
                    collect()
                );


            $reasonParts =
                $denialAdjustments

                ->map(
                    function ($adj) {

                        $group = trim(
                            (string) (
                                $adj->group_code
                                ?? ''
                            )
                        );


                        $reason = trim(
                            (string) (
                                $adj->reason_code
                                ?? ''
                            )
                        );


                        if (
                            $group === ''
                            &&
                            $reason === ''
                        ) {
                            return null;
                        }


                        $text = trim(
                            $group
                            .
                            (
                                $group !== ''
                                &&
                                $reason !== ''
                                    ? '-'
                                    : ''
                            )
                            .
                            $reason
                        );


                        $amount =
                            round(
                                (float) (
                                    $adj->amount
                                    ?? 0
                                ),
                                2
                            );


                        if (
                            abs($amount)
                            >=
                            0.005
                        ) {

                            $text .=
                                ' ($'
                                .
                                number_format(
                                    $amount,
                                    2
                                )
                                .
                                ')';
                        }


                        return $text;
                    }
                )

                ->filter()

                ->unique()

                ->values();


            $denialReason =
                $reasonParts->isNotEmpty()

                    ? $reasonParts->implode(
                        ', '
                    )

                    : (
                        (string) (
                            $denial->claim_status_code
                            ?? ''
                        )
                        ===
                        '4'

                        ? 'CLP Status 4 - Denied'

                        : 'Zero Payment'
                    );


            $exceptions->push(
                (object) [

                    'era_claim_no' =>
                        $eraClaimNo,

                    'claim_no' =>
                        $claimNo,

                    'denial_era_claim_id' =>
                        $denial->id,

                    'denial_era_file_id' =>
                        $denial->era_file_id,

                    'ParticipantId' =>
                        $participantId,

                    'member_name' =>
                        $memberName,

                    'service_from' =>
                        $serviceFrom,

                    'service_to' =>
                        $serviceTo,

                    'claim_total' =>
                        $claimTotal,

                    'original_paid' =>
                        round(
                            (float) (
                                $denial->total_paid
                                ?? 0
                            ),
                            2
                        ),

                    'current_paid' =>
                        $currentPaid,

                    'current_balance' =>
                        $currentBalance,

                    'claim_status_code' =>
                        $denial->claim_status_code,

                    'denial_reason' =>
                        $denialReason,

                    'denial_date' =>
                        $denialFile->payment_date
                        ?? null,

                    'denial_payer' =>
                        $denialFile->payer_name
                        ?? null,

                    'denial_trace_number' =>
                        $denialFile->trace_number
                        ?? null,

                    'latest_paid_era_claim_id' =>
                        $latestPaidEra->id
                        ?? null,

                    'latest_paid_era_file_id' =>
                        $latestPaidEra->era_file_id
                        ?? null,

                    'latest_paid_amount' =>
                        $latestPaidAmount,

                    'latest_paid_date' =>
                        $latestPaidFile->payment_date
                        ?? null,

                    'latest_paid_payer' =>
                        $latestPaidFile->payer_name
                        ?? null,

                    'report_status' =>
                        $reportStatus,

                    'resolution_source' =>
                        $resolutionSource,

                    'resolution_type' =>
                        $action->resolution_type
                        ?? null,

                    'resolution_note' =>
                        $action->resolution_note
                        ?? null,

                    'resolved_manually_at' =>
                        $action->resolved_manually_at
                        ?? null,

                    'resolved_manually_by' =>
                        $action->resolved_manually_by
                        ?? null,

                    'archived_at' =>
                        $action->archived_at
                        ?? null,

                    'archived_by' =>
                        $action->archived_by
                        ?? null,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */

        $counts = [

            'active' =>
                $exceptions
                    ->filter(
                        fn($row) =>
                        empty(
                            $row->archived_at
                        )
                    )
                    ->count(),

            'open' =>
                $exceptions
                    ->where(
                        'report_status',
                        'OPEN'
                    )
                    ->count(),

            'partial' =>
                $exceptions
                    ->where(
                        'report_status',
                        'PARTIAL'
                    )
                    ->count(),

            'resolved' =>
                $exceptions
                    ->where(
                        'report_status',
                        'RESOLVED'
                    )
                    ->count(),

            'archived' =>
                $exceptions
                    ->where(
                        'report_status',
                        'ARCHIVED'
                    )
                    ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        if ($filter === 'active') {

            $exceptions =
                $exceptions->filter(
                    fn($row) =>
                    empty(
                        $row->archived_at
                    )
                );

        } elseif (
            $filter === 'resolved'
        ) {

            $exceptions =
                $exceptions->filter(
                    function ($row) {

                        return
                            $row->report_status
                            ===
                            'RESOLVED'

                            &&

                            empty(
                                $row->archived_at
                            );
                    }
                );

        } elseif (
            $filter === 'archived'
        ) {

            $exceptions =
                $exceptions->filter(
                    fn($row) =>
                    !empty(
                        $row->archived_at
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        */

        $exceptions =
            $exceptions

            ->sortBy(
                function ($row) {

                    $priority = [
                        'OPEN'     => 1,
                        'PARTIAL'  => 2,
                        'RESOLVED' => 3,
                        'ARCHIVED' => 4,
                    ];


                    return sprintf(
                        '%02d-%010d',
                        $priority[
                            $row->report_status
                        ]
                            ?? 9,
                        9999999999
                            -
                            (int)
                            $row->denial_era_claim_id
                    );
                }
            )

            ->values();


        /*
        |--------------------------------------------------------------------------
        | Read-Only View
        |--------------------------------------------------------------------------
        */

        return view(
            'era.era_denial_exceptions_view',
            compact(
                'exceptions',
                'filter',
                'counts'
            )
        );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Auto Resolve / Archive Paid Denial Exception
    |--------------------------------------------------------------------------
    |
    | Called after ERA financial posting is complete.
    |
    | If a claim was previously denied / paid $0.00 and a later ERA now pays
    | the claim in full, automatically mark the prior denial exception as
    | resolved and archived.
    |--------------------------------------------------------------------------
    */

    private function autoArchiveResolvedDenialException($eraClaim,$userCd = null): void
    {

        /*
        |--------------------------------------------------------------------------
        | Positive Payment Only
        |--------------------------------------------------------------------------
        */

        $currentPaid = round(
            (float) (
                $eraClaim->total_paid
                ?? 0
            ),
            2
        );


        if ($currentPaid <= 0.005) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Ignore Reversals
        |--------------------------------------------------------------------------
        */

        if (
            (string) (
                $eraClaim->claim_status_code
                ?? ''
            )
            ===
            '22'
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Claim Number
        |--------------------------------------------------------------------------
        */

        $claimNo =
            $this->normalizeEraClaimNoForLookup(
                $eraClaim->patient_control_number
                ?? ''
            );


        if ($claimNo === '') {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ERA Claim Number Variants
        |--------------------------------------------------------------------------
        */

        $claimVariants = [
            $claimNo,
        ];


        if (
            ctype_digit(
                $claimNo
            )
        ) {

            $unpadded =
                ltrim(
                    $claimNo,
                    '0'
                );


            if ($unpadded !== '') {

                $claimVariants[] =
                    $unpadded;
            }
        }


        $claimVariants =
            array_values(
                array_unique(
                    $claimVariants
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Current ADC Claim Balance
        |--------------------------------------------------------------------------
        |
        | One claim may contain multiple service lines.
        |--------------------------------------------------------------------------
        */

        $currentBalance =
            round(
                (float)
                DB::table('claims')
                    ->where(
                        'claim_no',
                        $claimNo
                    )
                    ->sum(
                        'balance_amount'
                    ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Only Fully Paid / Fully Resolved Claims
        |--------------------------------------------------------------------------
        */

        if ($currentBalance > 0.005) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Find Most Recent PRIOR Denial / Zero-Payment ERA
        |--------------------------------------------------------------------------
        */

        $priorDenial =
            DB::table('era_claims')

            ->whereIn(
                'patient_control_number',
                $claimVariants
            )

            ->where(
                'id',
                '<',
                $eraClaim->id
            )

            ->where(function ($query) {

                $query
                    ->where(
                        'claim_status_code',
                        '4'
                    )

                    ->orWhere(function ($query2) {

                        $query2->whereRaw(
                            'ABS(COALESCE(total_paid, 0)) < 0.005'
                        );
                    });
            })

            ->where(function ($query) {

                $query
                    ->whereNull(
                        'claim_status_code'
                    )

                    ->orWhere(
                        'claim_status_code',
                        '<>',
                        '22'
                    );
            })

            ->orderByDesc(
                'id'
            )

            ->first();


        if (!$priorDenial) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Action
        |--------------------------------------------------------------------------
        */

        $existingAction =
            DB::table(
                'era_denial_exception_actions'
            )

            ->where(
                'denial_era_claim_id',
                $priorDenial->id
            )

            ->first();


        /*
        |--------------------------------------------------------------------------
        | Preserve Existing Manual Note
        |--------------------------------------------------------------------------
        */

        $resolutionNote =
            $existingAction->resolution_note
            ??
            'Automatically resolved by subsequent ERA payment.';


        /*
        |--------------------------------------------------------------------------
        | Auto Resolve + Archive
        |--------------------------------------------------------------------------
        */

        DB::table(
            'era_denial_exception_actions'
        )

            ->updateOrInsert(

                [
                    'denial_era_claim_id' =>
                        $priorDenial->id,
                ],

                [
                    'claim_no' =>
                        $claimNo,

                    'resolution_type' =>
                        'auto_paid',

                    'resolution_note' =>
                        $resolutionNote,

                    /*
                    | These are specifically manual-resolution fields,
                    | so leave them unchanged if already present.
                    */
                    'resolved_manually_at' =>
                        $existingAction->resolved_manually_at
                        ?? null,

                    'resolved_manually_by' =>
                        $existingAction->resolved_manually_by
                        ?? null,

                    'archived_at' =>
                        now(),

                    'archived_by' =>
                        $userCd
                        ?? 'SYSTEM',

                    'created_at' =>
                        $existingAction->created_at
                        ?? now(),

                    'updated_at' =>
                        now(),
                ]
            );
    } // End Method

    public function deniedClaimsReport(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Get denied ERA claims
        |--------------------------------------------------------------------------
        | CLP Status Code 4 = Denied
        */

        $claims = DB::table('era_claims')
            ->where('claim_status_code', '4')
            ->where('resolved_flag', 0)
            ->orderByDesc('service_from')
            ->orderByDesc('id')
            ->get();

        if ($claims->isEmpty()) {
            return view('era.era_denied_claims_report', [
                'claims' => collect(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. ERA Files
        |--------------------------------------------------------------------------
        */

        $eraFileIds = $claims
            ->pluck('era_file_id')
            ->filter()
            ->unique()
            ->values();

        $eraFiles = DB::table('era_files')
            ->whereIn('id', $eraFileIds)
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | 3. Billing Members
        |--------------------------------------------------------------------------
        | era_claims.member_id = billing_members.ParticipantId
        |
        | No JOIN is used, so there is no collation comparison between tables.
        */

        $participantIds = $claims
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();

        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $participantIds)
            ->get()
            ->keyBy(function ($member) {
                return trim((string) $member->ParticipantId);
            });

        /*
        |--------------------------------------------------------------------------
        | 4. ERA Adjustments
        |--------------------------------------------------------------------------
        */

        $eraClaimIds = $claims
            ->pluck('id')
            ->unique()
            ->values();

        $adjustments = DB::table('era_adjustments')
            ->whereIn('era_claim_id', $eraClaimIds)
            ->orderBy('era_claim_id')
            ->orderBy('id')
            ->get()
            ->groupBy('era_claim_id');

        /*
        |--------------------------------------------------------------------------
        | 5. CARC Reason Code Lookup
        |--------------------------------------------------------------------------
        */

        $reasonCodes = DB::table('era_reason_codes')
            ->where('active', 1)
            ->get()
            ->keyBy(function ($row) {
                return strtoupper(
                    trim((string) $row->group_code)
                    . '|'
                    . trim((string) $row->reason_code)
                );
            });

        /*
        |--------------------------------------------------------------------------
        | 6. ERA Remarks
        |--------------------------------------------------------------------------
        */

        $remarks = DB::table('era_remarks')
            ->whereIn('era_claim_id', $eraClaimIds)
            ->orderBy('era_claim_id')
            ->orderBy('id')
            ->get()
            ->groupBy('era_claim_id');

        /*
        |--------------------------------------------------------------------------
        | 7. RARC Remark Code Lookup
        |--------------------------------------------------------------------------
        */

        $remarkCodes = DB::table('era_remark_codes')
            ->where('active', 1)
            ->get()
            ->keyBy(function ($row) {
                return strtoupper(trim((string) $row->code));
            });

        /*
        |--------------------------------------------------------------------------
        | 8. Build Final Report Collection
        |--------------------------------------------------------------------------
        */

        $claims = $claims->map(function ($claim) use (
            $eraFiles,
            $members,
            $adjustments,
            $reasonCodes,
            $remarks,
            $remarkCodes
        ) {

            /*
            |--------------------------------------------------------------------------
            | ERA File / Payer
            |--------------------------------------------------------------------------
            */

            $eraFile = $eraFiles->get($claim->era_file_id);

            $claim->payer_name = $eraFile->payer_name ?? '';

            /*
            |--------------------------------------------------------------------------
            | Member Information
            |--------------------------------------------------------------------------
            */

            $participantId = trim((string) $claim->member_id);

            $member = $members->get($participantId);

            $claim->patient_name = '';

            $claim->insured_id = '';

            if ($member) {

                $lastName  = trim((string) ($member->LastName ?? ''));
                $firstName = trim((string) ($member->FirstName ?? ''));

                $claim->patient_name =
                    $lastName
                    . (
                        $lastName !== '' && $firstName !== ''
                            ? ', '
                            : ''
                    )
                    . $firstName;

                $claim->insured_id =
                    trim((string) ($member->medicaid_HMO_id ?? ''));
            }

            /*
            |--------------------------------------------------------------------------
            | Denial Reasons
            |--------------------------------------------------------------------------
            */

            $denials = collect();

            /*
            |--------------------------------------------------------------------------
            | CARC Reasons
            |--------------------------------------------------------------------------
            */

            foreach ($adjustments->get($claim->id, collect()) as $adjustment) {

                $groupCode  = strtoupper(trim((string) $adjustment->group_code));
                $reasonCode = trim((string) $adjustment->reason_code);

                $lookupKey = $groupCode . '|' . $reasonCode;

                $reason = $reasonCodes->get($lookupKey);

                $code = trim($groupCode . '-' . $reasonCode, '-');

                $description =
                    $reason->short_desc
                    ?? $reason->long_desc
                    ?? null;
                if ($description) {
                    $description = preg_split('/(?<=\.)\s+/', trim($description), 2)[0];
                }    

                if (!$description) {
                    $description = $code;
                }

                $denials->push([
                    'type'        => 'CARC',
                    'code'        => $code,
                    'description' => $description,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | RARC Reasons
            |--------------------------------------------------------------------------
            */

            foreach ($remarks->get($claim->id, collect()) as $remark) {

                $code = strtoupper(trim((string) $remark->code));

                $remarkLookup = $remarkCodes->get($code);

                $description =
                    $remarkLookup->short_desc
                    ?? $remarkLookup->long_desc
                    ?? null;

                if ($description) {
                    $description = preg_split('/(?<=\.)\s+/', trim($description), 2)[0];
                }    

                if (!$description) {
                    $description = $code;
                }

                $denials->push([
                    'type'        => 'RARC',
                    'code'        => $code,
                    'description' => $description,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Duplicate Denial Reasons
            |--------------------------------------------------------------------------
            */

            $claim->denials = $denials
                ->unique(function ($item) {
                    return strtoupper(
                        trim(
                            ($item['code'] ?? '')
                            . '|'
                            . ($item['description'] ?? '')
                        )
                    );
                })
                ->values();

            return $claim;
        });

        return view(
            'era.era_denied_claims_report',
            compact('claims')
        );
    } // End Method

    public function denialResolution(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | STEP 1 - Automatically resolve denials older than 200 days
        |--------------------------------------------------------------------------
        |
        | We use service_to as the aging date.
        |
        | Anything older than 200 days is automatically archived/resolved.
        |
        */

        $cutoffDate = now()->subDays(200)->toDateString();

        DB::table('era_claims')
            ->where('claim_status_code', '4')
            ->where('resolved_flag', 0)
            ->whereNotNull('service_to')
            ->whereDate('service_to', '<', $cutoffDate)
            ->update([
                'resolved_flag'   => 1,
                'resolved_at'     => now(),
                'resolved_reason' => 'AGED_200_DAYS',
                'resolved_by'     => null,
            ]);


        /*
        |--------------------------------------------------------------------------
        | STEP 2 - Get open denied ERA claims
        |--------------------------------------------------------------------------
        */

        $claims = DB::table('era_claims')
            ->where('claim_status_code', '4')
            ->where('resolved_flag', 0)
            ->orderByDesc('service_from')
            ->orderByDesc('id')
            ->get();


        if ($claims->isEmpty()) {

            return view('era.era_denial_resolution', [
                'claims' => collect(),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 3 - Get ERA Files
        |--------------------------------------------------------------------------
        */

        $eraFileIds = $claims
            ->pluck('era_file_id')
            ->filter()
            ->unique()
            ->values();

        $eraFiles = DB::table('era_files')
            ->whereIn('id', $eraFileIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | STEP 4 - Get Members
        |--------------------------------------------------------------------------
        */

        $participantIds = $claims
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();

        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $participantIds)
            ->get()
            ->keyBy(function ($member) {
                return trim((string) $member->ParticipantId);
            });


        /*
        |--------------------------------------------------------------------------
        | STEP 5 - Get denial CARC adjustments
        |--------------------------------------------------------------------------
        */

        $eraClaimIds = $claims
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $adjustments = DB::table('era_adjustments')
            ->whereIn('era_claim_id', $eraClaimIds)
            ->orderBy('era_claim_id')
            ->orderBy('id')
            ->get()
            ->groupBy('era_claim_id');


        /*
        |--------------------------------------------------------------------------
        | STEP 6 - CARC Description Lookup
        |--------------------------------------------------------------------------
        */

        $reasonCodes = DB::table('era_reason_codes')
            ->where('active', 1)
            ->get()
            ->keyBy(function ($row) {

                return strtoupper(
                    trim((string) $row->group_code)
                    . '|'
                    . trim((string) $row->reason_code)
                );
            });


        /*
        |--------------------------------------------------------------------------
        | STEP 7 - Get RARC remarks
        |--------------------------------------------------------------------------
        */

        $remarks = DB::table('era_remarks')
            ->whereIn('era_claim_id', $eraClaimIds)
            ->orderBy('era_claim_id')
            ->orderBy('id')
            ->get()
            ->groupBy('era_claim_id');


        /*
        |--------------------------------------------------------------------------
        | STEP 8 - RARC Description Lookup
        |--------------------------------------------------------------------------
        */

        $remarkCodes = DB::table('era_remark_codes')
            ->where('active', 1)
            ->get()
            ->keyBy(function ($row) {
                return strtoupper(trim((string) $row->code));
            });


        /*
        |--------------------------------------------------------------------------
        | STEP 9 - Find later payments for these denied claims
        |--------------------------------------------------------------------------
        |
        | patient_control_number is the claim/account reference coming from
        | the ERA.
        |
        | We look for another ERA claim with:
        |
        |     same patient_control_number
        |     total_paid > 0
        |     newer ERA claim ID
        |
        | No JOIN is used.
        |
        */

        $patientControlNumbers = $claims
            ->pluck('patient_control_number')
            ->filter()
            ->unique()
            ->values();

        $paidClaims = DB::table('era_claims')
            ->whereIn('patient_control_number', $patientControlNumbers)
            ->where('total_paid', '>', 0)
            ->orderByDesc('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Get ERA files for possible payment records
        |--------------------------------------------------------------------------
        */

        $paymentEraFileIds = $paidClaims
            ->pluck('era_file_id')
            ->filter()
            ->unique()
            ->values();

        $paymentEraFiles = DB::table('era_files')
            ->whereIn('id', $paymentEraFileIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | STEP 10 - Build screen data
        |--------------------------------------------------------------------------
        */

        $claims = $claims->map(function ($claim) use (
            $eraFiles,
            $members,
            $adjustments,
            $reasonCodes,
            $remarks,
            $remarkCodes,
            $paidClaims,
            $paymentEraFiles
        ) {

            /*
            |--------------------------------------------------------------------------
            | Original ERA payer
            |--------------------------------------------------------------------------
            */

            $eraFile = $eraFiles->get($claim->era_file_id);

            $claim->payer_name = $eraFile->payer_name ?? '';


            /*
            |--------------------------------------------------------------------------
            | Member
            |--------------------------------------------------------------------------
            */

            $participantId = trim((string) $claim->member_id);

            $member = $members->get($participantId);

            $claim->patient_name = '';
            $claim->insured_id   = '';

            if ($member) {

                $lastName = trim((string) ($member->LastName ?? ''));
                $firstName = trim((string) ($member->FirstName ?? ''));

                $claim->patient_name =
                    $lastName
                    . (
                        $lastName !== '' && $firstName !== ''
                            ? ', '
                            : ''
                    )
                    . $firstName;

                $claim->insured_id =
                    trim((string) ($member->medicaid_HMO_id ?? ''));
            }


            /*
            |--------------------------------------------------------------------------
            | Find later paid ERA claim
            |--------------------------------------------------------------------------
            */

            $claimNumber = trim(
                (string) $claim->patient_control_number
            );

            $payment = $paidClaims
                ->filter(function ($paid) use ($claim, $claimNumber) {

                    if (
                        trim((string) $paid->patient_control_number)
                        !== $claimNumber
                    ) {
                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Must be a later ERA record
                    |--------------------------------------------------------------------------
                    */

                    if ($paid->id <= $claim->id) {
                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | If both have member IDs, make sure they belong to same member
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !empty($claim->member_id)
                        && !empty($paid->member_id)
                        && trim((string) $claim->member_id)
                            !== trim((string) $paid->member_id)
                    ) {
                        return false;
                    }

                    return (float) $paid->total_paid > 0;

                })
                ->sortByDesc('id')
                ->first();


            /*
            |--------------------------------------------------------------------------
            | If later payment exists
            |--------------------------------------------------------------------------
            */

            $claim->payment_found = false;
            $claim->payment_amount = 0;
            $claim->payment_trace_number = null;
            $claim->payment_payer_name = null;
            $claim->payment_era_claim_id = null;
            $claim->payment_era_file_id = null;

            if ($payment) {

                $paymentFile = $paymentEraFiles->get(
                    $payment->era_file_id
                );

                $claim->payment_found = true;

                $claim->payment_amount =
                    (float) ($payment->total_paid ?? 0);

                $claim->payment_trace_number =
                    $paymentFile->trace_number ?? null;

                $claim->payment_payer_name =
                    $paymentFile->payer_name ?? '';

                $claim->payment_era_claim_id =
                    $payment->id;

                $claim->payment_era_file_id =
                    $payment->era_file_id;
            }


            /*
            |--------------------------------------------------------------------------
            | If payment is found, do NOT show old denial message
            |--------------------------------------------------------------------------
            |
            | Claim Admin will see:
            |
            | WELLCARE...
            | CHECK #xxxx PAID $287.88
            |
            */

            if ($claim->payment_found) {

                $claim->denials = collect();

                return $claim;
            }


            /*
            |--------------------------------------------------------------------------
            | Otherwise build CARC/RARC denial messages
            |--------------------------------------------------------------------------
            */

            $denials = collect();


            /*
            |--------------------------------------------------------------------------
            | CARC
            |--------------------------------------------------------------------------
            */

            foreach (
                $adjustments->get($claim->id, collect())
                as $adjustment
            ) {

                $groupCode = strtoupper(
                    trim((string) $adjustment->group_code)
                );

                $reasonCode = trim(
                    (string) $adjustment->reason_code
                );

                $lookupKey = $groupCode . '|' . $reasonCode;

                $reason = $reasonCodes->get($lookupKey);

                $code = trim(
                    $groupCode . '-' . $reasonCode,
                    '-'
                );

                $description =
                    $reason->short_desc
                    ?? $reason->long_desc
                    ?? null;


                /*
                |--------------------------------------------------------------------------
                | Only display first sentence
                |--------------------------------------------------------------------------
                */

                if ($description) {

                    $description = preg_split(
                        '/(?<=\.)\s+/',
                        trim($description),
                        2
                    )[0];
                }


                if (!$description) {
                    $description = $code;
                }


                $denials->push([
                    'type'        => 'CARC',
                    'code'        => $code,
                    'description' => $description,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | RARC
            |--------------------------------------------------------------------------
            */

            foreach (
                $remarks->get($claim->id, collect())
                as $remark
            ) {

                $code = strtoupper(
                    trim((string) $remark->code)
                );

                $remarkLookup = $remarkCodes->get($code);

                $description =
                    $remarkLookup->short_desc
                    ?? $remarkLookup->long_desc
                    ?? null;


                /*
                |--------------------------------------------------------------------------
                | Only display first sentence
                |--------------------------------------------------------------------------
                */

                if ($description) {

                    $description = preg_split(
                        '/(?<=\.)\s+/',
                        trim($description),
                        2
                    )[0];
                }


                if (!$description) {
                    $description = $code;
                }


                $denials->push([
                    'type'        => 'RARC',
                    'code'        => $code,
                    'description' => $description,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Remove duplicate messages
            |--------------------------------------------------------------------------
            */

            $claim->denials = $denials
                ->unique(function ($item) {

                    return strtoupper(
                        trim(
                            ($item['code'] ?? '')
                            . '|'
                            . ($item['description'] ?? '')
                        )
                    );
                })
                ->values();


            return $claim;
        });


        return view(
            'era.era_denial_resolution',
            compact('claims')
        );
    } // End Method

    public function resolveDeniedClaims(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Selected Claims
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'claim_ids'   => ['required', 'array', 'min:1'],
            'claim_ids.*' => ['required', 'integer'],
        ], [
            'claim_ids.required' => 'Please select at least one denial.',
            'claim_ids.min'      => 'Please select at least one denial.',
        ]);


        $claimIds = collect($validated['claim_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Get selected denied claims
        |--------------------------------------------------------------------------
        */

        $deniedClaims = DB::table('era_claims')
            ->whereIn('id', $claimIds)
            ->where('claim_status_code', '4')
            ->where('resolved_flag', 0)
            ->get();


        if ($deniedClaims->isEmpty()) {

            return redirect()
                ->route('era.denial.resolution')
                ->with(
                    'error',
                    'No open denied claims were found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Patient Control Numbers
        |--------------------------------------------------------------------------
        */

        $patientControlNumbers = $deniedClaims
            ->pluck('patient_control_number')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Find later payments
        |--------------------------------------------------------------------------
        */

        $paidClaims = DB::table('era_claims')
            ->whereIn(
                'patient_control_number',
                $patientControlNumbers
            )
            ->where('total_paid', '>', 0)
            ->orderByDesc('id')
            ->get();


        $resolvedCount = 0;


        DB::transaction(function () use (
            $deniedClaims,
            $paidClaims,
            &$resolvedCount
        ) {

            foreach ($deniedClaims as $denied) {

                $claimNumber = trim(
                    (string) $denied->patient_control_number
                );


                /*
                |--------------------------------------------------------------------------
                | Find corresponding later payment
                |--------------------------------------------------------------------------
                */

                $payment = $paidClaims
                    ->filter(function ($paid) use (
                        $denied,
                        $claimNumber
                    ) {

                        if (
                            trim(
                                (string) $paid->patient_control_number
                            ) !== $claimNumber
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Payment must have arrived after denial
                        |--------------------------------------------------------------------------
                        */

                        if ($paid->id <= $denied->id) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Same member where member ID is available
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($denied->member_id)
                            && !empty($paid->member_id)
                            && trim((string) $denied->member_id)
                                !== trim((string) $paid->member_id)
                        ) {
                            return false;
                        }


                        return (float) $paid->total_paid > 0;

                    })
                    ->sortByDesc('id')
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Determine resolution reason
                |--------------------------------------------------------------------------
                */

                $resolvedReason =
                    $payment
                        ? 'PAID'
                        : 'MANUAL';


                /*
                |--------------------------------------------------------------------------
                | Resolve / Archive
                |--------------------------------------------------------------------------
                */

                DB::table('era_claims')
                    ->where('id', $denied->id)
                    ->where('resolved_flag', 0)
                    ->update([

                        'resolved_flag' => 1,

                        'resolved_at' => now(),

                        'resolved_reason' =>
                            $resolvedReason,

                        'resolved_by' =>
                            auth()->id(),

                        'resolution_era_claim_id' =>
                            $payment->id ?? null,

                        'resolution_era_file_id' =>
                            $payment->era_file_id ?? null,
                    ]);


                $resolvedCount++;
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Return to Admin Resolution Screen
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('era.denial.resolution')
            ->with(
                'success',
                $resolvedCount
                . ' denial(s) successfully resolved.'
            );
    } // End Method



}












