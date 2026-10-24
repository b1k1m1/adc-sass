@php
use Illuminate\Support\Facades\DB;

    $user = auth()->user();
    $roleId = $user?->role;

    // 1) Get permission IDs for this role (NO JOIN)
    $permIds = [];
    if ($roleId) {
        $permIds = DB::table('role_has_permissions')
            ->where('role_id', $roleId)
            ->pluck('permission_id')
            ->toArray();
    }

    // 2) Get permission names (NO JOIN)
    $rolePerms = [];
    if (!empty($permIds)) {
        $rolePerms = DB::table('permissions')
            ->whereIn('id', $permIds)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->map(fn($v) => strtolower(trim($v)))
            ->toArray();
    }


    // strict permission checker
    $canMenu = function(string $perm) use ($rolePerms) {
        return in_array(strtolower(trim($perm)), $rolePerms, true);
    };
@endphp

<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
                        <ul class="metismenu list-unstyled" id="side-menu">

                <li class="menu-title" data-key="t-menu">Menu</li>

                {{-- Members --}}
                @if($canMenu('members.menu'))
                <li>
                    <a href="{{ route('members.dashboard') }}">
                        <i data-feather="meh" class="text-success"></i>
                        <span>{{ t('menu.members', 'Members') }}</span>
                    </a>
                </li>
                @endif

                {{-- Staff --}}
                @if($canMenu('staff.menu'))
                <li>
                    <a href="{{ route('employee.dashboard') }}">
                        <i data-feather="layout" class="text-warning"></i>
                        <span>{{ t('menu.staff', 'Staff') }}</span>
                    </a>
                </li>
                @endif

                {{-- Accounts --}}
                @if($canMenu('accounts.menu'))
                <li>
                    <a href="{{ route('accounts.dashboard') }}">
                        <i data-feather="dollar-sign" class="text-info"></i>
                        <span>{{ t('menu.accounts', 'Accounts') }}</span>
                    </a>
                </li>
                @endif

                {{-- Drivers --}}
                @if($canMenu('drivers.menu'))
                <li>
                    <a href="{{ route('drivers.dashboard') }}">
                        <i data-feather="truck"></i>
                        <span>{{ t('menu.drivers', 'Drivers') }}</span>
                    </a>
                </li>
                @endif

                {{-- Doctors --}}
                @if($canMenu('doctors.menu'))
                <li>
                    <a href="{{ route('doctors.dashboard') }}">
                        <i data-feather="thermometer"></i>
                        <span>{{ t('menu.doctors', 'Doctors') }}</span>
                    </a>
                </li>
                @endif

                {{-- Administrator --}}
                @if($canMenu('administrator.menu'))
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="key" class="text-info"></i>
                        <span>{{ t('menu.administrator', 'Administrator') }}</span>
                    </a>
                </li>
                @endif

                 {{-- Nursing --}}
                @if($canMenu('nursing.menu'))
                <li>
                    <a href="{{ route('nursing.dashboard') }}">
                        <i data-feather="heart" class="text-danger"></i>
                        <span>{{ t('menu.nursing', 'Nursing') }}</span>
                    </a>
                </li>
                @endif

                 {{-- Attendance --}}
                @if($canMenu('attendance.menu'))
                <li>
                    <a href="{{ route('attendance.dashboard') }}">
                        <i data-feather="box"></i>
                        <span>{{ t('menu.attendance', 'Attendance') }}</span>
                    </a>
                </li>
                @endif

                {{-- Activity Director --}}
                @if($canMenu('activity.menu'))
                <li>
                    <a href="{{ route('activity.dashboard') }}">
                        <i data-feather="sliders" class="text-warning"></i>
                        <span>{{ t('menu.activity', 'Activity Director') }}</span>
                    </a>
                </li>
                @endif

                {{-- Assessments --}}
                @if($canMenu('assessments.menu'))
                <li>
                    <a href="{{ route('social.dashboard') }}">
                        <i data-feather="monitor" class="text-info"></i>
                        <span data-key="t-tables">{{ t('menu.social', 'Assessment') }}</span>
                    </a>
                </li>
                @endif

                {{-- Pharmacist & Dietician --}}
                @if($canMenu('nutrition.menu'))
                <li>
                    <a href="{{ route('nutrition.dashboard') }}">
                        <i data-feather="activity" class="text-danger"></i>
                        <span data-key="t-tables">{{ t('menu.nutrition', 'Pharmacist & Dietician') }}</span>
                    </a>
                </li>
                @endif

                {{-- Shift Accounts --}}
                @if($canMenu('shift.menu'))
                <li>
                    <a href="{{ route('shift.dashboard') }}">
                        <i data-feather="pie-chart"></i>
                        <span>{{ t('menu.shift', 'Shift Accounts') }}</span>
                    </a>
                </li>
                @endif

                {{-- Claims & ERA --}}
                @if($canMenu('claims.menu'))
                <li>
                    <a href="{{ route('claims.dashboard') }}">
                        <i data-feather="package"></i>
                        <span data-key="t-package">{{ t('menu.claims', 'Claims & ERA') }}</span>
                    </a>
                </li>
                @endif

                {{-- System Admin --}}
                @if($canMenu('sysadmin.menu'))
                <li>
                    <a href="{{ route('sysadmin.dashboard') }}">
                        <i data-feather="user" class="text-danger"></i>
                        <span>{{ t('menu.sysadmin', 'System Admin') }}</span>
                    </a>
                </li>
                @endif

                {{-- Roles & Permissions dropdown --}}
                @if($canMenu('roles.menu'))
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="folder-plus" class="text-info"></i>
                        <span>{{ t('menu.roles', 'Roles & Permissions') }}</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('all.permission') }}">All Permissions</a></li>
                        <li><a href="{{ route('add.permission') }}">Add Permission</a></li>
                        <li><a href="{{ route('all.role') }}">All Roles</a></li>
                        <li><a href="{{ route('add.role') }}">Add Role</a></li>
                        <li><a href="{{ route('all.roles.permission') }}">All Roles Permission</a></li>
                        <li><a href="{{ route('add.roles.permission') }}">Assign Permissions to Role</a></li>
                    </ul>
                </li>
                @endif

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
