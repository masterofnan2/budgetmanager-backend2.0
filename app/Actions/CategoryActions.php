<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\Cycle;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

const DEFAULTCATEGORIES = [
    [
        'name' => 'transport',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'expenses relative to transport, such as fuel, bus subscription, ...'
    ],
    [
        'name' => 'food',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'restaurants, provisions, goods, supplies, or anything that comes to food.',
    ],
    [
        'name' => 'bills',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'electricity, loan, internet subscriptions, gym, etc...',
    ],
    [
        'name' => 'entertainment',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'anything but necessities, may concern accessories, new clothes, shoes, ...',
    ]
];

class CategoryActions extends Actions
{
    protected $user_id;
    protected $id;
    protected $cycle_id;

    private function addStaticAssetsTo(array $categoryData)
    {
        $staticAssets = [
            'user_id' => $this->user_id ?? Auth::user()->id,
            'cycle_id' => Helper::getCycle()->id
        ];

        $categoryData = array_merge($categoryData, $staticAssets);
    }

    private function createDefaultCategories(): array
    {
        $defaultCategories = [];

        foreach (DEFAULTCATEGORIES as $defaultCategory) {
            $this->addStaticAssetsTo($defaultCategory);
            $defaultCategories[] = Category::create($defaultCategory);
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

        $this->addStaticAssetsTo($category);
        return Category::create($category);
    }

    public function updateCategory(array $category): bool
    {
        $budgetActions = new BudgetActions;
        $available = $budgetActions->availableCategoryBudget();

        $targetCategory = Category::find($category['id']);

        if ($category['budget'] > ($available + $targetCategory->budget)) {
            throw ValidationException::withMessages(['budget' => 'The budget is greater than the maximum available']);
        }

        return $targetCategory->update($category);
    }
}
