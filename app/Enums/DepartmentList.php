<?php

namespace App\Enums;

class DepartmentList
{
    public const DEPARTMENTS = [
        1 => 'BSA',
        2 => 'BSBA',
        3 => 'BSCRIM',
        4 => 'BSIT',
        5 => 'BSCE',
        6 => 'BEE',
    ];

    public static function ids()
    {
        return array_keys(self::DEPARTMENTS);
    }

    public static function names()
    {
        return self::DEPARTMENTS;
    }
}
