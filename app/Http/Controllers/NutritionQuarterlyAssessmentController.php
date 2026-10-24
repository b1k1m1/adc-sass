<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\NutritionQuarterlyAssessment;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;


class NutritionQuarterlyAssessmentController extends Controller
{
    public function NutritionDashboard()
    {
        return view('daycareform.nutrition_index'); // optional
    } // End Method

    public function index(Request $request)
    {
        // Morning = A, Noon = N (default Morning)
        $type = $request->input('type', 'A');  // 'A' or 'N'
        $selectedParticipantId = $request->input('participant_id');

        // Dropdown members by shift
        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $assessments = collect();

        if ($selectedParticipantId) {
            $assessments = NutritionQuarterlyAssessment::where('participant_id', $selectedParticipantId)
                ->orderByDesc('assessment_date')
                ->orderByDesc('id')
                ->get();
        }

        return view('daycareform.nutrition.index', compact(
            'members',
            'selectedParticipantId',
            'type',
            'assessments'
        ));
    } // End Method

    public function create(Request $request)
    {
        $participantId = $request->query('participant_id')
            ?? $request->query('ParticipantId');

        $type = $request->query('type', 'A');

        if (!$participantId) {
            return redirect()
                ->route('nutrition.index', ['type' => $type])
                ->with('error', 'Please select a member first.');
        }

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nutrition.index', ['type' => $type])
                ->with('error', 'Selected member not found.');
        }

        $assessment = null;
        $mode = 'create';

        return view('daycareform.nutrition.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));
    } // End Method

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        NutritionQuarterlyAssessment::create($data);

        return redirect()
            ->route('nutrition.index', [
                'type'           => $request->input('type', 'A'),
                'participant_id' => $data['participant_id'],
            ])
            ->with('message', 'Nutrition Quarterly Assessment saved.');
    } // End Method

    public function edit(Request $request, $id)
    {
        $assessment = NutritionQuarterlyAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nutrition.index')
                ->with('error', 'Member not found.');
        }

        $mode = 'edit';
        $type = $request->input('type', 'A');

        return view('daycareform.nutrition.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));
    } // End Method

    public function update(Request $request, $id)
    {
        $assessment = NutritionQuarterlyAssessment::findOrFail($id);

        $data = $this->validateData($request);

        $assessment->update($data);

        return redirect()
            ->route('nutrition.index', [
                'type'           => $request->input('type', 'A'),
                'participant_id' => $assessment->participant_id,
            ])
            ->with('message', 'Nutrition Quarterly Assessment updated.');
    } // End Method

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'participant_id'  => 'required|string|max:4',
            'assessment_date' => 'required|date',
            'medical_record_no' => 'nullable|string|max:50',

            'weight_lbs'      => 'nullable|numeric',
            'weight_status'   => 'nullable|in:lost,gain,stable',

            'new_medical_diagnosis' => 'nullable|string',
            'medication_changes'    => 'nullable|string',

            'appetite_hydration'    => 'nullable|in:good,fair,poor',

            'current_diet_order'    => 'nullable|string',
            'labs'                  => 'nullable|string',
            'comments'              => 'nullable|string',

            'dietitian_signature'   => 'nullable|string|max:100',
            'signed_date'           => 'nullable|date',
        ]);

        // Diet order changed (Yes/No stored as boolean)
        $data['diet_order_changed'] = $request->boolean('diet_order_changed');

        // Goals (checkboxes)
        $goalFields = [
            'cp_tolerate_current_diet',
            'cp_bp_bg_within_limits',
            'cp_weight_stable_or_change',
            'cp_understands_diet',
        ];

        foreach ($goalFields as $f) {
            $data[$f] = $request->boolean($f);
        }

        // Recommendations (checkboxes)
        $recFields = [
            'rec_decrease_salty_saturated',
            'rec_encourage_weight_1_2',
            'rec_increase_fiber_protein_calcium_omega3',
            'rec_limit_spicy_greasy_acidic_citrus_caffeine',
            'rec_small_frequent_meals_snack',
            'rec_softer_foods_chewing',
            'rec_cho_counseling_timing_consistency',
            'rec_limit_vitamin_k_foods',
            'rec_increase_physical_activity',
        ];

        foreach ($recFields as $f) {
            $data[$f] = $request->boolean($f);
        }

        return $data;
    } // End Method

    // ✅ keep route order the same; this can be a "screen view" if you still want it
    // But per your standard: show uses pdf blade with renderForScreen flag.
    public function show(Request $request, $id)
    {
        $assessment = NutritionQuarterlyAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nutrition.index')
                ->with('error', 'Member not found.');
        }

        $type = $request->input('type', 'A');
        $renderForScreen = true;

        // show uses pdf blade
        return view('daycareform.nutrition.pdf', compact(
            'assessment',
            'member',
            'type',
            'renderForScreen'
        ));
    } // End Method

    public function pdf($id)
    {
        $assessment = NutritionQuarterlyAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nutrition.index')
                ->with('error', 'Member not found.');
        }

        $lastName  = $member->LastName  ?? $member->lastname  ?? '';
        $firstName = $member->FirstName ?? $member->firstname ?? '';
        $pid       = $member->ParticipantId ?? $member->participant_id ?? '';

        $fileName = 'Nutrition_Quarterly_Assessment_'
            . preg_replace('/\s+/', '_', trim($lastName . '_' . $firstName))
            . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('daycareform.nutrition.pdf', [
            'assessment'      => $assessment,
            'member'          => $member,
            'lastName'        => $lastName,
            'firstName'       => $firstName,
            'pid'             => $pid,
            'renderForScreen' => false,
        ])->render();

        $mpdf = new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
            'format' => 'Letter',
            'margin_top'    => 10,
            'margin_bottom' => 10,
            'margin_left'   => 10,
            'margin_right'  => 10,
        ]);

        $mpdf->WriteHTML($html);

        $pdfContent = $mpdf->Output($fileName, 'S');

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    } // End Method

    public function report(Request $request)
    {
        $type = $request->input('type', 'A');
        $selectedParticipantId = $request->input('participant_id');

        $membersQuery = DB::table('members');

        if ($type === 'A') {
            $membersQuery->where('Trans_flag', 'A');
        } elseif ($type === 'N') {
            $membersQuery->where('Trans_flag', 'N');
        }

        $members = $membersQuery->orderBy('ParticipantId')->get();

        $member = null;
        $assessments = collect();

        if ($selectedParticipantId) {
            $member = DB::table('members')
                ->where('ParticipantId', $selectedParticipantId)
                ->first();

            if ($member) {
                $assessments = NutritionQuarterlyAssessment::where('participant_id', $selectedParticipantId)
                    ->orderByDesc('assessment_date')
                    ->orderByDesc('id')
                    ->get();
            }
        }

        return view('daycareform.nutrition.report', compact(
            'members',
            'selectedParticipantId',
            'member',
            'assessments',
            'type'
        ));
    } // End Method
}

