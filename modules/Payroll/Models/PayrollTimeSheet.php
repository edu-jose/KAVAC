<?php

namespace Modules\Payroll\Models;

use Carbon\Carbon;
use App\Traits\ModelsTrait;
use App\Models\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PayrollTimeSheet
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheet extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos con el tipo de dato a retornar
     *
     * @var array $casts
     */
    protected $casts = [
        'time_sheet_data' => 'array',
        'time_sheet_original_data' => 'array',
        'time_sheet_columns' => 'array',
    ];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'from_date', 'to_date', 'payroll_supervised_group_id', 'payroll_time_sheet_parameter_id', 'time_sheet_data',
        'time_sheet_columns', 'document_status_id', 'observations', 'institution_id', 'time_sheet_original_data'
    ];

    /**
     * Método que obtiene la información personal del trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSupervisedGroup()
    {
        return $this->belongsTo(PayrollSupervisedGroup::class);
    }

    /**
     * Método que obtiene la información de los parámetros de hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollTimeSheetParameters()
    {
        return $this->belongsTo(PayrollTimeSheetParameter::class, 'payroll_time_sheet_parameter_id');
    }

    /**
     * Método que el estatus de la hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    /**
     * Método que obtiene la institución de la hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Scope para ordenar datos de Hojas de Tiempo
     *
     * @param  \Illuminate\Database\Eloquent\Builder Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, string $orderBy, string $ascending)
    {
        // Aplicar ordenación y filtrado según los parámetros de la tabla del servidor virtual
        if ($orderBy === 'payroll_supervised_group.code') {
            $query->join('payroll_supervised_groups', 'payroll_time_sheets.payroll_supervised_group_id', '=', 'payroll_supervised_groups.id')
                    ->orderBy('payroll_supervised_groups.code', $ascending);
        } elseif ($orderBy === 'payroll_supervised_group.supervisor') {
            $query->join('payroll_supervised_groups', 'payroll_time_sheets.payroll_supervised_group_id', '=', 'payroll_supervised_groups.id')
                    ->leftJoin('payroll_staffs as supervisors', 'payroll_supervised_groups.supervisor_id', '=', 'supervisors.id')
                    ->orderBy('supervisors.first_name', $ascending); // O el campo deseado para ordenar al supervisor
        } elseif ($orderBy === 'payroll_supervised_group.approver') {
            $query->join('payroll_supervised_groups', 'payroll_time_sheets.payroll_supervised_group_id', '=', 'payroll_supervised_groups.id')
                    ->leftJoin('payroll_staffs as approvers', 'payroll_supervised_groups.approver_id', '=', 'approvers.id')
                    ->orderBy('approvers.first_name', $ascending); // O el campo deseado para ordenar al aprobador
        } elseif ($orderBy === 'document_status.name') {
            $query->join('document_status', 'payroll_time_sheets.document_status_id', '=', 'document_status.id')
                    ->orderBy('document_status.name', $ascending);
        } elseif ($orderBy === 'date') { // Maneja el ordenamiento por "date"
            $query->orderBy('from_date', $ascending)
                  ->orderBy('to_date', $ascending);
        } else {
            $query->orderBy('payroll_time_sheets.' . $orderBy, $ascending);
        }

        // Para evitar ambigüedades en las columnas después de los joins
        return $query->select('payroll_time_sheets.*');
    }

    /**
     * Scope para buscar y filtrar datos de Hojas de Tiempo
     *
     * @param  \Illuminate\Database\Eloquent\Builder Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            // Intenta parsear el string de búsqueda como una fecha en formato 'd/m/Y'
            try {
                $parsedDate = Carbon::createFromFormat('d/m/Y', $search);
                if ($parsedDate) {
                    $formattedDate = $parsedDate->format('Y-m-d');
                    // Búsqueda exacta si from_date o to_date coinciden
                    $q->orWhereDate('from_date', $formattedDate)
                        ->orWhereDate('to_date', $formattedDate);
                }
            } catch (\Exception $e) {
                // Si no es una fecha 'd/m/Y', no se aplica el filtro exacto de fecha
            }

            // Búsqueda general insensible a mayúsculas/minúsculas para campos de fecha (parciales)
            // y campos relacionados
            $q->orWhere('from_date', 'ILIKE', "%{$search}%")
                ->orWhere('to_date', 'ILIKE', "%{$search}%")
                ->orWhereHas('payrollSupervisedGroup', function ($subQ) use ($search) {
                    $subQ->where('code', 'ILIKE', "%{$search}%")
                        ->orWhereHas('supervisor', function ($supQ) use ($search) {
                            $supQ->where('first_name', 'ILIKE', "%{$search}%")
                                ->orWhere('last_name', 'ILIKE', "%{$search}%")
                                ->orWhere('id_number', 'ILIKE', "%{$search}%")
                                ->orWhere('passport', 'ILIKE', "%{$search}%");
                        })
                        ->orWhereHas('approver', function ($appQ) use ($search) {
                            $appQ->where('first_name', 'ILIKE', "%{$search}%")
                                ->orWhere('last_name', 'ILIKE', "%{$search}%")
                                ->orWhere('id_number', 'ILIKE', "%{$search}%")
                                ->orWhere('passport', 'ILIKE', "%{$search}%");
                        });
                })
                ->orWhereHas('documentStatus', function ($subQ) use ($search) {
                    $subQ->where('name', 'ILIKE', "%{$search}%");
                });
        });
    }
}
