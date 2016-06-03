<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BookmarksController
{
    protected $bookmarksService;

    public function __construct($service)
    {
        $this->bookmarksService = $service;
    }

    public function getAll(Request $request)
    {
        return new JsonResponse($this->bookmarksService->getAll($request->request->get("max_on_page", 10)));
    }

    private function getBy($field, $value) {
        return $this->bookmarksService->getOneBy($field, $value);
    }

    public function getElement(Request $request)
    {
        $data = [];
        $bookmark = $this->getDataFromRequest($request);

        $this->convertUrl($bookmark);

        if($this->validateUrl($bookmark['url'])) {
            $data = $this->getBy('url', $bookmark['url']);
            $data['comments'] = $this->bookmarksService->getComments($data['uid']);
        } else {
            throw new HttpException(400, 'Not valid param');
        }

        asort($data);

        return new JsonResponse($data);
    }
    
    public function save(Request $request)
    {
        $data = [];
        $bookmark = $this->getDataFromRequest($request);

        if($this->validateUrl($bookmark['url'])) {
            $bookmark['id'] = uniqid();

            try{
                if ($this->bookmarksService->save($bookmark)) {
                    $data = $this->getBy('id', $bookmark['id']);
                    unset($data['created_at']);
                }
            } catch (\Exception $e) {
                if($e->getPrevious()->getCode() == 23000) {
                    $data = $this->getBy('url', $bookmark['url']);
                    unset($data['created_at']);
                } else {
                    throw new HttpException(400, 'Not valid param');
                }
            }

        } else {
            throw new HttpException(400, 'Not valid param');
        }

        return new JsonResponse($data);
    }

    public function getDataFromRequest(Request $request)
    {
        $param = $request->request->has('url') ? $request->request->get('url') : $request->attributes->get('url');

        return $note = array(
            'url' => $param
        );
    }

    private function validateUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        }

        return false;
    }

    private function convertUrl(& $url)
    {
        $url['url'] = 'http://' . str_replace('-', '.', $url['url']);
    }
}
