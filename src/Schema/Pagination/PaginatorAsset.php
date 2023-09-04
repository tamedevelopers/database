<?php

namespace builder\Database\Schema\Pagination;


class PaginatorAsset
{

    /**
     * Pagination Default Texts
     * @param string $mode
     * [optional] get array data
     *
     * @return array
     */
    public static function texts(string $mode = null)
    {
        $data =  [
            'first'     => 'First',
            'last'      => 'Last',
            'next'      => 'Next',
            'prev'      => 'Prev',
            'span'      => 'page-span',
            'showing'   => 'Showing',
            'to'        => 'to',
            'of'        => 'of',
            'results'   => 'results',
            'view'      => 'simple',
            'buttons'   => 5,
        ];

        return $data[$mode] ?? $data;
    }

    /**
     * Pagination Views Style
     * @param string $mode
     * [optional] get array data
     * 
     * @return array|string
     */
    public static function views(string $mode = null)
    {
        $data = [
            'bootstrap' => 'bootstrap',
            'simple'    => 'simple',
            'cursor'    => 'cursor',
        ];

        return $data[$mode] ?? $data;
    }
    
    /**
     * Customize browser header
     *
     * @return void
     */
    public static function headerControlNoCache()
    {
        if (!headers_sent()) {
            @header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            @header("Pragma: no-cache");
            @header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
        }
    }

    /**
     * Pagination style
     * @param string $mode
     * 
     * @return array
     */
    public static function getStyles(?string $mode = 'simple')
    {
        if(!defined('STYLE_EXISTS')){
            // Helps to define not getting style more than once per page
            define('STYLE_EXISTS', $mode);

            if($mode == self::views('simple')){
                return self::getSimpleCss();
            } elseif($mode == self::views('bootstrap')){
                return self::getBootstrapCss();
            } 
            return self::getCursorCss();
        } elseif(STYLE_EXISTS != $mode){
            if($mode == self::views('simple')){
                return self::getSimpleCss();
            } elseif($mode == self::views('bootstrap')){
                return self::getBootstrapCss();
            } 
            return self::getCursorCss();
        }
    }
    
    /**
     * Return boostrap pagination css style
     *
     * @return string
     */
    public static function getBootstrapCss()
    {
        return self::compressCss("<style>
            .pagination{text-align:center;margin-top:20px}.pagination .page-item.active .page-link,.pagination .page-item.active .page-link:hover{background-color:#1098ad}.pagination .page-item{margin:0 1px}.pagination .page-link{border:0;height:40px;min-width:40px;text-align:center;padding:10px;font-weight:600;color:#212121;border-radius:2em;background:0 0;box-shadow:none}.pagination .page-item.disabled .page-link{background:0 0;color:#a6a6a6}.pagination .page-item:not(.disabled) .page-link:hover{background-color:#fff}.pagination .page-item.active .page-link,.pagination .page-item.active .page-link:hover{background-color:#1098ad}.pagination .page-item:first-child .page-link,.pagination .page-item:last-child .page-link{border-radius:2em}
        </style>");
    }

    /**
     * Return boostrap pagination css style
     *
     * @return string
     */
    public static function getSimpleCss()
    {
        return self::compressCss("<style>
            .pagination{text-align: center;margin-top: 20px;display: block;}.pagination * {margin: 0 5px 0 0;}.pagination span {background: 0 0;color: #a6a6a6;}.pagination span:not(.disabled) a[href]:hover {background-color: #fff;} .pagination a[href] {display: inline-block;background-color: rgba(0, 0, 0, 0);background: 0 0;text-align: center;color: #212121;font-weight: 600;padding: 5px 10px;border: 0;box-shadow: none;}.pagination a[href].active,.pagination a[href].active:hover {background-color: #d0e2ff;}
        </style>");
    }

    /**
     * Return cursor pagination css style
     *
     * @return string
     */
    public static function getCursorCss()
    {
        return self::compressCss("<style>
            .pagination{text-align: center;}.pagination-cursor{display: inline-block;width: 90%;max-width: 150px;margin: 0px auto 30px auto !important;border: 1px solid #fff;box-shadow: rgba(0, 0, 0, 0.1) -4px 9px 25px -6px;border-radius: 12px;padding: 9px 10px;}.pagination .pagination-cursor a[href]{outline: none;text-decoration: none;cursor: pointer;font-size: 14px;color: #000;font-weight: 550;}.pagination .pagination-cursor a[href].active, .pagination .pagination-cursor a[href].active:hover {background-color: #fff;}.pagination .pagination-cursor a[href].active{margin: 0 3px 0 3px;}.pagination .pagination-cursor span {background: 0 0;color: #a6a6a6;}.pagination .pagination-cursor span:not(.disabled) a[href]:hover {background-color: #fff;}
        </style>");
    }

    /**
     * Compresses CSS by removing comments, compressing spaces, and trimming the resulting string.
     *
     * @param string $s The CSS string to compress
     * 
     * @return string 
     * - The compressed CSS string
     */
    private static function compressCss(string $string)
    {
        // Step 1: Remove CSS comments (/* ... */)
        $string = preg_replace('#/\*.*?\*/#s', '', $string);

        // Step 2: Compress consecutive whitespace characters into a single space
        $string = preg_replace('#[ \t\r\n]+#', ' ', $string);

        // Step 3: Remove spaces before certain characters to ensure proper CSS syntax
        $string = preg_replace('# ([^0-9a-z.\#*-])#i', '$1', $string);

        // Step 4: Remove spaces after certain characters to ensure proper CSS syntax
        $string = preg_replace('#([^0-9a-z%]) #i', '$1', $string);

        // Step 5: Remove leading semicolon after a closing bracket
        $string = str_replace(';}', '}', $string);

        // Trim leading and trailing whitespace
        return trim($string);
    }
}
