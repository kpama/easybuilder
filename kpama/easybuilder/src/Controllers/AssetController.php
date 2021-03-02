<?php

namespace Kpama\Easybuilder\Controllers;
use Illuminate\Http\Testing\MimeType;

class AssetController
{
    public function serveAction($path)
    {
        $path = dirname(__DIR__) . '/public/' . $path;

        if (file_exists($path)) {
            $type = MimeType::from($path);
            return response()->file($path, [
                'Content-type' => $type
                ]);
        }
        return response('', '404');
    }
}
