<?php
namespace app\index\validate;

use think\validate;

class Teacher extends Validate {
    
    protected $rule = [
        'name' => 'require|max:5',
        'specialty' => 'require|max:8',
        'number' => 'require|mobile',
        'student_id' => 'require',
        'language' => 'require|number|between:0,100',
        'math' => 'require|number|between:0,100',
        'english' => 'require|number|between:0,100'
    ];
    
    protected $message = [
        'name.require' => '名字不能为空',
        'name.max' => '名字不能超过5',
        'specialty.require' => '专业不能为空',
        'specialty.max' => '专业名不能超过8',
        'number.require' => '电话不能为空',
        'number.mobile' => '电话格式不正确',
        'language.require' => '语文成绩不能为空',
        'language.bumber' => '语文成绩请输入数值',
        'language.between' => '语文成绩请0-100的数值',
        'math.require' => '数学成绩不能为空',
        'math.bumber' => '数学成绩请输入数值',
        'math.between' => '数学成绩请0-100的数值',
        'english.require' => '英文成绩不能为空',
        'english.bumber' => '英文成绩请输入数值',
        'english.between' => '英文成绩请0-100的数值',
        'student_id.require' => '请选择学生'
    ];
    
    protected $scene = [
        'teacher' => ['name','specialty','number'],
        'score' => ['language','math','english','student_id']
    ];
    
}