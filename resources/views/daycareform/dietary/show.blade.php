@extends('admin.admin_dashboard')
@section('title', 'Dietary Initial Assessment')

@section('admin')

<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
    <a href="{{ route('dietary.index', ['type'=>$type, 'participant_id'=>$assessment->participant_id]) }}"
        class="btn btn-secondary">&laquo; Back</a>

        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-outline-dark">Print</button>

            <a href="{{ route('dietary.pdf', $assessment->id) }}"
            class="btn btn-outline-danger">PDF</a>

            <a href="{{ route('dietary.edit', ['id'=>$assessment->id, 'type'=>$type]) }}"
            class="btn btn-outline-primary">{{ t("common.edit", "Edit") }}</a>
        </div>
    </div>

    {{-- Reuse the PDF layout for on-screen view --}}
    @include('daycareform.dietary.pdf', ['renderForScreen' => true])

  </div>
</div>
@endsection
