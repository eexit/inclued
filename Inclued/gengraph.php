<?php
/*
  +----------------------------------------------------------------------+
  | Inclued                                                              |
  +----------------------------------------------------------------------+
  | Copyright (c) 2007 The PHP Group                                     |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt.                                 |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Gopal Vijayaraghavan <gopalv@php.net>                       |
  +----------------------------------------------------------------------+
*/
$options = getopt("i:t:o:d:s:");

function usage()
{
	echo "{$GLOBALS['argv'][0]} -i <inclued_dump> [-t includes|classes] [-o <output_file>] [-d docroot] [-s serialized|json]\n";
	exit(1);
}

if(!isset($options['o'])) $ofile = "inclued.out.dot";	
else $ofile = $options['o'];

if(!isset($options['i'])) usage();
else $ifile = $options['i'];

if(!isset($options['d'])) $docroot = "";
else $docroot = $options['d'];

if(!isset($options['t'])) $type = "classes";
else $type = $options['t'];

if(!isset($options['s'])) $storage_type = "auto_detect";
else $storage_type = $options['s'];

if($type != 'includes' && $type != 'classes')
{
	echo "unknown type provided: $type\n";
	exit(1);
}

if (!in_array($storage_type, array('auto_detect', 'json', 'serialized')))
{
	echo "unknown storage type provided: $storage_type\n";
	exit(1);
}

if (!is_readable($ifile))
{
	echo "Could not read file: $ifile\n";
	exit(1);
}

$ifile_contents = file_get_contents($ifile);
if (!$ifile_contents)
{
	echo "Could not get contents for file: $ifile\n";
	exit(1);
}

$data = get_inclued_data($ifile_contents, $storage_type);
if(!$data)
{
	echo "Could not decode the data dump as type: $storage_type\n";
	exit(1);
}

function dump_include($fp, $inc)
{
	global $docroot;

	$style = isset($inc['duplicate']) ? "dashed" : "solid";

	$short_fromfile = str_replace($docroot, '', $inc['fromfile']);
	$short_opened_path = str_replace($docroot, '', $inc['opened_path']);
	
	if(isset($inc["autoload"])) 
	{
		$autoload = $inc['autoload'];
		$short_fromfile = str_replace($docroot, '', $autoload['fromfile']);
		
		$content = <<<EOF
			"{$autoload['fromfile']}" [label="{$short_fromfile}"];
			"{$inc['opened_path']}" [label="{$short_opened_path}"];
			"{$autoload['fromfile']}" -> "{$inc['opened_path']}" [label = "__autoload({$inc['operation']})", style="{$style}"];
EOF;
	}
	else
	{
		$content = <<<EOF
			"{$inc['fromfile']}" [label="{$short_fromfile}"];
			"{$inc['opened_path']}" [label="{$short_opened_path}"];
			"{$inc['fromfile']}" -> "{$inc['opened_path']}" [label = "{$inc['operation']}", style="{$style}"];
EOF;
	}

	fwrite($fp, $content);
}

function dump_filemap($fp, $filemap)
{
	global $docroot;
	
	foreach($filemap as $k => $v)
	{
		$short_path = str_replace($docroot, '', $k);
		$content = <<<EOF
			subgraph  "cluster_{$k}" {
				label="{$short_path}";
				fontcolor = "blue";
				color = "lightgrey";
				fontsize = "11pt";
EOF;
        fwrite($fp, $content);
		
		foreach($v as $kk => $vv)
		{
			$class = $vv;
			if(!isset($class['mangled_name'])) 
			{
				$class['mangled_name'] = strtolower($class['name']);
			}
			$content = <<<EOF
				"{$class['mangled_name']}" [label="{$class['name']}"];
EOF;
			fwrite($fp, $content);
		}

		$content = <<<EOF
		}; /* end subgraph "{$k}"	 */
EOF;
		fwrite($fp, $content);
	}

	foreach($filemap as $k => $v)
	{
		foreach($v as $kk => $vv)
		{
			$class = $vv;
			$derieved = isset($class['parent']);
			$internal = false;
			
			if(!isset($class['mangled_name'])) 
			{
				$class['mangled_name'] = strtolower($class['name']);
			}

			if($derieved)
			{
				$parent_name = strtolower($class['parent']['name']);
				$internal = isset($class['parent']['internal']);
			}
			else
			{
				$parent_name = "<object>";
			}

			$content = <<<EOF

				"{$parent_name}" -> "{$class['mangled_name']}";

EOF;
			if($internal)
			{
				$content .= <<<EOF
					"<object>" -> "{$parent_name}";

EOF;
			}
			fwrite($fp, $content);
		}
	}
}

// Not meant to detect encoding of any value
// We really only care about strings to arrays
function detect_and_get_storage_data($ifile_contents)
{
	if (!is_string($ifile_contents))
	{
		return FALSE;
	}

	$str_ser = @unserialize($ifile_contents);
	if ($str_ser && is_array($str_ser))
	{
		return $str_ser;
	}

	$str_json = @json_decode($ifile_contents, TRUE);
	if ($str_json && is_array($str_json))
	{
		return $str_json;
	}
	
	return FALSE;
}

function get_inclued_data($ifile_contents, $storage_type)
{
	if ($storage_type === 'auto_detect')
	{
		if ($storage_data = detect_and_get_storage_data($ifile_contents))
		{
			return $storage_data;
		}
		else
		{
			return FALSE;
		}
	}

	switch($storage_type)
	{
		case 'json':
			$data = json_decode($ifile_contents, TRUE);
			break;
		case 'serialized':
			$data = unserialize($ifile_contents);
			break;
		default:
			$data = FALSE;
	}
	
	return $data;
}

$fp = fopen($ofile, "wb");

$content = 	<<<EOF
digraph phpdeps {
	node [shape = ellipse];
	node [color="#add960", style=filled];
	graph [bgcolor="#f7f7f7"];
EOF;

fwrite($fp, $content);

if($type == 'includes')
{
	$content = 	<<<EOF
		rankdir = "LR";
EOF;

	fwrite($fp, $content);

	foreach($data["includes"] as $k => $v) dump_include($fp, $v);
}
else /* classes */ 
{
	$filemap = array();

	foreach($data["classes"] as $k => $v) {
		$class = $v;
		
        $class['name'] = str_replace('\\', '\\\\', $class['name']);
        $class['mangled_name'] = str_replace('\\', '\\\\', $class['mangled_name']);
		
		if(isset($class["mangled_name"]))
		{
			if(strstr($class["mangled_name"], '/'))
			{
				/* must have a real counterpart, if it was used */
				continue;
			}
		}

		if(isset($class['internal']))
		{
			$class['filename'] = "internal";
		}
		
		if(!isset($filemap[$class['filename']]))
		{
			$filemap[$class['filename']] = array();
		}

		array_push($filemap[$class['filename']], $class);
	}
	
	dump_filemap($fp, $filemap);
}

fwrite($fp, "}");
fclose($fp);

echo "Written $ofile...\nTo generate images: dot -Tpng -o inclued.png $ofile\n";

/* $Id$ */
?>
