<?php

namespace App\Http\Controllers;

use App\Models\ParticipantActivityAssesment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;

class ParticipantActivityAssesmentController extends Controller
{
    //


    public function ActivityDashboard(){

        return view('activity.activity_index', ['title' => 'Activity Assessment','module' => 'activity' ]);
    }


    /** All Y/N checkbox columns mapped to request names (same as DB columns) */
    private array $ynFields = [
        // Interaction
        'Interestedinindividualactivity','Socializeswithothermembers','Socializeingroups',
        // Participation
        'MemberisActiveParticipant','MemberisPassiveParticipant','MemberSeldomParticipates',
        // Sitting preference
        'Individual','WithOtherMember','SmallGroup','LargeGroup',
        // Emotional status
        'Happy_Cheerful','Depressed_Sad','Content_Satisfied','Worried_Anxious','Angry_Mad','Serious',
        // Cognitive status
        'Alert_Oriented','Confused_MembersNeedHelp','Forgetful','CannotExpressNeeds',
        // Behavioral status
        'Calm','Anxious','Tearful','Disruptive','Agitated',
        // Recommended program
        'Empowerment','Maintenance',
        // Therapeutic programs
        'MusicalActivities','EducationalTalks','TripsAndGroupOutings','Games','ESLClasses',
        'ExerciseandOtherPhysical','ArtsandCrafts','SpiritualandReligiousActivity',
        // Therapeutic Intervention Needs
        'NeedsDirectAssistanceWith','ProvideStepByStepTaskDemo','StructuredSeatingToOptimize',
    ];

