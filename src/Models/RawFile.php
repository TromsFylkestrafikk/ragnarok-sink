<?php

namespace TromsFylkestrafikk\RagnarokSink\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\RagnarokSink\Models\RawFile
 *
 * @property int $id
 * @property string $name
 * @property string $checksum
 * @property string $date
 * @property string $import_status Current import status of file.
 * @property string|null $import_msg Message from last import, if any
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $is_imported
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereImportMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereImportStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RawFile extends Model
{
    protected $table = 'ragnarok_files';
    protected $fillable = ['name', 'checksum', 'date', 'import_status', 'import_msg'];

    public function getIsImportedAttribute()
    {
        return $this->import_status === 'imported';
    }
}
