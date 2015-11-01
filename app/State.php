<?php namespace pintegration;

use Illuminate\Database\Eloquent\Model;

class State extends Model {

    protected $table = 'states';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'id_prestashop'];

    public function address(){
        return $this->belongsTo('pintegration\Direccion');
    }
}
