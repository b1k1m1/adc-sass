<table width="100%" style="border-bottom:1px solid #999; font-size:10px;">
  <tr>
    <td width="60%" style="text-align:left;">
      <div class="h1">{{ $compname }}</div>
      @if($compaddr1) <div class="muted">{{ $compaddr1 }}</div> @endif
      @if($compaddr2) <div class="muted">{{ $compaddr2 }}</div> @endif
      @if($compphone) <div class="muted">Phone: {{ $compphone }}</div> @endif
    </td>
    <td width="40%" style="text-align:right;">
      <div class="h2">Participant Activity Assessment</div>
      <div class="muted">
        @if($member)
          {{ $member->id }} — {{ $member->name }}
        @else
          ID: {{ $keys['participantid'] }}
        @endif
      </div>
      <div class="muted">Year: {{ $keys['assessedyear'] }} | Quarter: Q{{ $keys['quarter'] }}</div>
      <div class="muted">Assessment Date: {{ $assessmentDate }}</div>
    </td>
  </tr>
</table>
