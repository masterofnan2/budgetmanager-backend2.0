<?php

namespace App\Actions;

use App\Models\Income;
use Illuminate\Database\Eloquent\Collection;

class IncomeActions extends Actions
{
    public function getCurrentIncomes(): Collection
    {
        $CycleActions = new CycleActions;
        $cycle_id = $CycleActions->getCurrent()->id;

        $incomes = Income::where(['cycle_id' => $cycle_id])->get();
        return $incomes;
    }

    public function getCurrentIncomesSum()
    {
        $incomes = $this->getCurrentIncomes();
        $sum = 0;

        if ($incomes->count() > 0) {
            $sum = $incomes->sum(fn($income) => $income->amount);
        }

        return $sum;
    }
}