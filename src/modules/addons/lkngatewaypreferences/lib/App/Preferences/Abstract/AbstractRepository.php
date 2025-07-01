<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract;

use Illuminate\Database\Query\Builder;
use WHMCS\Database\Capsule;

abstract class AbstractRepository
{
    protected string $table;
    protected Builder $query;

    public function __construct()
    {
        $this->query = Capsule::table($this->table);
    }

    /**
     * @since 1.0.0
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function query(): Builder
    {
        return Capsule::table($this->table);
    }

    /**
     * @since 1.0.0
     *
     * @param array $value
     *
     * @return string
     */
    protected function jsonEncode(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @since 1.0.0
     *
     * @param string $value
     *
     * @return array
     */
    protected function jsonDecode(string $value): array
    {
        return json_decode($value, true);
    }
}
