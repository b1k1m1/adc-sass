@extends('admin.admin_dashboard')
@section('admin')
    <div class="page-content">
        <div class="container-fluid">

            <h5 class="mb-3">Select Driver for Pickup Report</h5>

                   <form method="GET" action="{{ route('driver-pickup') }}" class="row g-3">
                         <!-- LEFT SIDE CONTROLS -->
                        <div class="col d-flex gap-2 flex-wrap align-items-end">
                            <div class="col-md-4">
                                <label for="staffid" class="form-label">Driver</label>
                                <select name="staffid" id="staffid" class="form-select" required>
                                    <option value="">-- Select Driver --</option>
                                    @foreach($drivers as $d)
                                        <option value="{{ $d->staffid }}">
                                            {{ $d->stafflastname }}, {{ $d->stafffirstname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date" class="form-label">Service Date</label>
                                <input type="date" name="date" id="date" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-2 align-self-end">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </div>
                        <!-- RIGHT SIDE BACK BUTTON -->
                        <div class="col-auto ms-auto">
                            <a href="{{ route('drivers.dashboard') }}#profile1" class="btn btn-primary no-print">
                                {{ t('common.back','Back') }}
                            </a>
                        </div>
                    </form>
        </div>
    </div>
@endsection
