<?php
/**
 * Copyright 2017 Liming Jin
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Created by Liming
 * Date: 2016/11/3
 * Time: 20:25
 */
use Workerman\Connection\TcpConnection;

/**
 * 格式化Json常量
 *
 * @param string $node
 * @param bool   $parseArray
 *
 * @return array|stdClass
 */
function parseJsonConstant(string $node, bool $parseArray = false) {
    $json = json_decode(file_get_contents(__DIR__.'/Constant/constant.json'));
    if($parseArray) {
        $ret = [];
        foreach($json->$node as $v) {
            $ret[] = $v[0];
        }
    } else {
        $ret = new stdClass();
        foreach($json->$node as $k => $v) {
            $v = $v[0];
            $ret->$v = $k;
        }
    }
    return $ret;
}

/**
 * 输出日志
 *
 * @param mixed  $message
 * @param string $type
 */
function logs($message, string $type = ' ') {
    if(!is_string($message)) {
        $message = (string)$message;
    }
    $message = str_replace("\n", "\n                        ", $message);
    if($type == '') {
        $type = ' ';
    } elseif(strlen($type) > 1) {
        $type = substr($type, 0, 1);
    }
    echo date('Y-m-d H:i:s').'  '.$type.'  '.$message."\n";
}

/**
 * 连接心跳
 *
 * @param TcpConnection $connection
 */
function heartBeat(TcpConnection $connection) {
    $connection->lastMessageTime = time();
}

/**
 * 取当前时间戳（13位）
 * @return string
 */
function timestamp() {
    list($t1, $t2) = explode(' ', microtime());
    $t1 = round($t1 * 1000);
    return floatval($t2.($t1 >= 100 ? $t1 : ($t1 >= 10 ? '0'.$t1 : '00'.$t1)));
}

/**
 * 删除文件中间部分，从指定位置删除到空行为止
 *
 * @param string $path
 * @param int    $position
 */
function deleteFileToBlankLine(string $path, int $position) {
    $file = fopen($path, 'r+');
    $p1 = $position;
    fseek($file, $p1);
    while($line = fgets($file)) {  //定位到下一个空行
        if(strlen(trim($line)) == 0) {
            break;
        }
    }
    $p2 = ftell($file);
    if($p2 == $p1) {
        return;
    }
    while(true) {
        $buffer = fread($file, 102400);  //最多100KB
        if($buffer === false || ftell($file) == $p2) {
            break;
        }
        $p2 = ftell($file);
        fseek($file, $p1);
        fwrite($file, $buffer);
        $p1 = ftell($file);
        fseek($file, $p2);
    }
    ftruncate($file, $p1);
    fclose($file);
}

/**
 * 计算测试用例数量
 *
 * @param string $text
 *
 * @return int
 */
function getTestCount(string $text) {
    $lines = explode("\n", $text);
    $total = 0;
    $flag = true;
    foreach($lines as $line) {
        if(trim($line) != '') {
            if($flag) {
                ++$total;
                $flag = false;
            }
        } else {
            $flag = true;
        }
    }
    return $total;
}

/**
 * 追加测试用例到文件尾
 *
 * @param string $path
 * @param string $text
 */
function appendTestCase(string $path, string $text) {
    $lines = explode("\n", $text);
    $flag = false;
    $file = fopen($path, 'a');
    foreach($lines as $line) {
        $line = trim($line);
        if($line != '') {
            if($flag) {
                fwrite($file, "\n");
                $flag = false;
            }
            fwrite($file, $line."\n");
        } else {
            $flag = true;
        }
    }
    fwrite($file, "\n");
    fclose($file);
}

/**
 * 更新测试用例版本文件
 *
 * @param string $path
 * @param string $path_in
 * @param string $path_out
 */
function updateVersionFile(string $path, string $path_in, string $path_out) {
    $file = fopen($path, 'w');
    fwrite($file, md5_file($path_in)."\n".md5_file($path_out));
    fclose($file);
}
