<?php
/*
 * MOTD Fixer Server Script
 *
 * Fixes the MOTD loading of data under Counter-Strike : Global Offensive
 *
 * Coded by dubbeh - www.dubbeh.net
 *
 * Licensed under the GPLv3
 *
 * Database wrapper class - Idea based from Mike @ http://stackoverflow.com/users/340355/mike
 *
 * http://stackoverflow.com/questions/6740153/simple-pdo-wrapper
 *
 */

class MOTDDB
{
    private $dbh;
    private $stmt;

    function __construct() {
        $this->dbh = new PDO(
            "mysql:host=".MYSQL_DB_HOST.";dbname=".MYSQL_DB_NAME,
            MYSQL_DB_USER,
            MYSQL_DB_PASS,
            array( PDO::ATTR_PERSISTENT => true)
        );
        $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    }

    function __destruct() {
        $this->dbh = null;
    }

    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
        return $this;
    }

    public function bind($pos, $value, $type = null) {

        if( is_null($type) ) {
            switch( true ) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($pos, $value, $type);
        return $this;
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function resultset() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resultsetBoth() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_BOTH);
    }
    
    public function resultsetNum() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_NUM);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function singleNum() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_NUM);
    }
    
    public function executeRows () {
        $this->stmt->execute();
        return $this->stmt->rowCount();
    }
    
    public function getStatement () {
        return $this->stmt;
    }
}
?>