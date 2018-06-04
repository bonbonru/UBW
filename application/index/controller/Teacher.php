<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Teacher extends Base {
    
    protected $sex = [ 1=>'男', 0=> '女'];
    
    
    public function index() {
        $where['status'] = 1;
        $keyword = $this->request->post('keyword','','htmlspecialchars,rtrim');
        
        $db = Db::name('teacher');
        empty($keyword) ? "" : $db->whereLike('id|name',"%$keyword%");;
               
        $list = $db->where('status = 1')->order('id desc')->paginate(7);
    
        $this->assign('keyword',$keyword);
        $this->assign('sex',$this->sex);
        $this->assign('list',$list);
        $this->assign('title','教师资料');
        $this->assign('info','老师详情');
    
        return $this->fetch();
    
    }
    
    // 编辑页面
    public function edit($id) {
    
        if($this->request->isPost()){
            $this->update();
            exit;
        }
        $teacher = Db::name('teacher')->find($id);
         
        $this->assign('teacher',$teacher);
        $this->assign('title','教师信息编辑');
        
        return $this->fetch();
    }
    
    // 编辑处理
    public function update(){
        $data = $this->request->post();
        $data['create_time'] = strtotime($data['create_time']);
        $data['id'] = $this->request->post('id', 0, 'intval');
        $data['name'] = $this->request->post('name','','htmlspecialchars,rtrim');
        $data['pic'] =  $this->request->post('pic','');
        if(empty($data['name'])){
            $this->error('教师名不能为空');
        }
        $data['specialty'] = $this->request->post('specialty','','htmlspecialchars,rtrim');
        if(empty($data['specialty'])){
            $this->error('专业不能为空');
        }
        if(empty($this->request->post('pic'))){
            unset($data['pic']);
        }
        $re = Db::name('teacher')->update($data);
        if(false !== $re) {
            $this->success('已操作成功',url('index'));
        } else {
            $this->error('操作失败请重试');
        }
    
    }
    
    
    // 添加页面
    public function add() {
    
        if($this->request->isPost()){
            $this->addSave();
            exit;
        }
    
        return $this->fetch();
    
    }
    
    // 添加处理
    public function addSave() {
        
        $data = $this->request->post();
        $data['id'] = $this->request->post('id',0,'intval');
        $data['create_time'] = strtotime($data['create_time']);
        $data['name'] = $this->request->post('name','','htmlspecialchars,rtrim');
        $data['status'] = 1;
        if(empty($data['name'])){
            $this->error('教师名不能为空');
        }
        $data['specialty'] = $this->request->post('specialty','','htmlspecialchars,rtrim');
        if(empty($data['specialty'])){
            $this->error('专业不能为空');
        }
        $data['number'] = $this->request->post('number','','htmlspecialchars,rtrim');
        if(empty($data['number'])){
            $this->error('电话不能为空');
        }
        $data['pic'] = $this->request->post('pic','');
        
        if(Db::name('teacher')->insert($data)) {
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
    
        if(Db::name('teacher')->where(['id'=>$key])->update(['status' => 0])){
            $this->success('已成功删除到回收站');
        } else {
            $this->error('删除失败,请重试');
        }
    }
    
    
    // 回收站页面
    public function trach() {
        
        $db = Db::name('teacher');
        
        $list = $db->where('status = 0')->order('id desc')->paginate(7);
    
        $this->assign('sex',$this->sex);
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
    
        if(Db::name('teacher')->where(['id'=>$key])->update(['status'=>1])){
            $this->success('已成功还原',url('index'));
        } else {
            $this->error('还原失败,请重试');
        }
        
    }
    
    
    // 获取详细信息
    public function getInfo() {
        $re = ['status'=>1,'msg'=>'成功','data'=>[]];
        
        $id = $this->request->param('id',0,'intval');
        $info = Db::name('teacher')->find($id);
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
        
        $re['data'] = $info;
        $re['for'] = $for;
        
        return json($re);
        
    }
    
    
    // 彻底删除
    public function clear() {
    
        if($this->request->isPost()){
            $key = $this->request->post();
            $key = $key['key'];
        } else {
            $key = $this->request->param('id','','intval');
        }
    
        if(Db::name('teacher')->where(['id'=>$key])->delete()){
            $this->success('已彻底删除',url('index'));
        } else {
            $this->error('删除失败,请重试');
        }
        
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