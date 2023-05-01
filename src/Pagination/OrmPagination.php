<?php

declare(strict_types=1);

namespace builder\Database\Pagination;

class OrmPagination{

    /**
     * Return boostrap pagination css style
     *
     * @return string \getBootstrapCss
     */
    static public function getBootstrapCss()
    {
        return "
            <style>
                .pagination{text-align:center;margin-top:20px}.pagination .page-item.active .page-link,.pagination .page-item.active .page-link:hover{background-color:#1098ad}.pagination .page-item{margin:0 1px}.pagination .page-link{border:0;height:40px;min-width:40px;text-align:center;padding:10px;font-weight:600;color:#212121;border-radius:2em;background:0 0;box-shadow:none}.pagination .page-item.disabled .page-link{background:0 0;color:#a6a6a6}.pagination .page-item:not(.disabled) .page-link:hover{background-color:#fff}.pagination .page-item.active .page-link,.pagination .page-item.active .page-link:hover{background-color:#1098ad}.pagination .page-item:first-child .page-link,.pagination .page-item:last-child .page-link{border-radius:2em}
            </style>";
    }

    /**
     * Return boostrap pagination css style
     *
     * @return string \getSimpleCss
     */
    static public function getSimpleCss()
    {
        return "
            <style>
                .pagination{text-align: center;margin-top: 20px;display: block;}.pagination * {margin: 0 5px 0 0;}.pagination span {background: 0 0;color: #a6a6a6;}.pagination span:not(.disabled) a[href]:hover {background-color: #fff;}
                .pagination a[href] {display: inline-block;background-color: rgba(0, 0, 0, 0);background: 0 0;text-align: center;color: #212121;font-weight: 600;padding: 5px 10px;border: 0;box-shadow: none;}.pagination a[href].active,.pagination a[href].active:hover {background-color: #d0e2ff;}
            </style>";
    }

}