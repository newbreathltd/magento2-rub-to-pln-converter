<?php
namespace Newbreath\Nbp\Controller\Index;

use \Magento\Framework\App\Action\Context;

use \Magento\Framework\HTTP\Client\Curl;

use \Magento\Framework\Json\Helper\Data;

use \Magento\Framework\App\Cache\Manager as CacheManager;
use \Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_resultJsonFactory;
    
    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;
    
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\App\Cache\Manager as CacheManager
     */
    protected $_cacheManager;
    
    /**
     * @param Context                             $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param CacheTypeListInterface $cache
     * @param CacheManager $cacheManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CacheTypeListInterface $cache,
        CacheManager $cacheManager
    ) {
        
        // set _resultJsonFactory to have acecssibilities to on the redner.
        $this->_resultJsonFactory = $resultJsonFactory;
        
        // set _resultJsonFactory to have accessibilites to on the Client URL
        $this->_curl = $curl;


        // set _jsonHelper to jave accessibilites to tools wiht JSOn
        $this->_jsonHelper = $jsonHelper;

        // call parent construct class
        parent::__construct($context);

        // set _cache to have accessibilites to cache control
        $this->_cache = $cache;

        // set _cacheManager to have accessibilites to on the cache control
        $this->_cacheManager = $cacheManager;
    }
 



    public function execute()
    {
        
        // NBP public api url to get exchange rates - see complete documentation at http://api.nbp.pl
        $nbpapi = "http://api.nbp.pl/api/exchangerates/rates/a/rub/?format=json";

        // get posdata from request
        $postdata = $this->getRequest()->getPostValue();

        // make sure that interessing data is float
        if (!isset($postdata["amount"]) || empty($postdata["amount"])) {
            $postdata["amount"] = 0;
        }
        $postamount=floatval($postdata["amount"]);

        // request to nbp api 
        $this->_curl->get($nbpapi);
        
        // response will contain the output in form of JSON string
        // then change to assoc array
        $exchangerate = $this->_jsonHelper->jsonDecode($this->_curl->getBody());
        
        // how many pln we have
        $amount = $postamount * $exchangerate["rates"][0]["mid"];
        
        // create JSON, which will be used to show resposne
        $result = $this->_resultJsonFactory->create();

        // set data to json
        $result->setData(["success"=>true,"exchange_rate"=>$exchangerate["rates"][0]["mid"],"amount"=>$amount]);
            
        // return result to show json
        return $result;
    }   
}