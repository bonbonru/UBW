<?php

namespace app\index\controller;

use app\index\validate\Teacher as TeacherValidate;
use think\Controller;
use think\Db;
use app\index\model\Score as ScoreModel;
use app\index\model\Grade;
use think\facade\Request;
use app\index\model\Student;

class Score extends Base{
    
    protected $type = [ 1 => "普通测试" , 2 => "期中考试" , 3 => "期末考试" ];
    
    // 列表首页
    public function index() {
        
        $searClass = $this->request->param('searClass',0,'intval');
        $keyword = $this->request->param('keyword', '', 'htmlspecialchars,trim');
        // 列表
        $score = ScoreModel::hasWhere('student', ScoreModel::search($keyword,$searClass))
                           ->order('score.id desc')
                           ->paginate(5,false,['query'=>['keyword'=>$keyword,'searClass'=>$searClass]]);
        // 下拉班级选择
        $class = Grade::field('id,name')->select();
        // 班级统计
        if(!empty($searClass)){
            $classCount = ScoreModel::hasWhere('grade',ScoreModel::search('',$searClass))
                                    ->field('avg(english) as english,avg(math) as math,avg(language) as language,count(*) as count')
                                    ->find();
            $this->assign('classCount',$classCount);
        }
        
        $this->assign('list',$score);
        $this->assign('class',$class);
        $this->assign('searClass',$searClass);
        $this->assign('title','成绩资料');
        $this->assign('keyword',$keyword); 
        
        return $this->fetch();  
    }
    
    // 添加页面
    public function add() {
        
        if ($this->request->isPost()) {     
            return $this->addSave();            
        }       
        $list = Grade::field('id,name')->select();
        
        $this->assign('list',$list);
        $this->assign('type',$this->type);
        $this->assign('title','添加成绩');
        
        return $this->fetch();
    }
    
    // 添加处理
    public function addSave(){
        
        $data = $this->request->param();
        
        // 验证
        $vali = new TeacherValidate;
        if(!$vali->scene('score')->check($data)){
            $this->error($vali->getError());
        }
        
        $re = ScoreModel::create($data);
        
        if($re->id){
            $this->success('已成功添加新资料',url('index'));
        } else {
            $this->error('添加失败请重试');
        }
    
    }
    
    
    
    // 编辑页面
    public function edit() {
        
        if($this->request->isPost()){
            return  $this->update();
        }
        
        $id = $this->request->param('id',0,'intval');
        
        $id || $this->error('参数异常');
        
        $score = ScoreModel::useGlobalScope(false)->where('status=1')->find($id);
        
        $score = ScoreModel::useGlobalScope(false)->where('id = '.$id)->find();
        
        $class = Grade::field('id,name')->where('status = 1')->select();
        $student = Student::field('id,name')->where(['status'=>1,'class_id'=>$score->grade->id])->select();
        
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
        
        $data['id'] || $this->error('参数异常');
        
        $vali = new TeacherValidate;
        if(!$vali->scene('score')->check($data)){
            $this->error($vali->getError());
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
                                ->join('student s ',' s.id = sc.student_id')
                                ->join('class c ',' c.id = s.class_id')
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