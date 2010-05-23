<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *  KO3 Facebook API
 *  Version 0.1
 *  Last changed: 2010-01-23
 */

// Grab the vendor api library
require_once Kohana::find_file('vendor', 'facebook/facebook');
//require Kohana::find_file('vendor', 'facebook/facebook_mobile', $ext = '.php');

/**
 *  Main Class
 */
class Vendor_Facebook extends Facebook {
    // Configuration
    protected $_config = array();
    // Singleton instance of Facebook
    protected static $instance = null;

    public function  __construct(array $config=Null)
    {
	$this->_config = Kohana::config('facebook.default');
	if (isset($config)) {
	    $this->_config = Arr::merge($this->_config, $config);
	}
	$api_key = $this->get_config('api_key');
	$secret = $this->get_config('secret');
	$generate_session_secret = $this->get_config('generate_session_secret', FALSE);
	parent::__construct($api_key, $secret, $generate_session_secret);

    }

    final private function  __clone() {
	// Shouldn't need more than one instance of this...
    }

    public function get_config($key, $default = NULL)
    {
	return Arr::path($this->_config, $key, $default);
    }

    public function set_config($key, $value=NULL)
    {
	if ( ! empty($key))
	{
	    if (is_array($key)) {
		$value = $key;
	    } else {
		// Convert dot-noted key string to an array
		$keys = explode('.', rtrim($key, '.'));
		// This will set a value even if it didn't previously exist
		do {
		    $key = array_pop($keys);
		    $value = array($key => $value);
		} while ($keys);
	    }
	    $this->_config = Arr::merge($this->_config, $value);
	}
	return $this;
    }

    public function feature_loader(){
	 $tags  = '<script src="%s" type="text/javascript"></script>';
	 return sprintf($tags, $this->get_config('feature_loader'));
    }

    public function js_init(array $appSettings = NULL) {
	 $tags = 'FB.init("%s", "%s"%s);';
	 $opts = '';
	 if (isset($appSettings))
	 {
	     $opts = ', {';
	     foreach ($appSettings as $option => $value)
	     {
		 $opts .= ($opts == ', {') ? '"' : ', "';
		 $opts .= $option.'":'.$value;
	     }
	     $opts .= '}';
	 }
	 return sprintf($tags, $this->api_key, Url::site($this->get_config('xdreceiver'), true), $opts);
    }

    protected function _get_options($element_name, array $options = NULL) {
	if ( ! empty($options)) {
	    return Arr::merge($this->get_config($element_name), $options);
	} else {
	    return $this->get_config($element_name);
	}
    }

    protected function _set_attributes($element_tag, $element_name, array $options = NULL) {
	$options = $this->_get_options($element_name, $options);
	$attributes = '';
	$inner = '';
	foreach ($options as $name => $value){
	    if (isset ($value))
	    {
		if ($name == 'element_text' or $name == 'inner_html') {
		    $inner = $value;
		} else {
		    $attributes .= $name.'="'.$value.'" ';
		}
	    }
	}
	return sprintf($element_tag, $attributes, $inner);
    }

    public function fb_login_button(array $options = NULL) {
	$tag = '<fb:login-button %s>%s</fb:login-button>';
	return $this->_set_attributes($tag, 'login-button', $options);
    }

    public function fb_logout_link(array $options = NULL) {
	$tag = '<a %s>%s</a>';
	$options = $this->_get_options('logout-link', $options);
	$href = (array_key_exists('href', $options)) ? $options['href'] : Url::site(Request::instance()->uri, TRUE);
	$options['href'] = '#';
	$options['onclick'] = 'FB.Connect.logoutAndRedirect(\''.$href.'\');';
	return $this->_set_attributes($tag, 'logout-link', $options);
    }
    
    public function fb_profile_pic(array $options=NULL) {
	$tag = '<fb:profile-pic %s>%s</fb:profile-pic>';
	return $this->_set_attributes($tag, 'profile-pic', $options);
    }

    public function fb_name(array $options=NULL) {
	$tag = '<fb:name %s>%s</fb:name>';
	return $this->_set_attributes($tag, 'name', $options);
    }

    public static function instance($_config=Null) {
	if( !self::$instance) {
	    self::$instance = new self($_config);
	} else {
	    self::$instance->set_config($_config);
	}
	return self::$instance;
    }
}