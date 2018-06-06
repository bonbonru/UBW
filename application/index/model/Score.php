<?php
namespace app\index\model;

use think\Model;
use think\model\relation\HasOne;
use app\index\model\Score as ScoreModel;

class Score extends Model{
    
    protected $table = "xyh_score";
    
    protected $pk = 'id';
    
    protected $auto = [];
    protected $insert = [ 'status' => 1];
    protected $update = [];
    
    protected static function init() {
        
    }
    
    // 全局范围查询   User::useGlobalScope(false);
   /*  public function base($query) {  
        $query->where('status = 1');
    } */
    
    // 搜索框
    public function scopeSearch($query,$keyword,$searClass) {
        if(!empty($keyword) || $keyword == '0' ){          
            $query -> wherelike('score.id',"%{$keyword}%");
        }
        if(!empty($searClass)){
            $query->where('score.class_id = '.$searClass);
        }
        
    }
    
    // 状态获取
    public function getTypeAttr($v){
        $type = [1=>'普通测试',2=>'期中考试',3=>'期末考试'];
        return $type[$v];
    }
    
    // 新增状态
    public function getStatusValAttr($v,$d){
        $status = [0=>'删除',1=>'良好'];
        return $status[$d['status']];
    }
    
    public function setNameAttr($v){
        return strtolower($v);
    }
    
    // 相对关联学生表
    public function student() {
        return $this->belongsTo('student');
    }
    
    // 相对关联班级表
    public function grade() {
        return $this->belongsTo('grade','class_id');
    }
    
    
}