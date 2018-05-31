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
				$accessList = request()->server(['_AUTH_LIST_' . $this->uid. '1']); //
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

	public function getParentCate() {
		header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
		$count = Db::name('CategoryView')->where(array('pid' => 0, 'type' => 0))->count();
		$list  = Db::name('CategoryView')->nofield('content')->where(array('pid' => 0, 'type' => 0))->order('category.sort,category.id')->select();
		if (empty($list)) {
			$list = array();
		}
		//权限检测
		$checkflag = true;    
		if (empty(session(config('ADMIN_AUTH_KEY')))) {
			$checkaccess = Db::name('categoryAccess')->distinct(true)
			                      ->where([['floa','=',1],['role_id','in',session('yang_adm_group_id')]])
			                      ->column('cat_id');
		} else {
			$checkflag = false;
		}
		if (empty($checkaccess)) {
			$checkaccess = array();
		}

		$menudoclist = array('count' => $count);
		foreach ($list as $v) {
			if (!$checkflag || in_array($v['id'], $checkaccess)) {
				$menudoclist['list'][] = array(
					'id'   => $v['id'],
					'name' => $v['name'],
					'url'  => url(ucfirst($v['table_name']) . '/index', array('pid' => $v['id'])),
				);
			}
		}
		exit(json_encode($menudoclist));
	}
    
	
	
}
