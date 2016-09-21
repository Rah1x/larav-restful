<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    const CREATED_AT = 'added_on';

    protected $guarded = [];


    /** stop updated_at **/
    public function getUpdatedAtColumn() {
    return null;
    }
    public function setUpdatedAt($value){
    return $this;
    }

}
