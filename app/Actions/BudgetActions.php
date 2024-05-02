<?php

namespace App\Actions;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BudgetActions extends Actions
{
    protected $amount;
    protected $user_id;
    protected $cycle_id;

    public function setUserId($user_id): BudgetActions
    {
        $this->user_id = $user_id;
        return $this;
    }
    public function setAmount($amount): BudgetActions
    {
        $this->amount = $amount;
        return $this;
    }


    public function setCycleId($cycle_id): BudgetActions
    {
        $this->cycle_id = $cycle_id;
        return $this;
    }

    public function __construct()
    {
        $this->amount = 0;
        $this->user_id = Auth::user()->id;
        $this->cycle_id = null;
    }

    public function createBudget(): Budget|Model
    {
        $cycle = Helper::getCycle();

        $defaultBudget = $this
            ->setCycleId($cycle->id)
            ->get();

        return Budget::create($defaultBudget);
    }

    public function getInitial(): Budget|Model
    {
        $cycle_id = Helper::getCycle()->id;
        $currentBudget = Budget::where('cycle_id', $cycle_id)->first();

        if (!$currentBudget) {
            $currentBudget = $this->createBudget();
        }

        return $currentBudget;
    }

    public function getCurrentBalance()
    {
        $ExpenseActions = new ExpenseActions;
        $IncomeActions = new IncomeActions;

        $initialBudgetAmount = $this->getInitial()->amount;
        $expensesAmount = $ExpenseActions->getCurrentExpensesSum();
        $incomesAmount = $IncomeActions->getCurrentIncomesSum();

        return ($initialBudgetAmount + $incomesAmount) - $expensesAmount;
    }

    public function save(): int
    {
        $cycle_id = Helper::getCycle()->id;
        $changes = $this->get();

        foreach ($changes as $property => $value) {
            if ($value === null) {
                unset($changes[$property]);
            }
        }

        return DB::table('budgets')
            ->where('cycle_id', $cycle_id)
            ->update($changes);
    }

    public function availableCategoryBudget(?CategoryActions $categoryActions = new CategoryActions)
    {
        $initial = $this->getInitial();
        $categoriesBudgetSum = $categoryActions->categoriesBudgetSum();

        $available = intval($initial->amount) - $categoriesBudgetSum;

        return $available;
    }
}