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
            '&quot;' => '', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'Ae',
            '&Auml;' => 'A', '�' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', '�' => 'Ae',
            '�' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'D', '?' => 'D',
            '�' => 'D', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '?' => 'E',
            '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'G', '?' => 'G',
            '?' => 'G', '?' => 'G', '?' => 'H', '?' => 'H', '�' => 'I', '�' => 'I',
            '�' => 'I', '�' => 'I', '?' => 'I', '?' => 'I', '?' => 'I', '?' => 'I',
            '?' => 'I', '?' => 'IJ', '?' => 'J', '?' => 'K', '?' => 'K', '?' => 'K',
            '?' => 'K', '?' => 'K', '?' => 'K', '�' => 'N', '?' => 'N', '?' => 'N',
            '?' => 'N', '?' => 'N', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O',
            '�' => 'Oe', '&Ouml;' => 'Oe', '�' => 'O', '?' => 'O', '?' => 'O', '?' => 'O',
            '�' => 'OE', '?' => 'R', '?' => 'R', '?' => 'R', '?' => 'S', '�' => 'S',
            '?' => 'S', '?' => 'S', '?' => 'S', '?' => 'T', '?' => 'T', '?' => 'T',
            '?' => 'T', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'Ue', '?' => 'U',
            '&Uuml;' => 'Ue', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U',
            '?' => 'W', '�' => 'Y', '?' => 'Y', '�' => 'Y', '?' => 'Z', '�' => 'Z',
            '?' => 'Z', '�' => 'T', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a',
            '�' => 'ae', '&auml;' => 'ae', '�' => 'a', '?' => 'a', '?' => 'a', '?' => 'a',
            '�' => 'ae', '�' => 'c', '?' => 'c', '?' => 'c', '?' => 'c', '?' => 'c',
            '?' => 'd', '?' => 'd', '�' => 'd', '�' => 'e', '�' => 'e', '�' => 'e',
            '�' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e',
            '�' => 'f', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'h',
            '?' => 'h', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '?' => 'i',
            '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'ij', '?' => 'j',
            '?' => 'k', '?' => 'k', '?' => 'l', '?' => 'l', '?' => 'l', '?' => 'l',
            '?' => 'l', '�' => 'n', '?' => 'n', '?' => 'n', '?' => 'n', '?' => 'n',
            '?' => 'n', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'oe',
            '&ouml;' => 'oe', '�' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', '�' => 'oe',
            '?' => 'r', '?' => 'r', '?' => 'r', '�' => 's', '�' => 'u', '�' => 'u',
            '�' => 'u', '�' => 'ue', '?' => 'u', '&uuml;' => 'ue', '?' => 'u', '?' => 'u',
            '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'w', '�' => 'y', '�' => 'y',
            '?' => 'y', '�' => 'z', '?' => 'z', '?' => 'z', '�' => 't', '�' => 'ss',
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