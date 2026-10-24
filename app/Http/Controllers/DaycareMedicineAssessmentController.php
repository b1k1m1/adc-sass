<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class DaycareMedicineAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A','N'])) $type = 'A';

        $selectedParticipantId = $request->input('participant_id');

        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $assessments = DB::table('daycare_med_assessments')
            ->when($selectedParticipantId, fn($q) => $q->where('participant_id', $selectedParticipantId))
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('daycareform.medicine_assessment.index', compact(
            'type','members','selectedParticipantId','assessments'
        ));

    } // End Method

     public function create(Request $request)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A','N'])) $type = 'A';

        $selectedParticipantId = $request->input('participant_id');

        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $assessment = (object)[
            'id' => null,
            'participant_id' => $selectedParticipantId,
            'medical_record_no' => null,
            'assessment_date' => now()->toDateString(),
            'nurse_name' => null,
            // NEW fields (must exist to avoid undefined property)
            'use_splints' => null,
            'use_braces' => null,
            'home_glucose' => null,
            'home_glucose_freq' => null,
            'splints_braces' => null,
            'home_glucose_monitoring_freq' => null,
            'physical_therapy' => null,
            'otc_meds' => null,
            'rem_take_independently' => false,
            'rem_caregiver_gives' => false,
            'rem_follows_label' => false,
            'rem_pill_minder' => false,
            'rem_schedule_calendar' => false,
            'rem_other' => null,
            'pharmacy_provider' => null,
            'pharmacy_phone' => null,
            'takes_as_ordered' => null,
            'takes_while_center' => null,
            'notes' => null,
        ];

        // nurse-maintained meds for preview on form (read-only)
        $nurseMeds = collect();
        if ($selectedParticipantId) {
            $nurseMeds = DB::table('daycare_member_meds')
                ->where('participant_id', $selectedParticipantId)
                ->orderBy('line_no')
                ->get();
        }

        return view('daycareform.medicine_assessment.form', compact(
            'type','members','assessment','nurseMeds'
        ));

    } // End Method

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        $id = DB::table('daycare_med_assessments')->insertGetId([
            'participant_id' => $data['participant_id'],
            'medical_record_no' => $data['medical_record_no'] ?? null,
            'assessment_date' => $data['assessment_date'] ?? null,
            'nurse_name' => $data['nurse_name'] ?? null,
            'use_splints'       => $data['use_splints'] ?? null,
            'use_braces'        => $data['use_braces'] ?? null,
            'home_glucose'      => $data['home_glucose'] ?? null,
            'home_glucose_freq' => $data['home_glucose_freq'] ?? null,
            'splints_braces' => $data['splints_braces'] ?? null,
            'home_glucose_monitoring_freq' => $data['home_glucose_monitoring_freq'] ?? null,
            'physical_therapy' => $data['physical_therapy'] ?? null,
            'otc_meds' => $data['otc_meds'] ?? null,

            'rem_take_independently' => !empty($data['rem_take_independently']),
            'rem_caregiver_gives' => !empty($data['rem_caregiver_gives']),
            'rem_follows_label' => !empty($data['rem_follows_label']),
            'rem_pill_minder' => !empty($data['rem_pill_minder']),
            'rem_schedule_calendar' => !empty($data['rem_schedule_calendar']),
            'rem_other' => $data['rem_other'] ?? null,

            'pharmacy_provider' => $data['pharmacy_provider'] ?? null,
            'pharmacy_phone' => $data['pharmacy_phone'] ?? null,

            'takes_as_ordered' => $data['takes_as_ordered'] ?? null,
            'takes_while_center' => $data['takes_while_center'] ?? null,

            'notes' => $data['notes'] ?? null,

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('medicine_assessment.edit', ['id' => $id, 'type' => $request->input('type','A')])
            ->with('message', 'Medicine assessment saved.');

    } // End Method

    public function edit(Request $request, $id)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A','N'])) $type = 'A';

        $assessment = DB::table('daycare_med_assessments')->where('id', $id)->first();
        abort_if(!$assessment, 404);

        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        $nurseMeds = DB::table('daycare_member_meds')
            ->where('participant_id', $assessment->participant_id)
            ->orderBy('line_no')
            ->get();

        return view('daycareform.medicine_assessment.form', compact(
            'type','members','assessment','nurseMeds'
        ));

    } // End Method

    public function update(Request $request, $id)
    {
        $assessment = DB::table('daycare_med_assessments')->where('id', $id)->first();
        abort_if(!$assessment, 404);

        $data = $this->validateRequest($request);

        DB::table('daycare_med_assessments')->where('id', $id)->update([
            'participant_id' => $data['participant_id'],
            'medical_record_no' => $data['medical_record_no'] ?? null,
            'assessment_date' => $data['assessment_date'] ?? null,
            'nurse_name' => $data['nurse_name'] ?? null,
            'use_splints'       => $data['use_splints'] ?? null,
            'use_braces'        => $data['use_braces'] ?? null,
            'home_glucose'      => $data['home_glucose'] ?? null,
            'home_glucose_freq' => $data['home_glucose_freq'] ?? null,
            'splints_braces' => $data['splints_braces'] ?? null,
            'home_glucose_monitoring_freq' => $data['home_glucose_monitoring_freq'] ?? null,
            'physical_therapy' => $data['physical_therapy'] ?? null,
            'otc_meds' => $data['otc_meds'] ?? null,

            'rem_take_independently' => !empty($data['rem_take_independently']),
            'rem_caregiver_gives' => !empty($data['rem_caregiver_gives']),
            'rem_follows_label' => !empty($data['rem_follows_label']),
            'rem_pill_minder' => !empty($data['rem_pill_minder']),
            'rem_schedule_calendar' => !empty($data['rem_schedule_calendar']),
            'rem_other' => $data['rem_other'] ?? null,

            'pharmacy_provider' => $data['pharmacy_provider'] ?? null,
            'pharmacy_phone' => $data['pharmacy_phone'] ?? null,

            'takes_as_ordered' => $data['takes_as_ordered'] ?? null,
            'takes_while_center' => $data['takes_while_center'] ?? null,

            'notes' => $data['notes'] ?? null,

            'updated_at' => now(),
        ]);

        return redirect()
            ->route('medicine_assessment.edit', ['id'=>$id, 'type'=>$request->input('type','A')])
            ->with('message', 'Medicine assessment updated.');

    } // End Method

     public function show(Request $request, $id)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A','N'])) $type = 'A';

        $assessment = DB::table('daycare_med_assessments')->where('id', $id)->first();
        abort_if(!$assessment, 404);

        $member = DB::table('members')->where('ParticipantId', $assessment->participant_id)->first();

        $nurseMeds = DB::table('daycare_member_meds')
            ->where('participant_id', $assessment->participant_id)
            ->orderBy('line_no')
            ->get();

        return view('daycareform.medicine_assessment.show', compact(
            'type','assessment','member','nurseMeds'
        ));

    } // End Method

    public function pdf(Request $request, $id)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A','N'])) $type = 'A';

        $assessment = DB::table('daycare_med_assessments')->where('id', $id)->first();
        abort_if(!$assessment, 404);

        $member = DB::table('members')->where('ParticipantId', $assessment->participant_id)->first();

        $nurseMeds = DB::table('daycare_member_meds')
            ->where('participant_id', $assessment->participant_id)
            ->orderBy('line_no')
            ->get();

        $company = (object)[
            'comp_nm' => config('app.name', 'Daycare'),
        ];

        $html = view('daycareform.medicine_assessment.pdf', compact(
            'type','assessment','member','nurseMeds','company'
        ))->render();

        $mpdf =new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 18,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->SetHTMLFooter('<div style="font-size:10px;text-align:right;">Page {PAGENO} of {nbpg}</div>');
        $mpdf->WriteHTML($html);

        $fileName = 'medicine-assessment-' . $id . '.pdf';
        return response($mpdf->Output($fileName, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);

    } // End Method

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'participant_id' => ['required','string','max:4'],
            'medical_record_no' => ['nullable','string','max:50'],
            'assessment_date' => ['nullable','date'],
            'nurse_name' => ['nullable','string','max:80'],

            'use_splints'       => ['nullable','in:YES,NO'],
            'use_braces'        => ['nullable','in:YES,NO'],
            'home_glucose'      => ['nullable','in:YES,NO'],
            'home_glucose_freq' => ['nullable','string','max:50'],

            'splints_braces' => ['nullable','string','max:50'],
            'home_glucose_monitoring_freq' => ['nullable','string','max:50'],
            'physical_therapy' => ['nullable','string','max:50'],

            'otc_meds' => ['nullable','string','max:200'],

            'rem_take_independently' => ['nullable'],
            'rem_caregiver_gives' => ['nullable'],
            'rem_follows_label' => ['nullable'],
            'rem_pill_minder' => ['nullable'],
            'rem_schedule_calendar' => ['nullable'],
            'rem_other' => ['nullable','string','max:80'],

            'pharmacy_provider' => ['nullable','string','max:80'],
            'pharmacy_phone' => ['nullable','string','max:30'],

            'takes_as_ordered' => ['nullable','in:Yes,No'],
            'takes_while_center' => ['nullable','in:Yes,No'],

            'notes' => ['nullable','string'],
        ]);

    } // End Mehtod

}
