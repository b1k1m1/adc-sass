@extends('admin.admin_dashboard')
@section('admin')
@section('page-toolbar')
<div class='no-print'>
    <button type="button" class="btn btn-secondary"
        onclick="window.open(`{{ route('vitals.print', ['ParticipantId'=>str_pad($member->ParticipantId,3,'0',STR_PAD_LEFT), 'monthYear'=>$monthYear]) }}`, '_blank')">
            Print Preview
    </button>
    <a class="btn btn-danger"
        href="{{ route('vitals.pdf', ['ParticipantId'=>str_pad($member->ParticipantId,3,'0',STR_PAD_LEFT), 'monthYear'=>$monthYear]) }}">
            Export PDF
    </a>
    <a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
        {{ t('common.back','Back') }}
    </a>
</div>
@endsection
@inject('carbon', 'Carbon\Carbon')
<style>

    input[type="checkbox"] {
        height: 25px;
        width: 25px;
        accent-color: gray; /* Default */
    }

    input[type="checkbox"]:checked {
        accent-color: blue; /* Checkbox color when checked */
    }

    input[type="checkbox"]:checked + label {
        color: blue;
        font-weight: bold;
    }

    .checkbox-label {
        margin-right: 15px;
    }

    .checkbox-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .col-date {
    width: 120px;
    min-width: 120px;
}
    .card-left h6 {
        color: navy;
        font-size: 0.9rem;
    }

    .card-center h6 {
        color: green;
        font-size: 1.1rem;
    }

    .card-right h6 {
        color: maroon;
        font-size: 1rem;
    }

    .img-thumbnail {
    max-width: 125px; /* Adjust as needed */
    height: auto;
    }

    .data-entry-table {
    font-size: 0.85rem;
}

.data-entry-table th {
    font-weight: 600;
    white-space: nowrap;
}

.data-entry-table td, .data-entry-table th {
    padding: 0.5rem;
    vertical-align: middle;
}

.data-entry-table input {
    min-width: 60px;
    text-align: center;
}

.sub-header th {
    background-color: #f8f9fa;
    font-weight: 500;
    font-size: 0.8rem;
}


.weekend-row {
    background-color: rgba(33, 199, 33, 0.5);
}

.form-actions {
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
}

.btn-success {
    background-color: #34a853;
    border-color: #34a853;
}

.btn-success:hover {
    background-color: #2d9248;
    border-color: #2d9248;
}

/* Custom checkbox styling */
.custom-checkbox {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 4px;
    outline: none;
    cursor: pointer;
    position: relative;
    vertical-align: middle;
}

/* Medication checkbox - Green style */
.medication-check:checked {
    background-color: #4CAF50; /* Green */
    border-color: #4CAF50;
}

/* Uniform checkbox - Blue style */
.uniform-check:checked {
    background-color: #2196F3; /* Blue */
    border-color: #2196F3;
}

/* Checkmark styling */
.custom-checkbox:checked::before {
    content: "✓";
    position: absolute;
    color: white;
    font-size: 14px;
    font-weight: bold;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
}

/* Hover effects */
.custom-checkbox:hover {
    border-color: #aaa;
}

/* Focus state */
.custom-checkbox:focus {
    box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.3);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .data-entry-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    .card-header h3 small {
        display: block;
        float: none !important;
        margin-top: 0.5rem;
    }
}


