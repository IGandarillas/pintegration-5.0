<?php namespace pintegration;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model {

    protected $fillable = ['user_id'];

    public function user(){
        return $this->belongsTo('pintegration\User');
    }

}
