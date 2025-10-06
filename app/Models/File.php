<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'expiration_date',
        'owner_name',
        'folder_id',
        'assign_reviewer',
        'assign_approver',
        'locked',
        'keywords',
        'org_filename',
        'file',
        'file_type',
        'file_size',
    ];

    protected $casts = [
        'name' => 'encrypted',
        'description' => 'encrypted',
        'expiration_date' => 'encrypted:date',
        'owner_name' => 'encrypted',
        // 'folder_id' => 'encrypted',  
        'assign_reviewer' => 'encrypted:array',
        'assign_approver' => 'encrypted:array',
        'locked' => 'boolean',
        'keywords' => 'encrypted',
        'org_filename' => 'encrypted',
        'file' => 'encrypted',
        'file_type' => 'encrypted',
        'file_size' => 'encrypted:integer',
    ];
}
