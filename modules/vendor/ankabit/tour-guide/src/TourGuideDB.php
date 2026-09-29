<?php

namespace TourGuide;

class TourGuideDB
{
    private $connection;
    private $tablePrefix;
    private $charset;    // Character set for the database connection
    private $collation;  // Collation for the database connection


    /**
     * SimpleDB constructor.
     * Establishes a database connection using mysqli with support for port, charset, and collation.
     * 
     * @param string $host        Database host
     * @param string $username    Database username
     * @param string $password    Database password
     * @param string $dbname      Database name
     * @param string $tablePrefix Optional table prefix
     * @param int    $port        Database port (default: 3306)
     * @param string $charset     Charset (default: utf8mb4)
     * @param string $collation   Collation (default: utf8mb4_general_ci)
     * 
     * @throws Exception If connection to the database fails
     */
    public function __construct($host, $username, $password, $dbname, $tablePrefix = '', $port = 3306, $charset = 'utf8mb4', $collation = 'utf8mb4_general_ci')
    {
        $this->charset = $charset;
        $this->collation = $collation;

        $this->connection = new \mysqli($host, $username, $password, $dbname, $port);
        $this->tablePrefix = $tablePrefix;

        // Check for connection error
        if ($this->connection->connect_error) {
            throw new \Exception('TourGuide:Database connection failed: ' . $this->connection->connect_error);
        }

        // Set charset and collation
        $this->setCharsetAndCollation($charset, $collation);
    }

    /**
     * Sets the charset and collation for the database connection.
     * 
     * @param string $charset   Charset to set (e.g. utf8mb4)
     * @param string $collation Collation to set (e.g. utf8mb4_general_ci)
     * 
     * @throws Exception If charset or collation cannot be set
     */
    private function setCharsetAndCollation($charset, $collation)
    {
        if (!$this->connection->set_charset($charset)) {
            throw new \Exception('TourGuide:Error setting charset: ' . $this->connection->error);
        }

        // Collation is set using query because mysqli doesn't have a direct method for it
        if (!$this->connection->query("SET collation_connection = '$collation'")) {
            throw new \Exception('TourGuide:Error setting collation: ' . $this->connection->error);
        }
    }

    /**
     * Retrieves the character set used by the database connection.
     *
     * @return string The current character set.
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Retrieves the collation used by the database connection.
     *
     * @return string The current collation.
     */
    public function getCollation()
    {
        return $this->collation;
    }

    /**
     * Retrieves the database prefix used for table names.
     *
     * @return string The current database prefix.
     */
    public function getPrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Executes a raw SQL query against the database.
     *
     * @param string $query The SQL query to execute.
     * @return mixed The result set returned by the query, or throws an exception on failure.
     * @throws Exception if the query execution fails.
     */
    public function runRawQuery($query)
    {
        if (!$result = $this->connection->query($query)) {
            throw new \Exception("Query failed: " . $this->connection->error);
        }
        return $result;
    }


    /**
     * Inserts a new record into the database.
     * 
     * @param string $table Table name
     * @param array  $data  Associative array of data to insert (column => value)
     * 
     * @return int|bool Inserted record ID or false on failure
     * 
     * @throws Exception If query preparation or execution fails
     */
    public function create($table, $data)
    {
        $table = $this->tablePrefix . $table;
        $fields = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($sql);

        if ($stmt === false) {
            throw new \Exception('TourGuide:Error preparing statement: ' . $this->connection->error);
        }

        $this->bindParams($stmt, $data);

        if (!$stmt->execute()) {
            throw new \Exception('TourGuide:Error executing statement: ' . $stmt->error);
        }

        return $stmt->insert_id ? $stmt->insert_id : false;
    }

    /**
     * Retrieves records from the database.
     * 
     * @param string $table      Table name
     * @param array  $conditions Associative array of conditions (column => value)
     * @param string $fields     Comma-separated string of fields to retrieve, default is '*'
     * @param string $order      Order clause i.e priority DESC
     * 
     * @return array Fetched records as an associative array
     * 
     * @throws Exception If query preparation or execution fails
     */
    public function read($table, $conditions = [], $fields = '*', $order = '')
    {
        $table = $this->tablePrefix . $table;
        $sql = "SELECT $fields FROM $table";

        if (!empty($conditions)) {
            $where = implode(' AND ', array_map(function ($key) {
                return "$key = ?";
            }, array_keys($conditions)));
            $sql .= " WHERE $where";
        }

        if (!empty($order)) {
            $sql .= " ORDER BY $order";
        }

        $stmt = $this->connection->prepare($sql);

        if ($stmt === false) {
            throw new \Exception('TourGuide:Error preparing statement: ' . $this->connection->error);
        }

        if (!empty($conditions)) {
            $this->bindParams($stmt, $conditions);
        }

        if (!$stmt->execute()) {
            throw new \Exception('TourGuide:Error executing statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Updates records in the database.
     * 
     * @param string $table      Table name
     * @param array  $data       Associative array of data to update (column => value)
     * @param array  $conditions Associative array of conditions (column => value)
     * 
     * @return int Number of affected rows
     * 
     * @throws Exception If query preparation or execution fails
     */
    public function update($table, $data, $conditions)
    {
        $table = $this->tablePrefix . $table;

        $setFields = implode(',', array_map(function ($key) {
            return "$key = ?";
        }, array_keys($data)));

        $where = implode(' AND ', array_map(function ($key) {
            return "$key = ?";
        }, array_keys($conditions)));

        $sql = "UPDATE $table SET $setFields WHERE $where";
        $stmt = $this->connection->prepare($sql);

        if ($stmt === false) {
            throw new \Exception('TourGuide:Error preparing statement: ' . $this->connection->error);
        }

        $params = $data;
        foreach ($conditions as $key => $value) {
            $params['cond_' . $key] = $value;
        }
        $this->bindParams($stmt, $params);

        if (!$stmt->execute()) {
            throw new \Exception('TourGuide:Error executing statement: ' . $stmt->error);
        }

        return $stmt->affected_rows;
    }

    /**
     * Deletes records from the database.
     * 
     * @param string $table      Table name
     * @param array  $conditions Associative array of conditions (column => value)
     * 
     * @return int Number of affected rows
     * 
     * @throws Exception If query preparation or execution fails
     */
    public function delete($table, $conditions)
    {
        $table = $this->tablePrefix . $table;

        $where = implode(' AND ', array_map(function ($key) {
            return "$key = ?";
        }, array_keys($conditions)));

        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->connection->prepare($sql);

        if ($stmt === false) {
            throw new \Exception('TourGuide:Error preparing statement: ' . $this->connection->error);
        }

        $this->bindParams($stmt, $conditions);

        if (!$stmt->execute()) {
            throw new \Exception('TourGuide:Error executing statement: ' . $stmt->error);
        }

        return $stmt->affected_rows;
    }

    /**
     * Binds parameters to the prepared statement dynamically.
     * 
     * @param mysqli_stmt $stmt  The prepared statement
     * @param array       $params Associative array of data to bind
     */
    private function bindParams($stmt, $params)
    {
        $types = '';
        $values = [];

        foreach ($params as $value) {
            if (is_int($value)) {
                $types .= 'i'; // Integer
            } elseif (is_float($value)) {
                $types .= 'd'; // Double
            } elseif (is_string($value)) {
                $types .= 's'; // String
            } else {
                $types .= 'b'; // Blob or other types
            }

            $values[] = $value;
        }

        $stmt->bind_param($types, ...$values);
    }

    /**
     * Closes the database connection.
     */
    public function close()
    {
        $this->connection->close();
    }
}
