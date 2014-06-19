<?php

return array(
    
    'collections' => array(
        
        'main' => array(
        	'css' => 'example.css',
            'js' => array(
                'example.js',
                'example2.js'
            )
        )
        
    ),
    
    'tags' => array(
        'css' => '<link rel="stylesheet" type="text/css" href="%s">',
        'js' => '<script src="%s"></script>'
    ),
    
    'filters' => array(
        'less' => array(
            'less',
            'cssrewrite',
            '?cssmin',
        ),
        'css' => array(
            'cssrewrite',
            '?cssmin',
        ),
        'js' => array(
            '?jscompile',
        ),
    )
    
);
