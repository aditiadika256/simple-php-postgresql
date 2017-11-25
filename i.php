<?php require 'db.php';
$el_camino = getcwd();
$el_nombre = $_GET['f'];
$statement = $dbh->prepare(
	"SELECT (encode(src, 'base64')) AS src_64 FROM ".$my_schema.".".$my_table." WHERE camino = :camino AND nombre = :nombre LIMIT 1;")
	or die('failed to prepare SQL SELECT statement: error ' . $dbh->errorCode());
$statement->bindParam(':camino', $el_camino);
$statement->bindParam(':nombre', $el_nombre);
$statement->execute() or die('failed to execute SQL SELECT statement: error ' . $statement->errorCode());
$record_set = $statement->fetchAll();
if (!isset($record_set[0]['src_64'])) {
	die('404 record not found');
}
$ext = substr($el_nombre, strrpos($el_nombre, '.'));
switch ($ext) {
case '.jpeg':
case '.jpg': header('Content-type:image/jpeg'); break;
case '.png': header('Content-type:image/png'); break;
case '.gif': header('Content-type:image/gif'); break;
case '.bmp': header('Content-type:image/bmp'); break;
default: header('Content-type:text/plain');
	echo $record['src_64'];
	exit();
}
echo base64_decode($record_set[0]['src_64']);
