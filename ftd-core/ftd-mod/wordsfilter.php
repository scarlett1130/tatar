<?php
class FilterWordsModel extends ModelBase
{
public function FilterWords( $text = "", $replace = "*" )
{
require_once( LIB_PATH."filter.php" );
$patterns = array( "/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\\.([A-Z]{2,4})(\\((.+?)\\))?/i", "/\\b(?:(?:https?|ftp):\\/\\/|www\\.)[-a-z0-9+&@#\\/%?=~_|!:,.;]*[-a-z0-9+&@#\\/%=~_|]/i" );
foreach ( $filter as $sword )
{
$patterns[] = sprintf( "/(?<!\\pL)(%s)(?!\\pL)/u", $sword['name'] );
}
return  $textnew = preg_replace( $patterns, $replace, $text );
}
}

?>