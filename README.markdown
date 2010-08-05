# Inclued

Just a graph generation class to use with the [inclued](http://pecl.php.net/package/inclued "inclued") PHP PECL package.
Have fun using it and improving it!

## Usage

Firstfully, move **gengraph.php** in your default include path (/*/lib/php).
This little class allows you to generate kind of graphic :

![Inclued graphtree](http://www.eexit.net/projects/inclued/inclued.png "Inclued graph example")

### Basic example

    <?php
    require_once '../listFiles.php';
    require_once 'Inclued/Inclued.php';
    require_once 'Inclued/Exception.php';
    
    $clue = new Inclued\Inclued();
    $clue->genClue()
         ->saveClue()
         ->genGraph();
    var_dump($clue->getClue());
    ?>

More examples on [http://www.eexit.net/projects/inclued.html](the project page "http://www.eexit.net/projects/inclued.html")

## Changelog

### 2010-08-05 — Version 1.10
    *  ADDED custom gengraph.php file in the package
    * ADDED Exception class has now it own file
    * ADDED parameter in Inclued::saveClue() to choose the way to compress clue datas
    * ADDED parameter in Inclued::genGraph() to avoid printing abspath from root server
    * MODIFIED Namespace use
    * FIXED gengraph.php default include path if it has been modified before the instanciation of the class
### 2010-02-27 — Version 1.00
    * Intial release

## Contact

Joris Berthelot <admin@eexit.net>

[http://www.eexit.net](http://www.eexit.net "http://www.eexit.net")