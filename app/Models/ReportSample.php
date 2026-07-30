<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSample extends Model
{
    protected $fillable = [
        'report_id','laboratory_code','sample_id','sample_description','sample_type','soil_condition','soil_color','soil_depth','soil_others','water_filtered','water_temperature','water_others','topography','coordinates','longitude','latitude','region','province','municipality','barangay','farm_area','crops','remarks','analysis_requested','analysis_requested_chemist','analysis_requested_agriculturist','subtotal',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
