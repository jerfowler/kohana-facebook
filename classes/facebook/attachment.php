<?php defined('SYSPATH') or die('No direct script access.'); 

abstract class Facebook_Attachment
{
	static $input_prefix = 'stream-attachment-';
	static $input_suffix = '';

	protected $_upload = NULL;

	protected $_inputs = array(
	    'name' => array(
		'label' => 'Name',
		'description' => 'The title of the post. The post should fit on one line in a user\'s stream; make sure you account for the width of any thumbnail.',
		'hidden' => false,
	    ),
	    'href' => array(
		'label' => 'Post URL',
		'description' => 'The URL to the source of the post referenced in the name. The URL should not be longer than 1024 characters.',
		'hidden' => false,
	    ),
	    'caption' => array(
		'label' => 'Caption',
		'description' => 'A subtitle for the post that should describe why the user posted the item or the action the user took. This field can contain plain text only, as well as the {*actor*} token, which gets replaced by a link to the profile of the session user. The caption should fit on one line in a user\'s stream; make sure you account for the width of any thumbnail.',
		'hidden' => false,
	    ),
	    'description' => array(
		'label' => 'Description',
		'description' => 'Descriptive text about the story. This field can contain plain text only and should be no longer than is necessary for a reader to understand the story. Facebook displays the first 300 or so characters of text by default; users can see the remaining text by clicking a "See More" link that we append automatically to long stories, or attachments with more than one image.',
		'hidden' => false,
	    ),
	    'properties' => array('hidden' => true),
	    'media' => array('hidden' => true),
	    'comments_xid' => array('hidden' => true),
	);

	protected $_attachment = array(
	    'name' => NULL,
	    'href' => NULL,
	    'caption' => NULL,
	    'description' => NULL,
	    'properties' => NULL,
	    'media' => NULL,
	    'comments_xid' => NULL
	);

	public static function factory($type, array $values = NULL)
	{
		$field = 'Facebook_Attachment_'.$type;
		return new $field($values);
	}

	public function __construct(array $values = NULL)
	{
		if ( ! empty($values))
		{
			$this->_attachment = Arr::merge($values, $this->_attachment);
		}
	}
	
	
	public function  __get($name) 
	{

		if ( ! array_key_exists($name, $this->_attachment))
		{
			throw new Kohana_Exception('Not a valid stream attachment field - :name', array(':name' => $name));
		}

//		switch($_datatypes[$name])
//		{
//			case 'string':
//			    return (empty ($this->_stream[$name])) ? '\'\'' : '\''.addslashes($this->_stream[$name]).'\'';
//			    break;
//			case 'object':
//			    if ($name == 'attachment')
//			    {
//				return $this->_attachment->render();
//			    }
//			    return (empty ($this->_stream[$name])) ? 'null' : $this->_stream[$name];
//			    break;
//			case 'boolean':
//			    return ($this->_stream[$name]) ? 'true' : 'false';
//			    break;
//		}

		if (isset ($this->_attachment[$name]))
		{
		    return $this->_attachment[$name];
		}
		else
		{
		    return NULL;
		}
		
	}
	
	public function  __set($name, $value) 
	{
		//TODO validation...
		$this->_attachment[$name] = $value;
		return $this;
	}

	public function  __toString() 
	{
		return $this->render();
	}

	abstract protected function _media_type($input, $attributes);

	public function values(array $values)
	{
		if (isset ($values['upload']))
		{
		    $this->_upload = $values['upload'];
		    //todo process upload...
		}
		$values = array_intersect_key($values, $this->_attachment);
		$this->_attachment = Arr::merge($this->_attachment, $values);
		$this->_attachment['media'] = (object) $values['media'];
		return $this;
	}

	public function as_array()
	{
		return $this->_attachment;
	}

	public function input($name, $attributes = NULL)
	{
	    $input = Facebook_Attachment::$input_prefix.$name.Facebook_Attachment::$input_suffix;
	    if($name == 'media')
	    {
		return $this->_media_type($input, $attributes);
	    }
	    if($this->_inputs[$name]['hidden'])
	    {
		return '';//Form::hidden($input, $this->_attachment[$name], $attributes);
	    }
	    return Form::input($input, $this->_attachment[$name], $attributes);
	}

	public function inputs($attributes = NULL)
	{
		$out = array();
		foreach ($this->_attachment as $name => $value)
		{
			$input = Facebook_Attachment::$input_prefix.$name.Facebook_Attachment::$input_suffix;
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

	public function render()
	{
//		$out = array();
//		foreach($this->_attachment as $name => $value)
//		{
//		    if (!is_null($value))
//		    {
//			if ($name == 'media')
//			{
//			    $out[$name] = (object) $value;
//			}
//			else
//			{
//			    $out[$name] = $value;
//			}
//
//		    }
//		}

		return json_encode($this->_attachment);
	}

	public function add_property($name, $text, $href=NULL)
	{
		$this->_attachment['properties'] = (isset($this->_attachment['properties']))
			? $this->_attachment['properties']
			: array();
		if (isset($href))
		{
			$this->_attachment['properties'][$name] = array('text' => $text, 'href' => $href);
		}
		else
		{
			$this->_attachment['properties'][$name] = $text;
		}
		return $this;
	}
   
}
