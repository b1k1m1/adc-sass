@extends('admin.admin_dashboard')

@section('title', 'Nursing Comments')
@section('page-title', 'Nursing Comments')

@section('admin')

<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                Nursing Comments
            </h5>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold d-block">Select Shift</label>

                    <button type="button"
                            class="btn btn-primary me-2 shift-btn"
                            data-shift="A">
                        Morning Shift
                    </button>

                    <button type="button"
                            class="btn btn-outline-primary shift-btn"
                            data-shift="N">
                        Noon Shift
                    </button>

                    <input type="hidden" id="selected_shift" value="A">
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="participant_id" class="form-label fw-bold">
                        Select Member
                    </label>

                    <select id="participant_id" class="form-select">
                        <option value="">Select Member</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" id="loadCommentsBtn" class="btn btn-info me-2">
                        Load Comments
                    </button>

                    <button type="button" id="exportPdfBtn" class="btn btn-danger me-2">
                        Export to PDF
                    </button>

                    <button type="button" id="clearBtn" class="btn btn-secondary">
                        Clear
                    </button>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-12">
                    <div id="memberStatus" class="alert alert-light border d-none mb-2"></div>
                </div>
            </div>

            <form id="nursingCommentsForm">
                @csrf

                <div class="row">
                    <div class="col-md-12">
                        <label for="comments" class="form-label fw-bold">
                            Comments
                        </label>
                        <textarea id="comments"
                                name="comments"
                                class="form-control"
                                rows="15"
                                maxlength="5000"
                                placeholder="Enter nursing comments here..."></textarea>

                        <div class="mt-2">
                            <button type="button" id="startDictationBtn" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-microphone me-1"></i> Start Dictation
                            </button>

                            <button type="button" id="stopDictationBtn" class="btn btn-outline-danger btn-sm" disabled>
                                <i class="fas fa-stop me-1"></i> Stop Dictation
                            </button>

                            <span id="dictationStatus" class="ms-2 text-muted small">
                                Dictation is off.
                            </span>
                        </div>

                        <div class="alert alert-warning mt-2 py-2 mb-1">
                            <strong>Important:</strong> Please review dictated comments before saving.
                        </div>

                        <div class="d-flex justify-content-between mt-1">

                            <small class="text-muted">
                                Maximum 5000 characters.
                            </small>
                            <small class="text-muted">
                                <span id="charCount">0</span> / 5000
                            </small>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" id="saveCommentsBtn" class="btn btn-success">
                        Save Comments
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

@endsection

@push('scripts')

