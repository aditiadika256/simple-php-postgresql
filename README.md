# i.php
simple PHP//PostgreSQL web server image indexing application

This application is very simple.

1.) Verify dependencies: php-gd php-pdo-pgsql

2.) Drop the three files
        index.php  i.php  db.php
    into a folder that contains images on a web server.

3.) Edit the file
        db.php
    with your PostgreSQL database connection details.
    You need permission to CREATE TABLE 'imagen' and
    to perform basic queries such as INSERT and SELECT
    on that table.

4.) Visit the web server image folder from your browser.
    The script will create thumbnail images and insert
    them into the database if they are not there already,
    and output an html page that displays each thumbnail
    hyperlinked to the corresponding original image file.
    
***** ***** ***** ***** ***** ***** ***** ***** ***** *****
     =   =   =   =   =   =   =   =   =   =   =   =   =

VERY SIMPLE. WORKS, BUT DEFINITELY STILL NEEDS WORK.
