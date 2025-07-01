{extends "../layout.tpl"}

{$page="general"}
{$title={$lang['General settings']}}

{block "content"}
    <div style="width: 824.5px;">
        <h5
            class="help-block"
            style="margin-left: 0px; margin-top: -3em; margin-bottom: 2em;"
        >
            {$lang['pluginsettings_subtitle']}
        </h5>
    </div>

    {if isset($settingsSaved)}
        <div
            class="alert alert-success"
            role="alert"
        >
            {$lang['Settings updated!']}
        </div>
    {/if}

    <form
        class="form-horizontal"
        style="max-width: 850px;"
        method="POST"
        target="_self"
        action=""
    >

        <input
            type="hidden"
            name="update-general-settings"
        >

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['Link Nacional License']}</label>
            </div>

            <div class="col-sm-5">
                <input
                    class="form-control"
                    name="lkn-license"
                    type="password"
                    value="{$lkn_license}"
                />
                {if isset($license_msg)}
                    <div
                        class='alert alert-warning'
                        style='margin-top: 1em;'
                    >
                        <p><em>{$lang['license_blurb']}</em></p>
                        <a
                            class="alert-link"
                            href="https://cliente.linknacional.com.br/solicitar/whmcs-preferencia-gateway-pagamento"
                        >
                            {$lang['premiumfunction_buytext']}
                        </a>
                    </div>
                {/if}
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['fraudstatus_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['fraudstatus_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="enable-fraudchange"
                        name="enable-fraudchange"
                        {if isset($license_msg)}
                            disabled
                        {/if}
                        {($enable_fraudchange) ? 'checked' : ''}
                    >
                </div>

                <label
                    for="enable-fraudchange"
                    style="font-weight: normal;"
                >{$lang['fraudstatus_option']}</label>
                {if isset($license_msg)}
                    <div
                        class='alert alert-warning'
                        style='margin-top: 1em; '
                    >
                        <p>
                            <strong>{$lang['premiumfunction_subtext_warning']}</strong>
                        </p>
                        <p>{$lang['premiumfunction_subtext_explanation']}</p>
                    </div>
                {/if}
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['notes_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['notes_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="enable-notes"
                        name="enable-notes"
                        {if isset($license_msg)}
                            disabled
                        {/if}
                        {($enable_notes) ? 'checked' : ''}
                    >
                </div>

                <label
                    for="enable-notes"
                    style="font-weight: normal;"
                >{$lang['notes_option']}</label>
                {if isset($license_msg)}
                    <div
                        class='alert alert-warning'
                        style='margin-top: 1em; '
                    >
                        <p>
                            <strong>{$lang['premiumfunction_subtext_warning']}</strong>
                        </p>
                        <p>{$lang['premiumfunction_subtext_explanation']}</p>
                    </div>
                {/if}
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label lknhn-control-label">{$lang['Enable debug logs']}</label>
                <span class="help-block lknhn-help-block">
                    <a
                        style="color: #777;"
                        href="logs/module-log"
                    >
                        {$lang['Access logs']}
                    </a>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="enable-log"
                        name="enable-log"
                        {($enable_log) ? 'checked' : ''}
                    >
                </div>
                <label
                    for="enable-log"
                    style="font-weight: normal;"
                >{$lang['Enable logs']}</label>
            </div>
        </div>

        <div
            class="form-group"
            style="margin: 55px 0px;"
        >
            <button
                type="submit"
                class="btn btn-primary btn-sm btn-block"
                style="width: 200px;"
            >{$lang['Save']}</button>
        </div>
    </form>
{/block}