<?php
/**
 * Inclued
 * 
 * @copyright Copyright (c) 2010, Joris Berthelot
 * @author Joris Berthelot <admin@eexit.net>
 * @package Inclued
 * 
 *  ###########
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
 * 
 */

namespace Inclued;

/**
 * Inclued
 * 
 * @copyright Copyright (c) 2010, Joris Berthelot
 * @author Joris Berthelot <admin@eexit.net>
 * @since 1.00
 * @version 1.10
 * @package Inclued
 */
class Inclued
{
    
    /**
     *  The generator filename given in the PECL package
     * 
     *  @since 1.00
     *  @version 1.00
     */
    const GENERATOR_FILENAME = 'gengraph.php';
    
    /**
     *  The PHP CLI command to transform the serialized data into DOT file
     * 
     *  @since 1.00
     *  @version 1.10
     */
    const GENERATOR_CMD = 'php %gen% -d %dir% -i %clue% -o ~tmp.dot';
    
    /**
     *  The succeed PHP CLI command last message
     * 
     *  @since 1.00
     *  @version 1.00
     */
    const GENERATOR_CMD_SUCCEED = 'To generate images: dot -Tpng -o inclued.png ~tmp.dot';
    
    /**
     *  The bash command to generate PNG file from DOT file
     * 
     *  @since 1.00
     *  @version 1.00
     */
    const GRAPHVIZ_CMD = 'dot -Tpng -o %png% ~tmp.dot';
    
    /**
     *  The default CLUE file
     * 
     *  @since 1.00
     *  @version 1.00
     *  @access public
     */
    public $clue_filename = 'inclued.clue';
    
    /**
     *  The default generator file path (PHP include_path)
     * 
     *  @since 1.00
     *  @version 1.00
     *  @access public
     */
    public $generator_default_path;
    
    /**
     *  The CLUE data
     * 
     *  @since 1.00
     *  @version 1.00
     *  @access private
     */
    private $_clue;
    
    /**
     *  Inclued constructor. Checks if inclued extension is loaded and activated
     *  and initializes the default generator file path
     * 
     *  @since 1.00
     *  @version 1.00
     *  @access public
     * 
     *  @throws Exception
     */
    public function __construct()
    {
        if (!function_exists('inclued_get_data') || !ini_get('inclued.enabled')) {
            throw new Exception('inclued extension not loaded or not activated!');
        }
        
        $paths = explode(':', get_include_path());
        
        // Gets default PHP include
        $this->generator_default_path = array_pop($paths)
            . DIRECTORY_SEPARATOR
            . self::GENERATOR_FILENAME;
    }
    
    /**
     *  Generates the CLUE, this method should be called once every inclusions are
     *  done (EOF most of time)
     * 
     *  @since 1.00
     *  @version 1.00
     *  @access public
     * 
     *  @param [bool $self_exclude = false] Removes self file inclued data if true
     *  @return $this Inclued instance
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
     * 
     *  @since 1.00
     *  @version 1.00
     *  @access public
     * 
     *  @return array $this->_clue The clue data
     */
    public function getClue()
    {
        return $this->_clue;
    }
    
    /**
     *  Saves the CLUE datas in a CLUE serialized file
     * 
     *  @since 1.00
     *  @version 1.10
     *  @access public
     * 
     *  @param [mixed $clue_filename = null] The CLUE filename. Could be a direct
     *  filename or a closure which takes one parameter (the default CLUE filename)
     *  and must return a filename
     *  @param [string $compressor = serialized] The clue data compressor to be
     *  treated by graph generator. Must be « serialized » or « json ».
     *  @return $this Inclued instance
     *  @throws Exception
     */
    public function saveClue($clue_filename = null, $compressor = 'serialized')
    {        
        if (is_callable($clue_filename)) {
            $this->clue_filename = $clue_filename($this->clue_filename);
        }
        
        if (!in_array($compressor, array('serialized', 'json'))) {
            throw new Exception('Invalid compressor parameter. Must be « serialized »
                or « json »!');
        }
        
        if (!fopen($this->clue_filename, 'w+')) {
            throw new Exception('Unable to create the CLUE file '
                . $this->clue_filename);
        }
        
        switch ($compressor) {
            case 'serialized' :
                $data = serialize($this->_clue);
                break;
            case 'json' :
                $data = json_encode($this->_clue);
                break;
        }
        
        if (!is_writable($this->clue_filename) 
            || false === file_put_contents($this->clue_filename, $data)) {
            throw new Exception('Unable to write in the CLUE file '
                . $this->clue_filename);
        }
        
        return $this;
    }
    
    /**
     *  Generates a DOT file from CLUE file and generates a PNG file from DOT file
     * 
     *  @since 1.00
     *  @version 1.10
     *  @access public
     * 
     *  @param [string|closure $ignore_path = __DIR__] The path to ignore in the graph. Should
     *  be the path from / to all included files (lib directory).
     *  @param [mixed $generator_full_path = null] The absolute path to the generator
     *  file if not found in the PHP include_path. Could be a direct filename or
     *  a closure which has no parameter and must return a filename
     *  @return $this Inclued instance
     *  @throws Exception
     */
    public function genGraph($ignore_path = __DIR__, $generator_full_path = null)
    {
        if (is_callable($ignore_path)) {
            $ignore_path = $ignore_path();
        } elseif (!is_dir($ignore_path)) {
            $ignore_path = __DIR__;
        }
        
        if (is_callable($generator_full_path)) {
            $gen = $generator_full_path();
        } elseif (is_file($generator_full_path)) {
            $gen = $generator_full_path;
        } else {
            $gen = $this->generator_default_path;
        }
        
        if (!is_file($gen)) {
            throw new Exception('The '
                . self::GENERATOR_FILENAME
                . ' ('
                . $gen
                . ') does not exist!');
        }
        
        if (!is_file($this->clue_filename)) {
            throw new Exception('The CLUE file '
                . $this->clue_filename
                . ' does not exist!');
        }
        
        // PHP script generator return a default succeed message
        if (self::GENERATOR_CMD_SUCCEED !== exec(str_replace(
                array('%dir%', '%gen%', '%clue%'),
                array($ignore_path, $gen, $this->clue_filename),
                self::GENERATOR_CMD
        ))) {
            throw new Exception('DOT file generation failed! Results:' . $result);
        }
    
        // Obtains the PNG filename from the CLUE file
        $png = function($clue_filename) {
            return rtrim($clue_filename, '.clue') . '.png';
        };
        
        // No error = empty string
        if (0 < strlen(exec(
            str_replace('%png%', $png($this->clue_filename), self::GRAPHVIZ_CMD
        )))) {
            throw new Exception('DOT to PNG transformation proccess failed!');
        }
        
        @unlink('~tmp.dot');
        @unlink($this->clue_filename);
        return $this;
    }
}

/*
Filename: Inclued.php
Date: Thu, 05 Aug 2010 17:03:13 CEST
Tab size: 4
Soft tabs: YES
Column limit: 80
Word count: 1068
*/
?>