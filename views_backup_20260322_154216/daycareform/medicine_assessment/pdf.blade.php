<!doctype html>


<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 11px; }
        .hdr { text-align:center; }
        .cname { font-size: 16px; font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; margin-top: 2px; }
        hr { margin: 8px 0; }

        .meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { padding: 4px 6px; vertical-align: top; }
        .box { border: 1px solid #000; padding: 6px; margin-bottom: 8px; }

        .tbl { width:100%; border-collapse: collapse; }
        .tbl th, .tbl td { border: 1px solid #000; padding: 4px 5px; }
        .tbl th { background: #f2f2f2; font-weight: bold; text-align: left; }

        .small { font-size: 10px; }
        .right { text-align: right; }
        .center { text-align: center; }

    </style>
</head>
<body>

    @php
        // Generic value (shows '—' if null/empty)
        $v = function ($value, $dash = '—') {
        $value = is_string($value) ? trim($value) : $value;
        return ($value === null || $value === '') ? $dash : $value;
        };


        // Yes/No/NULL display (tri-state)
        $yn = function ($value, $dash = '—') {
            if ($value === null || $value === '') return $dash;

            $val = is_string($value) ? strtoupper(trim($value)) : $value;

            if ($val === 1 || $val === true || $val === 'Y' || $val === 'YES') return 'Yes';
            if ($val === 0 || $val === false || $val === 'N' || $val === 'NO') return 'No';

            // If stored as "Yes"/"No"
            if (is_string($value)) {
                $t = ucfirst(strtolower(trim($value)));
                if (in_array($t, ['Yes', 'No'])) return $t;
            }

            return $dash;
        };

        // Checkbox marker: Y / N / —
        $chk = function ($value, $dash = '—') {
            if ($value === null || $value === '') return $dash;

            $val = is_string($value) ? strtoupper(trim($value)) : $value;

            if ($val === 1 || $val === true || $val === 'Y' || $val === 'YES') return 'Y';
            if ($val === 0 || $val === false || $val === 'N' || $val === 'NO') return 'N';

            return $dash;

        

        };

        $yesno = fn($val) => in_array($val, ['YES','NO']) ? $val : '—';

    @endphp

    <div class="hdr">
        <div class="cname">{{ $company->comp_nm ?? 'Company Name' }}</div>
        <div class="title">MEDICATION REVIEW REPORT</div>
    </div>
    <hr>

    <table class="meta">
        <tr>
        <td style="width:50%;">
            <strong>Member:</strong>
            {{ $assessment->participant_id }} - {{ $member->LastName ?? '' }}, {{ $member->FirstName ?? '' }}<br>
            <strong>MR#:</strong> {{ $v($assessment->medical_record_no) }}
        </td>
        <td style="width:50%;">
            <strong>Assessment Date:</strong>
            {{ !empty($assessment->assessment_date) ? \Carbon\Carbon::parse($assessment->assessment_date)->format('m/d/Y') : '—' }}<br>
            <strong>Nurse:</strong> {{ $v($assessment->nurse_name) }}
        </td>
        </tr>
    </table>

    <div class="box">
        <table class="meta" style="margin:0;">
        <tr>
            <tr>
                <td style="width:20%;"><strong>Use of Splints:</strong> {{ $yesno($assessment->use_splints) }}</td>
                <td style="width:20%;"><strong>Braces:</strong> {{ $yesno($assessment->use_braces) }}</td>
                <td style="width:20%;"><strong>Home Glucose Monitoring:</strong> {{ $yesno($assessment->home_glucose) }}</td>
                <td style="width:20%;"><strong>Frequency:</strong> {{ $v($assessment->home_glucose_freq) }}</td>
                <td style="width:20%;"><strong>Physical Therapy:</strong> {{ $v($assessment->physical_therapy) }}
            </tr>

        </tr>


    <tr>
        <td colspan="3">
        <strong>OTC Medications:</strong> {{ $v($assessment->otc_meds) }}
        </td>
    </tr>

    {{-- Reminder Method: show each as Y/N/— --}}
    <tr>
        <td colspan="3">
        <strong>Reminder Method:</strong>

        <table class="tbl" style="margin-top:6px;">
            <thead>
            <tr>
                <th>Option</th>
                <th style="width:60px;" class="center">Y/N/—</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Takes independently</td>
                <td class="center">{{ $chk($assessment->rem_take_independently) }}</td>
            </tr>
            <tr>
                <td>Caregiver gives</td>
                <td class="center">{{ $chk($assessment->rem_caregiver_gives) }}</td>
            </tr>
            <tr>
                <td>Follows label directions</td>
                <td class="center">{{ $chk($assessment->rem_follows_label) }}</td>
            </tr>
            <tr>
                <td>Uses pill minder</td>
                <td class="center">{{ $chk($assessment->rem_pill_minder) }}</td>
            </tr>
            <tr>
                <td>Uses schedule/calendar</td>
                <td class="center">{{ $chk($assessment->rem_schedule_calendar) }}</td>
            </tr>
            <tr>
                <td>Other</td>
                <td>{{ $v($assessment->rem_other) }}</td>
            </tr>
            </tbody>
        </table>

        </td>
    </tr>

    <tr>
        <td style="width:50%;" colspan="2">
        <strong>Pharmacy Provider:</strong> {{ $v($assessment->pharmacy_provider) }}
        </td>
        <td style="width:50%;">
        <strong>Pharmacy Phone:</strong> {{ $v($assessment->pharmacy_phone) }}
        </td>
    </tr>

    <tr>
        <td colspan="2">
        <strong>Takes meds as ordered?</strong> {{ $yn($assessment->takes_as_ordered) }}
        </td>
        <td>
        <strong>Takes meds while at center?</strong> {{ $yn($assessment->takes_while_center) }}
        </td>
    </tr>

    <tr>
        <td colspan="3">
        <strong>Notes:</strong>
        <div style="white-space: pre-wrap;">{{ $v($assessment->notes) }}</div>
        </td>
    </tr>
    </table>

  </div>

  <div class="box">
    <strong>Current Medication List (Nurse Maintained)</strong>

    <table class="tbl" style="margin-top:6px;">
    <thead>
        <tr>
        <th style="width:26px;">#</th>
        <th>Medicine</th>
        <th style="width:120px;">Physician</th>
        <th style="width:70px;">Dosage</th>
        <th style="width:70px;">Frequency</th>
        <th style="width:150px;">Comments</th>
        <th style="width:60px;">Status</th>
        <th style="width:75px;">Status Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($nurseMeds as $i=>$m)
        <tr>
            <td class="right">{{ $i+1 }}</td>
            <td>{{ $v($m->medicine_nm) }}</td>
            <td>{{ $v($m->physician_name ?? $m->doctor_id) }}</td>
            <td>{{ $v($m->dosage_nm) }}</td>
            <td>{{ $v($m->dosage_freq) }}</td>
            <td>{{ $v($m->comments) }}</td>
            <td>{{ $v(ucfirst($m->status ?? 'active')) }}</td>
            <td>{{ !empty($m->status_date) ? \Carbon\Carbon::parse($m->status_date)->format('m/d/Y') : '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="small" style="text-align:center;">No medication list found for this member.</td>
        </tr>
        @endforelse
    </tbody>
    </table>

  </div>

</body>
</html>
