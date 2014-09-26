<?php
namespace Core\Driver\PDOPgSql;

use Doctrine\DBAL\Platforms;

class Driver extends \Doctrine\DBAL\Driver\PDOPgSql\Driver
{
    public function getDatabasePlatform()
    {
        return new \Core\Driver\PDOPgSql\PostgreSqlPlatform();
    }

    public function getName()
    {
        return 'pdo_pgsql_ext';
    }
}

