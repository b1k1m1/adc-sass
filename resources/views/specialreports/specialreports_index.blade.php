@extends('admin.admin_dashboard')

@section('title', 'Special Reports')

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
                        Special Reports
                    </div>

                    <div class="claims-page-subtitle">
                        Claims send /processed or rejected and ERAs reporting.
                    </div>

                </div>

            </div>



            {{-- ============================================================
             CLAIM  & ERA Processed
             ============================================================ --}}

            <div class="claims-section">

                <button class="claims-section-header" type="button" data-bs-toggle="collapse"
                    data-bs-target="#claimEntrySection" aria-expanded="true">

                    <i class="fas fa-chevron-right claims-section-arrow"></i>

                    <span class="claims-section-heading">
                        Claims & ERA Processed Report
                    </span>

                    <span class="claims-section-description">
                        View claims submitted processed or rejected and ERA proccessed
                    </span>

                </button>


                <div id="claimEntrySection" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Claims & ERA information --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('reports.submitted.claims.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-user"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Submitted Claims Reports,
                                        </div>

                                        <div class="claims-menu-description">
                                            Submitted claims information
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Manual Claims --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('reports.processed.era.index') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-file-medical"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Processed ERA Reports
                                        </div>

                                        <div class="claims-menu-description">
                                            processed ERA information
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
                        Claims rejections before submitting to Insurance and ERA exceptions from Insurance
                    </span>

                    <span class="claims-section-description">
                        Rejected, pending and previously submitted claims
                    </span>

                </button>


                <div id="resubmissionSection" class="collapse show">

                    <div class="claims-section-body">

                        <div class="row">


                            {{-- Rejections --}}

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('specialreports.pending.rebill') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            Claim Rejections
                                        </div>

                                        <div class="claims-menu-description">
                                            Review rejected claims
                                        </div>

                                    </div>

                                </a>

                            </div>

                            <div class="col-lg-6 claims-menu-wrapper">

                                <a href="{{ route('era.denied.claims.report') }}" class="claims-menu-item">

                                    <div class="claims-menu-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>

                                    <div>

                                        <div class="claims-menu-title">
                                            ERA Rejections
                                        </div>

                                        <div class="claims-menu-description">
                                            Review rejected claims from Insurance
                                        </div>

                                    </div>

                                </a>

                            </div>


                            {{-- Pending Rebill

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

                            </div> --}}


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
