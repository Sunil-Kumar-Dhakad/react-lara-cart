<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->search)   $query->where('name','like',"%{$request->search}%");
        if ($request->category) $query->where('category', $request->category);
        if ($request->status)   $query->where('status', $request->status);
        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Product::select('category')->distinct()->pluck('category');
        return view('admin.products.index', compact('products','categories'));
    }

    public function create()
    {
        return view('admin.products.form', ['product' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => 'required|string|unique:products,sku',
            'category'       => 'required|string',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'status'         => 'required|in:active,inactive',
            'badge'          => 'nullable|string|max:50',
            'image_url'      => 'nullable|url',
        ]);
        Product::create($data);
        return redirect()->route('admin.products.index')->with('success','Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => 'required|string|unique:products,sku,'.$product->id,
            'category'       => 'required|string',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'status'         => 'required|in:active,inactive',
            'badge'          => 'nullable|string|max:50',
            'image_url'      => 'nullable|url',
        ]);
        $product->update($data);
        return redirect()->route('admin.products.index')->with('success','Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success','Product deleted.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['status' => $product->status === 'active' ? 'inactive' : 'active']);
        return back()->with('success','Status updated.');
    }
}
