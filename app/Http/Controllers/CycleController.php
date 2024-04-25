<?php

namespace App\Http\Controllers;

use App\Actions\CycleActions;
use Illuminate\Http\Request;

const CUSTOMRENEWALFREQUENCY = 3;

class CycleController extends Controller
{
    public function get()
    {
        $cycleActions = new CycleActions;
        return response()->json(['cycle' => $cycleActions->getCurrent()]);
    }

    public function edit(Request $request, CycleActions $cycleActions)
    {
        $endDate = $request->input('end_date');
        $validated = $cycleActions->validateEndDate($endDate);

        return response()->json([
            'affected' => $cycleActions
                ->setEndDate($validated['end_date'])
                ->setRenewalFrequencyId(CUSTOMRENEWALFREQUENCY)
                ->save()
        ]);
    }
}
