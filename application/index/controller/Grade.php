<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Grade extends Base{
    
    protected $type = [1=>'小学',2=>'初中'];
    
    public function index() {
        $keyword = $this->request->post('keyword', '', 'htmlspecialchars,trim');
        
        $db = Db::name('class');
        empty($keyword) ? : $db->whereLike('id|name',"%{$keyword}%");     
        
        
        $list = $db->where('status = 1')->order('id desc')->paginate(5);
               
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
            $this->update();
            exit;
        }
        $id = $this->request->param('id',0,'intval');
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
            $this->addSave();
            exit;
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
        
        foreach($list as $k=>$v) {
            $in[] = $v['teacher_id'];
            $in[] = $v['math_id'];
            $in[] = $v['english_id'];
            $in[] = $v['language_id'];
        }
        $name = Db::name('teacher')->field('id,name')->where(['id'=>$in])->select();
        
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
        $data['name'] = $this->request->post('name','','htmlspecialchars,rtrim');
        if(empty($data['name'])){
           $this->erorr('班级名称不能为空'); 
        }
        $data['create_time'] = strtotime($data['create_time']);
        if(db::name('class')->update($data) !== false ){
            $this->success('已成功更改',url('index'));
        } else {
            $this->error('操作失败,请重试');
        }
    }
    
    // 添加处理
    public function addSave() {
        $data = $this->request->post();
        $data['name'] = $this->request->post('name', '', 'htmlspecialchars,rtrim');
        if(empty($data['name'])){
            $this->error('班级名不能为空');
        }
        $data['create_time'] = strtotime($data['create_time']);        
        
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
    
        if(Db::name('class')->where(['id'=>$key])->delete()){
            $this->success('已彻底删除',url('index'));
        } else {
            $this->error('删除失败,请重试');
        }
        
    }
    
    // 详细资料
    public function getInfo() {
        $id = $this->request->post('id',0,'intval');
        $t_db = Db::name('teacher');
        $info = Db::name('class')->find($id);
        
        $info['teacher'] =  $t_db->field('name,number')->find($info['teacher_id']);
        $info['language'] =  $t_db->field('name')->find($info['language_id']);
        $info['math'] =  $t_db->field('name')->find($info['math_id']);
        $info['english'] =  $t_db->field('name')->find($info['english_id']);
        
        $info['create_time'] = date('Y-m-d',$info['create_time']);
        $info['grade'] = $this->type[$info['grade']];
        
        $avg['math'] = round(Db::name('score')->where('class_id = '.$id)->avg('math'));
        $avg['language'] = round(Db::name('score')->where('class_id = '.$id)->avg('language'));
        $avg['english'] = round(Db::name('score')->where('class_id = '.$id)->avg('english'));
        
        $html = "<div class='container' style='width:100%;' >
                	<div class='row'>
                		<div class='col-md-2'>
                			<label>编号: {$info['id']}</label>
                		</div>
                		<div class='col-md-3 '>
                			<label>班级名: {$info['name']}</label>
                		</div>
                		<div class='col-md-3 '>
                			<label>学级: {$info['grade']}</label>
                		</div>	               
                		<div class='col-md-4 '>
                			<label></label>
                		</div> 		                         	
                	</div>	
					<div class='row'>
                		<div class='col-md-3 '>
                			<label>班主任: {$info['teacher']['name']}</label>
                		</div>
                		<div class='col-md-4'>
                			<label>电话 : {$info['teacher']['number']}</label>
                		</div>                		
                	</div>
                	<div class='row'>                		
                		<div class='col-md-4 '>
                			<label>入学日期 : {$info['create_time']} </label>
                		</div>                		
                	</div>
                	<div class='row'>
                		<div class='col-md-4'>
                			<label>语文老师: {$info['language']['name']}</label>
                		</div>
                		<div class='col-md-4'>
                			<label>语文平均成绩 : {$avg['language']}</label>
                		</div>	 	                		
                	</div>
                	<div class='row'>
                		<div class='col-md-4'>
                			<label>数学老师: {$info['math']['name']}</label>
                		</div>
                		<div class='col-md-4'>
                			<label>数学平均成绩 : {$avg['math']}</label>
                		</div>	 	                		
                	</div>
                	<div class='row'>
                		<div class='col-md-4'>
                			<label>英语老师: {$info['english']['name']}</label>
                		</div>
                		<div class='col-md-4'>
                			<label>英语平均成绩 : {$avg['english']}</label>
                		</div>	 	                		
                	</div>      ";
        
        echo $html; 
                
    }
    
}