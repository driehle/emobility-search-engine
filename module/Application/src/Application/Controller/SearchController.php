<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Search\QueryParser as LuceneQueryParser;
use Exception;

class SearchController extends AbstractActionController
{
    public function indexAction()
    {
        $lucene = Lucene::open("data/index/");
        $q = $this->params()->fromQuery("q");

        try {
            LuceneQueryParser::setDefaultEncoding('UTF-8');
            $query = LuceneQueryParser::parse($q);
            $hits = $lucene->find($query);
        }
        catch (Exception $e) {
            $hits = array();
        }

        return new ViewModel(array(
            'hits' => $hits,
            'q' => $q,
            'query' => $query
        ));
    }
}
