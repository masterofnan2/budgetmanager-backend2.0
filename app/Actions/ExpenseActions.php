<?php

namespace App\Actions;

use App\Actions\CycleActions;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

class ExpenseActions extends Actions
{
    public function getCurrentExpenses(): Collection
    {
        $cycle_id = Helper::getCycle()->id;
        $allExpenses = Expense::where(['cycle_id' => $cycle_id])->get();

        return $allExpenses;
    }

    public function getCurrentExpensesSum()
    {
        $expenses = $this->getCurrentExpenses();
        $sum = 0;

        if ($expenses->count() > 0) {
            $sum = $expenses->sum(fn($expense) => $expense->amount);
        }

        return $sum;
    }
}