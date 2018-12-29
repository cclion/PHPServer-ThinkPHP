<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 是否是手机号码，含虚拟运营商的170号段
 * @author wei sun
 * @param string $phone 手机号码
 * @return boolean
 */
function is_phone($phone)
{
    if (is_numeric($phone) && strlen($phone) == 11 && substr($phone, 0, 1) == 1) {
        return true;
    }
    return false;
}
