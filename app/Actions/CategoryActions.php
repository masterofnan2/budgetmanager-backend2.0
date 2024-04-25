<?php

namespace App\Actions;

use App\Models\Category;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

const DEFAULTCATEGORIES = [
    [
        'name' => 'transport',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'expenses relative to transport, such as fuel, bus subscription, ...',
        'icon' => 'transport',
    ],
    [
        'name' => 'food',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'restaurants, provisions, goods, supplies, or anything that comes to food.',
        'icon' => 'food',
    ],
    [
        'name' => 'bills',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'electricity, loan, internet subscriptions, gym, etc...',
        'icon' => 'bills',
    ],
    [
        'name' => 'entertainment',
        'user_id' => null,
        'budget' => 0,
        'cycle_id' => null,
        'description' => 'anything but necessities, may concern accessories, new clothes, shoes, ...',
        'icon' => 'entertainment',
    ]
];

class CategoryActions extends Actions
{
    protected $user_id;
    protected $id;
    protected $cycle_id;


    private function createDefaultCategories(): array
    {
        $defaultCategories = [];
        $user_id = $this->user_id ?? Auth::user()->id;
        $cycle_id = Helper::getCycle()->id;

        foreach (DEFAULTCATEGORIES as $defaultCategory) {
            $defaultCategory['user_id'] = $user_id;
            $defaultCategory['cycle_id'] = $cycle_id;

            $defaultCategories[] = Category::create($defaultCategory);
        }

        return $defaultCategories;
    }

    private function createCategories(): array
    {
        $cycle_id = Helper::getCycle()->id;
        $categories = [];

        if ($cycle_id > 1) {
            $previous_cycle_id = --$cycle_id;
            $backupCategories = Category::where('cycle_id', $previous_cycle_id)
                ->withTrashed()
                ->get()
                ->toArray();

            if (count($backupCategories) > 0) {
                foreach ($backupCategories as $backupCategory) {
                    $backupCategory['cycle_id'] = $cycle_id;
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

    public function categoriesBudgetSum()
    {
        $cycle = Helper::getCycle();
        $sum = Category::where('cycle_id', $cycle->id)->sum('budget');

        return $sum;
    }

    public function addCategory(array $category)
    {
        return Category::create($category);
    }
}
