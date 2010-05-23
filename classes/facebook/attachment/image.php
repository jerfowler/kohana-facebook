<?php defined('SYSPATH') or die('No direct script access.');

class Facebook_Attachment_Image extends Facebook_Attachment
{

 	public function __construct(array $values = NULL)
	{
		parent::__construct($values);
		$this->_attachment['media'] = array();
	}

	protected function _media_type($input, $attributes)
	{
	    $type = Facebook_Attachment::$input_prefix.'media-type'.Facebook_Attachment::$input_suffix;
	    $src = Facebook_Attachment::$input_prefix.'media-src'.Facebook_Attachment::$input_suffix;
	    $upload = Facebook_Attachment::$input_prefix.'upload'.Facebook_Attachment::$input_suffix;
	    $href = Facebook_Attachment::$input_prefix.'media-href'.Facebook_Attachment::$input_suffix;
	    $out = array();
	    $out[] = Form::hidden($type, 'image');
	    $out[] = strtr(Facebook_Stream::$form_template, array(
		':label' => Form::label($src, 'Image Location URL', $attributes),
		':description' => 'Each record must contain a src key, which maps to the photo URL',
		':input' => Form::input($src, NULL, $attributes)
	    ));
	    $out[] = strtr(Facebook_Stream::$form_template, array(
		':label' => Form::label($upload, 'Image Upload', $attributes),
		':description' => 'Upload a file to use as the image src',
		':input' => Form::file($upload, $attributes)
	    ));
	    $out[] = strtr(Facebook_Stream::$form_template, array(
		':label' => Form::label($href, 'Image Hyperlink Destination', $attributes),
		':description' => 'Maps to the URL where a user should be taken if he or she clicks the photo.',
		':input' => Form::input($href, NULL, $attributes)
	    ));
	    return implode("\n", $out);

	}

	public function add_image($src, $href)
	{
		if (count($this->_attachment['media'] <= 5))
		{
			$this->_attachment['media'][] = array(
			    'type' => 'image',
			    'src' => $src,
			    'href' => $href
			);
		}
	}
}