<div class="modal fade"
     id="providerModal"
     tabindex="-1"
     aria-labelledby="providerModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <form id="providerForm"
                  method="POST"
                  autocomplete="off">

                @csrf

                <input type="hidden"
                       name="_method"
                       id="providerMethod"
                       value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="providerModalLabel">
                        Add Provider
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-hospital-building me-2"></i>
                            Provider Identification
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">
                                    Provider Code <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="provider_code"
                                       class="form-control text-uppercase"
                                       maxlength="30"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Provider Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="provider_name"
                                       class="form-control"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Entity Type <span class="text-danger">*</span>
                                </label>

                                <select name="entity_type_qualifier"
                                        class="form-select"
                                        required>
                                    <option value="2">2 - Organization</option>
                                    <option value="1">1 - Person</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    NPI <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="npi"
                                       class="form-control"
                                       maxlength="10"
                                       inputmode="numeric"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Taxonomy Code <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="taxonomy_code"
                                       class="form-control text-uppercase"
                                       maxlength="20"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">
                                    Tax ID Qualifier
                                </label>

                                <input type="text"
                                       name="tax_id_qualifier"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Tax ID <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="tax_id"
                                       class="form-control text-uppercase"
                                       maxlength="20"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-map-marker-outline me-2"></i>
                            Provider Address
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Address Line 1 <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="address1"
                                       class="form-control"
                                       maxlength="100"
                                       required>
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
                                    City <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="city"
                                       class="form-control"
                                       maxlength="50"
                                       required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">
                                    State <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="state"
                                       class="form-control text-uppercase"
                                       maxlength="2"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    ZIP Code <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="zip_code"
                                       class="form-control"
                                       maxlength="15"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Status <span class="text-danger">*</span>
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
                            <i class="mdi mdi-card-account-phone-outline me-2"></i>
                            Contact Information
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">
                                    Contact Name
                                </label>

                                <input type="text"
                                       name="contact_name"
                                       class="form-control"
                                       maxlength="100">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Phone
                                </label>

                                <input type="text"
                                       name="phone"
                                       class="form-control"
                                       maxlength="25">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Fax
                                </label>

                                <input type="text"
                                       name="fax"
                                       class="form-control"
                                       maxlength="25">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="x12-modal-section mb-0">
                        <div class="x12-modal-section-title">
                            <i class="mdi mdi-account-cog-outline me-2"></i>
                            Provider Usage
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="use_as_billing_provider"
                                           id="use_as_billing_provider"
                                           value="1">

                                    <label class="form-check-label"
                                           for="use_as_billing_provider">
                                        Use as billing provider
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="use_as_rendering_provider"
                                           id="use_as_rendering_provider"
                                           value="1">

                                    <label class="form-check-label"
                                           for="use_as_rendering_provider">
                                        Use as rendering provider
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="use_as_service_facility"
                                           id="use_as_service_facility"
                                           value="1">

                                    <label class="form-check-label"
                                           for="use_as_service_facility">
                                        Use as service facility
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_default"
                                           id="provider_is_default"
                                           value="1">

                                    <label class="form-check-label"
                                           for="provider_is_default">
                                        Default provider
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
                        Save Provider
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    #providerModal {
        overflow: hidden;
    }

    #providerModal .modal-dialog {
        height: calc(100vh - 30px);
        max-height: calc(100vh - 30px);
        margin-top: 15px;
        margin-bottom: 15px;
    }

    #providerModal .modal-content {
        height: 100%;
        max-height: 100%;
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
    }

    #providerForm {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    #providerModal .modal-header {
        flex: 0 0 auto;
        background: #556ee6;
        color: #ffffff;
        border-bottom: 0;
        padding: 16px 20px;
    }

    #providerModal .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.85;
    }

    #providerModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: scroll !important;
        overflow-x: hidden;
        background: #f8fafc;
        padding: 20px;
        scrollbar-gutter: stable;
    }

    #providerModal .modal-footer {
        flex: 0 0 auto;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 14px 20px;
    }

    #providerModal .form-label {
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    #providerModal .form-control,
    #providerModal .form-select {
        min-height: 38px;
        border-color: #dbe2ea;
    }

    #providerModal .form-control:focus,
    #providerModal .form-select:focus {
        border-color: #8ea1ff;
        box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.14);
    }

    #providerModal .form-check {
        padding: 13px 14px 13px 40px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        background: #f8fafc;
    }

    #providerModal .form-check-input {
        margin-top: 0.17rem;
    }

    #providerModal .form-check-label {
        color: #475569;
        font-size: 13px;
        font-weight: 500;
    }

    @media (max-width: 767.98px) {
        #providerModal .modal-dialog {
            width: auto;
            height: calc(100vh - 20px);
            max-height: calc(100vh - 20px);
            margin: 10px;
        }

        #providerModal .modal-body {
            padding: 14px;
        }
    }
</style>
@endpush
