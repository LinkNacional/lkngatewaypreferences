{extends "../../layout.tpl"}

{$page="pref.byCountry"}
{$title={$lang['Global gateway preferences by country']}}

{block "content"}
    {include file="../../../../modal.tpl"}

    <div style="width: 824.5px;">
        <h5
            class="help-block"
            style="margin-left: 0px; margin-top: -3em; margin-bottom: 2em;"
        >
            {$lang['countrypreferences_subtitle']}
        </h5>
    </div>

    <div
        class="row"
        style="margin-bottom: 1em; border-radius: 4px; width: 824.5px;"
    >
        <div
            class="col-md-12"
            style="margin-bottom: 1em;"
        >
            <h2>{$lang['Global preference']}</h2>
            <span class="help-block lknhn-help-block">
                {$lang['Applies to all countries that do not have a specific preference.']}
            </span>
        </div>

        <div class="col-md-9">
            <select
                id="select-global-pref"
                class="form-control"
                style="height: 150px;"
                multiple
            >
                {foreach from=$paymentMethodsList item=$method}
                    <option
                        {if in_array($method['code'], $globalPrefGateways)}selected{/if}
                        value="{$method['code']}"
                    >{$method['name']}</option>
                {/foreach}
            </select>
        </div>

        <div
            class="col-md-3"
            style="height: 150px; display: flex; justify-content: start; align-items: center;"
        >
            <button
                type="button"
                class="btn btn-sm btn-primary"
                id="btn-global-pref-save"
            >
                {$lang['Save']}
            </button>
        </div>
    </div>


    <div
        class="row"
        style="padding: 15px; border-radius: 4px; width: 824.5px;"
    >
        <div class="col-md-12">
            <hr>
        </div>
        <div
            class="col-md-12"
            style="margin-bottom: 1em;"
        >
            <h2>{$lang['Add specific preference']}</h2>
            <span class="help-block lknhn-help-block">
                {$lang['This rule overrides the global configuration.']}
            </span>
        </div>

        <div class="col-md-9">
            <select
                id="selectNewCountryPreference"
                class="form-control"
                {($license->doesUserExceedPrefsOnFreePlan('country', false))?'disabled':''}
            >
                <option value="">{$lang['Select country']}</option>
                {foreach from=$countriesList item=$country}
                    <option value="{$country['code']}">{$country['name']}</option>
                {/foreach}
            </select>
        </div>

        <div class="col-md-3">
            <button
                id="addNewCountryPreference"
                type="button"
                class="btn btn-sm btn-primary"
                {($license->doesUserExceedPrefsOnFreePlan('country', false))?'disabled':''}
            >
                {$lang['Add']}
            </button>
        </div>

        {if ($license->doesUserExceedPrefsOnFreePlan('country'))}
            <div
                class='alert alert-danger col-md-9'
                style='margin-top: 1em;'
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
                class='alert alert-warning col-md-9'
                style='margin-top: 1em;'
            >
                <p>
                    <strong>{$lang['premiumfunction_subtext_warning_country']}</strong>
                </p>
                <p>{$lang['premiumfunction_subtext_additionalrules']}<a
                        class="alert-link"
                        href="{$_SERVER[HTTP_HOST]}/admin/addonmodules.php?module=lkngatewaypreferences&c=general&r=create"
                    >{$lang['premiumfunction_buytext_inline']}</a>.</p>
            </div>
        {/if}
    </div>

    <div style="width: 824.5px;">
        <div
            class="row"
            style="margin: 0 auto 0; margin-bottom: 1em;"
        >
            <div class="col-md-3">
                <h4 style="font-weight: bold;">{$lang['Country']}</h4>
            </div>
            <div class="col-md-9">
                <h4 style="font-weight: bold;">{$lang['Allowed payment methods']}</h4>
                <small>{$lang['Use CTRL + Click to select more than one option or disable an option.']}</small>
            </div>
        </div>

        <div
            id="countryPreferenceTemplate"
            class="row country-preference"
            style="margin-bottom: 1em; margin: 0 auto 0; display: none; margin-bottom: 2.5em;"
        >
            <div
                class="col-md-3"
                style="display: flex; align-items: center; height: 150px;"
            >
                <span
                    class="country-label"
                    style="font-size: 1.1em;"
                ></span>
            </div>

            <div class="col-md-6">
                <select
                    class="country-gateway-prefs-select form-control"
                    style="height: 150px;"
                    multiple
                >
                    {foreach from=$paymentMethodsList item=$method}
                        <option value="{$method['code']}">{$method['name']}</option>
                    {/foreach}
                </select>
            </div>

            <div style="display: flex; align-items: center; height: 150px;">
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
                    title={$lang['The preference for this country will once again be the global preference']}
                >
                    {$lang['Remove']}
                </button>
            </div>
        </div>

        <form
            id="formCountriesPreferencesCont"
            action="{$systemURL}/modules/addons/lkngatewaypreferences/api.php"
            method="POST"
        >
            {foreach from=$currentPrefsList item=$pref}
                <div
                    class="row country-preference"
                    style="margin-bottom: 1em; margin: 0 auto 0; margin-bottom: 2.5em;"
                    data-country-code="{$pref['countryCode']}"
                >
                    <div
                        class="col-md-3"
                        style="display: flex; align-items: center; height: 150px;"
                    >
                        <span
                            class="country-label"
                            style="font-size: 1.1em;"
                        >
                            {$pref['countryName']}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <select
                            class="country-gateway-prefs-select form-control"
                            style="height: 150px;"
                            multiple
                            {if $license->doesUserExceedPrefsOnFreePlan('country')}
                                disabled
                            {/if}
                        >
                            {foreach from=$paymentMethodsList item=$method}
                                <option
                                    {if in_array($method['code'], $pref['gateways'])}selected{/if}
                                    value="{$method['code']}"
                                >
                                    {$method['name']}
                                </option>
                            {/foreach}
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; height: 150px;">
                        <button
                            type="button"
                            class="btn btn-sm btn-primary btn-save-country-pref"
                            style="margin-right: 20px;"
                            {if $license->doesUserExceedPrefsOnFreePlan('country')}
                                disabled
                            {/if}
                        >
                            {$lang['Save']}
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-danger btn-remove-country-pref"
                            title={$lang['The preference for this country will once again be the global preference']}
                        >
                            {$lang['Remove']}
                        </button>
                    </div>
                </div>
            {/foreach}
        </form>

        <input
            type="hidden"
            form="formCountriesPreferencesCont"
            name="a"
            value="create-by-country"
        ></input>
    </div>

    <div style="height: 200px;"></div>

    <script type="text/javascript">
        const paymentMethods = {$paymentMethodsList|@json_encode nofilter}
        const countries = {$countriesList|@json_encode nofilter}
    </script>

    <script>
        const systemURL = "{$systemURL}"
    </script>
    <script
        src="{$systemURL}/modules/addons/lkngatewaypreferences/lib/resources/views/admin/pages/countries_pref/index.js"
        defer
    ></script>
{/block}