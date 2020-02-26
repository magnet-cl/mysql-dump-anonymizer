<?php

namespace PayU\MysqlDumpAnonymizer;


class RuntimeProgress
{
    private const FULL = "\u{2588}";
    private const EMPTY = "\u{2591}";

    public static $output = STDERR;

    public static function output($string)
    {
        fwrite(self::$output, $string);
    }

    public static function show($read, $total)
    {
        self::output("\r");
        self::goUp(1);

        $perc = round($read / $total * 100, 0);
        self::showProgressBar(self::FULL, self::EMPTY, $perc);
        $str = $read . '/' . $total . ' bytes (' . (round($read / $total * 100, 4)) . '%)  ';
        self::output('  ' . $str);
        self::output(PHP_EOL);
    }

    private static function showProgressBar($full, $empty, $percentage)
    {
        $progress = '';
        for ($i = 1; $i <= 100; $i++) {
            if ($i <= $percentage) {
                $progress .= $full;
            } else {
                $progress .= $empty;
            }
        }
        self::output('  ' . $progress . ' ' . $percentage);
    }

    public static function goUp($times = 1)
    {
        self::output(chr(27) . '[' . $times . 'A');
    }

}