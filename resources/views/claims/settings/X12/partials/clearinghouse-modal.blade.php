<div class="modal fade"
     id="clearinghouseModal"
     tabindex="-1"
     aria-labelledby="clearinghouseModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <form id="clearinghouseForm"
                  method="POST"
                  autocomplete="off">

                @csrf

                <input type="hidden"
                       name="_method"
                       id="clearinghouseMethod"
                       value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="clearinghouseModalLabel">
                        Add Clearinghouse
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-office-building-cog-outline me-2"></i>
                            General Information
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Setting Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="setting_name"
                                       class="form-control"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Setting Code <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="setting_code"
                                       class="form-control text-uppercase"
                                       maxlength="30"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Status <span class="text-danger">*</span>
                                </label>

                                <select name="status" class="form-select" required>
                                    <option value="A">Active</option>
                                    <option value="I">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-file-code-outline me-2"></i>
                            ISA Interchange Settings
                        </div>

                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">ISA01 Auth. Qualifier</label>
                                <input type="text"
                                       name="isa_authorization_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ISA02 Auth. Information</label>
                                <input type="text"
                                       name="isa_authorization_information"
                                       class="form-control"
                                       maxlength="10">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ISA03 Security Qualifier</label>
                                <input type="text"
                                       name="isa_security_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ISA04 Security Information</label>
                                <input type="text"
                                       name="isa_security_information"
                                       class="form-control text-uppercase"
                                       maxlength="10">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ISA05 Sender Qualifier</label>
                                <input type="text"
                                       name="isa_sender_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    ISA06 Sender ID <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="isa_sender_id"
                                       class="form-control text-uppercase"
                                       maxlength="15"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ISA07 Receiver Qualifier</label>
                                <input type="text"
                                       name="isa_receiver_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    ISA08 Receiver ID <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="isa_receiver_id"
                                       class="form-control text-uppercase"
                                       maxlength="15"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ISA11 Repetition Separator</label>
                                <input type="text"
                                       name="isa_repetition_separator"
                                       class="form-control text-center"
                                       maxlength="1"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ISA12 Version</label>
                                <input type="text"
                                       name="isa_version"
                                       class="form-control text-uppercase"
                                       maxlength="5"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ISA14 Ack Requested</label>
                                <select name="isa_ack_requested"
                                        class="form-select"
                                        required>
                                    <option value="0">0 - No</option>
                                    <option value="1">1 - Yes</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">ISA15 Usage Indicator</label>
                                <select name="isa_usage_indicator"
                                        class="form-select"
                                        required>
                                    <option value="T">T - Test</option>
                                    <option value="P">P - Production</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">ISA16 Component Separator</label>
                                <input type="text"
                                       name="isa_component_separator"
                                       class="form-control text-center"
                                       maxlength="1"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-layers-triple-outline me-2"></i>
                            GS Functional Group and BHT Settings
                        </div>

                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">GS01 Functional Code</label>
                                <input type="text"
                                       name="gs_functional_code"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    GS02 Sender Code <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="gs_sender_code"
                                       class="form-control text-uppercase"
                                       maxlength="15"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    GS03 Receiver Code <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="gs_receiver_code"
                                       class="form-control text-uppercase"
                                       maxlength="15"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">GS07 Agency Code</label>
                                <input type="text"
                                       name="gs_responsible_agency_code"
                                       class="form-control text-uppercase"
                                       maxlength="1"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">GS08 Version</label>
                                <input type="text"
                                       name="implementation_version"
                                       class="form-control text-uppercase"
                                       maxlength="20"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">BHT01 Structure Code</label>
                                <input type="text"
                                       name="bht_structure_code"
                                       class="form-control text-uppercase"
                                       maxlength="4"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">BHT02 Purpose Code</label>
                                <input type="text"
                                       name="bht_purpose_code"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">BHT06 Transaction Type</label>
                                <input type="text"
                                       name="bht_transaction_type"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-account-tie-outline me-2"></i>
                            Submitter Information — Loop 1000A
                        </div>

                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">
                                    Submitter Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="submitter_name"
                                       class="form-control"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Entity Type</label>
                                <select name="submitter_entity_type"
                                        class="form-select"
                                        required>
                                    <option value="2">2 - Organization</option>
                                    <option value="1">1 - Person</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ID Qualifier</label>
                                <input type="text"
                                       name="submitter_id_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Submitter ID <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="submitter_id"
                                       class="form-control text-uppercase"
                                       maxlength="80"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Contact Name</label>
                                <input type="text"
                                       name="submitter_contact_name"
                                       class="form-control"
                                       maxlength="100">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Phone</label>
                                <input type="text"
                                       name="submitter_phone"
                                       class="form-control"
                                       maxlength="25">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email"
                                       name="submitter_email"
                                       class="form-control"
                                       maxlength="100">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Fax</label>
                                <input type="text"
                                       name="submitter_fax"
                                       class="form-control"
                                       maxlength="25">
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-bank-transfer-in me-2"></i>
                            Receiver Information — Loop 1000B
                        </div>

                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">
                                    Receiver Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="receiver_name"
                                       class="form-control"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Entity Type</label>
                                <select name="receiver_entity_type"
                                        class="form-select"
                                        required>
                                    <option value="2">2 - Organization</option>
                                    <option value="1">1 - Person</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ID Qualifier</label>
                                <input type="text"
                                       name="receiver_id_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Receiver Transaction ID
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="receiver_transaction_id"
                                       class="form-control text-uppercase"
                                       maxlength="80"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section mb-0">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-format-list-bulleted-type me-2"></i>
                            File Formatting
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label">Element Separator</label>
                                <input type="text"
                                       name="element_separator"
                                       class="form-control text-center"
                                       maxlength="1"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Segment Terminator</label>
                                <input type="text"
                                       name="segment_terminator"
                                       class="form-control text-center"
                                       maxlength="1"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="line_break_after_segment"
                                           id="line_break_after_segment"
                                           value="1">

                                    <label class="form-check-label"
                                           for="line_break_after_segment">
                                        Add a line break after each X12 segment
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_default"
                                           id="clearinghouse_is_default"
                                           value="1">

                                    <label class="form-check-label"
                                           for="clearinghouse_is_default">
                                        Use as the default clearinghouse profile
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline me-1"></i>
                        Save Clearinghouse
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    #clearinghouseModal {
        overflow: hidden;
    }

    #clearinghouseModal .modal-dialog {
        height: calc(100vh - 30px);
        max-height: calc(100vh - 30px);
        margin-top: 15px;
        margin-bottom: 15px;
    }

    #clearinghouseModal .modal-content {
        height: 100%;
        max-height: 100%;
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
    }

    /*
    |--------------------------------------------------------------------------
    | Important
    |--------------------------------------------------------------------------
    | The form wraps the modal header, body and footer, so it must also be
    | configured as a flex container.
    */

    #clearinghouseForm {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    #clearinghouseModal .modal-header {
        flex: 0 0 auto;
        background: #556ee6;
        color: #ffffff;
        border-bottom: 0;
        padding: 16px 20px;
    }

    #clearinghouseModal .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.85;
    }

    #clearinghouseModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: scroll !important;
        overflow-x: hidden;
        background: #f8fafc;
        padding: 20px;
        scrollbar-gutter: stable;
    }

    #clearinghouseModal .modal-footer {
        flex: 0 0 auto;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 14px 20px;
    }

    .x12-modal-section {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .x12-modal-section-title {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #edf0f4;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
    }

    .x12-modal-section-title i {
        color: #556ee6;
        font-size: 18px;
    }

    #clearinghouseModal .form-label {
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    #clearinghouseModal .form-control,
    #clearinghouseModal .form-select {
        min-height: 38px;
        border-color: #dbe2ea;
    }

    #clearinghouseModal .form-control:focus,
    #clearinghouseModal .form-select:focus {
        border-color: #8ea1ff;
        box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.14);
    }

    @media (max-width: 767.98px) {
        #clearinghouseModal .modal-dialog {
            width: auto;
            height: calc(100vh - 20px);
            max-height: calc(100vh - 20px);
            margin: 10px;
        }

        #clearinghouseModal .modal-body {
            padding: 14px;
        }

        .x12-modal-section {
            padding: 14px;
        }
    }
</style>
@endpush
