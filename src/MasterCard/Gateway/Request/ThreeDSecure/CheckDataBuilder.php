<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\ThreeDSecure;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Framework\UrlInterface;

class CheckDataBuilder implements BuilderInterface
{
    const PAGE_GENERATION_MODE = 'CUSTOMIZED';
    const PAGE_ENCODING = 'UTF_8';
    const RESPONSE_URL = 'tns/threedsecure/response';

    /**
     * @var UrlInterface
     */
    protected $urlHelper;

    /**
     * ThreeDSecureDataBuilder constructor.
     * @param UrlInterface $urlHelper
     */
    public function __construct(UrlInterface $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $data = [
            '3DSecure' => [
                'authenticationRedirect' => [
                    'pageGenerationMode' => self::PAGE_GENERATION_MODE,
                    'responseUrl' => $this->urlHelper->getUrl(self::RESPONSE_URL),
                ]
            ],
        ];

        return $data;
    }
}
