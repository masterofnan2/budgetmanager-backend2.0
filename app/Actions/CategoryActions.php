<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\Cycle;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

const DEFAULTCATEGORIES = [
    [
        'name' => 'transport',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'expenses relative to transport, such as fuel, bus subscription, ...',
        'image' => 'images/categories/VwPs49UQI81eLQwkXreeFwiyeVlP0GJQIk6FOV5j.svg'
    ],
    [
        'name' => 'food',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'restaurants, provisions, goods, supplies, or anything that comes to food.',
        'image' => 'images/categories/kmtTC5UhvMghApo0ioWzUdyHLYV2tEZ3dJr2HHDb.svg'
    ],
    [
        'name' => 'bills',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'electricity, loan, internet subscriptions, gym, etc...',
        'image' => 'images/categories/SAXOx1oSZAhYtusWkNVtB918ta2uDbm1blqBznLp.svg'
    ],
    [
        'name' => 'entertainment',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'anything but necessities, may concern accessories, new clothes, shoes, ...',
        'image' => 'images/categories/36R7tV22mr6FMcJl79Pv8pqGvwUY6T47GLZXOVQN.svg'
    ]
];

class CategoryActions extends Actions
{
    protected $user_id;

    private function withStaticAssets(array $categoryData)
    {
        $staticAssets = [
            'user_id' => $this->user_id ?? Auth::user()->id,
            'cycle_id' => Helper::getCycle()->id
        ];

        return array_merge($categoryData, $staticAssets);
    }

    private function createDefaultCategories(): array
    {
        $defaultCategories = [];

        foreach (DEFAULTCATEGORIES as $defaultCategory) {
            $defaultCategories[] = Category::create($this->withStaticAssets($defaultCategory));
        }

        return $defaultCategories;
    }

    private function createCategories(?CycleActions $cycleActions = new CycleActions): array
    {
        $cycleId = $cycleActions->getCurrent()->id;
        $previousCycle = $cycleActions->previousCycle();
        $categories = [];

        if ($previousCycle && isset($previousCycle->id)) {
            $previousCycleId = $previousCycle->id;

            $backupCategories = Category::where('cycle_id', $previousCycleId)
                ->withTrashed()
                ->get()
                ->toArray();

            if (count($backupCategories) > 0) {
                foreach ($backupCategories as $backupCategory) {
                    $backupCategory['cycle_id'] = $cycleId;
                    $backupCategory['deleted_at'] = null;

                    unset($backupCategory['created_at'], $backupCategory['updated_at']);

                    $categories[] = Category::create($backupCategory);
                }

                return $categories;
            }
        }

        return $this->createDefaultCategories();
    }

    private function deleteImage(string $imagePath): bool
    {
        $image = 'public/' . $imagePath;

        if (Storage::exists($image)) {
            return Storage::delete($image);
        }

        return false;
    }

    public function storeImage(UploadedFile $image): string
    {
        $path = $image->store('public/images/categories');
        $path = str_replace('public/', '', $path);

        return $path;
    }

    public function getCurrents(): array|Collection
    {
        $cycle = Helper::getCycle();

        $categories = Category::where('cycle_id', $cycle->id)
            ->withTrashed()
            ->get();

        $currentCategories = $categories->where('deleted_at', '=', null)->values();
        $trashedCategories = $categories->whereNotNull('deleted_at')->values();

        if ($currentCategories->count() > 0) {
            $categories = $currentCategories;
        } else {
            if ($trashedCategories->count() > 0) {
                $categories = [];
            } else {
                $categories = $this->createCategories();
            }
        }

        return $categories;
    }

    public function categoriesBudgetSum(): int
    {
        $cycle = Helper::getCycle();
        $sum = Category::where('cycle_id', $cycle->id)->sum('budget');

        return intval($sum);
    }

    public function addCategory(array $category)
    {
        $budgetActions = new BudgetActions;
        $available = $budgetActions->availableCategoryBudget();

        if ($category['budget'] > $available) {
            throw ValidationException::withMessages(['budget' => 'The budget is greater than the maximum available']);
        }

        return Category::create($this->withStaticAssets($category));
    }

    public function updateCategory(array $category): bool
    {
        $budgetActions = new BudgetActions;
        $available = $budgetActions->availableCategoryBudget();

        $targetCategory = Category::find($category['id']);

        if ($category['budget'] > ($available + $targetCategory->budget)) {
            throw ValidationException::withMessages(['budget' => 'The budget is greater than the maximum available']);
        }

        if ($category['image']) {
            if ($targetCategory->image && $category['image'] !== $targetCategory->image) {
                $this->deleteImage($targetCategory->image);
            }
        }

        return $targetCategory->update($category);
    }
}
