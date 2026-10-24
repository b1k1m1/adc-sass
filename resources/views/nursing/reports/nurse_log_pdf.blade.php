<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Nurse Log - {{ $shift === 'N' ? 'Noon Shift' : 'Morning Shift' }}
    </title>


    <style>

        @page {
            margin: 8mm 8mm 12mm 8mm;
        }

        body {
            margin: 0;
            padding: 0;

            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            font-weight: bold;

            color: #000;
        }


        /*
        |--------------------------------------------------------------------------
        | Report Page
        |--------------------------------------------------------------------------
        */

        .report-page {
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | Report Header
        |--------------------------------------------------------------------------
        */

        .report-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .report-header td {
            border: none;
            padding: 1px 3px;
            vertical-align: top;
        }


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        .company-section {
            width: 38%;
            font-size: 9px;
            line-height: 1.35;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }


        /*
        |--------------------------------------------------------------------------
        | Report Title
        |--------------------------------------------------------------------------
        */

        .title-section {
            width: 24%;
            text-align: center;
        }

        .report-title {
            font-size: 15px;
            font-weight: bold;
        }

        .shift-title {
            font-size: 10px;
            font-weight: bold;
            margin-top: 2px;
        }

        .report-date {
            margin-top: 7px;
            font-size: 9px;
        }

        .date-line {
            display: inline-block;
            width: 100px;
            border-bottom: 1px solid #000;
            margin-left: 4px;
        }


        /*
        |--------------------------------------------------------------------------
        | Phone/Fax
        |--------------------------------------------------------------------------
        */

        .contact-section {
            width: 38%;
            text-align: right;
            font-size: 9px;
            line-height: 1.4;
        }


        /*
        |--------------------------------------------------------------------------
        | Nurse Log Table
        |--------------------------------------------------------------------------
        */

        .nurse-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .nurse-table th,
        .nurse-table td {
            border: 1px solid #000;
            vertical-align: middle;
        }

        .nurse-table th {
            font-size: 7.3px;
            font-weight: bold;
            text-align: center;
            line-height: 1.15;
            padding: 3px 1px;
        }

        .nurse-table td {
            font-size: 10px;
            height: 29px;
            padding: 3px 2px;
        }


        /*
        |--------------------------------------------------------------------------
        | Column Widths
        |--------------------------------------------------------------------------
        */

        .col-nr {
            width: 3%;
            text-align: center;
        }

        .col-id {
            width: 4.5%;
            text-align: center;
        }

        .col-last {
            width: 9%;
        }

        .col-first {
            width: 8%;
        }

        .col-mi {
            width: 2.5%;
            text-align: center;
        }

        .col-t {
            width: 3%;
            text-align: center;
        }

        .col-p {
            width: 3%;
            text-align: center;
        }

        .col-r {
            width: 3%;
            text-align: center;
        }

        .col-bp {
            width: 5%;
            text-align: center;
        }

        .col-weight {
            width: 5%;
            text-align: center;
        }

        .col-sugar {
            width: 6%;
            text-align: center;
        }


        /*
        |--------------------------------------------------------------------------
        | New Monitoring Columns
        |--------------------------------------------------------------------------
        */

        .col-monitor-sugar {
            width: 10%;
            text-align: center;
        }

        .col-monitor-bp {
            width: 11%;
            text-align: center;
        }

        .col-monitor-weight {
            width: 9%;
            text-align: center;
        }

        .col-monitor-others {
            width: 18%;
        }

    </style>

</head>


<body>


@php

    /*
    |--------------------------------------------------------------------------
    | Fixed Rows Per Page
    |--------------------------------------------------------------------------
    */

    $rowsPerPage = 22;

    $pages = $members->chunk($rowsPerPage);

    $runningNumber = 1;

@endphp



@foreach($pages as $pageIndex => $pageMembers)


    {{-- ================================================================
         PAGE BREAK BEFORE EVERY PAGE EXCEPT FIRST
         ================================================================ --}}

    @if($pageIndex > 0)

        <pagebreak />

    @endif



    <div class="report-page">


        {{-- ============================================================
             REPORT HEADER
             ============================================================ --}}

        <table class="report-header">

            <tr>


                {{-- Company --}}
                <td class="company-section">

                    <div class="company-name">
                        {{ $company['name'] ?? '' }}
                    </div>


                    @if(!empty($company['address']))

                        <div>
                            {{ $company['address'] }}
                        </div>

                    @endif


                    <div>

                        @if(!empty($company['city']))
                            {{ $company['city'] }}
                        @endif


                        @if(!empty($company['state']))

                            @if(!empty($company['city']))
                                ,
                            @endif

                            {{ $company['state'] }}

                        @endif


                        @if(!empty($company['zip']))
                            {{ $company['zip'] }}
                        @endif

                    </div>

                </td>



                {{-- Report Title --}}
                <td class="title-section">

                    <div class="report-title">
                        Nurse Log
                    </div>


                    <div class="shift-title">

                        {{ $shift === 'N'
                            ? 'Noon Shift'
                            : 'Morning Shift'
                        }}

                    </div>


                    <div class="report-date">

                        <strong>Date:</strong>

                        <span class="date-line">
                            &nbsp;
                        </span>

                    </div>

                </td>



                {{-- Phone / Fax --}}
                <td class="contact-section">

                    @if(!empty($company['phone']))

                        <div>

                            <strong>TEL:</strong>

                            {{ $company['phone'] }}

                        </div>

                    @endif


                    @if(!empty($company['fax']))

                        <div>

                            <strong>FAX:</strong>

                            {{ $company['fax'] }}

                        </div>

                    @endif

                </td>

            </tr>

        </table>



        {{-- ============================================================
             NURSE LOG TABLE
             ============================================================ --}}

        <table class="nurse-table">

            <thead>

                <tr>

                    <th class="col-nr">
                        Nr.
                    </th>

                    <th class="col-id">
                        ID
                    </th>

                    <th class="col-last">
                        Last
                    </th>

                    <th class="col-first">
                        First
                    </th>

                    <th class="col-mi">
                        Mi
                    </th>

                    <th class="col-t">
                        T
                    </th>

                    <th class="col-p">
                        P
                    </th>

                    <th class="col-r">
                        R
                    </th>

                    <th class="col-bp">
                        BP
                    </th>

                    <th class="col-weight">
                        Weight
                    </th>

                    <th class="col-sugar">
                        Blood<br>
                        Sugar
                    </th>


                    {{-- New Monitoring Columns --}}

                    <th class="col-monitor-sugar">
                        Monitoring<br>
                        Blood Sugar
                    </th>

                    <th class="col-monitor-bp">
                        Monitoring<br>
                        Blood Pressure
                    </th>

                    <th class="col-monitor-weight">
                        Monitoring<br>
                        Weight
                    </th>

                    <th class="col-monitor-others">
                        Monitoring<br>
                        Others
                    </th>

                </tr>

            </thead>


            <tbody>


                {{-- ====================================================
                     MEMBER ROWS
                     ==================================================== --}}

                @foreach($pageMembers as $member)

                    <tr>


                        {{-- Nr --}}
                        <td class="col-nr">
                            {{ $runningNumber }}
                        </td>


                        {{-- Participant --}}
                        <td class="col-id">
                            {{ $member->ParticipantId }}
                        </td>


                        {{-- Last --}}
                        <td class="col-last">
                            {{ $member->LastName }}
                        </td>


                        {{-- First --}}
                        <td class="col-first">
                            {{ $member->FirstName }}
                        </td>


                        {{-- MI --}}
                        <td class="col-mi">

                            @if(!empty($member->MiddleName))

                                {{ strtoupper(
                                    substr(
                                        trim($member->MiddleName),
                                        0,
                                        1
                                    )
                                ) }}

                            @endif

                        </td>


                        {{-- Manual Nurse Fields --}}
                        <td class="col-t">
                            &nbsp;
                        </td>

                        <td class="col-p">
                            &nbsp;
                        </td>

                        <td class="col-r">
                            &nbsp;
                        </td>

                        <td class="col-bp">
                            &nbsp;
                        </td>

                        <td class="col-weight">
                            &nbsp;
                        </td>

                        <td class="col-sugar">
                            &nbsp;
                        </td>


                        {{-- Monitoring Blood Sugar --}}
                        <td class="col-monitor-sugar">
                            {{ $member->bloodsugar ?? '' }}
                        </td>


                        {{-- Monitoring Blood Pressure --}}
                        <td class="col-monitor-bp">
                            {{ $member->bloodpressure ?? '' }}
                        </td>


                        {{-- Monitoring Weight --}}
                        <td class="col-monitor-weight">
                            {{ $member->weight ?? '' }}
                        </td>


                        {{-- Monitoring Others --}}
                        <td class="col-monitor-others">
                            {{ $member->others ?? '' }}
                        </td>

                    </tr>


                    @php
                        $runningNumber++;
                    @endphp


                @endforeach



                {{-- ====================================================
                     BLANK ROWS TO KEEP EXACTLY 22 ROWS/PAGE
                     ==================================================== --}}

                @for(
                    $blank = $pageMembers->count();
                    $blank < $rowsPerPage;
                    $blank++
                )

                    <tr>

                        <td class="col-nr">&nbsp;</td>

                        <td class="col-id">&nbsp;</td>

                        <td class="col-last">&nbsp;</td>

                        <td class="col-first">&nbsp;</td>

                        <td class="col-mi">&nbsp;</td>

                        <td class="col-t">&nbsp;</td>

                        <td class="col-p">&nbsp;</td>

                        <td class="col-r">&nbsp;</td>

                        <td class="col-bp">&nbsp;</td>

                        <td class="col-weight">&nbsp;</td>

                        <td class="col-sugar">&nbsp;</td>

                        <td class="col-monitor-sugar">&nbsp;</td>

                        <td class="col-monitor-bp">&nbsp;</td>

                        <td class="col-monitor-weight">&nbsp;</td>

                        <td class="col-monitor-others">&nbsp;</td>

                    </tr>

                @endfor


            </tbody>

        </table>


    </div>


@endforeach


</body>

</html>