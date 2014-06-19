<?php

namespace Application\SearchEngine;

use Application\SearchEngine\DocumentProvider;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8\CaseInsensitive as Utf8Analyzer;
use ZendSearch\Lucene\Search\Query\Term as QueryTerm;
use ZendSearch\Lucene\Index\Term as IndexTerm;

class Indexer
{
    /**
     * @var DocumentProvider\AbstractDocumentProvider[]
     */
    protected $_providers;

    /**
     * @var \ZendSearch\Lucene\Lucene
     */
    protected $_lucene;

    /**
     *
     */
    public function __construct()
    {
        $this->_providers = array(
            new DocumentProvider\BayernSachsenProvider(),
            new DocumentProvider\EmoBerlinProvider(),
            new DocumentProvider\LivinglabBweProvider(),
            new DocumentProvider\MetropolregionProvider()
        );

        Analyzer::setDefault(new Utf8Analyzer());
    }

    /**
     * Create or recreate the search index
     */
    public function buildIndex()
    {
        $this->_lucene = Lucene::create('data/index/');

        foreach ($this->_providers as $provider) {
            $documents = $provider->getDocuments();

            foreach ($documents as $document) {
                $this->_lucene->addDocument($document);
            }
        }

        $this->_lucene->optimize();
    }

    /**
     * Update the current search index
     */
    public function updateIndex()
    {
        $this->_lucene = Lucene::open('data/index/');

        foreach ($this->_providers as $provider) {
            $documents = $provider->getDocuments();

            foreach ($documents as $document) {
                // find document by url and delete id
                $query = new QueryTerm(new IndexTerm($document->url, 'url'));
                $hits = $this->_lucene->find($query);
                foreach ($hits as $hit) {
                    $this->_lucene->delete($hit);
                }
                // re-add document to index
                $this->_lucene->addDocument($document);
            }
        }

        $this->_lucene->optimize();
    }
}