<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class DaycarePharmacistReviewController extends Controller
{

    public function index(Request $request)
    {
        // Morning=A, Noon=N
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A', 'N'])) {
            $type = 'A';
        }

        $selectedParticipantId = $request->input('participant_id');

        // Members dropdown (filter by shift/type)
        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        // Reviews list
        $reviews = DB::table('daycare_pharm_reviews')
            ->when($selectedParticipantId, function ($q) use ($selectedParticipantId) {
                $q->where('participant_id', $selectedParticipantId);
            })
            ->orderByDesc('review_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view(
            'daycareform.pharmacist.index',
            compact('type', 'selectedParticipantId', 'members', 'reviews')
        );

    } // End Method


    public function create(Request $request)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A', 'N'])) {
            $type = 'A';
        }

        $selectedParticipantId = $request->input('participant_id');

        $nurseMeds = collect();

        if (!empty($selectedParticipantId)) {
        $nurseMeds = DB::table('daycare_member_meds')
            ->where('participant_id', $selectedParticipantId)
            ->orderBy('line_no')
            ->get();
        }

        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('Trans_flag', $type)   // SAME logic
            ->orderBy('ParticipantId')
            ->get();

        $review = (object)[
            'id' => null,
            'participant_id' => $selectedParticipantId,
            'medical_record_no' => null,
            'review_date' => now()->toDateString(),
            'period_type' => 'Q',
            'period_key' => null,
            'comments' => null,
            'status' => 'draft',
        ];

        $details = [];

        return view(
            'daycareform.pharmacist.form',
            compact('type', 'members', 'review', 'nurseMeds')
        );

    } // End Method

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $periodKey = $this->makePeriodKey($data['review_date'], $data['period_type']);

        DB::table('daycare_pharm_reviews')->insert([
                'participant_id'    => $data['participant_id'],
                'medical_record_no' => $data['medical_record_no'] ?? null,
                'review_date'       => $data['review_date'],
                'period_type'       => $data['period_type'],
                'period_key'        => $periodKey,
                'comments'          => $data['comments'] ?? null,
                'status'            => $data['status'] ?? 'draft',
                //'created_by'        => auth()->user()->name ?? auth()->id(),
                //'updated_by'        => auth()->user()->name ?? auth()->id(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            //$this->saveDetails($reviewId, $request->input('details', []));


        return redirect()
            ->route('pharmacist_review.index', ['type' => $request->input('type', 'A')])
            ->with('message', 'Pharmacist review saved successfully.');

    } // End Method

    public function edit(Request $request, $id)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A', 'N'])) {
            $type = 'A';
        }

        $review = DB::table('daycare_pharm_reviews')->where('id', $id)->first();
        abort_if(!$review, 404);

        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $nurseMeds = DB::table('daycare_member_meds')
            ->where('participant_id', $review->participant_id)
            ->orderBy('line_no')
            ->get();


        return view(
            'daycareform.pharmacist.form',
            compact('type', 'members', 'review', 'nurseMeds')
        );

    } // ENd Method

    public function update(Request $request, $id)
    {
        $review = DB::table('daycare_pharm_reviews')->where('id', $id)->first();
        abort_if(!$review, 404);

        $data = $this->validateRequest($request);
        $periodKey = $this->makePeriodKey($data['review_date'], $data['period_type']);

        DB::transaction(function () use ($request, $id, $data, $periodKey) {

            DB::table('daycare_pharm_reviews')->where('id', $id)->update([
                'participant_id'    => $data['participant_id'],
                'medical_record_no' => $data['medical_record_no'] ?? null,
                'review_date'       => $data['review_date'],
                'period_type'       => $data['period_type'],
                'period_key'        => $periodKey,
                'comments'          => $data['comments'] ?? null,
                'status'            => $data['status'] ?? 'draft',
                //'updated_by'        => auth()->user()->name ?? auth()->id(),
                'updated_at'        => now(),
            ]);

        });

        return redirect()
            ->route('pharmacist_review.edit', ['id' => $id, 'type' => $request->input('type', 'A')])
            ->with('message', 'Pharmacist review updated successfully.');

    } // End Method


    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'participant_id'    => ['required'],
            'medical_record_no' => ['nullable', 'string', 'max:50'],
            'review_date'       => ['required', 'date'],
            'period_type'       => ['required', 'in:M,Q'],
            'comments'          => ['nullable', 'string'],
            'status'            => ['nullable', 'in:draft,completed'],
        ]);

    } // End Method

    private function makePeriodKey(string $reviewDate, string $periodType): string
    {
        $ts = strtotime($reviewDate);

        if ($periodType === 'M') {
            return date('Y-m', $ts); // 2025-12
        }

        $q = (int)ceil(date('n', $ts) / 3);
        return date('Y', $ts) . '-Q' . $q; // 2025-Q4

    } // End Method

   private function saveDetails(int $reviewId, array $rows): void
    {
        // Cache master lookups once per request (fast)
        static $dosageMap = null;
        static $freqMap   = null;
        static $physMap   = null;

        if ($dosageMap === null) {
            $dosageMap = DB::table('dosagemaster')
                ->pluck('dosage_nm', 'dosage_id')
                ->toArray();
        }

        if ($freqMap === null) {
            $freqMap = DB::table('dosagefreqmaster')
                ->pluck('dosagefreq_nm', 'dosagefreq_id')
                ->toArray();
        }

        if ($physMap === null) {
            $physMap = DB::table('physicianmaster')
                ->select('doctor_id', 'lastname', 'firstname')
                ->get()
                ->mapWithKeys(fn($p) => [
                    $p->doctor_id => trim(($p->lastname ?? '') . ', ' . ($p->firstname ?? ''))
                ])
                ->toArray();
        }

        $line = 1;

        foreach ($rows as $r) {
            $medicine = trim($r['medicine_nm'] ?? '');
            $doctorId = trim($r['doctor_id'] ?? '');

            // New: comments column
            $rowComments = trim($r['comments'] ?? '');

            // Dropdown IDs + manual text
            $dosageId    = trim($r['dosage_id'] ?? '');
            $dosageOther = trim($r['dosage_other'] ?? '');

            $freqId      = trim($r['dosagefreq_id'] ?? '');
            $freqOther   = trim($r['freq_other'] ?? '');

           $dosageText = $dosageOther !== ''
                ? $dosageOther
                : ($dosageId !== ''
                    ? ($dosageMap[$dosageId] ?? '')
                    : trim($r['dosage_nm'] ?? '')
                );

            $freqText = $freqOther !== ''
                ? $freqOther
                : ($freqId !== ''
                    ? ($freqMap[$freqId] ?? '')
                    : trim($r['dosage_freq'] ?? '')
                );

            // Snapshot physician name (no joins in reports)
            $physName = $doctorId !== '' ? ($physMap[$doctorId] ?? null) : null;

            // Skip fully blank rows
            if ($medicine === '' && $doctorId === '' && $dosageText === '' && $freqText === '' && $rowComments === '') {
                continue;
            }

            DB::table('daycare_pharm_review_meds')->insert([
                'review_id'       => $reviewId,
                'line_no'         => $line++,
                'medicine_nm'     => $medicine ?: null,
                'doctor_id'       => $doctorId ?: null,          // varchar(4)
                'physician_name'  => $physName,                  // snapshot
                'dosage_nm'       => $dosageText ?: null,        // final text
                'dosage_freq'     => $freqText ?: null,          // final text
                'comments'        => $rowComments ?: null,       // NEW column varchar(50)
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

    } // End Method

    public function show(Request $request, $id)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A', 'N'])) $type = 'A';

        $review = DB::table('daycare_pharm_reviews')->where('id', $id)->first();
        abort_if(!$review, 404);

        $member = DB::table('members')->where('ParticipantId', $review->participant_id)->first();

        // ✅ Nurse-maintained medication list (NOT pharmacist table)
        $nurseMeds = DB::table('daycare_member_meds')
            ->where('participant_id', $review->participant_id)
            ->orderBy('line_no')
            ->get();

        return view('daycareform.pharmacist.show', compact('type', 'review', 'member', 'nurseMeds'));

    } // End Method


    public function pdf(Request $request, $id)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A', 'N'])) $type = 'A';

        $review = DB::table('daycare_pharm_reviews')->where('id', $id)->first();
        abort_if(!$review, 404);

        $member = DB::table('members')->where('ParticipantId', $review->participant_id)->first();

        // Company info (use your existing pattern/table if you already have one)
        $company = [
            'name' => config('app.name', 'Daycare'),
            'address' => '',
            'phone' => '',
            'fax' => '',
        ];

        $html = view('daycareform.pharmacist.pdf', compact(
            'type', 'review', 'member', 'company'
        ))->render();

        $mpdf = new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 28,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans',
        ]);

        // Header / Footer (optional)
        //$mpdf->SetHTMLHeader(view('daycareform.pharmacist_review.pdf_header', compact('company', 'review', 'member'))->render());
        $mpdf->SetHTMLFooter('<div style="font-size:10px;text-align:right;">Page {PAGENO} of {nbpg}</div>');

        $mpdf->WriteHTML($html);

        $fileName = 'pharmacist-review-' . $id . '.pdf';
        return response($mpdf->Output($fileName, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);

    } // End Method

    public function memberSnapshot($participantId)
    {
        $m = DB::table('members')->where('ParticipantId', $participantId)->first();
        if (!$m) return response()->json(['ok' => false], 404);

        // Adjust field names below to whatever exists in your members table
        return response()->json([
            'ok' => true,
            'member' => [
                'ParticipantId' => $m->ParticipantId ?? null,
                'LastName'      => $m->LastName ?? null,
                'FirstName'     => $m->FirstName ?? null,
                'DateOfBirth'   => $m->DateOfBirth ?? null,
                'Admission_date'=> $m->AdmissionDate ?? null,
                // optional vitals if stored on members:
                'blood_sugar'   => $m->bloodsugar ?? null,
                'blood_pressure'=> $m->bloodpressure ?? null,
                'weight'        => $m->weight ?? null,
            ]
        ]);
    }


    public function lastReview($participantId)
    {
        $review = DB::table('daycare_pharm_reviews')
            ->where('participant_id', $participantId)
            ->orderByDesc('review_date')
            ->orderByDesc('id')
            ->first();

        if (!$review) {
            return response()->json(['ok' => false]);
        }


        return response()->json([
            'ok' => true,
            'review' => [
                'id'         => $review->id,
                'review_date'=> $review->review_date,
                'period_key' => $review->period_key,
                'comments'   => $review->comments,
                'updated_at' => $review->updated_at,
            ],

        ]);

    } // End method

    public function nurseMedsJson($participantId)
    {
        $meds = DB::table('daycare_member_meds')
            ->where('participant_id', $participantId)
            ->orderBy('line_no')
            ->get();

        return response()->json(['ok' => true, 'meds' => $meds]);

    } // End Method


}




