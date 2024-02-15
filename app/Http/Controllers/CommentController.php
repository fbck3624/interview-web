<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CommentController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum')->except(['show']);
        // $this->authorizeResource(Comment::class, 'comment');
        $this->middleware(function ($request, $next) {
            $apiPath = $request->route()->getAction();
            // api 路徑
            $methodName = $apiPath['uses'];
            // 切割路徑
            $action = explode('@', $methodName);

            $functionName = end($action);

            // 手動登入
            // 原因：使用middleware('auth:sanctum') 被排除的action吃不到Auth
            if (request()->bearerToken() && $user = Auth::guard('sanctum')->user()) {
                Auth::setUser($user);
            }

            if (!Auth::check() && $functionName !== 'show') {
                return response()->json('You are not login', 401);
            }

            return $next($request);
        });
    }

    /**
     * 單筆評論
     * 
     * @param string $id comment id
     * 
     * @return Comment
     */
    public function show(string $id): Comment
    {
        return Comment::findOrFail($id);
    }

    /**
     * 新增評論
     * 
     * @param CommentRequest $request
     * 
     * @return Comment
     */
    public function store(CommentRequest $request): Comment
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $comment = new Comment();
            $comment->setRawAttributes($data);
            if ($comment->save() === false) {
                throw new BadRequestException('insert_error');
            }
            DB::commit();

            return $comment;
        } catch (Exception $e) {
            DB::rollBack();
            throw new BadRequestException($e->getMessage());
        }
    }

    /**
     * 更新評論
     * 
     * @param CommentRequest $request
     * @param string $id
     * 
     * @return Comment
     */
    public function update(CommentRequest $request, string $id): Comment
    {

        DB::beginTransaction();
        $comment = Comment::findOrFail($id);

        $this->authorize('update-comment', $comment);

        try {
            $data = $request->validated();
            $comment->setRawAttributes($data);

            if ($comment->save() === false) {
                throw new BadRequestException('update_error');
            }
            DB::commit();

            return $comment;
        } catch (Exception $e) {
            DB::rollBack();
            throw new BadRequestException($e->getMessage());
        }
    }

    /**
     * 刪除評論
     * 
     * @param string $id
     * 
     * @return OK
     */
    public function destory(string $id)
    {
        DB::beginTransaction();
        $comment = Comment::findOrFail($id);
        $this->authorize('delete-comment', $comment);

        try {
            $comment->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new BadRequestException($e->getMessage());
        }

        return 'OK';
    }
}
