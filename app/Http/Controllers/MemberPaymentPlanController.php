<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Yajra\DataTables\Facades\DataTables;

class MemberPaymentPlanController extends Controller
{
     public function index()
    {
        return view('members.members_paymentplan');

    } // End Method

    public function data(Request $request)
    {
        $flag = $request->get('trans_flag', 'A'); // default Morning

        $q = DB::table('members')
            ->select([
                'ParticipantId',
                'LastName',
                'FirstName',
                'City',
                'payment_plan',
                'pp_comments',
                'Trans_flag',
            ])
            ->where('Trans_flag', $flag);

        return DataTables::of($q)
            ->addColumn('full_name', function ($row) {
                return trim(($row->LastName ?? '') . ', ' . ($row->FirstName ?? ''));
            })
            ->addColumn('photo', function ($row) {
                // "001" -> 1 -> "1.jpg"
                $num = (int) ltrim((string)$row->ParticipantId, '0');
                $num = $num > 0 ? $num : 0;

                $rel = "members/images/{$num}.jpg";
                $url = asset($rel);
                $fallback = asset('members/images/no-photo.jpg'); // create this placeholder

                // small image with fallback if missing
                return '
                    <img src="'.$url.'"
                        onerror="this.onerror=null;this.src=\''.$fallback.'\';"
                        class="member-photo">
                ';
            })
            ->rawColumns(['photo'])
            ->make(true);

    } // End Method

     public function bulkUpdate(Request $request)
    {
        $flag = $request->input('trans_flag', 'A');

        $rows = $request->input('rows', []);
        if (!is_array($rows) || count($rows) === 0) {
            return response()->json(['ok' => false, 'message' => 'No rows to update.'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                $pid = $r['ParticipantId'] ?? null;
                if (!$pid) continue;

                // keep it simple; add validation rules if you want strict lists
                $paymentPlan = $r['payment_plan'] ?? null;
                $comments    = $r['pp_comments'] ?? null;

                DB::table('members')
                    ->where('ParticipantId', $pid)
                    ->where('Trans_flag', $flag)  // extra safety
                    ->update([
                        'payment_plan' => $paymentPlan,
                        'pp_comments'  => $comments,
                        'update_dt'    => now(),     // if you have it
                        // 'user_cd'   => auth()->user()->user_cd ?? null,
                        // 'update_flag'=> 'Y',
                    ]);
            }

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Updated successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }

    } // End Method

    public function report(Request $request)
        {
        $month  = $request->get('month', now()->format('Y-m'));
        $sortBy = $request->get('sort_by', 'name'); // id | name

        $q = DB::table('members')
            ->select([
                'ParticipantId',
                'LastName',
                'FirstName',
                'City',
                'payment_plan',
                'pp_comments',
            ])
            ->where('payment_plan', '>', 0);

        if ($sortBy === 'id') {
            $q->orderBy('ParticipantId');
        } else {
            $q->orderBy('LastName')->orderBy('FirstName');
        }

        $members = $q->get();
        $total   = $members->sum('payment_plan');

        return view('members.members_paymentplan_report', compact('members', 'total', 'month', 'sortBy'));

    } // End method


    public function pdf(Request $request)
    {
        //Log::info('PDF called', $request->all());

        $month  = $request->get('month', now()->format('Y-m'));
        $sortBy = $request->get('sort_by', 'name');
        $action = $request->get('action', 'preview'); // preview | download

        // same query as index()
        $q = DB::table('members')
            ->select([
                'ParticipantId',
                'LastName',
                'FirstName',
                'City',
                'payment_plan',
                'pp_comments',
            ])
            ->where('payment_plan', '>', 0);

        if ($sortBy === 'id') {
            $q->orderBy('ParticipantId');
        } else {
            $q->orderBy('LastName')->orderBy('FirstName');
        }

        $members = $q->get();
        $total   = $members->sum('payment_plan');

        // PDF view (separate, print-friendly)
        $html = view('members.members_paymentplan_report_pdf', compact('members', 'total', 'month', 'sortBy'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => storage_path('app/mpdf'), // you already use this approach
        ]);

        $mpdf->WriteHTML($html);

        $fileName = "PaymentPlanReport_{$month}_{$sortBy}.pdf";

        // Clean output buffer (prevents corrupted PDF)
        if (ob_get_length()) { ob_end_clean(); }

        if ($action === 'download') {
            $mpdf->Output($fileName, 'D');  // Download
            exit;
        }

        // Preview in browser
        $mpdf->Output($fileName, 'I');      // Inline preview
        exit;

    } // End Method

}

