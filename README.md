# makeUp
<b>makeUp</b> is an easy to use PHP framework and a compilation of popular frontend libraries. It is a package that includes almost everything you need to build awesome web applications from the ground up.

Components:
- makeUp PHP Framework
- jQuery
- Twitter Bootstrap
- Font Awesome

## Requirements
PHP 5.6 or above, MySQL

## Download

Either download or clone the sources from the Git repository: 
<pre>$ git clone https://github.com/dahas/makeup.git</pre>

## Get started
- The source files must be in a folder named "makeup" within the root directory of your project. 
- Install dependent libraries: 
<pre>$ cd makeup 
$ php composer.phar install</pre>
- Create file "index.php" in your root directory. Paste the following lines into it:
<pre>&lt;?php
require_once('makeup/app/controller/app.php');
$App = new makeup\app\controller\App();
$App->execute();</pre>
- Launch your app in the webbrowser

## Author 
Martin J. Wolf

## License
MIT
