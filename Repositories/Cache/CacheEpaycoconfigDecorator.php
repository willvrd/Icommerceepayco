<?php

namespace Modules\Icommerceepayco\Repositories\Cache;

use Modules\Icommerceepayco\Repositories\EpaycoconfigRepository;
use Modules\Core\Repositories\Cache\BaseCacheDecorator;

class CacheEpaycoconfigDecorator extends BaseCacheDecorator implements EpaycoconfigRepository
{
    public function __construct(EpaycoconfigRepository $epaycoconfig)
    {
        parent::__construct();
        $this->entityName = 'icommerceepayco.epaycoconfigs';
        $this->repository = $epaycoconfig;
    }
}
