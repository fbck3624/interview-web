<?php

namespace App\Search;

use App\Models\Company;
use App\Search\Base\SearchModel;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CompanySearch extends SearchModel
{
    /**
     * @var array<mixed> 主要表格排序欄位
     */
    public array $tableSortColumns = [
        'name',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<mixed> 其他表格排序欄位
     */
    public array $otherSortColumns = [
        'comment_avg_score',
    ];

    public function __construct()
    {
        $this->searchArray = [
            'name' => 'like',
        ];
    }

    /**
     * @param Request $request 查詢條件
     * 
     * @return Builder $query
     */
    public function search(Request $request): Builder
    {
        $company = new Company();
        $filter = $request->input('filter');
        $sort = $request->input('sort') ?? [];
        [$column, $order] = $this->sort($sort);
        // 收集條件篩選
        if ($filter) {
            $this->buildFilter($filter);
        }

        $query = $company->sortCompany($column, $order)
            ->where($this->codition)
            ->withAvg('comment', 'score');

        return $query;
    }

    /**
     * 排序初始化
     * @param array $sort
     * 
     * @return array
     */
    public function sort(array $sort): array
    {
        $column = ($sort !== []) ? key($sort) : key($this->defaultSort);
        $order = ($sort !== []) ? current($sort) : current($this->defaultSort);

        return [$column, $order];
    }
}
