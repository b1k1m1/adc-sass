@extends('admin.admin_dashboard')

@section('title', 'Activity')

@section('admin')

<style>

    /*
    |--------------------------------------------------------------------------
    | Compact Module Menu
    |--------------------------------------------------------------------------
    */

    .module-menu-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px;
        table-layout: fixed;
    }

    .module-menu-table td {
        width: 25%;
        vertical-align: top;
        padding: 0;
    }

    .module-menu-item {
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

    .module-menu-item:hover {
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

    .module-menu-icon {
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

    .module-menu-content {
        flex: 1;
        min-width: 0;
    }

    .module-menu-title {
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

    .module-section-title {
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

        .module-menu-table,
        .module-menu-table tbody,
        .module-menu-table tr,
        .module-menu-table td {
            display: block;
            width: 100%;
        }

        .module-menu-table {
            border-spacing: 0;
        }

        .module-menu-table td {
            margin-bottom: 6px;
        }

        .module-menu-item {
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

                        <ul class="nav nav-tabs nav-tabs-custom nav-justified"
                            role="tablist">


                            {{-- Data Entry Tab --}}

                            <li class="nav-item">

                                <a class="nav-link active"
                                   data-bs-toggle="tab"
                                   href="#home1"
                                   role="tab">

                                    <span class="d-block d-sm-none">
                                        <i class="fas fa-running"></i>
                                    </span>

                                    <span class="d-none d-sm-block">
                                        Data Entry
                                    </span>

                                </a>

                            </li>


                            {{-- Reports Tab --}}

                            <li class="nav-item">

                                <a class="nav-link"
                                   data-bs-toggle="tab"
                                   href="#profile1"
                                   role="tab">

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

                                <a class="nav-link"
                                   data-bs-toggle="tab"
                                   href="#messages1"
                                   role="tab">

                                    <span class="d-block d-sm-none">
                                        <i class="fas fa-cog"></i>
                                    </span>

                                    <span class="d-none d-sm-block">
                                        Settings
                                    </span>

                                </a>

                            </li>


                        </ul>



                        <div class="tab-content pt-4">


                            {{-- =====================================================
                                 DATA ENTRY
                            ====================================================== --}}

                            <div class="tab-pane active"
                                 id="home1"
                                 role="tabpanel">


                                <div class="module-section-title">
                                    Activity Assessments
                                </div>


                                <table class="module-menu-table">

                                    <tbody>

                                        <tr>


                                            {{-- Initial Client Activity Assessment --}}

                                            <td>

                                                <a href="{{ route('participant.activity.assessment.index') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-clipboard-list"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Initial Client Activity Assessment
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Activity Assessment --}}

                                            <td>

                                                <a href="{{ route('paa.form') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-tasks"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Activity Assessment
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            <td></td>

                                            <td></td>


                                        </tr>

                                    </tbody>

                                </table>


                            </div>



                            {{-- =====================================================
                                 REPORTS
                            ====================================================== --}}

                            <div class="tab-pane"
                                 id="profile1"
                                 role="tabpanel">


                                <div class="module-section-title">
                                    Activity Reports
                                </div>


                                <table class="module-menu-table">

                                    <tbody>

                                        <tr>


                                            {{-- Activity Assessment Report --}}

                                            <td>

                                                <a href="{{ route('paa.report.view') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-file-alt"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Activity Assessment Report
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



                            {{-- =====================================================
                                 SETTINGS
                            ====================================================== --}}

                            <div class="tab-pane"
                                 id="messages1"
                                 role="tabpanel">


                                <div class="module-section-title">
                                    Activity Settings
                                </div>


                                <div class="text-muted">
                                    No activity settings are currently configured.
                                </div>


                            </div>


                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection