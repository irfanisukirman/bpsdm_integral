<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BidangSettingController extends Controller
{
    public function index()
    {
        $references = Bidang::referenceColumns();
        $bidangs = Bidang::orderBy('id')->get()->each(fn ($bidang) => $bidang->setAttribute('usage_count', $bidang->usage($references)));

        return view('settings.bidang', compact('bidangs'));
    }

    public function update(Request $request, Bidang $bidang)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('bidang_units', 'name')->ignore($bidang->id), Rule::notIn(['Semua Bidang', 'Pengelola Aset'])]]);
        $references = Bidang::referenceColumns();
        DB::transaction(function () use ($bidang, $data, $references) {
            $unit = Bidang::lockForUpdate()->findOrFail($bidang->id);
            foreach ($references as [$table, $column]) {
                DB::table($table)->where($column, $unit->name)->update([$column => $data['name']]);
            }
            $unit->update($data);
        });

        return back()->with('success', 'Nama bidang dan data terkait berhasil diperbarui.');
    }

    public function destroy(Bidang $bidang)
    {
        $references = Bidang::referenceColumns();
        DB::transaction(function () use ($bidang, $references) {
            $units = Bidang::orderBy('id')->lockForUpdate()->get();
            $unit = $units->firstWhere('id', $bidang->id);
            abort_unless($unit, 404);
            if ($units->count() <= 1 || $unit->usage($references) > 0) {
                throw ValidationException::withMessages(['bidang' => 'Bidang belum dapat dihapus karena masih digunakan oleh akun atau data kegiatan, atau merupakan bidang terakhir. Pindahkan data terkait ke bidang lain terlebih dahulu.']);
            }
            $unit->delete();
        });

        return back()->with('success', 'Nama bidang berhasil dihapus.');
    }
}
