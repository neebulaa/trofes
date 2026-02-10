<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $primaryKey = 'message_id';
    protected $guarded = ['message_id'];

    public function getRouteKeyName(){
        return 'message_id';
    }
}
