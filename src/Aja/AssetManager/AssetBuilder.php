<?php namespace Aja\AssetManager;

use Asset as AssetFacade;
use Assetic\Asset\AssetCollection;

class AssetBuilder
{
    protected $collections = array();
    protected $name;
    protected $path;
    protected $factory;
    
    public function __construct($name, $path, $factory)
    {
        $this->name = $name;
        $this->path = $path;
        $this->factory = $factory;
    }
    
    public function addLess($files)
    {
        $this->addFiles('css', $files, 'less');
    }
    
    public function addCss($files)
    {
        $this->addFiles('css', $files);
    }
    
    public function addJs($files)
    {
        $this->addFiles('js', $files);
    }
    
    public function addFiles($type, $files, $filter = NULL)
    {
        if (is_null($filter))
        {
            $filter = $type;
        }
        $filter = AssetFacade::GetFilter($filter);
        
        if (!isset($this->collections[$type]))
        {
            $col = new AssetCollection();
            $col->setTargetPath($this->path.'/'.$this->name.'.'.$type);
            $this->collections[$type] = $col;
        }
        
        $collection = $this->factory->createAsset(
            $files,
            $filter
        );
        
        $this->collections[$type]->add($collection);
    }
    
    public function getCollection($type)
    {
        if (!isset($this->collections[$type]))
        {
            return FALSE;
        }
        
        // We only want a specific subset.
        return $this->collections[$type];
    }
    
    public function getCollections()
    {
        // Return everything.
        return $this->collections;
    }
    
}
