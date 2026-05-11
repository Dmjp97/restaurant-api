<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ContentTypeFilter
 *
 * Ensures that POST, PUT, and PATCH requests have the correct Content-Type.
 * Prevents issues with unexpected data formats and some CSRF-like attacks on APIs.
 */
class ContentTypeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $method = $request->getMethod();

        if (in_array($method, ['post', 'put', 'patch'], true)) {
            $contentType = $request->getHeaderLine('Content-Type');

            if (!str_contains(strtolower($contentType), 'application/json')) {
                return response()
                    ->setStatusCode(415)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Unsupported Media Type. Expected application/json.'
                    ]);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after
    }
}
