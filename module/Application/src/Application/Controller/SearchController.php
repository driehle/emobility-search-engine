<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZendSearch\Lucene;

class SearchController extends AbstractActionController
{
    public function indexAction()
    {
        $lucene = Lucene\Lucene::open("data/index/");
        $q = $this->params()->fromQuery("q");

        try {
            $query = Lucene\Search\QueryParser::parse($q);
            $hits = $lucene->find($query);
        }
        catch (\Exception $e) {
            $hits = array();
        }

        return new ViewModel(array(
            'hits' => $hits,
            'q' => $q,
            'query' => $query
        ));
    }
}
