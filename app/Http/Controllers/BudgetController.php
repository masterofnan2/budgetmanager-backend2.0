<?php

namespace App\Http\Controllers;

use App\Actions\BudgetActions;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

    public function get(BudgetActions $BudgetActions)
    {
        $currentBudget = $BudgetActions->getInitial();
        return response()->json(['budget' => $currentBudget]);
    }

    public function getBalance(BudgetActions $BudgetActions)
    {
        return response()->json(['balance' => $BudgetActions->getCurrentBalance()]);
    }

    public function set(BudgetActions $budgetActions, Request $request)
    {
        $affected = $budgetActions
            ->setAmount($request->input('amount'))
            ->save();

        return response()->json(['affected' => $affected]);
    }
}