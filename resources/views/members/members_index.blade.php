@extends('admin.admin_dashboard')

@section('title', 'Members')

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
    | Dropdown Menu
    |--------------------------------------------------------------------------
    */

    .module-dropdown-wrapper {
        position: relative;
        width: 100%;
    }

    .module-dropdown-button {
        border: 1px solid #e9ecef;
        text-align: left;
        cursor: pointer;
    }

    .module-dropdown-button::after {
        display: none;
    }

    .module-dropdown-button .mdi-chevron-down {
        font-size: 16px;
        color: #74788d;
    }

    .module-dropdown-wrapper .dropdown-menu {
        font-size: 13px;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    | Desktop = 4 buttons per row
    | Small screen = 1 button per row
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

                        {{-- Shared Tabs --}}
                        @include('admin.partials.tabs_3', ['active' => 'data'])


                        <div class="tab-content pt-4">


                            {{-- =====================================================
                                 DATA ENTRY
                            ====================================================== --}}

                            <div class="tab-pane active"
                                 id="tab-data"
                                 role="tabpanel">

                                <div class="module-section-title">
                                    Member Data Entry
                                </div>


                                <table class="module-menu-table">

                                    <tbody>

                                        <tr>


                                            {{-- Applicant Initial Assessment --}}

                                            <td>

                                                <a href="{{ route('applicants.index') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-clipboard-list"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Applicant Initial Assessment
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Add New Member --}}

                                            <td>

                                                <a href="{{ route('add.member') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-user-plus"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Add New Member
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Edit Member --}}

                                            <td>

                                                <a href="{{ route('all.members') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-user-edit"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Edit Member
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Import Members from Excel --}}

                                            <td>

                                                <a href="{{ route('members.import.form') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-file-excel"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Import Members from Excel
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

                            <div class="tab-pane"
                                 id="tab-reports"
                                 role="tabpanel">

                                <div class="module-section-title">
                                    Member Reports
                                </div>


                                <table class="module-menu-table">

                                    <tbody>

                                        <tr>


                                            {{-- Members List --}}

                                            <td>

                                                <a href="{{ route('all.member.list') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-users"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Members List
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Dietary Restriction Report --}}

                                            <td>

                                                <a href="{{ route('members.reports.dietary-restrictions') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-utensils"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Dietary Restriction Report
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Allergy Report --}}

                                            <td>

                                                <a href="{{ route('members.reports.allergy') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">
                                                        <i class="fas fa-allergies"></i>
                                                    </div>

                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Allergy Report
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Historical Member Reports --}}

                                            <td>

                                                <div class="dropdown module-dropdown-wrapper">

                                                    <button type="button"
                                                            class="module-menu-item module-dropdown-button dropdown-toggle"
                                                            data-bs-toggle="dropdown"
                                                            aria-expanded="false">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-history"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Historical Member Reports
                                                            </div>

                                                        </div>

                                                        <i class="mdi mdi-chevron-down ms-2"></i>

                                                    </button>


                                                    <div class="dropdown-menu w-100">

                                                        <a class="dropdown-item"
                                                           href="{{ route('attendance.history') }}">

                                                            Attendance History

                                                        </a>


                                                        <a class="dropdown-item"
                                                           href="{{ route('vitals.history') }}">

                                                            Vitals History

                                                        </a>


                                                        <a class="dropdown-item"
                                                           href="{{ route('adl.history') }}">

                                                            ADL History

                                                        </a>

                                                    </div>

                                                </div>

                                            </td>


                                        </tr>

                                    </tbody>

                                </table>

                            </div>



                            {{-- =====================================================
                                 SETTINGS
                            ====================================================== --}}

                            <div class="tab-pane"
                                 id="tab-settings"
                                 role="tabpanel">

                                <div class="module-section-title">
                                    Member Settings
                                </div>


                                <table class="module-menu-table">

                                    <tbody>

                                        <tr>


                                            {{-- Dropdown Setup --}}

                                            <td>

                                                <div class="dropdown module-dropdown-wrapper">

                                                    <button type="button"
                                                            class="module-menu-item module-dropdown-button dropdown-toggle"
                                                            data-bs-toggle="dropdown"
                                                            aria-expanded="false">

                                                        <div class="module-menu-icon">

                                                            <i class="fas fa-list-alt"></i>

                                                        </div>


                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Dropdown Setup
                                                            </div>

                                                        </div>


                                                        <i class="mdi mdi-chevron-down ms-2"></i>

                                                    </button>


                                                    <div class="dropdown-menu w-100">


                                                        <a class="dropdown-item"
                                                           href="{{ route('hmo.index') }}">

                                                            HMO Dropdown

                                                        </a>


                                                        <a class="dropdown-item"
                                                           href="{{ route('frequency.index') }}">

                                                            Frequency Monitor Dropdown

                                                        </a>


                                                        <a class="dropdown-item"
                                                           href="{{ route('member_status.index') }}">

                                                            Members Status Dropdown

                                                        </a>


                                                    </div>

                                                </div>

                                            </td>


                                            {{-- Upload Member Photo --}}

                                            <td>

                                                <a href="{{ route('uploadimage.member') }}"
                                                   class="module-menu-item">

                                                    <div class="module-menu-icon">

                                                        <i class="fas fa-camera"></i>

                                                    </div>


                                                    <div class="module-menu-content">

                                                        <div class="module-menu-title">
                                                            Upload Member Photo
                                                        </div>

                                                    </div>

                                                </a>

                                            </td>


                                            {{-- Empty Column --}}

                                            <td></td>


                                            {{-- Empty Column --}}

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