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
 * Date: 2016/11/5
 * Time: 15:42
 */


namespace Database;
require_once __DIR__.'/../config.php';

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Query;
use Exception;


/**
 * Class Question
 * @package Database
 */
final class Question extends Database {
    private static $table = 'question';

    /**
     * Question constructor.
     */
    public function __construct() {
        parent::__construct();
        Question::$table = Database::$database.'.'.Question::$table;
    }

    /**
     * @param string $title
     * @param string $description
     * @param array  $test_case
     * @param array  $answer
     * @param float  $memory_limit (MB)
     * @param float  $time_limit   (Seconds)
     * @param array  $data
     *
     * @return int
     * @throws Exception\TestCaseCountException
     * @throws RuntimeException
     */
    public function add($title, $description, $test_case, $answer, $memory_limit = 64.0, $time_limit = 1.0, $data = []) {
        $n = count($test_case);
        if($n != count($answer)) {
            throw new Exception\TestCaseCountException;
        }
        if(!is_dir(CONFIG['test case'])) {
            mkdir(CONFIG['test case'], 0660, true);
        }
        if(!is_dir(CONFIG['answer'])) {
            mkdir(CONFIG['answer'], 0660, true);
        }
        $bulk = new BulkWrite(['ordered' => true]);
        $insert = $bulk->insert([
            'title'       => $title,
            'description' => $description,
            'memory'      => $memory_limit,
            'time'        => $time_limit,
            'data'        => $data
        ]);
        $tmp = '';
        for($i = 0; $i < $n; $i++) {
            if($test_case[$i] != '') {
                $tmp .= trim($test_case)."\n\n";
            }
        }
        file_put_contents(CONFIG['test case'].(string)$insert, $tmp);
        $tmp = '';
        for($i = 0; $i < $n; $i++) {
            if($answer[$i] != '') {
                $tmp .= trim($answer)."\n\n";
            }
        }
        file_put_contents(CONFIG['answer'].(string)$insert, $tmp);
        $bulk->update(['_id' => $insert], ['$set' => [
            'test'   => CONFIG['test case'].(string)$insert,
            'answer' => CONFIG['answer'].(string)$insert
        ]]);
        $result = Database::$connection->executeBulkWrite(Question::$table, $bulk);
        if($result->getInsertedCount() > 0) {
            return (string)$insert;
        }
        return false;
    }

    /**
     * @param ObjectID|string $id
     *
     * @return \stdClass|null
     * @throws \InvalidArgumentException
     */
    public function getOne($id) {
        if(is_string($id)) {
            $id = new ObjectID($id);
        }
        if(!($id instanceof ObjectID)) {
            throw new \InvalidArgumentException;
        }
        $query = new Query(['_id' => $id]);
        $rows = Database::$connection->executeQuery(Question::$table, $query)->toArray();
        if(count($rows) > 0) {
            return $rows[0];
        }
        return null;
    }
}
