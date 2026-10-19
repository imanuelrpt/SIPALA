<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Category;
use App\Models\Concerns\GuardsPrivilegedFields;
use CodeIgniter\Model;

class CategoryModel extends Model
{
    use GuardsPrivilegedFields;

    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $returnType       = Category::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    /** @var list<string> */
    protected $allowedFields = ['name'];

    /** @var list<string> */
    protected array $privilegedFields = ['agency_id', 'is_active'];

    /** @var array<string, string> */
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
    ];

    /** @var array<string, array<string, string>> */
    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama kategori wajib diisi.',
            'min_length' => 'Nama kategori minimal 3 karakter.',
        ],
    ];

    /**
     * Kategori aktif beserta nama instansi penanggung jawab.
     *
     * @return list<Category>
     */
    public function withAgency(bool $onlyActive = true): array
    {
        $builder = $this->select('categories.*, agencies.name AS agency_name, agencies.is_active AS agency_active')
            ->join('agencies', 'agencies.id = categories.agency_id', 'left')
            ->orderBy('categories.name', 'ASC');

        if ($onlyActive) {
            $builder->where('categories.is_active', 1);
        }

        /** @var list<Category> $categories */
        $categories = $builder->findAll();

        return $categories;
    }

    public function findWithAgency(int $id): ?Category
    {
        /** @var Category|null $category */
        $category = $this->select('categories.*, agencies.name AS agency_name')
            ->join('agencies', 'agencies.id = categories.agency_id', 'left')
            ->where('categories.id', $id)
            ->first();

        return $category;
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

        foreach ($builder->findAll() as $category) {
            $options[(int) $category->id] = (string) $category->name;
        }

        return $options;
    }
}
