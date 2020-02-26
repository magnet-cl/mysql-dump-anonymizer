<?php

namespace PayU\MysqlDumpAnonymizer\Services;

use PayU\MysqlDumpAnonymizer\Services\LineParser\InterfaceLineParser;
use PayU\MysqlDumpAnonymizer\Services\LineParser\MySqlDumpLineParser;
use RuntimeException;

class LineParserFactory {

    public const LINE_PARSER_MYSQL_DUMP = 'mysqldump';


    public function chooseLineParser($configString) : InterfaceLineParser {

        if ($configString === self::LINE_PARSER_MYSQL_DUMP) {
            return new MySqlDumpLineParser();
        }

        throw new RuntimeException('Invalid line parser config');
    }


}