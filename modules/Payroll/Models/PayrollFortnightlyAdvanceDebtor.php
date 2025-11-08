<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Models\PayrollStaff;

/**
 * Class PayrollFortnightlyAdvanceDebtor
 *
 * @property int $id
 * @property ... // Add your table columns as properties
 */
class PayrollFortnightlyAdvanceDebtor extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payroll_fortnightly_advance_debtors';

    // If you want to allow mass assignment for all fields, uncomment below:
    protected $guarded = [];

    // If you want to specify fillable fields, use:
    // protected $fillable = ['column1', 'column2'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Relation with the payroll_staff table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }
}
