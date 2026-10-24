<style>
.org-name {
    font-weight: 700;
    color: #0d6efd;
}

.org-divider {
    color: #6c757d;
    margin: 0 6px;
}

.header-title-container {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.user-dropdown-menu {
    min-width: 260px;
}

.user-login-info {
    line-height: 1.4;
}

.user-login-info .text-dark {
    color: #343a40 !important;
}

</style>
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('backend/assets/images/logo-sm.svg')}}" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('backend/assets/images/logo-sm.svg')}}" alt="" height="24"> <span class="logo-txt">Home</span>
                    </span>
                </a>

                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('backend/assets/images/logo-sm.svg')}}" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('backend/assets/images/logo-sm.svg')}}" alt="" height="24"> <span class="logo-txt">Home</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button class="btn btn-primary" type="button"><i class="bx bx-search-alt align-middle"></i></button>
                </div>
            </form>
        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item" id="page-header-search-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ..." aria-label="Search Result">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Title should go here and should be in the middle of the header and it should be dynamic -->
            <!-- Centered Title -->
            <div class="header-title-container">
                <h4 class="header-title mb-0 text-truncate animate-title" data-module="{{ $module ?? 'dashboard' }}">
                    <span class="org-name">
                        {{ Str::limit(session('organization_name', 'ADC Test'),35) }}
                    </span>
                    <span class="org-divider"> | </span>
                        {{ $title ?? 'Dashboard' }}
                </h4>
            </div>
            @php
                $locale = app()->getLocale();

                // Keep these 3 for now
                $langs = [
                    'en' => 'English',
                    'ko' => 'Korean',
                    'es' => 'Spanish',
                ];

                $currentLabel = $langs[$locale] ?? $langs['en'];
            @endphp

            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item dropdown-toggle"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ $currentLabel }}
                </button>

                <div class="dropdown-menu dropdown-menu-end">
                    @foreach($langs as $code => $label)
                        <a href="{{ route('lang.switch', $code) }}"
                        class="dropdown-item notify-item {{ $locale === $code ? 'active' : '' }}">
                            <span class="align-middle">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i data-feather="moon" class="icon-lg layout-mode-dark"></i>
                    <i data-feather="sun" class="icon-lg layout-mode-light"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item right-bar-toggle me-2">
                    <i data-feather="settings" class="icon-lg"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button"
                        class="btn header-item bg-light-subtle border-start border-end"
                        id="page-header-user-dropdown"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false">

                    <img class="rounded-circle header-profile-user"
                        src="{{ asset('backend/assets/images/users/avatar-1.jpg') }}"
                        alt="Header Avatar">

                    <span class="lead-text">
                        {{ Auth::user()->firstname }},
                        {{ Auth::user()->lastname }}
                    </span>

                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-end user-dropdown-menu">

                    <div class="px-3 py-2 user-login-info">

                        <div class="fw-semibold text-dark">
                            {{ Auth::user()->firstname }}
                            {{ Auth::user()->lastname }}
                        </div>

                        <div class="small text-muted text-break">
                            {{ Auth::user()->email }}
                        </div>

                        <div class="dropdown-divider my-2"></div>

                        <div class="small text-muted">
                            <i class="mdi mdi-clock-outline me-1"></i>
                            Last Login
                        </div>

                        <div class="small fw-semibold text-dark mt-1">
                            @if(Auth::user()->last_login_time)
                                {{ Auth::user()->last_login_time->format('m/d/Y h:i A') }}
                            @else
                                First Login
                            @endif
                        </div>

                        @if(Auth::user()->login_ip_address)
                            <div class="small text-muted mt-1">
                                IP: {{ Auth::user()->login_ip_address }}
                            </div>
                        @endif

                    </div>

                    <div class="dropdown-divider my-1"></div>

                    <a class="dropdown-item" href="{{ route('admin.profile') }}">
                        <i class="mdi mdi-face-man font-size-16 align-middle me-1"></i>
                        Profile
                    </a>

                    <div class="dropdown-divider my-1"></div>

                    <a class="dropdown-item" href="{{ route('profile.logout') }}">
                        <i class="mdi mdi-logout font-size-16 align-middle me-1"></i>
                        Logout
                    </a>

                </div>
            </div>

        </div>
    </div>
</header>

<style>
    .header-title-container {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    padding-top: 1.5rem; /* Added top spacing */
}

.header-title {
    font-weight: 700; /* Bold */
    transition: all 0.3s ease; /* Smooth transition */
}

/* Dynamic Colors for Modules */
.header-title[data-module="dashboard"] { color: #6c757d; } /* Gray */
.header-title[data-module="members"]  { color: #0d6efd; } /* Blue */
.header-title[data-module="employees"] { color: #198754; } /* Green */
.header-title[data-module="settings"]  { color: #fd7e14; } /* Orange */
.header-title[data-module="nursing"]  { color: #27fd14; } /* Orange */

/* Animation for Title Changes */
.animate-title {
    animation: fadeIn 0.5s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

</style>
