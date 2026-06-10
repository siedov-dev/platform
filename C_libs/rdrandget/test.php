<?php

$ffi = FFI::cdef(
    "extern int check_rdrand();
    extern int get_rand(unsigned int *);
    extern unsigned int get_uint();"
    ,"librdrandget.so");

$r = $ffi->get_uint();
var_dump($r);
var_dump($r%8);
echo $r;
