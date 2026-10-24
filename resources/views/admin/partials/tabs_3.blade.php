@php
    $active = $active ?? 'data'; // data | reports | settings
@endphp

<ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ $active === 'data' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-data" role="tab">
            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
            <span class="d-none d-sm-block">{{ t('common.tabs.data_entry', 'Data Entry') }}</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ $active === 'reports' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-reports" role="tab">
            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
            <span class="d-none d-sm-block">{{ t('common.tabs.reports', 'Reports') }}</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ $active === 'settings' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-settings" role="tab">
            <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
            <span class="d-none d-sm-block">{{ t('common.tabs.settings', 'Settings') }}</span>
        </a>
    </li>
</ul>
