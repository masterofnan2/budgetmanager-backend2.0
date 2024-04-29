<?php

namespace App\Http\Controllers;

use App\Actions\BudgetActions;
use App\Actions\CategoryActions;
use App\Http\Requests\Category\AddCategoryRequest;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function getCurrents(CategoryActions $categoryActions)
    {
        $categories = $categoryActions->getCurrents();
        return response()->json(['categories' => $categories]);
    }

    public function delete(int $id)
    {
        $deleted = Category::where('id', $id)->delete();
        return response()->json(compact('deleted'));
    }

    public function add(AddCategoryRequest $request, CategoryActions $categoryActions)
    {
        $data = $request->all();

        return response()->json([
            'category' => $categoryActions->addCategory($data)
        ]);
    }

    public function edit(UpdateCategoryRequest $request, CategoryActions $categoryActions)
    {
        $data = $request->all();

        return response()->json([
            'updated' => $categoryActions->updateCategory($data),
        ]);
    }
}