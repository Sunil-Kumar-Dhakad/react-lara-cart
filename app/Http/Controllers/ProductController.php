<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Params: search, category, status, sort, per_page, page
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name',        'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('sku',         'like', "%{$term}%")
                  ->orWhere('category',    'like', "%{$term}%");
            });
        }

        switch ($request->sort) {
            case 'price-asc':  $query->orderBy('price', 'asc');        break;
            case 'price-desc': $query->orderBy('price', 'desc');       break;
            case 'rating':     $query->orderBy('rating', 'desc');      break;
            case 'newest':     $query->orderBy('created_at', 'desc');  break;
            default:           $query->orderBy('id', 'asc');           break;
        }

        $perPage  = min((int) $request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => 'required|string|unique:products,sku',
            'category'       => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'status'         => 'in:active,inactive',
            'image_url'      => 'nullable|url|max:500',
            'badge'          => 'nullable|string|max:50',
        ]);

        return response()->json(['message' => 'Product created.', 'data' => Product::create($data)], 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'sku'            => 'sometimes|string|unique:products,sku,' . $product->id,
            'category'       => 'sometimes|string|max:100',
            'description'    => 'nullable|string',
            'price'          => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock'          => 'sometimes|integer|min:0',
            'status'         => 'sometimes|in:active,inactive',
            'image_url'      => 'nullable|url|max:500',
            'badge'          => 'nullable|string|max:50',
        ]);

        $product->update($data);
        return response()->json(['message' => 'Product updated.', 'data' => $product]);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted.']);
    }

    public function updateStock(Request $request, Product $product)
    {
        $request->validate(['stock' => 'required|integer|min:0']);
        $product->update(['stock' => $request->stock]);
        return response()->json(['message' => 'Stock updated.', 'data' => $product]);
    }
}
