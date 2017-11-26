<?php require 'db.php';
$el_camino = getcwd();
$los_ficheros = Array();
foreach (scandir('.') as $nombre){
	if ( is_file($nombre) ) {
		switch (strtolower(substr($nombre, strrpos($nombre, '.'))))
		{
			case '.jpeg':
			case '.jpg':
			case '.png':
			case '.gif':
			case '.bmp':
				$los_ficheros[$nombre] = Array (
					'fecha' => date("c", filemtime($nombre)),
					'largo' => filesize($nombre)
				); break;
			default: break;
		} // end switch;
	} // end if;
} // end foreach;


// CREATE TABLE IF NOT EXISTS
	$create_table = $dbh->prepare(
			'CREATE TABLE IF NOT EXISTS '
			. $my_schema . '.' . $my_table . ' (
			camino		varchar(255),
			nombre		varchar(255),
			fecha		timestamp with time zone,
			largo		bigint,
			alt			text,
			src			bytea,
			PRIMARY KEY (camino,nombre)
			) WITHOUT OIDS;')
	or die('failed to prepare CREATE TABLE: error ' . $dbh->errorCode());
	$create_table->execute()
		or die('failed to execute CREATE TABLE: error '
				. $create_table->errorCode());


// VERIFY EXISTING RECORDS IN DATABASE
	$select = $dbh->prepare('SELECT nombre, camino, fecha, largo
				FROM '. $my_schema . '.' . $my_table .' WHERE camino = :camino;')
				or die('failed to prepare SELECT: error ' . $dbh->errorCode());

	$select->bindParam(':camino', $el_camino);

	$select->execute() or die('failed to execute SELECT: error ' . $select->errorCode() );

	$D = Array(); // records to be deleted
	$O = Array(); // records to be preserved

	foreach($select->fetchAll() as $record){
		if (!array_key_exists($record['nombre'], $los_ficheros))
		{
			$D[$record['nombre']] = 1;
		}
		elseif ((new DateTime($record['fecha']))
					->diff(new DateTime($los_ficheros[$record['nombre']]['fecha']))
						== new DateInterval("PT0S")
			&&	$record['largo'] == $los_ficheros[$record['nombre']]['largo'] )
		{
			$O[$record['nombre']] = 1;
		}
	}

	
// DELETE OLD RECORDS FROM DATABASE	
	$delete_old = $dbh->prepare("DELETE FROM ".$my_schema.".".$my_table
		. " WHERE camino = :camino AND nombre = :nombre;")
		or die ('failed to prepare DELETE: error ' . $dbh->errorCode());
	$delete_old->bindParam(':camino', $el_camino);
	foreach ($D as $nombre => $b){
		$delete_old->bindParam(':nombre', $nombre);
		$delete_old->execute() or die ('failed to execute DELETE: error '
		. $delete_old->errorCode());
	} // end foreach;


// UPDATE DATABASE WITH NEW RECORDS
	$transaction = $dbh->prepare(
		"INSERT INTO ".$my_schema.".".$my_table." (camino, nombre, fecha, largo, alt, src)"
		. " VALUES (:camino, :nombre, :fecha, :largo, :alt, decode(:src_64, 'base64'))"
		. " ON CONFLICT ON CONSTRAINT ".$my_table."_pkey DO UPDATE SET (fecha, largo, alt, src)"
		. " = (:fecha, :largo, :alt, decode(:src_64, 'base64'));"
)
		or die('failed to prepare TRANSACTION: error ' . $transaction->errorCode());
	$transaction->bindParam(':camino', $el_camino);
	foreach ($los_ficheros as $nombre => $A) {
		if(array_key_exists($nombre, $O))
			continue; // record is good and does not need to be updated
		switch (strtolower(substr($nombre, strrpos($nombre, '.')))) {
			case '.jpeg':
			case '.jpg': $im = imagecreatefromjpeg($nombre); break;
			case '.png': $im = imagecreatefrompng($nombre); break;
			case '.gif': $im = imagecreatefromgif($nombre); break;
			case '.bmp': $im = imagecreatefromwbmp($nombre); break;
			default: die('could not create image: unknown type: ' . $nombre);
		} // end switch;
		$size0 = getimagesize($nombre);
		$ratio = min(120/max(120,$size0[0]), 120/max(120,$size0[1]));
		$jm = imagescale($im, $ratio*$size0[0], $ratio*$size0[1]);
		imagedestroy($im);

		// START BUFFERING OUTPUT AND GENERATE THUMBNAIL IMAGE
		ob_start(NULL, 0, PHP_OUTPUT_HANDLER_CLEANABLE|PHP_OUTPUT_HANDLER_REMOVABLE)
			or die ('failed to start buffering output');
		// "@" -- silence all error messages while output is buffered
		switch (@strtolower(@substr($nombre, @strrpos($nombre, '.')))){
			case '.jpeg':
			case '.jpg': @imagejpeg($jm) || @imagejpeg(@imagecreatetruecolor(50,50)); @imagedestroy($jm); break;
			case '.png': @imagepng($jm) || @imagepng(@imagecreatetruecolor(50,50)); @imagedestroy($jm); break;
			case '.gif': @imagegif($jm) || @imagegif(@imagecreatetruecolor(50,50)); @imagedestroy($jm); break;
			case '.bmp': @imagewbmp($jm) || @imagewbmp(@imagecreatetruecolor(50,50)); @imagedestroy($jm); break;
			default: break;
		} //end switch;

		// SAVE IMAGE CONTENT, CLEAN OUTPUT BUFFER, AND STOP BUFFERING OUTPUT
		$src = ob_get_clean();
		$src_64 = base64_encode($src);
	    $transaction->bindParam(':nombre', $nombre);
		$transaction->bindParam(':fecha', $A['fecha']);
		$transaction->bindParam(':largo', $A['largo']);
		$transaction->bindParam(':alt', $nombre);
		$transaction->bindParam(':src_64', $src_64);
		$transaction->execute()
		or die('failed to execute TRANSACTION: error '
		. $transaction->errorCode());
	} // end foreach;
	$h1title = htmlspecialchars(urldecode(substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/") + 1)));
?><html>
<head>
  <meta charset="utf-8" />
  <title><?php print $h1title; ?></title>
  <meta name="description" content="las imágenes" />
  <meta name="author" content="i.php" />
<!-- IE -->
<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
<!-- other browsers -->
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
</head><body>
<h1><?php print $h1title; ?></h1>
<p><?php
	foreach($los_ficheros as $nombre => $A){
		print " <a href='".urlencode($nombre)."'><img alt='".htmlspecialchars($nombre)."' title='".htmlspecialchars($nombre)."' src='i.php?i=".urlencode($nombre)."' /></a>";
	}
?></p>
</body></html>