    public function form(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Selected Shift
        |--------------------------------------------------------------------------
        | A = Morning
        | N = Noon
        |--------------------------------------------------------------------------
        */

        $transFlag = strtoupper($request->query('trans_flag', 'A'));

        if (!in_array($transFlag, ['A', 'N'], true)) {
            $transFlag = 'A';
        }

        /*
        |--------------------------------------------------------------------------
        | Filter / Key Values
        |--------------------------------------------------------------------------
        */

        $participantid = $request->query('participantid');
        $assessedyear  = $request->query('assessedyear');
        $quarter       = $request->query('quarter');

        /*
        |--------------------------------------------------------------------------
        | Load Existing Assessment
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Existing primary key is:
        |
        | participantid + assessedyear + quarter
        |
        | Therefore trans_flag is NOT included in the lookup key.
        |--------------------------------------------------------------------------
        */

        $assessment = null;

        if ($participantid && $assessedyear && $quarter) {

            $assessment = DB::table('participant_activity_assesment')
                ->where('participantid', $participantid)
                ->where('assessedyear', (int) $assessedyear)
                ->where('quarter', (string) $quarter)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | If Existing Assessment Has Shift, Use It
            |--------------------------------------------------------------------------
            |
            | Normally this will match the selected member's shift.
            |--------------------------------------------------------------------------
            */

            if (
                $assessment &&
                in_array($assessment->trans_flag, ['A', 'N'], true)
            ) {
                $transFlag = $assessment->trans_flag;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Load Members For Selected Shift
        |--------------------------------------------------------------------------
        */

        $participants = DB::table('members')
            ->select(
                'ParticipantId as id',
                DB::raw("CONCAT(LastName, ', ', FirstName) as name")
            )
            ->where('Trans_flag', $transFlag)
            ->orderBy('ParticipantId')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('activity.activity_form', [

            'assessment'   => $assessment,
            'participants' => $participants,

            'prefill' => [
                'participantid' => $participantid,
                'assessedyear'  => $assessedyear ?? now()->year,
                'quarter'       => $quarter ?? (string) ceil(now()->month / 3),
                'trans_flag'    => $transFlag,
            ],

        ]);

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Save / Upsert
    |--------------------------------------------------------------------------
    |
    | Keep this method if it is being used elsewhere in your application.
    |--------------------------------------------------------------------------
    */

    public function save(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'participantid'  => ['required', 'string', 'max:4'],
            'assessedyear'   => ['required', 'integer', 'digits:4'],
            'quarter'        => ['required', 'in:1,2,3,4'],
            'trans_flag'     => ['required', 'in:A,N'],
            'assessmentdate' => ['required', 'date_format:m/d/Y'],
            'AssessedBy'     => ['nullable', 'string', 'max:30'],
            'PreparedBy'     => ['nullable', 'string', 'max:30'],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Convert Assessment Date
        |--------------------------------------------------------------------------
        */

        $assessmentDate = Carbon::createFromFormat(
            'm/d/Y',
            $validated['assessmentdate']
        )
            ->startOfDay()
            ->toDateTimeString();

        /*
        |--------------------------------------------------------------------------
        | Existing Composite Primary Key
        |--------------------------------------------------------------------------
        */

        $keys = [

            'participantid' => $validated['participantid'],
            'assessedyear' => (int) $validated['assessedyear'],
            'quarter' => (string) $validated['quarter'],

        ];

        /*
        |--------------------------------------------------------------------------
        | Build Update Data
        |--------------------------------------------------------------------------
        */

        $update = [

            'assessmentdate' => $assessmentDate,

            'AssessedBy' => $request->input('AssessedBy'),
            'PreparedBy' => $request->input('PreparedBy'),
            'trans_flag' => $validated['trans_flag'],
            'update_dt' => now()->toDateTimeString(),
            'user_cd' => '',
            'company_id' => session('company_id') ?? null,
            'update_flag' => 'U',

        ];

        /*
        |--------------------------------------------------------------------------
        | Checkboxes
        |--------------------------------------------------------------------------
        */

        foreach ($this->ynFields as $field) {

            $update[$field] = $request->boolean($field)
                ? 'Y'
                : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Insert Or Update
        |--------------------------------------------------------------------------
        */

        DB::table('participant_activity_assesment')
            ->updateOrInsert(
                $keys,
                $update
            );

        /*
        |--------------------------------------------------------------------------
        | Redirect And Preserve Shift
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('paa.form', [

                'participantid' => $validated['participantid'],
                'assessedyear' => $validated['assessedyear'],
                'quarter' => $validated['quarter'],
                'trans_flag' => $validated['trans_flag'],
            ])
            ->with(
                'success',
                'Assessment saved successfully.'
            );

    } // End Method


/*
|--------------------------------------------------------------------------
| Insert Only
|--------------------------------------------------------------------------
*/

    public function insertOnly(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'participantid' => [
                'required',
                'string',
                'max:4'
            ],

            'assessedyear' => [
                'required',
                'integer',
                'digits:4'
            ],

            'quarter' => [
                'required',
                'in:1,2,3,4'
            ],

            'trans_flag' => [
                'required',
                'in:A,N'
            ],

            'assessmentdate' => [
                'required',
                'date_format:m/d/Y'
            ],

            'AssessedBy' => [
                'nullable',
                'string',
                'max:30'
            ],

            'PreparedBy' => [
                'nullable',
                'string',
                'max:30'
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Existing Composite Primary Key
        |--------------------------------------------------------------------------
        */

        $keys = [

            'participantid' => $validated['participantid'],
            'assessedyear' => (int) $validated['assessedyear'],
            'quarter' => (string) $validated['quarter'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate
        |--------------------------------------------------------------------------
        */

        $exists = DB::table('participant_activity_assesment')
            ->where($keys)
            ->exists();

        if ($exists) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    "Record already exists for ID "
                    . "{$keys['participantid']}, "
                    . "Year {$keys['assessedyear']}, "
                    . "Q{$keys['quarter']}. "
                    . "Use Update instead."
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Convert Assessment Date
        |--------------------------------------------------------------------------
        */

        $assessmentDate = Carbon::createFromFormat(
            'm/d/Y',
            $validated['assessmentdate']
        )
            ->startOfDay()
            ->toDateTimeString();

        /*
        |--------------------------------------------------------------------------
        | Build Insert Data
        |--------------------------------------------------------------------------
        */

        $insert = $keys + [

            'assessmentdate' => $assessmentDate,

            'AssessedBy' => $request->input('AssessedBy'),
            'PreparedBy' => $request->input('PreparedBy'),

            /*
            |--------------------------------------------------------------------------
            | Store Selected Shift
            |--------------------------------------------------------------------------
            */

            'trans_flag' => $validated['trans_flag'],
            'update_dt' => now()->toDateTimeString(),
            'user_cd' => '',
            'company_id' => session('company_id') ?? null,
            'update_flag' => 'I',
        ];

        /*
        |--------------------------------------------------------------------------
        | Checkboxes
        |--------------------------------------------------------------------------
        */

        foreach ($this->ynFields as $field) {

            $insert[$field] = $request->boolean($field)
                ? 'Y'
                : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Insert
        |--------------------------------------------------------------------------
        */

        DB::table('participant_activity_assesment')
            ->insert($insert);

        /*
        |--------------------------------------------------------------------------
        | Redirect And Preserve Selected Shift
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('paa.form', [

                'participantid' => $validated['participantid'],
                'assessedyear' => $validated['assessedyear'],
                'quarter' => $validated['quarter'],
                'trans_flag' => $validated['trans_flag'],
            ])
            ->with(
                'success',
                'Assessment created successfully.'
            );

    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Update Only
    |--------------------------------------------------------------------------
    */

    public function updateOnly(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'participantid' => [
                'required',
                'string',
                'max:4'
            ],

            'assessedyear' => [
                'required',
                'integer',
                'digits:4'
            ],

            'quarter' => [
                'required',
                'in:1,2,3,4'
            ],

            'trans_flag' => [
                'required',
                'in:A,N'
            ],

            'assessmentdate' => [
                'required',
                'date_format:m/d/Y'
            ],

            'AssessedBy' => [
                'nullable',
                'string',
                'max:30'
            ],

            'PreparedBy' => [
                'nullable',
                'string',
                'max:30'
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Existing Composite Primary Key
        |--------------------------------------------------------------------------
        */

        $keys = [

            'participantid' => $validated['participantid'],
            'assessedyear' => (int) $validated['assessedyear'],
            'quarter' => (string) $validated['quarter'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Make Sure Assessment Exists
        |--------------------------------------------------------------------------
        */

        $existing = DB::table('participant_activity_assesment')
            ->where($keys)
            ->first();

        if (!$existing) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    "No existing assessment found for ID "
                    . "{$keys['participantid']}, "
                    . "Year {$keys['assessedyear']}, "
                    . "Q{$keys['quarter']}. "
                    . "Load an existing record first."
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Convert Assessment Date
        |--------------------------------------------------------------------------
        */

        $assessmentDate = Carbon::createFromFormat(
            'm/d/Y',
            $validated['assessmentdate']
        )
            ->startOfDay()
            ->toDateTimeString();

        /*
        |--------------------------------------------------------------------------
        | Build Update Data
        |--------------------------------------------------------------------------
        */

        $update = [

            'assessmentdate' => $assessmentDate,
            'AssessedBy' => $request->input('AssessedBy'),
            'PreparedBy' => $request->input('PreparedBy'),
            /*
            |--------------------------------------------------------------------------
            | Store Current Selected Shift
            |--------------------------------------------------------------------------
            */

            'trans_flag' => $validated['trans_flag'],
            'update_dt' => now()->toDateTimeString(),
            'user_cd' => '',
            'company_id' => session('company_id') ?? null,
            'update_flag' => 'U',
        ];

        /*
        |--------------------------------------------------------------------------
        | Checkboxes
        |--------------------------------------------------------------------------
        */

        foreach ($this->ynFields as $field) {

            $update[$field] = $request->boolean($field)
                ? 'Y'
                : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $affected = DB::table('participant_activity_assesment')
            ->where($keys)
            ->update($update);

        /*
        |--------------------------------------------------------------------------
        | Message
        |--------------------------------------------------------------------------
        */

        $msg = $affected > 0
            ? 'Assessment updated successfully.'
            : 'No changes detected (data already up to date).';

        /*
        |--------------------------------------------------------------------------
        | Redirect And Preserve Selected Shift
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('paa.form', [

                'participantid' => $validated['participantid'],
                'assessedyear' => $validated['assessedyear'],
                'quarter' => $validated['quarter'],
                'trans_flag' => $validated['trans_flag'],
            ])
            ->with(
                'success',
                $msg
            );

    } // End Method

    public function pdf(Request $request)
        {
            $request->validate([
                'participantid' => ['required','string','max:4'],
                'assessedyear'  => ['required','integer','digits:4'],
                'quarter'       => ['required','in:1,2,3,4'],
            ]);

            $keys = [
                'participantid' => (string)$request->query('participantid'),
                'assessedyear'  => (int)$request->query('assessedyear'),
                'quarter'       => (string)$request->query('quarter'),
            ];

            $row = DB::table('participant_activity_assesment')->where($keys)->first();
            if (!$row) {
                return redirect()->route('paa.form')->with('error', 'No assessment found for the supplied keys.');
            }

            // Optional: member name for header
            $member = DB::table('members')
                ->select('ParticipantId as id', DB::raw("CONCAT(LastName, ', ', FirstName) as name"), 'DateOfBirth')
                ->where('ParticipantId', $keys['participantid'])
                ->first();

            // Prepare display values
            $assessmentDate = \Illuminate\Support\Carbon::parse($row->assessmentdate)->format('m/d/Y');
            $nowStamp = now()->format('m/d/Y h:i A');

            $company = DB::table('gnmscomp')->first();

            // Company / header values (customize as you like)
            $compname  = $company->comp_nm;
            $compaddr1 = '';  // e.g., session('company_addr1')
            $compaddr2 = '';
            $compphone = '';

            // Render Blade to HTML
            $html = view('activity.activity_pdf', [
                'row'            => $row,
                'member'         => $member,
                'keys'           => $keys,
                'assessmentDate' => $assessmentDate,
                'compname'       => $compname,
                'compaddr1'      => $compaddr1,
                'compaddr2'      => $compaddr2,
                'compphone'      => $compphone,
                'generatedAt'    => $nowStamp,
            ])->render();

            // Optional: centralize CSS so headers/footers don’t repeat CSS text
            $css = <<<CSS
            body { font-family: sans-serif; font-size: 11px; color:#111; }
            .section-title { font-weight:700; color:#7a0c0c; margin:8px 0 2px; }

            /* ✅ explicit table class for mPDF */
            .tbl { width:100%; border-collapse: collapse; margin-top:4px; }
            .tbl td { border:0.6pt solid #000; padding:6px; vertical-align:top; }
            .tbl td.label { width:85%; }
            .tbl td.chk   { width:15%; text-align:center; }

            /* (optional) no outer table border */
            .tbl { border:0; }
            CSS;

            // Build PDF
            $mpdf = new Mpdf([
                'tempDir'=>storage_path('app/mpdf'),
                'format'        => 'A4',
                'margin_top'    => 32,
                'margin_bottom' => 18,
                'margin_left'   => 10,
                'margin_right'  => 10,
            ]);

            // Header & Footer
            $headerHtml = view('activity.activity_pdf_header', [
                'compname' => $compname,
                'compaddr1'=> $compaddr1,
                'compaddr2'=> $compaddr2,
                'compphone'=> $compphone,
                'member'   => $member,
                'keys'     => $keys,
                'assessmentDate' => $assessmentDate,
            ])->render();

            $footerHtml = view('activity.activity_pdf_footer', [
                'generatedAt' => $nowStamp,
            ])->render();

            $mpdf->SetHTMLHeader($headerHtml);
            $mpdf->SetHTMLFooter($footerHtml);

            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

            // Stream inline
            return response($mpdf->Output('', 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="participant_activity_assessment.pdf"');

        } // End Method


    public function reportView(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Selected Shift
        |--------------------------------------------------------------------------
        | A = Morning
        | N = Noon
        |--------------------------------------------------------------------------
        */

        $transFlag = strtoupper($request->query('trans_flag', 'A'));

        if (!in_array($transFlag, ['A', 'N'], true)) {
            $transFlag = 'A';
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Values
        |--------------------------------------------------------------------------
        */

        $participantid = $request->query('participantid');
        $assessedyear  = $request->query('assessedyear');
        $quarter       = $request->query('quarter');

        /*
        |--------------------------------------------------------------------------
        | Load Existing Assessment
        |--------------------------------------------------------------------------
        |
        | Existing Primary Key:
        |
        | participantid + assessedyear + quarter
        |
        | trans_flag is NOT part of the primary key.
        |--------------------------------------------------------------------------
        */

        $record = null;
        $member = null;

        if ($participantid && $assessedyear && $quarter) {

            $record = DB::table('participant_activity_assesment')
                ->where('participantid', $participantid)
                ->where('assessedyear', (int) $assessedyear)
                ->where('quarter', (string) $quarter)
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | If Record Exists, Use Stored Shift
        |--------------------------------------------------------------------------
        |
        | This keeps the report on the shift that was stored with the assessment.
        |--------------------------------------------------------------------------
        */

        if (
            $record &&
            in_array($record->trans_flag, ['A', 'N'], true)
        ) {
            $transFlag = $record->trans_flag;
        }

        /*
        |--------------------------------------------------------------------------
        | Participants Based On Selected Shift
        |--------------------------------------------------------------------------
        */

        $participants = DB::table('members')
            ->select(
                'ParticipantId as id',
                DB::raw("CONCAT(LastName, ', ', FirstName) as name")
            )
            ->where('Trans_flag', $transFlag)
            ->orderBy('ParticipantId')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load Member Information
        |--------------------------------------------------------------------------
        */

        if ($record) {

            $member = DB::table('members')
                ->select(
                    'ParticipantId as id',
                    'LastName',
                    'FirstName',
                    DB::raw("CONCAT(LastName, ', ', FirstName) as fullname")
                )
                ->where('ParticipantId', $participantid)
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('activity.activity_report_view', [

            'participants' => $participants,
            'member' => $member,
            'record' => $record,
            'prefill' => [
                'participantid' => $participantid,
                'assessedyear' => $assessedyear ?? now()->year,
                'quarter' => $quarter ?? (string) ceil(now()->month / 3),
                'trans_flag' => $transFlag,
            ],
        ]);

    } // End Method

    public function index(Request $request)
    {
        $shift = $request->input('shift', 'A');

        return view('activity.initial_activity_index', compact('shift'));

    } // End Method

    public function getMembersByShift(Request $request)
    {
        $shift = $request->input('shift', 'A');

        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'DateOfBirth'
            )
            ->where('Trans_flag', $shift)
            ->orderBy('ParticipantId')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        return response()->json($members);

    } // End Method

    public function listByMember(Request $request)
    {
        $request->validate([
            'ParticipantId' => 'required',
            'shift' => 'required|in:A,N',
        ]);

        $assessments = DB::table('participant_activity_assessments')
            ->where('ParticipantId', $request->ParticipantId)
            ->where('shift', $request->shift)
            ->orderBy('assessment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'assessments' => $assessments,
        ]);

    } // End Method


    public function create(Request $request)
    {
        $shift = $request->input('shift', 'A');
        $participantId = $request->input('ParticipantId');

        $member = null;

        if (!empty($participantId)) {
            $member = DB::table('members')
                ->where('ParticipantId', $participantId)
                ->first();
        }

        $assessment = null;
        $mode = 'create';

        return view('activity.initial_activity_assessment_form', compact(
            'shift',
            'participantId',
            'member',
            'assessment',
            'mode'
        ));

    } // End Method


    public function edit($id)
    {
        $assessment = DB::table('participant_activity_assessments')
            ->where('id', $id)
            ->first();

        if (!$assessment) {
            abort(404, 'Assessment not found.');
        }

        $assessment->activities = $assessment->activities
            ? json_decode($assessment->activities, true)
            : [];

        $assessment->functional_needs = $assessment->functional_needs
            ? json_decode($assessment->functional_needs, true)
            : [];

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->ParticipantId)
            ->first();

        $shift = $assessment->shift;
        $participantId = $assessment->ParticipantId;
        $mode = 'edit';

        return view('activity.initial_activity_assessment_form', compact(
            'shift',
            'participantId',
            'member',
            'assessment',
            'mode'
        ));
    }

    public function view($id)
    {
        $assessment = DB::table('participant_activity_assessments')
            ->where('id', $id)
            ->first();

        if (!$assessment) {
            abort(404, 'Assessment not found.');
        }

        $assessment->activities = $assessment->activities
            ? json_decode($assessment->activities, true)
            : [];

        $assessment->functional_needs = $assessment->functional_needs
            ? json_decode($assessment->functional_needs, true)
            : [];

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->ParticipantId)
            ->first();

        $shift = $assessment->shift;
        $participantId = $assessment->ParticipantId;
        $mode = 'view';

        return view('activity.initial_activity_assessment_form', compact(
            'shift',
            'participantId',
            'member',
            'assessment',
            'mode'
        ));
    }

    public function load(Request $request)
    {
        $request->validate([
            'ParticipantId' => 'required',
            'assessment_date' => 'required|date',
            'shift' => 'required|in:A,N',
        ]);

        $assessmentDate = Carbon::parse($request->assessment_date)->format('Y-m-d');

        $member = DB::table('members')
            ->where('ParticipantId', $request->ParticipantId)
            ->first();

        if (!$member) {
            return response()->json([
                'message' => 'Participant not found.',
            ], 404);
        }

        $assessment = DB::table('participant_activity_assessments')
            ->where('ParticipantId', $request->ParticipantId)
            ->whereDate('assessment_date', $assessmentDate)
            ->where('shift', $request->shift)
            ->first();

        if ($assessment) {
            $assessment->activities = $assessment->activities
                ? json_decode($assessment->activities, true)
                : [];

            $assessment->functional_needs = $assessment->functional_needs
                ? json_decode($assessment->functional_needs, true)
                : [];
        }

        $pdfUrl = null;

        if ($assessment) {
            $pdfUrl = route('participant.activity.assessment.pdf', $assessment->id);
        }


        return response()->json([
            'member' => $member,
            'assessment' => $assessment,
            'pdf_url' => $pdfUrl,

        ]);

    } // End Method

   public function store(Request $request)
    {
        $validated = $request->validate([
            'assessment_id' => 'nullable|integer',

            'ParticipantId' => 'required',
            'assessment_date' => 'required|date',
            'shift' => 'required|in:A,N',

            'assessment_type' => 'nullable|string|max:50',
            'diagnosis' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'responsible_party' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'activity_preferences' => 'nullable|string',

            'activities' => 'nullable|array',
            'functional_needs' => 'nullable|array',

            'admission_date' => 'nullable|date',

            'may_participate_activities' => 'nullable',
            'may_have_alcoholic_beverages' => 'nullable',
            'may_leave_facility_trips' => 'nullable',
            'may_participate_volunteer_work' => 'nullable',
            'doctor_orders_other' => 'nullable|string|max:255',

            'summary' => 'nullable|string',
            'completed_by' => 'nullable|string|max:150',
            'completed_title' => 'nullable|string|max:150',
            'completed_date' => 'nullable|date',
        ]);

        $assessmentDate = Carbon::parse($request->assessment_date)->format('Y-m-d');

        $data = [
            'ParticipantId' => $request->ParticipantId,
            'assessment_date' => $assessmentDate,
            'shift' => $request->shift,

            'assessment_type' => $request->assessment_type,
            'diagnosis' => $request->diagnosis,

            'date_of_birth' => $request->date_of_birth
                ? Carbon::parse($request->date_of_birth)->format('Y-m-d')
                : null,

            'responsible_party' => $request->responsible_party,

            // Store only digits in table: XXXXXXXXXX
            'phone' => preg_replace('/\D/', '', (string) $request->phone),

            'activity_preferences' => $request->activity_preferences,

            'activities' => json_encode($request->input('activities', [])),
            'functional_needs' => json_encode($request->input('functional_needs', [])),

            'admission_date' => $request->admission_date
                ? Carbon::parse($request->admission_date)->format('Y-m-d')
                : null,

            'may_participate_activities' => $request->has('may_participate_activities') ? 1 : 0,
            'may_have_alcoholic_beverages' => $request->has('may_have_alcoholic_beverages') ? 1 : 0,
            'may_leave_facility_trips' => $request->has('may_leave_facility_trips') ? 1 : 0,
            'may_participate_volunteer_work' => $request->has('may_participate_volunteer_work') ? 1 : 0,

            'doctor_orders_other' => $request->doctor_orders_other,
            'summary' => $request->summary,
            'completed_by' => $request->completed_by,
            'completed_title' => $request->completed_title,

            'completed_date' => $request->completed_date
                ? Carbon::parse($request->completed_date)->format('Y-m-d')
                : null,

            'user_cd' => Auth::user()->user_cd ?? null,
            'updated_at' => now(),
        ];

        DB::beginTransaction();

        try {
            $assessmentId = $request->input('assessment_id');

            /*
            |--------------------------------------------------------------------------
            | Edit existing assessment by ID
            |--------------------------------------------------------------------------
            */
            if (!empty($assessmentId)) {
                $existingById = DB::table('participant_activity_assessments')
                    ->where('id', $assessmentId)
                    ->first();

                if (!$existingById) {
                    DB::rollBack();

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Assessment record not found for update.',
                    ], 404);
                }

                DB::table('participant_activity_assessments')
                    ->where('id', $assessmentId)
                    ->update($data);

                $savedId = $assessmentId;
                $message = 'Initial Client Activity Assessment updated successfully.';
            } else {
                /*
                |--------------------------------------------------------------------------
                | Create new assessment or update duplicate same member/date/shift
                |--------------------------------------------------------------------------
                */
                $existing = DB::table('participant_activity_assessments')
                    ->where('ParticipantId', $request->ParticipantId)
                    ->whereDate('assessment_date', $assessmentDate)
                    ->where('shift', $request->shift)
                    ->first();

                if ($existing) {
                    DB::table('participant_activity_assessments')
                        ->where('id', $existing->id)
                        ->update($data);

                    $savedId = $existing->id;
                    $message = 'Initial Client Activity Assessment updated successfully.';
                } else {
                    $data['created_at'] = now();

                    $savedId = DB::table('participant_activity_assessments')
                        ->insertGetId($data);

                    $message = 'Initial Client Activity Assessment saved successfully.';
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'assessment_id' => $savedId,
                'pdf_url' => route('participant.activity.assessment.pdf', $savedId),
                'redirect_url' => route('participant.activity.assessment.edit', $savedId),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to save assessment. ' . $e->getMessage(),
            ], 500);
        }

    } // End Method

    public function exportPdf($id)
    {
        $assessment = DB::table('participant_activity_assessments')
            ->where('id', $id)
            ->first();

        if (!$assessment) {
            abort(404, 'Assessment not found.');
        }

        $assessment->activities = $assessment->activities
            ? json_decode($assessment->activities, true)
            : [];

        $assessment->functional_needs = $assessment->functional_needs
            ? json_decode($assessment->functional_needs, true)
            : [];

        $assessment->assessment_date = $assessment->assessment_date
            ? Carbon::parse($assessment->assessment_date)
            : null;

        $assessment->date_of_birth = $assessment->date_of_birth
            ? Carbon::parse($assessment->date_of_birth)
            : null;

        $assessment->admission_date = $assessment->admission_date
            ? Carbon::parse($assessment->admission_date)
            : null;

        $assessment->completed_date = $assessment->completed_date
            ? Carbon::parse($assessment->completed_date)
            : null;

        $member = DB::table('members')
            ->where('ParticipantId', $assessment->ParticipantId)
            ->first();

        $html = view('activity.initial_activity_pdf', compact(
            'assessment',
            'member'
        ))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'Initial_Client_Activity_Assessment_' .
            $assessment->ParticipantId . '_' .
            optional($assessment->assessment_date)->format('Ymd') . '.pdf';

        return response($mpdf->Output($fileName, 'I'))
            ->header('Content-Type', 'application/pdf');

    } // End Method

}



