@extends('admin.admin_dashboard')

@section('title', 'Claims')

@section('admin')

    <style>
        /*
            |--------------------------------------------------------------------------
            | Compact Claims Menu
            |--------------------------------------------------------------------------
            */

        .claims-menu-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            table-layout: fixed;
        }

        .claims-menu-table td {
            width: 25%;
            vertical-align: top;
            padding: 0;
        }

        .claims-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 50px;
            padding: 7px 10px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #ffffff;
            text-decoration: none !important;
            transition: all 0.2s ease;
            color: inherit;
        }

        .claims-menu-item:hover {
            border-color: #556ee6;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.07);
            transform: translateY(-1px);
            text-decoration: none !important;
        }

        /*
                                            |--------------------------------------------------------------------------
                                            | Menu Icon
                                            |--------------------------------------------------------------------------
                                            */

        .claims-menu-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 6px;
            background: #f3f6ff;

            margin-right: 9px;

            font-size: 16px;
            color: #556ee6;
        }

        /*
                                            |--------------------------------------------------------------------------
                                            | Menu Text
                                            |--------------------------------------------------------------------------
                                            */

        .claims-menu-content {
            flex: 1;
            min-width: 0;
        }

        .claims-menu-title {
            font-size: 13px;
            font-weight: 600;
            color: #343a40;
            margin: 0;
            line-height: 1.25;
        }

        /*
                                            |--------------------------------------------------------------------------
                                            | Section Heading
                                            |--------------------------------------------------------------------------
                                            */

        .claims-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /*
                                            |--------------------------------------------------------------------------
                                            | Responsive
                                            |--------------------------------------------------------------------------
                                            */

        @media (max-width: 768px) {

            .claims-menu-table,
            .claims-menu-table tbody,
            .claims-menu-table tr,
            .claims-menu-table td {
                display: block;
                width: 100%;
            }

            .claims-menu-table {
                border-spacing: 0;
            }

            .claims-menu-table td {
                margin-bottom: 6px;
            }

            .claims-menu-item {
                min-height: 48px;
            }
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">

            <div class="row">

                <div class="col-xl-12">

                    <div class="card">

                        <div class="card-body">


                            {{-- =========================================================
                             TABS
                        ========================================================== --}}

                            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">


                                {{-- Data Entry Tab --}}

                                <li class="nav-item">

                                    <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">

                                        <span class="d-block d-sm-none">
                                            <i class="fas fa-edit"></i>
                                        </span>

                                        <span class="d-none d-sm-block">
                                            Data Entry
                                        </span>

                                    </a>

                                </li>


                                {{-- Reports Tab --}}

                                <li class="nav-item">

                                    <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">

                                        <span class="d-block d-sm-none">
                                            <i class="fas fa-file-alt"></i>
                                        </span>

                                        <span class="d-none d-sm-block">
                                            Reports
                                        </span>

                                    </a>

                                </li>


                                {{-- Settings Tab --}}

                                <li class="nav-item">

                                    <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">

                                        <span class="d-block d-sm-none">
                                            <i class="fas fa-cog"></i>
                                        </span>

                                        <span class="d-none d-sm-block">
                                            Settings
                                        </span>

                                    </a>

                                </li>


                            </ul>



                            {{-- =========================================================
                             TAB CONTENT
                        ========================================================== --}}

                            <div class="tab-content pt-4">


                                {{-- =====================================================
                                 DATA ENTRY
                            ====================================================== --}}

                                <div class="tab-pane active" id="home1" role="tabpanel">


                                    <div class="claims-section-title">
                                        Claims Data Entry & Processing
                                    </div>


                                    <table class="claims-menu-table">

                                        <tbody>


                                            {{-- =================================================
                                             ROW 1
                                        ================================================== --}}

                                            <tr>


                                                {{-- Enter Billing Member --}}

                                                <td>

                                                    <a href="{{ route('billing.members.index') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-user"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Enter Billing Member
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Enter Claims Manually --}}

                                                <td>

                                                    <a href="{{ route('claims.create') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-medical"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Enter Claims Manually
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Generate Claims from Excel Sheet --}}

                                                <td>

                                                    <a href="{{ route('claims.excel.form') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-excel"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Generate Claims from Excel Sheet
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Generate Claims from Attendance --}}

                                                <td>

                                                    <a href="{{ route('claims.attendance.form') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-calendar-check"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Generate Claims from Attendance
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                            </tr>



                                            {{-- =================================================
                                             ROW 2
                                        ================================================== --}}

                                            <tr>


                                                {{-- Generate Claims from Medisoft CSV --}}

                                                <td>

                                                    <a href="{{ route('claims.medisoft.import.form') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-csv"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Generate Claims from Medisoft CSV
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Check Claims --}}

                                                <td>

                                                    <a href="{{ route('claims.draft.index') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-check-circle"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Check Claims
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Import Payments from Medisoft --}}

                                                <td>

                                                    <a href="{{ route('era.medisoft.payments.form') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-money-check-alt"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Import Payments from Medisoft
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Import ERA 835 --}}

                                                <td>

                                                    <a href="{{ route('era.index') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-invoice-dollar"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Import ERA 835
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                            </tr>



                                            {{-- =================================================
                                             ROW 3
                                        ================================================== --}}

                                            <tr>


                                                {{-- Import ERA 835 From Clearing House --}}

                                                <td>

                                                    <a href="{{ route('era.claimmd.index') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-invoice-dollar"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Import ERA 835 From Clearing House
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>
                                                <td>
                                                    <a href="{{ route('claims.special.index') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-invoice-dollar"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Special / Resubmit Claim
                                                            </div>

                                                        </div>

                                                    </a>


                                                </td>

                                                <td>
                                                    <a href="{{ route('claims.pending.rebill.index') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-invoice-dollar"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Pending - Exception / Resubmit Claim
                                                            </div>

                                                        </div>

                                                    </a>


                                                </td>

                                                <td>

                                                    <a href="{{ route('claims.inquiry') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-medical"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Claim Inquiry
                                                            </div>

                                                        </div>

                                                    </a>
                                                </td>


                                            </tr>

                                            <td>

                                                <a href="{{ route('claims.rejections.index') }}"
                                                    class="claims-menu-item">

                                                    <div class="claims-menu-icon">
                                                        <i class="fas fa-file-medical"></i>
                                                    </div>

                                                    <div class="claims-menu-content">

                                                        <div class="claims-menu-title">
                                                            Claim Rejections and Re-submission
                                                        </div>

                                                    </div>

                                                </a>
                                            </td>

                                            <td>

                                                <a href="{{ route('claims.legacy837.import.index') }}"
                                                    class="claims-menu-item">

                                                    <div class="claims-menu-icon">
                                                        <i class="fas fa-file-medical"></i>
                                                    </div>

                                                    <div class="claims-menu-content">

                                                        <div class="claims-menu-title">
                                                            Legacy 837P claims
                                                        </div>

                                                    </div>

                                                </a>
                                            </td>

                                            <td>

                                                <a href="{{ route('era.legacy.payment.import.index') }}"
                                                    class="claims-menu-item">

                                                    <div class="claims-menu-icon">
                                                        <i class="fas fa-file-medical"></i>
                                                    </div>

                                                    <div class="claims-menu-content">

                                                        <div class="claims-menu-title">
                                                            Legacy ERA Payments
                                                        </div>

                                                    </div>

                                                </a>
                                            </td>

                                            <td>

                                                <a href="{{ route('claims.resubmit.batches.index') }}"
                                                    class="claims-menu-item">

                                                    <div class="claims-menu-icon">
                                                        <i class="fas fa-file-medical"></i>
                                                    </div>

                                                    <div class="claims-menu-content">

                                                        <div class="claims-menu-title">
                                                            Re-submit Batch Claims
                                                        </div>

                                                    </div>

                                                </a>
                                            </td>

                                            <tr>
                                                <td>
                                                    <a href="{{ route('claims.corrections.index') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-file-medical"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Correct / Void Claims
                                                            </div>

                                                        </div>

                                                    </a>
                                                </td>

                                            </tr>


                                        </tbody>

                                    </table>


                                </div>



                                {{-- =====================================================
                                 REPORTS
                            ====================================================== --}}

                                <div class="tab-pane" id="profile1" role="tabpanel">


                                    <div class="claims-section-title">
                                        Claims Reports & Electronic Submission
                                    </div>


                                    <table class="claims-menu-table">

                                        <tbody>

                                            <tr>


                                                {{-- Generate 837P - Morning --}}

                                                <td>

                                                    <a href="{{ route('claims.837.form', 'A') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Generate 837P - Morning
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Generate 837P - Noon --}}

                                                <td>

                                                    <a href="{{ route('claims.837.form', 'N') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Generate 837P - Noon
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Sent Claims Batch History --}}

                                                <td>

                                                    <a href="{{ route('claims.batches.index') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-history"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Sent Claims Batch History
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Aging Summary --}}

                                                <td>

                                                    <a href="{{ route('ar.aging.summary') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Aging Summary
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                            </tr>
                                            <tr>
                                                <td>

                                                    <a href="{{ route('era.exceptions') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                ERA Excepation Report
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>

                                                <td>

                                                    <a href="{{ route('claims.resubmission.history') }}"
                                                        class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                Batch Claims Re-submission Report
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>



                                            </tr>

                                        </tbody>

                                    </table>


                                </div>



                                {{-- =====================================================
                                 SETTINGS
                            ====================================================== --}}

                                <div class="tab-pane" id="messages1" role="tabpanel">


                                    <div class="claims-section-title">
                                        Claims Configuration
                                    </div>


                                    <table class="claims-menu-table">

                                        <tbody>

                                            <tr>


                                                {{-- X12 Settings --}}

                                                <td>

                                                    <a href="{{ route('x12.settings.index') }}" class="claims-menu-item">

                                                        <div class="claims-menu-icon">
                                                            <i class="fas fa-cogs"></i>
                                                        </div>

                                                        <div class="claims-menu-content">

                                                            <div class="claims-menu-title">
                                                                X12 Settings
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                <td></td>

                                                <td></td>

                                                <td></td>


                                            </tr>

                                        </tbody>

                                    </table>


                                </div>


                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
