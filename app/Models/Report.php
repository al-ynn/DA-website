<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    protected $fillable = [
        'user_id','request_code','date','status','is_draft','surname','first_name','middle_name','full_name','rsbsa_no','company_name','classification','student_type','sex','age','address','contact_number','email_address','sampling_date','sampling_time','mode_of_release','retrieve_sample','agreed_release_date','number_of_samples','date_received','received_by','payment_status','deposit','or_no','payment_date','balance','total_amount_due','total_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'sampling_date' => 'date',
        'agreed_release_date' => 'date',
        'date_received' => 'date',
        'payment_date' => 'date',
        'is_draft' => 'boolean',
        'retrieve_sample' => 'boolean',
        'deposit' => 'decimal:2',
        'balance' => 'decimal:2',
        'total_amount_due' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(ReportSample::class);
    }
}
