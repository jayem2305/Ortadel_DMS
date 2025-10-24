<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SupportingFile extends Model
{
    use HasFactory;

    protected $table = 'supporting_files';

    protected $fillable = [
        'name',
        'description',
        'expiration_date',
        'owner_name',
        'assign_reviewer',
        'assign_approver',
        'locked',
        'keywords',
        'category',
        'org_filename',
        'file',
        'file_type',
        'file_size',
        'file_id',
        'created_by',
        'updated_by',
        'status',
    ];

    protected $casts = [
        'locked' => 'boolean',
        'status' => 'string',
        'expiration_date' => 'datetime',
        'file_size' => 'integer',
        'file_id' => 'integer',
    ];

    protected function decryptField($value)
    {
        if ($value === null)
            return null; // Do not decrypt null

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Already decrypted or invalid
        }
    }

    // Field-specific getters and setters
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getNameAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getDescriptionAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setOwnerNameAttribute($value)
    {
        $this->attributes['owner_name'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getOwnerNameAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setAssignReviewerAttribute($value)
    {
        $this->attributes['assign_reviewer'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getAssignReviewerAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setAssignApproverAttribute($value)
    {
        $this->attributes['assign_approver'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getAssignApproverAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setKeywordsAttribute($value)
    {
        $this->attributes['keywords'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getKeywordsAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getCategoryAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setOrgFilenameAttribute($value)
    {
        $this->attributes['org_filename'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getOrgFilenameAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setFileAttribute($value)
    {
        $this->attributes['file'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getFileAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setFileTypeAttribute($value)
    {
        $this->attributes['file_type'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getFileTypeAttribute($value)
    {
        return $this->decryptField($value);
    }

    /**
     * 🔓 Safely decrypt all encryptable fields when converting to array or JSON
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ([
            'name',
            'description',
            'owner_name',
            'assign_reviewer',
            'assign_approver',
            'keywords',
            'category',
            'org_filename',
            'file',
            'file_type'
        ] as $key) {
            if (array_key_exists($key, $attributes) && $attributes[$key] !== null) {
                try {
                    $decrypted = Crypt::decryptString($attributes[$key]);
                    $json = json_decode($decrypted, true);
                    $attributes[$key] = json_last_error() === JSON_ERROR_NONE ? $json : $decrypted;
                } catch (\Exception $e) {
                    // Keep original value if already decrypted
                }
            }
        }

        return $attributes;
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
