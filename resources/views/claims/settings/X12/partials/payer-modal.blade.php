<div class="modal fade"
     id="payerModal"
     tabindex="-1"
     aria-labelledby="payerModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <form id="payerForm"
                  method="POST"
                  autocomplete="off">

                @csrf

                <input type="hidden"
                       name="_method"
                       id="payerMethod"
                       value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="payerModalLabel">
                        Add Payer
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-shield-account-outline me-2"></i>
                            Insurance and Payer Identification
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">
                                    Insurance / HMO
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="hmo_id"
                                        class="form-select"
                                        required>
                                    <option value="">Select Insurance / HMO</option>

                                    @foreach($hmos as $hmo)
                                        <option value="{{ $hmo->Id }}">
                                            {{ $hmo->name }} ({{ $hmo->Id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Payer Code
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="payer_code"
                                       class="form-control text-uppercase"
                                       maxlength="30"
                                       required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">
                                    Payer Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="payer_name"
                                       class="form-control"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Payer ID Qualifier
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="payer_identifier_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">
                                    Electronic Payer ID
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="payer_identifier"
                                       class="form-control text-uppercase"
                                       maxlength="80"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Status
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="status"
                                        class="form-select"
                                        required>
                                    <option value="A">Active</option>
                                    <option value="I">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-map-marker-outline me-2"></i>
                            Payer Address
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Address Line 1
                                </label>

                                <input type="text"
                                       name="address1"
                                       class="form-control"
                                       maxlength="100">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Address Line 2
                                </label>

                                <input type="text"
                                       name="address2"
                                       class="form-control"
                                       maxlength="100">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    City
                                </label>

                                <input type="text"
                                       name="city"
                                       class="form-control"
                                       maxlength="50">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">
                                    State
                                </label>

                                <input type="text"
                                       name="state"
                                       class="form-control text-uppercase"
                                       maxlength="2">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    ZIP Code
                                </label>

                                <input type="text"
                                       name="zip_code"
                                       class="form-control"
                                       maxlength="15">
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-file-document-edit-outline me-2"></i>
                            Claim Defaults
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">
                                    Claim Filing Indicator
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="claim_filing_indicator_code"
                                       class="form-control text-uppercase"
                                       maxlength="5"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Subscriber Relationship
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="subscriber_relationship_code"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Place of Service
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="place_of_service_code"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Claim Frequency
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="claim_frequency_code"
                                       class="form-control text-uppercase"
                                       maxlength="1"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Provider Signature
                                </label>

                                <select name="provider_signature_indicator"
                                        class="form-select"
                                        required>
                                    <option value="Y">Y - Yes</option>
                                    <option value="N">N - No</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Assignment Participation
                                </label>

                                <input type="text"
                                       name="assignment_participation_code"
                                       class="form-control text-uppercase"
                                       maxlength="1"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Benefits Assignment
                                </label>

                                <select name="benefits_assignment_indicator"
                                        class="form-select"
                                        required>
                                    <option value="Y">Y - Yes</option>
                                    <option value="N">N - No</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Release Information Code
                                </label>

                                <input type="text"
                                       name="release_information_code"
                                       class="form-control text-uppercase"
                                       maxlength="1"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section mb-0">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-tune-variant me-2"></i>
                            Optional Loop Settings
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="include_payer_address"
                                           id="include_payer_address"
                                           value="1">

                                    <label class="form-check-label"
                                           for="include_payer_address">
                                        Include payer address
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="include_payer_taxonomy_ref"
                                           id="include_payer_taxonomy_ref"
                                           value="1">

                                    <label class="form-check-label"
                                           for="include_payer_taxonomy_ref">
                                        Include payer taxonomy REF
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="include_rendering_provider"
                                           id="include_rendering_provider"
                                           value="1">

                                    <label class="form-check-label"
                                           for="include_rendering_provider">
                                        Include rendering provider
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="include_service_facility"
                                           id="include_service_facility"
                                           value="1">

                                    <label class="form-check-label"
                                           for="include_service_facility">
                                        Include service facility
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
                        Save Payer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    #payerModal {
        overflow: hidden;
    }

    #payerModal .modal-dialog {
        height: calc(100vh - 30px);
        max-height: calc(100vh - 30px);
        margin-top: 15px;
        margin-bottom: 15px;
    }

    #payerModal .modal-content {
        height: 100%;
        max-height: 100%;
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
    }

    #payerForm {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    #payerModal .modal-header {
        flex: 0 0 auto;
        background: #556ee6;
        color: #ffffff;
        border-bottom: 0;
        padding: 16px 20px;
    }

    #payerModal .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.85;
    }

    #payerModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: scroll !important;
        overflow-x: hidden;
        background: #f8fafc;
        padding: 20px;
        scrollbar-gutter: stable;
    }

    #payerModal .modal-footer {
        flex: 0 0 auto;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 14px 20px;
    }

    #payerModal .form-label {
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    #payerModal .form-control,
    #payerModal .form-select {
        min-height: 38px;
        border-color: #dbe2ea;
    }

    #payerModal .form-control:focus,
    #payerModal .form-select:focus {
        border-color: #8ea1ff;
        box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.14);
    }

    #payerModal .form-check {
        min-height: 52px;
        padding: 14px 14px 14px 40px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        background: #f8fafc;
    }

    #payerModal .form-check-input {
        margin-top: 0.18rem;
    }

    #payerModal .form-check-label {
        color: #475569;
        font-size: 13px;
        font-weight: 500;
    }

    @media (max-width: 767.98px) {
        #payerModal .modal-dialog {
            width: auto;
            height: calc(100vh - 20px);
            max-height: calc(100vh - 20px);
            margin: 10px;
        }

        #payerModal .modal-body {
            padding: 14px;
        }
    }
</style>
@endpush
