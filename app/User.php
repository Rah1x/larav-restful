<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
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

    /** relationships **/
    public function address()
    {
        return $this->hasOne('App\User_info');
    }

}
