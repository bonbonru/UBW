<?php
namespace app\index\model;

use think\Model;

class Student extends Model{
    
    protected $table = "xyh_student";
    protected $pk = 'id';
    protected $auto = [];
    protected $inset = [ 'status' => 1 ];
    protected $update = [];
    
    //获取器
    protected function getSexAttr($v){
        $sex = [0=>'女',1=>'男'];
        return $sex[$v];
    }
    protected function getTypeAttr($v){
        $type = [1=>'一年级',2=>'二年级',3=>'三年级',4=>'四年级',5=>'五年级',6=>'六年级',7=>'初一',8=>'初二',9=>'初三'];
        return $type[$v];
    }
        
    // 全局范围
    public function base($query) {
        $query->where('status = 1');
    }
    
    // 范围查询
    public function scopeSearch($query,$keyword) {
        if(!empty($keyword) || $keyword == '0'){
            $query->wherelike('id|name',"%{$keyword}%");
        }
    }
    
    // 相对关联班级
    public function grade() {
        return $this->belongsTo('grade','class_id');
    }
    
    // 关联成绩
    public function score() {
        return $this->hasMany('score');
    }
    
    
}