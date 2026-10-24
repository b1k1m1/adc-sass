<table>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}" style="text-align:center; font-weight:bold; font-size:16px;">
            {{ config('company.comp_nm', 'Company Name') }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}" style="text-align:center;">
            {{ config('company.company_address', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}" style="text-align:center;">
            {{ config('company.company_city_state_zip', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}" style="text-align:center;">
            {{ config('company.company_phone', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}" style="text-align:center; font-weight:bold;">
            HMO Attendance Crosstab Report
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}"><strong>HMO:</strong> {{ $selectedHmo }}</td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}"><strong>Date Range:</strong> {{ $startDateInput }} - {{ $endDateInput }}</td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($dateHeaders) }}"><strong>Grand Total:</strong> {{ $grandTotal }}</td>
    </tr>
    <tr></tr>

    <tr>
        <th>ParticipantId</th>
        <th>LastName</th>
        <th>FirstName</th>
        @foreach($dateHeaders as $hdr)
            <th>{{ $hdr['day_no'] }}</th>
        @endforeach
        <th style="text-align:right;">Total</th>
    </tr>

    @forelse($rows as $row)
        <tr>
            <td>{{ $row['ParticipantId'] }}</td>
            <td>{{ $row['LastName'] }}</td>
            <td>{{ $row['FirstName'] }}</td>
            @foreach($row['days'] as $val)
                <td>{{ $val }}</td>
            @endforeach
            <td style="text-align:right;">{{ $row['row_total'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="{{ 4 + count($dateHeaders) }}">No records found for selected HMO and date range.</td>
        </tr>
    @endforelse

    <tr>
        <th colspan="3" style="text-align:right;">Total</th>
        @foreach($columnTotals as $total)
            <th>{{ $total }}</th>
        @endforeach
        <th style="text-align:right;">{{ $grandTotal }}</th>
    </tr>
</table>
