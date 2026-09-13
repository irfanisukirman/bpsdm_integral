<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | INTEGRAL - Pengelolaan Pelatihan Terintegrasi</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/inte.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <style>
        .table-responsive {
            overflow: inherit !important; /* Mencegah dropdown terpotong di dalam tabel responsive */
        }
        .dropdown-header {
            text-transform: uppercase;
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding-top: 10px;
            color: #a1acb8 !important;
        }
        .integral-confirm-modal .modal-content{border:0;border-radius:1.25rem;box-shadow:0 1.25rem 4rem rgba(34,48,62,.22)}.integral-confirm-modal .modal-body{padding:2rem}.integral-confirm-icon{width:4.5rem;height:4.5rem;margin:0 auto 1.15rem;border-radius:50%;display:grid;place-items:center;font-size:2.1rem;background:rgba(105,108,255,.12);color:#696cff}.integral-confirm-modal.is-danger .integral-confirm-icon{background:rgba(255,62,29,.12);color:#ff3e1d}.integral-confirm-message{color:#697a8d;white-space:pre-line;line-height:1.65}
    </style>

    @stack('css')

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- Menu / Sidebar -->
            @include('layouts.sidebar')

            <!-- Layout page -->
            <div class="layout-page">
                
                <!-- Navbar -->
                @include('layouts.navbar')

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                © {{ date('Y') }} <strong>Integral</strong> - BPSDM Provinsi Jawa Barat
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <div class="modal fade integral-confirm-modal" id="integralConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-body text-center"><div class="integral-confirm-icon"><i class="bx bx-help-circle"></i></div><h5 class="mb-2" id="integralConfirmTitle">Konfirmasi Tindakan</h5><p class="integral-confirm-message mb-4" id="integralConfirmMessage"></p><div class="d-flex gap-2 justify-content-center"><button type="button" class="btn btn-label-secondary flex-fill" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary flex-fill" id="integralConfirmAction">Ya, Lanjutkan</button></div></div></div></div></div>

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('js')

    
    <script>

        window.IntegralConfirm=(()=>{let modal,resolver;function ask(message,options={}){const text=String(message||'Apakah Anda yakin ingin melanjutkan?'),danger=options.danger??/hapus|tolak|batalkan|cabut|permanen/i.test(text),el=document.getElementById('integralConfirmModal');modal=modal||bootstrap.Modal.getOrCreateInstance(el);el.classList.toggle('is-danger',danger);el.querySelector('.integral-confirm-icon i').className=danger?'bx bx-trash':'bx bx-help-circle';document.getElementById('integralConfirmTitle').textContent=options.title||'Konfirmasi Tindakan';document.getElementById('integralConfirmMessage').textContent=text;const action=document.getElementById('integralConfirmAction');action.textContent=options.confirmText||'Ya, Lanjutkan';action.className='btn flex-fill '+(danger?'btn-danger':'btn-primary');return new Promise(resolve=>{if(resolver)resolver(false);resolver=resolve;modal.show()})}document.getElementById('integralConfirmAction').addEventListener('click',()=>{const done=resolver;resolver=null;modal.hide();if(done)done(true)});document.getElementById('integralConfirmModal').addEventListener('hidden.bs.modal',()=>{const done=resolver;resolver=null;if(done)done(false)});return{ask}})();
        (()=>{const bypass=new WeakSet();function message(code){if(!code||!code.includes('confirm('))return null;const match=code.match(/confirm\(\s*(['"`])([\s\S]*?)\1\s*\)/);return match?match[2].replace(/\\n/g,'\n'):null}document.addEventListener('click',async event=>{const el=event.target.closest('[onclick*="confirm("]');if(!el||bypass.has(el)){if(el)bypass.delete(el);return}const text=message(el.getAttribute('onclick'));if(!text)return;event.preventDefault();event.stopImmediatePropagation();if(await IntegralConfirm.ask(text)){const handler=el.getAttribute('onclick');el.removeAttribute('onclick');el.click();setTimeout(()=>el.setAttribute('onclick',handler),0)}},true);document.addEventListener('submit',async event=>{const form=event.target;if(bypass.has(form)){bypass.delete(form);return}const text=message(form.getAttribute('onsubmit'));if(!text)return;event.preventDefault();event.stopImmediatePropagation();if(await IntegralConfirm.ask(text)){const handler=form.getAttribute('onsubmit');form.removeAttribute('onsubmit');form.requestSubmit?form.requestSubmit(event.submitter||undefined):form.submit();setTimeout(()=>form.setAttribute('onsubmit',handler),0)}},true)})();

        function copyText(id) {
            var copyText = document.getElementById(id);
            copyText.select();
            document.execCommand("copy");
            alert("Link berhasil disalin!");
        }
        // Menampilkan SweetAlert untuk pesan sukses
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
        
    </script>
        
</body>
</html>
