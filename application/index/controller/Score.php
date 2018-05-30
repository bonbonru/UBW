<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Response;

class Score extends Base{
    
    protected $type = [ 1 => "普通测试" , 2 => "期中考试" , 3 => "期末考试" ];
    
    // 列表首页
    public function index() {
    
        $searClass = $this->request->post('searClass',0,'intval');
        $keyword = $this->request->post('keyword', '', 'htmlspecialchars,trim');
    
        $sc_db = Db::name('score');
        if($this->request->isPost()){
            $sc_db->whereLike('sc.id|s.name',"%{$keyword}%");
        }
        empty($searClass) ? : $sc_db -> where('sc.class_id = '.$searClass);
    
        $list = $sc_db  ->alias('sc')
                        ->join('__STUDENT__ s ','s.id = sc.student_id')
                        ->join('__CLASS__ c ','c.id = s.class_id')
                        ->field('sc.id,s.name,c.name as c_name,sc.english,sc.language,sc.math,sc.create_time,sc.type')
                        ->where('sc.status = 1')
                        ->order('id desc')
                        ->paginate(5);
                        
        $class = Db::name('class')->field('id,name')->select();
    
        if(!empty($searClass)){
            $classCount = Db::name('score')->alias('sc')
                                ->join('__STUDENT__ s ',' s.id = sc.student_id')
                                ->join('__CLASS__ c ',' c.id = s.class_id')
                                ->field('avg(sc.english) as english,avg(sc.math) as math,avg(sc.language) as language,c.name,count(*) as count')
                                ->where('sc.class_id = '.$searClass)
                                ->find();   
            $this->assign('classCount',$classCount);
        }
    
        $this->assign('list',$list);
        $this->assign('class',$class);
        $this->assign('searClass',$searClass);
        $this->assign('title','成绩资料');
        $this->assign('type',$this->type);
        $this->assign('keyword',$keyword);
    
        return $this->fetch();
    }
    
    // 添加页面
    public function add() {
        
        if ($this->request->isPost()) {        
            $this->addSave();
            exit();
        }
        
        $list = DB::name('class')->select();
    
        $this->assign('list',$list);
        $this->assign('type',$this->type);
        $this->assign('title','添加成绩');
        
        return $this->fetch();
    }
    
    // 添加处理
    public function addSave(){
    
        $data = $this->request->param();
        $data['status'] = 1;
        $data['id'] = $this->request->param('id',0,'intval');
        $data['student_id'] = $this->request->param('student_id',0,'intval');
        if(empty($data['student_id'])){
            $this->error('请选择学生');
        }
        $data['language'] = $this->request->param('language',0,'intval');
        $data['math'] = $this->request->param('math',0,'intval');
        $data['english'] = $this->request->param('english',0,'intval');
        if(($data['language']==0 or $data['english'] == 0 or $data['math'] == 0) or ($data['language']>100 or $data['english']>100 or $data['math']>100 )){
            $this->error('各科成绩不能为空 ,而且输入0-100数字');
        }
    
        $re = Db::name('score')->insert($data);
    
        if($re){
            $this->success('已成功添加新资料',url('index'));
        } else {
            $this->error('添加失败请重试');
        }
    
    }
    
    
    
    // 编辑页面
    public function edit() {
    
        if($this->request->isPost()){
            $this->update();
            exit;
        }
        
        $id = $this->request->param('id',0,'intval');
        $score = DB::name('score')->find($id);
    
        $score = DB::name('score')->alias('sc')
                            ->field('c.name as c_name,s.type,sc.create_time,s.id as s_id,s.name as s_name,sc.language,sc.english,sc.math,c.id as c_id,sc.id')
                            ->join('__CLASS__ c ',' c.id = '.$score['class_id'])
                            ->join('__STUDENT__ s ',' s.id = '.$score['student_id'])
                            ->where('sc.id = '.$id)
                            ->find();
    
        $class = Db::name('class')->field('id,name')->where('status = 1')->select();
        $student = Db::name('student')->field('id,name')->where(['status'=>1,'class_id'=>$score['c_id']])->select();
    
        $this->assign('type',$this->type);
        $this->assign('class',$class);
        $this->assign('student',$student);
        $this->assign('title','修改成绩');
        $this->assign('score',$score);
        
        return $this->fetch();
        
    }
    
    // 编辑处理
    public function update() {
    
        $data = $this->request->param();
        $data['id'] = $this->request->param('id',0,'intval');
        $data['student_id'] = $this->request->param('student_id',0,'intval');
        if(empty($data['student_id'])){
            $this->error('请选择学生');
        }
        $data['language'] = $this->request->param('language',0,'intval');
        $data['math'] = $this->request->param('math',0,'intval');
        $data['english'] = $this->request->param('english',0,'intval');
        if(($data['language']==0 or $data['english'] == 0 or $data['math'] == 0) or ($data['language']>100 or $data['english']>100 or $data['math']>100 )){
            $this->error('各科成绩不能为空 ,而且输入0-100数字');
        }
    
        $re = Db::name("score")->update($data);
        
        if(false !== $re){
            $this->success('已成功修改',url('index'));
        } else {
            $this->error('操作失败请重试');
        }
    
    }
    
    
    // 回收站页面
    public function trach() {
    
        $list = Db::name('score')->alias('sc')
                                ->join('__STUDENT__ s ',' s.id = sc.student_id')
                                ->join('__CLASS__ c ',' c.id = s.class_id')
                                ->field('sc.id,s.name,c.name as c_name,sc.english,sc.language,sc.math,sc.create_time,sc.type')
                                ->where('sc.status = 0')
                                ->order('id desc')
                                ->paginate(5);
           
        $this->assign('list',$list);
        $this->assign('title','成绩回收站');
        $this->assign('type',$this->type);
    
        return $this->fetch('index');
    
    }
    
    // 无状态删除
    public function del() {
    
        if($this->request->isPost()){
            $key = $this->request->post();
            $key = $key['key'];
        } else {
            $key = $this->request->param('id','','intval');
        }
    
        if(Db::name('score')->where(['id'=>$key])->update(['status' => 0])){
            $this->success('已成功删除到回收站');
        } else {
            $this->error('删除失败,请重试');
        }
    }
    
    // 还原状态
    public function restore() {
        
        if($this->request->isPost()){
            $key = $this->request->post();
            $key = $key['key'];
        } else {
            $key = $this->request->param('id','','intval');            
        }
    
        if(Db::name('score')->where(['id'=>$key])->update(['status'=>1])){
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
    
        if(Db::name('score')->where(['id'=>$key])->delete()){
            $this->success('已彻底删除',url('index'));
        } else {
            $this->error('删除失败,请重试');
        }
    }
    
    // 添加页面选择学生
    public function getStudent() {
        $where['class_id'] = $this->request->param('id',0,'intval');
        $where['status'] = 1;
        
        $result = ['code'=>0, 'msg'=>'请求失败,请重试', 'data'=>false];
        $result['data'] = Db::name("student")->field('id,name')->where($where)->select();
        
        if($result['data'] === false){
            return json($result);
        } else {
            $result['code'] = 1;
            $result['msg'] = "已成功返回数据";
            return json($result);
        }
    
    }
    
    
    
    
    
}