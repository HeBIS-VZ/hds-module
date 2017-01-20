<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.17
 * Time: 17:09
 */

namespace Hebis\View\Helper\Record;


trait CorporateHelperTrait
{

    /**
     * @param $subFields
     * @return string
     */
    protected function getAeg($subFields)
    {
        $ret = "";
        foreach ($subFields as $key => $subField) {
            switch ((string) $key) {
                case 'a':
                    $ret .= htmlentities($subField);
                    break;
                case 'e':
                    $ret .= ". " . htmlentities($subField);
                    break;
                case 'g':
                    $ret .= " (".htmlentities($subField).")";
            }
        }
        return $ret;
    }

    /**
     * @param $subFields
     * @return string
     */
    protected function getAbgn($subFields)
    {
        $ret = "";
        foreach ($subFields as $key => $subField) {
            switch ((string) $key) {
                case 'a':
                    $ret .= htmlentities($subField);
                    break;
                case 'b':
                    $ret .= ". " . htmlentities($subField);
                    break;
                case 'g':
                case 'n':
                    $ret .= " (".htmlentities($subField).")";
            }
        }
        return $ret;
    }


    /**
     * @param $subFields
     * @return array
     */
    protected function getNdc($subFields)
    {
        $keys = ['n', 'd', 'c'];

        $ndc_ = array_filter($subFields, function ($key) use ($keys) {
            return in_array($key, $keys, true);
        }, ARRAY_FILTER_USE_KEY);

        /* sortiere ndc so dass ndc = ['n' => ...,'d' => ...,'c' => ...] */
        $ndc = [];
        foreach ($ndc_ as $key => $value) {
            $k = array_search($key, $keys);
            $ndc[$k] = htmlentities($value);
        }
        return $ndc;
    }

    protected function expandSubfield($subfields)
    {
        if (is_array($subfields)) {
            return implode(", ", $this->toStringArray($subfields));
        }
        return "";
    }
}