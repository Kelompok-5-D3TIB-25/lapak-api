<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\RantaiHash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class LogAudit extends Model
{
    protected $table = 'log_audit';

    public $timestamps = false;

    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'rincian' => 'array',
            'dicatat_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(fn () => throw new LogicException('LP-17: log audit tidak boleh diubah.'));
        self::deleting(fn () => throw new LogicException('LP-17: log audit tidak boleh dihapus.'));
    }

    /** @return BelongsTo<Pengguna, $this> */
    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pelaku_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function isiTerlindung(): array
    {
        return [
            'peristiwa' => $this->peristiwa,
            'pelaku_id' => $this->pelaku_id,
            'sasaran' => $this->sasaran,
            'rincian' => $this->rincian,
            'id_permintaan' => $this->id_permintaan,
            'dicatat_pada' => $this->dicatat_pada->format('Y-m-d H:i:s'),
        ];
    }
}