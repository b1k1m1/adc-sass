@php
    use Illuminate\Support\Facades\DB;

    $user = auth()->user();
    $roleId = $user?->role;

    // -------------------------------------------------------
    // Get permission IDs for this role
    // -------------------------------------------------------
    $permIds = [];

    if ($roleId) {
        $permIds = DB::table('role_has_permissions')->where('role_id', $roleId)->pluck('permission_id')->toArray();
    }

    // -------------------------------------------------------
    // Get permission names
    // -------------------------------------------------------
    $rolePerms = [];

    if (!empty($permIds)) {
        $rolePerms = DB::table('permissions')
            ->whereIn('id', $permIds)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->map(fn($v) => strtolower(trim($v)))
            ->toArray();
    }

    // Strict permission checker
    $canMenu = function (string $perm) use ($rolePerms) {
        return in_array(strtolower(trim($perm)), $rolePerms, true);
    };

    $tenantDatabase = strtolower((string) session('tenant_database'));

    // Roles & Permissions submenu active status
    $rolesMenuOpen =
        request()->routeIs('all.permission') ||
        request()->routeIs('add.permission') ||
        request()->routeIs('all.role') ||
        request()->routeIs('add.role') ||
        request()->routeIs('all.roles.permission') ||
        request()->routeIs('add.roles.permission');
@endphp


