<?php

namespace App\Models;

use PDO;
use PDOException;

class QueryBuilder
{
    private PDO $pdo;
    private string $query = '';

    /**
     * @param string[] $data
     */
    public function __construct(private array $data)
    {
        $dsn = $this->data['databaseType'] . ":host=" . $this->data['host'] . ";dbname=" . $this->data['databaseName'];

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        ];

        try {
            $this->pdo = new PDO($dsn, $user = $this->data['username'], $password = $this->data['password'], $options);
        } catch (PDOException $PDOException) {
            throw new PDOException($PDOException->getMessage(), (int)$PDOException->getCode());
        }

    }


    public function select(array $args = []): self
    {
        $this->query .= 'SELECT ';

        if (count($args) === 1) {
            $this->query .= "$args[0] ";
        } else {
            foreach ($args as $arg) {
                if ($arg === end($args)) {
                    $this->query .= "$arg ";
                } else {
                    $this->query .= "$arg,";
                }

            }
        }
        return $this;
    }

    public function count(string $whatCount): self
    {
        $this->query .= 'COUNT(';
        $this->query .= $whatCount . ')' . ' ';

        return $this;
    }

    public function from(string $tableName): self
    {
        $this->query .= 'FROM ';
        $this->query .= "$tableName ";
        return $this;
    }


    public function where(string $arg, string $comparisonSign, int|string $comparingWith): self
    {
        $this->query .= 'WHERE ';
        $this->query .= $arg . ' ' . $comparisonSign . ' ' . $comparingWith . ' ';
        return $this;
    }


    public function whereIn(string $value, array $allValues): self
    {
        $this->query .= 'WHERE ';
        $this->query .= $value . ' ' . 'IN ';
        $this->query .= '(';

        for ($i = 0; $i < count($allValues); $i++) {
            if ($i === count($allValues) - 1) {
                $this->query .= "'$allValues[$i]') ";
            } else {
                $this->query .= "'$allValues[$i]',";
            }
        }

        return $this;
    }


    public function orderBy(string $orderArg, string $ascDesc = null): self
    {
        $this->query .= 'ORDER BY ';
        $this->query .= "$orderArg ";

        if ($ascDesc) {
            $this->query .= "$ascDesc ";
        }

        return $this;
    }


    public function join(string $typeOfJoin, string $tableName, string $firstColumn, string $secondColumn): self
    {

        $joinArr = [mb_strtoupper($typeOfJoin),$tableName,'ON',$firstColumn,'=',$secondColumn];
        foreach ($joinArr as $elem) {
            $this->query .= "$elem ";
        }

        return $this;
    }


    public function limit(string|int $numberOfLimit): self
    {
        $this->query .= 'LIMIT ';
        $this->query .= $numberOfLimit . " ";

        return $this;
    }


    public function andOr(string $andOr, string $arg, string $comparisonSign, int|string $comparingWith): self
    {
        $andOr = mb_strtoupper($andOr);
        if ($andOr === 'AND' or $andOr === 'OR') {
            $this->query .= "$andOr ";
            $this->query .= $arg . ' ' . $comparisonSign . ' ' . $comparingWith . ' ';
        }

        return $this;
    }

    public function groupBy(string $columnName): self
    {
        $this->query .= 'GROUP BY ';
        $this->query .= "$columnName ";

        return $this;
    }

    public function having(string $afterHaving, string $columnName, string $compareSign, int|string $compareWith): self
    {
        $this->query .= 'HAVING ';
        $this->query .= $afterHaving;
        $this->query .= "($columnName) ";
        $this->query .= "$compareSign ";
        $this->query .= "$compareWith ";

        return $this;
    }


    public function createNewRecord(string $tableName, array $records, array $namesColumns = []): self
    {
        $this->query .= 'INSERT INTO ';
        $this->query .= "$tableName ";
        $this->iterateColumnsNames($namesColumns);
        $this->query .= 'VALUES ';
        $this->iterateInsertsValues($records);
        return $this;
    }


    public function updateRecords(string $tableName, string $key, string $sign, string $value, array $where = []): self
    {
        $updateArr = ['UPDATE',$tableName,'SET',$key,$sign,$value];

        foreach ($updateArr as $word) {
            $this->query .= "$word ";
        }

        if (!empty($where)) {
            $this->where($where['key'], $where['comparisonSign'], $where['value']);
        }

        return $this;
    }


    public function deleteRecord(string $tableName, array $where): self
    {
        $this->query .= 'DELETE FROM ';
        $this->query .= "$tableName ";
        $this->where($where['key'], $where['comparisonSign'], $where['value']);

        return $this;
    }


    public function getQuery(): string
    {
        return $this->query;
    }

    public function execute()
    {
        $this->getPdo()->query($this->query)->execute();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    protected function iterateColumnsNames($columns)
    {
        if (count($columns) !== 0) {
            $this->query .= '(' ;
            for ($i = 0; $i < count($columns); $i++) {
                if ($i === count($columns) - 1) {
                    $this->query .= "$columns[$i]) " ;
                } else {
                    $this->query .= "$columns[$i], ";
                }
            }
        }
    }

    protected function iterateInsertsValues($values)
    {
        if (count($values) !== 0) {
            $this->query .= '(' ;
            for ($i = 0; $i < count($values); $i++) {
                if ($i === count($values) - 1) {
                    $this->query .= "'$values[$i]') " ;
                } else {
                    $this->query .= "'$values[$i]', ";
                }
            }
        }
    }


}

