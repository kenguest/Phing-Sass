# Phing-Sass
Phing Sass task that uses either gem installed sass or scssphp.

Based on https://github.com/phingofficial/phing/pull/151/ and https://github.com/phingofficial/phing/pull/206/


<sass style="compact" trace="yes" unixnewlines="yes" outputpath="${compiled.dir.resolved}">
    <fileset dir="."/>
</sass>
