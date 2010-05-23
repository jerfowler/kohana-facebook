<?php defined('SYSPATH') or die('No direct script access.');

class Facebook_Attachment_Flash extends Facebook_Attachment
{
	protected $_options = array(
		'width' => '',
		'height' => '',
		'expanded_width' => '',
		'expanded_height' => ''
	);

 	public function __construct(array $values = NULL)
	{
		parent::__construct($values);
		$this->_attachment['media'] = array();
	}

	public function add_flash($swfsrc, $imgsrc, array $options = NULL)
	{
		$options = (isset($options))
		    ? array_intersect_key($options, $this->_options)
		    : array();

		$values = array(
		    'type' => 'flash',
		    'swfsrc' => $swfsrc,
		    'imgsrc' => $imgsrc
		);

		$this->_attachment['media'][0] = Arr::merge($values, $options);
	}
}