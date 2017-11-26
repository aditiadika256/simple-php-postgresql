<?php
// edit with your PostgreSQL database connection details
// http://php.net/manual/en/ref.pdo-pgsql.connection.php
$dbh = new PDO ( 'pgsql:'
	. 'user=my_username;'
	. 'password=my_secret;'
	. 'host=localhost;'
	. 'dbname=my_database;'
// additional options not documented by PHP may be needed for security
// https://www.postgresql.org/docs/current/static/libpq-connect.html#LIBPQ-PARAMKEYWORDS
//	. 'sslmode=verify-full;'
//	. 'sslrootcert=/etc/pki/ca-trust/extracted/openssl/ca-bundle.trust.crt;'
);

// schema and name of table to create in database
// $my_schema = 'public';
$my_schema = 'public';
// $my_table = 'imagen';
$my_table = 'imagen';


// verify database connection but do nothing else if script is called directly
// https://www.postgresql.org/docs/current/static/errcodes-appendix.html
if ( substr ( $_SERVER['SCRIPT_NAME'],
	strrpos ( $_SERVER['SCRIPT_NAME'], '/' ) )
	=== '/db.php' )
{
	die ( $dbh->errorCode() );
}
