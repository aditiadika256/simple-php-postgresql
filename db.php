<?php /* edit with your PostgreSQL database connection details */
$dbh = new PDO('pgsql:
		user=imagen;
		password=my_super_top_secret;
		host=db.example.biz;
		dbname=imagen;
		sslmode=verify-full;                                                    
		sslrootcert=/etc/pki/ca-trust/extracted/openssl/ca-bundle.trust.crt')			
or /* https://www.postgresql.org/docs/current/static/errcodes-appendix.html */
die('failed to connect to database: error ' . $dbh->errorCode());

/* verify database connection but do nothing else if script is called directly */
if (substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/')) === '/db.php')
	die('successfully connected to database');
