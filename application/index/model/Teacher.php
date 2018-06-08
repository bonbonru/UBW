<?php
namespace app\index\model;

use think\Model;

class Teacher extends Model{
    
    protected $table = "xyh_teacher";
    protected $pk = "id";
    protected $insert = [ 'status' => 1 ];
    
    // 获取器
    public function getSexAttr($v){
        $sex = [ 0 => '女' , 1 => '男' ];
        return $sex[$v];
    }
    public function getCreatetimeAttr($v){
        return date("Y-m-d",$v);
    }
    
    // 全局范围查询
    public function base($query) {
        $query -> where('status = 1');
    }
    
    // 搜索查询
    public function scopeSearch($query,$keyword) {
        if(!empty($keyword) || $keyword == '0') {
            $query -> wherelike('id|name',"%$keyword%");
        }
    }
    
    // 一对多 关联班级表
    public function grade() {
        return $this->HasMany('grade');
    }
}