</style>

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <!-- LEFT SIDE -->
                    <div>
                        <h4 class="mb-0 font-size-18">Member's ADL Entry</h4>
                    </div>
                    <!-- RIGHT SIDE TOOLBAR -->
                    <div class="page-title-right">
                        @yield('page-toolbar')
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        @php
            $formattedBirthDate = $member->DateOfBirth ? \Carbon\Carbon::parse($member->DateOfBirth)->format('m/d/Y') : '';
            $formattedPAEXPDate = $member->medicaid_PA_End ? \Carbon\Carbon::parse($member->medicaid_PA_End)->format('m/d/Y') : '';
        @endphp

        <div class="row">
            <div class="col-12">
                <div class="card border border-success">
                    <div class="card-header bg-transparent border-success">
                        <div class="d-flex justify-content-between align-items-center w-100"">
                            <!-- Left Aligned -->
                            <div class="text-start card-left">
                                <h6 class="mb-0 text-primary fs-5">
                                    <strong>ID:</strong> {{ $member->ParticipantId }}<br>
                                    <strong>Name:</strong> {{ $member->LastName }} {{ $member->FirstName }} &nbsp; &nbsp;
                                    <strong>Date Of Birth: </strong>{{ $formattedBirthDate  }}
                                    <br><br>
                                    <strong>BP:</strong> {{ $member->bloodpressure }}&nbsp;&nbsp; {{ $member->Allergies1 }}
                                    <br>
                                    <strong>Sugar:</strong> {{ $member->bloodsugar }}&nbsp; &nbsp; {{ $member->Allergies2 }}
                                    <br>
                                    <strong>Weight:</strong> {{ $member->weight }}&nbsp; &nbsp; {{ $member->Allergies3 }}
                                    <br><br>
                                    <strong>PA Exp:</strong> {{ $formattedPAEXPDate }}
                                </h6>
                            </div>

                             <!-- Center Aligned and Vertically Centered -->
                            <div class="text-center align-self-center card-center">
                                <h6 class="mb-0 fs-5">

                                    <div>
                                        @php
                                            $numericId = (int) trim($member->ParticipantId); // '005 ' → 5
                                            $imageFilename = $numericId . '.jpg';
                                            $imagePath = 'members/images/' . $imageFilename;
                                            $fullImagePath = public_path($imagePath);
                                            $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('members/images/no_image.jpg');
                                        @endphp
                                        <img src="{{ $displayPath }}" alt="Participant Photo" width="150" height="150">
                                    </div>

                                    <!--
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" id="toilet" class="form-check-input" disabled {{ $member->toilet_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="toilet" class="checkbox-label">Toilet</label>

                                        <input type="checkbox" id="transfer" class="form-check-input" disabled {{ $member->transfer_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="transfer" class="checkbox-label">Transfer</label>

                                        <input type="checkbox" id="locomotion" class="form-check-input" disabled {{ $member->locomotion_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="locomotion" class="checkbox-label">Locomotion</label>

                                        <input type="checkbox" id="eating" class="form-check-input" disabled {{ $member->eating_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="eating" class="checkbox-label">Eating</label>
                                    </div>
                                -->

                                </h6>
                            </div>

                            <!-- Right Aligned -->
                            <div class="text-end card-right">
                                <h6 class="mb-0 fs-5">

                                    <strong>Doctor: </strong> {{ $member->primary_doctor_nm }}<br>
                                    <strong>Dr.Phone: </strong>{{ substr($member->primary_doctor_ph, 0, 3) }}-{{ substr($member->primary_doctor_ph, 3, 3) }}-{{ substr($member->primary_doctor_ph, 6) }}<br>
                                    <strong>Comments: </strong>  {{ $member->Comments }}<br><br>

                                    <!--<input type="text" data-mask="(000)000-0000" value="{{ $member->primary_doctor_ph }}" disabled="disabled"> -->
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" id="toilet" class="form-check-input" disabled {{ $member->toilet_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="toilet" class="checkbox-label">Toilet</label>

                                        <input type="checkbox" id="transfer" class="form-check-input" disabled {{ $member->transfer_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="transfer" class="checkbox-label">Transfer</label>

                                        <input type="checkbox" id="locomotion" class="form-check-input" disabled {{ $member->locomotion_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="locomotion" class="checkbox-label">Locomotion</label>

                                        <input type="checkbox" id="eating" class="form-check-input" disabled {{ $member->eating_yn == 'Y' ? 'checked' : '' }}>
                                        <label for="eating" class="checkbox-label">Eating</label>
                                    </div>

                                </h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="physicalDataForm" method="POST" action="{{ route('nursing.store_bpdata',['ParticipantId'=>str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) , 'monthYear'=>$monthYear, 'type'=>$type] ) }}">
                            @csrf
                        <div class="table-rep-plugin">
                            <div class="table-responsive mb-0" data-pattern="priority-columns">
                                <!-- <table table-bordered table-sm data-entry-table> -->
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">Day</th>
                                            <th class="text-center col-date">Date</th>
                                            <th class="text-center">Toilet</th>
                                            <th class="text-center">Transfer</th>
                                            <th class="text-center">Locomotion</th>
                                            <th class="text-center">Eating</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        @php
                                             $date = \Carbon\Carbon::createFromFormat('Y-m', $monthYear);
                                        @endphp

                                        @for($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $currentDate = $date->copy()->day($day);
                                                $existingRecord = $existingData[$day] ?? null;

                                            @endphp

                                        <tr>
                                            <td>{{ $day }}</td>
                                            <td>{{ $currentDate->format('D, M j') }}</td>

                                            <td class="text-center">
                                                <input type="hidden" name="toiletyn[{{ $day }}]" value="N">
                                                <input type="checkbox" name="toiletyn[{{ $day }}]" value="Y"
                                                       {{ ($existingRecord->toiletyn ?? 'N') == 'Y' ? 'checked' : '' }}
                                                       class="custom-checkbox medication-check">
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="transferyn[{{ $day }}]" value="N">
                                                <input type="checkbox" name="transferyn[{{ $day }}]" value="Y"
                                                       {{ ($existingRecord->transferyn ?? 'N') == 'Y' ? 'checked' : '' }}
                                                       class="custom-checkbox uniform-check">
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="locomotionyn[{{ $day }}]" value="N">
                                                <input type="checkbox" name="locomotionyn[{{ $day }}]" value="Y"
                                                       {{ ($existingRecord->locomotionyn ?? 'N') == 'Y' ? 'checked' : '' }}
                                                       class="custom-checkbox medication-check">
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="eatingyn[{{ $day }}]" value="N">
                                                <input type="checkbox" name="eatingyn[{{ $day }}]" value="Y"
                                                       {{ ($existingRecord->eatingyn ?? 'N') == 'Y' ? 'checked' : '' }}
                                                       class="custom-checkbox uniform-check">
                                            </td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('nursing.members',$type) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                        </form>
                    </div>
                </div>
                <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->

    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
@endsection

@section('scripts')
<script>

    $(document).ready(function() {

        let formChanged = false;

        // Track form changes
        $('#physicalDataForm').on('change keyup', 'input, select, textarea', function() {
            formChanged = true;
        });

        // Warn before leaving if changes exist
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        // Handle form submission
        $('#physicalDataForm').submit(function() {
            formChanged = false;
            return true;
        });

        // Handle navigation attempts
        $('a').click(function(e) {
            if (formChanged) {
                if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                    e.preventDefault();
                }
            }
        });
    });
    </script>

@endsection
