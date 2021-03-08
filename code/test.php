<?php
Class A
{
    public $a;
}

$a = new A();
$a->a = '1';
$b = $a;
$b->a = '2';
$c = '2';
$d = $c;
$c = '3';
var_dump($a);
var_dump($b);
var_dump($c);
var_dump($d);
var_dump($a === $b);