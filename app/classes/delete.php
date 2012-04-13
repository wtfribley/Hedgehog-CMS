<?php
/**
 * Hedgehog's Daring Delete Class
 *
 * @author wtfribley
 */
class Delete extends DB {
    
    /**
     * $type -  what to delete (posts, users, etc.)
     * @var string 
     */
    private static $type;
    
    /**
     * $deleteBy -  array of length 1, in the form ['field']=value.
     *              Used to find the entry to be deleted.
     * @var array 
     */
    private static $deleteBy = array();
    
    public static function D($type = null, $deleteBy = array())
    {
        if (!is_null($type))
        {
            self::$type = $type;
            self::$deleteBy = $deleteBy;
            
            return self::deletor();
        }
        else
        {
            throw new Exception('I do not know what type to delete.');
        }
    }
      
    /**
     * Delete->deletor()
     * 
     * Calls DB::DELETE to prepare a statement,
     * executes the statement, returns true on success.
     * 
     * @return boolean 
     */
    private static function deletor()
    {
        // Get the Statement
        $stmt = self::DELETE(self::$type, self::$deleteBy);
        
        // Execute the Statement
        $result = $stmt->execute();
        
        return $result;
    }
}