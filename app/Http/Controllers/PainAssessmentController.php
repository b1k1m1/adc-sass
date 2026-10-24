<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\PainAssessment;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class PainAssessmentController extends Controller
{


    public function PainDashboard()
    {
        return view('daycareform.pain_index'); // optional

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
            $assessments = PainAssessment::where('participant_id', $selectedParticipantId)
                ->orderByDesc('assessment_date')
                ->orderByDesc('id')
                ->get();
        }

        return view('daycareform.pain.index', compact(
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
                ->route('pain.index', ['type' => $type])
                ->with('error', 'Please select a member first.');
        }

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return redirect()
                ->route('pain.index', ['type' => $type])
                ->with('error', 'Selected member not found.');
        }

        $assessment = null;
        $mode = 'create';

        return view('daycareform.pain.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));

    } // End Method

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        //$data['created_by'] = Auth::id();
        //$data['updated_by'] = Auth::id();

        PainAssessment::create($data);

        return redirect()
            ->route('pain.index', [
                'type'           => $request->input('type', 'A'),
                'participant_id' => $data['participant_id'],
            ])
            ->with('message', 'Pain assessment saved.');

    } // End Method

    public function edit(Request $request, $id)
    {
        $assessment = PainAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('pain.index')
                ->with('error', 'Member not found.');
        }

        $mode = 'edit';
        $type = $request->input('type', 'A');

        return view('daycareform.pain.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));

    } // End Method

    public function update(Request $request, $id)
    {
        $assessment = PainAssessment::findOrFail($id);

        $data = $this->validateData($request);

        //$data['updated_by'] = Auth::id();

        $assessment->update($data);

        return redirect()
            ->route('pain.index', [
                'type'           => $request->input('type', 'A'),
                'participant_id' => $assessment->participant_id,
            ])
            ->with('message', 'Pain assessment updated.');

    } // End Method

    protected function validateData(Request $request): array
    {
        // IMPORTANT: keep participant_id max 4 as you specified
        $data = $request->validate([
            'participant_id'     => 'required|string|max:4',
            'assessment_date'    => 'required|date',
            'medical_record_no'  => 'nullable|string|max:50',

            // Optional: store type (A/N) for consistency in redirects + filtering if you want
            // If you don't want to store it, keep it out like your SS controller.
            // We'll keep it OUT of data, but allow it in request for redirects.
        ]);

        // ===== Reason for assessment (checkboxes) =====
        $booleanFields = [
            'reason_new_admission',
            'reason_readmission',
            'reason_further_assessment',
            'reason_change_in_condition',
            'reason_quarterly',

            // ===== Quality words =====
            'qual_aching','qual_throbbing','qual_shooting','qual_stabbing','qual_penetrating','qual_sharp',
            'qual_burning','qual_tender','qual_exhausting','qual_tiring','qual_unbearable','qual_numb',
            'qual_nagging','qual_hammering','qual_pins_needles','qual_gnawing','qual_tingling','qual_stretching','qual_pulling',

            // ===== Symptoms =====
            'sym_constipation','sym_nausea','sym_vomiting','sym_fatigue','sym_insomnia','sym_depression','sym_drowsy',
            'sym_sore_mouth','sym_weakness','sym_short_breath',

            // ===== Behaviors =====
            'beh_calling_out','beh_restlessness','beh_disorientation','beh_not_eating','beh_pacing','beh_not_sleeping',
            'beh_withdrawn','beh_groaning','beh_rocking','beh_new_immobility','beh_tense','beh_distressed','beh_distracted',
            'beh_crying','beh_inexpressive','beh_fists_clenched','beh_striking_out','beh_knees_pulled_up','beh_frowning',
            'beh_facial_grimacing','beh_sad','beh_frighten','beh_resistant_movement','beh_pulling_pushing_away',

            // ===== Nursing pain diagnosis =====
            'dx_visceral','dx_somatic','dx_raised_intracranial','dx_naturopathic','dx_mixed','dx_unknown',

            // ===== PT evaluation =====
            'pt_evaluation_yes',
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        // ===== Text / numeric fields from the PDF ===== :contentReference[oaicite:1]{index=1}
        $data['pain_location'] = $request->input('pain_location');

        $data['pain_severity_score'] = $request->input('pain_severity_score'); // 0-10
        $data['pain_severity_label'] = $request->input('pain_severity_label'); // mild/distressing etc.

        $data['q_present_level']   = $request->input('q_present_level');
        $data['q_least_rate']      = $request->input('q_least_rate');
        $data['q_better']          = $request->input('q_better');
        $data['q_worst_rate']      = $request->input('q_worst_rate');
        $data['q_worse']           = $request->input('q_worse');
        $data['q_continuous']      = $request->input('q_continuous');
        $data['q_start']           = $request->input('q_start');
        $data['q_cause']           = $request->input('q_cause');
        $data['q_satisfied_level'] = $request->input('q_satisfied_level');

        $data['qual_other'] = $request->input('qual_other');

        // ADL Yes/No + comments
        $data['adl_sleep_yn']     = $request->input('adl_sleep_yn');
        $data['adl_social_yn']    = $request->input('adl_social_yn');
        $data['adl_appetite_yn']  = $request->input('adl_appetite_yn');
        $data['adl_physical_yn']  = $request->input('adl_physical_yn');
        $data['adl_emotions_yn']  = $request->input('adl_emotions_yn');
        $data['adl_sexuality_yn'] = $request->input('adl_sexuality_yn');
        $data['adl_comments']     = $request->input('adl_comments');

        $data['quality_of_life_text'] = $request->input('quality_of_life_text');

        $data['sym_other'] = $request->input('sym_other');
        $data['beh_other'] = $request->input('beh_other');

        $data['past_pain_experienced'] = $request->input('past_pain_experienced');
        $data['past_pain_management']  = $request->input('past_pain_management');

        $data['support_system'] = $request->input('support_system');
        $data['other_concerns'] = $request->input('other_concerns');

        $data['care_plan_updated_date'] = $request->input('care_plan_updated_date');
        $data['plan_of_care']           = $request->input('plan_of_care');
        $data['signature']              = $request->input('signature');

        return $data;

    } // End Method

    public function show(Request $request, $id)
    {
        $assessment = PainAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('pain.index')
                ->with('error', 'Member not found.');
        }

        $type = $request->input('type', 'A');

        return view('daycareform.pain.show', compact(
            'assessment',
            'member',
            'type'
        ));

    } // End Method

    public function pdf($id)
    {
        $assessment = PainAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('pain.index')
                ->with('error', 'Member not found.');
        }

        $lastName  = $member->LastName  ?? $member->lastname  ?? '';
        $firstName = $member->FirstName ?? $member->firstname ?? '';
        $pid       = $member->ParticipantId ?? $member->participant_id ?? '';

        $fileName = 'Pain_Assessment_'
            . preg_replace('/\s+/', '_', trim($lastName . '_' . $firstName))
            . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('daycareform.pain.pdf', [
            'assessment' => $assessment,
            'member'     => $member,
            'lastName'   => $lastName,
            'firstName'  => $firstName,
            'pid'        => $pid,
        ])->render();

        $mpdf = new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
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
        $type = $request->input('type', 'A'); // 'A' Morning, 'N' Noon
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
                $assessments = PainAssessment::where('participant_id', $selectedParticipantId)
                    ->orderByDesc('assessment_date')
                    ->orderByDesc('id')
                    ->get();
            }
        }

        return view('daycareform.pain.report', compact(
            'members',
            'selectedParticipantId',
            'member',
            'assessments',
            'type'
        ));

    } // End Method
}
