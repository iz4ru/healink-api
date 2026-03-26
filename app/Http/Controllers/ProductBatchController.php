<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductBatchController extends Controller
{
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'batch_number' => 'required|string',
        ],
        [
            'batch_number.required' => 'Nomor batch harus diisi',
            'batch_number.string' => 'Penamaan nomor batch tidak valid'
        ]);

        $user = Auth::user();
        
        $batch = ProductBatch::with('product')->findOrFail($id);
        $old = $batch->batch_number;

        DB::transaction(function () use ($id, $data, $user, $batch, $old) {
            $batch->update(['batch_number' => $data['batch_number']]);

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Ubah data batch',
                'detail' => $user->name . ' mengubah penamaan nomor batch ' . $old . ' menjadi ' . $batch->batch_number . ' pada produk ' . $batch->product->name . ' (ID: ' . $id . ')',
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $batch = ProductBatch::with('product')->findOrFail($id);
        $batchNumber = $batch->batch_number;
        $productName = $batch->product->name;

        DB::transaction(function () use ($id, $user, $batch, $batchNumber, $productName) {
            $batch->delete();

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Hapus batch',
                'detail' => $user->name . ' telah menghapus batch ' . $batchNumber . ' pada produk ' . $productName . ' (ID: ' . $id . ')',
            ]);
        });

        return response()->json(['success' => true]);
    }
}
