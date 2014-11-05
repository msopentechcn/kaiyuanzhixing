<?php

require_once('util/finediff.php');

class LicenseDiff
{
    public static $diffLength;

    public static function compareLicense($fromText, $toText) {
        $from_text = mb_convert_encoding($fromText, 'HTML-ENTITIES', 'UTF-8');
        $to_text = mb_convert_encoding($toText, 'HTML-ENTITIES', 'UTF-8');

        $granularity = FineDiff::$wordGranularity;
        $diff_opcodes = FineDiff::getDiffOpcodes($from_text, $to_text, $granularity);
        self::$diffLength = strlen($diff_opcodes);
        $rendered_diff = FineDiff::renderDiffToHTMLFromOpcodes($from_text, $diff_opcodes);
        return $rendered_diff;
    }
}

?>
