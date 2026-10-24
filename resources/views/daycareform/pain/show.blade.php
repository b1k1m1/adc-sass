@extends('admin.admin_dashboard')
@section('title', 'Pain Assessment - View')

@section('admin')
<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <a href="{{ route('pain.index', ['type'=>$type, 'participant_id'=>$assessment->participant_id]) }}"
         class="btn btn-secondary">&laquo; Back</a>

      <div class="d-flex gap-2">
        <a href="{{ route('pain.edit', ['id'=>$assessment->id, 'type'=>$type]) }}"
           class="btn btn-outline-primary">{{ t("common.edit", "Edit") }}</a>
        <a href="{{ route('pain.pdf', $assessment->id) }}"
           class="btn btn-outline-danger">PDF</a>
      </div>
    </div>

    {{-- Reuse the PDF layout for on-screen view --}}
    @include('daycareform.pain.pdf', ['renderForScreen' => true])

  </div>
</div>
@endsection
