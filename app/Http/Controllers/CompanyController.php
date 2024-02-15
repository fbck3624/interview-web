<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum')->except(['index']);
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

            if (!Auth::check() && !in_array($functionName, ['show', 'index'])) {
                return response()->json('You are not login', 401);
            }

            return $next($request);
        });
    }
    /**
     * 取得所有公司
     * 
     * @param Request $request
     * 
     * @return Company
     */
    public function index(Request $request)
    {
        $name = $request->input('name');
        $sort = $request->input('sort');
        $column = key($sort) ?? 'created_at';
        $order = current($sort) ?? 'desc';
        $codition = [];

        $company = new Company();

        // 收集條件篩選
        if ($name) {
            $codition[] = ['name', 'like', "%{$name}%"];
        }

        $perPage = (int) ($request->input('per_page') ?? 20);
        $page = (int) ($request->input('page') ?? 1);

        $company = $company->sortCompany($column, $order)
            ->where($codition)
            ->withAvg('comment', 'score')
            ->paginate($perPage, ['*'], 'page', $page);

        return $company;
    }

    /**
     * 取得單筆公司+所有評論
     * 
     * @param string $id
     * 
     * @return Company
     */
    public function show(string $id): Company
    {
        return Company::with('comment')->findOrFail($id);
    }
}
