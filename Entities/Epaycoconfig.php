<?php

namespace Modules\Icommerceepayco\Entities;

class Epaycoconfig
{

    private $description;
    private $merchantId;
    private $apilogin;
    private $apiKey;
    private $accountId;
    private $url_action;
    private $currency;
    private $test;
    private $image;
    private $status;

    public function __construct()
    {
        $this->description = setting('icommerceEpayco::description');
        $this->test = setting('icommerceEpayco::test');

        $this->image = setting('icommerceEpayco::image');
        $this->status = setting('icommerceEpayco::status');
    }

    public function getData()
    {
        return (object) [
            'description' => $this->description,
            'test' => $this->test,
            'image' => url($this->image),
            'status' => $this->status
        ];
    }

}