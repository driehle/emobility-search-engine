<?php

namespace Application\SearchEngine\DocumentProvider;

use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;

class MetropolregionProvider extends AbstractDocumentProvider
{
    /**
     * @var string path to the file
     */
    protected $_file = 'data/sources/metropolregion.txt';

    /**
     * @param \DOMDocument $dom the DOM document
     * @param \DOMXPath $xpath the XPath object
     * @param Document $doc the Lucene document
     * @param string $charset the charset of the document
     * @return void
     */
    public function _parseDocument(\DOMDocument $dom, \DOMXPath $xpath, Document $doc, $charset)
    {
        $doc->addField(Field::text('country', 'Niedersachsen'));

        $headers = $dom->getElementsByTagName("h2");
        foreach ($headers as $header) {
            /* @var \DOMNode $header */
            $next = $header->nextSibling;
            while ($next->nodeName != 'p') {
                $next = $next->nextSibling;
            }

            $title = $this->_retrieveNodeText($header, $charset);
            $description = $this->_retrieveNodeText($next, $charset);
            $description = preg_replace('/\s+/', ' ', $description);

            $doc->addField(Field::text('title', $title));
            $doc->addField(Field::text('description', $description));
            break;
        }
    }
}