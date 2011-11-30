# Silex Extensions

Some small extensions for the [Silex framework](silex.sensiolabs.org).
Well... For the moment, there is only one Provider, described below - but more will come ! :-)

## LessProvider

This Provider creates a shared instance of ```DrBenton\Component\LessCompiler```, usable via ```$app['less']``` : use this compiler to generate CSS files from [LESS](http://lesscss.org/) files.

If the Twig Provider is used (i.e. we have a ```$app['twig']```), it also creates the ```compileLess($lessInputPath, $cssOutputPath)``` and ```less($lessInputFile)``` Twig functions.

Foe the moment, only the "official" LESS Node.js compiler is supported : you have to install Node.js prior to use this Provider, and install the LESS script - you can also just download the LESS script [here](http://lesscss.googlecode.com/files/less-1.1.3.min.js) and provide its path to the Provider via the ```less.node_less_module_path``` parameter (see below).

The LESS generation uses a small portion of the excellent PHP [Assetic](https://github.com/kriswallsmith/assetic) library. See its LICENSE [here](https://github.com/kriswallsmith/assetic/blob/master/LICENSE).


#### Parameters

- **less.node_path** (optional) : if not provided, the LessCompiler will use its default Node path, '/usr/bin/node'.
- **less.node_less_module_path** (optional) : if the ```less``` Node module is not in your Node modules path, you can provide the LESS script path here (otherwise, a simple ```var less = require('less')``` is used).
- **less.tmp_folder** (optional) : if you don't have access to the system temp folder (as returned by the PHP ```sys_get_temp_dir()``` function), you can provide an alternative temp folder (in this this folder we need to create a temporary Node.js script in order to compile the LESS file ; this script is deleted after use).
- **less.web_files_folder_path** (optional) : if your public assets are not in the same folder than your 'index.php' file, you can provide the public assets folder path here (it is automatically prepended to the LESS file path in the ```less``` Twig function).
- **less.compress** (optional) : set it to ```true``` to compress the CSS files after LESS conversion.
- **less.force_compilation** (optional) : set it to ```true``` to force the LESS-to-CSS compilation for each page load (the standard behaviour is to compile onl if the source LESS file is newer than the target CSS  file).
- **less.enabled** (optional) : set it to ```false``` to disable the LESS files compilation (use it for your production server, when you have uploaded the previously generated CSS files from the LESS rules).

#### Services

- **less** : The ```DrBenton\Component\LessCompiler``` instance.

Example usage:

```
$app['less']->compile('src/Resources/less/main.less', 'web/css/main.css');
```

#### Registering

```
// Less compiler init
$app->register(new DrBenton\Silex\Provider\LessServiceProvider(), array(
    'less.node_path'                => '/home/webmaster/local/node/bin/node',
    'less.node_less_module_path'    => '/home/webmaster/local/node/lib/node_modules/less',
    'less.web_files_folder_path'    =>  __DIR__.'/web',
    'less.tmp_folder'               =>  __DIR__.'/app/cache',
    'less.compress'                 =>  !$app['debug'],
    'less.force_compilation'        =>  $app['debug'],
    'less.enabled'        					=>  true,
) );
```

#### Twig extension

If you registered the [Twig Silex Provider](http://silex.sensiolabs.org/doc/providers/twig.html) into your ```$app``` before registering this Provider, the ```compileLess($lessInputPath, $cssOutputPath)``` and ```less($lessInputFile)``` functions will be added to the Silex environment :

 - ```compileLess($lessInputPath, $cssOutputPath)``` : allows a Twig view to request a LESS file compilation before calling the matching CSS.
This is useful if your LESS file are not in a public folder (for example, if your LESS files are in 'src/Resources/less/' whereas your public files (JS, CSS and images) are in 'web/'.
 - ```less($lessInputFile)``` : give this function a LESS file path, and it will compile it in a CSS file (in the same folder, with the same name, with a '.css' extension instead of '.less'), and return the CSS file path, ready to be used in your Twig template "as-is". Use this function when your LESS files are in a public folder.
Example usage : ```<link rel="stylesheet" type="text/css" href="{{ less('/css/main.less') }}" />```.


#### TODO

Allows [lessphp](https://github.com/leafo/lessphp) use when its 3.0 version is released (this one will be compatible with the Javascript LESS implementation).

