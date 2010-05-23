<?php defined('SYSPATH') OR die('No direct access allowed.');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Facebook API Example</title>
	<?php echo FB::feature_loader(); ?>
    </head>
    <body>
	<h1> Facebook Connect API Example </h1>
	<div id="comments_post">
	    <h3>Leave a comment:</h3>
	    <form method="POST">
		<div id="user">
		    <?php if (FB::get_loggedin_user()):?>
			<span>
			<?php echo FB::profile_pic(); ?>
			Welcome, <?php echo FB::name(array('useyou' => 'false')); ?>. You are signed in with your Facebook account.
			</span>
		    <?php else: ?>
			Name: <input name="name" size="27"/><br/>
			Or, you can <?php echo FB::login_button(array('onlogin' => 'update_user_box();', 'length' => 'long')); ?>
		    <?php endif; ?>
		</div>
		<textarea name="comment" rows="5" cols="30"></textarea><br />
		<input type="submit" value="Submit Comment"/>
	    </form>

	    <fb:comments title="Facbook Comments on my comments... lol"></fb:comments>
	</div>
	<script type="text/javascript">
	    function update_user_box() {
		var user_box = document.getElementById("user");
		// add in some XFBML. note that we set useyou=false so it doesn't display "you"
		user_box.innerHTML =
		    "<span>"
			+ '<?php echo FB::profile_pic(); ?>'
			+ 'Welcome, <?php echo FB::name(array('useyou' => 'false')); ?>. You are signed in with your Facebook account.'
			+ "</span>";
		// because this is XFBML, we need to tell Facebook to re-process the document
		FB.XFBML.Host.parseDomTree();
	    }
	    <?php echo FB::js_init(); // To do it with javascript = FB::js_init(array('ifUserConnected' => 'update_user_box')); ?>
	</script>
    </body>
</html>
