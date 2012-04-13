<?php

/**
 * Hedgehog's Dastardly DB class
 *
 * @author wtfribley
 * 
 * @todo troubleshoot / debug the whole binds thing. I've rewritten using
 * simple string concatenation, but binds might be more flexible... if they worked.
 */
class DB {
        
    /**
    * This holds the PDO object.
    * @var PDO 
    */
    private static $dbh = null;
    
    private static $host, $user, $pass, $name;

    public static function SetDBDetails($db)
    {
        self::$host = $db['host'];
        self::$user = $db['user'];
        self::$pass = $db['pass'];
        self::$name = $db['name'];        
    }

    protected static function connect()
    {
        try
        {
            $dsn = 'mysql:host='.  self::$host .';dbname=' . self::$name;
            self::$dbh = new PDO($dsn, self::$user, self::$pass);
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
        }
    }
    
    protected static function close()
    {
        self::$dbh = null;
    }
    
    protected static function SELECT($table, $searchBy, $returnFields, $orderBy, $sortBy, $onlyPublished)
    {
        // Initialize connection if needed...
        if(is_null(self::$dbh))
            self::connect ();
        
        // BUILD THE MySQL STATEMENT...       
        $sql = "SELECT ";
        
        // Only return the fields we're interested in...
        for ($i=0;$i<count($returnFields);$i++)
        {
            $sql.= $table . '.' . $returnFields[$i];
            
            if ($i < count($returnFields) - 1)
                $sql.= ", ";
        }
        
        // From...
        $sql.= " FROM " . $table;
        
        // Are we searching for something?
        if (!empty($searchBy))
        {
            // What field are we using to search?
            $searchField = key($searchBy);
            
            // What are we trying to match? (i.e. the needle)
            $searchTerm = current($searchBy);
            
            // Get an array of properties (column names) for this type of entry/object
            $properties = array();
            foreach (self::$dbh->query('SHOW COLUMNS FROM ' . $table) as $row)
            {
                $properties[] = $row['Field'];
            }           

            // If the field we're using to search is not a property, we'll have to do a join.
            $whereTable = ''; // when using joins, we need to know the table for the WHERE clause.
            $refTable = 'none';
            if (!in_array($searchField, $properties))
            {
                // Identify the Reference Table...
                if ($table == 'posts' || $table == 'projects')
                    $refTable = $table . '_' . $searchField;
                else
                    $refTable = $searchField . '_' . $table;
                                
                // First join
                $sql.= " LEFT JOIN " . $refTable . " ON " . $table . ".id = " . $refTable . "." . $table . "id";
                $whereTable = $refTable . ".";              
                
                // Second join?
                if (!is_numeric($searchTerm))
                {
                    $sql.= " LEFT JOIN " . $searchField . " ON " . $refTable . "." . $searchField . "id = " . $searchField . ".id";
                    $whereTable = $searchField . ".";
                }            
            }
            
            // WHERE... 
            // (surround non-numeric terms with ')
            (is_numeric($searchTerm)) ? $searchTerm = $searchTerm : $searchTerm = "'" . $searchTerm . "'";
            
            // if the WHERE is operating on a reference table, we have to modify the searchField to match...
            (strrpos($whereTable, $refTable) === false) ? $searchField = $searchField : $searchField = $searchField . 'id';
                        
            $sql.= " WHERE " . $whereTable . $searchField . " = " . $searchTerm;
            
            // Only Show Published Posts?
            ($onlyPublished === true && $table == 'posts') ? $sql.= " AND status = 'published'" : $sql = $sql;
        }
        elseif ($onlyPublished === true && $table == 'posts')
        {
            $sql.= " WHERE status = 'published'";
        }
        
        // Order by?
        if (!is_null($orderBy))
        {
            $sql.= " ORDER BY " . $orderBy;
            // Sort by
            $sql.= " " . $sortBy . ";";
        }
        
        // Prepare the Statement
        return self::$dbh->prepare($sql);
    }
    
    protected static function INSERT($table, $fields)
    {
        if(is_null(self::$dbh))
            self::connect ();
               
        // Build the Statement
        $sql = "INSERT INTO " . $table . ' (';
        
        // List the Fields
        for ($i=0;$i<count($fields);$i++)
        {
            $sql.= $fields[$i];
            if ($i < count($fields) - 1)
                $sql.= ", ";
        }
        
        $sql.= ") VALUES (";
        
        // List the placeholders in form :fields - to be used later by execute()
        for ($i=0;$i<count($fields);$i++)
        {
            $sql.= ":" . $fields[$i];
            if ($i < count($fields) - 1)
                $sql.= ", ";
        }
        
        $sql.= ")";
                
        return self::$dbh->prepare($sql);
    }
    
    protected static function UPDATE($table, $fields, $updateBy)
    {
        if(is_null(self::$dbh))
            self::connect ();
              
        // Build the Statement
        $sql = "UPDATE " . $table . ' SET ';
        
        // List the fields and their PLACEHOLDER values (:field)...
        for ($i=0;$i<count($fields);$i++)
        {
            $sql.= $fields[$i] . " = :" . $fields[$i];
            if ($i < count($fields) - 1)
                $sql.= ", ";
        }
        
        // WHERE... using $updateBy       
        $sql.= self::WHERE($updateBy); 
                
        return self::$dbh->prepare($sql);
    }
    
    protected static function DELETE($table, $deleteBy)
    {
        if(is_null(self::$dbh))
            self::connect ();
              
        // Build the Statement
        $sql = "DELETE FROM " . $table;
        
        // WHERE... using $deleteBy
        if (!empty($deleteBy))
            $sql.= self::WHERE($deleteBy);
        
        return self::$dbh->prepare($sql);
    }
    
    private static function WHERE($searchBy)
    {
        // (surround non-numeric terms with ')
        $searchTerm = current($searchBy);
        (is_numeric($searchTerm)) ? $searchTerm = $searchTerm : $searchTerm = "'" . $searchTerm . "'";

        $sql = " WHERE " . key($searchBy) . " = " . $searchTerm;
        
        return $sql;
    }
}