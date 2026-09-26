<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'uploaded_by',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected static function booted(): void
    {
        // Remove the stored file along with the row.
        static::deleted(fn (ProjectFile $file) => Storage::disk('local')->delete($file->path));
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** "2.4 MB", "830 KB" */
    public function getReadableSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return ($i === 0 ? (int) $size : round($size, 1)).' '.$units[$i];
    }

    /** lucide icon name for the file type */
    public function getIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));

        return match (true) {
            in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']) => 'file-image',
            in_array($ext, ['xls', 'xlsx', 'csv']) => 'file-spreadsheet',
            in_array($ext, ['zip', 'rar', '7z']) => 'file-archive',
            default => 'file-text',
        };
    }
}
