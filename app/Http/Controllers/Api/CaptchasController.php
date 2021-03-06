<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;

class CaptchasController extends Controller
{
    //
    public function store(CaptchaRequest $request, CaptchaBuilder $captchaBuilder)
    {
        $key = 'captcha-'. str_random(15);
        $phone = $request->phone;
        $captcha = $captchaBuilder->build();
        $expireAt = now()->addMinutes(2);
        \Cache::put($key, ['phone'=>$request->phone, 'code'=>$captcha->getPhrase()], $expireAt);
        $result = [
            'captcha_key' => $key,
            'expired_at' => $expireAt,
            'captcha_image_content' => $captcha->inline()
        ];
        return $this->response->array($result)->setStatusCode(201);
    }
}
