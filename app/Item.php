<?php

namespace pintegration;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['id_item_prestashop','user_id','name','description','code','id_item_pipedrive'];
    public function user(){
        return $this->belongsTo('pintegration\User');
    }
}
