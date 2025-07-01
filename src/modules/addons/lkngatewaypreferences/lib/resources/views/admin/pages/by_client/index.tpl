{extends "../../layout.tpl"}

{$page="pref.byClient"}
{$title={{$lang['Global gateway preferences by client']}}}

{block "content"}
    {include file="../../../../modal.tpl"}

    <div style="width: 824.5px;">
        <h5
            class="help-block"
            style="margin-left: 0px; margin-top: -3em;"
        >
            {$lang['clientpreferences_subtitle']}
        </h5>
        {include file="../../../../clientpicker.tpl"}
        {if ($license->doesUserExceedPrefsOnFreePlan('client'))}
            <div
                class='alert alert-danger'
                style="margin-top: 1em; width: 42em;"
            >
                <p>
                    <strong>{$lang['premiumfunction_limitreached']}</strong>
                </p>
                <p>
                    {$lang['premiumfunction_limitreached_subtext']}<a
                        class="alert-link"
                        href="{$_SERVER[HTTP_HOST]}/admin/addonmodules.php?module=lkngatewaypreferences&c=general&r=create"
                    >{$lang['premiumfunction_buytext_inline']}</a>.
                </p>
            </div>
        {elseif (isset($license_msg))}
            <div
                class='alert alert-warning'
                style="margin-top: 1em; width: 42em;"
            >
                <p>
                    <strong>{$lang['premiumfunction_subtext_warning_client']}</strong>
                </p>
                <p>{$lang['premiumfunction_subtext_additionalrules']}<a
                        class="alert-link"
                        href="{$_SERVER[HTTP_HOST]}/admin/addonmodules.php?module=lkngatewaypreferences&c=general&r=create"
                    >{$lang['premiumfunction_buytext_inline']}</a>.</p>
            </div>
        {/if}
    </div>

    {if $totalPrefs > 0}
        <div style="width: 824.5px;">
            <div
                class="row"
                style="margin: 0 auto 0; margin-bottom: 1em;"
            >
                <div class="col-md-3">
                    <h4 style="font-weight: bold;">{$lang['Client']}</h4>
                </div>
                <div class="col-md-9">
                    <h2 style="font-weight: bold;">{$lang['Allowed payment methods']}</h2>
                    <span class="help-block lknhn-help-block">
                        {$lang['Use CTRL + Click to select more than one option or disable an option.']}
                    </span>
                </div>
            </div>

            <div
                id="clientPrefTemplate"
                class="row client-pref"
                style="margin-bottom: 1em; margin: 0 auto 0; display: none; margin-bottom: 2.5em;"
            >
                <div
                    class="col-md-3"
                    style="display: flex; align-items: center; height: 150px;"
                >
                    <span
                        class="client-name"
                        style="font-size: 1.1em;"
                    ></span>
                </div>

                <div class="col-md-7">
                    <select
                        class="select-gateways form-control"
                        style="height: 150px;"
                        multiple
                    >
                        {foreach from=$paymentMethodsList item=$method}
                            <option value="{$method['code']}">{$method['name']}</option>
                        {/foreach}
                    </select>
                </div>

                <div
                    class="col-md-2"
                    style="display: flex; align-items: center; height: 150px;"
                >
                    <button
                        type="button"
                        class="btn btn-sm btn-primary btn-save-country-pref"
                        style="margin-right: 20px;"
                    >
                        {$lang['Save']}
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-danger btn-remove-country-pref"
                        title="{$lang['The preference for this country will once again be the global preference']}"
                    >
                        {$lang['Remove']}
                    </button>
                </div>
            </div>

            <form
                id="form-client-prefs"
                action="{$systemURL}/modules/addons/lkngatewaypreferences/api.php"
                method="POST"
            >
                {foreach from=$prefs item=$pref}
                    <div
                        class="row client-preferences"
                        style="margin-bottom: 1em; margin: 0 auto 0; margin-bottom: 2.5em;"
                        data-client-id="{$pref->client_id}"
                    >
                        <div
                            class="col-md-3"
                            style="display: flex; align-items: center; height: 150px;"
                        >
                            <span
                                class="client-name"
                                style="font-size: 1.1em;"
                            >
                                <a
                                    href="clientssummary.php?userid={$pref->client_id}"
                                    target="_blank"
                                >
                                    #{$pref->client_id} {$pref->client_name}
                                </a>
                            </span>
                        </div>
                        <div class="col-md-7">
                            <select
                                class="select-gateways form-control"
                                style="height: 150px;"
                                multiple
                                {if $license->doesUserExceedPrefsOnFreePlan('client')}
                                    disabled
                                {/if}
                            >
                                {foreach from=$paymentMethodsList item=$method}
                                    <option
                                        {if in_array($method['code'], $pref->gateways)}selected{/if}
                                        value="{$method['code']}"
                                    >
                                        {$method['name']}
                                    </option>
                                {/foreach}
                            </select>
                        </div>

                        <div
                            class="col-md-2"
                            style="display: flex; align-items: center; height: 150px;"
                        >
                            <button
                                type="button"
                                class="btn btn-sm btn-primary btn-store-client-pref"
                                style="margin-right: 20px;"
                                {if $license->doesUserExceedPrefsOnFreePlan('client')}
                                    disabled
                                {/if}
                            >
                                {$lang['Save']}
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-danger btn-delete-client-pref"
                                title="{$lang['The preference for this country will once again be the global preference']}"
                            >
                                {$lang['Remove']}
                            </button>
                        </div>
                    </div>
                {/foreach}
            </form>
        </div>

        {assign var="totalPages" value=($totalPrefs/$prefsPerPage)|ceil}

        {if $totalPages > 1}
            <nav
                aria-label="Page navigation"
                style="text-align: center;"
            >
                <ul class="pagination">
                    <li
                        {if $currentPage == 1}
                            class="disabled"
                        {/if}
                    >
                        <a
                            href="?module=lkngatewaypreferences&c=by-client&r=create&per-page={$prefsPerPage}&page={($currentPage == 1) ? (1) : $currentPage - 1}"
                            aria-label="Previous"
                        >
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    {for $page=1 to $totalPages}
                        <li
                            {if $page == $currentPage}class="active"
                            {/if}
                        >
                            <a href="?module=lkngatewaypreferences&c=by-client&r=create&per-page={$prefsPerPage}&page={$page}">
                                {$page}
                            </a>
                        </li>
                    {/for}
                    <li
                        {if $currentPage == $totalPages}
                            class="disabled"
                        {/if}
                    >
                        <a
                            href="?module=lkngatewaypreferences&c=by-client&r=create&per-page={$prefsPerPage}&page={($currentPage == $totalPages) ? ($totalPages) : $currentPage + 1}"
                            aria-label="Next"
                        >
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        {/if}
    {else}
        <p style="text-align: center;">{$lang['There are no set preferences.']}</p>
    {/if}

    <div style="height: 200px;"></div>
    <script>
        const systemURL = "{$systemURL}"
    </script>
    <script
        src="{$systemURL}/modules/addons/lkngatewaypreferences/lib/resources/views/admin/pages/by_client/index.js"
        defer
    ></script>
{/block}