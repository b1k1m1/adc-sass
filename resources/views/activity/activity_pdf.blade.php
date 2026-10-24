@php
  // Small helper to mark Y with a check
  $mark = fn($v) => $v === 'Y' ? '✓' : '';

  // Group the fields for compact rendering
  $interaction = [
      ['Interested in individual activities', $row->Interestedinindividualactivity ?? null],
      ['Socializes with other members',       $row->Socializeswithothermembers ?? null],
      ['Socialize in groups',                 $row->Socializeingroups ?? null],
  ];

  $participation = [
      ['Active Participant',   $row->MemberisActiveParticipant ?? null],
      ['Passive Participant',  $row->MemberisPassiveParticipant ?? null],
      ['Seldom Participates',  $row->MemberSeldomParticipates ?? null],
  ];

  $sitting = [
      ['Individual',       $row->Individual ?? null],
      ['With other member',$row->WithOtherMember ?? null],
      ['Small Group',      $row->SmallGroup ?? null],
      ['Large Group',      $row->LargeGroup ?? null],
  ];

  $emotional = [
      ['Happy, Cheerful',     $row->Happy_Cheerful ?? null],
      ['Depressed, Sad',      $row->Depressed_Sad ?? null],
      ['Content, Satisfied',  $row->Content_Satisfied ?? null],
      ['Worried, Anxious',    $row->Worried_Anxious ?? null],
      ['Angry, Mad',          $row->Angry_Mad ?? null],
      ['Serious',             $row->Serious ?? null],
  ];

  $cognitive = [
      ['Alert, Oriented',                    $row->Alert_Oriented ?? null],
      ["Confused (Member's need help)",      $row->Confused_MembersNeedHelp ?? null],
      ['Forgetful',                          $row->Forgetful ?? null],
      ['Cannot Express Needs',               $row->CannotExpressNeeds ?? null],
  ];

  $behavioral = [
      ['Calm',        $row->Calm ?? null],
      ['Anxious',     $row->Anxious ?? null],
      ['Tearful',     $row->Tearful ?? null],
      ['Disruptive',  $row->Disruptive ?? null],
      ['Agitated',    $row->Agitated ?? null],
  ];

  $recommended = [
      ['Empowerment', $row->Empowerment ?? null],
      ['Maintenance', $row->Maintenance ?? null],
  ];

  $programs = [
      ['Musical Activities',                     $row->MusicalActivities ?? null],
      ['Educational Talks',                      $row->EducationalTalks ?? null],
      ['Trips and Group Outings',                $row->TripsAndGroupOutings ?? null],
      ['Games',                                  $row->Games ?? null],
      ['ESL Classes',                            $row->ESLClasses ?? null],
      ['Exercise and other physical activities', $row->ExerciseandOtherPhysical ?? null],
      ['Arts and Crafts',                        $row->ArtsandCrafts ?? null],
      ['Spiritual and Religious Activities',     $row->SpiritualandReligiousActivity ?? null],
  ];

  $interventions = [
      ['Needs Direct Assistance with Activity',                     $row->NeedsDirectAssistanceWith ?? null],
      ['Provide Step by Step Task Demonstration',                   $row->ProvideStepByStepTaskDemo ?? null],
      ['Structured Seating to Optimize Therapeutic Interventions',  $row->StructuredSeatingToOptimize ?? null],
  ];
@endphp

{{-- Top meta (Assessed By / Prepared By) --}}
<table width="100%" style="margin-bottom:10px; border-collapse:collapse;">
    <tr>
        <th width="50%" style="text-align:center; padding:4px 0;">
            Assessed By
        </th>
        <th width="50%" style="text-align:center; padding:4px 0;">
            Prepared By
        </th>
    </tr>

    <tr>
        <td width="50%" style="text-align:center; padding:4px 0 8px 0;">
            {{ $row->AssessedBy ?? '' }}
        </td>
        <td width="50%" style="text-align:center; padding:4px 0 8px 0;">
            {{ $row->PreparedBy ?? '' }}
        </td>
    </tr>
</table>
{{-- A reusable section renderer --}}
@php
  $render = function($title, $rows) use ($mark) {
      $out  = '<div class="section-title">'.$title.'</div>';

      $out .= '<table class="tbl"><tbody>';
      foreach ($rows as [$label, $val]) {
          $out .= '<tr>'
                . '<td class="label">'.e($label).'</td>'
                . '<td class="chk">'.($val === 'Y' ? '✓' : '').'</td>'
                . '</tr>';
      }
      $out .= '</tbody></table>';
      return $out;
  };
@endphp

{!! $render("Member's Interaction", $interaction) !!}
{!! $render("Member's Participation", $participation) !!}
{!! $render("Member's Sitting Preference", $sitting) !!}
{!! $render("Member's Emotional Status", $emotional) !!}
{!! $render("Member's Cognitive Status", $cognitive) !!}
{!! $render("Member's Behavioral Status", $behavioral) !!}
{!! $render("Recommended Program", $recommended) !!}
{!! $render("Therapeutic Programs", $programs) !!}
{!! $render("Therapeutic Intervention Needs", $interventions) !!}
