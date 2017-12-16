<?php
$dbh = new PDO('pgsql:'
		. 'user=imagen;'
		. 'password=iamsoheavy;'
		. 'host=db.example.biz;'
		. 'dbname=imagen;'
		// Addidional security options not documented in php
		// https://www.postgresql.org/docs/9.6/static/libpq-connect.html#LIBPQ-PARAMKEYWORDS
//		. 'sslmode=verify-full;'                                                    
//		. 'sslrootcert=/etc/pki/ca-trust/extracted/openssl/ca-bundle.trust.crt'
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
