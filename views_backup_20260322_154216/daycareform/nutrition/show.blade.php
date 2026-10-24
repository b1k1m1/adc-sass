@extends('admin.admin_dashboard')
@section('title', 'Nutrition Assessment')

@section('admin')
<div class="page-content">
  <div class="container-fluid">
         <div class="d-flex justify-content-between align-items-center mb-2">
        <a href="{{ route('nutrition.index', ['type'=>$type, 'participant_id'=>$assessment->participant_id]) }}"
            class="btn btn-secondary">&laquo; Back</a>

        <div class="d-flex gap-2">
            <a href="{{ route('nutrition.edit', ['id'=>$assessment->id, 'type'=>$type]) }}"
            class="btn btn-outline-primary">Edit</a>
            <a href="{{ route('nutrition.pdf', $assessment->id) }}"
            class="btn btn-outline-danger">PDF</a>
        </div>
        </div>

        {{-- Reuse the PDF layout for on-screen view --}}
        @include('daycareform.nutrition.pdf', ['renderForScreen' => true])
  </div>
</div>
@endsection
