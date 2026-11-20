<div class="mb-3">
    <label for="complaint-message" class="form-label">{{ $messageLabel }}</label>
    <textarea id="complaint-message" name="message" class="form-control" rows="3" maxlength="5000"
        placeholder="Tulis pesan atau tempel gambar" data-complaint-image-paste>{{ old('message') }}</textarea>
    <div class="form-text">Jangan masukkan kata sandi atau kode OTP.</div>
</div>
<div class="mb-3">
    <label for="complaint-attachment" class="form-label">Lampirkan gambar</label>
    <input type="file" id="complaint-attachment" name="attachment" class="form-control" accept="image/jpeg,image/png,image/webp">
    <div class="form-text">Tempel gambar di kolom pesan atau pilih file JPG, PNG, atau WebP hingga 5 MB.</div>
    <div class="complaint-image-preview mt-2" data-complaint-image-preview hidden>
        <img alt="Pratinjau gambar yang akan dikirim">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-complaint-image-remove>Hapus Gambar</button>
    </div>
    <div class="small text-danger mt-1" data-complaint-image-error role="status"></div>
</div>
