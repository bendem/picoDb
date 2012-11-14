<?php

namespace PicoDb;

class Database
{
    private $logs = array();
    private $identifer = '"';
    private $pdo;


    public function __construct(array $settings)
    {
        if (! isset($settings['driver'])) {

            throw new LogicException('You must define a database driver.');
        }

        switch ($settings['driver']) {

            case 'sqlite':
                $this->pdo = new \PDO('sqlite:'.$settings['filename']);
                $this->pdo->exec('PRAGMA foreign_keys = ON');
                $this->identifier = '"';
                break;

            case 'mysql':
                $this->pdo = new \PDO(
                    'mysql:host='.$settings['hostname'].';dbname='.$settings['dbname'],
                    $settings['username'],
                    $settings['password']
                );
                $this->identifier = '`';
                break;

            default:
                throw new LogicException('This database driver is not supported.');
        }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }


    public function setLogMessage($message)
    {
        $this->logs[] = $message;
    }


    public function getLogMessages()
    {
        return implode(', ', $this->logs);
    }


    public function getConnection()
    {
        return $this->pdo;
    }


    public function escapeIdentifier($identifier)
    {
        return $this->identifier.$identifier.$this->identifier;
    }


    public function execute($sql, array $values = array())
    {
        try {

            $this->setLogMessage($sql);
            $this->setLogMessage(implode(', ', $values));

            $rq = $this->pdo->prepare($sql);
            $rq->execute($values);

            return $rq;
        }
        catch (\PDOException $e) {

            $this->setLogMessage($e->getMessage());
            return false;
        }
    }


    public function table($table_name)
    {
        return new Table($this, $table_name);
    }
}