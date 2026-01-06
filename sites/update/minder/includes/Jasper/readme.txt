php-sample
----------

This sample shows how to use the JasperServer Web Services to run a report 
from a PHP application.

To use the sample, you need a web server that supports PHP scripts. The SOAP 
pear package is also required. See the PHP manual for information about 
installing the pear packages. 

The sample assumes you are running JasperServer instance at:

  http://127.0.0.1:8080/jasperserver/services/repository

If you need to change this URL (for example, if you are running JasperServer 
Professional), you must change the client.php file (you can find the URL in one
of the first lines of this file).

The sample application shows how to navigate the JasperServer repository, 
displaying only folder and report units.

On login, use a regular JasperServer account (such as jasperadmin/jasperadmin).

Click a ReportUnit to run it. If it contains input controls, they are displayed
before report execution.

Note: The sample application doesn't necesarily recognize all of the types of 
input control that JasperServer supports.

Testing Log:
------------

Additional Testing:
Date: 2009-11-15
Tested against JasperServer version: 3.7.0
Test environment:
    OS: Windows XP SP3
    Denwer (it's a package that includes programs and utilites: Apache 2, PHP 5, Perl,
    MySQL 5, virtual hosts management system etc. (More info on http://www.denwer.ru)
    used "base package" of Denwer version 3
    Note: this is a Russian language site

