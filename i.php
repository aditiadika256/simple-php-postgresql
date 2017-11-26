<?php
if(array_key_exists('i', $_GET))
{
	$el_nombre = $_GET['i'];
}
else
{
	header("HTTP/1.0 404 Not Found", true, 404);
	exit();
}
$el_camino = getcwd();

require 'db.php';

switch (@strtolower(@substr($el_nombre, @strrpos($el_nombre, '.')))) {
	case '.jpeg':
	case '.jpg': header('Content-type:image/jpeg'); break;
	case '.png': header('Content-type:image/png'); break;
	case '.gif': header('Content-type:image/gif'); break;
	case '.bmp': header('Content-type:image/bmp'); break;
	default:
		header("HTTP/1.0 404 Not Found", true, 404);
		exit();
}

$statement = @$dbh->prepare(
	"SELECT (encode(src, 'base64')) AS src_64 FROM "
	. $my_schema.".".$my_table
	. " WHERE camino = :camino AND nombre = :nombre LIMIT 1;");
@$statement->bindParam(':camino', $el_camino);
@$statement->bindParam(':nombre', $el_nombre);
@$statement->execute();
$record_set = @$statement->fetchAll();
if (!isset($record_set[0]['src_64']) || @strlen($record_set[0]['src_64']) == 0)
{
	header("HTTP/1.0 404 Not Found", true, 404);
	exit();
}
else echo base64_decode($record_set[0]['src_64']);
