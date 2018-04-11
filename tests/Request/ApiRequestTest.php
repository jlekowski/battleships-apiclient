<?php

namespace Tests\Request;

use BattleshipsApi\Client\Request\ApiRequest;
use PHPUnit\Framework\TestCase;

class ApiRequestTest extends TestCase
{
    /**
     * @var ApiRequest
     */
    protected $apiRequest;

    public function setUp()
    {
        $this->apiRequest = new ApiRequest();
    }

    public function testGetSetUri()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setUri('/testUri'));

        // set required options and resolve
        $this->apiRequest->setHttpMethod('GET')->setApiKey(null)->resolve();

        // returns set uri normalized using default api version
        $this->assertEquals('/v1/testUri', $this->apiRequest->getUri());
    }

    public function testNormalizeUriWithVersion()
    {
        // set required options + version and resolve
        $this->apiRequest->setUri('/testUri')->setHttpMethod('GET')->setApiVersion(2)->setApiKey(null)->resolve();

        // returns set uri normalized using api version 2
        $this->assertEquals('/v2/testUri', $this->apiRequest->getUri());
    }

    public function testNormalizeUriWithQueryParams()
    {
        // set required options + query and resolve
        $this->apiRequest->setUri('/testUri')->setHttpMethod('GET')->setApiKey(null)->setQueryParams(['a' => 1, 'hash' => '12#'])->resolve();

        // returns set uri normalized with url encoded query
        $this->assertEquals('/v1/testUri?a=1&hash=12%23', $this->apiRequest->getUri());
    }

    public function testNormalizeUriFullPath()
    {
        // set required options + service name and resolve
        $this->apiRequest->setUri('http://testUri')->setHttpMethod('GET')->setApiKey(null)->resolve();

        // returns url if full was set
        $this->assertEquals('http://testUri', $this->apiRequest->getUri());
    }

    public function testNormalizeUriWithQueryFromChildClass()
    {
        $apiRequest = new class() extends ApiRequest {
            /* protected */ const QUERY = ['a', 'b'];

            protected function configure()/* : void */
            {
                $this->setQueryParam('a', 1)->setQueryParam('b', '&');
            }
        };

        // set required options + service name and resolve
        $apiRequest->setUri('/testUri')->setHttpMethod('GET')->setApiKey(null)->resolve();

        // returns url with GET params
        $this->assertEquals('/v1/testUri?a=1&b=%26', $apiRequest->getUri());
    }

    public function testNormalizeUriSetQueryParamsTakesPrecedenceOverQueryFromChildClass()
    {
        $apiRequest = new class() extends ApiRequest {
            /* protected */ const QUERY = ['a', 'b'];

            protected function configure()/* : void */
            {
                $this->setQueryParam('a', 1)->setQueryParam('b', 2);
            }
        };

        // set required options + query and resolve
        $apiRequest->setUri('/testUri')->setHttpMethod('GET')->setApiKey(null)->setQueryParams(['c' => 3, 'd' => 4])->resolve();

        // returns url with GET params
        $this->assertEquals('/v1/testUri?c=3&d=4', $apiRequest->getUri());
    }

    public function testNormalizeDataFromChildClassDefaultRequired()
    {
        $apiRequest = new class() extends ApiRequest {
            /* protected */ const DATA = ['a', 'b'];

            protected function configure()/* : void */
            {
                $this->setDataParam('a', 1)->setDataParam('b', 2);
            }
        };

        // set required options + service name and resolve
        $apiRequest->setUri('/testUri')->setHttpMethod('POST')->setApiKey(null)->resolve();

        $this->assertEquals(['a' => 1, 'b' => 2], $apiRequest->getData());
    }

    public function testNormalizeDataFromChildClassRequiredOnly()
    {
        $apiRequest = new class() extends ApiRequest {
            /* protected */ const DATA = ['required' => ['a', 'b']];

            protected function configure()/* : void */
            {
                $this->setDataParam('a', 1)->setDataParam('b', 2);
            }
        };

        // set required options + service name and resolve
        $apiRequest->setUri('/testUri')->setHttpMethod('POST')->setApiKey(null)->resolve();

        $this->assertEquals(['a' => 1, 'b' => 2], $apiRequest->getData());
    }

    public function testNormalizeDataFromChildClassDefinedOnly()
    {
        $apiRequest = new class() extends ApiRequest {
            /* protected */ const DATA = ['defined' => ['a', 'b']];

            protected function configure()/* : void */
            {
                $this->setDataParam('a', 1);
            }
        };

        // set required options + service name and resolve
        $apiRequest->setUri('/testUri')->setHttpMethod('POST')->setApiKey(null)->resolve();

        $this->assertEquals(['a' => 1], $apiRequest->getData());
    }

    public function testNormalizeDataFromChildClassRequiredAndDefined()
    {
        $apiRequest = new class() extends ApiRequest {
            /* protected */ const DATA = [
                'required' => ['a', 'b'],
                'defined' => ['c', 'd']
            ];

            protected function configure()/* : void */
            {
                $this->setDataParam('a', 1)->setDataParam('b', 2)->setDataParam('d', 4);
            }
        };

        // set required options + service name and resolve
        $apiRequest->setUri('/testUri')->setHttpMethod('POST')->setApiKey(null)->resolve();

        $this->assertEquals(['a' => 1, 'b' => 2, 'd' => 4], $apiRequest->getData());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetUriThrowsExceptionWhenNotResolved()
    {
        $this->apiRequest->getUri();
    }

    public function testSetUriMakesRequestNotResolved()
    {
        // set required options and resolve, making sure the exception is not thrown here
        $this->apiRequest->setUri('/testUri')->setApiKey(null)->setHttpMethod('GET')->resolve()->getUri();

        // no resolve() after the setter (can't use @expectedException in case the line above throws it)
        try {
            $this->apiRequest->setUri('/testUri2')->getUri();
        } catch (\RuntimeException $e) {
            $this->assertEquals('Config options have not been resolved yet', $e->getMessage());
        }
    }

    public function testGetSetHttpMethod()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setHttpMethod('POST'));

        // set required options and resolve
        $this->apiRequest->setUri('/testUri')->setApiKey(null)->resolve();

        // returns set method
        $this->assertEquals('POST', $this->apiRequest->getHttpMethod());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetHttpMethodThrowsExceptionWhenNotResolved()
    {
        $this->apiRequest->getHttpMethod();
    }

    public function testSetHttpMethodMakesRequestNotResolved()
    {
        // set required options and resolve, making sure the exception is not thrown here
        $this->apiRequest->setUri('/testUri')->setApiKey(null)->setHttpMethod('GET')->resolve()->getHttpMethod();

        // no resolve() after the setter (can't use @expectedException in case the line above throws it)
        try {
            $this->apiRequest->setHttpMethod('GET')->getHttpMethod();
        } catch (\RuntimeException $e) {
            $this->assertEquals('Config options have not been resolved yet', $e->getMessage());
        }
    }

    public function testGetSetHttpData()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setData([1,2]));

        // set required options and resolve
        $this->apiRequest->setUri('/testUri')->setApiKey(null)->setHttpMethod('GET')->resolve();

        // returns set data
        $this->assertEquals([1,2], $this->apiRequest->getData());

        // returns set data json decodes if string provided
        $this->assertEquals([2,3], $this->apiRequest->setData('[2,3]')->resolve()->getData());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDataThrowsExceptionWhenNotResolved()
    {
        $this->apiRequest->getData();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDataThrowsExceptionOnNonJsonString()
    {
        $this->apiRequest->setData('{1');
    }

    /**
     * @see testNormalizeUriWithQueryParams shows the default value and uri changes when setting a different service name
     */
    public function testSetQueryParams()
    {
        // returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setQueryParams(['a' => 1, 'b' => 2]));
    }

    public function testGetHeaders()
    {
        // set required options and resolve
        $this->apiRequest->setUri('/testUri')->setApiKey(null)->setHttpMethod('GET')->resolve();

        // returns default headers
        $this->assertEquals([], $this->apiRequest->getHeaders());

        // returns normalized json headers when data set
        $this->assertEquals(['Authorization' => 'Bearer testKey'], $this->apiRequest->setApiKey('testKey')->resolve()->getHeaders());
    }

    /**
     * @see testGetHeaders shows how it sets Authorization header
     */
    public function testSetApiKey()
    {
        // returns itself when setting string
        $this->assertEquals($this->apiRequest, $this->apiRequest->setApiKey('testKey'));

        // returns itself when setting null
        $this->assertEquals($this->apiRequest, $this->apiRequest->setApiKey(null));
    }

    /**
     * @see testNormalizeUriWithVersion shows the default value and uri changes when setting a different version
     */
    public function testSetApiVersion()
    {
        // returns itself when setting string
        $this->assertEquals($this->apiRequest, $this->apiRequest->setApiVersion(2));
    }

    public function testSetApiKeyMakesRequestNotResolved()
    {
        // set required options and resolve, making sure the exception is not thrown here
        $this->apiRequest->setUri('/testUri')->setApiKey('testKey')->setHttpMethod('GET')->resolve()->getHeaders();

        // no resolve() after the setter (can't use @expectedException in case the line above throws it)
        try {
            $this->apiRequest->setApiKey(null)->getHeaders();
        } catch (\RuntimeException $e) {
            $this->assertEquals('Config options have not been resolved yet', $e->getMessage());
        }
    }

    public function testResolve()
    {
        // set required options and resolve returns itself
        $this->assertEquals($this->apiRequest, $this->apiRequest->setUri('/testUri')->setApiKey(null)->setHttpMethod('GET')->resolve());
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The required option "uri" is missing.
     */
    public function testResolveThrowsExceptionOnMissingUri()
    {
        $this->apiRequest->setApiKey(null)->setHttpMethod('TEST')->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "httpMethod" with value null is invalid. Accepted values are:
     */
    public function testResolveThrowsExceptionOnMissingHttpMethod()
    {
        $this->apiRequest->setUri('/testUri')->setApiKey(null)->resolve();
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @expectedExceptionMessage The option "httpMethod" with value "TEST" is invalid.
     */
    public function testResolveThrowsExceptionOnInvalidHttpMethod()
    {
        $this->apiRequest->setUri('/testUri')->setApiKey(null)->setHttpMethod('TEST')->resolve();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Query config option `b` does not exist
     */
    public function testChildClassSettingUndefinedQueryParamThrowsException()
    {
        new class() extends ApiRequest {
            /* protected */ const QUERY = ['a'];

            protected function configure()/* : void */
            {
                $this->setQueryParam('a', 1)->setQueryParam('b', 2);
            }
        };
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Data config option `b` does not exist
     */
    public function testChildClassSettingUndefinedDataParamThrowsException()
    {
        new class() extends ApiRequest {
            /* protected */ const DATA = ['a'];

            protected function configure()/* : void */
            {
                $this->setDataParam('a', 1)->setDataParam('b', 2);
            }
        };
    }
}
