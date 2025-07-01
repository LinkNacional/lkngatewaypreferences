<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\Helpers;

use WHMCS\Database\Capsule;

final class Client
{
    public static function getCountry(int $clientId): ?string
    {
        return Capsule::table('tblclients')
            ->where('id', $clientId)
            ->first('country')
            ->country;
    }

    public static function getClientIdByInvoiceId(int $invoiceId): ?int
    {
        return Capsule::table('tblinvoices')
            ->where('tblinvoices.id', $invoiceId)
            ->join('tblclients', 'tblinvoices.userid', '=', 'tblclients.id')
            ->select('tblclients.id')
            ->first()
            ->id;
    }

    public static function getClientInvoiceGatewayCode(int $invoiceId): ?string
    {
        return Capsule::table('tblinvoices')
            ->where('id', $invoiceId)
            ->first('paymentmethod')
            ->paymentmethod;
    }

    /**
     * Searchs clients from name, id, domain or email. Also filters for clients with preferences set up or not.
     *
     * @param string $search             The search string to be used
     * @param string $type               Search for client's 'name', 'id', 'domain', 'email' or use '' to return all clients
     * @param int    $preferenceSettings Set to 0 to search for clients without preferences, 1 to search for clients with preferences and -1 to search for any client
     *
     * @return string A json string with the client's related information
     */
    public static function getClientsByType(string $search = '', string $type = '', int $preferenceSettings = -1): ?string
    {
        switch($preferenceSettings) {
            case 0:
                $table = Capsule::table('tblclients')
                ->selectRaw('DISTINCT `tblclients`.`id`, `tblclients`.`firstname`, `tblclients`.`lastname`, `tblclients`.`email`')
                ->leftJoin('mod_lkngatewaypreferences_by_client', 'tblclients.id', '=', 'mod_lkngatewaypreferences_by_client.client_id')
                ->whereRaw('`mod_lkngatewaypreferences_by_client`.`client_id` is null');
                break;

            case 1:
                $table = Capsule::table('tblclients')
                ->selectRaw('DISTINCT `tblclients`.`id`, `tblclients`.`firstname`, `tblclients`.`lastname`, `tblclients`.`email`')
                ->join('mod_lkngatewaypreferences_by_client', 'tblclients.id', '=', 'mod_lkngatewaypreferences_by_client.client_id');
                break;

            default:
                $table = Capsule::table('tblclients')
                ->select('id', 'firstname', 'lastname', 'email');
                break;
        }

        switch ($type) {
            case 'id':
                $res = $table
                ->whereRaw('INSTR(CONCAT("", id), "' . $search . '")')
                ->where('status', 'LIKE', 'Active')
                ->take(20)
                ->get();
                break;

            case 'name':
                $res = $table
                ->whereRaw('INSTR(CONCAT(CONCAT(firstname, " "), lastname), "' . $search . '")')
                ->where('status', 'LIKE', 'Active')
                ->take(20)
                ->get();
                break;

            case 'domain':
                $res = $table
                ->crossJoin(Capsule::raw('`tbldomains` on `tblclients`.`id` = `tbldomains`.`userid`'))
                ->whereRaw('INSTR(`tbldomains`.`domain`, "' . $search . '") and (`tblclients`.`status` LIKE "Active")')
                ->take(20)
                ->get();
                break;

            case 'email':
                $res = $table
                ->whereRaw('INSTR(email, "' . $search . '")')
                ->where('status', 'LIKE', 'Active')
                ->take(20)
                ->get();
                break;
            default:
                $res = $table
                ->where('status', 'LIKE', 'Active')
                ->take(20)
                ->get();
                break;
        }

        return $res->toJson();
    }
}
