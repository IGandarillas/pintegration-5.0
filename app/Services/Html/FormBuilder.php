<?php namespace pintegration\Services\Html;
/**
 * Created by PhpStorm.
 * User: yukorff
 * Date: 18/10/2015
 * Time: 23:26
 */
class FormBuilder extends \Collective\Html\FormBuilder {


    public function datetime($name, $value = null, $options = array())
    {
        if ($value instanceof \DateTime)
        {
            $value = $value->format('Y-m-d');
        }

        return $this->input('datetime', $name, $value, $options);
    }
}