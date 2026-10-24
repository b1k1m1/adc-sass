@extends('admin.admin_dashboard')

@section('title', 'Edit Billing Member')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        {{-- =========================================================
             PAGE TITLE
        ========================================================== --}}

        <div class="row">

            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">

                        Edit Billing Member

                        @if(!empty($member->ParticipantId))

                            -
                            {{ $member->ParticipantId }}

                        @endif

                    </h4>


                    <div>

                        <a href="{{ route('billing.members.index') }}"
                           class="btn btn-secondary">

                            Back to List

                        </a>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             ERROR MESSAGE
        ========================================================== --}}

        @if(session('error'))

            <div class="alert alert-danger">

                {{ session('error') }}

            </div>

        @endif


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}

        @if($errors->any())

            <div class="alert alert-danger">

                <strong>
                    Please correct the following:
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =========================================================
             EDIT FORM
        ========================================================== --}}

        <form method="POST"
              action="{{ route(
                  'billing.members.update',
                  $member->id
              ) }}">

            @csrf


            @include('billing_members._form', [

                'member' =>
                    $member,

                'diagnosisDescriptions' =>
                    $diagnosisDescriptions ?? collect()

            ])


        </form>

    </div>

</div>

@endsection


@section('scripts')

<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | ICD-10 Select2
    |--------------------------------------------------------------------------
    */

    $('.icd10-select').each(function () {

        $(this).select2({

            width: '100%',

            placeholder:
                'Search ICD-10 code or description',

            allowClear: true,

            minimumInputLength: 1,

            ajax: {

                url:
                    "{{ route('lookup.icd10') }}",

                dataType:
                    'json',

                delay:
                    250,

                data: function (params) {

                    return {

                        q:
                            params.term || '',

                        limit:
                            20

                    };

                },

                processResults: function (data) {

                    /*
                    |--------------------------------------------------------------------------
                    | Existing API already returns Select2 structure
                    |--------------------------------------------------------------------------
                    */

                    if (data.results) {

                        return data;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Convert normal array to Select2 structure
                    |--------------------------------------------------------------------------
                    */

                    return {

                        results: $.map(
                            data,
                            function (item) {

                                let code =
                                    item.code
                                    || item.id
                                    || '';

                                let description =
                                    item.short_description
                                    || item.description
                                    || '';

                                return {

                                    id:
                                        code,

                                    text:
                                        code
                                        +
                                        (
                                            description
                                                ? ' - ' + description
                                                : ''
                                        )

                                };

                            }
                        )

                    };

                },

                cache:
                    true

            }

        });

    });

});

</script>

@endsection