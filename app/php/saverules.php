<?php
$json = file_get_contents('php://input');
$djson = json_decode($json);
$name = $djson->instance . '_' . 'rules.json';
$filename = "../../data/" . $name;
$file = fopen( $filename, "w" );

if( $file == false )
{
   echo ( "Error in opening new file" );
   exit();
}
fwrite( $file, $json );
fclose( $file );
?>

