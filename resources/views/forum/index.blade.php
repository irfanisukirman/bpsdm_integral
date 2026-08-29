@extends('layouts.master')
@section('title', 'Forum Pelatihan')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h4 class="fw-bold mb-1">Forum Pelatihan</h4><p class="text-muted mb-0">{{ $training->nama_pelatihan }} · Angkatan {{ $training->angkatan }}</p></div>
    <button class="btn btn-outline-secondary" onclick="history.back()"><i class="bx bx-arrow-back me-1"></i>Kembali</button>
</div>
<div class="card shadow-sm border-0 overflow-hidden">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <strong><i class="bx bx-conversation me-2"></i>Ruang Diskusi</strong>
        <small><span class="text-success">●</span> diperbarui otomatis</small>
    </div>
    <div id="forumMessages" class="card-body bg-light" style="height:58vh;min-height:390px;overflow-y:auto">
        <div id="forumLoading" class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span>Memuat percakapan...</div>
    </div>
    <div class="card-footer bg-white p-3">
        <form id="forumForm" class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label small fw-semibold">Tulis pesan</label>
                <textarea id="forumInput" class="form-control" rows="2" maxlength="2000" placeholder="Tulis pesan untuk peserta, pengajar, dan pengelola..." required></textarea>
                <small class="text-muted">Enter untuk mengirim, Shift+Enter untuk baris baru.</small>
            </div>
            <button id="forumSend" class="btn btn-primary px-4"><i class="bx bx-send me-1"></i>Kirim</button>
        </form>
        <div id="forumError" class="alert alert-danger py-2 mt-2 d-none"></div>
    </div>
</div>
@endsection
@push('js')
<script>
(function () {
    var list = document.getElementById('forumMessages');
    var form = document.getElementById('forumForm');
    var input = document.getElementById('forumInput');
    var send = document.getElementById('forumSend');
    var errorBox = document.getElementById('forumError');
    var messagesUrl = @json(route('training.forum.messages', $training));
    var storeUrl = @json(route('training.forum.store', $training));
    var deleteUrl = @json(route('training.forum.destroy', [$training, '__MESSAGE__']));
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var lastId = 0;
    var loading = false;

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }
    function renderMessage(message) {
        if (document.getElementById('forum-message-' + message.id)) return;
        var loadingNode = document.getElementById('forumLoading');
        if (loadingNode) loadingNode.remove();
        var wrapper = document.createElement('div');
        wrapper.id = 'forum-message-' + message.id;
        wrapper.className = 'd-flex mb-3 ' + (message.mine ? 'justify-content-end' : 'justify-content-start');
        var deleteButton = message.can_delete
            ? '<button class="btn btn-sm p-0 ' + (message.mine ? 'text-white' : 'text-danger') + '" data-delete="' + message.id + '"><i class="bx bx-trash"></i></button>'
            : '';
        wrapper.innerHTML = '<div style="max-width:min(78%,720px)">'
            + '<div class="small mb-1 ' + (message.mine ? 'text-end' : '') + '"><strong>' + escapeHtml(message.name) + '</strong>'
            + '<span class="badge bg-label-secondary ms-1">' + escapeHtml(message.role) + '</span></div>'
            + '<div class="p-3 rounded shadow-sm ' + (message.mine ? 'bg-primary text-white' : 'bg-white border') + '">'
            + '<div style="white-space:pre-wrap;word-break:break-word">' + escapeHtml(message.message) + '</div>'
            + '<div class="d-flex justify-content-between gap-3 mt-2"><small class="' + (message.mine ? 'opacity-75' : 'text-muted') + '">' + escapeHtml(message.time) + '</small>'
            + deleteButton + '</div></div></div>';
        list.appendChild(wrapper);
        lastId = Math.max(lastId, Number(message.id));
    }
    async function loadMessages(initial) {
        if (loading) return;
        loading = true;
        try {
            var response = await fetch(messagesUrl + '?after_id=' + (initial ? 0 : lastId), {headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('Percakapan tidak dapat dimuat.');
            var messages = await response.json();
            var nearBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 120;
            messages.forEach(renderMessage);
            if (initial || nearBottom) list.scrollTop = list.scrollHeight;
            if (initial && !messages.length) list.innerHTML = '<div class="text-center text-muted py-5"><i class="bx bx-message-rounded-dots fs-1 d-block"></i>Belum ada pesan. Mulai percakapan pertama.</div>';
        } catch (error) {
            errorBox.textContent = error.message;
            errorBox.classList.remove('d-none');
        } finally { loading = false; }
    }
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        var message = input.value.trim();
        if (!message) return;
        send.disabled = true;
        try {
            var response = await fetch(storeUrl, {method:'POST', headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({message:message})});
            if (!response.ok) throw new Error('Pesan gagal dikirim.');
            renderMessage(await response.json());
            input.value = '';
            list.scrollTop = list.scrollHeight;
        } catch (error) {
            errorBox.textContent = error.message;
            errorBox.classList.remove('d-none');
        } finally { send.disabled = false; input.focus(); }
    });
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); form.requestSubmit(); }
    });
    list.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-delete]');
        if (!button || !confirm('Hapus pesan ini?')) return;
        var id = button.dataset.delete;
        var response = await fetch(deleteUrl.replace('__MESSAGE__', id), {method:'DELETE', headers:{Accept:'application/json','X-CSRF-TOKEN':csrf}});
        if (response.ok) document.getElementById('forum-message-' + id).remove();
    });
    loadMessages(true);
    setInterval(function () { loadMessages(false); }, 5000);
})();
</script>
@endpush
