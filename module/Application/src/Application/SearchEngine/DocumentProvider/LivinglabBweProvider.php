<?php

namespace Application\SearchEngine\DocumentProvider;

use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;

class LivinglabBweProvider extends AbstractDocumentProvider
{
    /**
     * @var string path to the file
     */
    protected $_file = 'data/sources/livinglabbwe.txt';

    /**
     * @param \DOMDocument $dom the DOM document
     * @param \DOMXPath $xpath the XPath object
     * @param Document $doc the Lucene document
     * @return void
     */
    public function _parseDocument(\DOMDocument $dom, \DOMXPath $xpath, Document $doc)
    {
        $doc->addField(Field::text('country', 'Baden-Württemberg'));

        $headers = $dom->getElementsByTagName("h1");
        foreach ($headers as $header) {
            /* @var \DOMNode $header */
            $title = '';
            $description = '';
            $next = $header->nextSibling;
            while ($next->nodeType == XML_TEXT_NODE) {
                $next = $next->nextSibling;
            }

            $this->_retrieveNodeText($header, $title);
            $this->_retrieveNodeText($next, $description);
            $description = preg_replace('/\s+/', ' ', $description);

            $doc->addField(Field::text('title', $title));
            $doc->addField(Field::text('description', $description));
            break;
        }
    }
}