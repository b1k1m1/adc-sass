@extends('admin.admin_dashboard')

@section('title', 'Import Medisoft Payments')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Import Historical Medisoft Payments
                    </h4>

                </div>

            </div>

        </div>


        @if(session('error'))

            <div class="alert alert-danger">
                {{ session('error') }}
            </div>

        @endif


        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    Medisoft Payment CSV
                </h5>

            </div>


            <div class="card-body">

                <form method="POST"
                      action="{{ route('era.medisoft.payments.preview') }}"
                      enctype="multipart/form-data">

                    @csrf


                    <div class="row">

                        <div class="col-md-8">

                            <label class="form-label">
                                Select Medisoft Payment CSV File
                            </label>


                            <input type="file"
                                   name="csv_file"
                                   class="form-control"
                                   accept=".csv,.txt"
                                   required>


                            @error('csv_file')

                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        <div class="col-md-4 d-flex align-items-end">

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="fas fa-eye me-1"></i>

                                Preview Payments

                            </button>

                        </div>

                    </div>

                </form>


                <div class="alert alert-info mt-4 mb-0">

                    <strong>Expected CSV columns:</strong>

                    <br><br>

                    Item,
                    id,
                    Date From,
                    Procedure Code,
                    Description,
                    Attending Provider,
                    Amount,
                    Check Number,
                    Unapplied Amount

                    <hr>

                    <strong>Important:</strong>

                    Participant IDs must already be changed to the
                    correct ADC ParticipantId before uploading.

                </div>

            </div>

        </div>

    </div>

</div>

@endsection