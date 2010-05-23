<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *  KO3 Facebook API
 *  Version 0.1
 *  Last changed: 2010-01-23
 */

/**
 *  Helper Class
 */
abstract class Facebook_FB {

    public function set_config($key, $value=NULL) 
    {
	return Facebook_API::instance()->set_config($key, $value);
    }

    public static function feature_loader(){
	return Facebook_API::instance()->feature_loader();
    }
    
    public static function js_init(array $appSettings = NULL)
    {
	return Facebook_API::instance()->js_init($appSettings);
    }

    public static function api_client()
    {
	return Facebook_API::instance()->api_client;
    }

    public static function require_login($required_permissions = '')
    {
	return Facebook_API::instance()->require_login($required_permissions);
    }

    public static function get_loggedin_user()
    {
	return Facebook_API::instance()->get_loggedin_user();
    }

    public static function logout($next)
    {
	return Facebook_API::instance()->logout($next);
    }
    
    // XFBML

    public static function login_button(array $options = NULL)
    {
	return Facebook_API::instance()->fb_login_button($options);
    }

    public static function logout_link(array $options = NULL) {
	return Facebook_API::instance()->fb_logout_link($options);
    }
    
    public static function profile_pic(array $options=NULL)
    {
	return Facebook_API::instance()->fb_profile_pic($options);
    }

    public static function name(array $options=NULL) {
	return Facebook_API::instance()->fb_name($options);
    }

    public static function getStandardInfo($uid, array $fields) {
	return Facebook_API::instance()->users_getStandardInfo($uid, $fields);
    }

    public static function getInfo($uid, array $fields) {
	return Facebook_API::instance()->users_getInfo($uid, $fields);
    }

    public static function fql_query($query) {
	return Facebook_API::instance()->api_client->fql_query($query);
    }

}