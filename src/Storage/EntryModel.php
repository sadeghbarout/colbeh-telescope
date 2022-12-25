<?php

namespace Laravel\Telescope\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Telescope\Database\Factories\EntryModelFactory;

class EntryModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'telescope_entries';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'content' => 'json',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Prevent Eloquent from overriding uuid with `lastInsertId`.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Scope the query for the given query options.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTelescopeOptions($query, $type, EntryQueryOptions $options)
    {
        $this->whereType($query, $type)
                ->whereBatchId($query, $options)
                ->whereTag($query, $options)
                ->whereFamilyHash($query, $options)
                ->whereBeforeSequence($query, $options)
                ->whereStartTime($query, $options)
                ->whereEndTime($query, $options)
                ->whereAroundTime($query, $options)
                ->wherePath($query, $options)
                ->whereMethod($query, $options)
                ->whereSearch($query, $options)
                ->sort($query, $options)
                ->statusCode($query, $options)
                ->filter($query, $options);

        return $query;
    }

    /**
     * Scope the query for the given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return $this
     */
    protected function whereType($query, $type)
    {
        $query->when($type, function ($query, $type) {
            return $query->where('type', $type);
        });

        return $this;
    }

    /**
     * Scope the query for the given batch ID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
     * @return $this
     */
    protected function whereBatchId($query, EntryQueryOptions $options)
    {
        $query->when($options->batchId, function ($query, $batchId) {
            return $query->where('batch_id', $batchId);
        });

        return $this;
    }

    /**
     * Scope the query for the given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
     * @return $this
     */
    protected function whereTag($query, EntryQueryOptions $options)
    {
        $query->when($options->tag, function ($query, $tag) {
            return $query->whereIn('uuid', function ($query) use ($tag) {
                $query->select('entry_uuid')->from('telescope_entries_tags')->whereTag($tag);
            });
        });

        return $this;
    }

    /**
     * Scope the query for the given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
     * @return $this
     */
    protected function whereFamilyHash($query, EntryQueryOptions $options)
    {
        $query->when($options->familyHash, function ($query, $hash) {
            return $query->where('family_hash', $hash);
        });

        return $this;
    }

    /**
     * Scope the query for the given pagination options.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
     * @return $this
     */
    protected function whereBeforeSequence($query, EntryQueryOptions $options)
    {
        $query->when($options->beforeSequence, function ($query, $beforeSequence) {
            return $query->where('sequence', '<', $beforeSequence);
        });

        return $this;
    }

    /**
     * Scope the query for the given display options.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
     * @return $this
     */
    protected function filter($query, EntryQueryOptions $options)
    {
        if ($options->familyHash || $options->tag || $options->batchId) {
            return $this;
        }

        $query->where('should_display_on_index', true);

        return $this;
    }



	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function whereStartTime($query, EntryQueryOptions $options)
	{
		$query->when($options->startTime, function ($query, $startTime) {
			return $query->where('created_at', '>', $startTime);
		});

		return $this;
	}

	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function whereEndTime($query, EntryQueryOptions $options)
	{
		$query->when($options->endTime, function ($query, $endTime) {
			return $query->where('created_at', '<', $endTime);
		});

		return $this;
	}

	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function whereAroundTime($query, EntryQueryOptions $options)
	{
		$query->when($options->aroundTime, function ($query,$aroundTime) {
			return $query->where('created_at', 'LIKE', "%$aroundTime%");
		});

		return $this;
	}

	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function wherePath($query, EntryQueryOptions $options)
	{
		$query->when($options->path, function ($query,$path) {
			$pathReformat = str_replace("/", '\\\\/' , $path);
			return $query->where('content', 'LIKE', '%'.$pathReformat.'%');
		});

		return $this;
	}

	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function whereMethod($query, EntryQueryOptions $options)
	{
		$query->when($options->method, function ($query,$method) {
			return $query->where('content', 'LIKE', '%"method":"'.$method.'"%');
		});

		return $this;
	}

	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function whereSearch($query, EntryQueryOptions $options)
	{
		$query->when($options->search, function ($query,$search) {
			return $query->where('content', 'LIKE', "%$search%");
		});

		return $this;
	}

	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function sort($query, EntryQueryOptions $options)
	{
		$query->when($options->sort, function ($query,$sort) {
			return $query->orderBy('sequence', $sort);
		});

		return $this;
	}

	/**
	 * Scope the query for the given type.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Laravel\Telescope\Storage\EntryQueryOptions  $options
	 * @return $this
	 */
	protected function statusCode($query, EntryQueryOptions $options)
	{
		$query->when($options->status_code, function ($query,$statusCode) {
			return $query->where('content', 'LIKE',  '%"response_status":'.$statusCode. '%');
		});

		return $this;
	}

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('telescope.storage.database.connection');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory()
    {
        return EntryModelFactory::new();
    }
}
