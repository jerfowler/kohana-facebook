<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Auth Facebook Connect Driver w/ ORM fallback
 */
class Facebook_Auth_ORM extends Auth_ORM
{

	/**
	 * Checks if a session is active.
	 *
	 * @param   string   role name
	 * @param   array    collection of role names
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		$user = $this->session->get($this->config['session_key']);

		if (! (is_object($user) AND $user instanceof Model_User AND $user->loaded()))
		{
		    if ($fb_uid = FB::get_loggedin_user())
		    {
			$user = ORM::factory('user')->where('fb_uid', '=', $fb_uid)->find();
			$this->force_login($user);
		    }
		}

		return parent::logged_in($role);
	}
	
	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  completely destroy the session
	 * @param	boolean  remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
	    parent::logout($destroy, $logout_all);
	    FB::logout(Url::base(FALSE, TRUE));
	}
}
