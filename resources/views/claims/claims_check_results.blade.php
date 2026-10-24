@extends('admin.admin_dashboard')
@section('title', 'Claims')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Claims Validation Results</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        @if($message)
                <div class="alert alert-info mt-2">{{ $message }}</div>
            @endif

            @if(!empty($claims))
                <form method="POST" action="{{ route('claims.send') }}">
                    @csrf

                    <p class="small text-muted">
                        Only selected claims will be sent (used in 837P file).
                    </p>

                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th class="text-center" style="width:40px;">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>Claim #</th>
                                <th>Participant</th>
                                <th>Service From</th>
                                <th>Service To</th>
                                <th>Lines</th>
                                <th>Status</th>
                                <th>Errors</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($claims as $c)
                                <tr class="{{ $c['is_valid'] ? 'table-success' : 'table-danger' }}">
                                    <td class="text-center">
                                        @if($c['is_valid'])
                                            <input type="checkbox"
                                                name="claim_nos[]"
                                                value="{{ $c['claim_no'] }}"
                                                class="claim-checkbox"
                                                checked>
                                        @else
                                            {{-- Invalid claims: not selectable by default --}}
                                            <input type="checkbox"
                                                disabled>
                                        @endif
                                    </td>
                                    <td>{{ $c['claim_no'] }}</td>
                                    <td>{{ $c['ParticipantId'] }}</td>
                                    <td>{{ $c['from'] }}</td>
                                    <td>{{ $c['to'] }}</td>
                                    <td>{{ $c['lines_count'] }}</td>
                                    <td>
                                        {{ $c['is_valid'] ? 'VALID' : 'INVALID' }}
                                    </td>
                                    <td>
                                        @if(!empty($c['errors']))
                                            <ul class="mb-0">
                                                @foreach($c['errors'] as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">No errors</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between mt-2">
                        <a href="{{ url()->previous() }}"
                        class="btn btn-outline-secondary btn-sm">
                            Back to Claims Entry
                        </a>

                        <button type="submit" class="btn btn-primary btn-sm">
                            Send Selected (Generate 837P)
                        </button>
                    </div>

                </form>
            @endif
    </div>
</div>
@endsection

@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const checked = this.checked;
            document.querySelectorAll('.claim-checkbox').forEach(cb => {
                cb.checked = checked;
            });
        });
    }
});
</script>
@endsection
