<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    protected $primaryKey = 'allergy_id';

    protected $guarded = ['allergy_id'];
}