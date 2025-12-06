<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guide extends Model
{
    use HasFactory;
    protected $primaryKey = 'guide_id';
    protected $guarded = ['guide_id'];
}
