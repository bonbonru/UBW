<?php

namespace app\index\controller;

use think\Db;

class Student extends Base {
        
    protected  $sex = [1=>'男',0=>'女'];
    protected  $type = [1=>'一年级',2=>'二年级',3=>'三年级',4=>'四年级',5=>'五年级',6=>'六年级',7=>'初一',8=>'初二',9=>'初三'];
    
    public function index() {
        
        $keyword = $this->request->param('keyword','','htmlspecialchars,trim');
        $where = null;
        $s_db = Db::name('student');
        
        if(!empty($keyword)){
            $s_db->whereLike('s.id|s.name',"%$keyword%");
        }
        
        $list = $s_db->alias('s')->field('s.id,s.name,s.sex,s.age,s.add,s.type,s.guardian,s.number,c.name as c_name')
                                   ->join('__CLASS__ c','c.id = s.class_id') 
                                   ->where('s.status = 1')
                                   ->order('s.id desc')
                                   ->paginate(6);
        
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
            $this->addSave();
            exit;
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
        $data['pic'] = 0;
        $data['guardian'] = $this->request->post('guardian', '', 'htmlspecialchars,rtrim');
        $data['name'] = $this->request->post('name', '', 'htmlspecialchars,rtrim');
        $data['add'] = $this->request->post('add', '', 'htmlspecialchars,rtrim');
        
        if(empty($data['name'])){
            $this->error('名字不能为空');
        }
        if(empty($data['add'])){
            $this->error('住址不能为空');
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
            $key = $this->request->param('id','','intval');            
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
        if(empty($data['id'])){
            $this->error('参入错误');
        }
        $data['name'] = $this->request->post('name','','htmlspecialchars,rtrim');
        if(empty($data['name'])){
            $this->error('姓名不能为空');
        }
        $data['add'] = $this->request->post('add','','htmlspecialchars,rtrim');
        if(empty($data['add'])){
            $this->error('住址不能为空');
        }
        //dump($data); exit;
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
                                   ->join('__CLASS__ c','c.id = s.class_id') 
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
            $key = $this->request->param('id','','intval');            
        }
    
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
    
        if(Db::name('student')->where(['id'=>$key])->delete()){
            $this->success('已彻底删除',url('index'));
        } else {
            $this->error('删除失败,请重试');
        }
    }
    
    // 详细资料
    public function getInfo() {
        $id = $this->request->param('id',0,'intval');
        $s_db = Db::name('student');
        
        $where['s.id'] = $id;
        $where['sc.type'] = 1;
        $info =    $s_db->alias('s')
                        ->field('s.id , s.name, s.guardian , s.sex , s.age , s.pic , s.add , s.number ,c.name as c_name , t.name as t_name,  s.create_time , s.guardian , sc.english , sc.language , sc.math')
                        ->join('xyh_class c',' c.id = s.class_id')
                        ->join('xyh_teacher t',' c.teacher_id = t.id')
                        ->join('xyh_score sc',' s.id = sc.student_id')
                        ->where($where)
                        ->find();
        
        $info['create_time'] = date('Y-m-d',$info['create_time']);
        $info['sex'] = $this->sex[$info['sex']];
        
        $date = explode('-',$info['age']);
        $on = explode('-',date('Y-m-d',time()));
        $info['onAge'] = $on[0] - $date[0];
        if(($on[1]*100+$on[2])>($date[1]*100+$date[2])){
            $info['onAge']++;
        }
        
        $html = "<div class='container' style='width:100%;' >
        <div class='row'>
        <div class='col-md-3'>
        <label>姓名: {$info['name']}</label>
        </div>
        <div class='col-md-2 '>
        <label>性别: {$info['sex']}</label>
        </div>
        <div class='col-md-5 '>
        <label></label>
        </div>
        </div>
        <div class='row'>
        <div class='col-md-4 '>
        <label>日生日期: {$info['age']}</label>
        </div>
        <div class='col-md-3 '>
        <label>年龄: {$info['onAge']}</label>
        </div>
        </div>
        <div class='row'>
        <div class='col-md-3 '>
        <label>班级: {$info['c_name']}</label>
        </div>
        <div class='col-md-3 '>
        <label>班主任: {$info['t_name']}</label>
        </div>
        </div>
        <div class='row'>
        <div class='col-md-3'>
        <label>监护人:{$info['guardian']}</label>
        </div>
        <div class='col-md-4'>
        <label>电话 : {$info['number']}</label>
        </div>
        </div>
        <div class='row'>
        <div class='col-md-3 '>
        <label>住址: {$info['add']}</label>
        </div>
        <div class='col-md-4 '>
        <label>入学日期 : {$info['create_time']} </label>
        </div>
        </div>
        <div class='row'>
        <div class='col-md-3'>
        <label>英语成绩 : {$info['english']}</label>
        </div>
        <div class='col-md-3'>
        <label>数字成绩 : {$info['math']}</label>
        </div>
        <div class='col-md-3'>
        <label>语文成绩 : {$info['language']}</label>
        </div>
        </div>      ";
        
        echo $html;
        
    }
    
}