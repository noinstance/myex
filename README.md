# myex
## A MySql EXporter

Exports a mysql database to a remote server.

### Built with Wordpress in mind
- Reads from wp-config.php and replicates the database in a remote server;
- Replaces urls;
- Fixes serialiazed data;
- Works with SiteOrigin Page Builder migrations.

Requires mysqldump.

### Options
```
  -d=value   destiny database name
  -u=value   destiny user name
  -p=value   destiny password
  -D=value   origin database name, defaults to -d value
  -f=value   backup filename, defaults to database name and timestamp
  -w=dir     wordpress dir; reads from wp-config.php
  -s=value   remote server 
  -r=v1,v2   replaces v1 for v2 in the file; useful for absolute urls
  -z         gzip output
  -h         print the help screen
```

### Setup
Make a copy of ```config.sample.php``` and rename to ```config.php```.
Edit ```$origin``` and ```$destiny``` arrays to provide info of your local database server, remote servers and remote database servers.


### Usage

```cd``` to src.

**Simple usage:** backup ```bloglocal``` database.

```
$ php myex.php -D=bloglocal
```

**Wordpress common scenario:** backup database (read name and user from ```wp-config.php``` in given directory), replace local urls for server urls, zip and restore on ```server1```.

```
$ php myex.php -w=/var/www/myblog -r=blog.local,blog.server1.com -z -s=server1
```


**All options:** backup local database ```bloglocal``` into file ```blog_backup.sql```, replace local urls for server urls, gzip it, send it to ```server1```, restore it there with the database name ```blogdev``` and user ```blog``` with password ```qwerty```.

```
$ php myex.php -D=bloglocal -f=blog_backup.sql -r=blog.local,blog.server1.com  -z -s=server1 -d=blogdev -u=blog -p=qwerty
```
