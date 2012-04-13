<?php
/**
 * Hedgehog's Copacetic Create Class
 *
 * @author wtfribley
 */
class Create extends DB {
    
    /**
     * $type -  what to create (posts, users, etc.)
     * @var string 
     */
    private static $type;
    
    /**
     * $data -  an array in the form ['field']=value to be stored
     *          in the database.
     * 
     * @var array 
     */
    private static $data = array();   
    
    public static function C($type = null, $data = array())
    {
        if (!is_null($type))
        {
            self::$data = $data;
            self::$type = $type;
            
            return self::creator();
        }
        else
        {
            throw new Exception('I do not know what to create.');
        }
    }
    
    /**
     * Create->creator()
     * 
     * Calls DB::INSERT to prepare a statement,
     * executes the statement, returns true on success.
     * 
     * @return boolean 
     */
    private static function creator()
    {
        // Get the Fields
        $fields = array_keys(self::$data);

        // Get the Statement
        $stmt = self::INSERT(self::$type, $fields);
        
        // Execute the Statement
        $result = $stmt->execute(self::$data);
        
        return $result;        
    }
}