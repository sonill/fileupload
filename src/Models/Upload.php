<?php

namespace Sanil\FileUpload\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
	protected $fillable = [
		'upload_path',
		'ext',
		'disk',
		'mime_type',
		'collection',
		'size',
		'uploadable_id',
		'uploadable_type',
	];

	public function uploadable()
	{
		return $this->morphTo();
	}
}
