<?php
/**
 * Hedgehog's Rad Read Class
 *
 * @author wtfribley
 */
class Read extends DB {
    
    /**
     * $type -  what to read (posts, users, etc.)
     * @var string 
     */
    protected static $type;
    
    /**
     * $searchBy -  array of length 1, in the form ['field']=value.
     * @var array 
     */
    protected static $searchBy = array();
    
    /**
     * $returnFields -  array in the form ('field1','field2','fieldn')
     *                  OR simply ('*') to return all fields.
     * @var array 
     */
    protected static $returnFields = array();
    
    protected static $orderBy, $sortBy, $onlyPublished;
    
    /**
     * Read($params) accepts an associative array with these keys and default values:
     * 
     * type = null, searchBy = array(), returnFields = array('*'), orderBy = null,
     * sortBy = 'DESC', onlyPublished = true
     * 
     * @param assoc array $params
     * @throws Exception 
     */
    public static function R($params = array())
    {
        $defaults = array('type'=>null,'searchBy'=>array(),'returnFields'=>array('*'),'orderBy'=>null,'sortBy'=>'DESC','onlyPublished'=>true);
        $params = array_merge($defaults, $params);
        
        if (!is_null($params['type']))
        {            
            // Make it plural!
            if(preg_match('/s$/',$params['type']) == 0)
                $params['type'].= 's';
            $params['type'] = preg_replace('/ys$/', 'ies', $params['type']);
                    
            // Assignment
            self::$type = $params['type'];           
            self::$searchBy = $params['searchBy'];
            self::$returnFields = $params['returnFields'];
            self::$orderBy = $params['orderBy'];
            self::$sortBy = $params['sortBy'];
            self::$onlyPublished = $params['onlyPublished'];
            
            // Read it!
            return self::reader();
        }
        else
        {
            throw new Exception('I do not know what type to read.');
        }
        
    }
    
    private static function reader()
    {
        // Get the Statement
        $stmt = self::SELECT(self::$type, self::$searchBy, self::$returnFields, self::$orderBy, self::$sortBy, self::$onlyPublished);
        
        // Execute the Statement.           
        $stmt->execute();
        
        // What Class do we use?
        $class = self::$type;
        // Make it singular!
        $class = preg_replace('/ies$/','y',$class);
        $class = preg_replace('/s$/','',$class);
                
        $results = $stmt->fetchAll(PDO::FETCH_CLASS, $class);
        
        // Get the list of categories, tags, and the related project.
        if ($class == 'post' || $class == 'project')
        {
            $results = self::getCategories(self::$type, $results);
            
            /**
             * @todo:   Uncomment this once Tags have been fully implemented
             *          (i.e. the Tag class has been created) 
             */
            //$results = self::getTags($results);
        }
        if ($class == 'post')
            $results = self::getAfflProject($results);        
        
        return $results;
    }
    
    private static function getCategories($type, $results)
    {
        foreach ($results as $r)
        {
            $id = $r->the('id');
            if (!is_null($id))
            {
                $categories = self::R(array('type'=>'categories','searchBy'=>array($type=>$id)));           
                $r->set('categories',$categories);
            }            
        }
        
        return $results;
    }
    
    private static function getTags($results)
    {
        foreach ($results as $r)
        {
            $id = $r->the('id');
            $tags = self::R(array('type'=>'tags','searchBy'=>array('posts'=>$id)));
            $r->set('tags',$tags);
        }
        
        return $results;
    }
    
    private static function getAfflProject($results)
    {
        foreach ($results as $r)
        {
            $proj = $r->the('project');
            $proj = self::R(array('type'=>'project','searchBy'=>array('slug'=>$proj),'returnFields'=>array('slug','title')));
            $r->set('project',$proj);
        }
        
        return $results;
    }
}