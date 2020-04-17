<?php


use Modules\Icommerceepayco\Entities\Epaycoconfig;

if (! function_exists('icommerceepayco_get_configuration')) {

    function icommerceepayco_get_configuration()
    {

    	$configuration = new Epaycoconfig();
    	return $configuration->getData();

    }

}

if (! function_exists('icommerceepayco_get_entity')) {

	function icommerceepayco_get_entity()
    {
    	$entity = new Epaycoconfig;
    	return $entity;	
    }

}