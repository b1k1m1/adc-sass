<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>ADC SaaS Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">

    <!-- preloader css -->
    <link rel="stylesheet" href="{{ asset('backend/assets/css/preloader.min.css') }}" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet"
        type="text/css" />

    <!-- Icons Css -->
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- App Css-->
    <link href="{{ asset('backend/assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

</head>

<body>

    <div class="auth-page">

        <div class="container-fluid p-0">

            <div class="row g-0">

                <!-- Centering Wrapper -->
                <div class="d-flex justify-content-center align-items-center min-vh-100">

                    <div class="col-xxl-3 col-lg-4 col-md-5">

                        <div class="auth-full-page-content d-flex p-sm-5 p-4">

                            <div class="w-100">

                                <div class="d-flex flex-column h-100">

                                    <div class="mb-4 mb-md-5 text-center">

                                        <a href="{{ url('/') }}" class="d-block auth-logo">

                                            <img src="{{ asset('backend/assets/images/logo-sm.svg') }}" alt=""
                                                height="28">

                                        </a>

                                    </div>

                                    <div class="auth-content my-auto">

                                        <div class="text-center">

                                            <h5 class="mb-0">
                                                Sign in to continue
                                            </h5>

                                            <p class="mt-2 mb-0">
                                                Access the ADC system using your
                                            </p>

                                            <p>
                                                Email, Username, Phone and Password.
                                            </p>

                                        </div>

                                        <form class="mt-4 pt-2" method="POST" action="{{ route('login') }}"
                                            id="loginForm">

                                            @csrf

                                            @if ($errors->any())
                                                <div class="alert alert-danger">

                                                    {{ $errors->first() }}

                                                </div>
                                            @endif

                                            <!-- Login -->
                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Email or Username or Phone
                                                </label>

                                                <input type="text" class="form-control" id="login" name="login"
                                                    value="{{ old('login') }}"
                                                    placeholder="Enter your email, username or phone"
                                                    autocomplete="username" autofocus required>

                                            </div>

                                            <!-- Password -->
                                            <div class="mb-3">

                                                <div class="d-flex align-items-start">

                                                    <div class="flex-grow-1">

                                                        <label class="form-label">
                                                            Password
                                                        </label>

                                                    </div>

                                                </div>

                                                <div class="input-group auth-pass-inputgroup">

                                                    <input type="password" class="form-control" id="password"
                                                        name="password" placeholder="Enter your password"
                                                        autocomplete="current-password" required aria-label="Password"
                                                        aria-describedby="password-addon">

                                                    <button class="btn btn-light shadow-none ms-0" type="button"
                                                        id="password-addon">

                                                        <i class="mdi mdi-eye-outline"></i>

                                                    </button>

                                                </div>

                                                <div id="capsLockWarning" class="text-warning small mt-1"
                                                    style="display:none;">

                                                    <i class="mdi mdi-alert-circle-outline me-1"></i>

                                                    Caps Lock is ON

                                                </div>

                                            </div>

                                            <!-- Remember Me -->
                                            <div class="row mb-4">

                                                <div class="col">

                                                    <div class="form-check">

                                                        <input class="form-check-input" type="checkbox"
                                                            id="remember-check" name="remember" value="1"
                                                            {{ old('remember') ? 'checked' : '' }}>

                                                        <label class="form-check-label" for="remember-check">

                                                            Remember me

                                                        </label>

                                                    </div>

                                                </div>

                                            </div>

                                            <!-- Login Button -->
                                            <div class="mb-3">

                                                <button class="btn btn-primary w-100 waves-effect waves-light"
                                                    type="submit" id="loginBtn">

                                                    Log In

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- end auth full page content -->

                </div>

            </div>

            <!-- end row -->

        </div>

        <!-- end container fluid -->

    </div>


    <!-- JAVASCRIPT -->

    <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js') }}"></script>

    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('backend/assets/libs/metismenu/metisMenu.min.js') }}"></script>

    <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>

    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>

    <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js') }}"></script>

    <!-- pace js -->
    <script src="{{ asset('backend/assets/libs/pace-js/pace.min.js') }}"></script>

    <!-- password addon init -->
    <script src="{{ asset('backend/assets/js/pages/pass-addon.init.js') }}"></script>

    <!-- Prevent double-click login submit -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const loginForm = document.getElementById('loginForm');

            const loginBtn = document.getElementById('loginBtn');

            const passwordInput = document.getElementById('password');

            const capsLockWarning =
                document.getElementById('capsLockWarning');


            /*
            |--------------------------------------------------------------------------
            | Caps Lock Warning
            |--------------------------------------------------------------------------
            */

            if (passwordInput && capsLockWarning) {

                function updateCapsLockWarning(event) {

                    if (
                        event.getModifierState &&
                        event.getModifierState('CapsLock')
                    ) {

                        capsLockWarning.style.display = 'block';

                    } else {

                        capsLockWarning.style.display = 'none';

                    }

                }

                passwordInput.addEventListener(
                    'keydown',
                    updateCapsLockWarning
                );

                passwordInput.addEventListener(
                    'keyup',
                    updateCapsLockWarning
                );

                passwordInput.addEventListener('blur', function() {

                    capsLockWarning.style.display = 'none';

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Double-Click Login Submit
            |--------------------------------------------------------------------------
            */

            if (loginForm && loginBtn) {

                loginForm.addEventListener('submit', function() {

                    /*
                     * Allow browser required-field validation first.
                     */

                    if (!loginForm.checkValidity()) {

                        return;

                    }

                    /*
                     * Prevent duplicate submission.
                     */

                    loginBtn.disabled = true;

                    loginBtn.innerHTML = `
                        <span
                            class="spinner-border spinner-border-sm me-1"
                            role="status"
                            aria-hidden="true">
                        </span>
                        Logging in...
                    `;

                });

            }

        });
    </script>

</body>

</html>
