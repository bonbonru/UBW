<?php
namespace app\index\model;

use think\Model;


class Grade extends Model {
    
    protected $table = "xyh_class" ; 
    protected $pk = "id";
    protected $insert = [ 'status' => 1 ];
    
    public function getGradeAttr($v) {
        $grade = [1=>'小学',2=>'初中'];
        return $grade[$v];
    }
    
    // 全局范围查询
    public function base($query) {
        $query->where('status = 1');
    }
    
    // 相对关系教师表
    public function teacher() {
        return $this->BelongsTo('teacher');
    }
    
}