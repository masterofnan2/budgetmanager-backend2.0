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

    public static function getCycle(): Cycle|Model
    {
        $cycleActions = new CycleActions;
        $cycle = $cycleActions->getCurrent();

        return $cycle;
    }
}