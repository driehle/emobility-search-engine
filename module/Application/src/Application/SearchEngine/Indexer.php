<?php

namespace Application\SearchEngine;

use Application\SearchEngine\DocumentProvider;
use ZendSearch\Lucene;

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
    }

    /**
     *
     */
    public function buildIndex()
    {
        $this->_lucene = Lucene\Lucene::create('data/index/');

        foreach ($this->_providers as $provider) {
            $documents = $provider->getDocuments();

            foreach ($documents as $document) {
                $this->_lucene->addDocument($document);
            }
        }

        $this->_lucene->optimize();
    }
}