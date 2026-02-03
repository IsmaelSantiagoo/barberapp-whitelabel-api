<?php

namespace App\Models;

use App\Traits\BelongsToBarbershop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessHour extends Model
{
    use HasUuids, BelongsToBarbershop;

    /**
     * The table associated with the model.
     */
    protected $table = 'business_hours';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'barbershop_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_open',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'day_of_week' => 'integer',
        'is_open' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Days of week constants
     */
    public const SUNDAY = 0;
    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY = 4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;

    /**
     * Get the day name.
     */
    public function getDayName(): string
    {
        $days = [
            self::SUNDAY => 'Sunday',
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }
}
