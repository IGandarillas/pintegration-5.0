<?php

namespace pintegration;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['id_client_prestashop','firstname','lastname','user_id','id_client_pipedrive'];

    public function user(){
        return $this->belongsTo('pintegration\User');
    }
    public function direccion(){
        return $this->hasOne('pintegration\Direccion');
    }

}
