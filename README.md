# Phing-Sass
Phing Sass task that uses either gem installed sass or scssphp.

Based on https://github.com/phingofficial/phing/pull/151/ and https://github.com/phingofficial/phing/pull/206/

<sass style="compact" trace="yes" unixnewlines="yes" outputpath="${projectOne}/public/assets/css" failonerror="yes">
    <fileset refid="scssToCompile"/>
</sass>

SassTask
========

The `SassTask` converts SCSS or Sass files to CSS using either the
['sass' gem](http://sass-lang.com/documentation/file.SASS_REFERENCE.html#using_sass)
or the [scssphp package](http://leafo.github.io/scssphp/).

  ----------------------------------------------------------------------------------
  Name          Type            Description                      Default Required
  ------------- ------- ---------------------------------------- ------- -----------
  `check`       `Boolean`       Whether to just check the syntax False   No
                                of the input files.                      

  `compact`     `Boolean`       Set the style to compact.        False   No

  `compressed`  `Boolean`       Set the style to compressed.     False   No

  `crunched`    `Boolean`       Set the style to crunched.       False   No
                                Supported by scssphp, not sass.          

  `expand`      `Boolean`       Set the style to expanded.       False   No

  `encoding`    `String`        Default encoding for input       utf-8   No
                                files. Supported by scssphp.             

  `executable`  `String`        Location/name of the sass        sass    No
                                executable, if required.                 

  `extfilter`   `String`        Extension to filter against.     n/a     No

  `failonerror` `Boolean`       Whether to fail/halt if an error `False` No
                                occurs.                                  

  `flags`       `String`        Additional flags to set for sass n/a     No
                                executable.                              

  `keepsubdirectories `Boolean` Whether to keep the directory    True    No
                                structure when compiling.                

  `linenumbers` `Boolean`       Whether to annotate generated    False   No
                                CSS with source file and line            
                                numbers.                                 

  `nested`      `Boolean`       Set the style to expanded.       true    No

  `newext`      `String`        Extension for newly created      css     No
                `               files.                                   

  `nocache`     `Boolean`       Whether to cache parsed sass     n/a     No
                n`              files.                                   

  `outputpath`  `String`        Where to place the generated CSS n/a     Yes
                `               files.                                   

  `path`        `String`        Specify sass import path. e.g.   n/a     No
                                --load-path ...                          

  `removeoldext `Boolean`       Whether to strip existing        True    No
  `                             extension off the output                 
                                filename.                                

  `style`       `String`        Name of style to output. Must be nested  No
                `               one of 'nested', 'compact',              
                                'compressed', 'crunched' or              
                                'expanded'. 'Helper' attributes          
                                may also be used. 'crunched' is          
                                supported by scssphp only.               

  `trace`       `Boolean`       Whether to show a full stack     False   No
                                trace on error.                          

  `unixnewlines `Boolean`       Use Unix-style newlines in       True    No
                                written files.                           

  `useSass`     `Boolean`       Whether to use the 'sass'        True    No
                                command line tool. Takes                 
                                precedence over scssphp if both          
                                are available and enabled.               

  `useScssphp`  `Boolean`       Whether to use the 'scssphp' PHP True    No
                                package.                                 
  --------------------------------------------------------------------------

  : Attributes

The useSass and useScssphp attributes can be used to indicate which
compiler should be used, which would be useful if both are available. If
both are available and enabled, then the 'sass' compiler is used rather
than the scssphp library.

Example
-------

``` {.xml}
<sass style="compact" trace="yes" unixnewlines="yes" outputpath="${compiled.dir.resolved}">
    <fileset dir="."/>
</sass>
```

Supported Nested Tags
---------------------

-   `fileset`

