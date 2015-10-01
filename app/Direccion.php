<?php

namespace pintegration;

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    public function client(){
        return $this->belongsTo('pintegration\Client');
    }
}
