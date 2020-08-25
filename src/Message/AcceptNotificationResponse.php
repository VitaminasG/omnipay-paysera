<?php

namespace Omnipay\Paysera\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Exception\InvalidResponseException;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AcceptNotificationResponse extends AbstractResponse implements NotificationInterface
{
    /**
     * Create an instance of Accept Notification response.
     *
     * @param  \Omnipay\Common\Message\RequestInterface  $request
     * @param  array  $data
     * @return void
     *
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

//        if ($this->hasUnsupportedType()) {
//            throw new InvalidResponseException('Only macro/EMA payment callbacks are accepted');
//        }

        if ($this->isSuccessful()) {
            echo 'OK';
        }
    }

    /**
     * Determine the response has unsupported type.
     *
     * @return bool
     */
    protected function hasUnsupportedType()
    {
        return ! in_array($this->getDataValueOrNull('type'), ['macro', 'EMA']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionReference()
    {
        return $this->getDataValueOrNull('orderid');
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionStatus()
    {
        switch ($this->getCode()) {
            case '0':
                return NotificationInterface::STATUS_FAILED;
            case '1':
                return NotificationInterface::STATUS_COMPLETED;
        }

        return NotificationInterface::STATUS_PENDING;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getDataValueOrNull('paytext');
    }

    /**
     * Determine test mode is on.
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->getDataValueOrNull('test') !== '0';
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return $this->getCode() === '1';
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getDataValueOrNull('status');
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

    /**
     * Return the value from data or null.
     *
     * @param  string  $name
     * @return string
     */
    protected function getDataValueOrNull($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}
