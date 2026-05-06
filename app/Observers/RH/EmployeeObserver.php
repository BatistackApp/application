<?php

namespace App\Observers\RH;

use App\Models\RH\Employee;

class EmployeeObserver
{
    public function creating(Employee $employee): void
    {
        if (empty($employee->matricule)) {
            $latestEmployee = Employee::latest('id')->first();
            $nextId = $latestEmployee ? $latestEmployee->id + 1 : 1;
            $employee->matricule = 'EMP-'.str_pad($nextId, 5, '0', STR_PAD_LEFT);
        }

    }
}
