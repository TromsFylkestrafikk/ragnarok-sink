<?php

namespace Ragnarok\Sink\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ragnarok\Sink\Models\SinkFile
 *
 * @property int $id
 * @property string $sink_id Sink that owns this file
 * @property string $name Name of file relative to disk
 * @property int $size File size in bytes
 * @property string|null $checksum Md5 sum of file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile whereSinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SinkFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SinkFile extends Model
{
    protected $table = 'ragnarok_files';
    protected $fillable = ['sink_id', 'name', 'size', 'checksum'];
}
