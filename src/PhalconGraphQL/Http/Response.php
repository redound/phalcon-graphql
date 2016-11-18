<?php

namespace PhalconGraphQL\Http;

use PhalconApi\Http\Request;
use PhalconGraphQL\Constants\Services;
use PhalconApi\Exception;

class Response extends \PhalconApi\Http\Response
{
    public function setErrorContent(\Exception $e, $developerInfo = false)
    {
        /** @var Request $request */
        $request = $this->getDI()->get(Services::REQUEST);

        /** @var \PhalconApi\Helpers\ErrorHelper $errorHelper */
        $errorHelper = $this->getDI()->has(Services::ERROR_HELPER) ? $this->getDI()->get(Services::ERROR_HELPER) : null;

        $errorCode = $e->getCode();
        $statusCode = 500;
        $message = $e->getMessage();

        if ($errorHelper && $errorHelper->has($errorCode)) {

            $defaultMessage = $errorHelper->get($errorCode);

            $statusCode = $defaultMessage['statusCode'];

            if (!$message) {
                $message = $defaultMessage['message'];
            }
        }

        $error = [
            'code' => $errorCode,
            'message' => $message ?: 'Unspecified error',
        ];

        if ($e instanceof Exception && $e->getUserInfo() != null) {
            $error['info'] = $e->getUserInfo();
        }

        if ($developerInfo === true) {

            $developerResponse = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->getMethod() . ' ' . $request->getURI()
            ];

            if ($e instanceof Exception && $e->getDeveloperInfo() != null) {
                $developerResponse['info'] = $e->getDeveloperInfo();
            }

            $error['developer'] = $developerResponse;
        }

        $this->setJsonContent(['data' => null, 'errors' => [$error]]);
        $this->setStatusCode($statusCode);
    }
}