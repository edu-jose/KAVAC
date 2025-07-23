<?php

namespace Modules\WorkAttendance\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Payroll\Models\PayrollStaff as BasePayrollStaff;

/**
 * @class Parish
 * @brief Extiende del modelo PayrollStaff del módulo de Talento Humano
 *
 * @author Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaff extends BasePayrollStaff
{
    protected $with = [];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get all of the workattendanceExternalActivities for the PayrollStaff
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workattendanceExternalActivities(): HasMany
    {
        return $this->hasMany(WorkattendanceExternalActivity::class);
    }
}
