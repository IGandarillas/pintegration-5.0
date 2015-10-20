<?php

namespace pintegration;

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    protected $fillable = ['client_id','address1','country','postcode','city'];

    public function client(){
        return $this->belongsTo('pintegration\Client');
    }
   /* public function state(){
        return $this->hasOne('pintegration\State');
    }*/
}