<script>
$(document).ready(function () {

    let selectedShift = 'A';
    let recognition = null;
    let isDictating = false;
    let finalDictationText = '';

    $('#participant_id').select2({
        placeholder: 'Select Member',
        allowClear: true,
        width: '100%'
    });

    loadMembers(selectedShift);

    $('.shift-btn').on('click', function () {
        selectedShift = $(this).data('shift');

        $('#selected_shift').val(selectedShift);

        $('.shift-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');

        clearForm();
        loadMembers(selectedShift);
    });

    function loadMembers(shift) {
        $('#participant_id').empty().append('<option value="">Loading members...</option>');

        $.ajax({
            url: "{{ url('/nursing/comments/members') }}/" + shift,
            type: "GET",
            success: function (response) {
                $('#participant_id').empty().append('<option value="">Select Member</option>');

                if (response.success && response.members.length > 0) {
                    $.each(response.members, function (index, member) {
                        $('#participant_id').append(
                            $('<option>', {
                                value: member.id,
                                text: member.text
                            })
                        );
                    });
                }

                $('#participant_id').val('').trigger('change');
            },
            error: function () {
                $('#participant_id').empty().append('<option value="">Select Member</option>');
                showMessage('Unable to load members.', 'danger');
            }
        });
    }

    $('#loadCommentsBtn').on('click', function () {
        let participantId = $('#participant_id').val();
        let shift = $('#selected_shift').val();

        if (!participantId) {
            showMessage('Please select a member first.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('nursing.comments.load') }}",
            type: "GET",
            data: {
                participant_id: participantId,
                shift: shift
            },
            success: function (response) {
                if (response.success) {
                    $('#comments').val(response.comments);
                    updateCharCount();

                    if (response.exists) {
                        showMessage('Existing comments loaded. You can edit and update.', 'info');
                    } else {
                        showMessage('No comments found for this member and shift. You can add new comments.', 'warning');
                    }
                }
            },
            error: function (xhr) {
                showMessage('Unable to load comments.', 'danger');
            }
        });
    });

    $('#exportPdfBtn').on('click', function () {
    let participantId = $('#participant_id').val();
    let shift = $('#selected_shift').val();

    if (!participantId) {
        showMessage('Please select a member first.', 'warning');
        return;
    }

    let pdfUrl = "{{ route('nursing.comments.pdf') }}"
        + "?participant_id=" + encodeURIComponent(participantId)
        + "&shift=" + encodeURIComponent(shift);

    window.open(pdfUrl, '_blank');

    });

    $('#nursingCommentsForm').on('submit', function (e) {
        e.preventDefault();

        let participantId = $('#participant_id').val();
        let shift = $('#selected_shift').val();
        let comments = $('#comments').val();

        if (!participantId) {
            showMessage('Please select a member first.', 'warning');
            return;
        }

        if (comments.length > 5000) {
            showMessage('Comments cannot be more than 5000 characters.', 'warning');
            return;
        }

        $('#saveCommentsBtn').prop('disabled', true).text('Saving...');

        $.ajax({
            url: "{{ route('nursing.comments.save') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                participant_id: participantId,
                shift: shift,
                comments: comments
            },
            success: function (response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                }
            },
            error: function (xhr) {
                let message = 'Unable to save comments.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                showMessage(message, 'danger');
            },
            complete: function () {
                $('#saveCommentsBtn').prop('disabled', false).text('Save Comments');
            }
        });
    });

    $('#clearBtn').on('click', function () {
        clearForm();
    });

    $('#comments').on('input', function () {
        updateCharCount();
    });

        /*
    |--------------------------------------------------------------------------
    | Speech Dictation for Nursing Comments
    |--------------------------------------------------------------------------
    */
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        $('#startDictationBtn')
            .prop('disabled', true)
            .removeClass('btn-outline-primary')
            .addClass('btn-secondary')
            .text('Dictation Not Supported');

        $('#dictationStatus')
            .removeClass('text-muted')
            .addClass('text-danger')
            .text('Speech dictation is not supported in this browser. Please use Google Chrome.');
    } else {
        recognition = new SpeechRecognition();

        recognition.lang = 'en-US';
        recognition.continuous = true;
        recognition.interimResults = true;

        $('#startDictationBtn').on('click', function () {
            let existingText = $('#comments').val().trim();

            finalDictationText = existingText.length > 0 ? existingText + ' ' : '';

            try {
                recognition.start();
                isDictating = true;

                $('#startDictationBtn').prop('disabled', true);
                $('#stopDictationBtn').prop('disabled', false);

                $('#dictationStatus')
                    .removeClass('text-muted text-danger')
                    .addClass('text-success')
                    .text('Listening... please speak now.');
            } catch (e) {
                showMessage('Unable to start dictation. Please try again.', 'danger');
            }
        });

        $('#stopDictationBtn').on('click', function () {
            if (recognition && isDictating) {
                recognition.stop();
            }
        });

        recognition.onresult = function (event) {
            let interimText = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                let transcript = event.results[i][0].transcript;

                if (event.results[i].isFinal) {
                    finalDictationText += transcript + ' ';
                } else {
                    interimText += transcript;
                }
            }

            let combinedText = finalDictationText + interimText;

            if (combinedText.length > 5000) {
                combinedText = combinedText.substring(0, 5000);
                recognition.stop();

                showMessage('Dictation stopped because comments reached 5000 characters.', 'warning');
            }

            $('#comments').val(combinedText);
            updateCharCount();
        };

        recognition.onerror = function (event) {
            isDictating = false;

            $('#startDictationBtn').prop('disabled', false);
            $('#stopDictationBtn').prop('disabled', true);

            $('#dictationStatus')
                .removeClass('text-success text-muted')
                .addClass('text-danger')
                .text('Dictation error: ' + event.error);

            showMessage('Dictation error: ' + event.error, 'danger');
        };

        recognition.onend = function () {
            isDictating = false;

            $('#startDictationBtn').prop('disabled', false);
            $('#stopDictationBtn').prop('disabled', true);

            $('#dictationStatus')
                .removeClass('text-success text-danger')
                .addClass('text-muted')
                .text('Dictation is off.');

            updateCharCount();
        };
    }

    function clearForm() {
        if (recognition && isDictating) {
            recognition.stop();
        }

        $('#participant_id').val('').trigger('change');
        $('#comments').val('');
        updateCharCount();

        finalDictationText = '';

        $('#dictationStatus')
            .removeClass('text-success text-danger')
            .addClass('text-muted')
            .text('Dictation is off.');

        $('#memberStatus')
            .addClass('d-none')
            .removeClass('alert-success alert-danger alert-warning alert-info')
            .text('');
    }

    function updateCharCount() {
        let count = $('#comments').val().length;
        $('#charCount').text(count);
    }

    function showMessage(message, type) {
        $('#memberStatus')
            .removeClass('d-none alert-success alert-danger alert-warning alert-info alert-light')
            .addClass('alert-' + type)
            .text(message);
    }

});
</script>

@endpush
