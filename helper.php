<?php 

define('PATH', realpath(__DIR__ . '/../../../') . '/');
define('WWW_PATH', PATH.'public/'); 

function init_laravel(){
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
    global $config;
    global $redis_lock; 
    //锁前缀
    global $lock_key;
    $lock_key = env("LOCK_KEY",'redis');
    $redis_lock = [
        'host'=>env('REDIS_HOST'),
        'port'=>env('REDIS_PASSWORD'),
        'auth'=>env('REDIS_PORT'),
    ];
    $medoo_db_config = [
        'db_host'=>env("DB_HOST"),
        'db_port'=>env("DB_PORT"),
        'db_name'=>env("DB_DATABASE"),
        'db_user'=>env("DB_USERNAME"),
        'db_pwd'=>env("DB_PASSWORD"),
    ];
    include PATH . '/vendor/thefunpower/db_medoo/boot.php'; 
}

/**
 * 设置配置
 */
function set_config($title, $body, $shop_id = '')
{
    if($shop_id) {
        $shop_id = "_" . $shop_id;
        $title = $title . $shop_id;
    }
    if(in_array($title, [
        '_timestamp',
        '_signature',
    ])) {
        return;
    }
    $title = strtolower($title);
    $one = db_get_one("config", "*", ['title' => $title]);
    if (!$one) {
        db_insert("config", ['title' => $title, 'body' => $body]);
    } else {
        db_update("config", ['body' => $body], ['id' => $one['id']]);
    }
}
/**
 * 优先取数据库，未找到后取配置文件
 */
function get_config($title, $shop_id = '')
{
    if($shop_id) {
        $shop_id = "_" . $shop_id;
    }
    global $config;
    if (is_array($title)) {
        if($shop_id) {
            $new_title = [];
            $in_array = [];
            foreach($title as $k) {
                $new_k = $k . $shop_id;
                $new_title[] = $new_k;
                $in_array[$new_k] = $k;
            }
            $title = $new_title;
        }
        $list = [];
        $in_arr = [];
        foreach($title as $kk) {
            $in_arr[] = strtolower($kk);
        }
        $all  = db_get("config", "*", ['title' => $in_arr]);
        foreach ($all as $one) {
            $body = $one['body'];
            $key  = $one['title'];
            $list[$key] = $body ?: $config[$key];
            $list[$in_array[$key]] = $list[$key];
        }
        return $list;
    } else {
        if($shop_id) {
            $title = $title . $shop_id;
        }
        $title = strtolower($title);
        $one   = db_get_one("config", "*", ['title' => $title]);
        $body = $one['body'];
        if (!$body) {
            return $config[$title];
        }
        return $body;
    }
}