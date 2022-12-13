<?php

namespace Less\Helper\String;

use Less\Foundation\ClassLoader;

function substr($string, $start, $length, $encoding = null)
{
    if ($encoding === null) {
        $encoding = \Less::getApp()->charset;
    }
    if (function_exists('mb_substr')) {
        return mb_substr($string, $start, $length, $encoding);
    }
    return substr($string, $start, $length);
}

/*
 * The function 'generate_random' to generate a random string.
 * and the length defaults to 32.
 */
function generate_random($length = 32)
{
    if (function_exists('openssl_random_pseudo_bytes')) {
        $string = base64_encode(openssl_random_pseudo_bytes($length, $strong));
        if ($strong === true) {
            return substr($string, 0, $length);
        }
    }
    $hash   = str_shuffle(md5(rand() . microtime() . time()));
    $string = base64_encode(str_shuffle(base64_encode($hash) . sha1($hash)));
    return substr($string, 0, $length);
}

function html_purifier($content, $options = [])
{
    if (ClassLoader::hasNamespace('HtmlPurifier') === false) {
        ClassLoader::registerNamespace('HtmlPurifier', LESS_PATH . 'Vendors' . DS . 'Security' . DS . 'HtmlPurifier' . DS);
    }

    $purifier = new \HtmlPurifier($options);
    $purifier->config->set('Cache.SerializerPath', \Less::getApp()->getRuntimePath());
    return $purifier->purify($content);
}

function get_charset($data, $curl_content_type = '')
{
    unset($charset);
    /* 1: HTTP Content-Type: header */
    preg_match('@([\w/+]+)(;\s*charset=(\S+))?@i', $curl_content_type, $matches);
    if (isset($matches[3])) {
        $charset = $matches[3];
    }

    /* 2: <meta> element in the page */
    if (!isset($charset)) {
        preg_match('@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s*charset=([^\s"]+))?@i', $data, $matches);
        if (isset($matches[3])) {
            $charset = $matches[3];
        }

    }

    /* 3: <xml> element in the page */
    if (!isset($charset)) {
        preg_match('@<\?xml.+encoding="([^\s"]+)@si', $data, $matches);
        if (isset($matches[1])) {
            $charset = $matches[1];
        }

    }

    /* 4: PHP's heuristic detection */
    if (!isset($charset)) {
        $encoding = mb_detect_encoding($data);
        if ($encoding) {
            $charset = $encoding;
        }

    }

    /* 5: Default for HTML */
    if (!isset($charset)) {
        if (strstr($content_type, "text/html") === 0) {
            $charset = "ISO 8859-1";
        }

    }
    return $charset;
}
