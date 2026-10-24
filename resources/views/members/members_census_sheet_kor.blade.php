@extends('admin.admin_dashboard')
@section('admin')
    <div class="page-content">
        <div class="container-fluid">
            <div class="col d-flex gap-2 mb-3 flex-wrap align-items-end">
                <a href="{{ $pdfUrl }}" target="_blank"
                    class="btn btn-secondary">{{ t('report.export_pdf', 'Export to PDF') }}</a>
                <!--
                                        <button onclick="window.print()" class="btn btn-primary">{{ t('common.print', 'Print') }}</button>
                                        -->
                <div class="col-auto ms-auto">
                    <a href="{{ route('drivers.dashboard') }}#profile1" class="btn btn-primary no-print">
                        {{ t('common.back', 'Back') }}
                    </a>
                </div>
            </div>

            <style>
                * {
                    box-sizing: border-box
                }

                body {
                    font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
                    font-size: 11px
                }

                .table-wrap {
                    margin-top: 6px
                }

                table.report {
                    width: 100%;
                    border-collapse: collapse;
                    table-layout: fixed;
                    font-size: 11px
                }

                thead {
                    display: table-header-group
                }

                tfoot {
                    display: table-footer-group
                }

                .report th,
                .report td {
                    border: 1px solid #000;
                    padding: 8px 8px;
                    text-align: center;
                    vertical-align: middle;
                    word-wrap: break-word;
                    min-height: 32px;
                }

                /* Taller rows for signatures */
                .report td {
                    height: 34px;
                }

                .report th {
                    font-weight: 700
                }

                .col-id {
                    width: 5%
                }

                .col-photo {
                    width: 5%
                }

                .col-last {
                    width: 10%
                }

                .col-first {
                    width: 10%
                }

                .col-korean {
                    width: 6%
                }

                .col-city {
                    width: 18%
                }

                .col-sign {
                    width: 19%
                }

                .col-arrival {
                    width: 8%
                }

                .col-departure {
                    width: 10%
                }

                .col-remarks {
                    width: 9%
                }

                .korean-name {
                    text-align: center !important;
                    font-weight: 600;
                    white-space: nowrap;
                }

                .photo {
                    width: 42px;
                    height: 42px;
                    object-fit: cover;
                    border-radius: 50%;
                    border: 1px solid #999;
                }
            </style>

            <div class="table-scroll-x">
                <table class="report">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-photo">Photo</th>
                            <th class="col-last">Last</th>
                            <th class="col-first">First</th>
                            <th class="col-korean">
                                Korean<br>Name
                            </th>
                            <th class="col-city">City (Address1 + City)</th>
                            <th class="col-sign">Signature</th>
                            <th class="col-arrival">Arrival time<br>at center</th>
                            <th class="col-departure">Departure time<br>from center</th>
                            <th class="col-remarks">Remarks</th>

                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rows as $member)
                            @php
                                // ---- ID padded like 005
                                $padded = str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT);

                                $mediaFolder = session('media_folder');

                                if (empty($mediaFolder)) {
                                    abort(403, 'Organization media folder is not configured.');
                                }

                                $numericId = (int) trim($member->ParticipantId);
                                $imageFilename = $numericId . '.jpg';

                                $imagePath = "uploads/{$mediaFolder}/members/images/{$imageFilename}";
                                $fullImagePath = public_path($imagePath);

                                $displayPath = file_exists($fullImagePath)
                                    ? asset($imagePath)
                                    : asset('members/images/no_image.jpg');

                                // ---- City column = address1 + city
                                $cityCol = trim(
                                    ($member->address1 ?? '') .
                                        (empty($member->address1) || empty($member->city) ? '' : ', ') .
                                        ($member->city ?? ''),
                                );
                            @endphp
                            <tr>
                                <td>{{ $padded }}</td>
                                <td>
                                    <img src="{{ $displayPath }}" alt="photo" class="photo">
                                </td>
                                <td class="text-left">{{ $member->last }}</td>
                                <td class="text-left">{{ $member->first }}</td>
                                <td class="korean-name"> {{ $member->comments ?? '' }}</td>
                                <td class="text-left">{{ $cityCol }}</td>
                                <td></td> {{-- Signature space --}}
                                <td></td> {{-- Arrival --}}
                                <td></td> {{-- Departure --}}
                                <td></td> {{-- Remarks --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center;">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>

                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
