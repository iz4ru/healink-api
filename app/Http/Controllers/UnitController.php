<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:255'],
        [
            'name.required' => 'Nama jenis satuan harus diisi',
        ]);
        // Untuk batch, ganti 'name' jadi 'batch_number'
        $item = Unit::create(['name' => $request->name]); 
        return response()->json(['success' => true, 'message' => 'Berhasil ditambahkan', 'data' => $item]);
    }

    public function update(Request $request, $id) {
        $request->validate(['name' => 'required|string|max:255'],
        [
            'name.required' => 'Nama jenis satuan harus diisi',
        ]);
        $item = Unit::findOrFail($id);
        $item->update(['name' => $request->name]);
        return response()->json(['success' => true, 'message' => 'Berhasil diperbarui']);
    }

    public function destroy($id) {
        $item = Unit::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Berhasil dihapus']);
    }
}
