<?php

namespace App\Http\Controllers;

use App\Models\ProductBatch;
use Illuminate\Http\Request;

class ProductBatchController extends Controller
{
    public function update(Request $request, $id)
    {
        $batch = ProductBatch::findOrFail($id);
        $batch->update($request->validate(['batch_number' => 'required|string']));
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $batch = ProductBatch::findOrFail($id);
        $batch->delete();
        return response()->json(['success' => true]);
    }
}
