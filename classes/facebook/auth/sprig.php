<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Auth Facebook Connect Driver w/ ORM fallback
 */
class Facebook_Auth_Sprig extends Auth_Sprig
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
//               if (FB::get_loggedin_user() == FALSE)
//               {
//                   $this->logout(TRUE, FALSE);
//                   return FALSE;
//               }
           
		if (isset($this->_user))// ? $this->user : $this->session->get($this->config['session_key']);
		{
		    // Let the parent handle roles...
		    return parent::logged_in($role);
		}
		else
		{
		    $user = $this->session->get($this->config['session_key']);
		    $user = ( ! isset($user)) ? $user : Sprig::factory('User', $user)->load();
		    if(is_object($user) AND $user->loaded())
		    {
			$this->_user = $user;
			return parent::logged_in($role);
		    }
		}

		// Attempt auto login
		if ($this->auto_login())
		{
		    // Success, get the user back out of the session
		    $user = $this->session->get($this->config['session_key']);
		    $user = ( ! isset($user)) ? $user : Sprig::factory('User', $user)->load();
		    if(is_object($user) AND $user->loaded())
		    {
			$this->_user = $user;
			return parent::logged_in($role);
		    }
		}

		if ($this->facebook_login($user) == FALSE) return FALSE;
		// Let the parent handle roles...
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

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username
	 * @return  boolean
	 */
	public function facebook_login()
	{
		if ($fb_uid = FB::get_loggedin_user())
		{
		    $user = Sprig::factory('User', array('fb_uid' => $fb_uid))->load();
		    if( ! $user->loaded())
		    {
			// New User...
			return FALSE;
		    }
			// Mark the session as forced, to prevent users from changing account information
			$this->session->set('auth_forced', TRUE);

			// Regenerate session_id
			$this->session->regenerate();

			// User info to store...
			$store = array('id' => $user->id, 'fb_uid' => $fb_uid);

			// Store user info in session
			$this->session->set($this->config['session_key'], $store);
			
			// Store user for later use...
			$this->_user = $user;

			// Update the number of logins
			$user->logins += 1;

			// Set the last login date
			$user->last_login = time();

			// Save the user
			$user->update();

			return TRUE;
		    
		}
		return FALSE;
	}

	/**
	 * Gets the currently logged in user from the session.
	 * Returns FALSE if no user is currently logged in.
	 *
	 * @return  mixed
	 */
	public function get_user()
	{
	    $user = parent::get_user();
	    if ($user !== FALSE) return $user;
	    if ($fb_uid = FB::get_loggedin_user())
	    {
		$user = Sprig::factory('User', array('fb_uid' => $fb_uid))->load();
		if(is_object($user) AND $user->loaded())
		{
		    $this->_user = $user;
		    return $this->_user;
		}
	    }
	    return FALSE;
	}
}
