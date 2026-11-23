(() => {
    const picker = document.getElementById('profilePhotoPicker');
    const upload = document.getElementById('profilePhotoUpload');
    const choose = document.getElementById('chooseProfilePhoto');
    const avatar = document.getElementById('uploadedAvatar');
    const placeholder = document.getElementById('profileAvatarPlaceholder');
    const status = document.getElementById('profilePhotoStatus');
    const modalElement = document.getElementById('profilePhotoCropModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const canvas = document.getElementById('profilePhotoCanvas');
    const context = canvas.getContext('2d');
    const zoom = document.getElementById('profilePhotoZoom');
    const horizontal = document.getElementById('profilePhotoX');
    const vertical = document.getElementById('profilePhotoY');
    const apply = document.getElementById('applyProfilePhoto');
    const cropError = document.getElementById('profilePhotoCropError');
    let image = null;
    let previewUrl = null;

    function drawCrop() {
        if (!image) return;
        const size = canvas.width;
        const scale = Math.max(size / image.naturalWidth, size / image.naturalHeight) * Number(zoom.value) / 100;
        const width = image.naturalWidth * scale;
        const height = image.naturalHeight * scale;
        const x = (size - width) * (1 - Number(horizontal.value) / 100) / 2;
        const y = (size - height) * (1 - Number(vertical.value) / 100) / 2;

        context.fillStyle = '#fff';
        context.fillRect(0, 0, size, size);
        context.drawImage(image, x, y, width, height);
    }

    choose.addEventListener('click', () => picker.click());

    picker.addEventListener('change', async () => {
        const [file] = picker.files;
        picker.value = '';
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            status.className = 'small mb-0 mt-1 text-danger';
            status.textContent = 'Pilih file JPG, PNG, atau WebP maksimal 5 MB.';
            return;
        }

        choose.disabled = true;
        const source = URL.createObjectURL(file);
        try {
            const candidate = new Image();
            candidate.src = source;
            await candidate.decode();
            image = candidate;
            zoom.value = '100';
            horizontal.value = '0';
            vertical.value = '0';
            cropError.textContent = '';
            apply.disabled = false;
            drawCrop();
            modal.show(choose);
        } catch {
            status.className = 'small mb-0 mt-1 text-danger';
            status.textContent = 'Foto tidak dapat dibuka. Pilih file gambar lain.';
        } finally {
            URL.revokeObjectURL(source);
            choose.disabled = false;
        }
    });

    [zoom, horizontal, vertical].forEach(control => control.addEventListener('input', drawCrop));

    apply.addEventListener('click', () => {
        if (!image) return;
        const selectedImage = image;
        apply.disabled = true;
        cropError.textContent = '';

        canvas.toBlob(blob => {
            if (image !== selectedImage) return;
            try {
                if (!blob) throw new Error();
                const files = new DataTransfer();
                files.items.add(new File([blob], 'foto-profil.jpg', { type: 'image/jpeg' }));
                upload.files = files.files;

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(blob);
                avatar.src = previewUrl;
                avatar.hidden = false;
                if (placeholder) placeholder.hidden = true;
                status.className = 'small mb-0 mt-1 text-success';
                status.textContent = 'Pratinjau foto baru. Klik Simpan Perubahan untuk menyimpannya.';
                modal.hide();
            } catch {
                cropError.textContent = 'Potongan foto tidak dapat disiapkan. Silakan coba lagi.';
                apply.disabled = false;
            }
        }, 'image/jpeg', 0.92);
    });

    modalElement.addEventListener('hide.bs.modal', () => { image = null; });
    modalElement.addEventListener('hidden.bs.modal', () => choose.focus());
})();
