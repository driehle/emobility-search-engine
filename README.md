emobility-search-engine
=======================

Within the program "Schaufenster Elektromobilität", the German government set-up a set of over 100 projects to promote e-mobility. This tool provides a search engine to efficiently search within these projects. Currently the projects listed on these four websites are added to the search index:

 -   [www.elektromobilitaet-verbindet.de](http://www.elektromobilitaet-verbindet.de/)
 -   [www.emo-berlin.de](http://www.emo-berlin.de/)
 -   [www.livinglab-bwe.de](http://www.livinglab-bwe.de/)
 -   [www.metropolregion.de](http://www.metropolregion.de/)


Setup
-----

To run this tool first clone the git repository. Make sure you have at least PHP 5.4 installed on your system and install the required dependencies by running composer:

```
php composer.phar install
```

In order to build the search index you need to crawl all releveant websites. Run the following command to start building the index and go grab a coffee as this may take a few minutes.

```
php bin/e-mobility.php search-index build
```

You can easily use the build-in webserver of PHP 5.4 to run the tool. To start the webserver:

```
php -S 127.0.0.1:8080 -t public/ public/index.php
```

Point your browser to http://127.0.0.1:8080/ and you should see a simple website with the search engine. Once you're done press Ctrl + Q in the console to stop the webserver. You may use some ordinare Apache set-up if you want to use the search engine continously.

If you want to update your search index later on, you can easily use the `search-index update` command, which is called similarily to the `search-index build` command. Please note that calling the latter one again a second time, will discard and re-create your search index.


Credits
-------

The search engine is based on the [ZendSearch\Lucene](https://github.com/zendframework/ZendSearch) component of the [Zend Framework 2](http://framework.zend.com/), which was also used to build the website around.

Unless otherwise noticed, files in this repository are under the MIT license which is rather permissive.
