<?php
/**
 * Created by PhpStorm.
 * User: Amine
 * Date: 01/17/2022
 * Time: 12:21
 */


$route['^file/([0-9]+)/(.*)'] = function ($v1,$v2) {
    return 'uploads/files/'.$v1.'/'.$v2;
};







