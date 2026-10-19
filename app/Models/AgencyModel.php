<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Agency;
use App\Models\Concerns\GuardsPrivilegedFields;
use CodeIgniter\Model;

class AgencyModel extends Model
{
    use GuardsPrivilegedFields;

    protected $table            = 'agencies';
    protected $primaryKey       = 'id';
    protected $returnType       = Agency::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    /** @var list<string> */
    protected $allowedFields = ['name', 'description'];

    /** @var list<string> */
    protected array $privilegedFields = ['is_active'];

    /** @var array<string, string> */
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[150]',
    ];

    /** @var array<string, array<string, string>> */
    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama instansi wajib diisi.',
            'min_length' => 'Nama instansi minimal 3 karakter.',
        ],
    ];

    /**
     * @return list<Agency>
     */
    public function activeOptions(): array
    {
        /** @var list<Agency> $agencies */
        $agencies = $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        return $agencies;
    }

    /**
     * @return array<int, string>
     */
    public function optionList(bool $onlyActive = true): array
    {
        $builder = $this->orderBy('name', 'ASC');

        if ($onlyActive) {
            $builder->where('is_active', 1);
        }

        $options = [];

        foreach ($builder->findAll() as $agency) {
            $options[(int) $agency->id] = (string) $agency->name;
        }

        return $options;
    }
}
