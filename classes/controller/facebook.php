<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Facebook Controller Class
 */
class Controller_Facebook extends Controller {

    public function action_xdreceiver() {
	$this->request->response = View::factory('xdreceiver');
    }

    public function action_index(){
	$this->request->response = View::factory('facebook');
    }
}