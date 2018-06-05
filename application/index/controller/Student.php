<?php

namespace app\index\controller;

use think\Db;
use think\validate;

class Student extends Base {
        
    protected  $sex = [1=>'男',0=>'女'];
    protected  $type = [1=>'一年级',2=>'二年级',3=>'三年级',4=>'四年级',5=>'五年级',6=>'六年级',7=>'初一',8=>'初二',9=>'初三'];
    
    public function index() {
        
        $keyword =  $this->request->param('keyword','','htmlspecialchars,rtrim');
        
        $s_db = Db::name('student');
        
        if(!empty($keyword)){
            $s_db->whereLike('s.id|s.name',"%$keyword%");
        }
        
        $list = $s_db->alias('s')->field('s.id,s.name,s.sex,s.age,s.add,s.type,s.guardian,s.number,c.name as c_name')
                                ->join('class c','c.id = s.class_id')
                                ->where('s.status = 1')
                                ->order('s.id desc')
                                ->paginate(6,false,['query'=>['keyword'=>$keyword]]);
        
        $this->assign('sex',$this->sex);
        $this->assign('type',$this->type);
        $this->assign('list',$list);
        $this->assign('title','学生资料');
        $this->assign('keyword',$keyword);
        
        return $this->fetch();
        
    }
    
    // 添加页面
    public function add() {
        
        if($this->request->isPost()){
            return $this->addSave();            
        }
        
        $list = Db::name('class')->select();
        $this->assign('list',$list);
        $this->assign('title','添加学生');
        
        return $this->fetch();
    }
    
    // 添加处理
    public function addSave(){
        
        $data = $this->request->post();
        
        $data['status'] = 1;
        
        $rule = [
            'name' => 'require|max:5',
            'guardian' => 'max:5',
            'number' => 'require|mobile',
            'add' => 'max:20'
        ];
        
        $message = [
            'name.require' => '名字必需填写',
            'name.max' => '名字的长度不能超过5位',
            'guardian.max' => '监护人的长度不能超过5位',
            'number.require' => '电话需要填写',
            'number.mobile' => '电话格式不正确',
            'add.max' => '地址不能超过20位'
        ];
        
        $validate = Validate::make($rule,$message);
        $validate -> check($data);
        
        if(true){
           $this->error($validate->getError());
        }
        
        $re = Db::name('student')->insert($data);
    
        if($re){
            $this->success('已成功添加新资料',url('index'));
        } else {
            $this->error('添加失败请重试');
        }
    }
    
    // 无状态删除
    public function del() {
        
        if($this->request->isPost()){
            $key = $this->request->post();
            $key = $key['key'];
        } else {
            $key = $this->request->param('id',0,'intval');       
        }
        
        if(empty($key)){
            $this->error('参数异常');
        }
        
        if(Db::name('student')->where(['id'=>$key])->update(['status' => 0])){
            $this->success('已成功删除到回收站');
        } else {
            $this->error('删除失败,请重试');
        }
    }
    
    // 编辑页面
    public function edit() {
    
        if($this->request->isPost()){
            $this->update();
            exit;
        }
        
        $id = $this->request->param('id',0,'intval');
        
        $id || $this->error('参数异常');
        
        $student = Db::name('student')->find($id);
        $class = Db::name('class')->select();
        
        $this->assign('title','编辑页面');
        $this->assign('class',$class);
        $this->assign('student',$student);
        
        return $this->fetch();
    }
    
    // 编辑处理
    public function update() {
    
        $data = $this->request->post();
        $data['id'] = $this->request->post('id',0,'intval');       
        
        $rule = [
            'name|名字' => 'require|max:5',
            'guardian|监护人' => 'max:5',
            'number|电话' => 'require|mobile',
            'add|住址' => 'max:20'
        ];
        
        $vali = validate::make()->rule($rule);
       
        if(!$vali->check($data)){
            $this->error($vali->getError());
        }
        
        if(empty($data['pic'])){
            unset($data['pic']);
        }
        
        $re = Db::name("student")->update($data);
    
        if(false !== $re){
            $this->success('已成功修改',url('index'));
        } else {
            $this->error('操作失败请重试');
        }
    
    }
    
    
    // 回收站页面
    public function trach() {
    
        $list = Db::name("student")->alias('s')->field('s.id,s.name,s.sex,s.age,s.add,s.type,s.guardian,s.number,c.name as c_name')
                                   ->join('class c','c.id = s.class_id') 
                                   ->where('s.status = 0')
                                   ->order('s.id desc')
                                   ->paginate(6);
        
        $this->assign('sex',$this->sex);
        $this->assign('list',$list);
        $this->assign('title','学生回收站');
        $this->assign('type',$this->type);
    
        return $this->fetch('index');
    
    }
    
    // 还原状态
    public function restore() {
        
        if($this->request->isPost()){
            $key = $this->request->post();
            $key = $key['key'];
        } else {
            $key = $this->request->param('id',0,'intval');            
        }
        
        $key || $this->error('参数异常');
        
        if(Db::name('student')->where(['id'=>$key])->update(['status'=>1])){
            $this->success('已成功还原',url('index'));
        } else {
            $this->error('还原失败,请重试');
            
        }
    }
    
    // 彻底删除
    public function clear() {
        
        if($this->request->isPost()){
                $key = $this->request->post();
                $key = $key['key'];
        } else {
            $key = $this->request->param('id','','intval');            
        }
        
        $key || $this->error('参数异常');
    
        if(Db::name('student')->where(['id'=>$key])->delete()){
            $this->success('已彻底删除',url('index'));
        } else {
            $this->error('删除失败,请重试');
        }
    }
    
    // 详细资料
    public function getInfo() {
        $result = ['status'=>0,'msg'=>'请求失败','data'=>[]];
        $id = $this->request->param('id',0,'intval');
        
        if(empty($id)){
            $result['status'] = 203;
            $result['msg'] = '参数异常';
            return json($result);
        }
        
        $s_db = Db::name('student');
        
        $where['s.id'] = $id;
        
        $info =    $s_db->alias('s')
                        ->field('s.id , s.name, s.guardian , s.sex , s.age , s.pic , s.add , s.number ,c.name as c_name , t.name as t_name,  s.create_time , s.guardian , sc.english , sc.language , sc.math')
                        ->leftJoin('class c',' c.id = s.class_id')
                        ->leftJoin('teacher t',' c.teacher_id = t.id')
                        ->leftJoin('score sc',' (s.id = sc.student_id and sc.status = 1)')
                        ->where($where)
                        ->find();
        
        if(!$info){
            return json($result);
        }
        
        $info['sex'] = $this->sex[$info['sex']];
        
        $date = explode('-',$info['age']);
        $on = explode('-',date('Y-m-d',time()));
        $info['onAge'] = $on[0] - $date[0];
        if(($on[1]*100+$on[2])>($date[1]*100+$date[2])){
            $info['onAge']++;
        }
        $result = ['status'=>1,'msg'=>'成功','data'=>$info];
        return json($result);
        
    }
    
    
    
    
}