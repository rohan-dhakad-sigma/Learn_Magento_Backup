<?php
namespace Sigma\CustomApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Sigma\CustomApi\Model\DataFactory;
use Magento\Framework\App\Action\Context;


class CustomOrderObserver implements ObserverInterface
{

    protected $_dataExample;
    /**
     * API request URL
     */
    //const API_REQUEST_URI = 'https://amcdev:44303/';
    protected $apiUrl = 'http://anothermagento.com/rest/V1/orders/9 ';


    protected $adminUsername = "rohan";
    protected $adminPassword = "rohan@123";

    protected $NRUrl = "https://log-api.newrelic.com/log/v1?Api-Key=cf711e114a73659b2569df3499ed9a351e79NRAL";

    protected $meshurl = "https://graph.adobe.io/api/0d5964fb-de1b-4a63-8d76-2558ee8b7530/graphql?api_key=27df7fb122fe4e7ba10670ab4512f9b5";
    /**
     * @var ProductFactory
     */
    protected $_productResourceModel;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    protected $scopeConfig;

    /**
     * CustomOrderObserver constructor.
     *
     * @param DataFactory $dataExample
     * @param LoggerInterface $logger
     * @param Client $guzzleClient
     * @param ResponseFactory $responseFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param ProductFactory $productFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Sigma\CustomApi\Model\DataFactory  $dataExample,
        LoggerInterface $logger,
        Client $guzzleClient,
        ResponseFactory $responseFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,
        ProductFactory $productFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_dataExample = $dataExample;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
        $this->guzzleClient = $guzzleClient;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
//            $adminToken = $this->getAdminToken();
//
//            $authorization = 'Bearer ' . $adminToken;
//            $headers = array(
//                'Content-Type' => 'application/json',
//                'Accept' => 'application/json',
//                'Authorization' => $authorization
//            );
//
//            $response = $this->guzzleClient->request('GET', $this->apiUrl, array(
//                    'headers' => $headers
//                )
//            );
//            $response = $response->getBody()->getContents();
//            $res = json_decode($response, true);

            //Auto retry values
            $autoValue = $this->scopeConfig->getValue(
                'retry/configuration/auto_retry',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            //Alert Values
            $alertValue = $this->scopeConfig->getValue(
                'retry/configuration/alerts',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            //Email
            $alertEmails = $this->scopeConfig->getValue(
                'retry/configuration/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            // Retrying option value for trigerring API upto specified value
            $retryValue = $this->scopeConfig->getValue(
                'retry/configuration/retry_number',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            //Get Third party API URLs
            $apiUrls = $this->scopeConfig->getValue(
                'retry/configuration/api_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            //




            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/SplitApiUrl.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            // Line braker for APIs url
            $splitApiUrl = explode(PHP_EOL,$apiUrls);
            $logger->info(print_r($splitApiUrl[1],true));

            $splitEmail = explode(',',$alertEmails);
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/SplitEmail.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r($splitEmail,true));
//            count($splitEmail)


            $headers = array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            );

            $response = $this->guzzleClient->request('GET', $this->apiUrl, array(
                    'headers' => $headers
                )
            );

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Responses.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r($response,true));

            // Loop for sending third party request for first time
//            for($j=0; $j<count($splitApiUrl); $j++) {
//
//                $tempUrl = $splitApiUrl[$j];
//                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ResponseOfAPIs.log');
//                $logger = new \Zend_Log();
//                $logger->addWriter($writer);
//                $logger->info(print_r($tempUrl,true));
//                $response = $this->guzzleClient->request('GET', $this->tempUrl, array(
//                        'headers' => $headers,
//                    )
//                );
////                $response = $response->getBody()->getContents();
////                $res = json_decode($response, true);
//
//            }

            // Retrying upto specified value
//            for($i=0; $i<$retryValue; $i++) {
//                for($j=0; $j<count($splitEmail); $j++ ) {
//
//                }
//                $tempUrl = $splitEmail[$j];
//
//                $response = $this->guzzleClient->request('GET', $tempUrl, array(
//                        'headers' => $headers,
//                    )
//                );
//
//                $response = $response->getBody()->getContents();
////                $logger->info(print_r($response, true));
//
//            }

            // Inserting data into DB Rest_API_logs
            // Creating copy of the object of Model/Data class of same module.
            $model = $this->_dataExample->create();
//            $model->addData([
//                "api_endpoint" => 'Test 1 API Endpoint',
//                "request_data" => 'Test 1 Data',
//                "response_data"=>'Test  1 Response',
//                "retry"=>3,
//                "status" => 0,
//                "error" => 'Test Error'
//            ]);
//            $saveData = $model->save();
//            if($saveData){
//                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Database_rest-API.log');
//                $logger = new \Zend_Log();
//                $logger->addWriter($writer);
//                $logger->info("Data inserted successfully");
//            }

            // Fetch data from Rest-API_logs
            $collection = $model->getCollection();
//            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Response_Database_rest-API.log');
//            $logger = new \Zend_Log();
//            $logger->addWriter($writer);
//            $logger->info(print_r($collection,true));
            foreach($collection as $data) {
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Response_Database_rest-API.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $temp = $data->getData();
                $data->setApiEndpoint('custom points');
                $data->save();
                $logger->info(print_r($temp,true));
                $logger->info(print_r($data->getResponseData(),true));
            }

//            $response = $response->getBody()->getContents();
//            $res = json_decode($response, true);

//            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/API_mesh.log');
//            $logger = new \Zend_Log();
//            $logger->addWriter($writer);
//            $logger->info(print_r($response, true));

        }
        catch (\Exception $e)
        {
            echo $e->getMessage();
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/FailureResponse.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r($e->getMessage(), true));
        }
    }

    public function insertMultiple($table, $data)
    {
        try {
            $tableName = $this->resource->getConnection()->getTableName($table);
            return $this->connection->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot insert data.'));
        }
    }

    /**
     * Get Admin Token via Admin Integration API
     */
    public function getAdminToken()
    {

        try{
            $headers = array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            );
            $request_body = array(
                'username' => $this->adminUsername,
                'password' => $this->adminPassword
            );
            $apiUrl = "http://anothermagento.com/rest/V1/integration/admin/token";
            $response =  $this->guzzleClient->request('POST', $apiUrl, array(
                    'headers' => $headers,
                    'json' => $request_body,
                )
            );
            $response = $response->getBody()->getContents();
            $res = json_decode($response,true);
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/AdminTokenApi.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('Admin token:\n');
            $logger->info(print_r($res, true));
        } catch(\Exception $e)
        {
            echo $e->getMessage();
        }

        return $res;
    }

}
