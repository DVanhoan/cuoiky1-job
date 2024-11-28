<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyCategory;
use App\Models\Post;

class Company extends Model
{
    use HasFactory;

    public function getCategory()
    {
        return $this->hasOne(CompanyCategory::class, 'id', 'company_category_id');
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
