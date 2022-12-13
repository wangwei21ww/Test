<?php
/**
 * This file is part of the Less
 * @author Stephen Lee <stephen.lee@lesscloud.com>
 * @link https://lesscloud.com/
 * @license All copyright and license information, please visit the web page
 *           https://lesscloud.com/license
 * @version $Id$
 */

namespace Less\Helper;

class Html
{
    public static function stripWhitespace($content, $stripStr, $replaceStr, $stripStrNum = 1, $trimLeft = false, $trimRight = true)
    {
        $stripStrs = '';
        for ($i = 0; $i < $stripStrNum; $i++) {
            $stripStrs .= $stripStr;
        }
        return str_replace($stripStrs, $replaceStr, $content);
    }

    public static function encode($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, \Less::getApp()->charset);
    }

    public static function decode($text)
    {
        return htmlspecialchars_decode($text, ENT_QUOTES);
    }

    public static function createLink($link, $content, array $htmlOptions = [])
    {
        $htmlOptions['href'] = $link;
        return self::tag('a', $content, $htmlOptions);
    }

    /**
     * Creates a custom tag
     */
    public static function tag($name, $text = '', array $options = [], $closeTag = true)
    {
        $tag = '<' . $name . self::getHtmlOptions($options);
        if ($closeTag === true) {
            $tag .= '>' . $text . '</' . $name . '>';
        } else {
            $tag .= ' />';
        }
        return $tag;
    }

    /**
     * Convert the parameter key&value pairs to a string
     * @param array $options
     * @return string
     */
    public static function getHtmlOptions(array $options)
    {
        $tagOptions = [];
        foreach ($options as $option => $value) {
            $tagOptions[] = $option . '="' . $value . '"';
        }
        return count($tagOptions) > 0 ? ' ' . implode(" ", $tagOptions) : '';
    }
}
