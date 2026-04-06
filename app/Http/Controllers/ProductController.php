<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->search)   $query->where('name', 'like', "%{$request->search}%");
        if ($request->category) $query->where('category', $request->category);
        if ($request->status)   $query->where('status', $request->status);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'required|unique:products',
            'category'    => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'status'      => 'in:active,inactive',
            'image_url'   => 'nullable|url',
        ]);

        return response()->json(['message' => 'Product created.', 'product' => Product::create($data)], 201);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'sku'         => 'sometimes|unique:products,sku,' . $product->id,
            'category'    => 'sometimes|string',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'status'      => 'sometimes|in:active,inactive',
        ]);

        $product->update($data);
        return response()->json(['message' => 'Product updated.', 'product' => $product]);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted.']);
    }

    public function byCategory(string $category)
    {
        return response()->json(Product::where('category', $category)->where('status', 'active')->get());
    }

    public function updateStock(Request $request, Product $product)
    {
        $request->validate(['stock' => 'required|integer|min:0']);
        $product->update(['stock' => $request->stock]);
        return response()->json(['message' => 'Stock updated.', 'product' => $product]);
    }
}
