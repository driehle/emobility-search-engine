<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use RuntimeException;
use Application\SearchEngine\Indexer as SearchIndexer;

class SearchIndexController extends AbstractActionController
{
    public function buildAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $indexer = new SearchIndexer();
        $indexer->buildIndex();
    }
    public function updateAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $indexer = new SearchIndexer();
        $indexer->updateIndex();
    }
}
