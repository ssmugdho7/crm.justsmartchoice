<?php

namespace Techy4m\XmlExports;
defined('BASEPATH') or exit('No direct script access allowed');

class XmlOutputWriter
{
    private static function defaultHeaders(string $name, string $data)
    {
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        //header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) or empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            header('Content-Length: ' . strlen($data));
        }
    }

    public static function download(string $name, string $data)
    {
        self::defaultHeaders($name, $data);

        // use the Content-Disposition header to supply a recommended filename
        header('Content-Disposition: attachment; filename="' . rawurlencode(basename($name)) . '"; ' . 'filename*=UTF-8\'\'' . rawurlencode(basename($name)));

        // force download dialog
        if (strpos(php_sapi_name(), 'cgi') === false) {
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);
            header('Content-Type: text/xml', false);
        } else {
            header('Content-Type: text/xml');
        }
        header('Content-Transfer-Encoding: binary');

        echo $data;
    }

    public static function stream(string $name, string $data)
    {
        self::defaultHeaders($name, $data);

        // use the Content-Disposition header to supply a recommended filename
        header('Content-Disposition: inline; filename="' . rawurlencode(basename($name)) . '"; ' . 'filename*=UTF-8\'\'' . rawurlencode(basename($name)));

        if (strpos(php_sapi_name(), 'cgi') === false) {
            header('Content-Type: text/xml', false);
        } else {
            header('Content-Type: text/xml');
        }

        echo $data;
    }

    /**
     * @param string $name
     * @param string $data
     * @return array{attachment: string, filename: string, type: string}
     */
    public static function emailAttachment(string $name, string $data): array
    {
        return [
            'attachment' => $data,
            'filename' => str_replace('/', '-', rawurlencode(basename($name))),
            'type' => 'text/xml',
        ];
    }
}
