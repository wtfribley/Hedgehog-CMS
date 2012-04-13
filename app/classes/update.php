<?php
/**
 * Hedgehog's Upstanding Update Class
 *
 * @author wtfribley
 */
class Update extends DB {
    
    /**
     * $type -  what to update (posts, users, etc.)
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
    
    /**
     * $updateBy -  array of length 1, in the form ['field']=value.
     *              Used to find the entry to be updated.
     * @var array 
     */
    private static $updateBy = array();
    
    public static function U($type = null, $data = array(), $updateBy = array())
    {
        if (!is_null($type) && !empty($updateBy))
        {
            self::$data = $data;
            self::$type = $type;
            self::$updateBy = $updateBy;
            
            return self::updater();
        }
        else
        {
            throw new Exception('I do not know what type to update AND/OR which entry to update.');
        }
    }
    
    /**
     * Update->updater()
     * 
     * Calls DB::UPDATE to prepare a statement,
     * executes the statement, returns true on success.
     * 
     * @return boolean 
     */
    private static function updater()
    {
        // Get the Fields
        $fields = array_keys(self::$data);

        // Get the Statement
        $stmt = self::UPDATE(self::$type, $fields, self::$updateBy);
        
        // Execute the Statement
        $result = $stmt->execute(self::$data);
        
        return $result;        
    }
    
}