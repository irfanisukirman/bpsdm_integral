    const addressInput = document.getElementById('addressSearch');
    const addressButton = document.getElementById('searchAddressButton');
    const addressResults = document.getElementById('addressSearchResults');
    const addressSearchUrl = @json(route('profile.address-search'));
    const selectedAddressArea = id => document.getElementById(id)?.value || '';

    async function searchProfileAddress() {
        const query = addressInput.value.trim();
        if (query.length < 3) {
            addressInput.setCustomValidity('Masukkan minimal 3 karakter alamat.');
            addressInput.reportValidity();
            return;
        }

        addressInput.setCustomValidity('');
        addressButton.disabled = true;
        addressButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mencari';
        addressResults.classList.remove('d-none');
        addressResults.innerHTML = '<div class="list-group-item text-muted">Mencari alamat...</div>';

        try {
            const area = [
                selectedAddressArea('kelurahan'),
                selectedAddressArea('kecamatan'),
                selectedAddressArea('kabupaten') || selectedAddressArea('kota'),
                selectedAddressArea('provinsi'),
                'Indonesia'
            ].filter(Boolean).join(', ');
            const params = new URLSearchParams({q: query + (area ? ', ' + area : '')});
            const response = await fetch(addressSearchUrl + '?' + params.toString(), {headers:{Accept:'application/json'}});
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Pencarian alamat gagal.');

            addressResults.innerHTML = '';
            if (!payload.data.length) {
                addressResults.innerHTML = '<div class="list-group-item text-muted"><i class="bx bx-info-circle me-1"></i>Alamat tidak ditemukan. Perjelas kata pencarian atau pilih titik pada peta.</div>';
                return;
            }

            payload.data.forEach(result => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'list-group-item list-group-item-action text-start';
                const title = document.createElement('strong');
                title.className = 'd-block';
                title.textContent = result.name;
                const detail = document.createElement('small');
                detail.className = 'text-muted';
                detail.textContent = result.display_name;
                option.append(title, detail);
                option.addEventListener('click', () => {
                    addressInput.value = result.display_name;
                    setLocationPoint(result.lat, result.lon);
                    addressResults.classList.add('d-none');
                    coordinateStatus.className = 'small mt-2 text-success';
                    coordinateStatus.innerHTML = '<i class="bx bx-check-circle me-1"></i>Alamat dipilih dan titik lokasi tersimpan.';
                });
                addressResults.appendChild(option);
            });
        } catch (error) {
            addressResults.innerHTML = '<div class="list-group-item text-danger"><i class="bx bx-error-circle me-1"></i>' + error.message + '</div>';
        } finally {
            addressButton.disabled = false;
            addressButton.innerHTML = '<i class="bx bx-search me-1"></i>Cari Alamat';
        }
    }

    addressButton.addEventListener('click', searchProfileAddress);
    addressInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchProfileAddress();
        }
    });
