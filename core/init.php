<?php
namespace Net\MJDawson\AddonManager\Core;
use Net\MJDawson\AddonManager\Core\htmlParse;

class Init {
    private $errorLisnters = [];

    public function load($response, $uri){
        // Headers
        $headers = $response->headers->all();

        // Status code
        $status = $response->getStatusCode();
        $statusText = Response::$statusTexts[$status] ?? '';

        // Send headers
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        // Build the HTML
        require dirname(__FILE__).'/htmlParse.php';

        $website = new htmlParse();
        $website->onError(function($err){
            $this->error(500, $err);
        });

        // Default admin tabs
        $website->addAdminTab('Addon Settings', '/admin/addonSettings', 'fa-wrench');
        $website->addAdminTab('Plugins', '/admin/addonSettings/plugins', 'fa-edit');
        $website->addAdminTab('Themes', '/admin/addonSettings/themes', 'fa-wrench');
        $site = $website->parse($response->getContent(), $status, $uri);

        // Update the status
        $status = $site['status'];
        $statusText = Response::$statusTexts[$status] ?? '';

        // Combine 🤯
        header(sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $status, $statusText));

        echo $site['html'];
    }
    public function onError($listner){
        array_push($this->errorLisnters, $listner);
    }
    private function error($error){
        foreach ($this->errorLisnters as $listner) {
            $listner($error);
        }
    }
}

class Response{
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];
}