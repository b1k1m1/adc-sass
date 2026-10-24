<table>
    <tr>
        <td colspan="7"
            style="text-align:center; font-weight:bold; font-size:16px;">
            {{ $company['name'] ?? '' }}
        </td>
    </tr>

    <tr>
        <td colspan="7" style="text-align:center;">
            {{ $company['address'] ?? '' }}
        </td>
    </tr>

    <tr>
        <td colspan="7" style="text-align:center;">
            @if(!empty($company['phone']))
                Phone: {{ $company['phone'] }}
            @endif

            @if(!empty($company['fax']))
                &nbsp; Fax: {{ $company['fax'] }}
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="7"
            style="text-align:center; font-weight:bold;">
            Shift BP / Pulse / Weight Average Report
        </td>
    </tr>

    <tr>
        <td colspan="7"></td>
    </tr>

    <tr>
        <td><strong>Shift</strong></td>
        <td>{{ $shift == 'A' ? 'Morning' : 'Noon' }}</td>

        <td><strong>Start Date</strong></td>
        <td>{{ $startDate }}</td>

        <td><strong>End Date</strong></td>
        <td>{{ $endDate }}</td>

        <td></td>
    </tr>

    <tr>
        <td colspan="7"></td>
    </tr>

    <tr>
        <th>ParticipantId</th>
        <th>LastName</th>
        <th>FirstName</th>
        <th>Avg LowBP</th>
        <th>Avg HighBP</th>
        <th>Avg Pulse</th>
        <th>Avg Weight</th>
    </tr>

    @foreach($rows as $row)
        <tr>
            <td>{{ $row->ParticipantId }}</td>
            <td>{{ $row->LastName }}</td>
            <td>{{ $row->FirstName }}</td>
            <td>{{ $row->avg_lowbp }}</td>
            <td>{{ $row->avg_highbp }}</td>
            <td>{{ $row->avg_pulse }}</td>

            <td>
                {{ $row->avg_weight !== null
                    ? number_format((float) $row->avg_weight, 1)
                    : '' }}
            </td>
        </tr>
    @endforeach
</table>
