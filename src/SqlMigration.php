<?php

namespace SqlMigrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class SqlMigration extends Migration
{
    /**
     * @var array
     */
    private static $keywords = [
        'ALTER', 'CREATE', 'DELETE', 'DROP', 'INSERT',
        'REPLACE', 'SELECT', 'SET', 'TRUNCATE', 'UPDATE', 'USE',
        'DELIMITER', 'END', 'DECLARE'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->apply('up');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->apply('down');
    }

    /**
     * Apply the migration.
     *
     * @param $path
     * @param mixed $direction
     */
    public function apply($direction)
    {
        if (file_exists($path = $this->migrationFile($direction))) {
            $connection = DB::connection($this->getConnection());
            $file = file($path);
            $sql = '';
            $delNum = false;
            $ignore = false;
            foreach($file as $line){
                $query = trim($line);
                $query = $this->manipulateCommentBlock($query, $ignore);
                if (!$query) {
                    continue;
                }
                $delimiter = is_int(strpos($query, "DELIMITER"));
                if($delimiter || $delNum){
                    if($delimiter && !$delNum ){
                        $sql = '';
                        $sql =  "$query; ";
                        $delNum = true;
                    }else if($delimiter && $delNum){
                        $sql .=  "$query ";
                        $delNum = false;
                        $connection->unprepared($sql);
                        $sql = '';
                    }else{
                        $sql .= "$query; ";
                    }
                }else{
                    $delimiter = is_int(strpos($query, ";"));
                    if($delimiter){
                        $connection->unprepared("$sql $query");
                        $sql = '';
                    } else {
                        $sql .= " $query";
                    }
                }
            }
        }
    }

    /**
     * @param $line
     * @param $ignoreLine
     * @return string
     */
    private function manipulateCommentBlock($query, &$ignore)
    {
        if (is_int(strpos($query, '-- '))) {
            $query = substr_replace($query, '', strpos($query, '-- '));
        }
        while (is_int(strpos($query, '*/'))) {
            $posOpen = strpos($query, '/*');
            $posClose = strpos($query, '*/');
            if (is_int($posOpen) && $posOpen < $posClose) {
                $query = substr_replace($query, '', $posOpen, $posClose - $posOpen + 2);
            } else {
                $query = substr_replace($query, '', 0, $posOpen + 2);
            }
            $ignore = false;
        }
        if ($ignore) {
            return '';
        }
        if (is_int(strpos($query, '/*'))) {
            $query = substr_replace($query, '', strpos($query, '/*'));
            $ignore = true;
        }
        $query = trim($query);
        return $query;
    }

    /**
     * @param $direction
     * @return string|string[]|null
     */
    public function migrationFile($direction)
    {
        return preg_replace(
            '/\.php$/',
            ".$direction.sql",
            (new \ReflectionObject($this))->getFileName()
        );
    }
}
