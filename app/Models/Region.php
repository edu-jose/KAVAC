<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Region extends Model implements Auditable
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

    protected $fillable = [
        'code',
        'name',
        'country_id',
    ];

    /**
     * Oculta los campos de fechas de creación, actualización y eliminación
     *
     * @var    array $hidden
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the country that owns the Region
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * The estates that belong to the Region
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function estates(): BelongsToMany
    {
        return $this->belongsToMany(Estate::class)->withTimestamps();
    }

    /**
     * Get all of the headquarters for the Region
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function headquarters(): HasMany
    {
        return $this->hasMany(Headquarter::class);
    }
}
