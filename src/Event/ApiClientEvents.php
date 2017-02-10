<?php

namespace BattleshipsApi\Client\Event;

final class ApiClientEvents
{
    /**
     * On ApiClient::call() before request options are resolved
     */
    const PRE_RESOLVE = 'apiclient.pre_resolve';

    /**
     * On ApiClient::call() after getting response
     */
    const POST_REQUEST = 'apiclient.post_request';

    /**
     * On ApiClient::call() on request exception
     */
    const ON_ERROR = 'apiclient.on_error';
}
