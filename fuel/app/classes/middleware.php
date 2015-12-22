<?php
// namespace 
/**
 * midleware 
 * 
 */
function handle() {
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
    header('X-Content-Type-Options: nosniff');
    error_reporting(-1);
}
