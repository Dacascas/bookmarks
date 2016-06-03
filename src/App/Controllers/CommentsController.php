<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CommentsController
{
    protected $commentsService;
    protected $bookmarksService;
    const HOUR = 1;

    public function __construct($serviceComments, $serviceBookmark)
    {
        $this->commentsService = $serviceComments;
        $this->bookmarksService = $serviceBookmark;
    }

    public function getAll(Request $request)
    {
        return new JsonResponse($this->commentsService->getAll($request->request->get("max_on_page", 10)));
    }

    private function getBy($field, $value)
    {
        return $this->commentsService->getOneBy($field, $value);
    }

    public function getElement(Request $request)
    {
        $data = [];
        $bookmark = $this->getDataFromRequest($request);

        $this->convertUrl($bookmark);

        if ($this->validateUrl($bookmark['url'])) {
            $data = $this->getBy('url', $bookmark['url']);
            $data['comments'] = $this->commentsService->getComments($data['id']);;
        } else {
            throw new HttpException(400, 'Not valid param');
        }

        $data['uid'] = $data['id'];
        unset($data['id']);

        asort($data);

        return new JsonResponse($data);
    }

    public function save(Request $request)
    {
        $data = [];
        $bookmark_id = $request->request->get('bookmark_id');

        if ($this->checkBookmarkId($bookmark_id)) {
            $comment['id'] = uniqid();
            $comment['ip'] = $request->getClientIp();
            $comment['text'] = $request->request->get('text');
            $comment['bookmark_id'] = $bookmark_id;

            try {
                if ($this->commentsService->save($comment)) {
                    $data['uid'] = $comment['id'];
                }
            } catch (\Exception $e) {
                throw new HttpException(400, 'Not valid param');
            }

        } else {
            throw new HttpException(400, 'Not valid param');
        }

        return new JsonResponse($data);
    }

    private function checkBookmarkId($bookmark)
    {
        return $this->bookmarksService->getOneBy('id', $bookmark);
    }

    public function update($comment_id, Request $request)
    {
        if($this->checkPermission($comment_id, $request)) {
            $data = $this->_parsePut();
            $this->commentsService->update($comment_id, ['text' => $data['text']]);

            return new JsonResponse(['success' => true]);
        } else {
            throw new HttpException(403, 'Cant to update');
        }
    }

    public function delete($comment_id, Request $request)
    {
        if($this->checkPermission($comment_id, $request)) {
            if ($this->commentsService->delete($comment_id)) {
                return new JsonResponse(['success' => true]);
            } else {
                throw new HttpException(400, 'Not valid param');
            }
        } else {
            throw new HttpException(403, 'Cant to delete');
        }
    }

    private function _parsePut()
    {
        /* PUT data comes in on the stdin stream */
        $putdata = fopen("php://input", "r");

        /* Open a file for writing */
        // $fp = fopen("myputfile.ext", "w");

        $raw_data = '';

        /* Read the data 1 KB at a time
           and write to the file */
        while ($chunk = fread($putdata, 1024)) {
            $raw_data .= $chunk;
        }

        /* Close the streams */
        fclose($putdata);

        // Fetch content and determine boundary
        $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

        if (empty($boundary)) {
            parse_str($raw_data, $data);
            $GLOBALS['_PUT'] = $data;
            return;
        }

        // Fetch each part
        $parts = array_slice(explode($boundary, $raw_data), 1);
        $data = array();

        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") {
                break;
            }

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                $tmp_name = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );
                list(, $type, $name) = $matches;

                //Parse File
                if (isset($matches[4])) {
                    //if labeled the same as previous, skip
                    if (isset($_FILES[$matches[2]])) {
                        continue;
                    }

                    //get filename
                    $filename = $matches[4];

                    //get tmp name
                    $filename_parts = pathinfo($filename);
                    $tmp_name = tempnam(ini_get('upload_tmp_dir'), $filename_parts['filename']);

                    //populate $_FILES with information, size may be off in multibyte situation
                    $_FILES[$matches[2]] = array(
                        'error' => 0,
                        'name' => $filename,
                        'tmp_name' => $tmp_name,
                        'size' => strlen($body),
                        'type' => $value
                    );

                    //place in temporary directory
                    file_put_contents($tmp_name, $body);
                } //Parse Field
                else {
                    $data[$name] = substr($body, 0, strlen($body) - 2);
                }
            }

        }
        return $data;
    }

    private function checkPermission($comment_id, Request $request)
    {
        $data = $this->commentsService->getOneBy('id', $comment_id, '*');
        $time_access = (new \DateTime())->diff(new \DateTime($data['created_at']));

        return $time_access->h < self::HOUR && $request->getClientIp() == $data['IP'];
    }
}