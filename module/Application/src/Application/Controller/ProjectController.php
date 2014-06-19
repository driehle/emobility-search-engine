<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZendSearch\Lucene\Lucene;
use Exception;

class ProjectController extends AbstractActionController
{
    public function indexAction()
    {
        try {
            $lucene = Lucene::open("data/index/");
            $num = $lucene->numDocs();
            $hits = array();
            for ($i = 0; $i < $num; $i ++) {
                if (!$lucene->isDeleted($i)) {
                    $hits[] = $lucene->getDocument($i);
                }
            }
            usort($hits, array($this, '_sortCallback'));
        }
        catch (Exception $e) {
            $hits = array();
        }

        return new ViewModel(array(
            'hits' => $hits
        ));
    }

    protected function _sortCallback($a, $b)
    {
        return $a->title < $b->title ? -1 : ($a->title > $b->title ? 1 : 0);
    }
}
