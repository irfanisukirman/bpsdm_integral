@php
if(!isset($widgetServices)){ $widgetServices=\App\Models\TicketService::where('is_active',true)->orderBy('sort_order')->get(); }
if(!isset($widgetCategories)){ $widgetCategories=\App\Models\TicketCategory::where('is_active',true)->orderBy('sort_order')->get(); }
if(!isset($widgetPerangkatDaerah)){ $widgetPerangkatDaerah=config('wilayah.perangkat_daerah',[]); }
if(!isset($widgetAvailability)){ $widgetAvailability=\App\Models\HotlineAvailabilitySetting::current()->statusInfo(); }
@endphp
<div class="hotline-widget" id="hotlineWidget">
    <div class="hotline-panel" id="hotlinePanel" aria-hidden="true">
        <div class="hotline-head">
            <div class="d-flex align-items-center gap-2">
                <span class="hotline-avatar"><i class="bx bx-support"></i></span>
                <div>
                    <div class="fw-bold hotline-title">Hotline Integral</div>
                    <div class="hotline-sub">BPSDM Provinsi Jawa Barat</div>
                    <div class="hotline-presence @if($widgetAvailability['online']) is-online @endif" id="hotlinePresence">
                        <span class="hotline-presence-dot"></span>
                        <span class="hotline-presence-label">{{ $widgetAvailability['label'] }}</span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn hotline-close" id="hotlineClose" aria-label="Tutup"><i class="bx bx-x"></i></button>
        </div>
        <div class="hotline-body" id="hotlineBody">
            <div class="hotline-msg msg-bot">Halo! 👋 Ada kendala atau pertanyaan? Ceritakan aduan Anda di bawah ini.</div>
            @if(!$widgetAvailability['online'])
            <div class="hotline-msg small-msg">⏰ Sedang di luar jam layanan ({{ $widgetAvailability['days'] }}, {{ $widgetAvailability['hours'] }}). Aduan tetap diterima dan akan ditindaklanjuti pada jam kerja berikutnya.</div>
            @endif
        </div>
        <form id="hotlineForm" class="hotline-form" enctype="multipart/form-data">
            @csrf
            <div class="hotline-fields" id="hotlineFields">

                <div class="form-floating hotline-field">
                    <select name="user_type" id="hwUserType" class="form-select form-select-sm">
                        <option value="">-- Jenis Pengguna --</option>
                        <option value="PNS">PNS</option>
                        <option value="PPPK">PPPK</option>
                        <option value="Non-ASN">Non-ASN</option>
                    </select>
                    <label for="hwUserType">Jenis Pengguna <b class="text-danger">*</b></label>
                </div>
                <div class="form-floating hotline-field">
                    <input type="text" name="name" id="hwName" class="form-control" value="{{ auth()->check()?auth()->user()->name:'' }}" placeholder=" ">
                    <label for="hwName">Nama Lengkap <b class="text-danger">*</b></label>
                </div>
                <div class="form-floating hotline-field">
                    <input type="text" name="nip_nik" id="hwNip" class="form-control" placeholder=" ">
                    <label for="hwNip" id="hwNipLabel">NIP <b class="text-danger">*</b></label>
                </div>
                <div class="form-floating hotline-field">
                    <select name="perangkat_daerah" id="hwPerangkat" class="form-select form-select-sm">
                        <option value="">-- Pilih Perangkat Daerah --</option>
                        @foreach($widgetPerangkatDaerah as $pd)<option value="{{ $pd }}" @selected(auth()->check() && auth()->user()->instansi === $pd)>{{ $pd }}</option>@endforeach
                    </select>
                    <label for="hwPerangkat">Perangkat Daerah <b class="text-danger">*</b></label>
                </div>
                <div class="form-floating hotline-field">
                    <input type="email" name="email" id="hwEmail" class="form-control" placeholder=" ">
                    <label for="hwEmail">Email <b class="text-danger">*</b></label>
                </div>
                <div class="form-floating hotline-field">
                    <select name="service" id="hwService" class="form-select form-select-sm">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($widgetServices as $service)<option value="{{ $service->slug }}">{{ $service->name }}</option>@endforeach
                    </select>
                    <label for="hwService">Layanan <b class="text-danger">*</b></label>
                </div>
                <div class="form-floating hotline-field">
                    <select name="category" id="hwCategory" class="form-select form-select-sm">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($widgetCategories as $category)<option value="{{ $category->slug }}">{{ $category->name }}</option>@endforeach
                    </select>
                    <label for="hwCategory">Kategori Aduan <b class="text-danger">*</b></label>
                </div>
                <div class="form-floating hotline-field">
                    <textarea name="message" id="hwMessage" class="form-control" style="height:80px" placeholder=" "></textarea>
                    <label for="hwMessage">Isi Aduan <b class="text-danger">*</b></label>
                </div>
                <div class="hotline-field">
                    <label class="hotline-file">
                        <i class="bx bx-image"></i><span id="hwFileName">Lampiran screenshot (opsional)</span>
                        <input type="file" name="attachment" id="hwAttachment" accept="image/*">
                    </label>
                </div>
                <div class="text-danger small mb-1" id="hwError" style="display:none"></div>
                <div class="hotline-info" id="hotlineInfo">
                    @if($widgetAvailability['online'])
                        <i class="bx bx-info-circle me-1"></i>Admin sedang online — aduan akan diproses segera. Bukan live chat, jawaban dikirim via email.
                    @else
                        <i class="bx bx-info-circle me-1"></i>Di luar jam layanan ({{ $widgetAvailability['days'] }}, {{ $widgetAvailability['hours'] }}) — aduan tetap diterima dan diproses di jam berikutnya.
                    @endif
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 hotline-send" id="hwSubmit"><span class="hotline-send-label"><i class="bx bx-send me-1"></i>Kirim Aduan</span><span class="spinner-border spinner-border-sm hotline-btn-spinner" aria-hidden="true"></span></button>
        </form>
        <div class="hotline-done" id="hotlineDone" style="display:none">
            <div class="hotline-msg msg-bot">✅ Aduan berhasil dikirim.</div>
        </div>
    </div>
    <button type="button" class="hotline-fab" id="hotlineFab" aria-label="Buka Hotline">
        <i class="bx bx-support"></i><span class="hotline-fab-tooltip">Hotline</span>
    </button>
