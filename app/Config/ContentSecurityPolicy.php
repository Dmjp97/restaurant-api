<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ContentSecurityPolicy extends BaseConfig
{
    public bool $reportOnly = false;
    public ?string $reportURI = null;
    public ?string $reportTo = null;
    public bool $upgradeInsecureRequests = false;
    public $defaultSrc = null;
    public $scriptSrc = 'self';
    public array|string $scriptSrcElem = 'self';
    public array|string $scriptSrcAttr = 'self';
    public $styleSrc = 'self';
    public array|string $styleSrcElem = 'self';
    public array|string $styleSrcAttr = 'self';
    public $imageSrc = 'self';
    public $baseURI = null;
    public $childSrc = 'self';
    public $connectSrc = 'self';
    public $fontSrc = null;
    public $formAction = 'self';
    public $frameAncestors = null;
    public $frameSrc = null;
    public $mediaSrc = null;
    public $objectSrc = 'self';
    public $manifestSrc = null;
    public array|string $workerSrc = [];
    public $pluginTypes = null;
    public $sandbox = null;
    public string $styleNonceTag = '{csp-style-nonce}';
    public string $scriptNonceTag = '{csp-script-nonce}';
    public bool $autoNonce = true;
}
