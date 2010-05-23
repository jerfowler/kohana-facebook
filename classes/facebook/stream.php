<?php defined('SYSPATH') or die('No direct script access.'); 

class Facebook_Stream
{
	static $input_prefix = 'stream-';
	static $input_suffix = '';
	static $form_template = '<h4 class="tuth4">:label</h4><p class="formtxtb">:description</p>:input';

	protected $_inputs = array(
	    'user_message' => array(
		'label' => 'User Message',
		'description' => 'The main user-entered message for the post. This field should typically be blank.',
		'hidden' => false,
	    ),
	    'attachment' => array('hidden' => true),
	    'action_links' => array('hidden' => true),
	    'target_id' => array('hidden' => true),
	    'user_message_prompt' => array('hidden' => true),
	    'callback' => array('hidden' => true),
	    'auto_publish' => array('hidden' => true),
	    'actor_id' => array('hidden' => true),
	);

	protected $_stream = array(
	    'user_message' => '',
	    'attachment' => NULL,
	    'action_links' => NULL,
	    'target_id' => NULL,
	    'user_message_prompt' => NULL,
	    'callback' => NULL,
	    'auto_publish' => FALSE,
	    'actor_id' => NULL,
	);

	protected $_datatypes = array(
	    'user_message' => 'string',
	    'attachment' => 'object',
	    'action_links' => 'object',
	    'target_id' => 'string',
	    'user_message_prompt' => 'string',
	    'callback' => 'object',
	    'auto_publish' => 'boolean',
	    'actor_id' => 'string',
	);

	protected $_attachment = NULL;

	public static function factory($type, array $values = NULL, array $params = NULL)
	{
		$stream = new Facebook_Stream($type, $values);
		return (isset ($params)) ? $stream->params($params) : $stream;
	}

	public function __construct($type, array $values = NULL)
	{
		$this->_attachment = Facebook_Attachment::factory($type, $values);
		if ( ! empty($values))
		{
			$values = array_intersect_key($values, $this->_stream);
			$this->_stream = Arr::merge($values, $this->_stream);
		}
	}
	
	
	public function  __get($name) 
	{
		if ( ! array_key_exists($name, $this->_stream))
		{
			throw new Kohana_Exception('Not a valid stream parameter - :name', array(':name' => $name));
		}

		switch($this->_datatypes[$name])
		{
			case 'string':
			    return (empty ($this->_stream[$name])) ? 'null' : '\''.addslashes($this->_stream[$name]).'\'';
			    break;
			case 'object':
			    if ($name == 'attachment')
			    {
				return $this->_attachment->render();
			    }
			    return (empty ($this->_stream[$name])) ? 'null' : $this->_stream[$name];
			    break;
			case 'boolean':
			    return ($this->_stream[$name]) ? 'true' : 'false';
			    break;
		}
	}
	
	public function  __set($name, $value) 
	{
		if( ! array_key_exists($name, $this->_stream))
		{
		    throw new Kohana_Exception('Not a valid stream option - :name', array(':name' => $name));
		}
		if ($name == 'attachment')
		{
		    $this->_attachment->values($value);
		}
		$this->_stream[$name] = $value;
		return $this;
	}

	public function  __toString() 
	{
		return $this->render();
	}

	public function attachment()
	{
		return $this->_attachment;
	}

	public function input($name, $attributes = NULL)
	{
	    $input = Facebook_Stream::$input_prefix.$name.Facebook_Stream::$input_suffix;
	    if ($name == 'attachment')
	    {
		return $this->_attachment->inputs($attributes);
	    }
	    if ($this->_inputs[$name]['hidden'])
	    {
		return Form::hidden($input, $this->_stream[$name], $attributes);
	    }
		return Form::input($input, $this->_stream[$name], $attributes);
	}

	public function inputs($attributes = NULL)
	{
		$out = array();
		
		foreach ($this->_stream as $name => $value)
		{
			$input = Facebook_Stream::$input_prefix.$name.Facebook_Stream::$input_suffix;
			if($this->_inputs[$name]['hidden'])
			{
				$out[] = $this->input($name, $attributes);
			}
			else
			{
				$out[] = strtr(Facebook_Stream::$form_template, array(
				    ':label' => Form::label($input, $this->_inputs[$name]['label'], $attributes),
				    ':description' => $this->_inputs[$name]['description'],
				    ':input' => $this->input($name, $attributes)
				));
			}
		
		}
		return implode("\n", $out);
	}

	public function parse_post(array $post)
	{
		Fire::info($post);
		$stream = array();
		foreach($post as $name => $value)
		{
			if(strpos($name, Facebook_Stream::$input_prefix) !== FALSE)
			{
				$stream[substr($name, strlen(Facebook_Stream::$input_prefix))] = $value;
			}
		}
		Fire::info($stream, 'strpos');
		$stream = $this->explodeTree($stream, '-');
		Fire::info($stream, 'tree');
		$stream = array_intersect_key($stream, $this->_stream);
		Fire::info($stream, 'intersect');
		Fire::info($this->_stream, '_stream');
		$this->_stream = Arr::merge($this->_stream, $stream);
		Fire::info($this->_stream, 'merge');
		if (isset($this->_stream['attachment']))
		{
			$this->_attachment->values($this->_stream['attachment']);
		}
		Fire::info($this);
		return $this;
	}

