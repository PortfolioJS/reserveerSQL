<?php

class DatabaseConnection
{
    protected $host = 'localhost';
    protected $username = 'root';
    protected $password = '';
    protected $database = 'reserveer';

    public $connection;

    public function __construct()
    {

        $Dsn = "mysql:host=" . $this->host . ';dbname=' . $this->database;

        $Options = array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        if ($this->connection === null)
            try {
                $this->connection = new PDO($Dsn, $this->username, $this->password, $Options);
            } catch (Exception $e) {
                echo 'Cannot connect to database server. Due to the following reason:';
                echo $e->getMessage();
                exit;
            }
    }
}
