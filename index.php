<?php require 'db.php';

print '<pre>'."\n";
//print_r(gd_info());
$el_camino = getcwd();
$F = scandir('.');

$statement1 = $dbh->prepare( 'CREATE TABLE IF NOT EXISTS
	imagen (
		camino		varchar(255),
		nombre		varchar(255),
		im		bytea,
		PRIMARY KEY	(camino, nombre)
	) WITHOUT OIDS;') or die('failed to prepare SQL statement: error ' . $dbh->errorCode());
$statement1->execute()
	or die('failed to execute SQL statement: error '
	. $statement1->errorCode() );
	
	
$statement2 = $dbh->prepare('SELECT (nombre) FROM imagen WHERE camino = :c;')
	or die('failed to prepare SQL SELECT statement: error ' . $dbh->errorCode());
$statement2->bindParam(':c', $el_camino);
echo $el_camino . "\n";
$statement2->execute() or die('failed to execute SQL SELECT statement: error ' . $statement2->errorCode() );

$file_list_d = Array();
while($record = $statement2->fetch()){
	$file_list_d[] = $record['nombre'];
}

$file_list_f = Array();
foreach($F as $f){
	$ext = substr($f, strrpos($f, '.'));
	switch ($ext){
		case '.jpeg':
		case '.jpg':
		case '.png':
		case '.gif':
		case '.bmp':
			$file_list_f[]=$f; break;
		default:
			continue;
	}
}

$file_list_n = Array();
foreach($file_list_f as $f){
	if (is_array($file_list_d) && !in_array($f, $file_list_d)){
		$file_list_n[]=$f;
	}
}
echo '</pre><p>';
foreach ($file_list_f as $f) {
	echo ' <a href="'.$f.'"><img src="i.php?f='.$f.'" alt="'.$f.'" /></a> ';
}
echo '</p><pre>';
/*
echo '$file_list_d == '; print_r($file_list_d);
echo "\n";
echo '$file_list_f == '; print_r($file_list_f);
echo "\n";
echo '$file_list_n == '; print_r($file_list_n);
 */
$statement3 = $dbh->prepare(
	'INSERT INTO imagen (camino, nombre, im) VALUES (:c, :n, decode(:i, \'base64\'));')
	or die('failed to prepare SQL INSERT statement: error ' . $statement->errorCode());


foreach ($file_list_n as $new) {
	$ext = substr($new, strrpos($new, '.'));
	print $new . ' ' . $ext . ' ';
	switch ($ext) {
		case '.jpeg':
		case '.jpg': $im = imagecreatefromjpeg($el_camino.'/'.$new); break;
		case '.png': $im = imagecreatefrompng($el_camino.'/'.$new); break;
		case '.gif': $im = imagecreatefromgif($el_camino.'/'.$new); break;
		case '.bmp': $im = imagecreatefrombmp($el_camino.'/'.$new); break;
		default: die('could not create image: unknown image filetype: ' . $ext);
	}
	$size0 = getimagesize($el_camino.'/'.$new); // width x height etc.
	print_r($size0);
	$ratio = min(120/max(120,$size0[0]), 120/max(120,$size0[1]));
	$jm = imagescale ($im, $ratio*$size0[0], $ratio*$size0[1]);
	imagedestroy($im);
	ob_start();
	switch ($ext){
		case '.jpeg':
		case '.jpg': imagejpeg($jm); break;
		case '.png': imagepng($jm); break;
		case '.gif': imagegif($jm); break;
		case '.bmp': imagebmp($jm); break;
		default: die('could not write image data: unknown type: ' . $ext);
	}
	$km = base64_encode(ob_get_contents());
	ob_end_clean();
	if (!isset($km) || !$km) die($el_camino.'/'.$new.': could not write image data: no output');
	$lm = strlen($km);
	print 'write ' . $lm . ' ' . ($lm==1 ? 'byte' : 'bytes') . "\n";
	imagedestroy($jm);
	$statement3->bindParam(':c', $el_camino);
        $statement3->bindParam(':n', $new);
        $statement3->bindParam(':i', $km);
	$statement3->execute()
		or die('failed to execute SQL INSERT statement: error '
		. $statement3->errorCode() . '(' . $el_camino . ', ' . $new . ', ' . strlen($km_64) . 'b)');
}

echo '</pre>'."\n";
