<table>
    <tr>
        <td colspan="5"
            style="text-align:center; font-weight:bold; font-size:16px;">
            {{ $company['name'] ?? '' }}
        </td>
    </tr>

    <tr>
        <td colspan="5" style="text-align:center;">
            {{ $company['address'] ?? '' }}
        </td>
    </tr>

    <tr>
        <td colspan="5" style="text-align:center;">
            @if(!empty($company['phone']))
                Phone: {{ $company['phone'] }}
            @endif

            @if(!empty($company['fax']))
                &nbsp; Fax: {{ $company['fax'] }}
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="5"
            style="text-align:center; font-weight:bold;">
            Member BP / Pulse / Weight Report
        </td>
    </tr>

    <tr>
        <td colspan="5"></td>
    </tr>

    <tr>
        <td><strong>Member</strong></td>

        <td colspan="4">
            {{ $member->ParticipantId ?? '' }} -
            {{ $member->LastName ?? '' }},
            {{ $member->FirstName ?? '' }}
        </td>
    </tr>

    <tr>
        <td><strong>Start Date</strong></td>
        <td>{{ $startDate }}</td>

        <td><strong>End Date</strong></td>
        <td>{{ $endDate }}</td>

        <td></td>
    </tr>

    <tr>
        <td><strong>Avg LowBP</strong></td>
        <td>{{ $avg->avg_lowbp ?? '' }}</td>

        <td><strong>Avg HighBP</strong></td>
        <td>{{ $avg->avg_highbp ?? '' }}</td>

        <td></td>
    </tr>

    <tr>
        <td><strong>Avg Pulse</strong></td>
        <td>{{ $avg->avg_pulse ?? '' }}</td>

        <td><strong>Avg Weight</strong></td>
        <td>
            {{ isset($avg->avg_weight)
                ? number_format((float) $avg->avg_weight, 1)
                : '' }}
        </td>

        <td></td>
    </tr>

    <tr>
        <td colspan="5"></td>
    </tr>

    <tr>
        <th>Date</th>
        <th>LowBP</th>
        <th>HighBP</th>
        <th>Pulse</th>
        <th>Weight</th>
    </tr>

    @foreach($rows as $row)
        <tr>
            <td>
                {{ \Carbon\Carbon::parse($row->CalenderDate)->format('m/d/Y') }}
            </td>

            <td>{{ $row->LowBP }}</td>

            <td>{{ $row->HighBP }}</td>

            <td>{{ $row->Pulse }}</td>

            <td>
                {{ $row->Weight !== null
                    ? number_format((float) $row->Weight, 1)
                    : '' }}
            </td>
        </tr>
    @endforeach
</table>
