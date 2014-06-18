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

class ProjectController extends AbstractActionController
{
    public function indexAction()
    {
        try {
            $lucene = Lucene\Lucene::open("data/index/");
            $num = $lucene->numDocs();
            $hits = array();
            for ($i = 0; $i < $num; $i ++) {
                if (!$lucene->isDeleted($i)) {
                    $hits[] = $lucene->getDocument($i);
                }
            }
            usort($hits, array($this, '_sortCallback'));
        }
        catch (\Exception $e) {
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
