<table>
    <tr>
        <td colspan="33" style="text-align:center; font-weight:bold; font-size:16px;">
            {{ config('company.company_name', 'Company Name') }}
        </td>
    </tr>
    <tr>
        <td colspan="33" style="text-align:center;">{{ config('company.company_address', '') }}</td>
    </tr>
    <tr>
        <td colspan="33" style="text-align:center;">{{ config('company.company_city_state_zip', '') }}</td>
    </tr>
    <tr>
        <td colspan="33" style="text-align:center;">{{ config('company.company_phone', '') }}</td>
    </tr>
    <tr>
        <td colspan="33" style="text-align:center; font-weight:bold;">Member Attendance Crosstab Report</td>
    </tr>
    <tr>
        <td colspan="33"><strong>Member:</strong> {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}</td>
    </tr>
    <tr>
        <td colspan="33"><strong>Range:</strong> {{ $startYearMonth }} to {{ $endYearMonth }}</td>
    </tr>
    <tr>
        <td colspan="33"><strong>Grand Total:</strong> {{ $grandTotal }}</td>
    </tr>
    <tr></tr>

    <tr>
        <th>Month</th>
        @for($day = 1; $day <= 31; $day++)
            <th>{{ $day }}</th>
        @endfor
        <th>Total</th>
    </tr>

    @foreach($months as $month)
        <tr>
            <td>{{ $month['month_name'] }}</td>
            @for($day = 1; $day <= 31; $day++)
                <td>{{ $month['days'][$day] }}</td>
            @endfor
            <td>{{ $month['row_total'] }}</td>
        </tr>
    @endforeach

    <tr>
        <th>Total</th>
        @for($day = 1; $day <= 31; $day++)
            <th>{{ $dayTotals[$day] }}</th>
        @endfor
        <th>{{ $grandTotal }}</th>
    </tr>
</table>
