<?php

namespace Application\SearchEngine\DocumentProvider;

interface DocumentProviderInterface
{
    /**
     * @return \ZendSearch\Lucene\Document[]
     */
    function getDocuments();
}