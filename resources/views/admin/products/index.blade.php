@extends('admin.layouts.app')
@section('title','Products')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Products</div>
    <div class="page-subtitle">{{ $products->total() }} products in catalog</div>
  </div>
  <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add Product</a>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
  <div class="search-wrap">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input name="search" value="{{ request('search') }}" placeholder="Search products…">
  </div>
  <select name="category" class="filter-input">
    <option value="">All Categories</option>
    @foreach($categories as $cat)
      <option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>
    @endforeach
  </select>
  <select name="status" class="filter-input">
    <option value="">All Status</option>
    <option value="active"   {{ request('status')=='active'?'selected':'' }}>Active</option>
    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="card table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Product</th><th>SKU</th><th>Category</th>
        <th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($products as $product)
      <tr>
        <td class="text-muted">{{ $product->id }}</td>
        <td>
          <div style="font-weight:600">{{ $product->name }}</div>
          @if($product->badge)
            <span class="badge badge-yellow" style="margin-top:3px">{{ $product->badge }}</span>
          @endif
        </td>
        <td class="text-muted" style="font-family:monospace;font-size:12px">{{ $product->sku }}</td>
        <td><span class="badge badge-purple">{{ $product->category }}</span></td>
        <td>
          <div class="fw-700">${{ number_format($product->price,2) }}</div>
          @if($product->original_price)
            <div style="font-size:11px;color:var(--text3);text-decoration:line-through">${{ number_format($product->original_price,2) }}</div>
          @endif
        </td>
        <td>
          <span class="{{ $product->stock < 10 ? 'text-danger' : 'text-success' }} fw-700">
            {{ $product->stock }}
          </span>
          @if($product->stock < 10 && $product->stock > 0)
            <span class="badge badge-red" style="margin-left:4px">Low</span>
          @elseif($product->stock == 0)
            <span class="badge badge-red" style="margin-left:4px">Out</span>
          @endif
        </td>
        <td>
          <span class="badge {{ $product->status==='active' ? 'badge-green' : 'badge-gray' }}">
            {{ $product->status }}
          </span>
        </td>
        <td>
          <div style="display:flex;gap:5px">
            <a href="{{ route('admin.products.edit',$product) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">✏️</a>
            <form method="POST" action="{{ route('admin.products.toggle',$product) }}" style="display:inline">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-warning btn-sm btn-icon" title="Toggle Status">
                {{ $product->status==='active' ? '🔴' : '🟢' }}
              </button>
            </form>
            <form method="POST" action="{{ route('admin.products.destroy',$product) }}" style="display:inline" onsubmit="return confirm('Delete this product?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">🗑</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text3)">No products found</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="pagination">{{ $products->withQueryString()->links() }}</div>
@endsection
