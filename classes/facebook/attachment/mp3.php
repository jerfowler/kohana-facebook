<?php defined('SYSPATH') or die('No direct script access.');


class Facebook_Attachment_MP3 extends Facebook_Attachment
{
	protected $_options = array(
		'title' => '',
		'artist' => '',
		'album' => ''
	);

 	public function __construct(array $values = NULL)
	{
		parent::__construct($values);
		$this->_attachment['media'] = array();
	}

	public function add_mp3($src, array $options = NULL)
	{
		$options = (isset($options))
		    ? array_intersect_key($options, $this->_options)
		    : array();

		$values += array(
		    'type' => 'mp3',
		    'src' => $src,
		);

		$this->_attachment['media'][0] = Arr::merge($values, $options);
	}
}