<style>
    /* =========================================================
       ADC PROFESSIONAL SIDEBAR
       ========================================================= */

    .vertical-menu {
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
    }

    #sidebar-menu {
        padding: 8px 0 25px;
    }

    /* =========================================================
       SECTION TITLES
       ========================================================= */

    #sidebar-menu .menu-title {
        padding: 18px 20px 7px;
        margin: 0;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.1px;
        text-transform: uppercase;
    }


    /* =========================================================
       MAIN MENU
       ========================================================= */

    #sidebar-menu ul li {
        position: relative;
    }

    #sidebar-menu ul li a {
        position: relative;

        display: flex;
        align-items: center;

        min-height: 42px;

        padding: 10px 15px;
        margin: 2px 10px;

        border-radius: 8px;

        color: #64748b;

        font-size: 14px;
        font-weight: 500;

        transition:
            background-color .18s ease,
            color .18s ease;
    }

    #sidebar-menu ul li a:hover {
        background: #f1f5f9;
        color: #0f172a;
    }


    /* =========================================================
       FEATHER ICONS
       ========================================================= */

    #sidebar-menu ul li a svg {
        width: 18px;
        height: 18px;

        margin-right: 11px;

        color: #94a3b8;

        stroke-width: 1.9;

        transition: color .18s ease;
    }

    #sidebar-menu ul li a:hover svg {
        color: #2563eb;
    }


    /* =========================================================
       ACTIVE MENU ITEM
       ========================================================= */

    #sidebar-menu ul li.mm-active>a,
    #sidebar-menu ul li>a.active {
        background: #eff6ff;
        color: #2563eb;
        font-weight: 600;
    }

    #sidebar-menu ul li.mm-active>a svg,
    #sidebar-menu ul li>a.active svg {
        color: #2563eb;
    }

    #sidebar-menu ul li.mm-active>a::before,
    #sidebar-menu ul li>a.active::before {
        content: "";

        position: absolute;

        left: -10px;
        top: 9px;

        width: 3px;
        height: 24px;

        border-radius: 0 4px 4px 0;

        background: #2563eb;
    }


    /* =========================================================
       SUB MENU
       ========================================================= */

    #sidebar-menu ul.sub-menu {
        padding: 4px 0 7px 0;
    }

    #sidebar-menu ul.sub-menu li a {
        min-height: 34px;

        padding: 7px 15px 7px 46px;
        margin: 1px 10px;

        font-size: 13px;
        font-weight: 500;

        color: #64748b;

        background: transparent;
    }

    #sidebar-menu ul.sub-menu li a:hover {
        color: #2563eb;
        background: #f8fafc;
    }

    #sidebar-menu ul.sub-menu li a.active {
        color: #2563eb;
        font-weight: 600;
        background: #f8fafc;
    }


    /* =========================================================
       ADMIN DIVIDER
       ========================================================= */

    .sidebar-admin-divider {
        margin: 18px 18px 0;
        border-top: 1px solid #e5e7eb;
    }


    /* =========================================================
       CURRENT ORGANIZATION
       ========================================================= */

    .adc-tenant-menu {
        margin: 8px 10px 10px;
    }

    .adc-current-organization {
        position: relative;

        display: flex;
        align-items: center;

        gap: 10px;

        padding: 10px 12px;

        background: #f8fafc;

        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .adc-org-icon {
        width: 34px;
        height: 34px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        background: #eef2ff;

        border-radius: 8px;

        color: #6366f1;
    }

    .adc-org-icon svg {
        width: 17px !important;
        height: 17px !important;

        margin: 0 !important;

        color: #6366f1 !important;
    }

    .adc-org-details {
        min-width: 0;
        line-height: 1.25;
    }

    .adc-org-caption {
        color: #94a3b8;

        font-size: 9px;
        font-weight: 700;

        text-transform: uppercase;

        letter-spacing: .8px;

        margin-bottom: 3px;
    }

    .adc-org-name {
        color: #334155;

        font-size: 12px;
        font-weight: 700;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    /* =========================================================
       COLLAPSED SIDEBAR
       ========================================================= */

    body[data-sidebar-size="sm"] #sidebar-menu .menu-title,
    body.vertical-collpsed #sidebar-menu .menu-title {
        display: none !important;
    }


    body[data-sidebar-size="sm"] .sidebar-admin-divider,
    body.vertical-collpsed .sidebar-admin-divider {
        display: none !important;
    }


    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a,
    body.vertical-collpsed #sidebar-menu>ul>li>a {

        width: 42px !important;
        height: 42px !important;

        min-height: 42px !important;

        padding: 0 !important;

        margin: 5px auto !important;

        display: flex !important;
        align-items: center !important;
        justify-content: center !important;

        border-radius: 8px !important;
    }


    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a svg,
    body.vertical-collpsed #sidebar-menu>ul>li>a svg {

        width: 19px !important;
        height: 19px !important;

        margin: 0 !important;
    }


    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a>span,
    body.vertical-collpsed #sidebar-menu>ul>li>a>span {
        display: none !important;
    }


    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a.has-arrow::after,
    body.vertical-collpsed #sidebar-menu>ul>li>a.has-arrow::after {
        display: none !important;
    }


    body[data-sidebar-size="sm"] #sidebar-menu ul li.mm-active>a::before,
    body[data-sidebar-size="sm"] #sidebar-menu ul li>a.active::before,
    body.vertical-collpsed #sidebar-menu ul li.mm-active>a::before,
    body.vertical-collpsed #sidebar-menu ul li>a.active::before {
        display: none !important;
    }


    body[data-sidebar-size="sm"] #sidebar-menu,
    body.vertical-collpsed #sidebar-menu {
        padding-top: 10px !important;
    }


    /* =========================================================
       COLLAPSED CURRENT ORGANIZATION
       ========================================================= */

    body[data-sidebar-size="sm"] .adc-tenant-menu,
    body.vertical-collpsed .adc-tenant-menu {

        margin: 7px auto 12px !important;

        width: 42px !important;
    }


    body[data-sidebar-size="sm"] .adc-current-organization,
    body.vertical-collpsed .adc-current-organization {

        width: 42px !important;
        height: 42px !important;

        padding: 0 !important;

        display: flex !important;
        align-items: center !important;
        justify-content: center !important;

        background: transparent;

        border: none;
    }


    body[data-sidebar-size="sm"] .adc-org-details,
    body.vertical-collpsed .adc-org-details {
        display: none !important;
    }


    body[data-sidebar-size="sm"] .adc-org-icon,
    body.vertical-collpsed .adc-org-icon {

        width: 42px !important;
        height: 42px !important;

        background: #f1f5ff;
    }


    /* =========================================================
       COLLAPSED MENU TOOLTIPS
       ========================================================= */

    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a[data-tooltip]::after,
    body.vertical-collpsed #sidebar-menu>ul>li>a[data-tooltip]::after {

        content: attr(data-tooltip);

        position: absolute;

        left: 52px;
        top: 50%;

        transform: translateY(-50%) translateX(-4px);

        padding: 7px 10px;

        background: #0f172a;
        color: #ffffff;

        font-size: 12px;
        font-weight: 500;

        border-radius: 6px;

        white-space: nowrap;

        opacity: 0;
        visibility: hidden;

        pointer-events: none;

        z-index: 99999;

        box-shadow:
            0 4px 6px rgba(0, 0, 0, .12);

        transition:
            opacity .15s ease,
            transform .15s ease;
    }


    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a[data-tooltip]::before,
    body.vertical-collpsed #sidebar-menu>ul>li>a[data-tooltip]::before {

        content: "";

        position: absolute;

        left: 47px;
        top: 50%;

        transform: translateY(-50%);

        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-right: 5px solid #0f172a;

        opacity: 0;
        visibility: hidden;

        z-index: 99999;
    }


    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a[data-tooltip]:hover::after,
    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a[data-tooltip]:hover::before,
    body.vertical-collpsed #sidebar-menu>ul>li>a[data-tooltip]:hover::after,
    body.vertical-collpsed #sidebar-menu>ul>li>a[data-tooltip]:hover::before {

        opacity: 1;
        visibility: visible;
    }


    body[data-sidebar-size="sm"] #sidebar-menu>ul>li>a[data-tooltip]:hover::after,
    body.vertical-collpsed #sidebar-menu>ul>li>a[data-tooltip]:hover::after {

        transform: translateY(-50%) translateX(0);
    }


    /* =========================================================
       ORGANIZATION TOOLTIP
       ========================================================= */

    body[data-sidebar-size="sm"] .adc-current-organization[data-org-tooltip]::after,
    body.vertical-collpsed .adc-current-organization[data-org-tooltip]::after {

        content: attr(data-org-tooltip);

        position: absolute;

        left: 52px;
        top: 50%;

        transform: translateY(-50%) translateX(-4px);

        padding: 7px 10px;

        background: #0f172a;
        color: #ffffff;

        font-size: 12px;
        font-weight: 500;

        border-radius: 6px;

        white-space: nowrap;

        opacity: 0;
        visibility: hidden;

        pointer-events: none;

        z-index: 99999;

        box-shadow:
            0 4px 6px rgba(0, 0, 0, .12);

        transition:
            opacity .15s ease,
            transform .15s ease;
    }


    body[data-sidebar-size="sm"] .adc-current-organization:hover::after,
    body.vertical-collpsed .adc-current-organization:hover::after {

        opacity: 1;
        visibility: visible;

        transform: translateY(-50%) translateX(0);
    }


    /* =========================================================
       SCROLLBAR
       ========================================================= */

    .vertical-menu .simplebar-scrollbar:before {
        background: #cbd5e1;
        opacity: .65;
    }
