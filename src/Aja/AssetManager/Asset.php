<?php namespace Aja\AssetManager;

use Config;

class Asset
{
    /**
     * Array of enabled assets.
     * @var array
     */
    protected $enabledAssets = array();
    
    protected $filters;
    
    public function __construct()
    {
        $this->filters = Config::get('asset-manager::asset.filters');
        $this->tags = Config::get('asset-manager::asset.tags');
    }
    
    public function EnableAssets($args = NULL)
    {
        if (!is_array($args))
        {
            $args = func_get_args();
        }
        
        $this->enabledAssets = array_unique(array_merge($this->enabledAssets, $args));
    }
    
    public function ResetAssets($args = NULL)
    {
        $this->enabledAssets = array();
        
        // Then enable any passed.
        $this->EnableAssets($args);
    }
    
    public function Stylesheets()
    {
        return $this->serve('css');
    }
    
    public function Javascripts()
    {
        return $this->serve('js');
    }
    
    public function GetFilter($type)
    {
        return $this->filters[$type];
    }
    
    protected function serve($type)
    {
        $compiler = new AssetCompiler();
        $files = $compiler->getFilesFor($type, $this->enabledAssets);
        
        $assets = array();
        
        foreach($files as $file)
        {
            $assets[] = sprintf($this->tags[$type], $file);
        }
        
        return implode(PHP_EOL, $assets);
    }
    
    public function build($collection = NULL, $overwrite = FALSE)
    {
        $compiler = new AssetCompiler();
        return $compiler->build($collection, $overwrite);
    }
}

