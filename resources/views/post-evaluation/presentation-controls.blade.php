<header class="expose-topbar">
<div class="expose-topbar-main"><div class="expose-topbar-title"><span>RUANG EKSPOSE</span><strong>{{ $summary['training'] }}</strong></div><div class="expose-actions">
@if(!$public)<a href="{{ route('post-evaluation.show', request()->route('training')) }}">&larr; Hasil Evaluasi</a><a href="{{ route('post-evaluation.narrative.edit', request()->route('training')) }}">Edit Narasi</a>@endif
<a class="expose-download" href="{{ $public ? route('post-evaluation.public.pdf',request()->route('token')) : route('post-evaluation.pdf',request()->route('training')) }}">Unduh PDF &darr;</a>
</div></div>
<nav class="controls" aria-label="Kontrol ekspose"><div class="expose-slide-nav"><button id="previousSlide" type="button" aria-label="Slide sebelumnya">&larr;</button><select id="slideSelect" aria-label="Pilih halaman"></select><button id="nextSlide" type="button" aria-label="Slide berikutnya">&rarr;</button><span id="slidePosition" aria-live="polite"></span></div><div class="expose-view-actions"><button id="fullscreenSlide" type="button">Layar penuh</button><button type="button" onclick="window.print()">Cetak</button></div></nav>
<div class="expose-reading-progress" aria-hidden="true"><span id="exposeProgress"></span></div>
</header>
<p class="expose-narrative-status">{{ ($summary['manual_narrative'] ?? false) ? (($summary['narrative_stale'] ?? false) ? 'Data berubah: tinjau kembali narasi tersimpan' : 'Narasi hasil tinjauan tersimpan') : (($summary['ai_status'] ?? 'none')==='current' ? 'Menggunakan hasil Gemini tersimpan' : 'Ringkasan dasar dari data aplikasi') }}</p>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const slides=[...document.querySelectorAll('.slide')],select=document.getElementById('slideSelect');
 const previous=document.getElementById('previousSlide'),next=document.getElementById('nextSlide'),position=document.getElementById('slidePosition'),progress=document.getElementById('exposeProgress');
 let current=0,scheduled=false,presenting=false;
 const frames=slides.map((slide,i)=>{const frame=document.createElement('div');frame.className='slide-frame';frame.id='slide-'+(i+1);slide.before(frame);frame.appendChild(slide);const option=document.createElement('option');option.value=i;const title=slide.querySelector('.heading,.cover-title')?.textContent.trim()||'Ekspose';option.textContent=String(i+1).padStart(2,'0')+' / '+slides.length+' - '+title;select.appendChild(option);return frame;});
 const full=document.getElementById('fullscreenSlide');
 const resizeStage=()=>{
  if(!presenting)return;
  const toolbar=document.querySelector('.expose-topbar');
  const scale=Math.max(.1,Math.min((innerWidth-24)/1280,(innerHeight-toolbar.offsetHeight-24)/720));
  document.documentElement.style.setProperty('--presentation-scale',scale);
  document.documentElement.style.setProperty('--presentation-width',(1280*scale)+'px');
  document.documentElement.style.setProperty('--presentation-height',(720*scale)+'px');
  document.documentElement.style.setProperty('--presentation-top',Math.max(0,(innerHeight-toolbar.offsetHeight-720*scale)/2)+'px');
  const active=slides[current];
  active.querySelectorAll('.discussion,.conclusion').forEach(panel=>{
   panel.style.setProperty('--presentation-body-font','16px');
   let font=16;
   while(panel.scrollHeight>panel.clientHeight+2&&font>12){font-=.5;panel.style.setProperty('--presentation-body-font',font+'px');}
  });
 };
 const sync=()=>{
  select.value=current;position.textContent=(current+1)+' / '+slides.length;
  previous.disabled=current===0;next.disabled=current===slides.length-1;
  progress.style.width=((current+1)/slides.length*100)+'%';
  frames.forEach((frame,i)=>{frame.classList.toggle('is-current',i===current);if(presenting)frame.setAttribute('aria-hidden',String(i!==current));else frame.removeAttribute('aria-hidden');});
  requestAnimationFrame(resizeStage);
 };
 const go=i=>{current=Math.max(0,Math.min(slides.length-1,i));sync();if(!presenting)frames[current].scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'start'});};
 const setPresenting=value=>{
  presenting=value;document.body.classList.toggle('expose-presenting',value);
  full.textContent=value?'Keluar presentasi':'Layar penuh';full.setAttribute('aria-pressed',String(value));sync();
  if(!value)requestAnimationFrame(()=>frames[current].scrollIntoView({behavior:'instant',block:'start'}));
 };
 const exitPresentation=()=>{setPresenting(false);if(document.fullscreenElement)document.exitFullscreen?.().catch(()=>{});};
 previous.onclick=()=>go(current-1);next.onclick=()=>go(current+1);select.onchange=()=>go(Number(select.value));
 full.onclick=()=>{
  if(presenting){exitPresentation();return;}
  setPresenting(true);
  if(document.fullscreenEnabled)document.documentElement.requestFullscreen?.().then(resizeStage).catch(resizeStage);
 };
 document.addEventListener('fullscreenchange',()=>{if(!document.fullscreenElement&&presenting)setPresenting(false);else resizeStage();});
 window.addEventListener('resize',resizeStage);
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&presenting){event.preventDefault();exitPresentation();return;}if(event.target.closest('input,select,textarea')||(!presenting&&event.target.closest('button,a'))||event.altKey||event.ctrlKey||event.metaKey)return;if(event.key==='ArrowRight'||(presenting&&(event.key==='PageDown'||event.key===' '))){event.preventDefault();go(current+1);}if(event.key==='ArrowLeft'||(presenting&&event.key==='PageUp')){event.preventDefault();go(current-1);}});
 window.addEventListener('scroll',()=>{if(presenting||scheduled)return;scheduled=true;requestAnimationFrame(()=>{const top=document.querySelector('.expose-topbar').getBoundingClientRect().bottom+140;frames.forEach((frame,i)=>{if(frame.getBoundingClientRect().top<=top)current=i;});sync();scheduled=false;});},{passive:true});
 const toolbar=document.querySelector('.expose-topbar');
 const updateOffset=()=>document.documentElement.style.setProperty('--expose-toolbar-height',(toolbar.offsetHeight+24)+'px');
 if('ResizeObserver' in window)new ResizeObserver(updateOffset).observe(toolbar);
 window.addEventListener('resize',updateOffset);updateOffset();
 sync();
});
</script>
