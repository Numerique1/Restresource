<?php
namespace Numerique1\Components\Restresources\Event\Listener;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Class BodyListener
 * Inspired from FOSRest BodyListener.php
 * Handle raw requests sent on POST,PATCH,PUT,DELETE and set the raw request->content on request->request to simulate a form submission.
 */
class BodyListener
{
    const API_FORMAT = 'json';

    /**
     * Core request handler.
     *
     * @param GetResponseEvent $event
     *
     * @throws BadRequestHttpException
     * @throws UnsupportedMediaTypeHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $contentType = $request->headers->get('Content-Type');
        if ($this->isDecodeable($request))
        {
            $format = null === $contentType ? $request->getRequestFormat() : $request->getFormat($contentType);
            $content = $request->getContent();
            if ($format === self::API_FORMAT)
            {
                if (!empty($content))
                {
                    $data = json_decode($content, true);
                    if (is_array($data))
                    {
                        $request->request = new ParameterBag($data);
                    }
                    else
                    {
                        throw new BadRequestHttpException('Invalid ' . $format . ' message received');
                    }
                }
            }
        }
    }

    /**
     * Check if we should try to decode the body.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isDecodeable(Request $request)
    {
        if (!in_array($request->getMethod(), [
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ]))
        {
            return false;
        }
        return !$this->isFormRequest($request);
    }
    /**
     * Check if the content type indicates a form submission.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isFormRequest(Request $request)
    {
        $contentTypeParts = explode(';', $request->headers->get('Content-Type'));
        if (isset($contentTypeParts[0]))
        {
            return in_array($contentTypeParts[0], [
                'multipart/form-data',
                'application/x-www-form-urlencoded'
            ]);
        }
        return false;
    }
}