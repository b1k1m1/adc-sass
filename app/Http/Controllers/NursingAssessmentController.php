<?php

namespace App\Http\Controllers;

use App\Models\NursingAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;


class NursingAssessmentController extends Controller
{
     public function index(Request $request)
    {
        // Morning=A, Noon=N
        $type = $request->input('type', 'A');
        $selectedParticipantId = $request->input('participant_id');

        // Members dropdown filtered by shift
        $members = DB::table('members')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $assessments = collect();

        if ($selectedParticipantId) {
            $assessments = NursingAssessment::where('participant_id', $selectedParticipantId)
                ->where('shift_type', $type)
                ->orderByDesc('assessment_date')
                ->orderByDesc('id')
                ->get();
        }

        return view('daycareform.nursing.index', compact(
            'members',
            'selectedParticipantId',
            'type',
            'assessments'
        ));

    } //End Method


    public function create(Request $request)
    {
        $participantId = $request->query('participant_id') ?? $request->query('ParticipantId');
        $type = $request->query('type', 'A');

        if (!$participantId) {
            return redirect()
                ->route('nursing-assessment.index', ['type' => $type])
                ->with('error', 'Please select a member first.');
        }

        $member = DB::table('members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nursing-assessment.index', ['type' => $type])
                ->with('error', 'Selected member not found.');
        }

        $assessment = null;
        $mode = 'create';

        return view('daycareform.nursing.form', compact(
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

        NursingAssessment::create($data);

        return redirect()
            ->route('nursing-assessment.index', [
                'type' => $request->input('type', 'A'),
                'participant_id' => $data['participant_id'],
            ])
            ->with('message', 'Nursing assessment saved.');

    } // Method

    public function edit(Request $request, $id)
    {
        $assessment = NursingAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nursing-assessment.index')
                ->with('error', 'Member not found.');
        }

        $mode = 'edit';
        $type = $request->input('type', $assessment->shift_type ?? 'A');

        return view('daycareform.nursing.form', compact(
            'member',
            'assessment',
            'mode',
            'type'
        ));

    } // Method

    public function update(Request $request, $id)
    {
        $assessment = NursingAssessment::findOrFail($id);

        $data = $this->validateData($request);
        $data['updated_by'] = Auth::id();

        $assessment->update($data);

        return redirect()
            ->route('nursing-assessment.index', [
                'type' => $request->input('type', $assessment->shift_type ?? 'A'),
                'participant_id' => $assessment->participant_id,
            ])
            ->with('message', 'Nursing assessment updated.');

    } // Method

    public function show(Request $request, $id)
    {
        $assessment = NursingAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nursing-assessment.index')
                ->with('error', 'Member not found.');
        }

        $type = $request->input('type', $assessment->shift_type ?? 'A');

        return view('daycareform.nursing.show', compact(
            'assessment',
            'member',
            'type'
        ));

    } // End Method

    public function pdf($id)
    {
        $assessment = NursingAssessment::findOrFail($id);

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->participant_id)
            ->first();

        if (!$member) {
            return redirect()
                ->route('nursing-assessment.index')
                ->with('error', 'Member not found.');
        }

        $lastName  = $member->LastName  ?? $member->lastname  ?? '';
        $firstName = $member->FirstName ?? $member->firstname ?? '';
        $pid       = $member->ParticipantId ?? $assessment->participant_id ?? '';

        $fileName = 'Nursing_Assessment_'
            . preg_replace('/\s+/', '_', trim($lastName . '_' . $firstName))
            . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('daycareform.nursing.pdf', [
            'assessment' => $assessment,
            'member'     => $member,
            'lastName'   => $lastName,
            'firstName'  => $firstName,
            'pid'        => $pid,
            'renderForScreen' => false,
        ])->render();

        $mpdf = new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
            'format' => 'Letter',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        $pdfContent = $mpdf->Output($fileName, 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
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
                $assessments = NursingAssessment::where('participant_id', $selectedParticipantId)
                    ->where('shift_type', $type)
                    ->orderByDesc('assessment_date')
                    ->orderByDesc('id')
                    ->get();
            }
        }

        return view('daycareform.nursing.report', compact(
            'members',
            'selectedParticipantId',
            'member',
            'assessments',
            'type'
        ));

    } // End Method

    /**
     * Validation + checkbox normalization
     */
    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'participant_id' => 'required|string|max:4',
            'medical_record_no' => 'nullable|string|max:50',

            'shift_type' => 'nullable|in:A,N',

            'assessment_type' => 'required|in:Initial,Annual',
            'assessment_date' => 'required|date',
            'doa' => 'nullable|date',

            'height' => 'nullable|string|max:20',
            'weight' => 'nullable|string|max:20',
            'allergies' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string|max:255',

            // Memory / decision / understanding / speech / vision / hearing statuses
            'memory_short_term' => 'nullable|string|max:20',
            'memory_long_term' => 'nullable|string|max:20',
            'decision_making' => 'nullable|string|max:30',
            'understanding_info' => 'nullable|string|max:60',

            'speech_quality' => 'nullable|string|max:20',
            'primary_language_spoken' => 'nullable|string|max:80',

            'right_ear_status' => 'nullable|string|max:20',
            'left_ear_status' => 'nullable|string|max:20',
            'date_last_hearing_eval' => 'nullable|date',

            'vision_ability' => 'nullable|string|max:40',
            'date_last_eye_exam' => 'nullable|date',
        ]);

        // Shift: prefer explicit shift_type, fall back to type, default A
        $data['shift_type'] = $request->input('shift_type')
            ?? $request->input('type')
            ?? 'A';

        // Boolean fields
        $booleanFields = [
            // Cognitive
            'cognitive_alert',
            'cognitive_oriented_person',
            'cognitive_oriented_place',
            'cognitive_oriented_time',
            'cognitive_orientation_fluctuates',
            'cognitive_confused',
            'cognitive_wanders_unaware',

            // Communication
            'expr_speech',
            'expr_writing',
            'expr_signs_gestures_sounds',
            'expr_incoherent_speech',
            'communicates_understood',
            'communicates_difficult_to_understand',
            'cannot_communicate_needs',
            'communication_board',

            // Hearing
            'hearing_minimal_difficulty_not_quiet',
            'hearing_highly_impaired_no_useful',

            // Vision
            'cataracts_right',
            'cataracts_right_sx_yes',
            'cataracts_right_sx_no',
            'cataracts_left',
            'cataracts_left_sx_yes',
            'cataracts_left_sx_no',
            'can_read_newsprint',
            'can_read_large_print',
            'macular_degeneration',
            'glaucoma',
            'blind_right',
            'blind_left',

            // Physical
            'ambulates_alone',
            'ambulates_with_assistance',
            'uses_cane',
            'uses_walker',
            'uses_wheelchair',
            'wheelchair_independently',
            'wheelchair_propel_with_help',
            'wheelchair_unable_to_propel',
            'transfers_independently',
            'transfers_with_assistance_1',
            'transfers_with_assistance_2',
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        return $data;

    } // End Method
}
