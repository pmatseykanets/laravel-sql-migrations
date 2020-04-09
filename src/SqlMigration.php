<?php

namespace SqlMigrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

abstract class SqlMigration extends Migration
{
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
            $ignore = false;
            foreach($file as $line){
                $query = trim($line);
                $query = $this->manipulateCommentBlock($query, $ignore);
                if (!$query) {
                    continue;
                }
                if(is_int(strpos($query, ';'))){
                    $connection->unprepared("$sql $query");
                    $sql = '';
                } else {
                    $sql .= " $query";
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