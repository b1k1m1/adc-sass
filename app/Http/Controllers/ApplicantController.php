<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;

class ApplicantController extends Controller
{
    public function index()
    {
        $applicants = Applicant::orderByDesc('form_no')->paginate(25);

        return view('applicants.index', compact('applicants'));

    } // End Method

    public function create()
    {
        $nextFormNo = ((int) Applicant::max('form_no')) + 1;

        $applicant = new Applicant();
        $applicant->form_no = $nextFormNo;

        return view('applicants.form', $this->formData($applicant, false));

    } // End Method

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $applicant = Applicant::create($data);

        return redirect()
            ->route('applicants.edit', $applicant->id)
            ->with('success', 'Initial Assessment saved successfully.');

    } // End Method

    public function edit(Applicant $applicant)
    {
        return view('applicants.form', $this->formData($applicant, true));

    } // End Method

    public function update(Request $request, Applicant $applicant)
    {
        $data = $this->validatedData($request, $applicant->id);

        $data['updated_by'] = Auth::id();

        $applicant->update($data);

        return redirect()
            ->route('applicants.edit', $applicant->id)
            ->with('success', 'Initial Assessment updated successfully.');

    } // End Method

    public function pdf(Applicant $applicant)
    {
        $html = view('applicants.pdf', [
            'applicant' => $applicant,
            'options' => $this->options(),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->SetTitle('Initial Assessment - Form #' . $applicant->form_no);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="initial-assessment-' . $applicant->form_no . '.pdf"'
            );

    } // End Method

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:applicants,form_no';

        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        $request->validate([
            'form_no' => ['required', 'integer', 'min:1', $uniqueRule],
            'participant_name' => ['nullable', 'string', 'max:150'],
            'birthday' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'string', 'max:20'],
            'religion' => ['nullable', 'string', 'max:100'],
            'no_of_children' => ['nullable', 'string', 'max:20'],
            'no_of_grandchildren' => ['nullable', 'string', 'max:20'],
            'past_occupation' => ['nullable', 'string', 'max:150'],
            'no_of_years' => ['nullable', 'string', 'max:50'],
            'current_living_conditions' => ['nullable', 'string', 'max:255'],
            'hha_services' => ['nullable', 'string', 'max:255'],
            'signature_date' => ['nullable', 'string', 'max:20'],
        ]);

        return [
            'form_no' => $request->form_no,
            'participant_name' => $request->participant_name,
            'birthday' => $this->dateToDb($request->birthday),
            'age' => $request->age,
            'religion' => $request->religion,
            'no_of_children' => $request->no_of_children,
            'no_of_grandchildren' => $request->no_of_grandchildren,
            'past_occupation' => $request->past_occupation,
            'no_of_years' => $request->no_of_years,
            'current_living_conditions' => $request->current_living_conditions,
            'hha_services' => $request->hha_services,

            'physical_limitations' => $request->physical_limitations,
            'effect_on_participation' => $request->effect_on_participation,

            'mobility' => $request->input('mobility', []),
            'motor' => $request->input('motor', []),
            'sensory' => $request->input('sensory', []),
            'vision_problem_specify' => $request->vision_problem_specify,
            'hearing_problem_specify' => $request->hearing_problem_specify,

            'attention_span' => $request->attention_span,
            'cognitive_status' => $request->input('cognitive_status', []),
            'cognitive_other' => $request->cognitive_other,
            'how_often_attends_program' => $request->how_often_attends_program,

            'emotional_reactions' => $request->input('emotional_reactions', []),
            'emotional_other' => $request->emotional_other,

            'task_technical_skills' => $request->input('task_technical_skills', []),
            'task_other' => $request->task_other,

            'interpersonal_group_skills' => $request->input('interpersonal_group_skills', []),
            'interpersonal_other' => $request->interpersonal_other,
            'interpersonal_comments' => $request->interpersonal_comments,
            'level_of_interaction' => $request->level_of_interaction,

            'interests_activities' => $request->input('interests_activities', []),
            'interests_other' => $request->interests_other,

            'objectives' => $request->input('objectives', []),
            'benefit_from_activities' => $request->benefit_from_activities,
            'activities_restrictions' => $request->activities_restrictions,
            'comments_plan' => $request->comments_plan,

            'staff_signature' => $request->staff_signature,
            'signature_date' => $this->dateToDb($request->signature_date),
        ];

    } // End Method

    private function dateToDb(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::createFromFormat('m/d/Y', $date)->format('Y-m-d');
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

    } // End Method

    private function formData(Applicant $applicant, bool $isEdit): array
    {
        return [
            'applicant' => $applicant,
            'isEdit' => $isEdit,
            'options' => $this->options(),
        ];

    } // End Method

    private function options(): array
    {
        return [
            'mobility' => [
                'Ambulatory',
                'Uses cane',
                'Uses walker',
                'Needs assistance',
                'Uses wheelchair',
                'Independent propulsion',
                'Dependent on transport',
            ],

            'motor' => [
                'Good coordination',
                'Poor gross motor coordination',
                'Poor fine motor coordination',
                'Lethargic',
                'Active',
                'Works slowly',
            ],

            'sensory' => [
                'Vision problems',
                'Hearing problems',
            ],

            'attention_span' => [
                '5',
                '10',
                '15',
                '30',
                '45',
                '60',
            ],

            'cognitive_status' => [
                'Ability to refocus',
                'Awareness of goal',
                'Awareness of error',
                'Oriented to person',
                'Oriented to place',
                'Oriented to time',
                'Distractible',
                'Very confused',
                'Difficulty processing verbal directions',
                'Short term memory impaired',
                'Long term memory impaired',
                'Shows poor awareness of goal',
                'Shows poor error recognition',
                'Impaired judgment',
                'Poor self esteem',
            ],

            'emotional_reactions' => [
                'Cheerful',
                'Sense of humor',
                'Angry',
                'Anxious',
                'Apathetic',
                'Blunted affect',
                'Hostile',
                'Irritable',
                'Labile',
                'Regressed',
                'Withdrawn',
                'Euphoric',
                'Sad',
            ],

            'task_technical_skills' => [
                'Actively involved',
                'Easily adapts',
                'Goal directed',
                'Motivated',
                'Seeks mastery',
                'Receives gratification from work',
                'Careless',
                'Performance anxiety',
                'Easily frustrated',
                'Easily discouraged',
                'Lacks interest',
                'Poorly organized',
                'Refuses structure',
                'Needs structure',
                'Impulsive',
                'Unable to perform',
            ],

            'interpersonal_group_skills' => [
                'Sociable',
                'Follows directions',
                'Initiates conversation',
                'Shares easily',
                'Seclusive/Isolative',
                'Attention seeking',
                'Critical/judgmental',
                'Passive',
                'Aggressive',
                'Physically intrusive',
                'Verbally intrusive',
                'Willing participant',
            ],

            'level_of_interaction' => [
                'Solitary',
                'Parallel',
                'Competitive',
                'Cooperative',
            ],

            'interests_activities' => [
                'Sewing',
                'Cooking',
                'Gardening',
                'Needlework',
                'Movies',
                'Pets/animals',
                'Spiritual/religious',
                'Volunteer',
                'Dancing',
                'Woodworking',
                'Crafts',
                'Collecting',
                'Exercise',
                'Writing',
                'Music listening',
                'Plays instrument',
                'Traveling',
                'Cards',
                'Bingo',
                'Reading',
                'Table games',
                'Active sports',
                'Spectator sports',
                'Shopping',
                'Current events',
            ],

            'objectives' => [
                'Identify and demonstrate ability to express feelings in a safe effective manner.',
                'Identify recreational interests and barriers.',
                'Identify strengths and positive self-qualities.',
                'Increase ability to focus attention.',
                'Identify ability to follow directions and complete task during each session.',
                'Initiate conversation with peers and staff.',
                'Demonstrate an understanding of the benefits of relaxation techniques.',
                'Display ability to take responsibility for his/her own behavior.',
                'Identify capacity for enjoyment of recreational pursuits.',
                'Increase mobility, strength and fine and gross motor coordination.',
            ],
        ];

    } // End Method

}
