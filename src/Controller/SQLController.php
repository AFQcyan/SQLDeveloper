<?php

namespace src\Controller;

use src\App\DB;

class SQLController
{
    public function sql()
    {
        $url = $_SERVER['REQUEST_URI'];
        $emplodeUri = explode('/', trim($url));
        var_dump(implode(' ', explode('%20', $emplodeUri[2])));
        $emplodeSql = implode(' ', explode('%20', $emplodeUri[2]));
        $columns = []; //컬럼 이름
        $columnAs = []; //컬럼 이름 재선언
        $table = []; // 테이블
        $tableAs = []; // 테이블 이름 재선언
        $whereRcs = []; // WHERE 절 재료
        $whereExp = []; // WHERE 절 표현식
        $whereInfo = []; // WHERE 절 합체 정보
        $orFirst = false;

        $sql = strtolower(trim($emplodeSql));
        $data = DB::fetchAll($sql);
        $isSelect = strstr($sql, ' ', true);
        if ($isSelect == "select") $sep = explode("from", $sql);
        else {
            echo "해당 SQL 은 SELECT 문이 아닙니다.";
            exit;
        }; //FROM 전과 후로 자르기
        if (count($sep) < 2) {
            echo "해당 SELECT 문은 FROM 문이 존재하지 않습니다.";
            exit;
        }
        $select = explode(',', explode("select", $sep[0])[1]); //FROM 전 >> SELECT 로 자른 후 나머지 컬럼 값 가져오기 >> 가져온 값 , 로 분해 >> column 배열 값 완성.


        foreach ($select as $each) {
            $as = explode(" ", trim($each));
            array_push($columns, $as[0]); //컬럼 배열의 값을 공백으로 잘라 컬럼 이름만 담기.
            array_push($columnAs, count($as) < 2 ? '' : ($as[1] == 'as' ? $as[2] : $as[1])); // 컬럼 배열의 재선언이 사용 되었는지, 재선언에 AS 가 사용되었는지 확인후 재선언 이름만 담기;
        }

        if ($columns[0] == '') {
            echo "해당 SELECT 문에는 불러올 컬럼이 명시되어 있지 않습니다.";
            exit;
        }


        $afterFrom = explode("where", $sep[1]); // FROM 후 >> WHERE 절로 자르기 -> 전 : 테이블 / 후 : WHERE
        $from = explode(",", implode(',', explode('join', $afterFrom[0]))); // FROM 후 >> WHERE 전 >> JOIN 과 , 로 자르기 >> table 배열 값 완성.

        foreach ($from as $t) {
            $as = explode(' ', trim($t));
            array_push($table, $as[0]); // 테이블 배열의 값을 잘라 테이블 이름만 담기
            array_push($tableAs, count($as) < 2 ? '' : ($as[1] == 'as' ? $as[2] : $as[1])); //테이블 배열의 재선언 사용 여부, 재선선에 AS 사용 여부를 파악해 재선언 이름만 담기
        }

        if ($table[0] == '') {
            echo "해당 SELECT 문에는 불러올 테이블이 명시되어 있지 않습니다.";
            exit;
        }

        $join = explode("join", count($afterFrom) < 2 ? '' : $afterFrom[1]); // FROM 후 >> WHERE 전 >> JOIN 으로 자르기 -> WHERE 절이 있는지 확인. --> 값 : 전체 where 절
        $where = explode("or", count($join) < 2 ? $join[0] : $join[1]); // FROM 후 >> WHERE 절 -> AND 가 먼저 연산되기에, [$join[0 or 1] => 전체 where 절]을  OR 로 자르기
        $realwhere = [];
        foreach ($where as $we) {
            $we = explode("and", $we); //전체 where 절이 OR 로 분리된 상태 >> 그 안을 AND 로 다시 한번 분해 --> OR 절 안에 AND 절이 포함된 이차원배열 구조
            if (count(explode(' ', $we[0])) > 2) array_push($realwhere, $we);  //완벽한 WHERE 절이 존재하는지 확인후 프로세스에 올림
        }

        foreach ($realwhere as $orWhere) {
            $andFirst = false;
            if ($orFirst) array_push($whereInfo, "or");
            $orFirst = true;
            foreach ($orWhere as $andWhere) {
                if ($andFirst) array_push($whereInfo, "and");
                $andFirst = true;
                $each = explode(' ', trim($andWhere));
                array_push($whereRcs, $each[0], $each[count($each) - 1]);
                for ($i = 0; $i < count($each); $i++) {
                    if ($i == 0 || $i == count($each) - 1) {
                    } else {
                        array_push($whereExp, $each[$i]);
                    }
                }
                array_push($whereInfo, "whereRcs[" . (count($whereRcs) - 2) . "] + whereExp[" . (count($whereExp) - 1) . "] + whereRcs[" . (count($whereRcs) - 1) . "]");
            }
        }
        // 분배 완료
        $sortedData = [];
        $describe = [];
        foreach ($table as $tb) {
            array_push($describe, DB::fetchAll("DESC " . $tb));
        }
        for ($i = 0; $i < count($data); $i++) {
            for ($j = 0; $j < count($columns); $j++) {
                if ($columns[$j] == "*") {
                    foreach ($describe as $desc) {
                        foreach ($desc as $sc) {
                            $pureData = ["row" => $i + 1];
                            $pureData["title"] = $sc->Field;
                            $pureData['type'] = $sc->Type;
                            $dumpArray = (array) $data[$i];
                            $pureData["data"] = $dumpArray[$pureData["title"]];
                            array_push($sortedData, $pureData);
                        }
                    }
                } else {
                    $pureData = ["row" => $i + 1];
                    $pureData["title"] = $columnAs[$j] != '' ? $columnAs[$j] : (count(explode(".", $columns[$j])) < 2 ? $columns[$j] : explode(".", $columns[$j])[1]);
                    foreach ($describe as $desc) {
                        foreach ($desc as $sc) {
                            if ($sc->Field == (count(explode(".", $columns[$j])) < 2 ? $columns[$j] : explode(".", $columns[$j])[1])) {
                                $pureData['type'] = $sc->Type;
                            }
                        }
                    }
                    $dumpArray = (array) $data[$i];
                    $pureData["data"] = $dumpArray[$pureData["title"]];
                    array_push($sortedData, $pureData);
                }
            }
        }
        echo json_encode(["rst" => $sortedData], JSON_UNESCAPED_UNICODE);
        // var_dump($sortedData);
    }
}
