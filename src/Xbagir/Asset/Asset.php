<?php namespace Xbagir\Asset;

class Asset
{
    protected $packages     = array();
    protected $usedPackages = array();
    protected $html;

    public function __construct($packages, $html)
    {
        $this->packages = $packages;
        $this->html     = $html;
    }

    public function package($package)
    {
        if ( ! $assets = array_get($this->packages, $package))
        {
            throw new \InvalidArgumentException("Package \"$package\" does not exist in config \"asset\"");
        }

        if ( ! isset($assets['styles']) AND ! isset($assets['scripts']))
        {
            throw new \InvalidArgumentException("The package \"$package\" does not contain styles and scripts");
        }
                
        if (in_array($package, $this->usedPackages, true))
        {
            return $this;
        }
            
        if ( ! empty($assets['depends']) and array_values($assets['depends']) == $assets['depends'])
        {
            foreach ($assets['depends'] as $depend)
            {
                $this->package($depend);
            }
        }
                
        if ( ! empty($assets['depends']['before']))
        {           
            foreach ($assets['depends']['before'] as $depend)
            {
                $this->package($depend);
            }
        }

        $this->usedPackages[$package] = $assets;

        if ( ! empty($assets['depends']['after']))
        {
            foreach ($assets['depends']['after'] as $depend)
            {
                $this->package($depend);
            }
        }
        
        return $this;
    }

    public function make()
    {
        $styles  = '';
        $scripts = '';
        
        foreach ($this->usedPackages as $package)
        {            
            if ( ! empty($package['styles']))
            {                
                foreach ($package['styles'] as $key => $value)
                {                
                    $styles .= is_numeric($key) ? $this->linkStyle($value) : $this->linkStyle($key, $value);    
                }
            }

            if ( ! empty($package['scripts']))
            {                
                foreach ($package['scripts'] as $key => $value)
                {
                    $scripts .= is_numeric($key) ? $this->linkScript($value) : $this->linkScript($key, $value);  
                }
            }
        }
        
        return $styles.$scripts;
    }

    protected function linkStyle($style, array $options = array())
    {       
        $style = $this->html->style($style, array_get($options, 'attributes', array()));
               
        if ( ! empty($options['condition']) )
        {
            $style = "<!--[if {$options['condition']}]>".PHP_EOL.$style.'<![endif]-->'.PHP_EOL;
        }
   
        return $style;
    }

    protected function linkScript($script, array $options = array())
    {
        return $this->html->script($script, array_get($options, 'attributes', array()));
    }

}