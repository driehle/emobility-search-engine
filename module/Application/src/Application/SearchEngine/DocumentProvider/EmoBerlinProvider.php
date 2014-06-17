<?php

namespace Application\SearchEngine\DocumentProvider;

use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;

class EmoBerlinProvider extends AbstractDocumentProvider
{
    /**
     * @var string path to the file
     */
    protected $_file = 'data/sources/emoberlin.txt';

    /**
     * @param \DOMDocument $dom the DOM document
     * @param \DOMXPath $xpath the XPath object
     * @param Document $doc the Lucene document
     * @return void
     */
    public function _parseDocument(\DOMDocument $dom, \DOMXPath $xpath, Document $doc)
    {
        $doc->addField(Field::text('country', 'Berlin'));

        $headers = $dom->getElementsByTagName("h2");
        foreach ($headers as $header) {
            /* @var \DOMNode $header */
            $content = '';
            $this->_retrieveNodeText($header, $content);

            if (strpos($content, 'Projekttitel') !== false) {
                $title = '';
                $this->_retrieveNodeText($header->nextSibling, $title);
                $doc->addField(Field::text('title', $title));
            }
            elseif (strpos($content, 'Kurzbeschreibung') !== false) {
                $description = '';
                $this->_retrieveNodeText($header->nextSibling, $description);
                $description = preg_replace('/\s+/', ' ', $description);
                $doc->addField(Field::text('description', $description));
            }
        }
    }
}