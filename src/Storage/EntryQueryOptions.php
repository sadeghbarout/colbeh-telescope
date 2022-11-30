<?php

namespace Laravel\Telescope\Storage;

use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;

class EntryQueryOptions
{
    /**
     * The batch ID that entries should belong to.
     *
     * @var string
     */
    public $batchId;

    /**
     * The tag that must belong to retrieved entries.
     *
     * @var string
     */
    public $tag;

    /**
     * The family hash that must belong to retrieved entries.
     *
     * @var string
     */
    public $familyHash;

    /**
     * The ID that all retrieved entries should be less than.
     *
     * @var mixed
     */
    public $beforeSequence;

    /**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var mixed
     */
    public $uuids;

	/**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var string
     */
    public $startTime;

	/**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var string
     */
    public $endTime;

	/**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var string
     */
    public $aroundTime;

	/**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var string
     */
    public $path;

	/**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var string
     */
    public $method;

	/**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var string
     */
    public $sort;

	/**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var string
     */
    public $search;

    /**
     * The number of entries to retrieve.
     *
     * @var int
     */
    public $limit = 50;

    /**
     * Create new entry query options from the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return static
     */
    public static function fromRequest(Request $request)
    {
        return (new static)
                ->batchId($request->batch_id)
                ->uuids($request->uuids)
                ->beforeSequence($request->before)
                ->tag($request->tag)
                ->familyHash($request->family_hash)
                ->startTime($request->start_time, $request->use_time_zone)
                ->endTime($request->end_time, $request->use_time_zone)
                ->aroundTime($request->around_time)
                ->path($request->path)
                ->method($request->method)
                ->sort($request->sort)
                ->search($request->search)
                ->limit($request->take ?? 50);
    }

    /**
     * Create new entry query options for the given batch ID.
     *
     * @param  string  $batchId
     * @return static
     */
    public static function forBatchId(?string $batchId)
    {
        return (new static)->batchId($batchId);
    }

    /**
     * Set the batch ID for the query.
     *
     * @param  string  $batchId
     * @return $this
     */
    public function batchId(?string $batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Set the list of UUIDs of entries tor retrieve.
     *
     * @param  array  $uuids
     * @return $this
     */
    public function uuids(?array $uuids)
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * Set the ID that all retrieved entries should be less than.
     *
     * @param  mixed  $id
     * @return $this
     */
    public function beforeSequence($id)
    {
        $this->beforeSequence = $id;

        return $this;
    }

    /**
     * Set the tag that must belong to retrieved entries.
     *
     * @param  string  $tag
     * @return $this
     */
    public function tag(?string $tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Set the family hash that must belong to retrieved entries.
     *
     * @param  string  $familyHash
     * @return $this
     */
    public function familyHash(?string $familyHash)
    {
        $this->familyHash = $familyHash;

        return $this;
    }

	/**
	 * Set the family hash that must belong to retrieved entries.
	 *
	 * @param  string  $familyHash
	 * @return $this
	 */
	public function startTime(?string $startTime,  $useTimeZone)
	{
		$this->startTime = $this->needConvertTimeZoneToUtc($startTime ,'start', $useTimeZone);
		return $this;
	}

	/**
	 * Set the family hash that must belong to retrieved entries.
	 *
	 * @param  string  $familyHash
	 * @return $this
	 */
	public function endTime(?string $endTime,$useTimeZone)
	{
		$this->endTime = $this->needConvertTimeZoneToUtc($endTime ,'end', $useTimeZone);;

		return $this;
	}

	/**
	 * Set the family hash that must belong to retrieved entries.
	 *
	 * @param  string  $familyHash
	 * @return $this
	 */
	public function aroundTime(?string $aroundTime)
	{
		$this->aroundTime = $aroundTime;

		return $this;
	}

	/**
	 * Set the family hash that must belong to retrieved entries.
	 *
	 * @param  string  $familyHash
	 * @return $this
	 */
	public function path(?string $path)
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * Set the family hash that must belong to retrieved entries.
	 *
	 * @param  string  $familyHash
	 * @return $this
	 */
	public function method(?string $method)
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Set the family hash that must belong to retrieved entries.
	 *
	 * @param  string  $familyHash
	 * @return $this
	 */
	public function sort(?string $sort)
	{
		$this->sort = $sort;

		return $this;
	}

	/**
	 * Set the family hash that must belong to retrieved entries.
	 *
	 * @param  string  $familyHash
	 * @return $this
	 */
	public function search(?string $search)
	{
		$this->search = $search;

		return $this;
	}

    /**
     * Set the number of entries that should be retrieved.
     *
     * @param  int  $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

	private function needConvertTimeZoneToUtc($time, $type, $useTimeZone){
		if($useTimeZone){
			$explodeTime = explode(' ', $time);
			if(count($explodeTime) == 1 && $explodeTime[0] !== ""){
				$newTime = '';

				if($type === 'start'){
					$newTime = "$explodeTime[0] 00:00:00";
				}else{
					$newTime = "$explodeTime[0] 23:59:59";
				}

				try {
					$new_str = new DateTime($newTime, new DateTimeZone('Asia/tehran') );
					$new_str->setTimeZone(new DateTimeZone('UTC'));
					return $new_str->format('Y-m-d H:i:s');
				} catch (\Throwable $e) {
					return $explodeTime[0];
				}
			}

			if(strlen($time) === 19){
				$new_str = new DateTime($time, new DateTimeZone('Asia/tehran') );
				$new_str->setTimeZone(new DateTimeZone('UTC'));
				return $new_str->format('Y-m-d H:i:s');
			}
		}

		return $time;

	}
}
