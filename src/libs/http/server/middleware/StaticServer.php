<?php namespace mfe\core\libs\http\server\middleware;

use ArrayObject;
use mfe\core\api\http\IHttpSocketReader;
use mfe\core\api\http\IHttpSocketWriter;
use mfe\core\api\http\IMiddlewareServer;

/**
 * Class StaticServer
 *
 * @package mfe\core\libs\http\server\middleware
 */
class StaticServer implements IMiddlewareServer
{
    private $document_root;
    private $document_index = 'index.html';

    public function __construct(ArrayObject $config)
    {
        if (isset($config->http->static->document_root)) {
            $this->document_root = str_replace('\\', '/', $config->http->static->document_root);
        }

        if (isset($config->http->static->document_index)) {
            $this->document_index = $config->http->static->document_index;
        }
    }

    /**
     * @param IHttpSocketReader $reader
     * @param IHttpSocketWriter $writer
     *
     * @return bool
     */
    public function request(IHttpSocketReader $reader, IHttpSocketWriter $writer)
    {
        $path = str_replace('..', '/', $reader->getUriPath());
        $files = [];
        if ($path === '/') {
            if (1 < count($indexes = explode(',', $this->document_index))) {
                foreach ($indexes as $index) {
                    $files[] = $this->document_root . '/' . trim($index);
                }
            } else {
                $files[] = $this->document_root . '/' . $this->document_index;
            }
        } else {
            $files[] = $this->document_root . $path;
        }

        foreach ($files as $file) {
            if (file_exists($file) && is_readable($file) && !is_dir($file)) {
                $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
                $writer
                    ->setHttpStatus(200)
                    ->addHeader('Content-Type', $mimeType)
                    ->send(file_get_contents($file), false);
                return true;
            }
        }

        return false;
    }
}
