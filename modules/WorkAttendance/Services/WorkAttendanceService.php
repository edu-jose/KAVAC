<?php

namespace Modules\WorkAttendance\Services;

use Modules\WorkAttendance\Models\WorkAttendance;
use Modules\WorkAttendance\Models\WorkAttendanceCustomSchedule;

class WorkAttendanceService
{
    public function getWorkAttendance($dateAt, $staffId): WorkAttendance|null
    {
        return WorkAttendance::whereDate(
            'date_at',
            $dateAt
        )->where(
            'payroll_staff_id',
            $staffId
        )->where(function ($query) {
            $query->whereNull('entry_time')->orWhereNull('exit_time');
        })->first();
    }

    public function getCustomSchedule($dateAt, $employmentId): WorkAttendance|null
    {
        return WorkAttendanceCustomSchedule::where([
            'active' => true
        ])->where(
            'start_date_at',
            '<=',
            $dateAt
        )->where(
            'end_date_at',
            '>=',
            $dateAt
        )->filterByEmployment($employmentId)->first();
    }

    public function createWorkAttendance($data): WorkAttendance
    {
        return WorkAttendance::create($data);
    }

    public function updateWorkAttendance($workAttendance, $data): bool
    {
        return $workAttendance->update($data);
    }
}
