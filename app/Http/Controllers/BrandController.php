<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('brand.view'), 403);
        
        $search = $request->get('search');
        
        $brands = Brand::when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->orderBy('name')
            ->paginate(10);
            
        return view('brands.index', compact('brands', 'search'));
    }

    public function store(StoreBrandRequest $request)
    {
        abort_unless(auth()->user()->can('brand.create'), 403);
        
        $data = $request->validated();
        $data['slug'] = Str::slug($request->name);
        
        Brand::create($data);
        
        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        abort_unless(auth()->user()->can('brand.edit'), 403);
        
        $data = $request->validated();
        
        if ($brand->name !== $request->name) {
            $data['slug'] = Str::slug($request->name);
        } else {
            $data['slug'] = $request->slug ?? $brand->slug;
        }
        
        $brand->update($data);
        
        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        abort_unless(auth()->user()->can('brand.delete'), 403);
        
        $brand->delete();
        
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
