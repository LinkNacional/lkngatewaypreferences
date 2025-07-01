<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository;

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractRepository;
use stdClass;

final class PrefByClientRepository extends AbstractRepository
{
    protected string $table = 'mod_lkngatewaypreferences_by_client';

    public function store(int $clientId, array $gateways): void
    {
        $this->query()
            ->updateOrInsert(
                ['client_id' => $clientId],
                ['gateways' => $this->jsonEncode($gateways)]
            );
    }

    public function getOne(int $clientId): array
    {
        $gateways = $this->query()
            ->where('client_id', $clientId)
            ->first('gateways')
            ->gateways;

        return $gateways ? $this->jsonDecode($gateways) : [];
    }

    public function index(int $prefsPerPage, int $currentPage): array
    {
        $offset = ($currentPage - 1) * $prefsPerPage;

        $prefs = $this->query()
            ->join('tblclients', "{$this->table}.client_id", '=', 'tblclients.id')
            ->select(
                "{$this->table}.*",
                $this->query()->raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client_name")
            )
            ->offset($offset)
            ->limit($prefsPerPage)
            ->get();

        $totalPrefs = $this->query()->count();

        $prefs = array_map(
            function (stdClass $pref): stdClass {
                $pref->gateways = $this->jsonDecode($pref->gateways);

                return $pref;
            },
            $prefs->toArray()
        );

        return ['prefs' => $prefs, 'totalPrefs' => $totalPrefs];
    }

    public function indexByType(string $search, string $type, int $prefsPerPage, int $currentPage): array
    {
        $offset = ($currentPage - 1) * $prefsPerPage;

        switch ($type) {
            case 'id':
                $res = Capsule::table('tblclients')
                ->select('id', 'firstname', 'lastname', 'email')
                ->whereRaw('INSTR(CONCAT("", id), "' . $search . '")')
                ->where('status', 'LIKE', 'Active')
                ->join($this->table, "{$this->table}.client_id", '=', 'tblclients.id')
                ->select(
                    "{$this->table}.*",
                    $this->query()->raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client_name")
                )
                ->offset($offset)
                ->limit($prefsPerPage)
                ->get();
                break;

            case 'name':
                $res = Capsule::table('tblclients')
                ->select('id', 'firstname', 'lastname', 'email')
                ->whereRaw('INSTR(CONCAT(CONCAT(firstname, " "), lastname), "' . $search . '")')
                ->where('status', 'LIKE', 'Active')
                ->join($this->table, "{$this->table}.client_id", '=', 'tblclients.id')
                ->select(
                    "{$this->table}.*",
                    $this->query()->raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client_name")
                )
                ->offset($offset)
                ->limit($prefsPerPage)
                ->get();
                break;

            case 'domain':
                $res = Capsule::table('tblclients')
                ->selectRaw('DISTINCT `tblclients`.`id`, `tblclients`.`firstname`, `tblclients`.`lastname`, `tblclients`.`email`')
                ->select(
                    "{$this->table}.*",
                    $this->query()->raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client_name")
                )
                ->join($this->table, "{$this->table}.client_id", '=', 'tblclients.id')
                ->crossJoin(Capsule::raw('`tbldomains` on `tblclients`.`id` = `tbldomains`.`userid` where INSTR(`tbldomains`.`domain`, "' . $search . '") and (`tblclients`.`status` LIKE "Active")'))
                ->offset($offset)
                ->limit($prefsPerPage)
                ->get();
                break;

            case 'email':
                $res = Capsule::table('tblclients')
                ->select('id', 'firstname', 'lastname', 'email')
                ->whereRaw('INSTR(email, "' . $search . '")')
                ->where('status', 'LIKE', 'Active')
                ->join($this->table, "{$this->table}.client_id", '=', 'tblclients.id')
                ->select(
                    "{$this->table}.*",
                    $this->query()->raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client_name")
                )
                ->offset($offset)
                ->limit($prefsPerPage)
                ->get();
                break;
            default:
                $res = Capsule::table('tblclients')
                ->select('id', 'firstname', 'lastname', 'email')
                ->where('status', 'LIKE', 'Active')
                ->join($this->table, "{$this->table}.client_id", '=', 'tblclients.id')
                ->select(
                    "{$this->table}.*",
                    $this->query()->raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client_name")
                )
                ->offset($offset)
                ->limit($prefsPerPage)
                ->get();
                break;
        }

        $prefs = $res;

        $totalPrefs = $this->query()->count();

        $prefs = array_map(
            function (stdClass $pref): stdClass {
                $pref->gateways = $this->jsonDecode($pref->gateways);

                return $pref;
            },
            $prefs->toArray()
        );

        return ['prefs' => $prefs, 'totalPrefs' => $totalPrefs];
    }

    /**
     * @since 1.0.0
     *
     * @param int $clientId
     *
     * @return void
     */
    public function delete(int $clientId): void
    {
        $this->query()->where('client_id', $clientId)->delete();
    }
}