	public function params(array $params)
	{
		$params = array_intersect_key($params, $this->_stream);
		$this->_stream = Arr::merge($params, $this->_stream);
		return $this;
	}

	public function as_array()
	{
		return $this->_stream;
	}
//
//	public function target_app()
//	{
//		$this->_stream['target_id'] = Kohana::config('facebook.default.app_id');
//		// target_id is not allowed when actor_id is specified
//		$this->_stream['actor_id'] = NULL;
//		return $this;
//	}

	public function actor_app()
	{
		$this->_stream['actor_id'] = Kohana::config('facebook.default.app_id');
		// target_id is not allowed when actor_id is specified
		$this->_stream['target_id'] = NULL;
		return $this;
	}

	public function message($text = '')
	{
		$this->_stream['user_message'] = '\''.$text.'\'';
		return $this;
	}

	public function render_form($action = NULL, $attributes = NULL)
	{
		$out = array();
		$form = (isset($attributes)) ? Arr::merge($attributes, array('enctype' => 'multipart/form-data')) : array('enctype' => 'multipart/form-data');
		$out[] = Form::open($action, $form);
		$out[] = $this->inputs($attributes);
		$out[] = Form::submit('submit', 'facebook_api_stream', $attributes);
		$out[] = Form::close();
		return implode('<br/>'."\n", $out);

	}

	public function render()
	{
	    	$out  = '<script type="text/javascript">'."\n";
		$out .= ' function callPublish() { '."\n";
		$out .= ' FB.ensureInit(function () { '."\n";
		$out .= ' FB.Connect.streamPublish(';
		$params = array();
		foreach($this->_stream as $name => $value)
		{
			$params[] = $this->{$name};
		}
		$out .= implode(', ', $params);
		$out .= ');'."\n";
		$out .= '}); }'."\n";
		$out .= 'callPublish();'."\n";
		$out .= '</script>'."\n";
		return $out;
	}

	public function render_preview()
	{
	    	$out  = '<script type="text/javascript">'."\n";
		$out .= ' function callPublish() { '."\n";
		$out .= ' FB.ensureInit(function () { '."\n";
		$out .= ' FB.Connect.streamPublish(';
		$params = array();
		foreach($this->_stream as $name => $value)
		{
		    $params[] = $this->{$name};
		}
		$out .= implode(', ', $params);
		$out .= ');'."\n";
		$out .= '}); }'."\n";
		$out .= '</script>'."\n";
		$out .= '<input type="button" onclick="callPublish();return false;" value="Preview Dialog" />';
		return $out;
	}

	public function render_publish($button = 'Publish Stream')
	{
	    	$out  = '<script type="text/javascript">'."\n";
		$out .= ' function callPublish() { '."\n";
		$out .= ' FB.ensureInit(function () { '."\n";
		$out .= ' FB.Connect.streamPublish(';
		$params = array();
		foreach($this->_stream as $name => $value)
		{
		    $params[] = $this->{$name};
		}
		$out .= implode(', ', $params);
		$out .= ');'."\n";
		$out .= '}); }'."\n";
		$out .= '</script>'."\n";
		$out .= '<input type="button" onclick="callPublish();" value="'.$button.'" />';
		return $out;
	}

	/**
	 * Explode any single-dimensional array into a full blown tree structure,
	 * based on the delimiters found in it's keys.
	 *
	 * @author    Kevin van Zonneveld <kevin@vanzonneveld.net>
	 * @author    Lachlan Donald
	 * @author    Takkie
	 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	 * @version   SVN: Release: $Id: explodeTree.inc.php 89 2008-09-05 20:52:48Z kevin $
	 * @link      http://kevin.vanzonneveld.net/
	 *
	 * @param array   $array
	 * @param string  $delimiter
	 * @param boolean $baseval
	 *
	 * @return array
	 */
	function explodeTree($array, $delimiter = '_', $baseval = false)
	{
	    if(!is_array($array)) return false;
	    $splitRE   = '/' . preg_quote($delimiter, '/') . '/';
	    $returnArr = array();
	    foreach ($array as $key => $val) {
		// Get parent parts and the current leaf
		$parts    = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
		$leafPart = array_pop($parts);

		// Build parent structure
		// Might be slow for really deep and large structures
		$parentArr = &$returnArr;
		foreach ($parts as $part) {
		    if (!isset($parentArr[$part])) {
			$parentArr[$part] = array();
		    } elseif (!is_array($parentArr[$part])) {
			if ($baseval) {
			    $parentArr[$part] = array('__base_val' => $parentArr[$part]);
			} else {
			    $parentArr[$part] = array();
			}
		    }
		    $parentArr = &$parentArr[$part];
		}

		// Add the final part to the structure
		if (empty($parentArr[$leafPart])) {
		    $parentArr[$leafPart] = $val;
		} elseif ($baseval && is_array($parentArr[$leafPart])) {
		    $parentArr[$leafPart]['__base_val'] = $val;
		}
	    }
	    return $returnArr;
	}


}