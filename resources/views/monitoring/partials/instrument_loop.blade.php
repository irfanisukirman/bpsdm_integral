@foreach($questions as $category => $items)
<div class="card mb-4 border-start border-primary border-3 shadow-sm question-card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 text-primary fw-bold">
            <i class="bx bx-list-check me-2"></i>KATEGORI: {{ strtoupper($category) }}
        </h6>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50%">Indikator / Pertanyaan</th>
                    <th class="text-center">Jawaban</th>
                    <th>Temuan & Tindak Lanjut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $q)
                @php 
                    $currentStageId = ($stage_id ?? 'std');
                    $uniqueId = $currentStageId . '_' . $q->id;

                    // AMBIL DATA LAMA DARI DATABASE (Sticky Value)
                    $existingRes = $training->monitoringResults
                        ->where('training_stage_id', $currentStageId == 'std' ? null : $currentStageId)
                        ->where('question_id', $q->id)
                        ->first();
                @endphp
                <tr>
                    <td class="text-wrap small text-dark">{{ $q->question_text }}</td>
                    <td class="text-center">
                        <select name="ans[{{ $q->id }}]" class="form-select form-select-sm select-ans w-auto mx-auto border-primary" data-target="fu-box-{{ $uniqueId }}" required>
                            <option value="ya" {{ optional($existingRes)->answer == 'ya' ? 'selected' : '' }}>YA</option>
                            <option value="tidak" {{ optional($existingRes)->answer == 'tidak' ? 'selected' : '' }}>TIDAK</option>
                        </select>
                    </td>
                    <td>
                        {{-- Box Tindak Lanjut otomatis terbuka jika data lama adalah 'tidak' --}}
                        <div id="fu-box-{{ $uniqueId }}" style="display: {{ optional($existingRes)->answer == 'tidak' ? 'block' : 'none' }};">
                            <select name="target[{{ $q->id }}]" class="form-select form-select-sm mb-1 border-warning">
                                <option value="">-- Pilih Penyelenggara Tujuan --</option>
                                @foreach($organizers as $org)
                                    <option value="{{ $org->bidang }}" {{ optional($existingRes)->follow_up_target == $org->bidang ? 'selected' : '' }}>
                                        {{ $org->bidang }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="notes[{{ $q->id }}]" class="form-control form-control-sm" value="{{ $existingRes->notes ?? '' }}" placeholder="Tuliskan temuan di sini...">
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @php
        // PRE-FILL KESIMPULAN KATEGORI
        $existingCatSummary = $training->summaries
            ->where('training_stage_id', ($stage_id ?? 'std') == 'std' ? null : ($stage_id ?? 'std'))
            ->where('category', $category)
            ->first();
    @endphp
    <div class="card-footer bg-label-secondary py-2 border-top">
        <label class="form-label fw-bold text-dark small text-uppercase">
            Kesimpulan Kategori {{ $category }} ({{ $stage_name }})
        </label>
        <textarea name="category_conclusions[{{ $category }}]" class="form-control border-primary bg-white" rows="2" placeholder="Tuliskan ringkasan untuk kategori ini..." required>{{ $existingCatSummary->conclusion ?? '' }}</textarea>
    </div>
</div>
@endforeach

@php
    // PRE-FILL KESIMPULAN FINAL TAHAPAN
    $existingFinalSummary = $training->summaries
        ->where('training_stage_id', ($stage_id ?? 'std') == 'std' ? null : ($stage_id ?? 'std'))
        ->where('category', 'STAGE_FINAL_SUMMARY')
        ->first();
@endphp
<div class="card border-top border-danger border-3 shadow mb-4">
    <div class="card-body">
        <h5 class="text-danger fw-bold"><i class="bx bx-check-double me-2"></i>KESIMPULAN KESELURUHAN ({{ strtoupper($stage_name) }})</h5>
        <textarea name="final_conclusion" class="form-control bg-white border-danger" rows="4" placeholder="Tuliskan narasi kesimpulan akhir dari seluruh pemantauan di tahap ini..." required>{{ $existingFinalSummary->conclusion ?? '' }}</textarea>
    </div>
    <div class="card-footer bg-light border-top">
        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold">
            <i class="bx bx-save me-1"></i> SIMPAN TAHAPAN {{ strtoupper($stage_name) }}
        </button>
    </div>
</div>