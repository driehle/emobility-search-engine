<?php

namespace Application\SearchEngine\DocumentProvider;

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

            $charset = 'UTF-8';
            if ($response->getHeaders()->has('Content-Type')) {
                $charset = $response->getHeaders()->get('Content-Type')->getCharset();
                $charset = trim(strtoupper($charset));
                if (empty($charset)) {
                    $charset = 'UTF-8';
                }
            }

            // add http-equiv meta tag if not present
            $data = $response->getBody();
            if (!preg_match('/<meta[^>]*Content-Type[^>]*charset=' . preg_quote($charset) . '[^>*]>/', $data)) {
                $data = preg_replace('/(<head[^>]*>)/', '\\1<meta http-equiv="Content-Type" content="text/html; charset="' . $charset . '" />', $data);
            }

            $dom = new \DOMDocument();
            $dom->substituteEntities = true;
            ErrorHandler::start(E_WARNING);
            $dom->loadHTML($data);
            ErrorHandler::stop();

            $xpath = new \DOMXPath($dom);
            $docBody = '';
            $bodyNodes = $xpath->query('/html/body');
            foreach ($bodyNodes as $bodyNode) {
                // body should always have only one entry, but we process all nodeset entries
                $docBody .= $this->_retrieveNodeText($bodyNode, $charset);
            }

            $doc = new Document();
            $doc->addField(Field::unStored('body', $docBody));
            $doc->addField(Field::keyword('url', $url));
            $doc->addField(Field::unIndexed('timestamp', time()));

            $this->_parseDocument($dom, $xpath, $doc, $charset);

            if (!isset($doc->title)) {
                $doc->addField(Field::text('title', 'Unknown title'));
            }
            if (!isset($doc->description)) {
                $doc->addField(Field::text('description', 'Unknown description'));
            }

            $documents[] = $doc;
            usleep(8000);
        }

        return $documents;
    }

    /**
     * @param \DOMDocument $dom the DOM document
     * @param \DOMXPath $xpath the XPath object
     * @param Document $doc the Lucene document
     * @param string $charset the charset of the document
     * @return void
     */
    abstract public function _parseDocument(\DOMDocument $dom, \DOMXpath $xpath, Document $doc, $charset);

    /**
     * Get node text
     *
     * We should exclude scripts, which may be not included into comment tags, CDATA sections,
     *
     * @param \DOMNode $node
     * @param string $charset
     * @return string
     * @see \ZendSearch\Lucene\Document\Html
     */
    protected function _retrieveNodeText(\DOMNode $node, $charset)
    {
        $text = '';
        $this->_retrieveNodeTextHelper($node, $text);

        // replace &nbsp;
        $text = str_replace("\xc2\xa0", ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * Get node text
     *
     * We should exclude scripts, which may be not included into comment tags, CDATA sections,
     *
     * @param \DOMNode $node
     * @param string &$text
     * @see \ZendSearch\Lucene\Document\Html
     */
    protected function _retrieveNodeTextHelper(\DOMNode $node, &$text)
    {
        if ($node->nodeType == XML_TEXT_NODE) {
            $text .= $node->nodeValue;
            if(!in_array($node->parentNode->tagName, $this->_inlineTags)) {
                $text .= ' ';
            }
        } elseif ($node->nodeType == XML_ELEMENT_NODE  &&  $node->nodeName != 'script') {
            foreach ($node->childNodes as $childNode) {
                $this->_retrieveNodeTextHelper($childNode, $text);
            }
        }
    }
}