<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'tags',
        'is_pinned',
        'color',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the user that owns the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only pinned notes.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope to search notes by title or content.
     */
    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('content', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope to filter by tag.
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(): bool
    {
        $this->is_pinned = !$this->is_pinned;
        return $this->save();
    }

    /**
     * Add a tag to the note.
     */
    public function addTag(string $tag): bool
    {
        $tags = $this->tags ?? [];
        
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Remove a tag from the note.
     */
    public function removeTag(string $tag): bool
    {
        $tags = $this->tags ?? [];
        
        if (($key = array_search($tag, $tags)) !== false) {
            unset($tags[$key]);
            $this->tags = array_values($tags);
            return $this->save();
        }
        
        return false;
    }

    /**
     * Get excerpt of content.
     */
    public function getExcerptAttribute(int $length = 100): string
    {
        return \Str::limit($this->content, $length);
    }
}
