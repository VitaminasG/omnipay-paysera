<?php

namespace Omnipay\Paysera\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * Get the API endpoint.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return 'https://www.paysera.com/pay/';
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl()
    {
        return $this->getEndpoint();
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirect()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectMethod()
    {
        return 'POST';
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectData()
    {
        return $this->getData();
    }

    /**
     * @return HttpRedirectResponse|HttpResponse
     */
    public function getRedirectResponse()
    {
        $this->validateRedirect();

        if ('GET' === $this->getRedirectMethod()) {
            return HttpRedirectResponse::create($this->getRedirectUrl());
        }

        $hiddenFields = '';
        foreach ($this->getRedirectData() as $key => $value) {
            $hiddenFields .= sprintf(
                    '<input type="hidden" name="%1$s" value="%2$s" />',
                    htmlentities($key, ENT_QUOTES, 'UTF-8', false),
                    htmlentities($value, ENT_QUOTES, 'UTF-8', false)
                )."\n";
        }

        $output = view('payments.redirect')->render();
        $output = sprintf(
            $output,
            htmlentities($this->getRedirectUrl(), ENT_QUOTES, 'UTF-8', false),
            $hiddenFields
        );

        return HttpResponse::create($output);
    }
}
