<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class RdRand
{
    static public function mhw_rand(): int
    {
        $ffi = \FFI::cdef(
            "extern int check_rdrand();
            extern int get_rand(unsigned int *);
            extern unsigned int get_uint();"
            , __DIR__ . "/../../C_libs/rdrandget/librdrandget.so");

        return $ffi->get_uint();
    }


    static public function randomInt(int $min, int $max): int
    {
        $temp = $max + 1 - $min;
        if ($temp == 0) $temp = 1;
        return self::mhw_rand() % $temp + $min;
    }
}
