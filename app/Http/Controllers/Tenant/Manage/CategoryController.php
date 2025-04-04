<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        // Apply search if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter if provided
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Apply sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['name', 'created_at', 'is_active'];
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at';
        }

        $query->orderBy($sortColumn, $sortDirection);

        // Execute query with pagination
        $categories = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('tenant/categories/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'sort' => $sortColumn,
                'direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Show the form for creating a new category.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('tenant/categories/Create');
    }

    /**
     * Store a newly created category in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Generate slug from name
        $slug = Str::slug($request->name);

        // Create category
        Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->input('is_active', true),
        ]);

        return redirect()->route('category.index')
            ->with('success', 'Category created successfully');
    }

    /**
     * Display the specified category.
     *
     * @param  \App\Models\Category  $category
     * @return \Inertia\Response
     */
    public function show(Category $category)
    {
        $category->loadCount('products');
        $products = $category->products()->with('images')->take(5)->get();

        return Inertia::render('tenant/categories/Show', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  \App\Models\Category  $category
     * @return \Inertia\Response
     */
    public function edit(Category $category)
    {
        return Inertia::render('tenant/categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Update slug if name has changed
        $slug = $category->name !== $request->name
            ? Str::slug($request->name)
            : $category->slug;

        // Update category
        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->input('is_active', $category->is_active),
        ]);

        return redirect()->route('category.index')
            ->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        $productsCount = $category->products()->count();

        if ($productsCount > 0) {
            return redirect()->route('category.index')
                ->with('error', 'Cannot delete category with associated products.');
        }

        // Delete the category
        $category->delete();

        return redirect()->route('category.index')
            ->with('success', 'Category deleted successfully');
    }
}
