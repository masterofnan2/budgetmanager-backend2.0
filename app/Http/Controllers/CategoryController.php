<?php

namespace App\Http\Controllers;

use App\Actions\BudgetActions;
use App\Actions\CategoryActions;
use App\Models\Category;
use Illuminate\Http\Request;
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

    public function add(Request $request, CategoryActions $categoryActions, BudgetActions $budgetActions)
    {
        $request->validate(['image' => 'image/*']);
        $budget = $request->input('budget');

        if ($budget > $budgetActions->availableCategoryBudget()) {
            throw ValidationException::withMessages(['budget' => 'The budget is greater than the maximum available']);
        }

        return response()->json(['category' => $categoryActions->addCategory($request->all())]);
    }
}