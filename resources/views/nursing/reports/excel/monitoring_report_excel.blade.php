<table>
    <tr>
        <td colspan="7" style="text-align:center; font-weight:bold; font-size:16px;">{{ $company['name'] ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="7" style="text-align:center;">{{ $company['address'] ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="7" style="text-align:center;">
            @if(!empty($company['phone'])) Phone: {{ $company['phone'] }} @endif
            @if(!empty($company['fax'])) Fax: {{ $company['fax'] }} @endif
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align:center; font-weight:bold;">Monitoring Report for Members</td>
    </tr>
    <tr>
        <td><strong>Shift</strong></td>
        <td colspan="6">{{ $shift == 'A' ? 'Morning' : 'Noon' }}</td>
    </tr>
    <tr><td colspan="7"></td></tr>

    <tr>
        <th>ParticipantId</th>
        <th>LastName</th>
        <th>FirstName</th>
        <th>Blood Sugar</th>
        <th>Blood Pressure</th>
        <th>Weight</th>
        <th>Others</th>
    </tr>

    @foreach($rows as $row)
        <tr>
            <td>{{ $row->ParticipantId }}</td>
            <td>{{ $row->LastName }}</td>
            <td>{{ $row->FirstName }}</td>
            <td>{{ $row->bloodsugar }}</td>
            <td>{{ $row->bloodpressure }}</td>
            <td>{{ $row->weight }}</td>
            <td>{{ $row->others }}</td>
        </tr>
    @endforeach
</table>
