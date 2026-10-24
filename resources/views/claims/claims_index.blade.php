@extends('admin.admin_dashboard')

@section('title', 'Claims')

@section('admin')

    <style>
        /* ============================================================
                   Main Claims Menu
                   ============================================================ */

        .claims-page-title {
            font-size: 22px;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 4px;
        }

        .claims-page-subtitle {
            color: #74788d;
            font-size: 13px;
            margin-bottom: 20px;
        }


        /* ============================================================
                   Search
                   ============================================================ */

        .claims-search-wrapper {
            max-width: 420px;
            position: relative;
        }

        .claims-search-wrapper i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #74788d;
            font-size: 14px;
        }

        .claims-search {
            padding-left: 36px;
            height: 40px;
            border-radius: 6px;
        }


        /* ============================================================
                   Section Container
                   ============================================================ */

        .claims-section {
            border: 1px solid #e3e6ea;
            border-radius: 7px;
            margin-bottom: 14px;
            background: #ffffff;
            overflow: hidden;
        }


        /* ============================================================
                   Section Header
                   ============================================================ */

        .claims-section-header {
            width: 100%;
            border: 0;
            background: #f8f9fa;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            text-align: left;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .claims-section-header:hover {
            background: #f1f3f5;
        }

        .claims-section-header:focus {
            box-shadow: none;
            outline: none;
        }

        .claims-section-arrow {
            width: 22px;
            margin-right: 6px;
            color: #495057;
            font-size: 14px;
            transition: transform .20s ease;
        }

        .claims-section-header[aria-expanded="true"] .claims-section-arrow {
            transform: rotate(90deg);
        }

        .claims-section-heading {
            font-size: 16px;
            font-weight: 600;
            color: #343a40;
            margin: 0;
        }

        .claims-section-description {
            font-size: 12px;
            color: #74788d;
            margin-left: auto;
            padding-left: 20px;
        }


        /* ============================================================
                   Section Body
                   ============================================================ */

        .claims-section-body {
            padding: 8px 18px 13px 18px;
        }


        /* ============================================================
                   Menu Item
                   ============================================================ */

        .claims-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 46px;
            padding: 8px 8px;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none !important;
            color: #343a40;
            transition: all .15s ease;
        }

        .claims-menu-item:hover {
            background: #f8f9ff;
            color: #556ee6;
            padding-left: 12px;
            text-decoration: none !important;
        }

        .claims-menu-item:last-child {
            border-bottom: 0;
        }


        /* ============================================================
                   Menu Icon
                   ============================================================ */

        .claims-menu-icon {
            width: 35px;
            height: 35px;
            min-width: 35px;
            border-radius: 6px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f3f6ff;
            color: #556ee6;

            font-size: 15px;
            margin-right: 11px;
        }


        /* ============================================================
                   Menu Text
                   ============================================================ */

        .claims-menu-title {
            font-size: 13px;
            font-weight: 600;
            color: #343a40;
            line-height: 1.25;
        }

        .claims-menu-item:hover .claims-menu-title {
            color: #556ee6;
        }

        .claims-menu-description {
            font-size: 11px;
            color: #74788d;
            margin-top: 2px;
            line-height: 1.2;
        }


        /* ============================================================
                   Favorites
                   ============================================================ */

        .claims-favorites {
            border-left: 4px solid #556ee6;
        }

        .favorite-star {
            margin-left: auto;
            color: #f1b44c;
            font-size: 15px;
        }


        /* ============================================================
                   Utility / Legacy Section
                   ============================================================ */

        .legacy-section .claims-section-header {
            background: #fffaf0;
        }


        /* ============================================================
                   Configuration Section
                   ============================================================ */

        .settings-section .claims-section-header {
            background: #f4f8fb;
        }


        /* ============================================================
                   Responsive
                   ============================================================ */

        @media (max-width: 767px) {

            .claims-section-description {
                display: none;
            }

            .claims-page-title {
                font-size: 19px;
            }

            .claims-menu-description {
                display: none;
            }

        }
    </style>


    <div class="page-content">

        <div class="container-fluid">


            {{-- ============================================================
             Page Header
             ============================================================ --}}

            <div class="row mb-3">

                <div class="col-md-7">

                    <div class="claims-page-title">
                        Claims
                    </div>

                    <div class="claims-page-subtitle">
                        Claims processing, electronic submission, ERA, resubmissions and reporting.
                    </div>

                </div>


                <div class="col-md-5">

                    <div class="claims-search-wrapper ms-md-auto">

                        <i class="fas fa-search"></i>

                        <input type="text" id="claimsMenuSearch" class="form-control claims-search"
                            placeholder="Search claims menu...">

                    </div>

                </div>

            </div>



            {{-- ============================================================
             FAVORITES
             ============================================================ --}}

            <div class="claims-section claims-favorites">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#claimsFavorites" aria-expanded="true">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Favorites
                    </span>

                    <span class="claims-section-description">
                        Frequently used claims functions
                    </span>

                </button>


                <div id="claimsFavorites" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Check Claims --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.draft.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Check Claims
                                        </div>

                                        <div class="claims-menu-description">
                                            Review and validate claims before submission
                                        </div>

                                    </div>

                                    <i class="fas fa-star favorite-star"></i>

                                </a>

                            </div>


                            {{-- Claim Inquiry --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.inquiry') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-search"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Claim Inquiry
                                        </div>

                                        <div class="claims-menu-description">
                                            Search and review existing claims
                                        </div>

                                    </div>

                                    <i class="fas fa-star favorite-star"></i>

                                </a>

                            </div>


                            {{-- Claim.MD ERA --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.claimmd.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-cloud-download-alt"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Import ERA 835 From Clearing House
                                        </div>

                                        <div class="claims-menu-description">
                                            Retrieve ERA files from Claim.MD
                                        </div>

                                    </div>

                                    <i class="fas fa-star favorite-star"></i>

                                </a>

                            </div>


                            {{-- Correct / Void --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.corrections.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-edit"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Correct / Void Claims
                                        </div>

                                        <div class="claims-menu-description">
                                            Submit replacement or void transactions
                                        </div>

                                    </div>

                                    <i class="fas fa-star favorite-star"></i>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>



            {{-- ============================================================
             CLAIM ENTRY & GENERATION
             ============================================================ --}}

            <div class="claims-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#claimEntrySection" aria-expanded="true">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Claim Entry & Generation
                    </span>

                    <span class="claims-section-description">
                        Create and prepare claims
                    </span>

                </button>


                <div id="claimEntrySection" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Billing Members --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('billing.members.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-user"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Enter Billing Member
                                        </div>

                                        <div class="claims-menu-description">
                                            Maintain member billing information
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Manual Claims --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.create') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-file-medical"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Enter Claims Manually
                                        </div>

                                        <div class="claims-menu-description">
                                            Create an individual claim manually
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Excel --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.excel.form') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-file-excel"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Generate Claims from Excel Sheet
                                        </div>

                                        <div class="claims-menu-description">
                                            Import and generate claims from ADC Excel data
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Attendance --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.attendance.form') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Generate Claims from Attendance
                                        </div>

                                        <div class="claims-menu-description">
                                            Generate claims using ADC attendance records
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Medisoft --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.medisoft.import.form') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-file-csv"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Generate Claims from Medisoft CSV
                                        </div>

                                        <div class="claims-menu-description">
                                            Import claim data exported from Medisoft
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Check Claims --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.draft.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Check Claims
                                        </div>

                                        <div class="claims-menu-description">
                                            Review draft and validated claims
                                        </div>

                                    </div>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>



            {{-- ============================================================
             ELECTRONIC SUBMISSION
             ============================================================ --}}

            <div class="claims-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#electronicSubmissionSection" aria-expanded="true">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Electronic Submission
                    </span>

                    <span class="claims-section-description">
                        Generate 837P files and review submitted batches
                    </span>

                </button>


                <div id="electronicSubmissionSection" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Morning --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.837.form', 'A') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Generate 837P - Morning
                                        </div>

                                        <div class="claims-menu-description">
                                            Generate electronic claims for Morning shift
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Noon --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.837.form', 'N') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Generate 837P - Noon
                                        </div>

                                        <div class="claims-menu-description">
                                            Generate electronic claims for Noon shift
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Batch History --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.batches.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-history"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Sent Claims Batch History
                                        </div>

                                        <div class="claims-menu-description">
                                            View previously generated 837P batches
                                        </div>

                                    </div>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>



            {{-- ============================================================
             REJECTIONS & RESUBMISSIONS
             ============================================================ --}}

            <div class="claims-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#resubmissionSection" aria-expanded="true">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Rejections & Resubmissions
                    </span>

                    <span class="claims-section-description">
                        Correct rejected, pending and previously submitted claims
                    </span>

                </button>


                <div id="resubmissionSection" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Rejections --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.rejections.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Claim Rejections and Re-submission
                                        </div>

                                        <div class="claims-menu-description">
                                            Review rejected claims and prepare resubmission
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Pending Rebill --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.pending.rebill.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Pending - Exception / Resubmit Claim
                                        </div>

                                        <div class="claims-menu-description">
                                            Reprocess pending or exception claims
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Resubmit Batch --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.resubmit.batches.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-redo-alt"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Re-submit Batch Claims
                                        </div>

                                        <div class="claims-menu-description">
                                            Select claims from an earlier batch for resubmission
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Special --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.special.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-file-medical-alt"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Special / Resubmit Claim
                                        </div>

                                        <div class="claims-menu-description">
                                            Prepare special or manually adjusted claims
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Correct Void --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.corrections.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-edit"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Correct / Void Claims
                                        </div>

                                        <div class="claims-menu-description">
                                            Submit corrected replacement or void transactions
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- History --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.resubmission.history') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-history"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Batch Claims Re-submission Report
                                        </div>

                                        <div class="claims-menu-description">
                                            Review completed claim resubmissions
                                        </div>

                                    </div>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>



            {{-- ============================================================
             ERA & PAYMENTS
             ============================================================ --}}

            <div class="claims-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#eraPaymentsSection" aria-expanded="true">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        ERA & Payments
                    </span>

                    <span class="claims-section-description">
                        ERA 835 files, payment imports and exceptions
                    </span>

                </button>


                <div id="eraPaymentsSection" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Claim.MD ERA --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.claimmd.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-cloud-download-alt"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Import ERA 835 From Clearing House
                                        </div>

                                        <div class="claims-menu-description">
                                            Download and process ERA files from Claim.MD
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Manual ERA --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Import ERA 835
                                        </div>

                                        <div class="claims-menu-description">
                                            Manually upload and process an ERA 835 file
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Medisoft Payments --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.medisoft.payments.form') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-money-check-alt"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Import Payments from Medisoft
                                        </div>

                                        <div class="claims-menu-description">
                                            Import payment information from Medisoft
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- ERA Exception --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.exceptions') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            ERA Exception Report
                                        </div>

                                        <div class="claims-menu-description">
                                            Review unmatched or exception ERA transactions
                                        </div>

                                    </div>

                                </a>

                            </div>

                            {{-- ERA Exception and resolutions --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.denial.exceptions') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            ERA Denial Exception Report
                                        </div>

                                        <div class="claims-menu-description">
                                            Review denial exception ERA transactions and resolve it
                                        </div>

                                    </div>

                                </a>

                            </div>

                            {{-- ERA Exception and resolutions --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.denial.resolution') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Resolve ERA Denials
                                        </div>

                                        <div class="claims-menu-description">
                                            Review denial and new ERA transactions and resolve it.
                                        </div>

                                    </div>

                                </a>

                            </div>



                        </div>

                    </div>

                </div>

            </div>



            {{-- ============================================================
             CLAIMS INQUIRY & ACCOUNTS RECEIVABLE
             ============================================================ --}}

            <div class="claims-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#claimsInquirySection" aria-expanded="true">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Claims Inquiry & Accounts Receivable
                    </span>

                    <span class="claims-section-description">
                        Inquiry, balances and aging
                    </span>

                </button>


                <div id="claimsInquirySection" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Inquiry --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.inquiry') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-search"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Claim Inquiry
                                        </div>

                                        <div class="claims-menu-description">
                                            Search claims and review claim details
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Aging --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('ar.aging.summary') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Aging Summary
                                        </div>

                                        <div class="claims-menu-description">
                                            Review accounts receivable aging
                                        </div>

                                    </div>

                                </a>

                            </div>

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.member.statement') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Member's Statement / Ledger
                                        </div>

                                        <div class="claims-menu-description">
                                            View member's claims and payments statement / Ledger
                                        </div>

                                    </div>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>



            {{-- ============================================================
             LEGACY / RECOVERY
             ============================================================ --}}

            <div class="claims-section legacy-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#legacySection" aria-expanded="false">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Legacy / Recovery Tools
                    </span>

                    <span class="claims-section-description">
                        Older claim and payment recovery utilities
                    </span>

                </button>


                <div id="legacySection" class="collapse">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Legacy Claims --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('claims.legacy837.import.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-archive"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Legacy 837P Claims
                                        </div>

                                        <div class="claims-menu-description">
                                            Import and recover legacy claim information
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Legacy ERA --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.legacy.payment.import.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-archive"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Legacy ERA Payments
                                        </div>

                                        <div class="claims-menu-description">
                                            Recover and process legacy ERA payment records
                                        </div>

                                    </div>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>



            {{-- ============================================================
             CONFIGURATION
             ============================================================ --}}

            <div class="claims-section settings-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#configurationSection" aria-expanded="false">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Configuration
                    </span>

                    <span class="claims-section-description">
                        Clearinghouse, provider and payer X12 settings
                    </span>

                </button>


                <div id="configurationSection" class="collapse">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- X12 Settings --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('x12.settings.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-cogs"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            X12 Settings
                                        </div>

                                        <div class="claims-menu-description">
                                            Configure clearinghouse, provider and payer settings
                                        </div>

                                    </div>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const searchBox =
                document.getElementById('claimsMenuSearch');


            if (!searchBox) {
                return;
            }


            searchBox.addEventListener('input', function() {

                const searchText =
                    this.value
                    .toLowerCase()
                    .trim();


                const sections =
                    document.querySelectorAll(
                        '.claims-section'
                    );


                sections.forEach(function(section) {

                    const items =
                        section.querySelectorAll(
                            '.claims-menu-wrapper'
                        );


                    let visibleCount = 0;


                    items.forEach(function(wrapper) {

                        const text =
                            wrapper.innerText
                            .toLowerCase();


                        if (
                            searchText === '' ||
                            text.includes(searchText)
                        ) {

                            wrapper.style.display = '';

                            visibleCount++;

                        } else {

                            wrapper.style.display = 'none';

                        }

                    });


                    /*
                     * When searching:
                     * hide categories with no matching items.
                     *
                     * Favorites are treated the same way.
                     */

                    if (
                        searchText !== '' &&
                        visibleCount === 0
                    ) {

                        section.style.display =
                            'none';

                    } else {

                        section.style.display =
                            '';

                    }


                    /*
                     * Open matching sections automatically.
                     */

                    if (
                        searchText !== '' &&
                        visibleCount > 0
                    ) {

                        const collapse =
                            section.querySelector(
                                '.collapse'
                            );


                        if (
                            collapse &&
                            typeof bootstrap !==
                            'undefined'
                        ) {

                            const instance =
                                bootstrap.Collapse
                                .getOrCreateInstance(
                                    collapse, {
                                        toggle: false
                                    }
                                );


                            instance.show();

                        }

                    }

                });

            });

        });
    </script>

@endsection
