@extends('admin.admin_dashboard') {{-- change to your main layout --}}
@section('admin')
<div class="page-content">
    <div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Translations (No Delete)</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter/Search --}}
    <form method="GET" action="{{ route('translations.index') }}" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="locale" class="form-select" onchange="this.form.submit()">
                @foreach($allowedLocales as $lc)
                    <option value="{{ $lc }}" {{ $locale === $lc ? 'selected' : '' }}>
                        {{ strtoupper($lc) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-auto">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search key or value...">
        </div>

        <div class="col-auto">
            <button class="btn btn-secondary">Search</button>
            <a class="btn btn-light" href="{{ route('translations.index', ['locale' => $locale]) }}">Reset</a>
        </div>
    </form>

    {{-- Add New Translation --}}
    <div class="card mb-3">
        <div class="card-header">
            <strong>Add / Upsert Translation</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('translations.store') }}" class="row g-2">
                @csrf

                <div class="col-md-2">
                    <label class="form-label">Locale</label>
                    <select name="locale" class="form-select">
                        @foreach($allowedLocales as $lc)
                            <option value="{{ $lc }}" {{ $locale === $lc ? 'selected' : '' }}>
                                {{ strtoupper($lc) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Key</label>
                    <input type="text" name="key" class="form-control"
                           placeholder="menu.members OR Save" required>
                    <div class="form-text">Tip: Use structured keys like <code>menu.members</code></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Value</label>
                    <input type="text" name="value" class="form-control"
                           placeholder="Translated text...">
                </div>

                <div class="col-12">
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- List + Inline Update --}}
    <div class="card">
        <div class="card-header">
            <strong>Translations List ({{ strtoupper($locale) }})</strong>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%;">Key</th>
                            <th>Value</th>
                            <th style="width: 140px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><code>{{ $row->key }}</code></td>

                                <td>
                                    <form method="POST" action="{{ route('translations.update', $row->id) }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="text" name="value" class="form-control"
                                               value="{{ $row->value }}"
                                               placeholder="Enter translation...">
                                </td>

                                <td>
                                        <button class="btn btn-sm btn-success">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center p-4">
                                    No translations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $rows->links() }}
        </div>
    </div>
    </div>
</div>
@endsection
