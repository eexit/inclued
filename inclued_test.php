<?php
// Inclued package test
require_once 'Inclued.php';

echo '<h1>PHP: inclued extension test</h1><p>Current PHP version: ' . phpversion() . '<br />Inclued extension loaded: ';
echo function_exists('inclued_get_data') ? 'true' : 'false';
echo '<br />Inclued extension enabled: ';
echo ini_get('inclued.enabled') ? 'true</p>' : 'false</p>';


include '../lab.php'; // includes a « require 'path.func.php' »
include '../listFiles.class.php';
require_once '../test_class.php';
include_once '../listFiles.class.php';


$foo = new \inclued\Inclued;
$foo->genClue();
    ->saveClue(function($fn) {
        return date('Y-m-d') . '_' . $fn;
    })
    ->genGraph();

var_dump($foo->getClue());
?>