</style>


<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <div id="sidebar-menu">

            <ul class="metismenu list-unstyled" id="side-menu">


                {{-- =====================================================
                     CURRENT ORGANIZATION
                     ===================================================== --}}
                @if (session('tenant_database'))
                    <li class="adc-tenant-menu">

                        <div class="adc-current-organization"
                            data-org-tooltip="Current Organization: {{ strtoupper((string) session('tenant_database')) }}">

                            <div class="adc-org-icon">
                                <i data-feather="database"></i>
                            </div>

                            <div class="adc-org-details">

                                <div class="adc-org-caption">
                                    Current Organization
                                </div>

                                <div class="adc-org-name">
                                    {{ strtoupper((string) session('tenant_database')) }}
                                </div>

                            </div>

                        </div>

                    </li>
                @endif



                {{-- =====================================================
                     CLIENT CARE
                     ===================================================== --}}

                <li class="menu-title">
                    Client Care
                </li>


                {{-- Members --}}
                @if ($canMenu('members.menu'))
                    <li class="{{ request()->routeIs('members.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('members.dashboard') }}"
                            class="{{ request()->routeIs('members.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.members', 'Members') }}">

                            <i data-feather="users"></i>

                            <span>
                                {{ t('menu.members', 'Members') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Attendance --}}
                @if ($canMenu('attendance.menu'))
                    <li class="{{ request()->routeIs('attendance.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('attendance.dashboard') }}"
                            class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.attendance', 'Attendance') }}">

                            <i data-feather="check-square"></i>

                            <span>
                                {{ t('menu.attendance', 'Attendance') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Nursing --}}
                @if ($canMenu('nursing.menu'))
                    <li class="{{ request()->routeIs('nursing.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('nursing.dashboard') }}"
                            class="{{ request()->routeIs('nursing.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.nursing', 'Nursing') }}">

                            <i data-feather="heart"></i>

                            <span>
                                {{ t('menu.nursing', 'Nursing') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Assessments --}}
                @if ($canMenu('assessments.menu'))
                    <li class="{{ request()->routeIs('social.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('social.dashboard') }}"
                            class="{{ request()->routeIs('social.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.social', 'Assessment') }}">

                            <i data-feather="clipboard"></i>

                            <span>
                                {{ t('menu.social', 'Assessment') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Activity Director --}}
                @if ($canMenu('activity.menu'))
                    <li class="{{ request()->routeIs('activity.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('activity.dashboard') }}"
                            class="{{ request()->routeIs('activity.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.activity', 'Activity Director') }}">

                            <i data-feather="calendar"></i>

                            <span>
                                {{ t('menu.activity', 'Activity Director') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Pharmacist & Dietician --}}
                @if ($canMenu('nutrition.menu'))
                    <li class="{{ request()->routeIs('nutrition.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('nutrition.dashboard') }}"
                            class="{{ request()->routeIs('nutrition.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.nutrition', 'Pharmacist & Dietician') }}">

                            <i data-feather="activity"></i>

                            <span>
                                {{ t('menu.nutrition', 'Pharmacist & Dietician') }}
                            </span>

                        </a>

                    </li>
                @endif



                {{-- =====================================================
                     OPERATIONS
                     ===================================================== --}}

                <li class="menu-title">
                    Operations
                </li>


                {{-- Staff --}}
                @if ($canMenu('staff.menu'))
                    <li class="{{ request()->routeIs('employee.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('employee.dashboard') }}"
                            class="{{ request()->routeIs('employee.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.staff', 'Staff') }}">

                            <i data-feather="briefcase"></i>

                            <span>
                                {{ t('menu.staff', 'Staff') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Drivers --}}
                @if ($canMenu('drivers.menu'))
                    <li class="{{ request()->routeIs('drivers.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('drivers.dashboard') }}"
                            class="{{ request()->routeIs('drivers.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.drivers', 'Drivers') }}">

                            <i data-feather="truck"></i>

                            <span>
                                {{ t('menu.drivers', 'Drivers') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Doctors --}}
                @if ($canMenu('doctors.menu'))
                    <li class="{{ request()->routeIs('doctors.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('doctors.dashboard') }}"
                            class="{{ request()->routeIs('doctors.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.doctors', 'Doctors') }}">

                            <i data-feather="user-plus"></i>

                            <span>
                                {{ t('menu.doctors', 'Doctors') }}
                            </span>

                        </a>

                    </li>
                @endif



                {{-- =====================================================
                     FINANCIAL
                     ===================================================== --}}

                <li class="menu-title">
                    Financial
                </li>


                {{-- Accounts --}}
                @if ($canMenu('accounts.menu'))
                    <li class="{{ request()->routeIs('accounts.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('accounts.dashboard') }}"
                            class="{{ request()->routeIs('accounts.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.accounts', 'Accounts') }}">

                            <i data-feather="credit-card"></i>

                            <span>
                                {{ t('menu.accounts', 'Accounts') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Shift Accounts --}}
                @if ($canMenu('shift.menu'))
                    <li class="{{ request()->routeIs('shift.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('shift.dashboard') }}"
                            class="{{ request()->routeIs('shift.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.shift', 'Shift Accounts') }}">

                            <i data-feather="repeat"></i>

                            <span>
                                {{ t('menu.shift', 'Shift Accounts') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Claims & ERA --}}
                @if ($tenantDatabase !== 'adc_kor' && $canMenu('claims.menu'))
                    <li class="{{ request()->routeIs('claims.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('claims.dashboard') }}"
                            class="{{ request()->routeIs('claims.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.claims', 'Claims & ERA') }}">

                            <i data-feather="file-text"></i>

                            <span>
                                {{ t('menu.claims', 'Claims & ERA') }}
                            </span>

                        </a>

                    </li>
                @endif



                {{-- =====================================================
                     REPORTING
                     ===================================================== --}}

                @if ($canMenu('specialreport.menu'))
                    <li class="menu-title">
                        Reporting
                    </li>

                    <li class="{{ request()->routeIs('specialreports.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('specialreports.dashboard') }}"
                            class="{{ request()->routeIs('specialreports.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.specialreport', 'Special Reports') }}">

                            <i data-feather="bar-chart-2"></i>

                            <span>
                                {{ t('menu.specialreport', 'Special Reports') }}
                            </span>

                        </a>

                    </li>
                @endif



                {{-- =====================================================
                     ADMINISTRATION
                     ===================================================== --}}

                @if ($canMenu('administrator.menu') || $canMenu('sysadmin.menu') || $canMenu('roles.menu'))
                    <li class="sidebar-admin-divider"></li>

                    <li class="menu-title">
                        Administration
                    </li>
                @endif


                {{-- System Admin --}}
                @if ($canMenu('sysadmin.menu'))
                    <li class="{{ request()->routeIs('sysadmin.*') ? 'mm-active' : '' }}">

                        <a href="{{ route('sysadmin.dashboard') }}"
                            class="{{ request()->routeIs('sysadmin.*') ? 'active' : '' }}"
                            data-tooltip="{{ t('menu.sysadmin', 'System Admin') }}">

                            <i data-feather="settings"></i>

                            <span>
                                {{ t('menu.sysadmin', 'System Admin') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Administrator --}}
                @if ($canMenu('administrator.menu'))
                    <li>

                        <a href="javascript:void(0);" data-tooltip="{{ t('menu.administrator', 'Administrator') }}">

                            <i data-feather="shield"></i>

                            <span>
                                {{ t('menu.administrator', 'Administrator') }}
                            </span>

                        </a>

                    </li>
                @endif


                {{-- Roles & Permissions --}}
                @if ($canMenu('roles.menu'))
                    <li class="{{ $rolesMenuOpen ? 'mm-active' : '' }}">

                        <a href="javascript:void(0);" class="has-arrow {{ $rolesMenuOpen ? 'active' : '' }}"
                            aria-expanded="{{ $rolesMenuOpen ? 'true' : 'false' }}"
                            data-tooltip="{{ t('menu.roles', 'Roles & Permissions') }}">

                            <i data-feather="shield"></i>

                            <span>
                                {{ t('menu.roles', 'Roles & Permissions') }}
                            </span>

                        </a>


                        <ul class="sub-menu" aria-expanded="{{ $rolesMenuOpen ? 'true' : 'false' }}">

                            <li>
                                <a href="{{ route('all.permission') }}"
                                    class="{{ request()->routeIs('all.permission') || request()->routeIs('add.permission') ? 'active' : '' }}">
                                    Permissions
                                </a>
                            </li>


                            <li>
                                <a href="{{ route('all.role') }}"
                                    class="{{ request()->routeIs('all.role') || request()->routeIs('add.role') ? 'active' : '' }}">
                                    Roles
                                </a>
                            </li>


                            <li>
                                <a href="{{ route('all.roles.permission') }}"
                                    class="{{ request()->routeIs('all.roles.permission') || request()->routeIs('add.roles.permission') ? 'active' : '' }}">
                                    Role Permissions
                                </a>
                            </li>

                        </ul>

                    </li>
                @endif

            </ul>

        </div>

    </div>

</div>
