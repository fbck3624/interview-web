<?php

namespace App\Http\Middleware;

use App\Models\Comment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCommentPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        dd($next($request));
        // // 检查用户是否已登录
        // if (Auth::check()) {
        //     // 用户已登录，继续处理请求
        //     return $next($request);
        // } else {
        //     // 用户未登录，设置评论的 editable 属性为 false
        //     $response = $next($request);
        //     if ($response->status() === 200) {
        //         $content = json_decode($response->content(), true);
        //         $content['editable'] = false;
        //         $response->setContent(json_encode($content));
        //     }
        //     return $response;
        // }
        // $response = $next($request);
        return $next($request);
    }
}
