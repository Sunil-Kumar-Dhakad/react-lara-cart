@extends('admin.layouts.app')
@section('title', $product ? 'Edit Product' : 'Add Product')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">{{ $product ? 'Edit Product' : 'Add New Product' }}</div>
    <div class="page-subtitle">{{ $product ? 'Update product information' : 'Fill in the details below' }}</div>
  </div>
  <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">← Back to Products</a>
</div>

<form method="POST" action="{{ $product ? route('admin.products.update',$product) : route('admin.products.store') }}" style="max-width:760px">
  @csrf
  @if($product) @method('PUT') @endif

  <div class="grid-2" style="margin-bottom:20px">
    <div class="card card-body">
      <div style="font-size:13px;font-weight:700;margin-bottom:14px;color:var(--text)">Basic Information</div>
      <div class="form-group">
        <label class="form-label">Product Name *</label>
        <input class="form-input" name="name" value="{{ old('name',$product->name??'') }}" placeholder="Enterprise Suite Pro" required>
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">SKU *</label>
          <input class="form-input" name="sku" value="{{ old('sku',$product->sku??'') }}" placeholder="ESP-001" required>
          @error('sku')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Category *</label>
          <select class="form-input" name="category" required>
            <option value="">Select category</option>
            @foreach(['Software','Cloud','Analytics','Security','Infrastructure','AI & ML','DevOps'] as $cat)
              <option value="{{ $cat }}" {{ old('category',$product->category??'')===$cat?'selected':'' }}>{{ $cat }}</option>
            @endforeach
          </select>
          @error('category')<div class="form-error">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea class="form-input" name="description" rows="3" placeholder="Product description…">{{ old('description',$product->description??'') }}</textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Image URL</label>
        <input class="form-input" name="image_url" value="{{ old('image_url',$product->image_url??'') }}" placeholder="https://…">
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px">
      <div class="card card-body">
        <div style="font-size:13px;font-weight:700;margin-bottom:14px;color:var(--text)">Pricing & Stock</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Price ($) *</label>
            <input class="form-input" type="number" step="0.01" name="price" value="{{ old('price',$product->price??'') }}" placeholder="0.00" required>
            @error('price')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Original Price ($)</label>
            <input class="form-input" type="number" step="0.01" name="original_price" value="{{ old('original_price',$product->original_price??'') }}" placeholder="0.00">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Stock Quantity *</label>
          <input class="form-input" type="number" name="stock" value="{{ old('stock',$product->stock??0) }}" placeholder="100" required>
          @error('stock')<div class="form-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="card card-body">
        <div style="font-size:13px;font-weight:700;margin-bottom:14px;color:var(--text)">Visibility</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Status *</label>
            <select class="form-input" name="status" required>
              <option value="active"   {{ old('status',$product->status??'active')==='active'?'selected':'' }}>Active</option>
              <option value="inactive" {{ old('status',$product->status??'')==='inactive'?'selected':'' }}>Inactive</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Badge Label</label>
            <select class="form-input" name="badge">
              <option value="">None</option>
              @foreach(['new','bestseller','premium','coming soon','sale'] as $b)
                <option value="{{ $b }}" {{ old('badge',$product->badge??'')===$b?'selected':'' }}>{{ ucfirst($b) }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">
          {{ $product ? '💾 Save Changes' : '✓ Create Product' }}
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>
@endsection
