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

class AssetCompiler
{
    protected $collections;
    protected $buildpath;
    protected $assetFactory;

    public function __construct()
    {
        $this->collections = Config::get('asset-manager::asset.collections');
        $this->buildpath = 'builds';
        
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

        $factory = new AssetFactory(public_path());
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
            $builder = new AssetBuilder(
                $key,
                $this->buildpath,
                $this->assetFactory
            );
            
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
                    $this->write($collection);
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
        $build = trim($this->buildpath, '/').'/';

        $results = array();
        foreach($args as $key)
        {
            $filename = $build.$key.'.'.$type;
            $results[] = asset($build.$key.'.'.$type).'?'.md5_file($filename);
        }
        return $results;
    }
    
    public function getFilesForDevelopment($type, $args)
    {
        $result = array();

        // For writing (for less stylesheets.)
        $writer = new AssetWriter(public_path());

        foreach($args as $key)
        {
            $builder = new AssetBuilder(
                $key,
                $this->buildpath,
                $this->assetFactory
            );
            
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
                        $writer->writeAsset($asset);
                    }
                    
                    $filename = $asset->getTargetPath();
                    $result[] = asset($filename).'?'.md5_file($filename);
                }
            }
        }
        
        return $result;
    }
    
    protected function is_stale(AssetInterface $asset)
    {
        $stale = TRUE;
        
        $target = public_path($asset->getTargetPath());
        
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
    
    protected function write($collection)
    {
        $writer = new AssetWriter(public_path());
        $writer->writeAsset($collection);
    }
}
