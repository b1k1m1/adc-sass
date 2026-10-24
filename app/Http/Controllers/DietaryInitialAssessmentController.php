<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use App\Models\DietaryInitialAssessment;

class DietaryInitialAssessmentController extends Controller
{
    public function index(Request $request)
    {
        // Morning = A, Noon = N (default Morning)
        $type = $request->input('type', 'A');
        $selectedParticipantId = $request->input('participant_id');

        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $assessments = collect();

        if ($selectedParticipantId) {
            $assessments = DietaryInitialAssessment::where('participant_id', $selectedParticipantId)
                ->orderByDesc('assessment_date')
                ->orderByDesc('id')
                ->get();
        }

        return view('daycareform.dietary.index', compact(
            'members',
            'selectedParticipantId',
            'type',
            'assessments'
        ));
    }

    public function create(Request $request)
    {
        $participantId = $request->query('participant_id') ?? $request->query('ParticipantId');
        $type = $request->query('type', 'A');

        if (!$participantId) {
            return redirect()
                ->route('dietary.index', ['type' => $type])
                ->with('error', 'Please select a member first.');
        }

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return redirect()
                ->route('dietary.index', ['type' => $type])
                ->with('error', 'Selected member not found.');
        }

        $assessment = null;
        $mode = 'create';

        return view('daycareform.dietary.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DietaryInitialAssessment::create($data);

        return redirect()
            ->route('dietary.index', [
                'type'           => $request->input('type', 'A'),
                'participant_id' => $data['participant_id'],
            ])
            ->with('message', 'Dietary initial assessment saved.');
    }

    public function edit(Request $request, $id)
    {
        $assessment = DietaryInitialAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('dietary.index')
                ->with('error', 'Member not found.');
        }

        $mode = 'edit';
        $type = $request->input('type', 'A');

        return view('daycareform.dietary.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));
    }

    public function update(Request $request, $id)
    {
        $assessment = DietaryInitialAssessment::findOrFail($id);

        $data = $this->validateData($request);

        $assessment->update($data);

        return redirect()
            ->route('dietary.index', [
                'type'           => $request->input('type', 'A'),
                'participant_id' => $assessment->participant_id,
            ])
            ->with('message', 'Dietary initial assessment updated.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'participant_id'     => 'required|string|max:4',
            'medical_record_no'  => 'nullable|string|max:50',
            'assessment_date'    => 'nullable|date',

            'height'                   => 'nullable|string|max:50',
            'weight'                   => 'nullable|string|max:50',
            'ibw'                      => 'nullable|string|max:50',
            'weight_changes'           => 'nullable|string|max:255',

            'eating_problems_devices'  => 'nullable|string|max:255',
            'appetite'                 => 'nullable|string|max:100',
            'religious_restrictions'   => 'nullable|string|max:255',
            'teeth'                    => 'nullable|string|max:100',
            'dentures'                 => 'nullable|string|max:100',

            'diet_order'               => 'nullable|string|max:255',
            'food_allergy'             => 'nullable|string|max:255',
            'diagnosis'                => 'nullable|string|max:255',
            'medications'              => 'nullable|string|max:255',

            'labs'                     => 'nullable|string',
            'food_preferences_dislikes'=> 'nullable|string',
            'initial_assessment'       => 'nullable|string',

            'dietitian_signature_title'=> 'nullable|string|max:150',
            'signed_date'              => 'nullable|date',
        ]);

        // Booleans (checkboxes)
        $booleanFields = [
            'goal_tolerate_current_diet',
            'goal_bp_bg_within_limits',
            'goal_weight_stable_or_lose_gain',
            'goal_select_healthy_choices',
            'goal_verbalize_knowledge_follow_home',

            'int_educate_decrease_salty_alternates',
            'int_encourage_change_1_2_per_week_month',
            'int_encourage_consume_over_75_each_meal',
            'int_encourage_increase_dietary_fiber',
            'int_educate_avoid_dark_green_vit_k',
            'int_educate_cho_counting_timing_exercise',
            'int_educate_increase_decrease_fluids',
            'int_monitor_alt_food_consistency',
            'int_encourage_increase_protein',
            'int_monitor_monthly_weights',
            'int_encourage_physical_activity',
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        return $data;
    }

    public function show(Request $request, $id)
    {
        $assessment = DietaryInitialAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('dietary.index')
                ->with('error', 'Member not found.');
        }

        $type = $request->input('type', 'A');

        return view('daycareform.dietary.show', compact(
            'assessment',
            'member',
            'type'
        ));
    }

    public function pdf($id)
    {
        $assessment = DietaryInitialAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('dietary.index')
                ->with('error', 'Member not found.');
        }

        //$company = DB::table('company')->first(); // same pattern as Pain (if you use company table)

        $lastName  = $member->LastName  ?? $member->lastname  ?? '';
        $firstName = $member->FirstName ?? $member->firstname ?? '';

        $fileName = 'Dietary_Initial_Assessment_'
            . preg_replace('/\s+/', '_', trim($lastName . '_' . $firstName))
            . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('daycareform.dietary.pdf', [
            'assessment' => $assessment,
            'member'     => $member,

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
    }

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
                $assessments = DietaryInitialAssessment::where('participant_id', $selectedParticipantId)
                    ->orderByDesc('assessment_date')
                    ->orderByDesc('id')
                    ->get();
            }
        }

        return view('daycareform.dietary.report', compact(
            'members',
            'selectedParticipantId',
            'member',
            'assessments',
            'type'
        ));
    }
}
