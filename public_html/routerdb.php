<?php
include ("db_pdo.inc");   # PHP Database Objects, PHPLIB style 
class DB_rancid extends DB_Sql {
  var $Host     = "localhost";
  var $Database = "rancid";
  var $User     = "rancid";
  var $Password = "cFa478w79VvQ5ved";
}
$db = new DB_rancid;

# where to put router.db files (%s=section)
$path = "/var/lib/rancid/%s/router.db";

$where = ""; # do all sections unless arguments found to specify the sections to do
for ($i=1; $i<$argc; $i++) {
	if ($where) $where .= ",";
	$where .= $db->quote($argv[$i]);  # escape strings according to database type and language
} 
if ($where) $where = "WHERE section IN ($where)";
# $where = "WHERE section IN ('NSW','QLD','VIC')";   #set sections manually here rather than command line

$db->query("SELECT section, hostname, state, type, comments FROM devices $where ORDER BY section, hostname");
$last = "";
$fp = false;
while ($db->next_record()) {
	extract($db->Record);
	if (!strpos($hostname,":",1)) {   #don't output if hostname has colons which are not permitted
		if ($section<>$last) {
			if ($fp) fclose($fp);
			$file = sprintf($path,$section);
			$fp = fopen($file,"w");
			fwrite($fp,"\n# WARNING: do not edit this file directly -- this file was automatically generated by FETID\n");
		}
		$state = strtolower($state);
		if ($comments) $cmt = ":$comments"; else $cmt="";
		if ($fp) fwrite($fp,"$hostname:$type:$state$cmt\n");
		$last = $section;
	}
}
if ($fp) fclose($fp);
?>
