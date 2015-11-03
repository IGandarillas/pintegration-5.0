<?php namespace Pipedrive;

use PSWebS\PrestaShopWebserviceException;

class Pipedrive
{

    public function __construct(){

    }
    public function searchName($name){

        $splited = explode(" ", $name);
        $match_words = array();

        foreach($splited as $split) {
            $split = $this->normlizr($split);
            $data = file_get_contents(storage_path('\\names.txt'));

            if (preg_match("/\b$split\b/", $data ,$matches)) {
                array_push($match_words,$split);
            }
        }
        $last_match = end($match_words);
        $word_index = -1;
        foreach($splited as $key => $value){
        //Key is an index number.
            if($this->normlizr($value) == $last_match){
                $word_index = $key;
            }
        }
       // echo 'HOLA'.$word_index;
        if($word_index>=0){
            $splitedOriginal = explode(" ", $name);
            $firstname  = implode(" ",array_slice($splitedOriginal,0,$word_index+1));
            $lastname   = implode(" ",array_slice($splitedOriginal,$word_index+1));
            return array($firstname,$lastname);
        }

        return 0;
    }
    public function normlizr($chain)
    {
        $replace = [
            '&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
            '&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
            '&Auml;' => 'A', 'Å' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', 'Æ' => 'Ae',
            'Ç' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'D', '?' => 'D',
            'Ğ' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', '?' => 'E',
            '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'G', '?' => 'G',
            '?' => 'G', '?' => 'G', '?' => 'H', '?' => 'H', 'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I', '?' => 'I', '?' => 'I', '?' => 'I', '?' => 'I',
            '?' => 'I', '?' => 'IJ', '?' => 'J', '?' => 'K', '?' => 'K', '?' => 'K',
            '?' => 'K', '?' => 'K', '?' => 'K', 'Ñ' => 'N', '?' => 'N', '?' => 'N',
            '?' => 'N', '?' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', '?' => 'O', '?' => 'O', '?' => 'O',
            'Œ' => 'OE', '?' => 'R', '?' => 'R', '?' => 'R', '?' => 'S', 'Š' => 'S',
            '?' => 'S', '?' => 'S', '?' => 'S', '?' => 'T', '?' => 'T', '?' => 'T',
            '?' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', '?' => 'U',
            '&Uuml;' => 'Ue', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U',
            '?' => 'W', 'İ' => 'Y', '?' => 'Y', 'Ÿ' => 'Y', '?' => 'Z', '' => 'Z',
            '?' => 'Z', 'Ş' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
            'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', '?' => 'a', '?' => 'a', '?' => 'a',
            'æ' => 'ae', 'ç' => 'c', '?' => 'c', '?' => 'c', '?' => 'c', '?' => 'c',
            '?' => 'd', '?' => 'd', 'ğ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
            'ë' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e',
            'ƒ' => 'f', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'h',
            '?' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', '?' => 'i',
            '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'ij', '?' => 'j',
            '?' => 'k', '?' => 'k', '?' => 'l', '?' => 'l', '?' => 'l', '?' => 'l',
            '?' => 'l', 'ñ' => 'n', '?' => 'n', '?' => 'n', '?' => 'n', '?' => 'n',
            '?' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
            '&ouml;' => 'oe', 'ø' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', 'œ' => 'oe',
            '?' => 'r', '?' => 'r', '?' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
            'û' => 'u', 'ü' => 'ue', '?' => 'u', '&uuml;' => 'ue', '?' => 'u', '?' => 'u',
            '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'w', 'ı' => 'y', 'ÿ' => 'y',
            '?' => 'y', '' => 'z', '?' => 'z', '?' => 'z', 'ş' => 't', 'ß' => 'ss',
            '?' => 'ss', '??' => 'iy', '?' => 'A', '?' => 'B', '?' => 'V', '?' => 'G',
            '?' => 'D', '?' => 'E', '?' => 'YO', '?' => 'ZH', '?' => 'Z', '?' => 'I',
            '?' => 'Y', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => 'O',
            '?' => 'P', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U', '?' => 'F',
            '?' => 'H', '?' => 'C', '?' => 'CH', '?' => 'SH', '?' => 'SCH', '?' => '',
            '?' => 'Y', '?' => '', '?' => 'E', '?' => 'YU', '?' => 'YA', '?' => 'a',
            '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'yo',
            '?' => 'zh', '?' => 'z', '?' => 'i', '?' => 'y', '?' => 'k', '?' => 'l',
            '?' => 'm', '?' => 'n', '?' => 'o', '?' => 'p', '?' => 'r', '?' => 's',
            '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c', '?' => 'ch',
            '?' => 'sh', '?' => 'sch', '?' => '', '?' => 'y', '?' => '', '?' => 'e',
            '?' => 'yu', '?' => 'ya'
        ];

        $chain= str_replace(array_keys($replace), $replace, $chain);
        $chain = strtolower($chain);
        return $chain;
    }
}