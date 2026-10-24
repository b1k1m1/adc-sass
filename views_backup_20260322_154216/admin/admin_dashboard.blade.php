<!doctype html>
<html lang="en">

    <head>

        <script>
            if (!localStorage.getItem('layout_mode') && window.matchMedia('(prefers-color-scheme: dark)').matches)
            document.documentElement.setAttribute("data-layout-mode","dark");
        </script>

        <meta charset="utf-8" />
        <!--
        <title>ADC | Dashboard</title>
        -->
        <title>
            @hasSection('title')
                @yield('title') | ADC
            @else
                Dashboard | ADC
            @endif
        </title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesbrand" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">

        <!-- Bootstrap Css -->
        <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />

        <!-- Icons Css -->
        <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- App Css -->
        <link href="{{ asset('backend/assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

        <!-- Preloader css (theme related) -->
        <link rel="stylesheet" href="{{ asset('backend/assets/css/preloader.min.css') }}" type="text/css" />

        <!-- Plugin CSS -->
        <link href="{{ asset('backend/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet" type="text/css" />

        <!-- SweetAlert2 CSS -->
        <link href="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- DataTables CSS (Bootstrap 4 theme - keep these, do NOT mix with CDN datatables css) -->
        <link href="{{ asset('backend/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Select2 CSS -->
        <link href="{{ asset('backend/assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Flatpickr CSS (move local below) -->
        <link rel="stylesheet" href="{{ asset('backend/assets/libs/flatpickr/flatpickr.min.css') }}">

        <!-- Toastr CSS (move local below) -->
        <link rel="stylesheet" href="{{ asset('backend/assets/libs/toastr/toastr.min.css') }}">

        <!-- Custom CSS (ALWAYS LAST so it wins) -->
        <link href="{{ asset('backend/assets/css/custom.css') }}" id="custom-style" rel="stylesheet" type="text/css" />

        <style>
            .swal2-superpopup {
                z-index: 999999 !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
              #aiChatPanel, #aiChatPanel * { pointer-events: auto !important; }
              #aiChatFrame { pointer-events: auto !important; }
        </style>
        @stack('styles')


    </head>

    <body>

    <!-- <body data-layout="horizontal"> -->

        <!-- Begin page -->
        <div id="layout-wrapper">


            @include('admin.body.header')

            <!-- ========== Left Sidebar Start ========== -->
            @include('admin.body.sidebar')
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

            @yield('admin')
            <!-- End Page-content -->


            @include('admin.body.footer')
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->


        <!-- Right Sidebar -->
        @include('admin.body.right_sidebar')

        <!-- JAVASCRIPT (Core first) -->
        <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js') }}"></script>

        <!-- Select2 -->
        <script src="{{ asset('backend/assets/libs/select2/select2.min.js') }}"></script>

        <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/metismenu/metisMenu.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js') }}"></script>

        <!-- Optional libs -->
        <script src="{{ asset('backend/assets/libs/pace-js/pace.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

        <!-- Plugins (depend on jQuery) -->
        <script src="{{ asset('backend/assets/libs/jquery-mask/jquery.mask.min.js') }}"></script>

        <script src="{{ asset('backend/assets/libs/toastr/toastr.min.js') }}"></script>

        <!-- Flatpickr (no jQuery dependency) -->
        <script src="{{ asset('backend/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
        {{-- if you use monthSelect plugin --}}
        <script src="{{ asset('backend/assets/libs/flatpickr/plugins/monthSelect/index.js') }}"></script>

        <!-- DataTables -->
        <script src="{{ asset('backend/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('backend/assets/js/pages/datatables.init.js') }}"></script>

        <!-- Vector Maps -->
        <script src="{{ asset('backend/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js') }}"></script>

        <!-- Page inits (only if used on that page; safe to keep for now) -->
        <script src="{{ asset('backend/assets/js/pages/dashboard.init.js') }}"></script>
        <script src="{{ asset('backend/assets/js/pages/form-validation.init.js') }}"></script>

        <!-- SweetAlert2 -->
        <script src="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

        @include('partials.sweetalert')

        <!-- App Main JS -->
        <script src="{{ asset('backend/assets/js/app.js') }}"></script>

        {{-- Page specific scripts --}}
        @yield('scripts')

<script>

 function bindDateTimePicker(selector){
    flatpickr(selector, { enableTime:true, time_24hr:false, dateFormat:'m/d/Y H:i' });
    }

 @if(Session::has('message'))
 var type = "{{ Session::get('alert-type','info') }}"
 switch(type){
    case 'info':
    toastr.info(" {{ Session::get('message') }} ");
    break;

    case 'success':
    toastr.success(" {{ Session::get('message') }} ");
    break;

    case 'warning':
    toastr.warning(" {{ Session::get('message') }} ");
    break;

    case 'error':
    toastr.error(" {{ Session::get('message') }} ");
    break;
 }
 @endif

   function initAccountmasterSelect($el){
        $el.select2({
            placeholder: 'Search Account (Code or Name)',
            allowClear: true,
            minimumInputLength: 1,
            width: '100%',
            ajax: {
            url: "{{ route('lookup.accountmaster') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => data,
            cache: true
            }
        });
    }

 // Set the session timeout in milliseconds (5 minutes = 300000 ms)
    const SESSION_LIFETIME = {{ config('session.lifetime') }};
    const sessionTimeout = (SESSION_LIFETIME - 3) * 60 * 1000; // e.g., for 5, this is 4 minutes
    const countdownSeconds = 180; // 60 second warning
    let sessionTimer;
    let countdownTimer;

    function resetSessionTimer() {
        clearTimeout(sessionTimer);
        clearInterval(countdownTimer);

        sessionTimer = setTimeout(function() {
            let remainingTime = countdownSeconds;

            // Show initial Toastr message with countdown
            toastr.info(`
                <div>You will be automatically logged off in <span id="countdown">${remainingTime}</span> seconds due to inactivity.</div>
                <div style="margin-top: 10px;">
                    <button onclick="stayLoggedIn()" class="btn btn-success btn-sm">Stay Logged In</button>
                </div>
            `, "Session Expired", {
                timeOut: (countdownSeconds + 5) * 1000,
                extendedTimeOut: 0,
                closeButton: false,
                allowHtml: true,
                tapToDismiss: false,
                onHidden: function() {
                    window.location.href = "{{ route('login') }}";
                }
            });

            // Start the countdown timer
            countdownTimer = setInterval(() => {
                remainingTime--;
                if (remainingTime <= 0) {
                    clearInterval(countdownTimer);
                    window.location.href = "{{ route('login') }}";
                } else {
                    document.getElementById('countdown').innerText = remainingTime;
                }
            }, 1000);

        }, sessionTimeout);
    }

    function stayLoggedIn() {
        toastr.clear();
        clearTimeout(sessionTimer);
        clearInterval(countdownTimer);
        resetSessionTimer();
        toastr.success('Session has been extended!', 'Success');

        $.ajax({
            url: "{{ route('session.extend') }}",
            type: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                console.log("Session successfully extended on server:", response.message);
            },
            error: function(xhr) {
                console.error("Failed to extend session on server:", xhr.responseText);
                toastr.error("Session extension failed! Please try again.", "Error");
            }
        });
    }

   // Event Listeners for User Activity
    window.onload = resetSessionTimer;
    window.onmousemove = resetSessionTimer;
    window.onmousedown = resetSessionTimer;
    window.ontouchstart = resetSessionTimer;
    window.onclick = resetSessionTimer;
    window.onkeypress = resetSessionTimer;
    window.onscroll = resetSessionTimer;


    // <<< -------------------------------------------
