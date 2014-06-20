<?php namespace Aja\AssetManager;

use Assetic\AssetWriter;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\FileAsset;
use Assetic\FilterManager;

use Assetic\Filter\LessphpFilter;
use Assetic\Filter\GoogleClosure\CompilerApiFilter;
use Assetic\Filter\GoogleClosure\CompilerJarFilter;
use Assetic\Filter\UglifyCssFilter;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Factory\AssetFactory;

use Config;
use Illuminate\Filesystem\Filesystem;

class AssetCompiler
{
    protected $collections;
    protected $assetFactory;
    protected $writer;
    protected $builders = array();
    
    public function __construct()
    {
        $this->collections = Config::get('asset-manager::asset.collections');
        
        $this->configureAssetTools();
    }
    
    protected function configureAssetTools()
    {
        $fm = new FilterManager();
        
        $fm->set('less', new LessphpFilter());
        $fm->set('cssrewrite', new AssetCssUriRewriteFilter());
        $fm->set('cssmin', new CssMinFilter());
        
        $fm->set('jscompile', new CompilerApiFilter());
        //$fm->set('jscompilejar', new CompilerJarFilter(storage_path('jar/compiler.jar')));
        
        $factory = new AssetFactory(Config::get('asset-manager::asset.paths.asset_path'));
        $factory->setFilterManager($fm);
        $factory->setDebug(Config::get('app.debug'));
        
        $this->assetFactory = $factory;
    }
    
    public function build($collection = NULL, $overwrite = FALSE)
    {
        $builders = array();
        
        if (!is_null($collection))
        {
            // Pull just the one item.
            $this->collections = array($collection => $this->collections[$collection]);
        }
        
        foreach($this->collections as $key => $config)
        {
            $builder = $this->getBuilder($key);
            
            foreach(array('css', 'js', 'less') as $sub)
            {
                if (!empty($config[$sub]))
                {
                    $builder->addFiles($sub, $config[$sub], ($sub == 'less' ? 'less' : null));
                }
            }
            
            $builders[] = $builder;
        }
        
        $production = FALSE;
        foreach($builders as $builder)
        {
            foreach($builder->getCollections() as $collection)
            {
                if ($overwrite || (!$production && $this->is_stale($collection)))
                {
                    $this->getWriter()->writeAsset($collection);
                    echo 'Wrote file. ['.$collection->getTargetPath().']'.PHP_EOL;
                }
                else
                {
                    echo 'Build not required. ['.$collection->getTargetPath().']'.PHP_EOL;
                }
            }
        }
    }
    
    public function getFilesFor($type, $args)
    {
        // FIX ME
        if (count($args) == 0)
        {
            $args = array_keys($this->collections);
        }
        
        if (Config::get('app.debug'))
        {
            $files = $this->getFilesForDevelopment($type, $args);
        }
        else
        {
            $files = $this->getFilesForProduction($type, $args);
        }
        
        return $files;
    }
    
    public function getFilesForProduction($type, $args)
    {
        // Build Subfolder
        $build_subfolder = trim(Config::get('asset-manager::asset.paths.build_subfolder'), '/').'/';
        
        $results = array();
        foreach($args as $key)
        {
            $results[] = $this->generateAssetUri(
                $build_subfolder .
                $key.'.'.$type
            );
        }
        return $results;
    }
    
    public function getFilesForDevelopment($type, $args)
    {
        $results = array();
        
        foreach($args as $key)
        {
            $builder = $this->getBuilder($key);
            
            $config = $this->collections[$key];
            foreach(array('css', 'js', 'less') as $sub)
            {
                if (!empty($config[$sub]))
                {
                    $builder->addFiles($sub, $config[$sub], ($sub == 'less' ? 'less' : null));
                }
            }
            
            if (FALSE !== ($collection = $builder->getCollection($type)))
            {
                foreach($collection as $asset)
                {
                    if ($this->is_stale($asset))
                    {
                        $this->getWriter()->writeAsset($asset);
                    }
                    $results[] = $this->generateAssetUri($asset->getTargetPath());
                }
            }
        }
        
        return $results;
    }
    
    protected function generateAssetUri($target)
    {
        $public = Config::get('asset-manager::asset.paths.public_path');
        $public = rtrim($public, '/').'/';
        
        return asset($target).'?'.md5_file($public.$target);
    }
    
    protected function is_stale(AssetInterface $asset)
    {
        $stale = TRUE;
        
        $target = $asset->getTargetPath();
        
        if (file_exists($target))
        {
            $last = filemtime($target);
            $mod = $asset->getLastModified();
            if ($mod && $last >= $mod)
            {
                $stale = FALSE;
            }
        }
        
        return $stale;
    }
    
    protected function getBuilder($key)
    {
        if (!isset($this->builders[$key]))
        {
            $this->builders[$key] = new AssetBuilder(
                $key,
                Config::get('asset-manager::asset.paths.build_subfolder'),
                $this->assetFactory
            );
        }
        
        return $this->builders[$key];
    }
    
    protected function getWriter()
    {
        if (is_null($this->writer))
        {
            $this->writer = new AssetWriter(Config::get('asset-manager::asset.paths.public_path'));
        }
    
        return $this->writer;
    }
    
}
