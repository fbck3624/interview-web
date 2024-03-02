<?php

namespace App\Search\Base;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class SearchModel extends Model
{
    public array $defaultSort = ['created_at' => 'desc'];
    /**
     * @var array<mixed> 主要表格排序欄位
     */
    public array $tableSortColumns = [];

    /**
     * @var array<mixed> 其他表格排序欄位
     */
    public array $otherSortColumns = [];
    public array $codition = [];
    public array $searchArray = [];

    /**
     * 篩選條件初始化(待補充)
     * @param array $filter
     * 
     * @return array
     */
    public function buildFilter(array $filter): void
    {
        foreach ($filter as $searchColumn => $value) {
            // 防止未經同意的篩選
            if (isset($this->searchArray[$searchColumn])) {
                switch ($this->searchArray[$searchColumn]) {
                    case 'like':
                        $this->codition[] = [$searchColumn, $this->searchArray[$searchColumn], "%$value%"];
                        break;
                    case '=' || '>' || '<' || '<>':
                        $this->codition[] = [$searchColumn, $this->searchArray[$searchColumn], $value];
                        break;
                }
            }
        }
    }

    /**
     * 分頁
     * @param Request $request
     * 
     * @return Paginator
     */
    public function result(Request $request): Paginator
    {
        $perPage = (int) ($request->input('per_page') ?? 20);
        $page = (int) ($request->input('page') ?? 1);

        $query = $this->search($request);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
