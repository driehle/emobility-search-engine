<?php

namespace Application\SearchEngine\DocumentProvider;

use Application\SearchEngine\DocumentProvider\DocumentProviderInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Stdlib\ErrorHandler;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;

abstract class AbstractDocumentProvider implements DocumentProviderInterface
{
    /**
     * @var null|string the filename
     */
    protected $_file;

    /**
     * @var null|array a collection of URLs
     */
    protected $_urls;

    /**
     * @var null|\Zend\Http\Client the HTTP client
     */
    protected $_client;

    /**
     * List of inline tags
     *
     * @var array
     */
    private $_inlineTags = array('a', 'abbr', 'acronym', 'dfn', 'em', 'strong', 'code',
        'samp', 'kbd', 'var', 'b', 'i', 'big', 'small', 'strike',
        'tt', 'u', 'font', 'span', 'bdo', 'cite', 'del', 'ins',
        'q', 'sub', 'sup');

    /**
     * @param string $file the path to the file to read URLs from
     */
    public function __construct($file = null)
    {
        $this->_client = new Client();
        $this->_client->setOptions(array(
            'useragent' => 'e-mobility crawler/1.0 (see https://github.com/driehle/emobility-search-engine)',
            'timeout' => 15,
            'maxredirects' => 2
        ));

        if ($file !== null) {
            $this->_file = (string) $file;
        }
    }

    /**
     * @return array a collection of URLs
     * @throws Exception if the file could not be found
     */
    public function getUrls()
    {
        if (is_array($this->_urls)) {
            return $this->_urls;
        }

        if ($this->_file == null || !file_exists($this->_file)) {
            throw new Exception('File not found in ' . get_class($this) . '!');
        }

        $data = file_get_contents($this->_file);
        $lines = preg_split("/(\r\n|\r\n)/", $data);
        $urls = array();

        foreach ($lines as $line) {
            $url = trim($line);
            if (!empty($url)) $urls[] = $url;
        }

        $this->_urls = $urls;
        return $urls;
    }

    /**
     * @return \ZendSearch\Lucene\Document[]
     */
    public function getDocuments()
    {
        $documents = array();

        foreach ($this->getUrls() as $url) {
            $request = new Request();
            $request->setUri($url);
            $request->setMethod('get');

            $response = $this->_client->send($request);

            if (!$response->isSuccess()) {
                printf(
                    'Error %d: %s for %s' . PHP_EOL,
                    $response->getStatusCode(),
                    $response->getReasonPhrase(),
                    $url
                );
                continue;
            }

            $charset = null;
            if ($response->getHeaders()->has('Content-Type')) {
                $charset = $response->getHeaders()->get('Content-Type')->getCharset();
            }
            if ($charset == null) {
                $charset = 'UTF-8';
            }

            $dom = new \DOMDocument();
            $dom->substituteEntities = true;
            ErrorHandler::start(E_WARNING);
            $dom->loadHTML($response->getBody());
            ErrorHandler::stop();

            $xpath = new \DOMXPath($dom);
            $docBody = '';
            $bodyNodes = $xpath->query('/html/body');
            foreach ($bodyNodes as $bodyNode) {
                // body should always have only one entry, but we process all nodeset entries
                $this->_retrieveNodeText($bodyNode, $docBody);
            }

            $doc = new Document();
            $doc->addField(Field::unStored('body', $docBody));
            $doc->addField(Field::unIndexed('url', $url));

            $this->_parseDocument($dom, $xpath, $doc);

            if (!isset($doc->title)) {
                $doc->addField(Field::text('title', 'Unknown title'));
            }
            if (!isset($doc->description)) {
                $doc->addField(Field::text('description', 'Unknown description'));
            }

            $documents[] = $doc;
        }

        return $documents;
    }

    /**
     * @param \DOMDocument $dom the DOM document
     * @param \DOMXPath $xpath the XPath object
     * @param Document $doc the Lucene document
     * @return void
     */
    abstract public function _parseDocument(\DOMDocument $dom, \DOMXpath $xpath, Document $doc);

    /**
     * Get node text
     *
     * We should exclude scripts, which may be not included into comment tags, CDATA sections,
     *
     * @param \DOMNode $node
     * @param string &$text
     * @see \ZendSearch\Lucene\Document\Html
     */
    protected function _retrieveNodeText(\DOMNode $node, &$text)
    {
        if ($node->nodeType == XML_TEXT_NODE) {
            $text .= $node->nodeValue;
            if(!in_array($node->parentNode->tagName, $this->_inlineTags)) {
                $text .= ' ';
            }
        } elseif ($node->nodeType == XML_ELEMENT_NODE  &&  $node->nodeName != 'script') {
            foreach ($node->childNodes as $childNode) {
                $this->_retrieveNodeText($childNode, $text);
            }
        }
    }
}