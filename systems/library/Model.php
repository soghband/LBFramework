<?php

class Model {
    private static $_data;
    public static function mapData($modelFile, $data) {
        $html = self::loadHtmlModel($modelFile);
        $returnHtml = "";
        if (count($data) > 0) {
            foreach ($data as  $dataVal) {
                list($search, $replace) = self::generateReplaceData($dataVal);
                $htmlReplaceData = $html;
                $innerModel = preg_match_all("/<![a-zA-Z_,]*!>/m",$htmlReplaceData,$match);
                $match = $match[0];
                if ($innerModel > 0) {
                    $htmlReplaceData = self::processSubModel($match, $dataVal, $htmlReplaceData);
                }
                $htmlReplaceData = str_replace($search,$replace,$htmlReplaceData);
                $returnHtml.=$htmlReplaceData;
            }
        }
        return $returnHtml;
    }
    public static function mapAssoc($modelFile,$data) {
        $html = self::loadHtmlModel($modelFile);
        $returnHtml = "";
        if (count($data) > 0) {
            self::processAssocMap($data, $html, $returnHtml);
        }
        return $returnHtml;
    }
    private static function registeredDataMap($modelFile,$dataName) {
        if (isset(self::$_data[$dataName])) {
            return self::mapData($modelFile,self::$_data[$dataName]);
        } else {
            if (ENV_MODE == "dev") {
                return "Model data not found :".$dataName;
            }
            return "";
        }
    }
    private static function parserModelCmd($match) {
        $cleanSearch = array("<!","!>");
        $cleanReplace = array("","");
        $cleanModelCommand = str_replace($cleanSearch,$cleanReplace,$match);
        $commandExplode = explode(",",$cleanModelCommand);
        if (empty($commandExplode[2])) {
            array_push($commandExplode,"");
        }
        return $commandExplode;
    }
    private static function loadHtmlModel($fileName) {
        $modelFile = BASE_DIR."/view/model/".$fileName.".html";
        $loadedModel = Cache::getCache("model_".$fileName);
        if ($loadedModel == "") {
            if (!file_exists($modelFile)) {
                LBUtil::showMsg("HTML model file not found:". $fileName);
            }
            $loadedModel = file_get_contents($modelFile);
            Cache::setCache("model_".$fileName,$loadedModel);
        }
        return $loadedModel;
    }
    private static function generateReplaceData($dataVal) {
        $search = array();
        $replace = array();
        if (is_array($dataVal)) {
            foreach ($dataVal as $field => $data) {
                if (!is_array($data)) {
                    $search[] = "<{" . $field . "}>";
                    $replace[] = $data;
                }
            }
        } else {
            LBUtil::showMsg("Data pattern mismatch please check key and data.");
        }
        return array($search, $replace);
    }
    private static function processSubModel($match, $dataVal, $htmlReplaceData) {
        $searchSub = array();
        $replaceSub = array();
        foreach ($match as $matchVal) {
            list($subModelFile, $dataRef, $assoc) = self::parserModelCmd($matchVal);
            if (isset($dataVal[$dataRef])) {
                $searchSub = $matchVal;
                if (empty($assoc)) {
                    $replaceSub = self::mapData($subModelFile, $dataVal[$dataRef]);
                } else {
                    $replaceSub = self::mapAssoc($subModelFile,$dataVal[$dataRef]);
                }
            } else {
                $searchSub[] = $matchVal;
                if (ENV_MODE == "dev") {
                    $replaceSub[] =  "Model data not found: ".$dataRef;
                } else {
                    $replaceSub[] = "";
                }
            }
        }
        return str_replace($searchSub, $replaceSub, $htmlReplaceData);
    }
    public static function addData($key, $data) {
        if (is_array($data)) {
            if (!is_array(self::$_data)) {
                self::$_data = array();
            }
            self::$_data[$key] = $data;
        } else {
            LBUtil::showMsg("Model data must be array.");
        }
    }
    private static function showAssocError($arrayCheck) {
        if ($arrayCheck > 1) {
            LBUtil::showMsg("Assoc data not equal.");
        }
    }
    private static function processAssocMap($data, $html, &$returnHtml){
        $searchReal = array();
        $replaceTemp = array();
        $countDataSet = 0;
        $arrayCheck = 0;
        foreach ($data as $dataVal) {
            foreach ($dataVal as $field => $assocData) {
                $searchReal[] = "<{" . $field . "}>";
                foreach ($assocData as $key => $data) {
                    $replaceTemp[$key][] = $data;
                }
                if ($countDataSet < count($assocData)) {
                    $arrayCheck++;
                    $countDataSet = count($assocData);
                }
            }
        }
        self::showAssocError($arrayCheck);
        foreach ($replaceTemp as $dataSet) {
            $htmlSet = str_replace($searchReal, $dataSet, $html);
            $returnHtml .= $htmlSet;
        }
    }
    public static function processModel(&$rawView) {
        $matchCount = preg_match_all("/<![a-zA-Z_,]*!>/m", $rawView, $match);
        $match = $match[0];
        if ($matchCount > 0) {
            $modelSearch = array();
            $modelReplace = array();
            foreach ($match as $matchStr) {
                list($modelFile, $dataName) = self::parserModelCmd($matchStr);
                $modelSearch[] = $matchStr;
                $modelReplace[] = self::registeredDataMap($modelFile, $dataName);
            }
            $rawView = str_replace($modelSearch, $modelReplace, $rawView);
        }
    }
}