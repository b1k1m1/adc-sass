<?php

namespace App\Http\Controllers;

use App\Models\SocialServiceQuarterlyAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class SocialServiceQuarterlyAssessmentController extends Controller
{
    public function SSDashboard(){


        return view('daycareform.ss_index');

    }
    public function index(Request $request)
    {
        // Morning = A, Noon = B (default Morning)
        $type = $request->input('type', 'A');   // 'A' or 'B'
        $selectedParticipantId = $request->input('participant_id');

        // Dropdown: all members (you can add where ActiveFlag = 'Y' etc)
        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $assessments = collect();

        if ($selectedParticipantId) {
            $assessments = SocialServiceQuarterlyAssessment::where('participant_id', $selectedParticipantId)
                ->orderByDesc('assessment_date')
                ->get();
            // if later you add a shift column, filter by ->where('shift_flag', $type)
        }

        return view('daycareform.ss_quarterly.index', compact(
            'members',
            'selectedParticipantId',
            'type',
            'assessments'
        ));

    } // End Method

    public function create(Request $request)
    {
        // Accept both participant_id and ParticipantId just in case
        $participantId = $request->query('participant_id')
            ?? $request->query('ParticipantId');

        $type = $request->query('type', 'A');

        if (!$participantId) {
            return redirect()
                ->route('ss-quarterly.index', ['type' => $type])
                ->with('error', 'Please select a member first.');
        }

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return redirect()
                ->route('ss-quarterly.index')
                ->with('error', 'Selected member not found.');
        }

        $assessment = null;
        $mode = 'create';

        return view('daycareform.ss_quarterly.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));

    } // End Method


    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        SocialServiceQuarterlyAssessment::create($data);

        return redirect()
            ->route('ss-quarterly.index', [
                'type'          => $request->input('type', 'A'),
                'participant_id'=> $data['participant_id'],
            ])
            ->with('message', 'Quarterly assessment saved.');

    } // End Method

    public function edit($id)
    {
        $assessment = SocialServiceQuarterlyAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('ss-quarterly.index')
                ->with('error', 'Member not found.');
        }

        $mode = 'edit';
        $type = request()->input('type', 'A');

        return view('daycareform.ss_quarterly.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));

    } // End Method

    public function update(Request $request, $id)
    {
        $assessment = SocialServiceQuarterlyAssessment::findOrFail($id);

        $data = $this->validateData($request);
        $data['updated_by'] = Auth::id();

        $assessment->update($data);

        return redirect()
            ->route('ss-quarterly.index', [
                'type'          => $request->input('type', 'A'),
                'participant_id'=> $assessment->participant_id,
            ])
            ->with('message', 'Quarterly assessment updated.');

    } // End Method

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'participant_id'     => 'required|string|max:10',
            'assessment_date'    => 'required|date',
            'medical_record_no'  => 'nullable|string|max:50',
            'social_worker_name' => 'nullable|string|max:100',
            'care_plan_reviewed'      => 'nullable|boolean',
            'discharge_plan_reviewed' => 'nullable|boolean',

        ]);

        // All checkbox / boolean fields
        $booleanFields = [
            'care_plan_reviewed',
            'discharge_plan_reviewed',

            'cognitive_change',
            'cognitive_alert',
            'cognitive_confused',
            'cognitive_forgetful',
            'cognitive_wanders',
            'cognitive_unaware_surroundings',
            'cognitive_oriented_person',
            'cognitive_oriented_place',
            'cognitive_oriented_time',
            'cognitive_orientation_fluctuates',

            'social_change',
            'social_socializes_appropriately',
            'social_attends_group_activities',
            'social_isolates_self',
            'social_cooperative',
            'social_friendly',
            'social_quiet',

            'communication_change',
            'communication_expresses_needs',
            'communication_does_not_express',
            'communication_requires_encouragement',

            'decision_change',
            'decision_independent',
            'decision_minimal_impairment',
            'decision_moderate_impairment',
            'decision_severe_impairment',

            'mood_change',
            'mood_normal',
            'mood_stable',
            'mood_anxious',
            'mood_lethargic',
            'mood_depressed',
            'mood_manic',
            'mood_agitated',
            'mood_combative',
            'mood_pessimistic',
            'mood_optimistic',
            'mood_confused',

            'mental_health_diagnosis',
            'treatment_psychiatrist',
            'treatment_medication_therapy',
            'treatment_psychotherapy',
            'treatment_none',

            'appearance_change',
            'appearance_neatly_groomed',
            'appearance_presentable',
            'appearance_disheveled',
            'appearance_appropriate_hygiene',
            'appearance_poor_hygiene',

            'services_home_health',
            'services_meals_on_wheels',
            'services_food_stamp_recert',
            'services_medicaid_recert',
            'services_geriatric_case_mgmt',
            'services_ssi_advocacy',
            'services_no_needs_assessed',
        ];

        foreach ($booleanFields as $field) {
        $data[$field] = $request->boolean($field);
    }

        // Enums / text / comments
        $data['cognitive_short_term_memory'] = $request->input('cognitive_short_term_memory');
        $data['cognitive_long_term_memory']  = $request->input('cognitive_long_term_memory');

        $data['mental_health_diagnosis_type'] = $request->input('mental_health_diagnosis_type');

        $data['mood_comment']       = $request->input('mood_comment');
        $data['appearance_comment'] = $request->input('appearance_comment');
        $data['services_comment']   = $request->input('services_comment');

        // IMPORTANT: we do NOT add 'type' here
        return $data;


        // Normalize checkboxes
        //$validated['care_plan_reviewed']      = $request->boolean('care_plan_reviewed');
        //$validated['discharge_plan_reviewed'] = $request->boolean('discharge_plan_reviewed');

        //return $validated;

    } // End Method

    public function show(Request $request, $id)
    {
        $assessment = SocialServiceQuarterlyAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (! $member) {
            return redirect()
                ->route('ss-quarterly.index')
                ->with('error', 'Member not found.');
        }

        // keep Morning/Noon context if it was passed
        $type = $request->input('type', 'A');

        return view('daycareform.ss_quarterly.show', compact(
            'assessment',
            'member',
            'type'
        ));

    } // End Method


    public function pdf($id)
    {
        $assessment = SocialServiceQuarterlyAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (! $member) {
            return redirect()
                ->route('ss-quarterly.index')
                ->with('error', 'Member not found.');
        }

        // Resolve name/ID like we did in the views
        $lastName  = $member->LastName  ?? $member->lastname  ?? '';
        $firstName = $member->FirstName ?? $member->firstname ?? '';
        $pid       = $member->ParticipantId ?? $member->participant_id ?? '';

        $fileName = 'SS_Quarterly_'
            . preg_replace('/\s+/', '_', trim($lastName . '_' . $firstName))
            . '_' . now()->format('Ymd_His') . '.pdf';

        // Render HTML from Blade
        $html = view('daycareform.ss_quarterly.pdf', [
            'assessment' => $assessment,
            'member'     => $member,
            'lastName'   => $lastName,
            'firstName'  => $firstName,
            'pid'        => $pid,
        ])->render();

        // Create mPDF instance (adjust options if needed)
        $mpdf = new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
            'format' => 'Letter',   // or 'A4'
            'margin_top'    => 10,
            'margin_bottom' => 10,
            'margin_left'   => 10,
            'margin_right'  => 10,
        ]);

        $mpdf->WriteHTML($html);

        // Return as download
        $pdfContent = $mpdf->Output($fileName, 'S'); // 'S' = string

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);

    } // End Method

    public function report(Request $request)
    {
        $type = $request->input('type', 'A'); // 'A' = Morning, 'N' = Noon

        $selectedParticipantId = $request->input('participant_id');

        $membersQuery = DB::table('members');

        if ($type === 'A') {
        // Morning
        $membersQuery->where('Trans_flag', 'A');
        } elseif ($type === 'N') {
            // Noon
            $membersQuery->where('Trans_flag', 'N');  // your mapping: Noon = 'B'
        }

        // Members for dropdown
         $members = $membersQuery
            ->orderBy('ParticipantId')
            ->get();

        $member = null;
        $assessments = collect();

        if ($selectedParticipantId) {
            $member = DB::table('members')
                ->where('ParticipantId', $selectedParticipantId)
                ->first();

            if ($member) {
                // 🔹 Get ALL assessments for this member, latest first
                $assessments = SocialServiceQuarterlyAssessment::where('participant_id', $selectedParticipantId)
                    ->orderByDesc('assessment_date')
                    ->orderByDesc('id')
                    ->get();
            }
        }

        return view('daycareform.ss_quarterly.report', compact(
            'members',
            'selectedParticipantId',
            'member',
            'assessments',
            'type'
        ));

    } // End Method

}

