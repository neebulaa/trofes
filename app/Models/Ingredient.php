<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $primaryKey = 'ingredient_id';

    protected $guarded = ['ingredient_id'];
}
