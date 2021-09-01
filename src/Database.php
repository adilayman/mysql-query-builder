<?php

// namespace rFx\core\models;

// use \PDO;
// use \Exception;
// use \PDOException;

class Database extends PDO
{
    private const NO_MODE = 0;
    private const EXTRACT_MODE = 1;
    private const INSERT_MODE = 2;

    private $query = '';
    private $values = [];
    private $mode = self::NO_MODE;

    public function __construct($dbname, $host, $username, $password)
    {
        try {
            $dsn = "mysql:dbname=$dbname;host=$host";
            parent::__construct($dsn, $username, $password);
            parent::setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            parent::setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {
            throw new Exception('Connection failed: ' . $e->getMessage());
        }
    }

    public function select($columns, $table)
    {
        if ($this->mode === self::NO_MODE)
            $this->mode = self::EXTRACT_MODE;

        $this->query .= 'SELECT ';

        if (is_array($columns))
            $this->query .= implode(',', $columns);
        else
            $this->query .= $columns;

        $this->query .= " FROM $table";

        return $this;
    }

    public function where($conditions, $logic = 'AND')
    {
        if ($this->mode === self::NO_MODE)
            throw new Exception("WHERE clause cannot be at the beginning of the query.");

        $this->query .= ' WHERE';

        $condSize = count($conditions);

        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $exploded = explode(' ', $key, 3);
                $length = count($exploded);

                if (!strcmp($exploded[$length - 1], 'BETWEEN')) {
                    $this->query .= " ($key ? AND ?)";
                    $this->values[] = $value[0];
                    $this->values[] = $value[1];
                } elseif (!strcmp($exploded[$length - 1], 'IN')) {
                    $this->query .= " $key ($this->replace($value))";
                } else {
                    $this->query .= ' ' . $key;

                    foreach ($value as $k => $v) {
                        $this->values[] = $v;
                    }
                }

                if (--$condSize > 0) {
                    $this->query .= ' ' . $logic;
                }
            } else {
                $this->query .= " $key";

                if ($value === NULL) {
                    $this->query .= " NULL";
                } else {
                    $this->query .= " ?";

                    if (--$condSize > 0) {
                        $this->query .= ' ' . $logic;
                    }

                    $this->values[] = $value;
                }
            }
        }

        return $this;
    }

    public function groupBy($columns)
    {
        if ($this->mode === self::NO_MODE)
            throw new Exception("GROUP BY clause cannot be at the beginning of the query.");

        $this->query .= ' GROUP BY ';

        if (is_array($columns))
            $this->query .= implode(',', $columns);
        else
            $this->query .= $columns;

        return $this;
    }

    public function orderBy($columns)
    {
        if ($this->mode === self::NO_MODE)
            throw new Exception("ORDER BY clause cannot be at the beginning of the query.");

        $this->query .= ' ORDER BY ';

        if (is_array($columns))
            $this->query .= implode(',', $columns);
        else
            $this->query .= $columns;

        return $this;
    }

    public function update($table, $columns)
    {
        if ($this->mode === self::NO_MODE)
            $this->mode = self::INSERT_MODE;

        $this->query .= " UPDATE $table SET";
        $this->separate($columns);

        return $this;
    }

    public function unionSelect($columns, $table)
    {
        if ($this->mode === self::NO_MODE)
            throw new Exception("UNION clause cannot be at the beginning of the query.");

        $this->query .= ' UNION ';

        return $this->select($columns, $table);
    }

    public function unionAllSelect($columns, $table)
    {
        if ($this->mode === self::NO_MODE)
            throw new Exception("UNION ALL clause cannot be at the beginning of the query.");

        $this->query .= ' UNION ALL ';

        return $this->select($columns, $table);
    }

    public function insertInto($table, $arg1, $arg2 = NULL)
    {
        if ($this->mode === self::NO_MODE)
            $this->mode = self::INSERT_MODE;

        $this->query .= "INSERT INTO $table";

        if ($arg2 !== NULL) {
            $this->query .= '(' . implode(',', $arg1) . ')';
            $this->query .= ' VALUES ';
            $this->replace($arg2);
        } else {
            $this->query .= ' VALUES ';
            $this->replace($arg1);
        }

        return $this;
    }

    public function delete($table)
    {
        if ($this->mode !== self::NO_MODE)
            throw new Exception("The initial query has to be empty");

        $this->mode = self::INSERT_MODE;
        $this->query .= "DELETE FROM $table";

        return $this;
    }

    public function truncate($table)
    {
        if ($this->mode !== self::NO_MODE)
            throw new Exception("The initial query has to be empty");

        $this->mode = self::INSERT_MODE;
        $this->query .= "TRUNCATE $table";

        return $this;
    }

    public function limit($arg1, $arg2 = NULL)
    {
        $this->query .= "LIMIT $arg1";

        if ($arg2 !== NULL)
            $this->query .= ", $arg2";
    }

    public function result($fetchMode = 'fetch')
    {
        $result = NULL;

        if ($this->mode === self::EXTRACT_MODE)
            $result = $this->extraction(
                $this->query,
                $this->values,
                $fetchMode
            );
        elseif ($this->mode === self::INSERT_MODE)
            $result = $this->insertion($this->query, $this->values);

        $this->reset();

        return $result;
    }

    public function insertion($query, array $params = NULL)
    {
        return $this->prepare($query)->execute($params);
    }

    public function extraction($query, $params = NULL, $fetchMode = 'fetchAll')
    {
        $stmt = $this->prepare($query);

        $stmt->execute($params);

        if (!method_exists($stmt, $fetchMode))
            throw new Exception("Undefined method PDOStatement::$fetchMode()");

        return $stmt->$fetchMode();
    }

    public function reset()
    {
        $this->query = '';
        $this->values = [];
        $this->mode = self::NO_MODE;
    }

    private function replace($array)
    {
        $this->query .= '(';
        $count = count($array);

        for ($i = 0; $i < $count; ++$i) {
            $this->query .= '?';

            if ($i < $count - 1)
                $this->query .= ',';

            $this->values[] .= $array[$i];
        }
        $this->query .= ')';
    }

    private function separate($array, $separator = 'AND')
    {
        $count = count($array);

        foreach ($array as $key => $value) {
            $this->query .= " $key = ?";

            if (--$count > 0) {
                $this->query .= ' ' . $separator;
            }

            $this->values[] = $value;
        }
    }
}
