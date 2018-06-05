<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\validate;

class Grade extends Base{
    
    protected $type = [1=>'小学',2=>'初中'];
    
    public function index() {
        
        $keyword = $this->request->param('keyword','','htmlspecialchars,rtrim');
        
        $db = Db::name('class');
        
        empty($keyword) ? : $db->whereLike('id|name',"%{$keyword}%");     
        
        $list = $db->where('status = 1')->order('id desc')->paginate(5,false,['query'=>['keyword'=>$keyword]]);
               
        $str = [];
        foreach($list as $k=>$v){
            $str[] = $v['teacher_id'];
            $str[] = $v['math_id'];
            $str[] = $v['language_id'];
            $str[] = $v['english_id'];     
        }
        
        $teacher = Db::name('teacher')->field('id,name')->where(['id'=>$str])->select();
        
        $t_name = '';
        foreach($teacher as $v){
            $t_name[$v['id']] = $v['name']; 
        }
        
        $this->assign('t_name',$t_name);
        $this->assign('keyword',$keyword);
        $this->assign('title','班级资料');
        $this->assign('type',$this->type);
        $this->assign('list',$list);
        
        return $this->fetch();
        
    }
    
    // 编辑页面
    public function edit() {
        
        if($this->request->isPost()){
            return $this->update();
            
        }
        $id = $this->request->param('id',0,'intval');
        
        $id || $this->error('参数异常');
        
        $class = Db::name('class')->find($id);
        $list = Db::name('teacher')->select();
    
        $this->assign('title','班级编辑');
        $this->assign('list',$list);
        $this->assign('class',$class);
        
        return $this->fetch();
    
    }
    
    // 添加页面
    public function add() {
       
        if($this->request->isPost()){
            return $this->addSave();
        }
        
        $list = Db::name('teacher')->select();
        $this->assign('list',$list);
        $this->assign('title','添加班级');
        
        return $this->fetch();
    }
    
    // 回收站
    public function trach() {
        
        $db = Db::name('class');
    
        $list = $db->where('status = 0')->order('id desc')->paginate(5);
        
        $in = [];
        foreach($list as $k=>$v) {
            $in[] = $v['teacher_id'];
            $in[] = $v['math_id'];
            $in[] = $v['english_id'];
            $in[] = $v['language_id'];
        }
        $name = Db::name('teacher')->field('id,name')->where(['id'=>$in])->select();
        
        $t_name = '';
        foreach ($name as $k=>$v){
            $t_name[$v['id']] = $v['name'];
        }
        
        $this->assign('t_name',$t_name);
        $this->assign('title','班级回收站');
        $this->assign('type',$this->type);
        $this->assign('list',$list);
        
        return $this->fetch('index');
        
    }
    
    
    // 编辑处理
    public function update() {
        $data = $this->request->post();
        $data['id'] = $this->request->post('id',0,'intval');
        $data['create_time'] = strtotime($data['create_time']);
        
        $re = Validate::make();
        
        $re->rule(['name'=>'require|max:6'])->message(['name.require'=>'班级名不能为空','name.max'=>'班级名长度不能超过6位']);
        
        if(!$re->check($data)){
            $this->error($re->getError());
        }
        
        if(empty($this->request->post('pic'))){
            unset($data['pic']);
        }
        if(db::name('class')->update($data) !== false ){
            $this->success('已成功更改',url('index'));
        } else {
            $this->error('操作失败,请重试');
        }
    }
    
    // 添加处理
    public function addSave() {
        $data['status'] = 1;
        $data = $this->request->post();
        $data['pic'] = $this->request->post('pic', '');
        $data['create_time'] = strtotime($data['create_time']);
        
        if(empty($data['teacher_id']) || empty($data['language_id']) || empty($data['english_id']) || empty($data['math_id']) ){
            $this->error('有科目教师未选择');
        }
        
        $re = Validate::make([ 'name' => 'require|max:6' ],
                             [ 'name.require' =>'班级名不能为空','name.max'=>'班级名长度不能超过6'] );
        
        if(!$re->check($data)){
            $this->error($re->getError());
        }
        
        if(DB::name('class')->insert($data)) {
            $this->success('已成功添加班级',url('index'));
        } else {
            $this->error('添加失败,请重试');
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
        
        if(Db::name('class')->where(['id'=>$key])->update(['status' => 0])){
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
        
        $this->error('参数异常');
        
        if(Db::name('class')->where(['id'=>$key])->update(['status'=>1])){
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
        
        if(Db::name('class')->where(['id'=>$key])->delete()){
            $this->success('已彻底删除',url('index'));
        } else {
            $this->error('删除失败,请重试');
        }
        
    }
    
    // 详细资料
    public function getInfo() {
        
        $re = ['status'=>1,'msg'=>'成功','data'=>[]];
        $id = $this->request->post('id',0,'intval');
        
        if(empty($id)){
            $re['status'] = 203;
            $re['msg'] = '参数异常';
            return json($re);
        }
        
        $t_db = Db::name('teacher');
        $info = Db::name('class')->find($id);
        
        $info['teacher'] =  $t_db->field('name,number')->find($info['teacher_id']);
        $info['math'] =  $t_db->field('name')->find($info['math_id']);
        $info['language'] =  $t_db->field('name')->find($info['language_id']);
        $info['english'] =  $t_db->field('name')->find($info['english_id']);
        
        $info['create_time'] = date('Y-m-d',$info['create_time']);
        $info['grade'] = $this->type[$info['grade']];
        
        $avg['math'] = round(Db::name('score')->where('class_id = '.$id)->avg('math'));
        $avg['language'] = round(Db::name('score')->where('class_id = '.$id)->avg('language'));
        $avg['english'] = round(Db::name('score')->where('class_id = '.$id)->avg('english'));
        $re['data'] = $info;
        $re['avg'] = $avg;
        
        return json($re);
        
    }
    
}