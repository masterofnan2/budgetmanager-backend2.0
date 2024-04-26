<?php

namespace App\Actions;

use App\Models\Cycle;
use Illuminate\Database\Eloquent\Model;

class Helper
{
    public static function getIsoString(\DateTime $datetime): string
    {
        return $datetime->format('Y-m-d H:i:s');
    }

    public static function getCycle(?CycleActions $cycleActions = new CycleActions): Cycle|Model
    {
        $cycle = $cycleActions->getCurrent();
        return $cycle;
    }
}