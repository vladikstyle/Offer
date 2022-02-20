<?php

$characters       = 'abcdef0123456789';
$charactersLength = strlen($characters);
for ( $i = 0; $i < 8; $i++ )
{
    $boundary .= substr($characters, rand(1, $charactersLength) - 1, 1);
}
$boundary .= '-';
for ( $i = 0; $i < 4; $i++ )
{
    $boundary .= substr($characters, rand(1, $charactersLength) - 1, 1);
}
$boundary .= '-';
for ( $i = 0; $i < 4; $i++ )
{
    $boundary .= substr($characters, rand(1, $charactersLength) - 1, 1);
}
$boundary .= '-';
for ( $i = 0; $i < 4; $i++ )
{
    $boundary .= substr($characters, rand(1, $charactersLength) - 1, 1);
}
$boundary .= '-';
for ( $i = 0; $i < 12; $i++ )
{
    $boundary .= substr($characters, rand(1, $charactersLength) - 1, 1);
}
echo $boundary;

?>