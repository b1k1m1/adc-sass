<table>
    <tr>
        <td colspan="3" align="center"><strong>{{ $compname }}</strong></td>
    </tr>
    <tr>
        <td colspan="3" align="center">
            <strong>Balance Sheet as of {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</strong>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>

    {{-- Assets Section --}}
    <tr>
        <th>Category</th>
        <th>Account Name</th>
        <th>{{ t('common.amount', 'Amount') }}</th>
    </tr>
    @foreach($assets as $row)
        <tr>
            <td>{{ $row->category_name }}</td>
            <td>{{ $row->party_nm }}</td>
            <td>{{ $row->balance }}</td>  {{-- Use raw value, no number_format --}}
        </tr>
    @endforeach
    <tr>
        <td colspan="2" align="right"><strong>Total Assets</strong></td>
        <td><strong>{{ $totalAssets }}</strong></td>
    </tr>

    <tr><td colspan="3"></td></tr>

    {{-- Liabilities Section --}}
    <tr>
        <th>Category</th>
        <th>Account Name</th>
        <th>{{ t('common.amount', 'Amount') }}</th>
    </tr>
    @foreach($liabilities as $row)
        <tr>
            <td>{{ $row->category_name }}</td>
            <td>{{ $row->party_nm }}</td>
            <td>{{ -1 * $row->balance }}</td> {{-- Invert sign for liabilities --}}
        </tr>
    @endforeach
    <tr>
        <td colspan="2" align="right">Current Year Profit/(Loss)</td>
        <td>{{ $profitLoss }}</td>
    </tr>
    <tr>
        <td colspan="2" align="right"><strong>Total Liabilities</strong></td>
        <td><strong>{{ $totalLiabilities }}</strong></td>
    </tr>
</table>
