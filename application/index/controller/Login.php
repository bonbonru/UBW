<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\facade\Session;
use Config;

class Login extends Controller{
    
    function index() {    
        
        return $this->fetch();
        
    }
    
    function Login() {
        
        $name = $this->request->post('username','','htmlspecialchars,rtrim');
        $password = $this->request->post('password','','htmlspecialchars,rtrim');
        $verify['captcha']  = $this->request->post('verify','','htmlspecialchars,rtrim');
        
        $re = $this->validate($verify,[
            'captcha|验证码'=>'require|captcha'
        ]);
        dump($re); exit;
        if($re !== true) {
            $this->error($re);
        }
        
        if(empty($name)){
            $this->error('用户名不能为空');
        } 
        if(empty($password)){
            $this->error('密码名不能为空');
        }
        $user = Db::name('admin')->where(['username'=>$name])->find();
        
        if(!$user || ($user['password'] != get_password($password,$user['encrypt']))){
            $this->error('用户名或密码不能为空');
        }        
        if ($user['is_lock']) {
            $this->error('用户被锁定！');
        }
        
        
        
        //更新数据库的参数
        $data = array('id' => $user['id'], //保存时会自动为此ID的更新
            'login_time'       => date('Y-m-d H:i:s'),
            //'login_ip'         => get_client_ip(),
            'login_num'        => $user['login_num'] + 1,
        );
        //更新数据库
        Db::name('admin')->update($data);
        $group_id = Db::name('AuthGroupAccess')->where('uid = '.$user['id'])->column('group_id');
        
        $group_id = implode(',', $group_id);
        
        //保存Session
        session::set('userid',$user['id']);
        session::set('yang_adm_username', $user['username']);
        session::set('yang_adm_realname', $user['realname']);
        session::set('yang_adm_username', $user['username']);
        session::set('yang_adm_department', $user['department']);
        session::set('yang_adm_group_id', $group_id);
        session::set('yang_adm_time', time());
        session::set('yang_adm_login_ip', $user['login_ip']);
        
        //跳转
        $this->success('登录成功', url('Index/index'));
        
    }
    
}