// <<< ADD NEW AUTO-TAB + HASH SCRIPT HERE
// <<< -------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
  /*
    // STEP 1: Auto-hide empty tabs + tab panes
    document.querySelectorAll('.nav-tabs').forEach(function (tabGroup) {

        const tabContent = tabGroup.closest('.card-body, .container, body')
                                   .querySelector('.tab-content');
        if (!tabContent) return;

        const panes = tabContent.querySelectorAll('.tab-pane');
        let firstVisibleTabId = null;

        panes.forEach(function (pane) {
            const html = pane.innerHTML.trim();
            const isEmpty =
                html === '' ||
                html === '<!-- -->' ||
                html.replace(/<[^>]*>/g, '').trim() === '';

            const paneId = pane.getAttribute('id');
            if (!paneId) return;

            const tabLink = tabGroup.querySelector('a[href="#' + paneId + '"]');
            if (!tabLink) return;

            if (isEmpty) {
                const li = tabLink.closest('.nav-item');
                if (li) li.style.display = 'none';
                pane.style.display = 'none';
            } else {
                if (!firstVisibleTabId) {
                    firstVisibleTabId = paneId;
                }
            }
        });

        // STEP 2: Activate first visible tab
        if (firstVisibleTabId) {
            tabGroup.querySelectorAll('.nav-link').forEach(a => a.classList.remove('active'));
            tabContent.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active', 'show'));

            const firstLink = tabGroup.querySelector('a[href="#' + firstVisibleTabId + '"]');
            const firstPane = tabContent.querySelector('#' + firstVisibleTabId);

            if (firstLink && firstPane) {
                firstLink.classList.add('active');
                firstPane.classList.add('active', 'show');
            }
        }
    });
    */

    //
    initAccountmasterSelect($('.js-accountmaster'));

     // ✅ IMPORTANT: make Select2 show preloaded selected option on edit screens
    $('.js-accountmaster').each(function () {
        const $sel = $(this);
        if ($sel.find('option:selected').length) {
        $sel.trigger('change');   // <-- THIS fixes edit display
        }
    });


    // STEP 3: Activate tab via URL hash
    var hash = window.location.hash;
    if (hash) {
        const triggerEl = document.querySelector('a[data-bs-toggle="tab"][href="' + hash + '"]');
        if (triggerEl && triggerEl.offsetParent !== null) {
            var tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }
});
// <<< -------------------------------------------
// <<< END OF NEW SCRIPT
// <<< -------------------------------------------
</script>

  <!-- Floating Chat Button -->
  <div id="aiChatBtn"
       style="
         position:fixed;
         right:20px;
         bottom:20px;
         z-index:999999;
         width:56px;
         height:56px;
         border-radius:50%;
         background:#0d6efd;
         color:#fff;
         display:flex;
         align-items:center;
         justify-content:center;
         cursor:pointer;
         box-shadow:0 4px 12px rgba(0,0,0,.2);
       "
       title="Ask Data">
    💬
  </div>

  <!-- Slide-in Chat Panel -->
  <div id="aiChatPanel"
       style="
         position:fixed;
         top:0;
         right:-420px;
         width:400px;
         height:100vh;
         background:#fff;
         border-left:1px solid #ddd;

         z-index:2147483647;
         pointer-events:auto;
         transition:right .25s ease;
         display:flex;
         flex-direction:column;
       ">

    <div class="p-2 border-bottom d-flex justify-content-between align-items-center bg-light">
      <strong>Member Assistant</strong>
      <a href="{{ route('chat.full') }}"
         class="btn btn-sm btn-outline-primary ms-2">
        Full Screen
    </a>
      <button id="aiChatClose" class="btn btn-sm btn-outline-secondary">✖</button>
    </div>

    <iframe id="aiChatFrame"
        src="{{ route('chat.index', ['embed' => 1]) }}"

        style="flex:1;border:0;width:100%;height:100%;display:block;pointer-events:auto;"></iframe>
  </div>

  <script>
    (function(){
    const btn   = document.getElementById('aiChatBtn');
    const panel = document.getElementById('aiChatPanel');
    const close = document.getElementById('aiChatClose');
    const frame = document.getElementById('aiChatFrame');

    if(!btn || !panel) return;

     // ✅ MOVE TO BODY to escape dashboard stacking contexts
  document.body.appendChild(panel);
  document.body.appendChild(btn);

  // ✅ Force clickability + top layer
  btn.style.zIndex   = '2147483646';
  panel.style.zIndex = '2147483647';
  panel.style.pointerEvents = 'auto';
  if(frame) frame.style.pointerEvents = 'auto';

    btn.addEventListener('click', () => {
        panel.style.right = '0';
    });

    function closeChat(){
        panel.style.right = '-420px';

        // ✅ Reset chat contents
        if(frame){
        frame.src = frame.src;   // reload iframe
        }
    }

    close && close.addEventListener('click', closeChat);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeChat();
    });

    })();

  </script>

</body>

</html>
