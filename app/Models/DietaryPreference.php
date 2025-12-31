<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietaryPreference extends Model
{
    protected $primaryKey = 'dietary_preference_id';
    protected $guarded = ['dietary_preference_id'];
    protected $appends = ['public_image'];
    public function getPublicImageAttribute(){
        return $this->image ? asset('assets/dietary-preference-images') . '/' . $this->image : null;
    }
}
