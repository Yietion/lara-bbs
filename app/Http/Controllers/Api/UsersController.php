<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\UserRequest;
use App\Models\User;
use App\Transformers\UserTransformer;
use App\Models\Image;
use App\Transformers\ReplyTransformer;

class UsersController extends Controller
{
    //
    public function store(UserRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);
        if(!$verifyData){
            return $this->response->error('验证码已经失效', 422);
        }
        if(!hash_equals($request->verification_code, $verifyData['code'])){
            return $this->response->errorUnauthorized('验证码错误');
        }
        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password)
        ]);
        \Cache::forget($request->verification_key);
        return $this->response->item($user, new UserTransformer())
        ->setMeta([
            'access_token' => \Auth::guard('api')->fromUser($user),
            'token_type' => 'Bearer',
            'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
        ])->setStatusCode(201);
    }
    /**
     * update更新
     */
    public function update(Request $request)
    {
        $user = $this->user();
        $attributes = $request->only(['name', 'email', 'introduction', 'registration_id']);
        if($request->avatar_image_id){
            $image = Image::find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }
        $user->update($attributes);
        return $this->response->item($user, new UserTransformer());
        
    }
    
    public function me()
    {
        return $this->response->item($this->user(), new UserTransformer());
    }
   
    public function activedIndex(User $user)
    {
        return $this->response->collection($user->getActiveUsers(), new UserTransformer());
    }
}