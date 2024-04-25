<?php

namespace App\Actions;

use App\Models\Cycle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CycleActions extends Actions
{
    protected $user_id;
    protected $start_date;
    protected $end_date;
    protected $renewal_frequency_id;

    public function __construct()
    {
        $user = Auth::user();

        $this->user_id = $user ? $user->id : null;
        $this->start_date = date_create();
        $this->end_date = date_add(date_create(), date_interval_create_from_date_string('1 month'));
        $this->renewal_frequency_id = 1;
    }

    public function setUserId($user_id): CycleActions
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function setStartDate(string $start_date): CycleActions
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function setEndDate(string $end_date): CycleActions
    {
        $this->end_date = $end_date;
        return $this;
    }

    public function setRenewalFrequencyId($renewal_frequency_id): CycleActions
    {
        $this->renewal_frequency_id = $renewal_frequency_id;
        return $this;
    }

    public function recycle()
    {
        $Cycle = new Cycle;
        $previousCycle = $Cycle
            ->with('renewalFrequency')
            ->where(['user_id' => Auth::user()->id])
            ->latest('id')
            ->first();

        if ($previousCycle) {
            $dateNow = date_create();
            $dateNowIsoString = Helper::getIsoString($dateNow);

            $previousCycleStartDate = date_create($previousCycle->start_date);
            $previousCycleEndDate = date_create($previousCycle->end_date);
            $previousCycleEndDateIsoString = $previousCycle->end_date;

            $newStartDate = $newStartDateIsoString = $newEndDate = $newEndDateIsoString = null;

            $previousCycleInterval = $previousCycle->renewalFrequency->interval ?
                date_interval_create_from_date_string($previousCycle->renewalFrequency->interval) : null;

            if (!$previousCycleInterval) {
                $datesDifference = date_diff($previousCycleStartDate, $previousCycleEndDate)->format('d') . ' days';
                $previousCycleInterval = date_interval_create_from_date_string($datesDifference);
            }

            $previousCycleEndDatePlusInterval = date_add(date_create($previousCycle->end_date), $previousCycleInterval);

            if (Helper::getIsoString($previousCycleEndDatePlusInterval) < $dateNowIsoString) {
                $newStartDate = $dateNow;
                $newStartDateIsoString = $dateNowIsoString;
            } else {
                $newStartDate = $previousCycleEndDate;
                $newStartDateIsoString = $previousCycleEndDateIsoString;
            }

            $newEndDate = date_add($newStartDate, $previousCycleInterval);
            $newEndDateIsoString = Helper::getIsoString($newEndDate);

            return $this
                ->setStartDate($newStartDateIsoString)
                ->setEndDate($newEndDateIsoString)
                ->createCycle();

        } else {
            return $this->createCycle();
        }
    }

    public function createCycle()
    {
        $defaultCycle = $this->get();

        $cycle = Cycle::create($defaultCycle);
        return $cycle;
    }

    public function getCurrent()
    {
        $user_id = $this->user_id;
        $dateNowIsoString = Helper::getIsoString(date_create());

        $queryConditions = "user_id = {$user_id} AND start_date <= '{$dateNowIsoString}' AND end_date > '{$dateNowIsoString}'";

        $currentCycle = Cycle::whereRaw($queryConditions)->first();

        if (!$currentCycle) {
            $currentCycle = $this->recycle();
        }

        return $currentCycle;
    }

    public function validateEndDate($endDate): array
    {
        $cycle = Helper::getCycle();
        $startDate = $cycle->start_date;

        $_startDate = date_create($startDate);
        $_endDate = date_create($endDate);

        $endDate_ = $_endDate->format('Y-m-d H:i:s');

        if ($startDate > $endDate_ || date_diff($_startDate, $_endDate)->days < 1) {
            throw ValidationException::withMessages([
                'end_date' => 'the end date should be at least a day after the start date'
            ]);
        }

        return [
            'end_date' => $endDate_
        ];
    }

    public function save(): bool
    {
        $cycle = Helper::getCycle();

        $cycle->end_date = $this->end_date;
        $cycle->renewal_frequency_id = $this->renewal_frequency_id;

        return $cycle->save();
    }
}