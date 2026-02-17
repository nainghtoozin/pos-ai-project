<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('category.view'), 403);
        
        $search = $request->get('search');
        
        $categories = Category::with('parent')
            ->withCount('children')
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->orderBy('name')
            ->paginate(10);
            
        $allCategories = Category::where('status', 'active')
            ->with('allChildren')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
            
        return view('categories.index', compact('categories', 'search', 'allCategories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        abort_unless(auth()->user()->can('category.create'), 403);
        
        $data = $request->validated();
        $data['slug'] = Str::slug($request->name);
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('categories'), $imageName);
            $data['image'] = $imageName;
        }
        
        Category::create($data);
        
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        abort_unless(auth()->user()->can('category.edit'), 403);
        
        $data = $request->validated();
        
        if ($category->name !== $request->name) {
            $data['slug'] = Str::slug($request->name);
        } else {
            $data['slug'] = $request->slug ?? $category->slug;
        }
        
        if ($request->hasFile('image')) {
            if ($category->image && File::exists(public_path('categories/' . $category->image))) {
                File::delete(public_path('categories/' . $category->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('categories'), $imageName);
            $data['image'] = $imageName;
        }
        
        $category->update($data);
        
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        abort_unless(auth()->user()->can('category.delete'), 403);
        
        if ($category->children()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category with subcategories.');
        }
        
        if ($category->image && File::exists(public_path('categories/' . $category->image))) {
            File::delete(public_path('categories/' . $category->image));
        }
        
        $category->delete();
        
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
