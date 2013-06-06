<?php

/**
 * Database management and access class
 * This is a very basic level of abstraction
 */
class Database extends PDO {

    private $connections      = array(); # Allows multiple database connections
    private $activeConnection = 0;       # Tells the DB object which connection to use. setActiveConnection($id) allows us to change this
    private $queryCache       = array(); # Queries which have been executed and then "saved for later"
    private $dataCache        = array(); # Data which has been prepared and then "saved for later"
    private $queryCounter     = 0;       # Number of queries made during execution process
    private $last;                       # Record of the last query

    public function __construct() {
    }

    /**
     * Create a new database connection
     * @param String database hostname
     * @param String database username
     * @param String database password
     * @param String database we are using
     * @return int the id of the new connection
     */
    public function newConnection( $host, $user, $password, $database ) {
        try {
            $this->connections[] = new PDO( "mysql:host=$host;dbname=$database", $user, $password );
            $connectionId = count( $this->connections ) - 1;
            $this->setActiveConnection( $connectionId );
            $this->connections[$this->activeConnection]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch(PDOException $e) {
            Registry::logIt( 'DATABASE: failed to connect to database ' . $database . ' >' . $e->getMessage() . '<' );
        }

        return $this->connections[$this->activeConnection];
    }

    /**
     * Close the active connection
     * @return void
     */
    public function closeConnection() {
        $this->connections[$this->activeConnection] = null;
    }

    /**
     * Change which database connection is actively used for the next operation
     * @param int the new connection id
     * @return void
     */
    public function setActiveConnection( $newConnectionId ) {
        if( is_int( $newConnectionId ) ) {
            $this->activeConnection = $newConnectionId;
        } else {
            Registry::logIt( "DATABASE: Invalid connection id >$newConnectionId<" );
        }
    }

    public function getActiveConnection() {
        return $this->connections[$this->activeConnection];
    }

    /**
     * Store a query in the query cache for processing later
     * @param String the query string
     * @return the pointed to the query in the cache
     */
    public function cacheQuery( $queryStr ) {
        try {
            $sth = $this->connections[$this->activeConnection]->prepare( $queryStr );
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $sth->execute();

            $result = array();
            while( $row = $sth->fetch() ) {
                array_push( $result, $row );
            }

            $this->queryCache[] = $result;
            return count($this->queryCache) - 1;
        }
        catch(PDOException $e) {
            Registry::logIt( 'DATABASE: Error executing and caching query >' . $e->getMessage() . '<' );
        }
    }

    /**
     * Get the number of rows from the cache
     * @param int the query cache pointer
     * @return int the number of rows
     */
    public function numRowsFromCache( $cache_id ) {
        return $this->queryCache[$cache_id]->num_rows;
    }

    /**
     * Get the rows from a cached query
     * @param int the query cache pointer
     * @return array the row
     */
    public function resultsFromCache( $cache_id ) {
        return $this->queryCache[$cache_id]->fetch_array(MYSQLI_ASSOC);
    }

    /**
     * Store some data in a cache for later
     * @param array the data
     * @return int the pointed to the array in the data cache
     */
    public function cacheData( $data ) {
        $this->dataCache[] = $data;
        return count( $this->dataCache ) - 1;
    }

    /**
     * Get data from the data cache
     * @param int data cache pointed
     * @return array the data
     */
    public function dataFromCache( $cache_id ) {
        return $this->dataCache[$cache_id];
    }

    /**
     * Delete records from the database
     * @param String the table to remove rows from
     * @param String the condition for which rows are to be removed
     * @param int the number of rows to be removed
     * @return void
     */
    public function deleteRecords( $table, $condition, $limit ) {
        $limit = ( $limit == '' ) ? '' : ' LIMIT ' . $limit;
        $delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
        $this->executeQuery( $delete );
    }

    /**
     * Update records in the database
     * @param String the table
     * @param array of changes field => value
     * @param String the condition
     * @return bool
     */
    public function updateRecords( $table, $changes, $condition = '' ) {
        $update = "UPDATE " . $table . " SET ";
        foreach( $changes as $field => $value ) {
            $update .= "`" . $field . "`='{$value}',";
        }

        // remove our trailing ,
        $update = substr($update, 0, -1);

        if( $condition != '' ) {
            $update .= "WHERE " . $condition;
        }

        $this->executeQuery( $update );
        return true;
    }

    /**
     * Insert records into the database
     * @param String the database table
     * @param array data to insert field => value
     * @return bool
     */
    public function insertRecords( $table, $data ) {
        // setup some variables for fields and values
        $fields  = "";
        $values = "";

        // populate them
        foreach ($data as $f => $v) {
            $fields  .= "`$f`,";
            $values .= ( is_numeric( $v ) && ( intval( $v ) == $v ) ) ? $v."," : "'$v',";
        }

        // remove trailing ,
        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);

        $insert = "INSERT INTO $table ({$fields}) VALUES({$values})";
        $this->executeQuery( $insert );
        return true;
    }

    /**
     * Execute a query string
     * @param String the query
     * @return void
     */
    public function executeQuery( $queryStr ) {
        if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) ) {
            trigger_error( 'Error executing query: ' . $this->connections[$this->activeConnection]->error, E_USER_ERROR );
        } else {
            $this->last = $result;
        }
    }

    /**
     * Get the rows from the most recently executed query, excluding cached queries
     * @return array 
     */
    public function getRows() {
        $result = array();

        while ( $row = $this->last->fetch_assoc() ) {
            array_push( $result, $row );
        }

        return $result;
    }

    /**
     * Gets the number of affected rows from the previous query
     * @return int the number of affected rows
     */
    public function affectedRows() {
        return $this->$this->connections[$this->activeConnection]->affected_rows;
    }

    /**
     * Sanitize data
     * @param String the data to be sanitized
     * @return String the sanitized data
     */
    public function sanitizeData( $data ) {
        return $this->connections[$this->activeConnection]->real_escape_string( $data );
    }

    /**
     * Deconstruct the object
     * close all of the database connections
     */
    public function __deconstruct() {
        foreach( $this->connections as $connection ) {
            $connection->closeConnection;
        }
    }
}
?>
