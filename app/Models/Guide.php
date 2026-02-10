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
        return $this->image ? asset('storage') . '/' . $this->image : asset('assets/sample-images/default-image.png');
    }

    public function getExcerptAttribute(){
        $text = trim(
            html_entity_decode(
                strip_tags($this->content)
            )
        );

        return mb_strlen($text) > 50
            ? mb_substr($text, 0, 50) . '…'
            : $text;
    }
}
