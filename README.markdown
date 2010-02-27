# Inclued

## Usage

This little class allows you to generate kind of graphic :
![Inclued graphtree](http://blog.eexit.net/wp-content/uploads/2010/02/inclued2.png "Inclued graph example")

### Basic example

    <?php
    require 'My_Class.php';
    require_once 'Inclued.php';
    $foo = new \inclued\Inclued;
    $foo->genClue();
    ?>

This will output

    array
      'request' => 
        array
          '_COOKIE' => 
            array
              empty
      'includes' => 
        array
          0 => 
            array
              'operation' => string 'include' (length=7)
              'op_type' => int 2
              'filename' => string 'My_Class.php' (length=12)
              'opened_path' => string '/htdocs/lab/My_Class.php' (length=24)
              'fromfile' => string '/htdocs/lab/Inclued/inclued_test.php' (length=36)
              'fromline' => int 10
          1 => 
            array
              'operation' => string 'require_once' (length=12)
              'op_type' => int 16
              'filename' => string 'Inclued.php' (length=11)
              'opened_path' => string '/htdocs/lab/Inclued/Inclued.php' (length=32)
              'fromfile' => string '/htdocs/lab/Inclued/inclued_test.php' (length=36)
              'fromline' => int 28
      'inheritance' => 
        array
          empty
      'classes' => 
        array
          0 => 
            array
              'name' => string 'My_Class' (length=8)
              'filename' => string '/htdocs/lab/My_Class.php' (length=24)
              'line' => int 26
          1 => 
            array
              'name' => string 'inclued\Inclued_Exception' (length=25)
              'filename' => string '/htdocs/lab/Inclued/Inclued.php' (length=32)
              'line' => int 23
              'parent' => 
                array
                  'name' => string 'Exception' (length=9)
                  'internal' => boolean true
          2 => 
            array
              'name' => string 'inclued\Inclued' (length=15)
              'filename' => string '/htdocs/lab/Inclued/Inclued.php' (length=32)
              'line' => int 26

To **do not** include the Inclued file itself, add `false` parameter to `Inclued::genClue()`.
Once you have your datas, generate your grapth :

    <?php
     require 'My_Class.php';
     require_once 'Inclued.php';
     $foo = new \inclued\Inclued;
     $foo->genClue()
         ->saveClue()
         ->genGraph();
    ?>

This will output files :
    * inclued.clue
    * inclued.png

If you want to give another name to file, specify it as parameter or if you want a more specific name, use PHP5.3 closures :

    <?php
     require 'My_Class.php';
     require_once 'Inclued.php';
     $foo = new \inclued\Inclued;
     $foo->genClue(false)
         ->saveClue(function($fn) {
             return date('Y-m-d') . '_' . $fn;
         })
         ->genGraph();
    ?>

- - -
## Changelog

### 2010-02-27 — Initial release

## Contact

Joris Berthelot <admin@eexit.net>
[http://www.eexit.net](http://www.eexit.net "http://www.eexit.net")