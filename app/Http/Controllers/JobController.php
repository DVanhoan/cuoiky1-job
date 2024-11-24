<?php

namespace App\Http\Controllers;

use App\Models\CompanyCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use App\http\Controllers\ProvinceController;

class JobController extends Controller
{
    private $provinceController;

    public function __construct(ProvinceController $provinceController)
    {
        $this->provinceController = $provinceController;
    }

    public function index(Request $request)
    {
        $categories = CompanyCategory::all();

        $provinces = $this->provinceController->getProvinces();
        // dd($provinces);

        $posts = Post::query()
            ->with('company')
            ->when($request->q, function ($query, $q) {
                return $query->where(function ($qBuilder) use ($q) {
                    $qBuilder->where('job_title', 'LIKE', "%$q%")
                        ->orWhere('skills', 'LIKE', "%$q%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->whereHas('company', function ($companyQuery) use ($categoryId) {
                    $companyQuery->where('company_category_id', $categoryId);
                });
            })
            ->when($request->job_level, fn($query, $jobLevel) => $query->where('job_level', 'LIKE', "%$jobLevel%"))
            ->when($request->education_level, fn($query, $eduLevel) => $query->where('education_level', 'LIKE', "%$eduLevel%"))
            ->when($request->employment_type, fn($query, $empType) => $query->where('employment_type', 'LIKE', "%$empType%"))
            ->when($request->job_location, fn($query, $location) => $query->where('job_location', 'LIKE', "%$location%"))
            ->has('company')
            ->orderBy('views', 'desc')
            ->paginate(6);

        return view('job.index', compact('posts', 'categories', 'provinces'));
    }
}
