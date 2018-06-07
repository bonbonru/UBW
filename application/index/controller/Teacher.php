<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use app\index\validate\Teacher as TeacherValidate;
use app\index\model\Teacher as TeacherModel;
use app\index\model\Grade;

class Teacher extends Base {
    
    protected $sex = [ 1=>'男', 0=> '女'];
    
    
    public function index() {
        
        $keyword = $this->request->param('keyword','','htmlspecialchars,rtrim') ;
        
        $db = Db::name('teacher');
        
        $list = TeacherModel::Search($keyword)->order('id desc')->paginate(5,false,[ 'query'=>['keyword'=>$keyword] ]);
        
        $this->assign('keyword',$keyword);
        $this->assign('list',$list);
        $this->assign('title','教师资料');
        $this->assign('info','老师详情');
    
        return $this->fetch();
    
    }
    
    // 编辑页面
    public function edit() {
        
        if($this->request->isPost()){
            return $this->update();
        }
        
        $id = $this->request->param('id',0,'intval');
        
        $id || $this->error('参数异常');
        
        $teacher = TeacherModel::get($id);
        
        echo $teacher->getData('sex');
        
        if(!$teacher){
            $this->error('页面错误');
        }
         
        $this->assign('teacher',$teacher);
        $this->assign('title','教师信息编辑');
        
        return $this->fetch();
    }
    
    // 编辑处理
    public function update(){
        
        $data = $this->request->post();
        $data['create_time'] = strtotime($data['create_time']);
        $data['id'] = $this->request->post('id', 0, 'intval');
        
        $vali = new TeacherValidate;
        if(!$vali->scene('teacher')->check($data)){
            $this->error($vali->getError());
        }
        if(empty($this->request->post('pic'))){
            unset($data['pic']);
        }
        
        $re = TeacherModel::update($data);
        if($re) {
            $this->success('已操作成功',url('index'));
        } else {
            $this->error('操作失败请重试');
        }
    
    }
    
    
    // 添加页面
    public function add() {
    
        if($this->request->isPost()){
            return $this->addSave();
        }
    
        return $this->fetch();
    
    }
    
    // 添加处理
    public function addSave() {
        
        $data = $this->request->post();
        $data['create_time'] = strtotime($data['create_time']);
        $data['pic'] = $this->request->post('pic','');
        
        $validater = new TeacherValidate;        
        if (!$validater->scene('teacher')->check($data)) {
            $this->error($validater->getError());
        }
        $re = TeacherModel::create($data);
        
        if($re) {
            $this->success('已成功添加教师资料',url('index'));
        } else {
            $this->error('添加资料失败,请重试');
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
        
        $key || $this->error('参数异常');
        
        $re = TeacherModel::where([ 'id'=>$key ])->update([ 'status'=>0 ]);
        if($re){
            $this->success('已成功删除到回收站');
        } else {
            $this->error('删除失败,请重试');
        }
    }
    
    
    // 回收站页面
    public function trach() {
        
        $db = Db::name('teacher');
        
        $list = TeacherModel::useGlobalScope(false)->where('status = 0')->order('id desc')->paginate(5);
    
        $this->assign('list',$list);
        $this->assign('title','教师回收站');
    
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
        
        $key || $this->error('参数异常');
        
        $re = TeacherModel::useGlobalScope(false)->where( [ 'id'=>$key ])->update( [ 'status'=>1 ]);
        if($re){
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
        $re = TeacherModel::useGlobalScope(false)->delete($key);
        if($re){
            $this->success('已彻底删除',url('index'));
        } else {
            $this->error('删除失败,请重试');
        }
    
    }
    
    // 获取详细信息
    public function getInfo() {
        $re = ['status'=>1,'msg'=>'成功','data'=>[]];
        
        $id = $this->request->param('id',41,'intval');
        
        if(empty($id)){
            $re['status'] = 203;
            $re['msg'] = '参数异常';
            return json($re);
        }
        
        $info = Db::name('teacher')->find($id);
        
        $info = TeacherModel::get($id);
        
        $className = Grade::where('teacher_id|english_id|language_id|math_id',$id)
                          ->field('id,name,teacher_id,language_id,math_id,english_id')
                          ->select();
        $array = [];    
        foreach ($className as $v) {
            if($v['teacher_id'] == 41){
                $array['teacher_id'][$v['id']] = $v['name']; 
            }
            if($v['english_id'] == 41){
                $array['english_id'][$v['id']] = $v['name'];
            }
            if($v['language_id'] == 41){
                $array['language_id'][$v['id']] = $v['name'];
            }
            if($v['teacher_id'] == 41){
                $array['math_id'][$v['id']] = $v['name'];
            }
        }
        
        
        dump($array); 
        $c_db = Db::name('class');
        $for['teacher'] = $c_db->field('name')->where('teacher_id = '.$id)->select();
        $for['english'] = $c_db->field('id , name')->where('english_id = '.$id)->select();
        $for['language'] = $c_db->field('id , name')->where('language_id = '.$id)->select();
        $for['math']= $c_db->field('id , name')->where('math_id = '.$id)->select();
    
        $info['create_time'] = date('Y-m-d',$info['create_time']);
        $info['sex'] = $this->sex[$info['sex']];
    
        $date = explode('-',$info['age']);
        $on = explode('-',date('Y-m-d',time()));
        $info['onAge'] = $on[0] - $date[0];
        if(($on[1]*100+$on[2])>($date[1]*100+$date[2])){
            $info['onAge']++;
        }
        dump($for); exit;
        $re['data'] = $info;
        $re['for'] = $for;
        
        return json($re);
    }
    
    // 图片上传
    public function uplode() {
        $file = $this->request->file('pic');
        $result = ['code'=>0,'msg'=>'上传失败','data'=>[]];
        if($file){
            $info = $file->move(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'uplode'.DIRECTORY_SEPARATOR);
            $img['title'] = $info->getExtension();
            $img['file_path'] = str_replace("\\","/",$info->getSaveName());
            $img['title'] =  $info->getFilename();
            $img['file_size'] = $info->getSize();
            $img['file_type'] = 1 ;
            $img['has_litpic'] = 1;
            $img['upload_time'] = date('Y-m-d H:i:s',time());
            $img['aid'] = 1;
            $img['user_id'] = 0;
            $id = Db::name('attachment')->insertGetId($img);
            if($id){
                $result['code'] = 1;
                $result['msg'] = '已成功上传图片';
                $result['data']['file_path'] = $img['file_path'];
                $result['data']['id'] = $id;
                return json($result);
            } else {
                $result['msg'] = '插入失败';
                $result['data']['id'] = $id;
                return json($result);
            }
        }else{
            return json($result);
        }
    }
    
    
   
    
    
    
}