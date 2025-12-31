<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guide extends Model
{
    use HasFactory;
    protected $primaryKey = 'guide_id';
    protected $guarded = ['guide_id'];
    protected $appends = ['public_image', 'excerpt'];
    
    public function getRouteKeyName(){
        return 'slug';
    }
    
    public function getPublicImageAttribute(){
        return $this->image ? asset('storage') . '/' . $this->image : null;
    }

    public function getExcerptAttribute(){
        return mb_substr($this->content, 0, 50);
    }
}
