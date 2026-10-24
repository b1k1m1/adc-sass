@extends('admin.admin_dashboard')
@section('title', 'Nursing Assessment')

@section('admin')
<div class="page-content">
  <div class="container-fluid">


<div class="d-flex gap-2 mb-3">
    <a href="{{ route('nursing-assessment.index', ['type'=>$type]) }}" class="btn btn-secondary">&laquo; Back</a>
    <a href="{{ route('nursing-assessment.pdf', $assessment->id) }}" class="btn btn-dark">PDF</a>
</div>

@include('daycareform.nursing.pdf', [
    'assessment' => $assessment,
    'member' => $member,
    'renderForScreen' => true
])


  </div>
</div>
@endsection
