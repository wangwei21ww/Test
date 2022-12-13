<?php

namespace Less\Helper\ArrayHelper;

/**
 * The function iin_array would be checks if a value exists in an array,
 * and this function different with in_array are case-insensitive.
 * @param string $needle
 * @param array $haystack
 * @param boolean $strict defaults to false
 * @return boolean
 */
function iin_array($needle, array $haystack, $strict = false)
{
    return in_array(strtolower($needle), array_map('Less\Helper\ArrayHelper\convert2lower', $haystack), $strict);
}

function convert2lower($mixed)
{
    if(is_string($mixed))
    {
        return strtolower($mixed);
    }
    if(is_array($mixed))
    {
        return array_map(__FUNCTION__, $mixed);
    }
}