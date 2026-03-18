<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with(['categories', 'unit', 'batches']);

            if ($request->has('category_ids')) {
                $catIds = is_array($request->category_ids) ? $request->category_ids : [$request->category_ids];

                $query->whereHas('categories', function ($q) use ($catIds) {
                    $q->whereIn('categories.id', $catIds);
                });
            }

            $sort = $request->query('sort', 'newest');
            switch ($sort) {
                case 'az':
                    $query->orderBy('product_name', 'asc');
                    break;
                case 'za':
                    $query->orderBy('product_name', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    // newest
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $products = $query->get();

            return response()->json(
                [
                    'success' => true,
                    'data' => $products,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function indexCategory()
    {
        return response()->json(
            [
                'status' => true,
                'message' => 'GET data sukses',
                'data' => ['categories' => Category::all()],
            ],
            200,
        );
    }

    public function indexUnit()
    {
        return response()->json(
            [
                'status' => true,
                'message' => 'GET data sukses',
                'data' => ['units' => Unit::all()],
            ],
            200,
        );
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'product_name' => 'required|string|max:255',
                'barcode' => 'nullable|string|max:255|unique:products,barcode',
                'category_ids' => 'required|array',
                'category_ids.*' => 'exists:categories,id',
                'unit_id' => 'required|exists:units,id',
                'sell_price' => 'required|numeric|min:0|gte:batch.buy_price',
                'min_stock' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

                'batch.buy_price' => 'required|numeric|min:0',
                'batch.stock' => 'required|integer|min:1',
                'batch.batch_number' => [
                    'nullable',
                    'string',
                    'max:255',
                    // Rule unik jika digabung dengan product_name (mencegah duplikasi batch saat input)
                    // Jika migration menggunakan unique(['product_id', 'batch_number'])
                ],
                'batch.exp_date' => 'nullable|date|after:today',
            ],
            [
                'product_name.required' => 'Nama produk tidak boleh kosong.',
                'barcode.unique' => 'Barcode ini sudah terdaftar untuk produk lain.',
                'sell_price.required' => 'Harga jual wajib diisi.',
                'sell_price.gte' => 'Harga jual tidak boleh di bawah harga beli.',
                'category_ids.required' => 'Silakan pilih kategori produk.',
                'category_ids.*.exists' => 'Kategori yang dipilih sudah dihapus dari database.',
                'unit_id.required' => 'Silakan pilih satuan produk.',
                'batch.buy_price.required' => 'Harga beli wajib diisi.',
                'batch.stock.min' => 'Stok awal minimal adalah 1.',
                'batch.exp_date.after' => 'Tanggal kadaluarsa harus lebih dari hari ini.',
                'image.max' => 'Ukuran foto maksimal adalah 2MB.',
            ],
        );

        $imagePath = null;
        $imageUrl = null;
        $disk = 'supabase';

        if ($request->hasFile('image')) {
            try {
                $imagePath = $request->file('image')->store('products', $disk);

                $bucket = env('SUPABASE_BUCKET');
                $baseUrl = rtrim(env('SUPABASE_PUBLIC_URL'), '/');

                $imageUrl = $baseUrl . '/' . $bucket . '/' . $imagePath;
            } catch (\Exception $e) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Gagal mengunggah gambar ke Supabase: ' . $e->getMessage(),
                    ],
                    500,
                );
            }
        }

        DB::beginTransaction();

        try {
            $product = Product::create([
                'product_name' => $request->product_name,
                'barcode' => $request->barcode,
                'unit_id' => $request->unit_id,
                'sell_price' => $request->sell_price,
                'min_stock' => $request->min_stock ?? 0,
                'description' => $request->description,
                'image_url' => $imageUrl,
            ]);

            $product->categories()->sync($request->category_ids);

            $product->batches()->create([
                'batch_number' => $request->input('batch.batch_number'),
                'exp_date' => $request->input('batch.exp_date'),
                'buy_price' => $request->input('batch.buy_price'),
                'stock' => $request->input('batch.stock'),
            ]);

            DB::commit();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan',
                    'data' => $product->load('batches'),
                ],
                201,
            );
        } catch (\Exception $e) {
            DB::rollBack();

            if ($imagePath) {
                Storage::disk($disk)->delete($imagePath);
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menyimpan data produk: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function show($id)
    {
        try {
            // Gunakan 'with' untuk menarik semua relasi yang dibutuhkan Flutter
            $product = Product::with(['categories', 'unit', 'batches'])->find($id);

            if (!$product) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Produk tidak ditemukan',
                    ],
                    404,
                );
            }

            return response()->json(
                [
                    'success' => true,
                    'data' => $product,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate(
            [
                'product_name' => 'required|string|max:255',
                'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $id,
                'category_ids' => 'required|array',
                'category_ids.*' => 'exists:categories,id',
                'unit_id' => 'required|exists:units,id',
                'sell_price' => [
                    'required',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request, $product) {
                        $buyPrice = $request->input('buy_price', optional($product->batches()->first())->buy_price ?? 0);
                        if ($value < $buyPrice) {
                            $fail('Harga jual tidak boleh di bawah harga beli.');
                        }
                    },
                ],
                'min_stock' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

                'batch_id' => 'nullable|exists:product_batches,id',

                'new_batch_number' => 'nullable|string|max:255',
                'buy_price' => 'nullable|required_with:batch_id,new_batch_number|numeric|min:0',
                'exp_date' => 'nullable|required_with:new_batch_number|date',
                'stock_qty' => 'nullable|required_with:new_batch_number|integer|min:1',

                'stock_action' => 'nullable|in:add,reduce',
            ],
            [
                'new_batch_number.required_with' => 'Nomor batch harus diisi.',
                'buy_price.required_with' => 'Harga beli wajib diisi.',
                'exp_date.required_with' => 'Tanggal kadaluarsa wajib diisi untuk batch baru.',
                'stock_qty.required_with' => 'Jumlah stok wajib diisi.',

                'product_name.required' => 'Nama produk wajib diisi.',
                'barcode.unique' => 'Barcode ini sudah terdaftar untuk produk lain.',
                'category_ids.required' => 'Silakan pilih minimal 1 kategori.',
                'category_ids.*.exists' => 'Kategori yang dipilih sudah dihapus dari database.',
                'unit_id.required' => 'Silakan pilih jenis satuan.',
                'sell_price.required' => 'Harga jual wajib diisi.',
                'image.max' => 'Ukuran foto maksimal adalah 2MB.',
                'stock_qty.min' => 'Jumlah penyesuaian stok minimal adalah 1.',
            ],
        );

        $disk = 'supabase';

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                if ($product->image_url && str_contains($product->image_url, 'storage/products/')) {
                    $oldPath = explode('products/', $product->image_url)[1] ?? null;
                    if ($oldPath) {
                        Storage::disk($disk)->delete('products/' . $oldPath);
                    }
                }

                $newImagePath = $request->file('image')->store('products', $disk);
                $bucket = env('SUPABASE_BUCKET');
                $baseUrl = rtrim(env('SUPABASE_PUBLIC_URL'), '/');
                $newImageUrl = $baseUrl . '/' . $bucket . '/' . $newImagePath;

                $product->image_url = $newImageUrl;
            }

            $product->update([
                'product_name' => $request->product_name ?? $product->product_name,
                'barcode' => $request->barcode ?? $product->barcode,
                'unit_id' => $request->unit_id ?? $product->unit_id,
                'sell_price' => $request->sell_price ?? $product->sell_price,
                'min_stock' => $request->min_stock ?? $product->min_stock,
                'description' => $request->description ?? $product->description,
            ]);

            if ($request->has('category_ids')) {
                $product->categories()->sync($request->category_ids);
            }

            if ($request->filled('new_batch_number')) {
                $product->batches()->create([
                    'batch_number' => $request->new_batch_number,
                    'exp_date' => $request->exp_date,
                    'buy_price' => $request->buy_price,
                    'stock' => $request->stock_qty, // Stok langsung bertambah sejumlah qty awal
                ]);
            }

            elseif ($request->filled('batch_id')) {
                $batch = $product->batches()->where('id', $request->batch_id)->first();

                if ($batch) {
                    $batch->update([
                        'exp_date' => $request->exp_date ?? $batch->exp_date,
                        'buy_price' => $request->buy_price ?? $batch->buy_price,
                    ]);

                    if ($request->filled('stock_action') && $request->filled('stock_qty')) {
                        $qty = (int) $request->stock_qty;

                        if ($request->stock_action === 'add') {
                            $batch->increment('stock', $qty);
                        } elseif ($request->stock_action === 'reduce') {
                            if ($qty > $batch->stock) {
                                throw new \Exception("Gagal: Jumlah pengurangan ($qty) melebihi stok batch yang ada ({$batch->stock}).");
                            }
                            $batch->decrement('stock', $qty);
                        }
                        $batch->save();
                    }
                }
            }

            DB::commit();
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Produk berhasil diperbarui!',
                ],
                200,
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memperbarui produk: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    // Soft Delete
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->transactionItems()->exists()) {
                $product->delete();
                $message = 'Produk diarsipkan (Soft Delete) karena sudah memiliki riwayat penjualan.';
            } else {
                if ($product->image_url && str_contains($product->image_url, 'storage/products/')) {
                    $oldPath = explode('products/', $product->image_url)[1] ?? null;
                    if ($oldPath) {
                        Storage::disk('supabase')->delete('products/' . $oldPath);
                    }
                }

                $product->forceDelete();
                $message = 'Produk dan gambar berhasil dihapus permanen.';
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus produk: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
