<?php
namespace Core\Driver\PDOPgSql;
use Doctrine\DBAL\Platforms;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;

class PostgreSqlPlatform extends \Doctrine\DBAL\Platforms\PostgreSqlPlatform {
    protected $index_algorithm_keyword = '_USING_';

    public function getCreateIndexSQL(Index $index, $table)
    {
        if ($table instanceof Table) {
            $table = $table->getQuotedName($this);
        }
        $name = $index->getQuotedName($this);
        $columns = $index->getQuotedColumns($this);

        if (count($columns) == 0) {
            throw new \InvalidArgumentException("Incomplete definition. 'columns' required.");
        }

        if ($index->isPrimary()) {
            return $this->getCreatePrimaryKeySQL($index, $table);
        }

        $query = 'CREATE ' . $this->getCreateIndexSQLFlags($index) . 'INDEX ' . $this->getParsedName($name) . ' ON ' . $table;

        if ($algorithm = $this->getIndexAlgorithm($index)) {
            $query .= ' USING ' . $algorithm;
        }
        $query .= ' (' . $this->getIndexFieldDeclarationListSQL($columns) . ')';

        return $query;
    }

    /**
     * Remove $index_algorithm_keyword from index name if it exists
     *
     * @param $name
     * @return string
     */
    private function getParsedName($name)
    {
        if ($pos = stripos($name, $this->index_algorithm_keyword)) {
            return str_ireplace($algorithm = substr($name, $pos), '', $name);
        }
        return $name;
    }

    /**
     * Returns algorithm's name from index name in the form of 'index_name_USING_ALGORITHM'
     * Being 'index_name' the index name and 'ALGORITHM' the algorithm used.
     *
     * @param $index
     * @return null|string
     */
    private function getIndexAlgorithm($index)
    {
        $algorithm = null;
        $name = $index->getQuotedName($this);
        if ($pos = stripos($name, $this->index_algorithm_keyword)) {
            $algorithm = substr($name, $pos + strlen($this->index_algorithm_keyword));
        }
        return $algorithm;
    }
} 