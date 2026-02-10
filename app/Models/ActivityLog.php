<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = true;
    protected $primaryKey = 'activity_log_id';
    protected $guarded = ['activity_log_id'];
}
