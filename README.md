# emobility-search-engine

Within the program "Schaufenster Elektromobilität", the German government set-up a set of over 100 projects to promote e-mobility. This tool provides a search engine to efficiently search within these projects. Currently the projects listed on these four websites are added to the search index:

 -   [www.elektromobilitaet-verbindet.de](http://www.elektromobilitaet-verbindet.de/)
 -   [www.emo-berlin.de](http://www.emo-berlin.de/)
 -   [www.livinglab-bwe.de](http://www.livinglab-bwe.de/)
 -   [www.metropolregion.de](http://www.metropolregion.de/)

![Click here for a screenshot](https://raw.githubusercontent.com/driehle/emobility-search-engine/master/public/img/screenshot.png)


## Setup

### Use a release package

This is the easiest way to get up running with this search engine. Simply head over to the [Release section](https://github.com/driehle/emobility-search-engine/releases) and download a package, which includes all required dependencies and a search index. Extract these files anywhere to your computer and start a webserver. The easiest way to start a webserver is using the PHP built-in webserver.

```
cd emobility-search-engine
php -S 127.0.0.1:8080 -t public/ public/index.php
```

Point your browser to http://127.0.0.1:8080/ and you should see a simple website with the search engine. Once you're done press Ctrl + Q in the console to stop the webserver. 

### Use composer to start from scratch

If you want to start from scratch with the latest (and probably instable) version, first clone this Git repository:

```
git clone https://github.com/driehle/emobility-search-engine.git
```

Make sure you have at least PHP 5.4 installed on your system and install the required dependencies by running composer:

```
cd emobility-search-engine
php composer.phar install
```

In order to build the search index you need to crawl all releveant websites. Run the following command to start building the index and go grab a coffee as this may take a few minutes.

```
php bin/e-mobility.php search-index build
```

Use the built-in webserver from PHP 5.4 or later as described above or do an ordinary Apache or nginx setup, creating a new virtual host for this application, pointing the document root to the `public/` folder.

If you want to update your search index later on, you can easily use the `search-index update` command, which is called similarily to the `search-index build` command. Please note that calling the latter one again a second time, will discard and re-create your search index.


## Credits

The search engine is based on the [ZendSearch\Lucene](https://github.com/zendframework/ZendSearch) component of the [Zend Framework 2](http://framework.zend.com/), which was also used to build the website around. The user interface is based upon [Twitter Bootstrap](http://getbootstrap.com/), which includes other componets, such as [jQuery](http://jquery.com/) and [Glyphicons](http://glyphicons.com/).

Unless otherwise noticed, files in this repository are under the MIT license which is rather permissive.
