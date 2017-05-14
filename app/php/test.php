<?php
$filename = "../../data/test.json";
$file = fopen( $filename, "w" );

if( $file == false )
{
echo ( "Error in opening new file" );
exit();
}

fwrite( $file, "test");
fclose( $file );
?>
