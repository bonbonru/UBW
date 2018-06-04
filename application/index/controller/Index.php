<?php

namespace app\index\Controller;

use Common\Lib\Category;
use think\Db;

class Index extends Base {

	public function index() {

		$menu = Db::name('menu')->where(array('status' => 1))->order('sort,id')->select();
		if (empty($menu)) {
			$menu = array();
		}
		$qmenu = Db::name('menu')->where(array('status' => 1, 'quick' => 1))->order('sort,id')->select();
		if (empty($qmenu)) {
			$qmenu = array();
		}
		$menu_c = $qmenu_c = array();

		$adminFlag  = config('ADMIN_AUTH_KEY'); //超级管理员
		$authOnFlag = config('AUTH_CONFIG.AUTH_ON'); //权限开启

		//权限，是否开启验证且不是超级管理员
		if (!$adminFlag && $authOnFlag) {
			if (config('AUTH_CONFIG.AUTH_TYPE') == 1) {
				//时验证模式
			} else {
				$accessList = $this->request>server(['_AUTH_LIST_' . $this->uid. '1']); //
			}

			//auth的权限规则目录--只有登录验证有效，否则无值
			foreach ($menu as $key => $val) {
				if (!in_array(strtolower($val['url']), $accessList)) {
					unset($menu[$key]);
				}
			}
			//auth的权限规则目录--只有登录验证有效，否则无值
			foreach ($qmenu as $key => $val) {
				if (!in_array(strtolower($val['url']), $accessList)) {
					unset($qmenu[$key]);
				}
			}

		}
		$this->assign('menu', toLayer($menu));
		$this->assign('qmenu', $qmenu);
		
		return $this->fetch();		
		
	}

}
