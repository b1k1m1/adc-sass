@extends('admin.admin_dashboard')
@section('title', $mode === 'edit' ? 'Vehicle Master - Edit' : 'Vehicle Master - Add')
@section('admin')

<style>
  .rpt-title { font-weight:700; font-size:18px; }
  .sub-note  { font-size:12px; color:#6c757d; }
  .photo-box {
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    height: 260px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#fff;
    overflow:hidden;
  }
  .photo-box img { max-width:100%; max-height:100%; object-fit:contain; }
  .form-label { font-weight:600; }
</style>

@php
  $vehicleId = old('vehicle_id', $row->vehicle_id ?? '');
  $photo     = $row->photo ?? null;
  $photoUrl  = $photo ? asset('storage/vehicles/'.$photo) : null;
@endphp

<div class="page-content">
  <div class="container-fluid">

    {{-- Header / Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <div class="rpt-title">{{ $mode === 'edit' ? 'Vehicle Master (Edit)' : 'Vehicle Master (Add New)' }}</div>
        <div class="sub-note">Enter vehicle details and save.</div>
      </div>

      <div class="d-flex gap-2">
        <a href="{{ route('vehiclemaster.index') }}" class="btn btn-sm btn-outline-secondary">
          {{ t('common.back','Back') }}
        </a>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">

        <form method="POST" enctype="multipart/form-data"
              action="{{ $mode === 'edit'
                        ? route('vehiclemaster.update', $row->vehicle_id)
                        : route('vehiclemaster.store') }}">
          @csrf

          {{-- Top row: Vehicle ID + Status --}}
          <div class="row g-3 mb-2">
            <div class="col-md-4">
              <label class="form-label">Vehicle ID <span class="text-danger">*</span></label>
              <input type="text" name="vehicle_id"
                     class="form-control @error('vehicle_id') is-invalid @enderror"
                     value="{{ $vehicleId }}"
                     {{ $mode==='edit' ? 'readonly' : '' }}>
              @error('vehicle_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Category</label>
              @php $cat = old('category', $row->category ?? ''); @endphp
              <select name="category" class="form-select @error('category') is-invalid @enderror">
                <option value="">-- Select --</option>
                @foreach($dropdowns['categories'] as $c)
                  <option value="{{ $c }}" {{ $cat===$c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
              @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              @php $tf = old('trans_flag', $row->trans_flag ?? ''); @endphp
              <select name="trans_flag" class="form-select @error('trans_flag') is-invalid @enderror">
                <option value="">--</option>
                @foreach($dropdowns['flags'] as $f)
                  <option value="{{ $f }}" {{ $tf===$f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
              </select>
              @error('trans_flag') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="row g-3">
            {{-- LEFT: fields --}}
            <div class="col-lg-7">

              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Description</label>
                  <input type="text" name="description"
                         class="form-control @error('description') is-invalid @enderror"
                         value="{{ old('description', $row->description ?? '') }}">
                  @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Manufacturer</label>
                  <input type="text" name="manufacturer"
                         class="form-control @error('manufacturer') is-invalid @enderror"
                         value="{{ old('manufacturer', $row->manufacturer ?? '') }}">
                  @error('manufacturer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                  <label class="form-label">Model</label>
                  <input type="text" name="model"
                         class="form-control @error('model') is-invalid @enderror"
                         value="{{ old('model', $row->model ?? '') }}">
                  @error('model') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                  <label class="form-label">Year</label>
                  <input type="text" name="make_year"
                         class="form-control @error('make_year') is-invalid @enderror"
                         value="{{ old('make_year', $row->make_year ?? '') }}">
                  @error('make_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Licence Plate No</label>
                  <input type="text" name="licence_plate_no"
                         class="form-control @error('licence_plate_no') is-invalid @enderror"
                         value="{{ old('licence_plate_no', $row->licence_plate_no ?? '') }}">
                  @error('licence_plate_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">VIN #</label>
                  <input type="text" name="vin_no"
                         class="form-control @error('vin_no') is-invalid @enderror"
                         value="{{ old('vin_no', $row->vin_no ?? '') }}">
                  @error('vin_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Purchased From</label>
                  <input type="text" name="purchase_from"
                         class="form-control @error('purchase_from') is-invalid @enderror"
                         value="{{ old('purchase_from', $row->purchase_from ?? '') }}">
                  @error('purchase_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Purchased Date</label>
                  <input type="date" name="purchase_dt"
                         class="form-control @error('purchase_dt') is-invalid @enderror"
                         value="{{ old('purchase_dt', isset($row->purchase_dt) ? \Carbon\Carbon::parse($row->purchase_dt)->format('Y-m-d') : '') }}">
                  @error('purchase_dt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Leased?</label>
                  @php $lt = old('lease_type', $row->lease_type ?? ''); @endphp
                  <select name="lease_type" class="form-select @error('lease_type') is-invalid @enderror">
                    <option value="">--</option>
                    @foreach($dropdowns['leaseTypes'] as $x)
                      <option value="{{ $x }}" {{ $lt===$x ? 'selected' : '' }}>{{ $x }}</option>
                    @endforeach
                  </select>
                  @error('lease_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Lien To</label>
                  <input type="text" name="liento"
                         class="form-control @error('liento') is-invalid @enderror"
                         value="{{ old('liento', $row->liento ?? '') }}">
                  @error('liento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                  <label class="form-label">Capacity</label>
                  <input type="number" step="1" name="capacity"
                         class="form-control @error('capacity') is-invalid @enderror"
                         value="{{ old('capacity', $row->capacity ?? '') }}">
                  @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label">Driver Allotted</label>
                    <select name="driver_id"
                            class="form-select @error('driver_id') is-invalid @enderror">
                        <option value="">-- Select Driver --</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->staffid }}"
                                    {{ old('driver_id', $row->driver_id ?? '') == $d->staffid ? 'selected' : '' }}>
                                    {{ $d->stafflastname }}, {{ $d->stafffirstname }}
                                </option>
                            @endforeach
                    </select>
                    @error('driver_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Reg valid from</label>
                  <input type="date" name="registration_from"
                         class="form-control @error('registration_from') is-invalid @enderror"
                         value="{{ old('registration_from', isset($row->registration_from) ? \Carbon\Carbon::parse($row->registration_from)->format('Y-m-d') : '') }}">
                  @error('registration_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Reg valid to</label>
                  <input type="date" name="registration_to"
                         class="form-control @error('registration_to') is-invalid @enderror"
                         value="{{ old('registration_to', isset($row->registration_to) ? \Carbon\Carbon::parse($row->registration_to)->format('Y-m-d') : '') }}">
                  @error('registration_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label">Photo (File Name)</label>
                  <input type="text" class="form-control" readonly
                         value="{{ old('photo', $row->photo ?? '') }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Upload Photo</label>
                  <input type="file" name="photo_file" class="form-control">
                  <div class="sub-note">Optional. Upload to replace existing photo.</div>
                </div>

              </div>
            </div>

            {{-- RIGHT: Photo preview --}}
            <div class="col-lg-5">
              <label class="form-label">Photo Preview</label>
              <div class="photo-box">
                @if($photoUrl)
                  <img src="{{ $photoUrl }}" alt="Vehicle Photo">
                @else
                  <div class="text-muted">No Photo</div>
                @endif
              </div>
            </div>
          </div>

          <hr class="my-3">

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('vehiclemaster.index') }}" class="btn btn-outline-secondary">
              Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              {{ $mode === 'edit' ? 'Update' : 'Save' }}
            </button>
          </div>

        </form>

      </div>
    </div>

  </div>
</div>

@endsection
