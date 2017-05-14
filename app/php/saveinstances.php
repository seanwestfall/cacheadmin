<?php
$json = file_get_contents('php://input');
$filename = "../../data/instances.json";
$file = fopen( $filename, "w" );

if( $file == false )
{
   echo ( "Error in opening new file" );
   exit();
}
fwrite( $file, $json );
fclose( $file );
?>
