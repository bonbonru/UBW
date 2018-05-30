<?php

namespace app\index\controller;

use think\Controller;

class Base extends Controller {
                    
	public function initialize() {
	    
		if (!session('userid')) {
			$this->redirect('Login/index', '', 0, '页面跳转中...');
			exit();
		}   
		
    }
    
}