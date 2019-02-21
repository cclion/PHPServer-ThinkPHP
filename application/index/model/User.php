<?php
/**
 * Created by PhpStorm.
 * User: job
 * Date: 2018-12-29
 * Time: 17:03
 */
namespace app\index\model;

use think\model;

class User extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'user';

    // 设置当前模型的数据库连接
    protected $connection = 'db_config';


}