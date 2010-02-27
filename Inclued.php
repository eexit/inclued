<?php
/*
* ###########
* #__________#
* __________#
* ________#
* _____###_____²xiT development
* _________#
* ___________#
* #__________#
* _#________#
* __#______#
* ____####
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
* IN THE SOFTWARE.
*/
namespace inclued;

class Inclued_Exception extends \Exception
{}

/**
*   @author Joris Berthelot <admin@eexit.net>
*   @copyright Copyright (c) 2010, Joris Berthelot
*   @version 1.00
*/
class Inclued
{
    
    /**
     *  The generator filename given in the PECL package
     *  @since 1.00
     *  @version 1.00
     */
    const GENERATOR_FILENAME = 'gengraph.php';
    
    /**
     *  The PHP CLI command to transform the serialized data into DOT file
     *  @since 1.00
     *  @version 1.00
     */
    const GENERATOR_CMD = 'php %gen% -i %clue% -o ~tmp.dot';
    
    /**
     *  The succeed PHP CLI command last message
     *  @since 1.00
     *  @version 1.00
     */
    const GENERATOR_CMD_SUCCEED = 'To generate images: dot -Tpng -o inclued.png ~tmp.dot';
    
    /**
     *  The bash command to generate PNG file from DOT file
     *  @since 1.00
     *  @version 1.00
     */
    const GRAPHVIZ_CMD = 'dot -Tpng -o %png% ~tmp.dot';
    
    /**
     *  The default CLUE file
     *  @since 1.00
     *  @version 1.00
     *  @access public
     */
    public $clue_filename = 'inclued.clue';
    
    /**
     *  The default generator file path (PHP include_path)
     *  @since 1.00
     *  @version 1.00
     *  @access public
     */
    public $generator_default_path;
    
    /**
     *  The CLUE data
     *  @since 1.00
     *  @version 1.00
     *  @access private
     */
    private $_clue;
    
    /**
     *  Inclued constructor. Checks if inclued extension is loaded and activated
     *  and initializes the default generator file path
     *  @since 1.00
     *  @version 1.00
     *  @access public
     */
    public function __construct()
    {
        if (!function_exists('inclued_get_data') || !ini_get('inclued.enabled')) {
            throw new Inclued_Exception('inclued extension not loaded or not actived!');
        }
        
        $paths = explode(':', get_include_path());
        $this->generator_default_path = $paths[1]
            . DIRECTORY_SEPARATOR
            . self::GENERATOR_FILENAME;
    }
    
    /**
     *  Generates the CLUE, this method should be called once every inclusions are
     *  done (EOF most of time)
     *  @since 1.00
     *  @version 1.00
     *  @access public
     *  @param [bool $self_exclude = false] Removes self file inclued data if true
     *  @return object Inclued instance
     */
    public function genClue($self_exclude = false)
    {
        $this->_clue = inclued_get_data();
        
        if ($self_exclude) {
            foreach($this->_clue as $inc_tk => $inc_tv) {
                foreach($inc_tv as $inc_vk => $inc_vv) {
                    if (strstr($inc_vv['name'], __NAMESPACE__)
                        || $inc_vv['filename'] == basename(__FILE__)) {
                        unset($this->_clue[$inc_tk][$inc_vk]);
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     *  Returns CLUE datas
     *  @since 1.00
     *  @version 1.00
     *  @access public
     *  @return array CLUE datas
     */
    public function getClue()
    {
        return $this->_clue;
    }
    
    /**
     *  Saves the CLUE datas in a CLUE serialized file
     *  @since 1.00
     *  @version 1.00
     *  @access public
     *  @param [mixed $clue_filename = null] The CLUE filename. Could be a direct
     *  filename or a closure which takes one parameter (the default CLUE filename)
     *  and must return a filename
     *  @return object Inclued instance
     */
    public function saveClue($clue_filename = null)
    {
        if (is_callable($clue_filename)) {
            $this->clue_filename = $clue_filename($this->clue_filename);
        }
        
        if (!fopen($this->clue_filename, 'w+')) {
            throw new Inclued_Exception('Unable to create the CLUE file '
                . $this->clue_filename);
        }
        
        if (!is_writable($this->clue_filename) 
            || false === file_put_contents($this->clue_filename, serialize($this->_clue))) {
            throw new Inclued_Exception('Unable to write in the CLUE file '
                . $this->clue_filename);
        }
        
        return $this;
    }
    
    /**
     *  Generates a DOT file from CLUE file and generates a PNG file from DOT file
     *  @since 1.00
     *  @version 1.00
     *  @access public
     *  @param [mixed $generator_full_path = null] The absolute path to the generator
     *  file if not found in the PHP include_path. Could be a direct filename or
     *  a closure which has no parameter and must return a filename
     *  @return object Inclued instance
     */
    public function genGraph($generator_full_path = null)
    {
        if (is_callable($generator_full_path)) {
            $gen = $generator_full_path();
        } elseif (is_file($generator_full_path)) {
            $gen = $generator_full_path;
        } else {
            $gen = $this->generator_default_path;
        }
        
        if (!is_file($gen)) {
            throw new Inclued_Exception('The '
                . self::GENERATOR_FILENAME
                . ' ('
                . $gen
                . ') does not exist!');
        }
        
        if (!is_file($this->clue_filename)) {
            throw new Inclued_Exception('The CLUE file '
                . $this->clue_filename
                . ' does not exist!');
        }
        
        // PHP script generator return a default succeed message
        if (self::GENERATOR_CMD_SUCCEED !== exec(
            str_replace(
                array('%gen%', '%clue%'),
                array($gen, $this->clue_filename),
                self::GENERATOR_CMD
        ))) {
            throw new Inclued_Exception('DOT file generation failed!');
        }
    
        // Obtains the PNG filename from the CLUE file
        $png = function($clue_filename) {
            return rtrim($clue_filename, '.clue') . '.png';
        };
        
        // No error = empty string
        if (0 < strlen(exec(
            str_replace('%png%', $png($this->clue_filename), self::GRAPHVIZ_CMD
        )))) {
            throw new Inclued_Exception('DOT to PNG transformation proccess failed!');
        }
        
        @unlink('~tmp.dot');
        return $this;
    }
}
?>