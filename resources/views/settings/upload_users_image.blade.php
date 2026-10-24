@extends('admin.admin_dashboard')
@section('admin')
@section('page-toolbar')
<a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
    {{ t('common.back','Back') }}
</a>
@endsection
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <!-- LEFT SIDE -->
                        <div>
                            <h4 class="mb-0 font-size-18">Upload User's Image and Rename</h4>
                        </div>
                        <!-- RIGHT SIDE TOOLBAR -->
                        <div class="page-title-right">
                            @yield('page-toolbar')
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        <form action="{{ route('user.image.upload') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="mb-3">
                <label for="image" class="form-label">Choose Image (JPG/JPEG/BMP)</label>
                <input type="file" name="image" id="image" class="form-control" accept=".jpg,.jpeg,.bmp" required>
            </div>
             <!-- Image Preview -->
            <div class="mb-3">
                <img id="preview" src="#" alt="Image Preview" style="display:none; max-height: 200px; border: 1px solid #ccc; padding: 5px;">
            </div>
            <div class="mb-3">
                <label for="new_filename" class="form-label">Save As (without .jpg)</label>
                <input type="text" name="new_filename" id="new_filename" class="form-control" placeholder="User id: 1,2,3 etc.," required>
            </div>

            <button type="submit" class="btn btn-primary">{{ t('common.save', 'Save') }}</button>
        </form>

    </div>
</div>

<!-- Script for image preview -->
<script>
    document.getElementById('image').addEventListener('change', function (e) {
        const [file] = this.files;
        if (file) {
            const preview = document.getElementById('preview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
</script>
@endsection
