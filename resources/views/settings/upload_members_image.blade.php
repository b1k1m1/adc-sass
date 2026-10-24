@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Upload Member Photo
                    </h4>

                    <div class="page-title-right">

                        <a href="{{ route('members.dashboard') }}#messages1"
                           class="btn btn-primary waves-effect waves-light">
                            {{ t('common.back', 'Back') }}
                        </a>

                    </div>

                </div>

            </div>
        </div>


        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">

                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>

            </div>
        @endif


        {{-- Error Message --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">

                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>

            </div>
        @endif


        {{-- Validation Errors --}}
        @if($errors->any())

            <div class="alert alert-danger mt-3">

                <ul class="mb-0">

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        <div class="card mt-3">

            <div class="card-body">

                {{-- Shift Selection --}}
                <div class="row mb-4">

                    <div class="col-md-12">

                        <label class="form-label fw-bold">
                            Select Shift
                        </label>

                        <div>

                            {{-- Morning Shift --}}
                            <a href="{{ url()->current() }}?shift=A"
                               class="btn {{ $shift == 'A' ? 'btn-primary' : 'btn-outline-primary' }} me-2">

                                Morning Shift

                            </a>


                            {{-- Noon Shift --}}
                            <a href="{{ url()->current() }}?shift=N"
                               class="btn {{ $shift == 'N' ? 'btn-primary' : 'btn-outline-primary' }}">

                                Noon Shift

                            </a>

                        </div>

                    </div>

                </div>


                <form action="{{ route('image.upload') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf


                    {{-- Current Shift --}}
                    <input type="hidden"
                           name="shift"
                           value="{{ $shift }}">


                    {{-- Member Selection --}}
                    <div class="row">

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label for="participant_id"
                                       class="form-label fw-bold">

                                    Select Member

                                </label>

                                <select name="participant_id"
                                        id="participant_id"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Select Member --
                                    </option>

                                    @foreach($members as $member)

                                        <option
                                            value="{{ $member->ParticipantId }}"
                                            {{ old('participant_id') == $member->ParticipantId ? 'selected' : '' }}
                                        >

                                            {{ $member->ParticipantId }}
                                            -
                                            {{ $member->LastName }},
                                            {{ $member->FirstName }}

                                            @if(!empty($member->MiddleName))
                                                {{ $member->MiddleName }}
                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                                <small class="text-muted">

                                    Photo will automatically be saved using the
                                    Participant ID as the filename.

                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- Image Selection --}}
                    <div class="row">

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label for="image"
                                       class="form-label fw-bold">

                                    Choose Member Photo

                                </label>

                                <input type="file"
                                       name="image"
                                       id="image"
                                       class="form-control"
                                       accept=".jpg,.jpeg,.bmp,.png"
                                       required>

                                <small class="text-muted">

                                    Allowed formats:
                                    JPG, JPEG, BMP or PNG.
                                    Maximum size: 5 MB.

                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- Image Preview --}}
                    <div class="row">

                        <div class="col-md-6">

                            <div class="mb-4">

                                <label class="form-label fw-bold">
                                    New Photo Preview
                                </label>

                                <div>

                                    <img id="preview"
                                         src="#"
                                         alt="Image Preview"
                                         style="
                                            display:none;
                                            width:200px;
                                            height:200px;
                                            object-fit:cover;
                                            border:1px solid #ccc;
                                            padding:5px;
                                            border-radius:5px;
                                         ">

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Information --}}
                    <div class="alert alert-info">

                        <strong>Photo Filename:</strong>

                        <span id="filenamePreview">
                            Select a member above
                        </span>

                        <br>

                        <small>
                            If this member already has a photo,
                            the existing photo will be replaced.
                        </small>

                    </div>


                    {{-- Save Button --}}
                    <div class="mt-3">

                        <button type="submit"
                                class="btn btn-primary">

                            {{ t('common.save', 'Save') }}

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const participantSelect =
        document.getElementById('participant_id');

    const filenamePreview =
        document.getElementById('filenamePreview');

    const imageInput =
        document.getElementById('image');

    const preview =
        document.getElementById('preview');


    /*
    |--------------------------------------------------------------------------
    | Display Automatic Filename
    |--------------------------------------------------------------------------
    */
    function updateFilename()
    {
        const participantId = participantSelect.value;

        if (participantId) {

            filenamePreview.textContent =
                participantId + '.jpg';

        } else {

            filenamePreview.textContent =
                'Select a member above';

        }
    }


    participantSelect.addEventListener(
        'change',
        updateFilename
    );


    /*
    |--------------------------------------------------------------------------
    | New Image Preview
    |--------------------------------------------------------------------------
    */
    imageInput.addEventListener('change', function () {

        const file = this.files[0];

        if (file) {

            preview.src =
                URL.createObjectURL(file);

            preview.style.display =
                'block';

        } else {

            preview.style.display =
                'none';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Initial Filename
    |--------------------------------------------------------------------------
    */
    updateFilename();

});

</script>

@endsection