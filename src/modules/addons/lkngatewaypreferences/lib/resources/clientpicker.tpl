<script type="text/javascript">
    if (!window.lkn) {
        window.lkn = {}
    }

    if (!window.lkn.gflang) {
        window.lkn.gflang = {}
    }

    window.lkn.gflang = {$jsonLang}

    var prefs = {json_encode($prefs)}
</script>

<a
    href="#"
    class='btn btn-primary'
    style='margin-top: 1em; margin-bottom: 1em;'
    data-toggle="modal"
    data-target="#lkngatewayprefs-clientpicker"
> {$lang['clientpreferences_insertclient']}</a>

<div
    id="lkngatewayprefs-clientpicker"
    class="modal fade"
    tabindex="-1"
    role="dialog"
>
    <div
        class="modal-dialog"
        role="document"
    >
        <div class="modal-content">
            <div class="modal-header">
                <button
                    id="btn-dismiss"
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                ><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$lang['clientpicker_header']}</h4>
            </div>

            <div class="modal-body">
                <div class='flex flex-center'>
                    <h5 style="font-weight: bold;">{$lang['Search']}</h5>

                    <div class='row'>
                        <div class='col-xs-2'>
                            <select
                                id="select-search-type"
                                class="form-control"
                                style="width:7em;"
                                {if $license->doesUserExceedPrefsOnFreePlan('client')}
                                    disabled
                                {/if}
                            >
                                {for $val=1 to 4}
                                    <option value="{$val}">{$lang["search_type_{strval($val)}"]}</option>
                                {/for}
                            </select>
                        </div>
                        <div class='col-xs-10'>
                            <input
                                class="form-control"
                                id='search-input'
                                name="modal-input"
                                value="{$client_id}"
                                placeholder={$lang['clientid_textbox']}
                                {if $license->doesUserExceedPrefsOnFreePlan('client')}
                                    disabled
                                {/if}
                            />
                        </div>
                    </div>

                    <div style='margin-top: 1em;'>
                        <h5 style="font-weight: bold;">{$lang['Clients']}</h5>
                        <select
                            id="select-client-prefs"
                            class="form-control"
                            {if $license->doesUserExceedPrefsOnFreePlan('client')}
                                disabled
                            {/if}
                        >
                        </select>
                    </div>
                    <div style='margin-top: 1em;'>
                        <h5 style="font-weight: bold;">{$lang['Gateways']}</h5>
                        <select
                            id='select-gateways'
                            class="select-gateways form-control"
                            style="height: 150px; margin-top: 1em;"
                            multiple
                            {if $license->doesUserExceedPrefsOnFreePlan('client')}
                                disabled
                            {/if}
                        >
                            {foreach from=$paymentMethodsList item=$method}
                                <option value="{$method['code']}">{$method['name']}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div style="margin-top: 2em; padding-left: 15px;">
                        <p>
                            <strong
                                id="save-prefs-feedback"
                                style="display: none;"
                            ></strong>
                        </p>
                    </div>
                </div>
                {if ($license->doesUserExceedPrefsOnFreePlan('client'))}
                    <div
                        class='alert alert-danger'
                        style="margin-top: 1em;"
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
                        style="margin-top: 1em;"
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

            <div class="modal-footer">
                <button
                    type="button"
                    id="btn-store-client-prefs"
                    class="btn btn-primary"
                    {if $license->doesUserExceedPrefsOnFreePlan('client')}
                        disabled
                    {/if}
                >{$lang['Add']}</button>
                <button
                    type="button"
                    id='btn-close'
                    class="btn btn-danger"
                    data-dismiss="modal"
                >{$lang['Close']}</button>
            </div>
        </div>
    </div>
</div>


<script
    src="{$systemURL}/modules/addons/lkngatewaypreferences/lib/resources/clientpicker.js"
    defer
></script>