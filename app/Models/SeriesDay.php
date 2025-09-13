<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesDay extends Model
{

    protected $fillable = [
        'series_id',
        'day_of_week',
    ];

    public function series()
    {
        return $this->belongsTo(LectureSeries::class, 'series_id');
    }

}
