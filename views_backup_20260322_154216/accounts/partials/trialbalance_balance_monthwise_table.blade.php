<thead>
<tr>
    <th>GL ID</th>
    <th>Category</th>
    <th>Account Code</th>
    <th>Account Name</th>
    <th>Opening Debit</th>
    <th>Opening Credit</th>
    @foreach($months as $month)
        <th>Debit<br>{{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}</th>
        <th>Credit<br>{{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}</th>
    @endforeach
    <th>Ending Debit</th>
    <th>Ending Credit</th>
</tr>
</thead>
@php
    $grand_op_dr = 0; $grand_op_cr = 0;
    $grand_bl_dr = 0; $grand_bl_cr = 0;
    $monthTotals = [];
    foreach($months as $month) {
        $monthTotals[$month]['tr_dr'] = 0;
        $monthTotals[$month]['tr_cr'] = 0;
    }
@endphp
<tbody>
@foreach($groupedByType as $type => $accounts)
    <tr class="table-secondary">
        <td colspan="{{ 6 + (count($months)*2) + 2 }}"><strong>{{ $type }}</strong></td>
    </tr>
    @foreach($accounts as $acc)
    <tr>
        <td>{{ $acc['info']->gl_id }}</td>
        <td>{{ $acc['info']->category_name }}</td>
        <td>{{ $acc['info']->party_cd }}</td>
        <td>{{ $acc['info']->party_nm }}</td>
        <td class="text-end">{{ $acc['op_dr'] > 0 ? number_format($acc['op_dr'],2) : '' }}</td>
        <td class="text-end">{{ $acc['op_cr'] > 0 ? number_format($acc['op_cr'],2) : '' }}</td>
        @foreach($months as $m)
            <td class="text-end">{{ $acc['months'][$m]['tr_dr'] > 0 ? number_format($acc['months'][$m]['tr_dr'],2) : '' }}</td>
            <td class="text-end">{{ $acc['months'][$m]['tr_cr'] > 0 ? number_format($acc['months'][$m]['tr_cr'],2) : '' }}</td>
        @endforeach
        <td class="text-end">{{ $acc['bl_dr'] > 0 ? number_format($acc['bl_dr'],2) : '' }}</td>
        <td class="text-end">{{ $acc['bl_cr'] > 0 ? number_format($acc['bl_cr'],2) : '' }}</td>
        @php
            $grand_op_dr += $acc['op_dr'];
            $grand_op_cr += $acc['op_cr'];
            $grand_bl_dr += $acc['bl_dr'];
            $grand_bl_cr += $acc['bl_cr'];
            foreach($months as $month) {
            $monthTotals[$month]['tr_dr'] += $acc['months'][$month]['tr_dr'];
            $monthTotals[$month]['tr_cr'] += $acc['months'][$month]['tr_cr'];
            }
        @endphp
    </tr>
    @endforeach
@endforeach
</tbody>
<tfoot>
    <tr class="table-dark fw-bold">
        <td colspan="4" class="text-end">Grand Total</td>
        <td class="text-end">{{ number_format($grand_op_dr,2) }}</td>
        <td class="text-end">{{ number_format($grand_op_cr,2) }}</td>
        @foreach($months as $month)
            <td class="text-end">{{ number_format($monthTotals[$month]['tr_dr'],2) }}</td>
            <td class="text-end">{{ number_format($monthTotals[$month]['tr_cr'],2) }}</td>
        @endforeach
        <td class="text-end">{{ number_format($grand_bl_dr,2) }}</td>
        <td class="text-end">{{ number_format($grand_bl_cr,2) }}</td>
    </tr>
</tfoot>
