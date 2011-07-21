
<div class="nl-wrapper">
    {insert name='getstatusmsg'}

    <h2>{gt text='Newsletter'}</h2>

    {if $user or !$coredata.logged_in}
    <div class="z-menu">
        <span class="z-menuitem-title">
            {strip}
            <a href="{modurl modname='Newsletter' type='user' func='main'}" title="{gt text='Your Information'}">
                {gt text='Your Information'}
            </a>

            {if $user}
                {if $modvars.Newsletter.show_archive|default:0}
                &nbsp;|&nbsp;
                <a href="{modurl modname='Newsletter' type='user' func='main' ot='archive'}" title="{gt text='View Archives'}">
                    {gt text='View Archives'}
                </a>
                {/if}

                &nbsp;|&nbsp;
                <a href="{modurl modname='Newsletter' type='user' func='main' ot='options'}" title="{gt text='Settings'}">
                    {gt text='Settings'}
                </a>
            {/if}

            {if $user or !$coredata.logged_in}
                &nbsp;|&nbsp;
                <a href="{modurl modname='Newsletter' type='user' func='main' ot='unsubscribe'}" title="{gt text='Unsubscribe'}">
                    {gt text='Unsubscribe'}
                </a>
            {/if}
            {/strip}
        </span>
    </div>
    {/if}