</div>

<style>
.hotline-fab{position:fixed;right:22px;bottom:22px;z-index:1050;width:58px;height:58px;border:none;border-radius:50% 50% 50% 12%;display:grid;place-items:center;background:linear-gradient(135deg,#696cff,#30336b);color:#fff;font-size:1.7rem;box-shadow:0 .75rem 2rem rgba(48,51,107,.45);cursor:pointer;transition:transform .2s ease}
.hotline-fab:hover{transform:translateY(-3px)}
.hotline-fab-tooltip{position:absolute;right:calc(100% + 10px);top:50%;transform:translateY(-50%);background:#30336b;color:#fff;font-size:.72rem;padding:.3rem .65rem;border-radius:.5rem;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .2s ease}
.hotline-fab:hover .hotline-fab-tooltip{opacity:1}
.hotline-fab.is-open{background:#ff3e1d}
.hotline-panel{display:none;flex-direction:column;position:fixed;right:22px;bottom:96px;z-index:1060;width:min(390px,calc(100vw - 32px));height:min(600px,calc(100vh - 130px));background:#fff;border-radius:1rem;box-shadow:0 1.25rem 3.5rem rgba(34,48,62,.28);overflow:hidden}
.hotline-panel.is-open{display:flex;animation:hotlinePop .25s ease}
@keyframes hotlinePop{from{opacity:0;transform:translateY(16px) scale(.98)}to{opacity:1;transform:none}}
.hotline-head{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;color:#fff;background:linear-gradient(135deg,#696cff,#30336b)}
.hotline-avatar{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:1.35rem}
.hotline-title{font-size:.95rem;line-height:1.1}
.hotline-sub{font-size:.72rem;opacity:.85}
.hotline-presence{display:inline-flex;align-items:center;gap:.35rem;margin-top:.4rem;padding:.15rem .55rem;border-radius:999px;background:rgba(255,255,255,.14);font-size:.7rem;line-height:1.4}
.hotline-presence-dot{width:8px;height:8px;border-radius:50%;background:#ff3e1d;box-shadow:0 0 0 3px rgba(255,62,29,.25);flex:0 0 auto}
.hotline-presence.is-online .hotline-presence-dot{background:#58e07f;box-shadow:0 0 0 3px rgba(88,224,127,.25);animation:hotlinePulse 2s infinite}
.hotline-presence .hotline-presence-label{white-space:nowrap}
@keyframes hotlinePulse{0%,100%{opacity:1}50%{opacity:.45}}
.hotline-close{color:#fff;font-size:1.3rem;padding:0 .25rem;line-height:1}
.hotline-close:hover{color:#fff;opacity:.8}
.hotline-body{flex:0 0 auto;overflow-y:auto;padding:1rem;background:#f4f6fb;display:flex;flex-direction:column;gap:.5rem}
.hotline-msg{max-width:88%;padding:.65rem .9rem;border-radius:1rem;font-size:.85rem;line-height:1.5;box-shadow:0 1px 2px rgba(34,48,62,.08)}
.hotline-msg.msg-bot{align-self:flex-start;background:#fff;border-bottom-left-radius:.25rem}
.hotline-msg.small-msg{font-size:.78rem;color:#697a8d;background:#eef1f9}
.hotline-form{border-top:1px solid #e8edf4;padding:.85rem;background:#fff;flex:1;display:flex;flex-direction:column;min-height:0}
.hotline-fields{display:flex;flex-direction:column;gap:.55rem;flex:1;min-height:0;overflow-y:auto;padding-right:2px}
.hotline-field .form-control,.hotline-field .form-select{font-size:.82rem;border-color:#e2e8f0;background:#f8fafc}
.hotline-field .form-select{padding-left:.875rem}
.hotline-field .form-control:focus,.hotline-field .form-select:focus{background:#fff}
.hotline-field label{font-size:.78rem}
.hotline-file{display:flex;align-items:center;gap:.5rem;padding:.55rem .8rem;border:1px dashed #c3cddb;border-radius:.6rem;font-size:.8rem;color:#697a8d;cursor:pointer;transition:all .2s ease}
.hotline-file:hover{border-color:#696cff;color:#696cff;background:#f3f4ff}
.hotline-file input[type=file]{display:none}
.hotline-info{font-size:.72rem;color:#a1acb8;margin:.25rem 0 .6rem}
.hotline-send{border-radius:.7rem;position:relative;min-height:2.5rem}
.hotline-send.is-loading .hotline-send-label{visibility:hidden}
.hotline-send .hotline-btn-spinner{display:none;position:absolute;left:50%;top:50%;width:1rem;height:1rem;margin:-.5rem 0 0 -.5rem;color:#fff}
.hotline-send.is-loading .hotline-btn-spinner{display:inline-block}
.hotline-done{padding:1rem;background:#f4f6fb}
@media(max-width:575.98px){.hotline-fab{width:54px;height:54px;right:16px;bottom:16px;font-size:1.5rem}.hotline-panel{right:16px;bottom:84px;width:calc(100vw - 32px)}}
</style>

<script>
(function(){
    var fab=document.getElementById('hotlineFab');
    var panel=document.getElementById('hotlinePanel');
    var close=document.getElementById('hotlineClose');
    var form=document.getElementById('hotlineForm');
    var body=document.getElementById('hotlineBody');
    var done=document.getElementById('hotlineDone');
    var errBox=document.getElementById('hwError');
    var userType=document.getElementById('hwUserType');
    var nipLabel=document.getElementById('hwNipLabel');
    var fileInput=document.getElementById('hwAttachment');
    var fileName=document.getElementById('hwFileName');

    function openWidget(){panel.classList.add('is-open');panel.setAttribute('aria-hidden','false');fab.classList.add('is-open');}
    function closeWidget(){panel.classList.remove('is-open');panel.setAttribute('aria-hidden','true');fab.classList.remove('is-open');}
    window.HotlineWidget={open:openWidget,close:closeWidget};
    fab.addEventListener('click',function(){panel.classList.contains('is-open')?closeWidget():openWidget();});
    close.addEventListener('click',closeWidget);

    userType.addEventListener('change',function(){
        nipLabel.innerHTML=(this.value==='Non-ASN'?'NIK':(this.value?'NIP':'NIP'))+' <b class="text-danger">*</b>';
        var max=this.value==='Non-ASN'?16:(this.value?18:30);
        document.getElementById('hwNip').setAttribute('maxlength',String(max));
    });
    fileInput.addEventListener('change',function(){fileName.textContent=fileInput.files[0]?fileInput.files[0].name:'Lampiran screenshot (opsional)';});

    function addMsg(text){
        var m=document.createElement('div');
        m.className='hotline-msg msg-bot';
        m.textContent=text;
        body.appendChild(m);
        body.scrollTop=body.scrollHeight;
    }

    form.addEventListener('submit',function(e){
        e.preventDefault();
        errBox.style.display='none';
        var data=new FormData(form);
        var btn=document.getElementById('hwSubmit');
        btn.disabled=true;
        btn.classList.add('is-loading');
        fetch('{{ route('hotline.store') }}',{
            method:'POST',
            body:data,
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?document.querySelector('meta[name="csrf-token"]').getAttribute('content'):'','Accept':'application/json'}
        }).then(function(r){return r.json().then(function(j){return {ok:r.ok,j:j};});}).then(function(res){
            if(res.ok){
                form.style.display='none';
                done.style.display='block';
                var m=document.createElement('div');
                m.className='hotline-msg msg-bot';
                m.innerHTML='🎉 Terima kasih! Aduan Anda tercatat dengan nomor tiket <b>'+res.j.ticket_number+'</b>. <a target="_blank" class="fw-bold" href="'+res.j.tracking_url+'">Lacak tiket</a>.';
                done.appendChild(m);
                body.scrollTop=body.scrollHeight;
            }else{
                btn.disabled=false;
                btn.classList.remove('is-loading');
                var msgs=res.j.errors?Object.values(res.j.errors).flat():[res.j.message||'Terjadi kesalahan. Silakan coba lagi.'];
                if(typeof msgs[0]==='string'){errBox.style.display='block';errBox.textContent=msgs[0];}
            }
        }).catch(function(){
            btn.disabled=false;
            btn.classList.remove('is-loading');
            addMsg('⚠️ Jaringan bermasalah. Silakan coba lagi.');
        });
    });
})();
</script>